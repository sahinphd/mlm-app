<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentRequest;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\MLMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PaymentManagementController extends Controller
{
    protected $mlmService;

    public function __construct(MLMService $mlmService)
    {
        $this->mlmService = $mlmService;
    }

    private function ensureAdmin(): void
    {
        $user = Auth::user();
        if (! $user || ! method_exists($user, 'isAdmin') || ! $user->isAdmin()) {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        $this->ensureAdmin();

        $status = $request->query('status');
        $q = trim((string) $request->query('q', ''));

        $requests = $this->filteredQuery($status, $q)
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END")
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'pending' => PaymentRequest::where('status', 'pending')->count(),
            'approved' => PaymentRequest::where('status', 'approved')->count(),
            'rejected' => PaymentRequest::where('status', 'rejected')->count(),
        ];

        $settingsFile = 'settings.json';
        $settings = Storage::disk('local')->exists($settingsFile)
            ? json_decode(Storage::disk('local')->get($settingsFile), true)
            : [];

        $currency = $settings['currency'] ?? 'INR';

        return view('payments.admin', compact('requests', 'stats', 'status', 'q', 'currency'));
    }

    public function export(Request $request)
    {
        $this->ensureAdmin();

        $status = $request->query('status');
        $q = trim((string) $request->query('q', ''));
        $type = $request->query('type', 'csv');

        $requests = $this->filteredQuery($status, $q)
            ->orderByDesc('created_at')
            ->get();

        if ($type === 'pdf') {
            $html = view('admin.reports.payments_pdf', [
                'requests' => $requests,
                'status' => $status,
                'q' => $q,
            ])->render();

            $pdf = app('dompdf.wrapper');
            $pdf->loadHTML($html);
            return $pdf->stream('payment_requests_' . date('Ymd_His') . '.pdf');
        }

        $filename = 'payment_requests_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($requests) {
            $f = fopen('php://output', 'w');
            fputcsv($f, ['id', 'user_name', 'user_email', 'amount', 'method', 'reference', 'status', 'admin_note', 'created_at', 'processed_at']);
            foreach ($requests as $r) {
                fputcsv($f, [
                    $r->id,
                    $r->user?->name,
                    $r->user?->email,
                    $r->amount,
                    $r->method,
                    $r->reference,
                    $r->status,
                    $r->admin_note,
                    (string) $r->created_at,
                    (string) $r->processed_at,
                ]);
            }
            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function approve(Request $request, PaymentRequest $paymentRequest)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        if ($paymentRequest->status !== 'pending') {
            return back()->with('error', 'This request is already processed.');
        }

        DB::transaction(function () use ($paymentRequest, $data) {
            $paymentRequest->status = 'approved';
            $paymentRequest->admin_note = $data['admin_note'] ?? null;
            $paymentRequest->processed_at = now();
            $paymentRequest->save();

            $user = $paymentRequest->user;
            $isFirstApproval = ($user->status === 'pending');

            if ($isFirstApproval) {
                $user->status = 'active';
                $user->save();
            }

            $wallet = Wallet::firstOrCreate(
                ['user_id' => $paymentRequest->user_id],
                ['main_balance' => 0, 'earning_balance' => 0, 'credit_balance' => 0]
            );

            $wallet->main_balance = (float) $wallet->main_balance + (float) $paymentRequest->amount;
            $wallet->save();

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'credit',
                'source' => $isFirstApproval ? 'joining' : 'manual',
                'amount' => $paymentRequest->amount,
                'reference_id' => 'payment_request:' . $paymentRequest->id,
                'description' => $isFirstApproval ? 'Account activated via joining fee' : 'Admin approved manual wallet recharge request',
            ]);

            if ($isFirstApproval) {
                $this->mlmService->distributeJoiningCommissions($user->id);
            }
        });

        return back()->with('success', 'Payment request approved. Account activated and commissions distributed if applicable.');
    }

    public function reject(Request $request, PaymentRequest $paymentRequest)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        if ($paymentRequest->status !== 'pending') {
            return back()->with('error', 'This request is already processed.');
        }

        $paymentRequest->status = 'rejected';
        $paymentRequest->admin_note = $data['admin_note'] ?? null;
        $paymentRequest->processed_at = now();
        $paymentRequest->save();

        return back()->with('success', 'Payment request rejected.');
    }

    public function reopen(PaymentRequest $paymentRequest)
    {
        $this->ensureAdmin();

        if ($paymentRequest->status !== 'rejected') {
            return back()->with('error', 'Only rejected requests can be reopened.');
        }

        $paymentRequest->status = 'pending';
        $paymentRequest->admin_note = null;
        $paymentRequest->processed_at = null;
        $paymentRequest->save();

        return back()->with('success', 'Payment request moved back to pending.');
    }

    private function filteredQuery(?string $status, string $q)
    {
        return PaymentRequest::query()
            ->with('user:id,name,email,phone')
            ->when(in_array($status, ['pending', 'approved', 'rejected'], true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('reference', 'like', '%' . $q . '%')
                        ->orWhere('method', 'like', '%' . $q . '%')
                        ->orWhereHas('user', function ($u) use ($q) {
                            $u->where('name', 'like', '%' . $q . '%')
                                ->orWhere('email', 'like', '%' . $q . '%')
                                ->orWhere('phone', 'like', '%' . $q . '%');
                        });
                });
            });
    }
}
