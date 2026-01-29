@props(['padding' => 'p-5'])

<div {{ $attributes->merge(['class' => "bg-white border border-gray-200 rounded-2xl shadow-md {$padding}"]) }}>
    {{ $slot }}
</div>
