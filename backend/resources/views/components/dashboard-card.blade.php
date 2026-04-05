@props([
    'icon' => 'fa-info',
    'bg' => 'bg-gray-50',
    'text' => 'text-gray-700',
    'label' => '',
    'value' => 0
])

<div class="px-3 py-2.5 rounded-lg shadow-sm border border-gray-200 flex items-center gap-2.5 {{ $bg }}">
    <div class="text-base {{ $text }}">
        <i class="fa {{ $icon }}"></i>
    </div>
    <div class="min-w-0">
        <p class="text-[11px] text-gray-500 leading-tight truncate">{{ $label }}</p>
        <p class="text-sm font-bold {{ $text }} leading-snug">{{ $value }}</p>
    </div>
</div>
