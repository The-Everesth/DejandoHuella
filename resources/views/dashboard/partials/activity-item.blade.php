@props([
    'title',
    'subtitle' => null,
    'date' => null,
    'status' => null,
    'statusLabel' => null,
    'statusColor' => null,
    'icon' => null,
])
<div class="bg-white rounded-lg shadow p-4 flex items-center gap-4">
    @if($icon)
        <span class="text-2xl text-{{ $statusColor ?? 'gray' }}-500">{!! $icon !!}</span>
    @endif
    <div class="flex-1 min-w-0">
        <div class="font-bold text-gray-900 truncate">{{ $title }}</div>
        @if($subtitle)
            <div class="text-xs text-gray-500 truncate">{{ $subtitle }}</div>
        @endif
        <div class="flex items-center gap-2 mt-1">
            @if($date)
                <span class="text-xs text-gray-400">{{ $date }}</span>
            @endif
            @if($status)
                <span class="ml-2 inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-{{ $statusColor ?? 'gray' }}-100 text-{{ $statusColor ?? 'gray' }}-700">
                    {{ $statusLabel ?? ucfirst($status) }}
                </span>
            @endif
        </div>
    </div>
</div>