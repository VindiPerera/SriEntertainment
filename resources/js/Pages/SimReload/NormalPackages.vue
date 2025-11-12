<template>
  <Head title="Normal Packages" />
  <Banner />

  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-24 px-16">
    <Header />

    <div class="w-full md:w-5/6 py-12 space-y-8 md:px-0 px-6 mx-auto">
      <!-- Page Header -->
      <div class="flex items-center justify-between mb-8">
        <div class="flex items-center space-x-4">
          <Link href="/sim-reload">
            <img src="/images/back-arrow.png" class="w-14 h-14" alt="Back" />
          </Link>
          <h1 class="text-4xl font-bold tracking-wide text-gray-800">
            Normal Reload Packages
          </h1>
        </div>
        <button
          @click="openAddPackageModal"
          class="px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition-colors duration-200 flex items-center space-x-2"
        >
          <span class="text-xl">➕</span>
          <span>Add Package</span>
        </button>
      </div>

      <!-- Operators Tabs -->
      <div class="bg-white rounded-2xl shadow-lg p-2">
        <div class="flex space-x-2 overflow-x-auto">
          <button
            v-for="operator in operators"
            :key="operator.id"
            @click="selectedOperator = operator"
            class="px-6 py-3 rounded-lg font-semibold transition-all duration-200 whitespace-nowrap"
            :class="selectedOperator?.id === operator.id 
              ? 'bg-gradient-to-r ' + getGradientClass(operator.code) + ' text-white shadow-lg'
              : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
          >
            {{ operator.name }}
          </button>
        </div>
      </div>

      <!-- Wallet Balance Card -->
      <div v-if="selectedOperator && getWalletForOperator(selectedOperator.id)" 
           class="bg-gradient-to-r rounded-2xl shadow-lg p-6 text-white"
           :class="getGradientClass(selectedOperator.code)">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-white/80 text-sm font-medium">{{ selectedOperator.name }} Wallet Balance</p>
            <p class="text-3xl font-bold mt-1">
              Rs. {{ formatCurrency(getWalletForOperator(selectedOperator.id).balance) }}
            </p>
          </div>
          <div class="bg-white/20 backdrop-blur-sm rounded-full p-4">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
          </div>
        </div>
      </div>

      <div v-else-if="selectedOperator" 
           class="bg-yellow-50 border-2 border-yellow-200 rounded-2xl p-6">
        <p class="text-yellow-800 text-center">
          ⚠️ No wallet found for {{ selectedOperator.name }}. 
          <Link href="/mobile-topup/manage-wallet" class="font-semibold underline hover:text-yellow-900">
            Create a wallet first
          </Link>
        </p>
      </div>

      <!-- Packages Grid -->
      <div v-if="selectedOperator" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div
          v-for="pkg in selectedOperator.reload_packages"
          :key="pkg.id"
          class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border-2 hover:scale-105"
          :class="'border-' + getBorderColorClass(selectedOperator.code)"
        >
          <div class="p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center space-x-2">
                <div class="w-10 h-10 rounded-full flex items-center justify-center"
                     :class="'bg-gradient-to-r ' + getGradientClass(selectedOperator.code)">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                  </svg>
                </div>
                <span class="text-sm font-medium text-gray-600">{{ selectedOperator.code }}</span>
              </div>
              <div class="flex space-x-1">
                <button
                  @click.stop="openEditPackageModal(pkg)"
                  class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200"
                  title="Edit Package"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                </button>
                <button
                  @click.stop="confirmDeletePackage(pkg)"
                  class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200"
                  title="Delete Package"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </button>
              </div>
            </div>

            <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ pkg.name }}</h3>
            <div class="flex items-baseline space-x-2 mb-4">
              <span class="text-4xl font-bold" :class="'text-' + getTextColorClass(selectedOperator.code)">
                Rs. {{ formatCurrency(pkg.face_value) }}
              </span>
            </div>

            <div v-if="pkg.description" class="text-sm text-gray-600 mb-4">
              {{ pkg.description }}
            </div>
          </div>
        </div>

        <div v-if="selectedOperator.reload_packages.length === 0" 
             class="col-span-full text-center py-12 text-gray-500">
          <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
          </svg>
          <p class="text-lg">No packages available for {{ selectedOperator.name }}</p>
        </div>
      </div>

      <!-- Initial State -->
      <div v-else class="text-center py-12">
        <svg class="w-20 h-20 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        <p class="text-xl text-gray-600">Select an operator to view packages</p>
      </div>
    </div>
  </div>

  <!-- Purchase Modal -->
  <Modal :show="showPurchaseModal" @close="closePurchaseModal">
    <div class="p-6">
      <h3 class="text-2xl font-bold text-gray-900 mb-6">
        Purchase {{ selectedPackage?.name }} - {{ selectedOperator?.name }}
      </h3>
      
      <form @submit.prevent="submitPurchase" class="space-y-4">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
          <div class="flex justify-between mb-2">
            <span class="text-blue-700">Package:</span>
            <span class="font-semibold text-blue-900">{{ selectedPackage?.name }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-blue-700">Face Value:</span>
            <span class="font-semibold text-blue-900">Rs. {{ formatCurrency(selectedPackage?.face_value) }}</span>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Mobile Number (MSISDN)</label>
          <input 
            v-model="purchaseForm.msisdn"
            type="text"
            pattern="[0-9]{10}"
            maxlength="10"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg"
            placeholder="07XXXXXXXX"
            autofocus
          />
          <p class="mt-1 text-sm text-gray-500">Enter 10-digit mobile number</p>
        </div>

        <div v-if="quoteData" class="bg-green-50 border border-green-200 rounded-lg p-4 space-y-2">
          <h4 class="font-semibold text-green-900 mb-2">Transaction Summary</h4>
          <div class="flex justify-between text-sm">
            <span class="text-green-700">Face Value:</span>
            <span class="font-semibold text-green-900">Rs. {{ formatCurrency(quoteData.face_value) }}</span>
          </div>
          <div v-if="quoteData.commission_amount > 0" class="flex justify-between text-sm">
            <span class="text-green-700">Commission ({{ quoteData.commission_percent }}%):</span>
            <span class="font-semibold text-green-600">Rs. {{ formatCurrency(quoteData.commission_amount) }}</span>
          </div>
          <div class="flex justify-between text-sm border-t border-green-200 pt-2">
            <span class="text-green-700 font-semibold">Net Cost:</span>
            <span class="font-semibold text-green-900">Rs. {{ formatCurrency(quoteData.net_cost) }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-green-700">Current Balance:</span>
            <span class="font-semibold">Rs. {{ formatCurrency(quoteData.current_balance) }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-green-700">Balance After:</span>
            <span class="font-semibold" :class="quoteData.balance_after >= 0 ? 'text-green-600' : 'text-red-600'">
              Rs. {{ formatCurrency(quoteData.balance_after) }}
            </span>
          </div>
          
          <div v-if="!quoteData.sufficient_balance" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm text-red-800 font-semibold">⚠️ Insufficient balance!</p>
            <Link href="/mobile-topup/manage-wallet" class="text-sm text-red-600 underline hover:text-red-700 mt-1 inline-block">
              Add funds to wallet
            </Link>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
          <textarea 
            v-model="purchaseForm.notes"
            rows="2"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Add any notes..."
          ></textarea>
        </div>

        <div class="flex space-x-3 pt-4">
          <button 
            type="submit"
            :disabled="purchaseForm.processing || !quoteData?.sufficient_balance || !purchaseForm.msisdn"
            class="flex-1 px-4 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
          >
            {{ purchaseForm.processing ? 'Processing...' : 'Confirm Purchase' }}
          </button>
          <button 
            type="button"
            @click="closePurchaseModal"
            class="px-4 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition-colors duration-200"
          >
            Cancel
          </button>
        </div>
      </form>
    </div>
  </Modal>

  <!-- Add/Edit Package Modal -->
  <Modal :show="showAddPackageModal || showEditPackageModal" @close="closePackageModal">
    <div class="p-6">
      <h3 class="text-2xl font-bold text-gray-900 mb-6">
        {{ showEditPackageModal ? 'Edit Package' : 'Add New Package' }}
      </h3>
      
      <form @submit.prevent="submitPackage" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Operator *</label>
          <select 
            v-model="packageForm.operator_id"
            required
            :disabled="showEditPackageModal"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100"
          >
            <option value="">Choose operator</option>
            <option v-for="operator in operators" :key="operator.id" :value="operator.id">
              {{ operator.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Package Name *</label>
          <input 
            v-model="packageForm.name"
            type="text"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="e.g., Data Pack 5GB"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Face Value (Rs.) *</label>
          <input 
            v-model="packageForm.face_value"
            type="number"
            step="0.01"
            min="0"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="e.g., 500.00"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
          <textarea 
            v-model="packageForm.description"
            rows="2"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Package details..."
          ></textarea>
        </div>

        <div class="flex items-center">
          <input 
            v-model="packageForm.is_active"
            type="checkbox"
            id="pkg_is_active"
            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
          />
          <label for="pkg_is_active" class="ml-2 text-sm font-medium text-gray-700">
            Active
          </label>
        </div>

        <div class="flex space-x-3 pt-4">
          <button 
            type="submit"
            :disabled="packageForm.processing"
            class="flex-1 px-4 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
          >
            {{ packageForm.processing ? 'Processing...' : (showEditPackageModal ? 'Update Package' : 'Add Package') }}
          </button>
          <button 
            type="button"
            @click="closePackageModal"
            class="px-4 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition-colors duration-200"
          >
            Cancel
          </button>
        </div>
      </form>
    </div>
  </Modal>

  <!-- Confirmation Modal -->
  <div v-if="showConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[120] p-4">
    <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl shadow-2xl p-8 w-full max-w-md text-white">
      <h3 class="text-xl font-bold mb-4">{{ confirmModalData.title }}</h3>
      <p class="text-gray-300 mb-8">{{ confirmModalData.message }}</p>
      
      <div class="flex justify-end gap-3">
        <button 
          @click="showConfirmModal = false" 
          class="px-8 py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-full font-medium transition-colors duration-200"
        >
          {{ confirmModalData.cancelText }}
        </button>
        <button 
          @click="() => { confirmModalData.onConfirm(); showConfirmModal = false; }" 
          class="px-8 py-3 bg-gradient-to-r from-pink-400 to-pink-500 hover:from-pink-500 hover:to-pink-600 text-white rounded-full font-medium transition-all duration-200 shadow-lg"
        >
          {{ confirmModalData.confirmText }}
        </button>
      </div>
    </div>
  </div>

  <!-- Alert Modal -->
  <div v-if="showAlertModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[120] p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
      <div class="text-center mb-6">
        <div v-if="alertModalData.type === 'success'" class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
          <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>
        <div v-else-if="alertModalData.type === 'error'" class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
          <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </div>
        <div v-else class="mx-auto w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
          <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ alertModalData.title }}</h3>
        <p class="text-gray-600">{{ alertModalData.message }}</p>
      </div>
      <div class="flex justify-center">
        <button 
          @click="showAlertModal = false; if (alertModalData.type === 'success') window.location.reload();"
          class="px-8 py-3 rounded-lg font-semibold transition-colors duration-200"
          :class="alertModalData.type === 'success' ? 'bg-green-600 hover:bg-green-700 text-white' : 
                  alertModalData.type === 'error' ? 'bg-red-600 hover:bg-red-700 text-white' : 
                  'bg-blue-600 hover:bg-blue-700 text-white'"
        >
          OK
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Banner from '@/Components/Banner.vue';
import Header from '@/Components/custom/Header.vue';
import Modal from '@/Components/Modal.vue';
import axios from 'axios';

const props = defineProps({
  operators: Array,
  wallets: Object,
});

const selectedOperator = ref(null);
const selectedPackage = ref(null);
const showPurchaseModal = ref(false);
const quoteData = ref(null);

// Package management state
const showAddPackageModal = ref(false);
const showEditPackageModal = ref(false);
const selectedPackageForEdit = ref(null);

// Confirmation modal state
const showConfirmModal = ref(false);
const confirmModalData = ref({
  title: '',
  message: '',
  onConfirm: null,
  confirmText: 'OK',
  cancelText: 'Cancel',
  type: 'warning'
});

// Alert modal state
const showAlertModal = ref(false);
const alertModalData = ref({
  title: '',
  message: '',
  type: 'success' // success, error, info
});

const packageForm = ref({
  operator_id: '',
  name: '',
  face_value: '',
  description: '',
  is_active: true,
  processing: false,
});

const purchaseForm = ref({
  operator_id: null,
  reload_package_id: null,
  msisdn: '',
  notes: '',
  processing: false,
});

// Set first operator as default
if (props.operators && props.operators.length > 0) {
  selectedOperator.value = props.operators[0];
}

const getWalletForOperator = (operatorId) => {
  return props.wallets[operatorId] || null;
};

const selectPackage = async (pkg) => {
  selectedPackage.value = pkg;
  purchaseForm.value = {
    operator_id: selectedOperator.value.id,
    reload_package_id: pkg.id,
    msisdn: '',
    notes: '',
    processing: false,
  };
  
  // Get quote
  await getQuote();
  showPurchaseModal.value = true;
};

const closePurchaseModal = () => {
  showPurchaseModal.value = false;
  selectedPackage.value = null;
  quoteData.value = null;
};

const getQuote = async () => {
  if (!purchaseForm.value.reload_package_id) {
    return;
  }
  
  try {
    const response = await axios.post('/api/wallet/quote', {
      operator_id: purchaseForm.value.operator_id,
      reload_package_id: purchaseForm.value.reload_package_id,
    });
    
    if (response.data.success) {
      quoteData.value = response.data.quote;
    }
  } catch (error) {
    console.error('Failed to get quote:', error);
    if (error.response?.status === 404) {
      alertModalData.value = {
        title: 'Wallet Not Found',
        message: 'Please create a wallet for this operator first.',
        type: 'error'
      };
      showAlertModal.value = true;
      closePurchaseModal();
    }
  }
};

const submitPurchase = async () => {
  if (!quoteData.value?.sufficient_balance) {
    alertModalData.value = {
      title: 'Insufficient Balance',
      message: 'Insufficient balance!',
      type: 'error'
    };
    showAlertModal.value = true;
    return;
  }
  
  if (!purchaseForm.value.msisdn || purchaseForm.value.msisdn.length !== 10) {
    alertModalData.value = {
      title: 'Invalid Mobile Number',
      message: 'Please enter a valid 10-digit mobile number',
      type: 'error'
    };
    showAlertModal.value = true;
    return;
  }
  
  purchaseForm.value.processing = true;
  
  try {
    const response = await axios.post('/api/wallet/sell', purchaseForm.value);
    
    if (response.data.success) {
      alertModalData.value = {
        title: 'Success',
        message: response.data.message,
        type: 'success'
      };
      showAlertModal.value = true;
    }
  } catch (error) {
    alertModalData.value = {
      title: 'Purchase Failed',
      message: error.response?.data?.message || error.message,
      type: 'error'
    };
    showAlertModal.value = true;
  } finally {
    purchaseForm.value.processing = false;
  }
};

const formatCurrency = (amount) => {
  return parseFloat(amount || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
};

const getGradientClass = (code) => {
  const gradients = {
    'MOB': 'from-yellow-500 to-yellow-600',
    'DIA': 'from-red-500 to-red-600',
    'AIR': 'from-red-600 to-red-700',
    'HUT': 'from-purple-500 to-purple-600',
  };
  return gradients[code] || 'from-blue-500 to-blue-600';
};

const getBorderColorClass = (code) => {
  const colors = {
    'MOB': 'gray-200 hover:border-yellow-400',
    'DIA': 'gray-200 hover:border-red-400',
    'AIR': 'gray-200 hover:border-red-500',
    'HUT': 'gray-200 hover:border-purple-400',
  };
  return colors[code] || 'gray-200 hover:border-blue-400';
};

const getTextColorClass = (code) => {
  const colors = {
    'MOB': 'yellow-600',
    'DIA': 'red-600',
    'AIR': 'red-600',
    'HUT': 'purple-600',
  };
  return colors[code] || 'blue-600';
};

// Package management functions
const openAddPackageModal = () => {
  resetPackageForm();
  if (selectedOperator.value) {
    packageForm.value.operator_id = selectedOperator.value.id;
  }
  showAddPackageModal.value = true;
};

const openEditPackageModal = (pkg) => {
  selectedPackageForEdit.value = pkg;
  packageForm.value = {
    operator_id: pkg.operator_id,
    name: pkg.name,
    face_value: pkg.face_value,
    description: pkg.description || '',
    is_active: pkg.is_active !== undefined ? pkg.is_active : true,
    processing: false,
  };
  showEditPackageModal.value = true;
};

const closePackageModal = () => {
  showAddPackageModal.value = false;
  showEditPackageModal.value = false;
  selectedPackageForEdit.value = null;
  resetPackageForm();
};

const resetPackageForm = () => {
  packageForm.value = {
    operator_id: '',
    name: '',
    face_value: '',
    description: '',
    is_active: true,
    processing: false,
  };
};

const submitPackage = async () => {
  packageForm.value.processing = true;
  
  try {
    const url = showEditPackageModal.value 
      ? `/api/reload-packages/${selectedPackageForEdit.value.id}` 
      : '/api/reload-packages';
    
    const method = showEditPackageModal.value ? 'put' : 'post';
    
    const response = await axios[method](url, packageForm.value);
    
    if (response.data.success) {
      alertModalData.value = {
        title: 'Success',
        message: response.data.message,
        type: 'success'
      };
      showAlertModal.value = true;
    }
  } catch (error) {
    alertModalData.value = {
      title: 'Operation Failed',
      message: error.response?.data?.message || error.message,
      type: 'error'
    };
    showAlertModal.value = true;
  } finally {
    packageForm.value.processing = false;
  }
};

const confirmDeletePackage = async (pkg) => {
  confirmModalData.value = {
    title: 'Confirm Delete',
    message: `Are you sure you want to delete "${pkg.name}"? This action cannot be undone.`,
    confirmText: 'OK',
    cancelText: 'Cancel',
    type: 'danger',
    onConfirm: async () => {
      try {
        const response = await axios.delete(`/api/reload-packages/${pkg.id}`);
        
        if (response.data.success) {
          window.location.reload();
        }
      } catch (error) {
        console.error('Delete failed:', error);
      }
    }
  };
  showConfirmModal.value = true;
};
</script>

<style scoped>
/* Custom scrollbar for tabs */
.overflow-x-auto::-webkit-scrollbar {
  height: 4px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 10px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>
