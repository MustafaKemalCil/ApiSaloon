<template>
  <nav class="text-gray-800 shadow-md bg-[#C1B16E]">
    <!-- Üst Bilgi Barı -->
    <div class="hidden md:flex justify-end items-center w-full bg-[#AA9A5A] text-gray-900 text-sm">
      <div class="container flex justify-end items-center px-6 py-2 mx-auto space-x-6">
        <!-- Telefon -->
        <a href="tel:+905551112233" class="flex items-center hover:text-gray-700">
          📞 <span class="ml-1 font-medium">+90 555 111 22 33</span>
        </a>
        <!-- WhatsApp -->
        <a href="https://wa.me/905551112233" target="_blank"
           class="flex items-center hover:text-gray-700 transition">
          💬 <span class="ml-1 font-medium">WhatsApp</span>
        </a>
        <!-- Instagram -->
        <a href="https://instagram.com" target="_blank"
           class="flex items-center hover:text-gray-700 transition">
          📸 <span class="ml-1 font-medium">Instagram</span>
        </a>
      </div>
    </div>

    <div class="container flex items-end justify-between p-4 mx-auto">
      <!-- Logo solda -->
      <a :href="routes.home" class="flex items-center gap-2 md:gap-3" id="logo">
        <img :src="logo" alt="Logo" class="h-16 w-auto md:h-20 lg:h-32 transition-all duration-300">
        <div class="hidden lg:flex flex-col leading-tight font-semibold text-gray-900 whitespace-nowrap transition-all duration-300
                    text-base lg:text-xl xl:text-2xl tracking-wide md:tracking-[0.25em] lg:tracking-[0.35em]">
          <span>SEBİHA ÇİL</span>
          <span>VİP</span>
          <span>ESTETİK</span>
        </div>
      </a>

      <!-- Linkler (Desktop) sağda -->
      <ul class="hidden md:flex items-end md:space-x-4 lg:space-x-5 mb-2 md:mb-3">
        <li v-for="link in links" :key="link.name">
          <a :href="link.href"
             class="text-[10px] sm:text-xs md:text-base lg:text-lg pl-2 md:pl-3 uppercase border-l border-gray-400 hover:text-gray-600 transition-all">
            {{ link.name }}
          </a>
        </li>
      </ul>

      <!-- Hamburger (Mobil) sağda -->
      <button @click="toggleMenu" aria-label="Toggle menu" class="block md:hidden focus:outline-none">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
             xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
      </button>
    </div>

    <!-- Mobil Menü -->
    <ul v-show="menuOpen" class="flex-col items-center justify-center w-full mt-2 bg-amber-100 md:hidden">
      <li v-for="link in links" :key="link.name + '-mobile'">
        <a :href="link.href" class="block w-full py-2 text-center uppercase border-b border-gray-300 hover:text-gray-600">
          {{ link.name }}
        </a>
      </li>
    </ul>
  </nav>
</template>

<script>
export default {
  name: "Navbar",
  data() {
    return {
      menuOpen: false,
      logo: '/storage/logo.png', // asset yolu
      links: [
        { name: 'Ana Sayfa', href: '/home' },
        { name: 'Hakkımızda', href: '/about' },
        { name: 'İletişim', href: '/contact' },
        { name: 'Giriş', href: '/dashboard' },
      ],
      routes: {
        home: '/home'
      }
    };
  },
  methods: {
    toggleMenu() {
      this.menuOpen = !this.menuOpen;
    },
    handleResize() {
      if (window.innerWidth >= 768) this.menuOpen = false;
    }
  },
  mounted() {
    window.addEventListener('resize', this.handleResize);
  },
  unmounted() {
    window.removeEventListener('resize', this.handleResize);
  }
};
</script>

<style scoped>
/* İstersen ekstra transition veya hover stilleri ekleyebilirsin */
</style>
