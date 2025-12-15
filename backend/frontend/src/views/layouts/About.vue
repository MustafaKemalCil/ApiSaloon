<script setup>
import MaviImage from '@/assets/images/mavi1.jpg'
import SideImage from '@/assets/images/Side1.jpg'
</script>

<template>
  <section class="px-4 py-12 bg-[#5E4B33]">
    <div class="container flex flex-col gap-6 mx-auto md:flex-row">

      <!-- Sol div -->
      <div class="flex flex-col w-full gap-0 md:flex-row md:w-8/12">
        <!-- Görsel -->
        <div class="flex items-center justify-center flex-1 w-5/12 p-4 rounded">
          <img 
            :src="MaviImage" 
            alt="Mavi Görsel" 
            class="object-cover w-full h-auto rounded" 
            style="border-radius:4rem;"
          />
        </div>

        <!-- Metin -->
        <div class="flex flex-col justify-center w-max items-left">
          <div class="flex-none h-[12.5%]"></div>
          <div class="flex flex-1 bg-[#E2CEC2] w-max px-12 text-start items-center">
            <div class="inline-block text-[#3C2F29] text-left">
              <p class="text-4xl tracking-wide font-normal leading-tight">SEBİHA ÇİL VİP</p>
              <p class="text-4xl tracking-wide font-normal leading-tight">ESTETİK</p>
              <p class="text-4xl tracking-wide font-normal leading-tight">KİMDİR ?</p>
            </div>
          </div>
          <div class="flex-none h-[12.5%]"></div>
        </div>
      </div>

      <!-- Sağ div -->
      <div class="flex items-center justify-center w-full p-6 order-last rounded md:w-4/12">
        <img 
          :src="SideImage" 
          alt="Side Görsel" 
          class="object-cover w-full h-auto"
        />
      </div>

    </div>
  </section>
</template>
