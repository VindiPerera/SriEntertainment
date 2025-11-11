<template>
  <Head title="Manage Wallet" />
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
            Manage Wallet
          </h1>
        </div>
      </div>

      <!-- Wallet Balances Section -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Existing Wallets -->
        <div 
          v-for="wallet in wallets" 
          :key="wallet.id"
          class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-lg p-6 border-2 hover:shadow-xl transition-all duration-300"
          :class="getBorderColor(wallet.operator.code)"
        >
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold" :class="getTextColor(wallet.operator.code)">
              {{ wallet.operator.name }}
            </h3>
            <span class="px-3 py-1 text-xs font-semibold rounded-full"
                  :class="wallet.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
              {{ wallet.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
          
          <div class="space-y-2">
            <div class="flex justify-between items-center">
              <span class="text-gray-600">Balance:</span>
              <span class="text-2xl font-bold text-gray-900">
                Rs. {{ formatCurrency(wallet.balance) }}
              </span>
            </div>
            <div class="flex justify-between text-sm text-gray-500">
              <span>Total Deposits:</span>
              <span>Rs. {{ formatCurrency(wallet.total_deposits) }}</span>
            </div>
            <div class="flex justify-between text-sm text-gray-500">
              <span>Total Sales:</span>
              <span>Rs. {{ formatCurrency(wallet.total_sales) }}</span>
            </div>
            <div class="flex justify-between text-sm text-gray-500">
              <span>Commission Earned:</span>
              <span class="text-green-600 font-semibold">
                Rs. {{ formatCurrency(wallet.total_commissions) }}
              </span>
            </div>
          </div>
          
          <div class="mt-4 flex space-x-2">
            <button 
              @click="openDepositModal(wallet)"
              class="flex-1 px-4 py-2 text-sm font-semibold text-white rounded-lg transition-colors duration-200"
              :class="getButtonColor(wallet.operator.code)"
            >
              Deposit
            </button>
            <button 
              @click="openSellModal(wallet)"
              class="flex-1 px-4 py-2 text-sm font-semibold bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition-colors duration-200"
            >
              Sell
            </button>
          </div>
        </div>

        <!-- Operators without wallets - show as cards to create wallet -->
        <div 
          v-for="operator in operators.filter(op => !wallets.some(w => w.operator_id === op.id))"
          :key="'op-' + operator.id"
          class="bg-gradient-to-br from-gray-50 to-white rounded-2xl shadow-lg p-6 border-2 border-dashed hover:shadow-xl transition-all duration-300 cursor-pointer"
          :class="'border-gray-300 hover:' + getBorderColor(operator.code)"
          @click="createWalletForOperator(operator)"
        >
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold" :class="getTextColor(operator.code)">
              {{ operator.name }}
            </h3>
            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
              New Wallet
            </span>
          </div>
          
          <div class="space-y-2 mb-4">
            <p class="text-sm text-gray-600">
              {{ operator.business_model === 'deposit_bonus' ? '🎁 Deposit Bonus: ' + operator.default_percentage + '%' : '💰 Sale Commission: ' + operator.default_percentage + '%' }}
            </p>
            <p class="text-xs text-gray-500">
              {{ operator.description || 'Click to create wallet and start depositing' }}
            </p>
          </div>
          
          <button 
            class="w-full px-4 py-2 text-sm font-semibold text-white rounded-lg transition-colors duration-200"
            :class="getButtonColor(operator.code)"
          >
            Create Wallet & Deposit
          </button>
        </div>
      </div>

      <!-- Recent Transactions Section -->
      <div class="bg-white rounded-2xl shadow-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Recent Transactions</h2>
        
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operator</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="transaction in recentTransactions" :key="transaction.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(transaction.transaction_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ transaction.wallet_account.operator.name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                        :class="getTransactionTypeClass(transaction.transaction_type)">
                    {{ transaction.transaction_type }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ transaction.reference }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-right"
                    :class="transaction.credit > 0 ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">
                  {{ transaction.credit > 0 ? '+' : '-' }}Rs. {{ formatCurrency(transaction.credit > 0 ? transaction.credit : transaction.debit) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                  Rs. {{ formatCurrency(transaction.balance_after) }}
                </td>
              </tr>
              <tr v-if="recentTransactions.length === 0">
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                  No transactions yet
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Deposit Modal -->
  <Modal :show="showDepositModal" @close="closeDepositModal">
    <div class="p-6">
      <h3 class="text-2xl font-bold text-gray-900 mb-6">
        Deposit to {{ selectedWallet?.operator?.name }} Wallet
      </h3>
      
      <form @submit.prevent="submitDeposit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Amount (Rs.)</label>
          <input 
            v-model="depositForm.amount"
            type="number"
            step="0.01"
            min="1"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Enter amount"
          />
        </div>

        <div v-if="selectedWallet?.operator?.business_model === 'deposit_bonus'" class="bg-green-50 border border-green-200 rounded-lg p-4">
          <p class="text-sm text-green-800">
            <strong>Bonus:</strong> You'll receive {{ getBonusPercentage(selectedWallet) }}% bonus on this deposit!
          </p>
          <p class="text-lg font-semibold text-green-900 mt-2" v-if="depositForm.amount">
            Total Credit: Rs. {{ calculateTotalCredit(depositForm.amount) }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
          <textarea 
            v-model="depositForm.notes"
            rows="3"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Add any notes..."
          ></textarea>
        </div>

        <div class="flex space-x-3 pt-4">
          <button 
            type="submit"
            :disabled="depositForm.processing"
            class="flex-1 px-4 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
          >
            {{ depositForm.processing ? 'Processing...' : 'Confirm Deposit' }}
          </button>
          <button 
            type="button"
            @click="closeDepositModal"
            class="px-4 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition-colors duration-200"
          >
            Cancel
          </button>
        </div>
      </form>
    </div>
  </Modal>

  <!-- Sell Reload Modal -->
  <Modal :show="showSellModal" @close="closeSellModal">
    <div class="p-6">
      <h3 class="text-2xl font-bold text-gray-900 mb-6">
        Sell Reload - {{ selectedWallet?.operator?.name }}
      </h3>
      
      <form @submit.prevent="submitSell" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Select Package</label>
          <select 
            v-model="sellForm.reload_package_id"
            @change="getQuote"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">Choose a package</option>
            <option v-for="pkg in reloadPackages" :key="pkg.id" :value="pkg.id">
              {{ pkg.name }} - Rs. {{ formatCurrency(pkg.face_value) }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Mobile Number (MSISDN)</label>
          <input 
            v-model="sellForm.msisdn"
            type="text"
            pattern="[0-9]{10}"
            maxlength="10"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="07XXXXXXXX"
          />
        </div>

        <div v-if="quoteData" class="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-2">
          <h4 class="font-semibold text-blue-900 mb-2">Transaction Summary</h4>
          <div class="flex justify-between text-sm">
            <span class="text-blue-700">Face Value:</span>
            <span class="font-semibold text-blue-900">Rs. {{ formatCurrency(quoteData.face_value) }}</span>
          </div>
          <div v-if="quoteData.commission_amount > 0" class="flex justify-between text-sm">
            <span class="text-blue-700">Commission ({{ quoteData.commission_percent }}%):</span>
            <span class="font-semibold text-green-600">Rs. {{ formatCurrency(quoteData.commission_amount) }}</span>
          </div>
          <div class="flex justify-between text-sm border-t border-blue-200 pt-2">
            <span class="text-blue-700 font-semibold">Net Cost:</span>
            <span class="font-semibold text-blue-900">Rs. {{ formatCurrency(quoteData.net_cost) }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-blue-700">Current Balance:</span>
            <span class="font-semibold">Rs. {{ formatCurrency(quoteData.current_balance) }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-blue-700">Balance After:</span>
            <span class="font-semibold" :class="quoteData.balance_after >= 0 ? 'text-green-600' : 'text-red-600'">
              Rs. {{ formatCurrency(quoteData.balance_after) }}
            </span>
          </div>
          
          <div v-if="!quoteData.sufficient_balance" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm text-red-800 font-semibold">⚠️ Insufficient balance!</p>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
          <textarea 
            v-model="sellForm.notes"
            rows="2"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Add any notes..."
          ></textarea>
        </div>

        <div class="flex space-x-3 pt-4">
          <button 
            type="submit"
            :disabled="sellForm.processing || !quoteData?.sufficient_balance"
            class="flex-1 px-4 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
          >
            {{ sellForm.processing ? 'Processing...' : 'Confirm Sale' }}
          </button>
          <button 
            type="button"
            @click="closeSellModal"
            class="px-4 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition-colors duration-200"
          >
            Cancel
          </button>
        </div>
      </form>
    </div>
  </Modal>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Banner from '@/Components/Banner.vue';
import Header from '@/Components/custom/Header.vue';
import Modal from '@/Components/Modal.vue';
import axios from 'axios';
import dayjs from 'dayjs';

const props = defineProps({
  operators: Array,
  wallets: Array,
  recentTransactions: Array,
});

const showDepositModal = ref(false);
const showSellModal = ref(false);
const showAddWalletModal = ref(false);
const selectedWallet = ref(null);
const reloadPackages = ref([]);
const quoteData = ref(null);

const depositForm = ref({
  operator_id: null,
  amount: '',
  notes: '',
  processing: false,
});

const sellForm = ref({
  operator_id: null,
  reload_package_id: '',
  msisdn: '',
  notes: '',
  processing: false,
});

const availableOperators = computed(() => {
  const existingOperatorIds = props.wallets.map(w => w.operator_id);
  return props.operators.filter(op => !existingOperatorIds.includes(op.id));
});

const openDepositModal = (wallet) => {
  selectedWallet.value = wallet;
  depositForm.value = {
    operator_id: wallet.operator_id,
    amount: '',
    notes: '',
    processing: false,
  };
  showDepositModal.value = true;
};

const closeDepositModal = () => {
  showDepositModal.value = false;
  selectedWallet.value = null;
  depositForm.value = {
    operator_id: null,
    amount: '',
    notes: '',
    processing: false,
  };
};

const openSellModal = async (wallet) => {
  selectedWallet.value = wallet;
  sellForm.value = {
    operator_id: wallet.operator_id,
    reload_package_id: '',
    msisdn: '',
    notes: '',
    processing: false,
  };
  
  // Load packages for this operator
  try {
    const response = await axios.get(`/api/wallet/packages?operator_id=${wallet.operator_id}`);
    reloadPackages.value = response.data.packages;
  } catch (error) {
    console.error('Failed to load packages:', error);
  }
  
  showSellModal.value = true;
};

const closeSellModal = () => {
  showSellModal.value = false;
  selectedWallet.value = null;
  quoteData.value = null;
  reloadPackages.value = [];
};

const createWalletForOperator = (operator) => {
  // Create a temporary wallet object for deposit
  selectedWallet.value = {
    id: null, // No wallet ID yet
    operator_id: operator.id,
    operator: operator,
    balance: 0,
    is_active: true
  };
  
  depositForm.value = {
    wallet_account_id: null, // Will create new wallet on backend
    operator_id: operator.id,
    amount: '',
    notes: `Initial deposit for ${operator.name} wallet`,
    processing: false
  };
  
  showDepositModal.value = true;
};

const submitDeposit = async () => {
  depositForm.value.processing = true;
  
  try {
    const response = await axios.post('/wallet/deposit', depositForm.value);
    
    if (response.data.success) {
      alert(response.data.message);
      window.location.reload();
    }
  } catch (error) {
    alert('Deposit failed: ' + (error.response?.data?.message || error.message));
  } finally {
    depositForm.value.processing = false;
  }
};

const getQuote = async () => {
  if (!sellForm.value.reload_package_id) {
    quoteData.value = null;
    return;
  }
  
  try {
    const response = await axios.post('/wallet/quote', {
      operator_id: sellForm.value.operator_id,
      reload_package_id: sellForm.value.reload_package_id,
    });
    
    if (response.data.success) {
      quoteData.value = response.data.quote;
    }
  } catch (error) {
    console.error('Failed to get quote:', error);
  }
};

const submitSell = async () => {
  if (!quoteData.value?.sufficient_balance) {
    alert('Insufficient balance!');
    return;
  }
  
  sellForm.value.processing = true;
  
  try {
    const response = await axios.post('/wallet/sell', sellForm.value);
    
    if (response.data.success) {
      alert(response.data.message);
      window.location.reload();
    }
  } catch (error) {
    alert('Sale failed: ' + (error.response?.data?.message || error.message));
  } finally {
    sellForm.value.processing = false;
  }
};

const formatCurrency = (amount) => {
  return parseFloat(amount || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
};

const formatDate = (date) => {
  return dayjs(date).format('MMM DD, YYYY HH:mm');
};

const getBorderColor = (code) => {
  const colors = {
    'MOB': 'border-yellow-400',
    'DIA': 'border-red-400',
    'AIR': 'border-red-500',
    'HUT': 'border-purple-400',
  };
  return colors[code] || 'border-gray-300';
};

const getTextColor = (code) => {
  const colors = {
    'MOB': 'text-yellow-600',
    'DIA': 'text-red-600',
    'AIR': 'text-red-600',
    'HUT': 'text-purple-600',
  };
  return colors[code] || 'text-gray-600';
};

const getButtonColor = (code) => {
  const colors = {
    'MOB': 'bg-yellow-500 hover:bg-yellow-600',
    'DIA': 'bg-red-500 hover:bg-red-600',
    'AIR': 'bg-red-600 hover:bg-red-700',
    'HUT': 'bg-purple-500 hover:bg-purple-600',
  };
  return colors[code] || 'bg-blue-500 hover:bg-blue-600';
};

const getTransactionTypeClass = (type) => {
  const classes = {
    'deposit': 'bg-green-100 text-green-800',
    'sale': 'bg-blue-100 text-blue-800',
    'adjustment': 'bg-yellow-100 text-yellow-800',
    'refund': 'bg-red-100 text-red-800',
  };
  return classes[type] || 'bg-gray-100 text-gray-800';
};

const getBonusPercentage = (wallet) => {
  // This should ideally come from the operator rates
  return wallet?.operator?.default_percentage || 0;
};

const calculateTotalCredit = (amount) => {
  if (!amount || !selectedWallet.value) return '0.00';
  
  const bonus = selectedWallet.value.operator.business_model === 'deposit_bonus' 
    ? (parseFloat(amount) * getBonusPercentage(selectedWallet.value)) / 100
    : 0;
  
  return formatCurrency(parseFloat(amount) + bonus);
};
</script>

<style scoped>
.card-base {
  @apply relative bg-white rounded-3xl shadow-lg p-8 transition-all duration-300 cursor-pointer;
  @apply hover:shadow-2xl hover:scale-105;
}

.icon-ring {
  @apply w-32 h-32 rounded-full bg-gradient-to-br flex items-center justify-center;
  @apply shadow-lg transition-transform duration-300;
}
</style>
