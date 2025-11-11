<template>
  <Head title="Services" />
  <Banner />
  <div
    class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16"
  >
    <Header />
    <div class="w-full md:w-5/6 py-12 space-y-16">
      <!-- Header Section -->
      

      <!-- Main Title -->
      <div class="flex items-center justify-between">
        <div class="flex items-center justify-center space-x-4">
          <Link href="/" class="back-button">
            <img src="/images/back-arrow.png" class="w-14 h-14" alt="Back" />
          </Link>
          <h1 class="page-title">
            Services
          </h1>
        </div>
      </div>

      <div class="mt-12 w-full flex justify-center">
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4
                 gap-y-12 gap-x-12 xl:gap-x-16 2xl:gap-x-20
                 max-w-[96rem] w-full">
          <!-- Photocopy Service -->
          <Link
            href="/services/photocopy"
            class="service-card group hover:from-blue-50 hover:to-indigo-50"
          >
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-indigo-600/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-3xl"></div>
            <div class="relative flex flex-col items-center justify-center space-y-6">
              <div class="service-icon from-blue-500 to-indigo-600 group-hover:scale-110">
                <i class="ri-file-copy-line text-white text-4xl"></i>
              </div>
              <h3 class="service-title group-hover:text-blue-600">
                Photocopy
              </h3>
              <div class="service-underline from-blue-500 to-indigo-600"></div>
            </div>
          </Link>

          <!-- Printout Service -->
          <Link
            href="/services/printout"
            class="service-card group hover:from-green-50 hover:to-emerald-50"
          >
            <div class="absolute inset-0 bg-gradient-to-br from-green-500/5 to-emerald-600/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-3xl"></div>
            <div class="relative flex flex-col items-center justify-center space-y-6">
              <div class="service-icon from-green-500 to-emerald-600 group-hover:scale-110">
                <i class="ri-printer-line text-white text-4xl"></i>
              </div>
              <h3 class="service-title group-hover:text-green-600">
                Printout
              </h3>
              <div class="service-underline from-green-500 to-emerald-600"></div>
            </div>
          </Link>

          <!-- Binding Service -->
          <Link
            href="/services/binding"
            class="service-card group hover:from-amber-50 hover:to-orange-50"
          >
            <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-orange-600/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-3xl"></div>
            <div class="relative flex flex-col items-center justify-center space-y-6">
              <div class="service-icon from-amber-500 to-orange-600 group-hover:scale-110">
                <i class="ri-bookmark-line text-white text-4xl"></i>
              </div>
              <h3 class="service-title group-hover:text-amber-600">
                Binding
              </h3>
              <div class="service-underline from-amber-500 to-orange-600"></div>
            </div>
          </Link>

          <!-- Laminating Service -->
          <Link
            href="/services/laminating"
            class="service-card group hover:from-red-50 hover:to-rose-50"
          >
            <div class="absolute inset-0 bg-gradient-to-br from-red-500/5 to-rose-600/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-3xl"></div>
            <div class="relative flex flex-col items-center justify-center space-y-6">
              <div class="service-icon from-red-500 to-rose-600 group-hover:scale-110">
                <i class="ri-file-shield-line text-white text-4xl"></i>
              </div>
              <h3 class="service-title group-hover:text-red-600">
                Laminating
              </h3>
              <div class="service-underline from-red-500 to-rose-600"></div>
            </div>
          </Link>
        </div>
      </div>
    </div>
  </div>
  <Footer />
</template>

<script setup>
import { ref } from "vue";
import { Head } from "@inertiajs/vue3";
import { Link, useForm, router } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import { HasRole } from "@/Utils/Permissions";

const props = defineProps({
  services: Object,
  totalServices: Number,
});

const search = ref("");

const performSearch = () => {
  router.get(
    route("services.index"),
    {
      search: search.value,
    },
    { preserveState: true }
  );
};
</script>

<style scoped>
/* Total Services Badge */
.total-services-badge {
  @apply flex items-center space-x-3 px-6 py-3 bg-white rounded-2xl shadow-lg border-2 border-gray-100;
}

.total-services-badge .count {
  @apply text-3xl font-bold text-blue-600;
}

.total-services-badge .label {
  @apply text-lg font-semibold text-gray-600;
}

/* Back Button */
.back-button {
  @apply transition-transform duration-300 hover:scale-110;
}

/* Page Title */
.page-title {
  @apply text-4xl font-bold tracking-wide text-black uppercase;
}

/* Service Card Base */
.service-card {
  @apply relative overflow-hidden bg-white p-8 rounded-3xl
         border-2 border-gray-200 shadow-xl hover:shadow-2xl transform-gpu
         transition-all duration-500 ease-out
         w-full
         min-h-[320px]
         hover:-translate-y-2 hover:scale-105
         bg-gradient-to-br;
  cursor: pointer;
}

/* Service Icon */
.service-icon {
  @apply w-32 h-32 rounded-full bg-gradient-to-br
         flex items-center justify-center shadow-2xl
         transition-all duration-500 ease-out
         ring-4 ring-white ring-opacity-50;
}

/* Service Title */
.service-title {
  @apply text-2xl font-bold text-gray-800 transition-colors duration-300;
}

/* Service Underline */
.service-underline {
  @apply h-1 w-16 bg-gradient-to-r rounded-full opacity-0 group-hover:opacity-100 transition-all duration-300;
}

/* Subtle glass highlight */
.service-card::before {
  content: '';
  @apply absolute inset-0 rounded-3xl;
  background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 100%);
  pointer-events: none;
  z-index: 1;
}

/* Shimmer effect on hover */
.service-card::after {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: linear-gradient(
    45deg,
    transparent 30%,
    rgba(255, 255, 255, 0.3) 50%,
    transparent 70%
  );
  transform: translateX(-100%) translateY(-100%) rotate(45deg);
  transition: transform 0.6s ease;
  pointer-events: none;
  z-index: 2;
}

.service-card:hover::after {
  transform: translateX(100%) translateY(100%) rotate(45deg);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .service-card {
    @apply min-h-[280px] p-6;
  }
  
  .service-icon {
    @apply w-28 h-28;
  }
  
  .service-title {
    @apply text-xl;
  }
  
  .page-title {
    @apply text-3xl;
  }
  
  .total-services-badge {
    @apply px-4 py-2;
  }
  
  .total-services-badge .count {
    @apply text-2xl;
  }
  
  .total-services-badge .label {
    @apply text-base;
  }
}

@media (max-width: 640px) {
  .grid-cols-2 {
    grid-template-columns: 1fr;
  }
  
  .service-card {
    @apply min-h-[260px] p-5;
  }
  
  .service-icon {
    @apply w-24 h-24;
  }
  
  .service-title {
    @apply text-lg;
  }
}
</style>