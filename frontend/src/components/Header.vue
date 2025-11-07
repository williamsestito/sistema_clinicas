<template>
  <header class="bg-white/0 backdrop-blur-md fixed top-0 left-0 w-full z-30">
    <div class="max-w-6xl mx-auto px-6 py-4 flex items-center">
      <!-- left: logo (sempre visível - espaço reservado) -->
      <div class="flex items-center flex-shrink-0 w-36">
        <a href="#top" @click.prevent="scrollToSection('top')" class="inline-flex items-center">
          <img src="/logo-192.svg" alt="Logo" class="h-12 w-auto" />
        </a>
      </div>

      <!-- center: main navigation (centered) -->
      <nav class="hidden md:flex items-center gap-8 text-sm flex-1 justify-center">
        <a href="#top" class="hover:text-emerald-600" @click.prevent="scrollToSection('top')">Início</a>
        <a href="#sobre" class="hover:text-emerald-600" @click.prevent="scrollToSection('sobre')">Sobre</a>
        <a href="#servicos" class="hover:text-emerald-600" @click.prevent="scrollToSection('servicos')">Serviços</a>
        <a href="#profissionais" class="hover:text-emerald-600" @click.prevent="scrollToSection('profissionais')">Profissionais</a>
        <a href="#agendamento" class="hover:text-emerald-600" @click.prevent="scrollToSection('agendamento')">Agendamento</a>
        <a href="#contato" class="hover:text-emerald-600" @click.prevent="scrollToSection('contato')">Contato</a>
        <a href="http://localhost:8080/login" class="hover:text-emerald-600" target="_blank" rel="noopener noreferrer">Entrar</a>
      </nav>

      <!-- right: actions (WhatsApp) -->
      <!-- adicionada margem à esquerda para afastar do item "Entrar" -->
      <div class="hidden md:flex items-center gap-4 flex-shrink-0 ml-6">
        <a href="https://wa.me/5547999999999" target="_blank" rel="noopener" class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-2 rounded-full">
          <Phone class="h-4 w-4 text-white" />
          <span class="hidden sm:inline">WhatsApp</span>
        </a>
      </div>

      <!-- mobile: menu button (à direita) -->
      <div class="ml-auto md:hidden flex items-center">
        <button class="p-2" @click="toggleMobile = !toggleMobile" aria-label="Abrir menu">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>
    </div>

    <!-- mobile dropdown; respeita espaço da logo com padding-left -->
    <transition name="fade">
      <div v-if="toggleMobile" class="md:hidden bg-white/95 backdrop-blur-sm border-t">
        <div class="px-4 py-3 flex flex-col gap-2 pl-36"> <!-- pl-36 reserva espaço para a logo -->
          <a href="#top" @click.prevent="mobileNavigate('top')" class="py-2">Início</a>
          <a href="#sobre" @click.prevent="mobileNavigate('sobre')" class="py-2">Sobre</a>
          <a href="#servicos" @click.prevent="mobileNavigate('servicos')" class="py-2">Serviços</a>
          <a href="#profissionais" @click.prevent="mobileNavigate('profissionais')" class="py-2">Profissionais</a>
          <a href="#agendamento" @click.prevent="mobileNavigate('agendamento')" class="py-2">Agendamento</a>
          <a href="#contato" @click.prevent="mobileNavigate('contato')" class="py-2">Contato</a>
          <a href="http://localhost:8080/login" class="py-2 text-emerald-600">Entrar</a>
        </div>
      </div>
    </transition>
  </header>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Phone } from 'lucide-vue-next'

const toggleMobile = ref(false)

function scrollToSection(id: string) {
  toggleMobile.value = false

  if (!id || id === 'top') {
    window.scrollTo({ top: 0, behavior: 'smooth' })
    return
  }

  const el = document.getElementById(id)
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'start' })
  } else {
    window.location.href = `/#${id}`
  }
}

function mobileNavigate(id: string) {
  scrollToSection(id)
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity .15s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* garante que o espaço reservado para a logo não seja comprimido em telas pequenas */
@media (max-width: 767px) {
  header .w-36 { min-width: 9rem; }
}
</style>