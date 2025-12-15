<template>
  <div class="min-h-screen bg-gray-100">
      
        <!-- Header -->
      <Navbar  />
       
      <!-- Header -->
      <Header  />

      <!-- About -->
      <About  />

      <!-- Map -->
      <Map/>

      <!-- Intro -->
      <Intro  />
        <!-- Header -->
      <Footer  />

  </div>
</template>

<script setup>
import Header from '@/views/layouts/Header.vue' 
import Map from '@/views/layouts/Map.vue'
import Intro from '@/views/layouts/Intro.vue'    
import About from '@/views/layouts/About.vue'   
import Navbar from '@/views/layouts/Navbar.vue'   
import Footer from '@/views/layouts/Footer.vue'   
</script>
