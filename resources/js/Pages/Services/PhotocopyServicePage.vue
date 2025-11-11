<template>
  <div class="min-h-screen bg-gray-100 py-8 px-16">
    <Header />
    <div class="w-full py-12">
      <PhotocopyServiceModel />
    </div>
    <Footer />
  </div>
</template>

<script setup>
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import PhotocopyServiceModel from "@/Components/custom/PhotocopyServiceModel.vue";
</script>

<style scoped>
/* Add any specific styles for the Photocopy Service Page here */
</style>