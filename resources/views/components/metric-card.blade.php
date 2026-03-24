@props(['label', 'value', 'color' => 'bg-gray-100', 'icon' => null])

<div class="flex items-center p-4 rounded-lg shadow-sm {{ $color }}">
    @if($icon)
        <span class="mr-3">
            @if($icon === 'calendar-days')
                <svg class="w-7 h-7 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            @elseif($icon === 'clock')
                <svg class="w-7 h-7 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            @elseif($icon === 'check-circle')
                <svg class="w-7 h-7 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2l4-4m5 4a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            @elseif($icon === 'x-circle')
                <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"/>
                </svg>
            @elseif($icon === 'ban')
                <svg class="w-7 h-7 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke-width="2"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 8l8 8"/>
                </svg>
            @endif
        </span>
    @endif
    <div>
        <div class="text-2xl font-bold text-gray-800">{{ $value }}</div>
        <div class="text-sm text-gray-600">{{ $label }}</div>
    </div>
</div>
