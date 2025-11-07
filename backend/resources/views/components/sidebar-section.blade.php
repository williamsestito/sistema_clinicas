@props(['title' => ''])

<div class="mt-4">
  <h3 class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 px-4 py-2 flex items-center justify-between">
    <span>{{ $title }}</span>
    <i class="fa fa-cog text-gray-500 text-xs"></i>
  </h3>
  <ul>
    {{ $slot }}
  </ul>
</div>
