<template>
  <div class="min-h-screen bg-gray-100 py-8 px-16">
    <Header />
    <div class="w-full py-12">
    
        <BindingServiceModel />
      </div>
    
    <Footer />
  </div>
</template>

<script setup>
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import BindingServiceModel from "@/Components/custom/BindingServiceModel.vue";
</script>

<style scoped>
.container {
  max-width: 1280px;
  margin: 0 auto;
}
</style>

<style scoped>
/* Add any specific styles for the Printout Service Page here */
</style>