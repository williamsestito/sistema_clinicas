@props([
    'icon' => 'fa-info',
    'bg' => 'bg-gray-50',
    'text' => 'text-gray-700',
    'label' => '',
    'value' => 0
])

<div class="p-4 rounded-xl shadow-sm border border-gray-200 flex items-center gap-3 {{ $bg }}">
    <div class="text-xl {{ $text }}">
        <i class="fa {{ $icon }}"></i>
    </div>
    <div>
        <p class="text-xs text-gray-600">{{ $label }}</p>
        <p class="text-lg font-semibold {{ $text }}">{{ $value }}</p>
    </div>
</div>
