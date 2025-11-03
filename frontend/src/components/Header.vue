<template>
  <header class="bg-white/0 backdrop-blur-md fixed top-0 left-0 w-full z-30">
  <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
      <!-- left: logo + name -->
      <div class="flex items-center">
          <div class="absolute left-6 top-1/2 -translate-y-1/2 flex items-center gap-3">
              <img src="/logo-192.svg" alt="Logo" class="h-12 w-auto" />
          </div>
      </div>

      <!-- center: main navigation (centered) -->
      <nav class="hidden md:flex items-center gap-8 text-sm flex-1 justify-center">
        <a href="#top" class="hover:text-emerald-600">Início</a>
        <a href="#sobre" class="hover:text-emerald-600">Sobre</a>
        <a href="#servicos" class="hover:text-emerald-600">Serviços</a>
        <a href="#profissionais" class="hover:text-emerald-600">Profissionais</a>
        <a href="#agendamento" class="hover:text-emerald-600">Agendamento</a>
        <a href="#contato" class="hover:text-emerald-600">Contato</a>
        <a href="#login" class="hover:text-emerald-600">Entrar</a>
      </nav>

      <!-- right: actions (WhatsApp only) positioned absolute on the right edge -->
      <div class="absolute right-6 top-1/2 -translate-y-1/2 hidden md:flex items-center gap-4">
        <a href="https://wa.me/5547999999999" target="_blank" rel="noopener" class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-2 rounded-full">
          <Phone class="h-4 w-4 text-white" />
          <span class="hidden sm:inline">WhatsApp</span>
        </a>
      </div>

      <!-- mobile menu button -->
      <button class="md:hidden p-2 mr-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </div>
  </header>
</template>

<script setup lang="ts">
import { Phone } from 'lucide-vue-next'
</script>

<style scoped>
header { box-shadow: 0 1px 0 rgba(0,0,0,0.04); }
</style>