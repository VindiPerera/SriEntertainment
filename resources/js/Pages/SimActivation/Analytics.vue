<template>
  <Head title="SIM Activation Analytics" />
  <Banner />

  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-24 px-16">
    <Header />

    <div class="w-full md:w-5/6 py-12 space-y-8 md:px-0 px-6 mx-auto">
      <!-- Page Header -->
      <div class="flex items-center justify-between mb-8">
        <div class="flex items-center space-x-4">
          <Link href="/sim-activation">
            <img src="/images/back-arrow.png" class="w-14 h-14" alt="Back" />
          </Link>
          <h1 class="text-4xl font-bold tracking-wide text-gray-800">
            📊 SIM Activation Analytics
          </h1>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-2xl shadow-xl p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Filters</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Operator</label>
            <select 
              v-model="filters.operator_id"
              @change="loadAnalytics"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Operators</option>
              <option v-for="operator in operators" :key="operator.id" :value="operator.id">
                {{ operator.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Transaction Type</label>
            <select 
              v-model="filters.transaction_type"
              @change="loadAnalytics"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Types</option>
              <option value="sim_activation">SIM Activation</option>
              <option value="reload">Reload Only</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
            <input 
              v-model="filters.from_date"
              @change="loadAnalytics"
              type="date"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
            <input 
              v-model="filters.to_date"
              @change="loadAnalytics"
              type="date"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>

        <div class="mt-4">
          <button
            @click="resetFilters"
            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors"
          >
            Reset Filters
          </button>
        </div>
      </div>

      <!-- Summary Cards -->
      <div v-if="summary" class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg p-6 text-white">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-medium opacity-90">Total Transactions</h3>
            <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
          <p class="text-3xl font-bold">{{ summary.total_transactions }}</p>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg p-6 text-white">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-medium opacity-90">Total Revenue</h3>
            <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <p class="text-3xl font-bold">Rs. {{ formatCurrency(summary.total_revenue) }}</p>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg p-6 text-white">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-medium opacity-90">Total Profit</h3>
            <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
          </div>
          <p class="text-3xl font-bold">Rs. {{ formatCurrency(summary.total_profit) }}</p>
        </div>

        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl shadow-lg p-6 text-white">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-medium opacity-90">Wallet Change</h3>
            <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
          </div>
          <p class="text-3xl font-bold">{{ summary.total_wallet_change >= 0 ? '+' : '' }}Rs. {{ formatCurrency(summary.total_wallet_change) }}</p>
        </div>
      </div>

      <!-- Profit Breakdown -->
      <div v-if="summary" class="bg-white rounded-2xl shadow-xl p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Profit Breakdown</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="border-2 border-blue-200 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-600 mb-2">Package Profit</h3>
            <p class="text-2xl font-bold text-blue-600">Rs. {{ formatCurrency(summary.package_profit) }}</p>
            <p class="text-xs text-gray-500 mt-1">
              {{ ((summary.package_profit / summary.total_profit) * 100).toFixed(1) }}% of total
            </p>
          </div>

          <div class="border-2 border-green-200 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-600 mb-2">SIM Profit</h3>
            <p class="text-2xl font-bold text-green-600">Rs. {{ formatCurrency(summary.sim_profit) }}</p>
            <p class="text-xs text-gray-500 mt-1">
              {{ ((summary.sim_profit / summary.total_profit) * 100).toFixed(1) }}% of total
            </p>
          </div>
        </div>
      </div>

      <!-- By Operator Breakdown -->
      <div v-if="byOperator && byOperator.length > 0" class="bg-white rounded-2xl shadow-xl p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Performance by Operator</h2>
        
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operator</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Transactions</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Profit</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Package Profit</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">SIM Profit</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Wallet Change</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="op in byOperator" :key="op.operator_id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ op.operator.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-gray-700">{{ op.transaction_count }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-gray-700">Rs. {{ formatCurrency(op.total_revenue) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right font-semibold text-purple-600">Rs. {{ formatCurrency(op.total_profit) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-blue-600">Rs. {{ formatCurrency(op.package_profit) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-green-600">Rs. {{ formatCurrency(op.sim_profit) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right" :class="op.wallet_change >= 0 ? 'text-green-600' : 'text-red-600'">
                  {{ op.wallet_change >= 0 ? '+' : '' }}Rs. {{ formatCurrency(op.wallet_change) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <Footer />
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Header from '@/Components/custom/Header.vue';
import Footer from '@/Components/custom/Footer.vue';
import Banner from '@/Components/Banner.vue';
import axios from 'axios';

const props = defineProps({
  operators: Array,
});

const filters = ref({
  operator_id: '',
  transaction_type: '',
  from_date: '',
  to_date: '',
  face_value: '',
});

const summary = ref(null);
const byOperator = ref([]);
const loading = ref(false);

const formatCurrency = (amount) => {
  return parseFloat(amount || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
};

const loadAnalytics = async () => {
  loading.value = true;
  
  try {
    const params = new URLSearchParams();
    Object.keys(filters.value).forEach(key => {
      if (filters.value[key]) {
        params.append(key, filters.value[key]);
      }
    });

    const response = await axios.get(`/api/sim-activation/analytics-data?${params.toString()}`);
    
    if (response.data.success) {
      summary.value = response.data.summary;
      byOperator.value = response.data.by_operator;
    }
  } catch (error) {
    console.error('Failed to load analytics:', error);
    alert('Failed to load analytics data');
  } finally {
    loading.value = false;
  }
};

const resetFilters = () => {
  filters.value = {
    operator_id: '',
    transaction_type: '',
    from_date: '',
    to_date: '',
    face_value: '',
  };
  loadAnalytics();
};

onMounted(() => {
  loadAnalytics();
});
</script>
