<template>
  <Head title="Mobitel Top-Up" />
  <Banner />

  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-24 px-16">
    <Header />

    <div class="w-full md:w-5/6 py-12 space-y-16 md:px-0 px-6 mx-auto">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center justify-center space-x-4">
          <Link href="/sim-reload">
            <img src="/images/back-arrow.png" class="w-14 h-14" alt="Back" />
          </Link>
          <p class="text-4xl font-bold tracking-wide text-black uppercase">
            Mobitel
          </p>
        </div>
      </div>

    </div>
  </div>

  <Footer />
</template>

<script setup>
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import { Link } from "@inertiajs/vue3";
import { Head } from "@inertiajs/vue3";
</script>
