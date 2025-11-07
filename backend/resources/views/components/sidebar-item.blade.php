@props(['icon' => 'circle', 'label' => '', 'route' => '#', 'class' => ''])

<li class="hover:bg-gray-700 {{ request()->routeIs($route) ? 'bg-gray-700' : '' }} {{ $class }}">
  <a href="{{ $route !== '#' ? route($route) : '#' }}" class="flex items-center px-4 py-3 space-x-3">
    <i class="fas fa-{{ $icon }} text-green-400 w-5 text-center"></i>
    <span :class="sidebarOpen ? 'inline' : 'hidden'" class="text-sm font-medium">{{ $label }}</span>
  </a>
</li>
