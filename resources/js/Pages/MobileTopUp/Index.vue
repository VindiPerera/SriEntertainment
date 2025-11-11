<template>
  <Head title="Mobile Top-Up Services" />
  <Banner />

  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-24 px-16">
    <Header />

    <div class="w-full md:w-5/6 py-12 space-y-16 md:px-0 px-6 mx-auto">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center justify-center space-x-4">
          <Link href="/">
            <img src="/images/back-arrow.png" class="w-14 h-14" alt="Back" />
          </Link>
          <p class="text-4xl font-bold tracking-wide text-black uppercase">
            Mobile Top-Up Services
          </p>
        </div>
      </div>

      <!-- Service Cards Grid -->
      <div class="mt-12 w-full flex justify-center">
        <div
          class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4
                 gap-y-12 gap-x-12 xl:gap-x-16 2xl:gap-x-20
                 max-w-[96rem] w-full"
        >
          <!-- Manage Wallet Card -->
          <button
            @click="navigateTo('manage-wallet')"
            aria-label="Manage Wallet"
            class="card-base group hover:from-green-50 hover:to-emerald-50"
          >
            <div class="absolute inset-0 bg-gradient-to-br from-green-500/5 to-emerald-600/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-3xl"></div>
            <div class="relative flex flex-col items-center justify-center space-y-6">
              <div class="icon-ring from-green-500 to-emerald-600 group-hover:scale-110">
                <svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
              </div>
              <h3 class="text-3xl font-bold text-gray-800 group-hover:text-green-600 transition-colors duration-300 text-center">
                Manage Wallet
              </h3>
              <div class="h-1 w-20 bg-gradient-to-r from-green-500 to-emerald-600 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-300"></div>
            </div>
          </button>

          <!-- Manage SIM Activation Packages Card -->
          <button
            @click="navigateTo('sim-activation-packages')"
            aria-label="Manage SIM Activation Packages"
            class="card-base group hover:from-blue-50 hover:to-indigo-50"
          >
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-indigo-600/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-3xl"></div>
            <div class="relative flex flex-col items-center justify-center space-y-6">
              <div class="icon-ring from-blue-500 to-indigo-600 group-hover:scale-110">
                <svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                </svg>
              </div>
              <h3 class="text-3xl font-bold text-gray-800 group-hover:text-blue-600 transition-colors duration-300 text-center">
                SIM Activation Packages
              </h3>
              <div class="h-1 w-20 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-300"></div>
            </div>
          </button>

          <!-- Normal Packages Card -->
          <button
            @click="navigateTo('normal-packages')"
            aria-label="Normal Packages"
            class="card-base group hover:from-purple-50 hover:to-pink-50"
          >
            <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-pink-600/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-3xl"></div>
            <div class="relative flex flex-col items-center justify-center space-y-6">
              <div class="icon-ring from-purple-500 to-pink-600 group-hover:scale-110">
                <svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
              </div>
              <h3 class="text-3xl font-bold text-gray-800 group-hover:text-purple-600 transition-colors duration-300 text-center">
                Normal Packages
              </h3>
              <div class="h-1 w-20 bg-gradient-to-r from-purple-500 to-pink-600 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-300"></div>
            </div>
          </button>

          <!-- SIM and Reload Page Card -->
          <button
            @click="navigateTo('sim-reload')"
            aria-label="SIM and Reload"
            class="card-base group hover:from-orange-50 hover:to-red-50"
          >
            <div class="absolute inset-0 bg-gradient-to-br from-orange-500/5 to-red-600/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-3xl"></div>
            <div class="relative flex flex-col items-center justify-center space-y-6">
              <div class="icon-ring from-orange-500 to-red-600 group-hover:scale-110">
                <svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
              </div>
              <h3 class="text-3xl font-bold text-gray-800 group-hover:text-orange-600 transition-colors duration-300 text-center">
                SIM and Reload
              </h3>
              <div class="h-1 w-20 bg-gradient-to-r from-orange-500 to-red-600 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-300"></div>
            </div>
          </button>
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
import { Link, Head, router } from "@inertiajs/vue3";

const navigateTo = (page) => {
  const routes = {
    'manage-wallet': '/mobile-topup/manage-wallet',
    'sim-activation-packages': '/mobile-topup/sim-activation-packages',
    'normal-packages': '/mobile-topup/normal-packages',
    'sim-reload': '/sim-reload'
  };
  
  router.visit(routes[page]);
};
</script>

<style scoped>
/* Shared card base — fixed width so gaps are always visible */
.card-base {
  @apply relative overflow-hidden bg-white p-10 rounded-3xl
         border-2 border-gray-200 shadow-xl hover:shadow-2xl transform-gpu
         transition-all duration-500 ease-out
         mx-auto
         w-[20rem] md:w-[21rem] xl:w-[22rem] 2xl:w-[23rem]
         min-h-[380px]
         hover:-translate-y-2 hover:scale-105
         bg-gradient-to-br;
  cursor: pointer;
}

/* Icon badge */
.icon-ring {
  @apply w-40 h-40 rounded-full bg-gradient-to-br
         flex items-center justify-center shadow-2xl
         transition-all duration-500 ease-out
         ring-4 ring-white ring-opacity-50;
}

/* Subtle glass highlight */
.card-base::before {
  content: '';
  @apply absolute inset-0 rounded-3xl;
  background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 100%);
  pointer-events: none;
  z-index: 1;
}

/* Shimmer effect on hover */
.card-base::after {
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

.card-base:hover::after {
  transform: translateX(100%) translateY(100%) rotate(45deg);
}
</style>
