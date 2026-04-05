<script setup lang="ts">
import { RouterView } from 'vue-router'
import Header from './components/Header.vue'
import Footer from './components/Footer.vue'
</script>

<template>
  <div class="min-h-screen flex flex-col bg-white text-emerald-900">
    <Header />

    <main class="flex-1">
      <RouterView />
    </main>

    <Footer class="mt-auto" />
  </div>
</template>

<style scoped></style>
