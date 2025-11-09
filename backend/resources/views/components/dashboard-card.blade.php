@props(['icon', 'label', 'value' => 0, 'color' => 'bg-gray-100 text-gray-800'])

<div class="p-4 rounded-lg shadow-sm {{ $color }}">
  <div class="flex items-center space-x-3">
    <i class="fa {{ $icon }} text-lg"></i>
    <div>
      <p class="text-xs uppercase tracking-wide font-semibold">{{ $label }}</p>
      <p class="text-xl font-bold">{{ $value }}</p>
    </div>
  </div>
</div>
