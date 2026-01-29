@props([
  'variant' => 'primary', // primary | soft | outline | danger
  'type' => 'button'
])

@php
  $base = "inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full font-bold transition";
  $variants = [
    'primary' => "bg-[#F5E7DA] text-black hover:opacity-90",
    'soft'    => "bg-gray-900 text-white hover:opacity-90",
    'outline' => "border border-gray-300 text-gray-800 hover:bg-gray-50",
    'danger'  => "bg-red-600 text-white hover:bg-red-700",
  ];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $base.' '.$variants[$variant]]) }}>
    {{ $slot }}
</button>
