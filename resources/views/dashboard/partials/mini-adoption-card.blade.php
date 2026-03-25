@props(['adoption'])
<div class="bg-white rounded-lg shadow p-4 flex flex-col gap-2 min-w-[220px] max-w-xs">
    <div class="flex items-center gap-3">
        <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center overflow-hidden">
            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg>
        </div>
        <div class="flex-1 min-w-0">
            <div class="font-bold text-gray-900 truncate">{{ $adoption['petName'] ?? 'Adopción' }}</div>
            <div class="text-xs text-gray-500 truncate">{{ $adoption['status'] ?? '' }}</div>
            @if(!empty($adoption['breed']))
                <div class="text-xs text-gray-400 truncate">Raza: {{ $adoption['breed'] }}</div>
            @endif
            @if(!empty($adoption['age']))
                <div class="text-xs text-gray-400 truncate">Edad: {{ $adoption['age'] }}</div>
            @endif
            @if(!empty($adoption['description']))
                <div class="text-xs text-gray-400 truncate">{{ $adoption['description'] }}</div>
            @endif
        </div>
    </div>
    <div class="flex items-center justify-between mt-2">
        <span class="text-xs text-gray-400">{{ isset($adoption['createdAt']) && $adoption['createdAt'] ? date('d/m/Y', strtotime($adoption['createdAt'])) : '' }}</span>
        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">{{ ucfirst($adoption['status'] ?? '') }}</span>
    </div>
</div>
