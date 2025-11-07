@props(['icon' => 'fa-circle', 'label' => '', 'route' => '#', 'class' => ''])

@php
  $isActive = $route !== '#' && request()->routeIs($route);
@endphp

<li class="transition duration-150">
  <a 
    href="{{ $route !== '#' ? route($route) : '#' }}" 
    class="flex items-center px-4 py-2.5 space-x-3 group relative rounded-md
        {{ $isActive ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}
        {{ $class }}">
    <i class="fas {{ $icon }} text-green-400 w-5 text-center text-base"></i>
    <span x-show="sidebarOpen" class="text-[13px] font-medium tracking-wide">{{ $label }}</span>

    <!-- Tooltip ao recolher -->
    <span 
      x-show="!sidebarOpen" 
      x-transition 
      class="absolute left-16 bg-gray-800 text-gray-100 text-xs rounded-md px-2 py-1 hidden group-hover:block whitespace-nowrap z-50">
      {{ $label }}
    </span>
  </a>
</li>
