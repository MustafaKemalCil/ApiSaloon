<section class="px-4 py-12 bg-[#5E4B33]">
  <div class="container flex flex-col gap-6 mx-auto md:flex-row">

    <!-- Sol div: 8/12 genişlik -->
    <div class="flex flex-col w-full gap-0 md:flex-row md:w-8/12">
      <!-- 1. alt div: görsel -->
      <div class="flex items-center justify-center flex-1 w-5/12 p-4 rounded ">
        <img src="{{ asset('storage/mavi1.jpg') }}"
        alt="Mavi Görsel"
        class="object-cover w-full h-auto rounded"
        style="border-radius:4rem;">
      </div>
      <!-- 2. alt div: metin -->
<div class="flex flex-col justify-center  w-max     items-left  ">
    <div class="flex-none h-[12.5%]  "> </div>

   <!-- Metin bloğu, dikeyde tamamı kaplayan flex -->
            <div class="flex flex-1 bg-[#E2CEC2]  w-max px-12  text-start items-center   ">
                <!-- Metin arka planı sadece yazı kadar yatay, dikeyde tam -->
                <div class="inline-block  text-[#3C2F29]  text-left ">
                    <p class=" text-4xl tracking-wide font-normal leading-tight">SEBİHA ÇİL VİP</p>
                    <p class="text-4xl tracking-wide font-normal leading-tight">ESTETİK</p>
                    <p class="text-4xl tracking-wide font-normal leading-tight">KİMDİR ?</p>
                </div>
            </div>


    <div class="flex-none h-[12.5%] "> </div>
</div>

    </div>

    <!-- Sağ div: 4/12 genişlik -->
    <div class="flex items-center justify-center w-full p-6 order-last rounded md:w-4/12">
      <img src="{{ asset('storage/Side1.jpg') }}" alt="Side Görsel" class="object-cover w-full h-auto ">
    </div>

  </div>
</section>
