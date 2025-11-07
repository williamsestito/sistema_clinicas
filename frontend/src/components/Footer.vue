<template>
  <footer id="contato" class="bg-emerald-50 text-emerald-900">
    <div class="max-w-6xl mx-auto px-6 py-12">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
        <div>
          <h3 class="font-serif font-bold text-lg">Clínica Duda</h3>
          <p class="text-sm mt-4 text-emerald-800">
            Cuidado especializado em podologia e estética, com dedicação e profissionalismo para o seu bem‑estar.
          </p>
        </div>

        <div>
          <h4 class="font-semibold mb-4">Contato</h4>
          <ul class="space-y-3 text-sm text-emerald-800">
            <li class="flex items-start gap-3">
              <span class="mt-1"><MapPin class="h-5 w-5 text-emerald-600" /></span>
              <span>Rua das Flores, 123 - Centro<br/>Joinville, SC - CEP 89201-000</span>
            </li>
            <li class="flex items-center gap-3">
              <Phone class="h-5 w-5 text-emerald-600" />
              <a href="tel:+5547999999999" class="hover:underline">(47) 99999-9999</a>
            </li>
            <li class="flex items-center gap-3">
              <Mail class="h-5 w-5 text-emerald-600" />
              <a href="mailto:contato@clinicaduda.com.br" class="hover:underline">contato@clinicaduda.com.br</a>
            </li>
          </ul>
        </div>

        <div>
          <h4 class="font-semibold mb-4">Redes Sociais</h4>
          <p class="text-sm text-emerald-800 mb-4">Siga-nos nas redes sociais para dicas de cuidados e novidades!</p>

          <div class="flex items-center gap-3">
            <a href="#" aria-label="Instagram" class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-emerald-100 text-emerald-700 hover:bg-emerald-200">
              <Instagram class="h-5 w-5" />
            </a>
            <a href="#" aria-label="Facebook" class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-emerald-100 text-emerald-700 hover:bg-emerald-200">
              <Facebook class="h-5 w-5" />
            </a>
            <a href="#" aria-label="Outras redes" class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-emerald-100 text-emerald-700 hover:bg-emerald-200">
              <User class="h-5 w-5" />
            </a>
          </div>
        </div>
      </div>

      <div class="border-t border-emerald-100 mt-8 pt-6">
        <div class="text-center text-xs text-emerald-700">© 2025 Clínica de Podologia e Estética Duda. Todos os direitos reservados.</div>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
import { MapPin, Phone, Mail, Instagram, Facebook, User } from 'lucide-vue-next'
</script>

<style scoped></style>