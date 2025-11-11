<template>
  <Head title="Manage SIM Cards" />
  <Banner />

  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-24 px-16">
    <Header />

    <div class="w-full md:w-5/6 py-8 space-y-8 md:px-0 px-6 mx-auto">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center justify-center space-x-4">
          <Link href="/sim-reload">
            <img src="/images/back-arrow.png" class="w-14 h-14" alt="Back" />
          </Link>
          <p class="text-4xl font-bold tracking-wide text-black uppercase">
            Manage SIM Cards
          </p>
        </div>
        <button
          @click="openAddModal"
          class="px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300"
        >
          <svg class="w-6 h-6 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Add New SIM
        </button>
      </div>

      <!-- SIM Cards Grid -->
      <div class="mt-8 w-full">
        <div v-if="simStocks.length > 0" 
          class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6
                 gap-4 max-w-full w-full"
        >
          <!-- SIM Card -->
          <div
            v-for="sim in simStocks"
            :key="sim.id"
            :class="getCarrierCardClass(sim.sim_name)"
            class="sim-card group relative overflow-hidden bg-white rounded-2xl border-2 shadow-lg hover:shadow-xl transform-gpu transition-all duration-300 hover:-translate-y-2 hover:scale-105"
          >
            <!-- Background Gradient Overlay -->
            <div :class="getCarrierGradientClass(sim.sim_name)" 
              class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            
            <!-- Decorative Corner Shape -->
            <div :class="getCarrierAccentClass(sim.sim_name)" 
              class="absolute top-0 right-0 w-20 h-20 rounded-bl-full opacity-10"></div>

            <!-- Card Content -->
            <div class="relative z-10 p-4 space-y-3">
              <!-- Carrier Badge with Icon -->
              <div class="flex justify-between items-start">
                <div :class="getCarrierBadgeClass(sim.sim_name)" 
                  class="px-3 py-1 rounded-full text-xs font-bold shadow-md transform group-hover:scale-110 transition-transform duration-300">
                  {{ sim.sim_name }}
                </div>
                <!-- SIM Card Icon -->
                <div class="p-1.5 bg-gray-100 rounded-lg group-hover:bg-white transition-colors duration-300">
                  <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                  </svg>
                </div>
              </div>

              <!-- Batch Number with Enhanced Design -->
              <div class="text-center bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-2 shadow-inner">
                <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold mb-0.5">Batch</p>
                <p class="text-sm font-black text-gray-800 tracking-tight">{{ sim.batch_number }}</p>
              </div>

              <!-- Stock Display with Circular Progress Look -->
              <div class="flex justify-center py-1">
                <div class="relative">
                  <div :class="sim.stock < 10 ? 'from-red-500 to-red-600' : 'from-green-500 to-green-600'" 
                    class="bg-gradient-to-br px-4 py-3 rounded-xl shadow-md transform group-hover:scale-105 transition-transform duration-300">
                    <p class="text-[10px] text-white uppercase tracking-wider font-bold mb-0.5">Stock</p>
                    <p class="text-2xl font-black text-white">{{ sim.stock }}</p>
                  </div>
                  <!-- Low Stock Warning Badge -->
                  <div v-if="sim.stock < 10" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full shadow-lg animate-pulse">
                    Low!
                  </div>
                </div>
              </div>

              <!-- Prices with Modern Layout -->
              <div class="grid grid-cols-2 gap-2">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-2 rounded-lg border border-blue-200 hover:shadow-md transition-shadow duration-300">
                  <div class="flex items-center space-x-1 mb-0.5">
                    <svg class="w-2.5 h-2.5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-[10px] text-blue-700 uppercase font-bold tracking-wide">Cost</p>
                  </div>
                  <p class="text-sm font-black text-blue-800">{{ sim.cost_price }}</p>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 p-2 rounded-lg border border-green-200 hover:shadow-md transition-shadow duration-300">
                  <div class="flex items-center space-x-1 mb-0.5">
                    <svg class="w-2.5 h-2.5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-[10px] text-green-700 uppercase font-bold tracking-wide">Sell</p>
                  </div>
                  <p class="text-sm font-black text-green-800">{{ sim.selling_price }}</p>
                </div>
              </div>

              <!-- Purchase Date with Calendar Icon -->
              <div class="flex items-center justify-center space-x-1 pt-1 pb-2">
                <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <div class="text-center">
                  <p class="text-[10px] text-gray-500 font-semibold">{{ formatDate(sim.purchase_date) }}</p>
                </div>
              </div>

              <!-- Action Buttons with Icons and Text -->
              <div class="grid grid-cols-3 gap-1.5 pt-2 border-t border-gray-100">
                <button
                  @click="openViewModal(sim)"
                  class="group/btn flex flex-col items-center justify-center bg-gradient-to-br from-blue-500 to-blue-600 text-white py-2 px-1 rounded-lg font-bold hover:from-blue-600 hover:to-blue-700 transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
                >
                  <svg class="w-4 h-4 mb-0.5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                  <span class="text-[10px]">View</span>
                </button>
                <button
                  @click="openEditModal(sim)"
                  class="group/btn flex flex-col items-center justify-center bg-gradient-to-br from-amber-500 to-orange-600 text-white py-2 px-1 rounded-lg font-bold hover:from-amber-600 hover:to-orange-700 transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
                >
                  <svg class="w-4 h-4 mb-0.5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                  <span class="text-[10px]">Edit</span>
                </button>
                <button
                  @click="confirmDelete(sim)"
                  class="group/btn flex flex-col items-center justify-center bg-gradient-to-br from-red-500 to-red-600 text-white py-2 px-1 rounded-lg font-bold hover:from-red-600 hover:to-red-700 transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
                >
                  <svg class="w-4 h-4 mb-0.5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                  <span class="text-[10px]">Delete</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-16">
          <svg class="w-24 h-24 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
          </svg>
          <p class="mt-4 text-xl text-gray-600">No SIM cards in stock</p>
          <button
            @click="openAddModal"
            class="mt-6 px-6 py-3 bg-blue-500 text-white font-bold rounded-lg hover:bg-blue-600 transition-colors"
          >
            Add Your First SIM
          </button>
        </div>
      </div>

      <!-- Stock Movement Table Section -->
      <div class="mt-8 w-full bg-white rounded-2xl shadow-xl p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold text-gray-800">Stock Movement History</h2>
          <div class="flex items-center space-x-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
          </div>
        </div>

    <!-- Filters -->
    <div class="bg-gray-50 rounded-xl p-4 mb-4 border border-gray-200">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div>
          <label class="block text-xs font-semibold text-gray-700 mb-1">SIM Provider</label>
          <select v-model="movementFilters.sim_name" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">All Providers</option>
            <option value="Mobitel">Mobitel</option>
            <option value="Dialog">Dialog</option>
            <option value="Airtel">Airtel</option>
            <option value="Hutch">Hutch</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-700 mb-1">Movement Type</label>
          <select v-model="movementFilters.movement_type" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">All Types</option>
            <option value="in">Stock In</option>
            <option value="out">Stock Out</option>
            <option value="adjustment">Adjustment</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-700 mb-1">Date From</label>
          <input v-model="movementFilters.date_from" type="date" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-700 mb-1">Date To</label>
          <input v-model="movementFilters.date_to" type="date" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
        </div>
      </div>
      <div class="flex justify-end mt-3">
        <button @click="clearFilters" class="px-4 py-1.5 text-sm bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition-colors">
          Clear Filters
        </button>
      </div>
    </div>

    <!-- Stock Movement Table -->
    <div class="overflow-x-auto rounded-lg border border-gray-200">
      <table class="w-full text-sm">
        <thead class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
          <tr>
            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider">Date & Time</th>
            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider">Provider</th>
            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider">Batch</th>
            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider">Type</th>
            <th class="px-3 py-2 text-center text-xs font-bold uppercase tracking-wider">Qty</th>
            <th class="px-3 py-2 text-center text-xs font-bold uppercase tracking-wider">Prev</th>
            <th class="px-3 py-2 text-center text-xs font-bold uppercase tracking-wider">Current</th>
            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider">Notes</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-if="filteredMovements.length === 0">
            <td colspan="8" class="px-4 py-8 text-center text-gray-500">
              <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
              </svg>
              <p class="font-semibold">No stock movements found</p>
              <p class="text-xs mt-1">Stock movements will appear here when SIM cards are added or updated</p>
            </td>
          </tr>
          <tr v-for="movement in filteredMovements" :key="movement.id" class="hover:bg-gray-50 transition-colors">
            <td class="px-3 py-2 whitespace-nowrap">
              <div class="flex items-center space-x-1">
                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="text-xs font-medium text-gray-900">{{ formatDateTime(movement.movement_date) }}</span>
              </div>
            </td>
            <td class="px-3 py-2 whitespace-nowrap">
              <span :class="getCarrierBadgeClass(movement.sim_stock?.sim_name)" class="px-2 py-0.5 rounded-full text-[10px] font-bold">
                {{ movement.sim_stock?.sim_name || 'N/A' }}
              </span>
            </td>
            <td class="px-3 py-2 whitespace-nowrap">
              <span class="text-xs font-semibold text-gray-700">{{ movement.sim_stock?.batch_number || 'N/A' }}</span>
            </td>
            <td class="px-3 py-2 whitespace-nowrap">
              <span :class="getMovementTypeClass(movement.movement_type)" class="px-2 py-0.5 rounded-full text-[10px] font-bold border">
                {{ getMovementTypeLabel(movement.movement_type) }}
              </span>
            </td>
            <td class="px-3 py-2 whitespace-nowrap text-center">
              <span :class="movement.movement_type === 'in' ? 'text-green-600' : movement.movement_type === 'out' ? 'text-red-600' : 'text-blue-600'" class="text-base font-bold">
                {{ movement.movement_type === 'in' ? '+' : movement.movement_type === 'out' ? '-' : '±' }}{{ movement.quantity }}
              </span>
            </td>
            <td class="px-3 py-2 whitespace-nowrap text-center">
              <span class="text-xs font-semibold text-gray-600">{{ movement.previous_stock }}</span>
            </td>
            <td class="px-3 py-2 whitespace-nowrap text-center">
              <span class="text-xs font-bold text-gray-900">{{ movement.current_stock }}</span>
            </td>
            <td class="px-3 py-2">
              <span class="text-xs text-gray-600">{{ movement.notes || '-' }}</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  </div>
  </div>

  <Footer />

  <!-- View Modal -->
  <div v-if="showViewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @click="closeViewModal">
    <div @click.stop class="bg-white rounded-2xl shadow-2xl p-8 max-w-2xl w-full mx-4 transform transition-all">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-800">SIM Card Details</h2>
        <button @click="closeViewModal" class="text-gray-500 hover:text-gray-700">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
      <div v-if="selectedSim" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-sm text-gray-600">Carrier</p>
            <p class="text-xl font-bold text-gray-800">{{ selectedSim.sim_name }}</p>
          </div>
          <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-sm text-gray-600">Batch Number</p>
            <p class="text-xl font-bold text-gray-800">{{ selectedSim.batch_number }}</p>
          </div>
          <div class="bg-blue-50 p-4 rounded-lg">
            <p class="text-sm text-blue-600">Cost Price</p>
            <p class="text-xl font-bold text-blue-700">Rs. {{ selectedSim.cost_price }}</p>
          </div>
          <div class="bg-green-50 p-4 rounded-lg">
            <p class="text-sm text-green-600">Selling Price</p>
            <p class="text-xl font-bold text-green-700">Rs. {{ selectedSim.selling_price }}</p>
          </div>
          <div class="bg-purple-50 p-4 rounded-lg">
            <p class="text-sm text-purple-600">Stock Available</p>
            <p class="text-xl font-bold text-purple-700">{{ selectedSim.stock }} units</p>
          </div>
          <div class="bg-amber-50 p-4 rounded-lg">
            <p class="text-sm text-amber-600">Purchase Date</p>
            <p class="text-xl font-bold text-amber-700">{{ formatDate(selectedSim.purchase_date) }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Add/Edit Modal -->
  <div v-if="showFormModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @click="closeFormModal">
    <div @click.stop class="bg-white rounded-2xl shadow-2xl p-8 max-w-2xl w-full mx-4 transform transition-all">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-800">{{ isEditMode ? 'Edit' : 'Add' }} SIM Card</h2>
        <button @click="closeFormModal" class="text-gray-500 hover:text-gray-700">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
      <form @submit.prevent="submitForm" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Carrier</label>
            <select v-model="form.sim_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              <option value="">Select Carrier</option>
              <option value="Mobitel">Mobitel</option>
              <option value="Dialog">Dialog</option>
              <option value="Airtel">Airtel</option>
              <option value="Hutch">Hutch</option>
            </select>
          </div>
          <div v-if="isEditMode && selectedSim" class="col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Batch Number</label>
            <input :value="selectedSim.batch_number" type="text" readonly class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed" />
            <p class="text-xs text-gray-500 mt-1">Batch number is auto-generated and cannot be changed</p>
          </div>
          <div v-if="!isEditMode" class="col-span-2">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
              <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-semibold text-blue-700">Batch number will be auto-generated</p>
              </div>
            </div>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Cost Price (Rs.)</label>
            <input v-model="form.cost_price" type="number" step="0.01" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Selling Price (Rs.)</label>
            <input v-model="form.selling_price" type="number" step="0.01" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Stock Quantity</label>
            <input v-model="form.stock" type="number" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Purchase Date</label>
            <input v-model="form.purchase_date" type="date" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
          </div>
        </div>
        <div class="flex gap-4 pt-4">
          <button type="button" @click="closeFormModal" class="flex-1 px-6 py-3 bg-gray-300 text-gray-700 font-bold rounded-lg hover:bg-gray-400 transition-colors">
            Cancel
          </button>
          <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold rounded-lg hover:from-blue-600 hover:to-indigo-700 transition-all shadow-lg">
            {{ isEditMode ? 'Update' : 'Add' }} SIM
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @click="closeDeleteModal">
    <div @click.stop class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4">
      <div class="text-center">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
          <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 mb-2">Delete SIM Card?</h3>
        <p class="text-gray-600 mb-6">Are you sure you want to delete this SIM card? This action cannot be undone.</p>
        <div class="flex gap-4">
          <button @click="closeDeleteModal" class="flex-1 px-6 py-3 bg-gray-300 text-gray-700 font-bold rounded-lg hover:bg-gray-400 transition-colors">
            Cancel
          </button>
          <button @click="deleteSim" class="flex-1 px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white font-bold rounded-lg hover:from-red-600 hover:to-red-700 transition-all">
            Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import { Link, Head, router } from "@inertiajs/vue3";

const props = defineProps({
  simStocks: Array,
  stockMovements: Array,
  pageTitle: String
});

const showViewModal = ref(false);
const showFormModal = ref(false);
const showDeleteModal = ref(false);
const selectedSim = ref(null);
const isEditMode = ref(false);

// Filter state for stock movements
const movementFilters = reactive({
  sim_name: '',
  movement_type: '',
  date_from: '',
  date_to: ''
});

const form = reactive({
  sim_name: '',
  cost_price: '',
  selling_price: '',
  stock: '',
  purchase_date: ''
});

const getCarrierCardClass = (carrier) => {
  const classes = {
    'Mobitel': 'border-emerald-400 hover:border-emerald-600',
    'Dialog': 'border-red-400 hover:border-red-600',
    'Airtel': 'border-red-500 hover:border-red-700',
    'Hutch': 'border-orange-400 hover:border-orange-600'
  };
  return classes[carrier] || 'border-gray-300';
};

const getCarrierBadgeClass = (carrier) => {
  const classes = {
    'Mobitel': 'bg-gradient-to-r from-emerald-500 to-blue-600 text-white',
    'Dialog': 'bg-gradient-to-r from-red-600 to-yellow-500 text-white',
    'Airtel': 'bg-gradient-to-r from-red-600 to-red-700 text-white',
    'Hutch': 'bg-gradient-to-r from-orange-500 to-gray-900 text-white'
  };
  return classes[carrier] || 'bg-gray-500 text-white';
};

const getCarrierGradientClass = (carrier) => {
  const classes = {
    'Mobitel': 'bg-gradient-to-br from-emerald-500/10 to-blue-600/10',
    'Dialog': 'bg-gradient-to-br from-red-600/10 to-yellow-500/10',
    'Airtel': 'bg-gradient-to-br from-red-600/10 to-red-700/10',
    'Hutch': 'bg-gradient-to-br from-orange-500/10 to-gray-900/10'
  };
  return classes[carrier] || 'bg-gradient-to-br from-gray-500/10 to-gray-600/10';
};

const getCarrierAccentClass = (carrier) => {
  const classes = {
    'Mobitel': 'bg-emerald-500',
    'Dialog': 'bg-red-600',
    'Airtel': 'bg-red-600',
    'Hutch': 'bg-orange-500'
  };
  return classes[carrier] || 'bg-gray-500';
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  });
};

const formatDateTime = (date) => {
  return new Date(date).toLocaleString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Computed property for filtered movements
const filteredMovements = computed(() => {
  let movements = props.stockMovements || [];
  
  if (movementFilters.sim_name) {
    movements = movements.filter(m => m.sim_stock?.sim_name === movementFilters.sim_name);
  }
  
  if (movementFilters.movement_type) {
    movements = movements.filter(m => m.movement_type === movementFilters.movement_type);
  }
  
  if (movementFilters.date_from) {
    const fromDate = new Date(movementFilters.date_from);
    movements = movements.filter(m => new Date(m.movement_date) >= fromDate);
  }
  
  if (movementFilters.date_to) {
    const toDate = new Date(movementFilters.date_to);
    toDate.setHours(23, 59, 59, 999);
    movements = movements.filter(m => new Date(m.movement_date) <= toDate);
  }
  
  return movements;
});

const clearFilters = () => {
  movementFilters.sim_name = '';
  movementFilters.movement_type = '';
  movementFilters.date_from = '';
  movementFilters.date_to = '';
};

const getMovementTypeClass = (type) => {
  const classes = {
    'in': 'bg-green-100 text-green-800 border-green-300',
    'out': 'bg-red-100 text-red-800 border-red-300',
    'adjustment': 'bg-blue-100 text-blue-800 border-blue-300'
  };
  return classes[type] || 'bg-gray-100 text-gray-800';
};

const getMovementTypeLabel = (type) => {
  const labels = {
    'in': 'Stock In',
    'out': 'Stock Out',
    'adjustment': 'Adjustment'
  };
  return labels[type] || type;
};

const openViewModal = (sim) => {
  selectedSim.value = sim;
  showViewModal.value = true;
};

const closeViewModal = () => {
  showViewModal.value = false;
  selectedSim.value = null;
};

const openAddModal = () => {
  isEditMode.value = false;
  resetForm();
  showFormModal.value = true;
};

const openEditModal = (sim) => {
  isEditMode.value = true;
  selectedSim.value = sim;
  form.sim_name = sim.sim_name;
  form.cost_price = sim.cost_price;
  form.selling_price = sim.selling_price;
  form.stock = sim.stock;
  form.purchase_date = sim.purchase_date;
  showFormModal.value = true;
};

const closeFormModal = () => {
  showFormModal.value = false;
  resetForm();
};

const resetForm = () => {
  form.sim_name = '';
  form.cost_price = '';
  form.selling_price = '';
  form.stock = '';
  form.purchase_date = '';
  selectedSim.value = null;
};

const submitForm = () => {
  if (isEditMode.value) {
    router.put(`/sim-stocks/${selectedSim.value.id}`, form, {
      onSuccess: () => {
        closeFormModal();
      }
    });
  } else {
    router.post('/sim-stocks', form, {
      onSuccess: () => {
        closeFormModal();
      }
    });
  }
};

const confirmDelete = (sim) => {
  selectedSim.value = sim;
  showDeleteModal.value = true;
};

const closeDeleteModal = () => {
  showDeleteModal.value = false;
  selectedSim.value = null;
};

const deleteSim = () => {
  router.delete(`/sim-stocks/${selectedSim.value.id}`, {
    onSuccess: () => {
      closeDeleteModal();
    }
  });
};
</script>

<style scoped>
.sim-card {
  min-height: 280px;
  backdrop-filter: blur(10px);
}

/* Smooth animations */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Enhanced hover effects */
.sim-card::before {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 1.5rem;
  padding: 2px;
  background: linear-gradient(135deg, rgba(255,255,255,0.3), rgba(255,255,255,0));
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  opacity: 0;
  transition: opacity 0.3s;
  pointer-events: none;
}

.sim-card:hover::before {
  opacity: 1;
}
</style>
