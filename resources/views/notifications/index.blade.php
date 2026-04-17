@extends('admin.layout')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <h2 class="mb-5 text-xl font-semibold text-gray-800 dark:text-white/90">Notifications</h2>

        <div class="space-y-4">
            @foreach($notes as $note)
                <div class="rounded-xl border border-gray-100 p-4 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.03] flex justify-between items-start transition hover:border-brand-300 dark:hover:border-brand-800">
                    <div>
                        <div class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $note->data['message'] ?? class_basename($note->type) }}</div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $note->created_at->diffForHumans() }}</div>
                    </div>
                    <div class="ml-4">
                        @if(is_null($note->read_at))
                            <form method="POST" action="{{ route('notifications.read', $note->id) }}">
                                @csrf
                                <button class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600 transition">
                                    Mark read
                                </button>
                            </form>
                        @else
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-white/5 dark:text-gray-400">
                                Read
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $notes->links() }}
        </div>
    </div>
</div>
@endsection
