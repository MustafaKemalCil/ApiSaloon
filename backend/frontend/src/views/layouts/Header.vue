<template>
  <!-- Ana Header Bölümü -->
  <section class="py-16 bg-[#DDDCD7]">
    <div class="container flex flex-col items-start gap-8 px-4 mx-auto md:flex-row md:px-0">

      <!-- Sol Div -->
      <div class="flex flex-col items-start justify-center gap-2 p-8 md:w-1/2">

        <!-- 1. Küçük yazı -->
        <div class="mb-4 text-xs text-left text-black sm:text-sm md:text-base lg:text-lg">
          S E B İ H A Ç İ L V İ P E S T E T İ K
        </div>

        <!-- 2. Çok büyük yazı: GÜZELLİĞİN -->
        <div class="text-3xl sm:text-5xl md:text-7xl lg:text-8xl font-normal bg-[#2B2B2B] text-white px-1 py-0 inline-block mt-4">
          GÜZELLİĞİN
        </div>

        <!-- 3. Büyük yazı: ADRESİ -->
        <div class="w-full px-3 py-0 mt-4 text-4xl font-normal text-left text-black rounded sm:text-5xl md:text-6xl lg:text-7xl">
          ADRESİ
        </div>

        <!-- 4. Orta yazı: Kampanyalar ve Fırsatlar -->
        <div class="w-full px-3 py-0 mt-4 mb-0 text-base font-medium text-left text-blue-500 rounded sm:text-lg md:text-xl">
          Kampanyalar ve Fırsatlar
        </div>

        <!-- 5. Orta yazı: VIP Üyelik... -->
        <div class="w-full px-3 py-2 mt-0 mb-4 text-base font-medium text-left text-blue-500 rounded sm:text-lg md:text-xl">
          VIP Üyelik Fırsatları
        </div>

        <!-- 6. Buton: Ayın Özel İndirimi -->
        <div class="inline-block px-6 py-4 mt-2 text-white bg-[#2B2B2B] cursor-pointer hover:bg-gray-800
                    text-sm sm:text-base md:text-2xl lg:text-4xl leading-tight font-semibold">
          Ayın Özel İndirimi
        </div>

      </div>

      <!-- Sağ Div -->
      <div class="md:w-1/2 h-[500px] md:h-[600px]">
        <div class="inline-block h-auto md:w-auto"
             :style="{
               backgroundImage: `url(${cerceveImage})`,
               backgroundSize: 'contain',
               backgroundRepeat: 'no-repeat',
               backgroundPosition: 'center',
               paddingTop: '28px',
               paddingLeft: '35px',
               paddingRight: '35px',
               paddingBottom: '0'
             }">

          <!-- GÖRSEL (%80) -->
          <div class="flex-[4] bg-[#C5A07D] overflow-hidden p-4">
            <img 
              :src="mainImage"
              alt="Estetik Görsel"
              class="object-cover w-full h-full shadow"
              style="border-radius:3rem;"
            >
          </div>

          <!-- AÇIKLAMA (%20) -->
          <div class="flex-[1] bg-[#1A1A1A] flex flex-col items-center justify-center text-center text-white p-4 gap-3 shadow">

            <!-- 📩 MAIL -->
            <a href="mailto:sebihacilvipesthetic@gmail.com"
               class="text-sm font-medium underline md:text-base hover:text-white">
              sebihacilvipesthetic@gmail.com
            </a>

            <!-- 🌐 Sosyal Medya -->
            <div class="flex justify-center gap-5">
              <a href="https://instagram.com" target="_blank" class="text-2xl transition-transform hover:scale-110">
                <i class="text-white fa-brands fa-instagram"></i>
              </a>
              <a href="https://facebook.com" target="_blank" class="text-2xl transition-transform hover:scale-110">
                <i class="text-white fa-brands fa-facebook"></i>
              </a>
              <a href="https://x.com" target="_blank" class="text-2xl transition-transform hover:scale-110">
                <i class="text-white fa-brands fa-x-twitter"></i>
              </a>
              <a href="https://tiktok.com" target="_blank" class="text-2xl transition-transform hover:scale-110">
                <i class="text-white fa-brands fa-tiktok"></i>
              </a>
            </div>

          </div>

        </div>
      </div>

    </div>
  </section>
</template>

<script setup>
import cerceveImage from '@/assets/images/Cerceve1.png'
import mainImage from '@/assets/images/freestocks-YGmk9UZMdZg-unsplash.jpg'
</script>

<style scoped>
/* Font Awesome için index.html veya main.js’de CDN yüklemelisin:
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
*/
</style>
