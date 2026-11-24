<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Support\Facades\Auth;

class IdCardController extends Controller
{
    protected LedgerService $ledgerService;

    public function __construct(LedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    private function ensureAdmin(): void
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403);
        }
    }

    /**
     * Display a listing of users with bulk selection & filtering for ID card printing.
     */
    public function index(Request $request)
    {
        $this->ensureAdmin();

        $query = User::with(['referralRecord', 'wallet', 'creditAccount'])->latest();

        // Keyword search (name, email, phone, id, referral_code)
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('id', $search)
                  ->orWhereHas('referralRecord', function ($rq) use ($search) {
                      $rq->where('referral_code', 'like', "%{$search}%");
                  });
            });
        }

        // Role filter
        if ($role = $request->input('role')) {
            if ($role !== 'all') {
                $query->where('role', $role);
            }
        }

        // KYC filter
        if ($kyc = $request->input('kyc_status')) {
            if ($kyc !== 'all') {
                $query->where('kyc_status', $kyc);
            }
        }

        // Has Photo filter
        if ($photo = $request->input('has_photo')) {
            if ($photo === 'yes') {
                $query->whereNotNull('avatar')->where('avatar', '!=', '');
            } elseif ($photo === 'no') {
                $query->where(function ($q) {
                    $q->whereNull('avatar')->orWhere('avatar', '');
                });
            }
        }

        // Registration Date filter
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Key stats
        $stats = [
            'total' => User::count(),
            'kyc_approved' => User::where('kyc_status', 'approved')->count(),
            'with_avatar' => User::whereNotNull('avatar')->where('avatar', '!=', '')->count(),
            'total_customers' => User::where('role', '!=', 'admin')->count(),
        ];

        $perPage = max(10, min(200, (int) $request->input('per_page', 25)));
        $users = $query->paginate($perPage)->withQueryString();

        $settings = SettingController::getSystemSettings();

        return view('admin.id_cards.index', [
            'users' => $users,
            'stats' => $stats,
            'settings' => $settings,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Printable view for bulk or single ID cards.
     */
    public function print(Request $request)
    {
        $this->ensureAdmin();

        $userIds = [];
        if ($request->has('user_ids')) {
            $raw = $request->input('user_ids');
            if (is_array($raw)) {
                $userIds = array_map('intval', $raw);
            } elseif (is_string($raw)) {
                $userIds = array_filter(array_map('intval', explode(',', $raw)));
            }
        }

        // If select_all is checked or no IDs given, pull from filtered list
        if ($request->boolean('select_all') || empty($userIds)) {
            $query = User::query();
            if ($search = trim((string) $request->input('search'))) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('id', $search);
                });
            }
            if ($role = $request->input('role')) {
                if ($role !== 'all') $query->where('role', $role);
            }
            if ($kyc = $request->input('kyc_status')) {
                if ($kyc !== 'all') $query->where('kyc_status', $kyc);
            }
            if ($photo = $request->input('has_photo')) {
                if ($photo === 'yes') {
                    $query->whereNotNull('avatar')->where('avatar', '!=', '');
                } elseif ($photo === 'no') {
                    $query->where(function ($q) {
                        $q->whereNull('avatar')->orWhere('avatar', '');
                    });
                }
            }
            // Limit to max 300 cards per print batch to protect browser performance
            $userIds = $query->limit(300)->pluck('id')->toArray();
        }

        if (empty($userIds)) {
            return redirect()->route('admin.id-cards.index')->with('error', 'No users selected for printing.');
        }

        $users = User::with(['referralRecord', 'wallet', 'creditAccount'])
            ->whereIn('id', $userIds)
            ->get();

        // Preserve the requested ID sequence
        if (!empty($userIds)) {
            $orderMap = array_flip(array_values($userIds));
            $users = $users->sortBy(fn($u) => $orderMap[$u->id] ?? 999999)->values();
        }

        $settings = SettingController::getSystemSettings();

        $layout = $request->input('layout', 'a4'); // 'a4' or 'cr80'
        $side = $request->input('side', 'both'); // 'both', 'front', 'back'
        $cuttingGuides = $request->boolean('cutting_guides', true);
        $brandName = $request->input('brand_name') ?: ($settings['id_card_brand_name'] ?? 'JANASEVA MIDIO VANDAR');
        $signatureTitle = $request->input('signature_title') ?: ($settings['id_card_signature_title'] ?? 'Authorized Signatory');
        $signaturePath = $settings['id_card_signature_path'] ?? 'images/signatures/default-signature.svg';

        $rawHost = config('app.url') ? parse_url(config('app.url'), PHP_URL_HOST) : '';
        $cleanHost = (!empty($rawHost) && !in_array($rawHost, ['localhost', '127.0.0.1'])) ? $rawHost : 'mabia.in';

        // Prepare card items
        $cardItems = $users->map(function ($u) use ($cleanHost) {
            $customerData = $this->ledgerService->formatCustomerQrData($u);
            $qrPayload = $this->ledgerService->getCustomerQrPayload($u);
            $paymentUrl = route('wallet.scan-pay', ['code' => $qrPayload]);
            $barcodeValue = $u->referralRecord->referral_code ?? ('U' . $u->id);

            return [
                'user' => $u,
                'customer' => $customerData,
                'qrPayload' => $qrPayload,
                'paymentUrl' => $paymentUrl,
                'barcodeValue' => $barcodeValue,
                'cleanHost' => $cleanHost,
            ];
        });

        return view('admin.id_cards.print', [
            'cardItems' => $cardItems,
            'totalCards' => $cardItems->count(),
            'layout' => $layout,
            'side' => $side,
            'cuttingGuides' => $cuttingGuides,
            'brandName' => $brandName,
            'signatureTitle' => $signatureTitle,
            'signaturePath' => $signaturePath,
            'autoPrint' => $request->boolean('auto_print', false),
        ]);
    }

    /**
     * Shortcut to print a single user's ID card.
     */
    public function singlePrint(Request $request, User $user)
    {
        $this->ensureAdmin();

        return redirect()->route('admin.id-cards.print', [
            'user_ids' => [$user->id],
            'layout' => $request->input('layout', 'a4'),
            'side' => $request->input('side', 'both'),
            'auto_print' => $request->boolean('auto_print', false),
        ]);
    }
}
