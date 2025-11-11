<template>
  <Head title="Dashboard" />
  <div
    class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gradient-to-br from-gray-50 to-gray-100 md:px-36 px-16"
  >
    <!-- Include the Header -->
    <Header />

    <div class="grid w-full h-full md:grid-cols-4 grid-cols-2 gap-6 auto-rows-fr">
      <Link href="/pos" v-if="HasRole(['Admin', 'Cashier'])">
        <div class="dashboard-card group bg-gradient-to-br from-lime-600 to-lime-700 hover:from-lime-500 hover:to-lime-600">
          <div class="card-content">
            <div class="icon-container">
              <img
                src="/images/dashboard/checkout.png"
                class="icon"
                alt="POS"
              />
            </div>
            <div class="text-container">
              <p class="title">POS</p>
              <p class="description">
                Quick billing and payment processing
              </p>
            </div>
          </div>
          <div class="card-glow"></div>
        </div>
      </Link>

      <Link href="/sim-reload" v-if="HasRole(['Admin', 'Cashier'])">
        <div class="dashboard-card group bg-gradient-to-br from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500">
          <div class="card-content">
            <div class="icon-container">
              <img
                src="/images/dashboard/cashier.png"
                class="icon"
                alt="SIM Reload"
              />
            </div>
            <div class="text-container">
              <p class="title">SIM & Reload</p>
              <p class="description">
                Mobile reloads and SIM activations
              </p>
            </div>
          </div>
          <div class="card-glow"></div>
        </div>
      </Link>

      <Link href="/categories" v-if="HasRole(['Admin', 'Manager'])">
        <div class="dashboard-card group bg-gradient-to-br from-blue-800 to-blue-900 hover:from-blue-700 hover:to-blue-800">
          <div class="card-content">
            <div class="icon-container">
              <img
                src="/images/dashboard/app.png"
                class="icon"
                alt="Categories"
              />
            </div>
            <div class="text-container">
              <p class="title">Categories</p>
              <p class="description">
                Organize products efficiently
              </p>
            </div>
          </div>
          <div class="card-glow"></div>
        </div>
      </Link>

      <Link href="/products" v-if="HasRole(['Admin', 'Manager'])">
        <div class="dashboard-card group bg-gradient-to-br from-red-600 to-red-700 hover:from-red-500 hover:to-red-600">
          <div class="card-content">
            <div class="icon-container">
              <img
                src="/images/dashboard/products.png"
                class="icon"
                alt="Products"
              />
            </div>
            <div class="text-container">
              <p class="title">Products</p>
              <p class="description">
                Manage inventory and pricing
              </p>
            </div>
          </div>
          <div class="card-glow"></div>
        </div>
      </Link>

      <Link href="/newspapers" v-if="HasRole(['Admin', 'Manager'])">
        <div class="dashboard-card group bg-gradient-to-br from-teal-600 to-teal-700 hover:from-teal-500 hover:to-teal-600">
          <div class="card-content">
            <div class="icon-container">
              <img
                src="/images/dashboard/newspapers.png"
                class="icon"
                alt="Newspapers"
              />
            </div>
            <div class="text-container">
              <p class="title">Newspapers</p>
              <p class="description">
                Manage newspaper inventory
              </p>
            </div>
          </div>
          <div class="card-glow"></div>
        </div>
      </Link>

       <Link href="/services" v-if="HasRole(['Admin', 'Manager'])">
        <div class="dashboard-card group bg-gradient-to-br from-pink-400 to-pink-500 hover:from-pink-300 hover:to-pink-400">
          <div class="card-content">
            <div class="icon-container">
              <img
                src="/images/dashboard/printout.png"
                class="icon"
                alt="Services"
              />
            </div>
            <div class="text-container">
              <p class="title">Services</p>
              <p class="description">
                Print, laminating & binding services
              </p>
            </div>
          </div>
          <div class="card-glow"></div>
        </div>
      </Link>

      <Link href="/suppliers" v-if="HasRole(['Admin', 'Manager'])">
        <div class="dashboard-card group bg-gradient-to-br from-amber-700 to-amber-800 hover:from-amber-600 hover:to-amber-700">
          <div class="card-content">
            <div class="icon-container">
              <img
                src="/images/dashboard/team-building.png"
                class="icon"
                alt="Suppliers"
              />
            </div>
            <div class="text-container">
              <p class="title">Suppliers</p>
              <p class="description">
                Manage supplier and purchase orders
              </p>
            </div>
          </div>
          <div class="card-glow"></div>
        </div>
      </Link>

      <Link href="/customers" v-if="HasRole(['Admin', 'Manager'])">
        <div class="dashboard-card group bg-gradient-to-br from-purple-400 to-purple-500 hover:from-purple-300 hover:to-purple-400">
          <div class="card-content">
            <div class="icon-container">
              <img
                src="/images/dashboard/customers.png"
                class="icon"
                alt="Customers"
              />
            </div>
            <div class="text-container">
              <p class="title">Customers</p>
              <p class="description">
                Customer profiles and loyalty programs
              </p>
            </div>
          </div>
          <div class="card-glow"></div>
        </div>
      </Link>

      <Link href="/reports" v-if="HasRole(['Admin'])">
        <div class="dashboard-card group bg-gradient-to-br from-orange-500 to-orange-600 hover:from-orange-400 hover:to-orange-500">
          <div class="card-content">
            <div class="icon-container">
              <img
                src="/images/dashboard/report.png"
                class="icon"
                alt="Reports"
              />
            </div>
            <div class="text-container">
              <p class="title">Reports</p>
              <p class="description">
                Sales and inventory insights
              </p>
            </div>
          </div>
          <div class="card-glow"></div>
        </div>
      </Link>

      <Link href="/colors" v-if="HasRole(['Admin', 'Manager'])">
        <div class="dashboard-card group bg-gradient-to-br from-gray-600 to-gray-700 hover:from-gray-500 hover:to-gray-600">
          <div class="card-content">
            <div class="icon-container">
              <img
                src="/images/dashboard/color-wheel.png"
                class="icon"
                alt="Colors"
              />
            </div>
            <div class="text-container">
              <p class="title">Colors</p>
              <p class="description">
                Product color variations
              </p>
            </div>
          </div>
          <div class="card-glow"></div>
        </div>
      </Link>

      <Link href="/sizes" v-if="HasRole(['Admin', 'Manager'])">
        <div class="dashboard-card group bg-gradient-to-br from-cyan-400 to-cyan-500 hover:from-cyan-300 hover:to-cyan-400">
          <div class="card-content">
            <div class="icon-container">
              <img
                src="/images/dashboard/measuring-tape.png"
                class="icon"
                alt="Sizes"
              />
            </div>
            <div class="text-container">
              <p class="title">Sizes</p>
              <p class="description">
                Manage product size options
              </p>
            </div>
          </div>
          <div class="card-glow"></div>
        </div>
      </Link>

      <Link href="/stock-transition" v-if="HasRole(['Admin'])">
        <div class="dashboard-card group bg-gradient-to-br from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600">
          <div class="card-content">
            <div class="icon-container">
              <img
                src="/images/dashboard/stock_transtion.png"
                class="icon"
                alt="Stock Transition"
              />
            </div>
            <div class="text-container">
              <p class="title">Stock Transition</p>
              <p class="description">
                Track stock changes and returns
              </p>
            </div>
          </div>
          <div class="card-glow"></div>
        </div>
      </Link>


      <Link href="/coupons" v-if="HasRole(['Admin'])">
        <div class="dashboard-card group bg-gradient-to-br from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600">
          <div class="card-content">
            <div class="icon-container">
              <img
                src="/images/dashboard/coupons.png"
                class="icon"
                alt="Coupons"
              />
            </div>
            <div class="text-container">
              <p class="title">Coupons</p>
              <p class="description">
                Manage discount coupons
              </p>
            </div>
          </div>
          <div class="card-glow"></div>
        </div>
      </Link>




      <Link href="/company-info" v-if="HasRole(['Admin'])">
        <div class="dashboard-card group bg-gradient-to-br from-green-500 to-green-600 hover:from-green-400 hover:to-green-500">
          <div class="card-content">
            <div class="icon-container">
              <img
                src="/images/dashboard/building.png"
                class="icon"
                alt="Company Info"
              />
            </div>
            <div class="text-container">
              <p class="title">Company Info</p>
              <p class="description">
                Business details and branding
              </p>
            </div>
          </div>
          <div class="card-glow"></div>
        </div>
      </Link>


      <Link href="/employees" v-if="HasRole(['Admin'])">
        <div class="dashboard-card group bg-gradient-to-br from-pink-600 to-pink-700 hover:from-pink-500 hover:to-pink-600">
          <div class="card-content">
            <div class="icon-container">
              <img
                src="/images/dashboard/employee.png"
                class="icon"
                alt="Employees"
              />
            </div>
            <div class="text-container">
              <p class="title">Employees</p>
              <p class="description">
                Staff roles and permissions
              </p>
            </div>
          </div>
          <div class="card-glow"></div>
        </div>
      </Link>

      <Link href="/transactionHistory" v-if="HasRole(['Admin'])">
        <div class="dashboard-card group bg-gradient-to-br from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400">
          <div class="card-content">
            <div class="icon-container">
              <img
                src="/images/dashboard/history.png"
                class="icon"
                alt="Order History"
              />
            </div>
            <div class="text-container">
              <p class="title">Order History</p>
              <p class="description">
                View and track past orders
              </p>
            </div>
          </div>
          <div class="card-glow"></div>
        </div>
      </Link>

      <Link href="/manualpos" v-if="HasRole(['Admin', 'Cashier'])">
        <div class="dashboard-card group bg-gradient-to-br from-teal-500 to-teal-600 hover:from-teal-400 hover:to-teal-500">
          <div class="card-content">
            <div class="icon-container">
              <img
                src="/images/dashboard/checkout.png"
                class="icon"
                alt="Manual POS"
              />
            </div>
            <div class="text-container">
              <p class="title">Manual POS</p>
              <p class="description">
                Quick manual billing
              </p>
            </div>
          </div>
          <div class="card-glow"></div>
        </div>
      </Link>



      <!-- <Link href="/return-bill" v-if="HasRole(['Admin'])">
        <div class="dashboard-card group bg-gradient-to-br from-purple-600 to-purple-700 hover:from-purple-500 hover:to-purple-600">
          <div class="card-content">
            <div class="icon-container">
              <img
                src="/images/dashboard/return-bill.png"
                class="icon"
                alt="Return Bill"
              />
            </div>
            <div class="text-container">
              <p class="title">Return Bill</p>
              <p class="description">
                Manage product returns
              </p>
            </div>
          </div>
          <div class="card-glow"></div>
        </div>
      </Link> -->








      <Link href="/quotation" v-if="HasRole(['Admin'])">
        <div class="dashboard-card group bg-gradient-to-br from-sky-700 to-sky-800 hover:from-sky-600 hover:to-sky-700">
          <div class="card-content">
            <div class="icon-container">
              <img
                src="/images/dashboard/quote.png"
                class="icon"
                alt="Quotations"
              />
            </div>
            <div class="text-container">
              <p class="title">Quotations</p>
              <p class="description">
                Create and manage quotes
              </p>
            </div>
          </div>
          <div class="card-glow"></div>
        </div>
      </Link>

    </div>
  </div>

  <!-- Include the Footer -->
  <Footer />
</template>

<script setup>
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import { Link } from "@inertiajs/vue3";
import { Head } from "@inertiajs/vue3";
import { HasRole } from "@/Utils/Permissions";
</script>

<style scoped>
.dashboard-card {
  @apply relative py-6 px-4 text-center rounded-2xl shadow-xl transform transition-all duration-300 hover:scale-105 hover:shadow-2xl h-full flex items-center justify-center overflow-hidden;
}

.card-glow {
  @apply absolute inset-0 bg-gradient-to-t from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none;
}

.card-content {
  @apply relative z-10 flex flex-col items-center justify-center space-y-4;
}

.icon-container {
  @apply flex flex-col items-center justify-center p-6 bg-white/95 backdrop-blur-sm rounded-2xl shadow-lg group-hover:scale-110 transition-transform duration-300;
}

.icon {
  @apply w-20 h-20 md:w-24 md:h-24;
}

.text-container {
  @apply flex flex-col items-center justify-center w-full px-3 space-y-1;
}

.title {
  @apply text-xl md:text-2xl font-bold text-white uppercase tracking-wide;
}

.description {
  @apply text-sm md:text-base text-white/90 leading-snug;
}
</style>
