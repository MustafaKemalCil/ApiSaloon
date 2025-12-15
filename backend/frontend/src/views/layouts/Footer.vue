<template>
  <section class="bg-gray-900 text-gray-200 py-12">
    <div class="container mx-auto px-4 flex flex-col md:flex-row justify-between gap-8">

      <!-- Hakkımızda kısa açıklama -->
      <div class="md:w-1/3">
        <h3 class="text-xl font-bold mb-4">Sebiha Çil VIP Estetik</h3>
        <p class="text-gray-400 text-sm leading-relaxed">
          Güzellik ve bakımda mükemmeliyetin adı. Mezitli, Soli ve Kosova Prizren'deki şubelerimizle sizlere en iyi hizmeti sunuyoruz.
        </p>
      </div>

      <!-- İletişim Bilgileri -->
      <div class="md:w-1/3">
        <h3 class="text-xl font-bold mb-4">İletişim</h3>
        <ul class="text-gray-400 text-sm space-y-2">
          <li>Adres: Kervan Official, Merkez, 52136. Sk. Kat:3 Daire:14, 33330 Mezitli/Mersin</li>
          <li>Telefon: <a href="tel:+905XXXXXXXXX" class="hover:text-white">+90 5XX XXX XX XX</a></li>
          <li>Email: <a href="mailto:info@sebihacilvip.com" class="hover:text-white">info@sebihacilvip.com</a></li>
        </ul>
      </div>

      <!-- Sosyal Medya -->
      <div class="md:w-1/3">
        <h3 class="text-xl font-bold mb-4">Sosyal Medya</h3>
        <div class="flex space-x-4">
          <a href="#" class="hover:text-white"><i class="fab fa-facebook-f"></i> Facebook</a>
          <a href="#" class="hover:text-white"><i class="fab fa-instagram"></i> Instagram</a>
          <a href="#" class="hover:text-white"><i class="fab fa-twitter"></i> Twitter</a>
        </div>
      </div>

    </div>

    <div class="border-t border-gray-700 mt-8 pt-4 text-center text-gray-500 text-sm">
      &copy; {{ currentYear }} Sebiha Çil VIP Estetik. Tüm hakları saklıdır.
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue';

// Dinamik yıl
const currentYear = ref(new Date().getFullYear());
</script>

<style scoped>
/* Eğer font-awesome kullanacaksan, index.html veya main.js’de yükle: 
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
*/
</style>
