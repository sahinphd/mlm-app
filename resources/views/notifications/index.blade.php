@extends('admin.layout')

@section('content')
<div class="mx-auto max-w-2xl">
    <div class="rounded-2xl border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-white/[0.03] md:p-4">
        <h2 class="mb-3 text-base font-semibold text-gray-800 dark:text-white/90">Notifications</h2>

        <div class="space-y-2">
            @foreach($notes as $note)
                <div class="flex items-start justify-between rounded-lg border border-gray-100 bg-gray-50 p-2.5 transition hover:border-brand-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-brand-800">
                    <div class="pr-2">
                        <div class="text-xs font-medium text-gray-800 dark:text-white/90">{{ $note->data['message'] ?? class_basename($note->type) }}</div>
                        <div class="mt-0.5 text-[10px] text-gray-500 dark:text-gray-400">{{ $note->created_at->diffForHumans() }}</div>
                    </div>
                    <div class="shrink-0">
                        @if(is_null($note->read_at))
                            <form method="POST" action="{{ route('notifications.read', $note->id) }}">
                                @csrf
                                <button class="inline-flex items-center justify-center rounded-md bg-brand-500 px-2 py-0.5 text-[10px] font-medium text-white transition hover:bg-brand-600">
                                    Mark read
                                </button>
                            </form>
                        @else
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-600 dark:bg-white/5 dark:text-gray-400">
                                Read
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-3">
            {{ $notes->links() }}
        </div>
    </div>
</div>
@endsection
