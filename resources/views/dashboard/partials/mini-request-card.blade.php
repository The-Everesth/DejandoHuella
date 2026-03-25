@props(['request'])
<div class="bg-white rounded-lg shadow p-4 flex flex-col gap-2 min-w-[220px] max-w-xs">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        </div>
        <div class="flex-1 min-w-0">
            <div class="font-bold text-gray-900 truncate">Solicitud para {{ $request['petName'] ?? 'adopción' }}</div>
            <div class="text-xs text-gray-500 truncate">{{ $request['applicantName'] ?? '' }}</div>
        </div>
    </div>
    <div class="flex items-center justify-between mt-2">
        <span class="text-xs text-gray-400">{{ isset($request['createdAt']) && $request['createdAt'] ? date('d/m/Y', strtotime($request['createdAt'])) : '' }}</span>
        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">{{ ucfirst($request['status'] ?? '') }}</span>
    </div>
</div>
