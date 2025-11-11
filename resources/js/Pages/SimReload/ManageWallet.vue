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
        <button
          @click="openAddOperatorModal"
          class="px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition-colors duration-200 flex items-center space-x-2"
        >
          <span class="text-xl">➕</span>
          <span>Add SIM Provider</span>
        </button>
      </div>

      <!-- Operator Wallet Cards - Always show all 4 operators -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div 
          v-for="operator in operators" 
          :key="operator.id"
          class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-lg p-6 border-2 hover:shadow-xl transition-all duration-300"
          :class="getBorderColor(operator.code)"
        >
          <!-- Operator Header -->
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-2">
              <h3 class="text-xl font-bold" :class="getTextColor(operator.code)">
                {{ operator.name }}
              </h3>
              <span class="px-2 py-1 text-xs font-semibold rounded-full"
                    :class="getOperatorBadgeColor(operator.code)">
                {{ operator.code }}
              </span>
            </div>
            <div class="flex space-x-1">
              <button
                @click="openEditOperatorModal(operator)"
                class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200"
                title="Edit Operator"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
              </button>
              <button
                @click="confirmDeleteOperator(operator)"
                class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200"
                title="Delete Operator"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Business Model Info -->
          <div class="mb-3 p-2 rounded-lg bg-gray-50">
            <p class="text-xs text-gray-600">
              {{ operator.business_model === 'deposit_bonus' ? '🎁 Deposit Bonus: ' + operator.default_percentage + '%' : '💰 Sale Commission: ' + operator.default_percentage + '%' }}
            </p>
          </div>
          
          <!-- Wallet Balance -->
          <div class="space-y-2 mb-4">
            <div class="flex justify-between items-center p-3 bg-white rounded-lg shadow-sm">
              <span class="text-sm text-gray-600">Wallet Balance:</span>
              <span class="text-2xl font-bold text-gray-900">
                Rs. {{ formatCurrency(getWalletBalance(operator.id)) }}
              </span>
            </div>
            
            <div v-if="getWallet(operator.id)" class="space-y-1">
              <div class="flex justify-between text-xs text-gray-500">
                <span>Total Deposits:</span>
                <span>Rs. {{ formatCurrency(getWallet(operator.id).total_deposits) }}</span>
              </div>
              <div class="flex justify-between text-xs text-gray-500">
                <span>Total Sales:</span>
                <span>Rs. {{ formatCurrency(getWallet(operator.id).total_sales) }}</span>
              </div>
              <div class="flex justify-between text-xs text-gray-500">
                <span>Commission:</span>
                <span class="text-green-600 font-semibold">
                  Rs. {{ formatCurrency(getWallet(operator.id).total_commissions) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Deposit and Sell Sections - Side by Side -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Deposit Section - Left Side -->
        <div class="bg-white rounded-xl shadow-lg p-6">
          <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
            <span class="text-3xl mr-3">💰</span>
            Deposit Money
          </h2>
          
          <form @submit.prevent="submitDeposit" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Select Operator *</label>
              <select 
                v-model="depositForm.operator_id"
                @change="depositForm.selected_percentage = null"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Choose operator</option>
                <option v-for="operator in operators" :key="operator.id" :value="operator.id">
                  {{ operator.name }}
                </option>
              </select>
            </div>

            <!-- Percentage Rate Selector (for deposit bonus operators) -->
            <div v-if="depositForm.operator_id && getSelectedDepositOperator()?.business_model === 'deposit_bonus' && getSelectedDepositOperator()?.rates?.length > 0">
              <label class="block text-sm font-medium text-gray-700 mb-2">Select Bonus Rate *</label>
              <div class="grid grid-cols-2 gap-2">
                <button
                  v-for="rate in getSelectedDepositOperator().rates"
                  :key="rate.id"
                  type="button"
                  @click="depositForm.selected_percentage = rate.percentage"
                  :class="[
                    'px-4 py-3 rounded-lg font-semibold transition-all duration-200',
                    depositForm.selected_percentage == rate.percentage
                      ? 'bg-green-600 text-white border-2 border-green-700 shadow-lg'
                      : 'bg-white text-green-700 border-2 border-green-300 hover:bg-green-50'
                  ]"
                >
                  {{ rate.percentage }}% Bonus
                </button>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Amount (Rs.) *</label>
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

            <!-- Quick Amount Buttons -->
            <div>
              <label class="block text-xs text-gray-600 mb-2">Quick Select:</label>
              <div class="grid grid-cols-4 gap-2">
                <button 
                  v-for="quickAmount in [500, 1000, 2000, 5000, 10000, 20000, 50000, 100000]"
                  :key="quickAmount"
                  type="button"
                  @click="depositForm.amount = quickAmount"
                  class="px-3 py-2 text-xs font-semibold border-2 border-blue-300 text-blue-700 rounded-lg hover:bg-blue-50 transition-colors duration-200"
                >
                  {{ quickAmount >= 1000 ? (quickAmount / 1000) + 'K' : quickAmount }}
                </button>
              </div>
            </div>

            <div v-if="depositForm.operator_id && depositForm.selected_percentage && getSelectedDepositOperator()?.business_model === 'deposit_bonus'" 
                 class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-300 rounded-lg p-4 space-y-2">
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-green-800">💰 Deposit Bonus Calculation</span>
                <span class="px-3 py-1 bg-green-600 text-white text-xs font-bold rounded-full">
                  {{ depositForm.selected_percentage }}%
                </span>
              </div>
              
              <div v-if="depositForm.amount && depositForm.amount > 0" class="space-y-2 pt-2 border-t border-green-200">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-700">Deposit Amount:</span>
                  <span class="font-semibold text-gray-900">Rs. {{ formatCurrency(depositForm.amount) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-green-700">Bonus Amount ({{ depositForm.selected_percentage }}%):</span>
                  <span class="font-semibold text-green-700">
                    + Rs. {{ formatCurrency((parseFloat(depositForm.amount) * parseFloat(depositForm.selected_percentage)) / 100) }}
                  </span>
                </div>
                <div class="flex justify-between text-lg font-bold pt-2 border-t-2 border-green-300">
                  <span class="text-green-900">Total Credit:</span>
                  <span class="text-green-900">
                    Rs. {{ calculateTotalCredit(depositForm.amount, depositForm.selected_percentage) }}
                  </span>
                </div>
              </div>
              
              <p v-else class="text-sm text-green-700 italic">
                Enter amount to see bonus calculation
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
              <textarea 
                v-model="depositForm.notes"
                rows="2"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Add any notes..."
              ></textarea>
            </div>

            <button 
              type="submit"
              :disabled="depositForm.processing"
              class="w-full px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
            >
              {{ depositForm.processing ? 'Processing...' : '💳 Confirm Deposit' }}
            </button>
          </form>
        </div>

        <!-- Sell Reload Section - Right Side -->
        <div class="bg-white rounded-xl shadow-lg p-6">
          <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
            <span class="text-3xl mr-3">📱</span>
            Sell Reload
          </h2>
          
          <form @submit.prevent="submitSell" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Select Operator *</label>
              <select 
                v-model="sellForm.operator_id"
                @change="loadPackagesForSell"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Choose operator</option>
                <option v-for="operator in operators" :key="operator.id" :value="operator.id">
                  {{ operator.name }} - Balance: Rs. {{ formatCurrency(getWalletBalance(operator.id)) }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Select Package *</label>
              
              <!-- Searchable Package Selector -->
              <div class="relative">
                <input 
                  v-model="packageSearchQuery"
                  @focus="showPackageDropdown = true"
                  @blur="hidePackageDropdown"
                  type="text"
                  required
                  :disabled="!sellForm.operator_id"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100"
                  :placeholder="!sellForm.operator_id ? 'Select operator first' : 'Search packages...'"
                />
                
                <!-- Dropdown List -->
                <div 
                  v-if="showPackageDropdown && sellForm.operator_id && filteredPackages.length > 0"
                  class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                >
                  <div
                    v-for="pkg in filteredPackages"
                    :key="pkg.id"
                    @mousedown="selectPackage(pkg)"
                    class="px-4 py-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors duration-150"
                  >
                    <div class="flex justify-between items-center">
                      <span class="font-medium text-gray-900">{{ pkg.name }}</span>
                      <span class="text-blue-600 font-semibold">Rs. {{ formatCurrency(pkg.face_value) }}</span>
                    </div>
                    <p v-if="pkg.description" class="text-xs text-gray-500 mt-1">{{ pkg.description }}</p>
                  </div>
                </div>
                
                <!-- No results -->
                <div 
                  v-if="showPackageDropdown && packageSearchQuery && filteredPackages.length === 0"
                  class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg p-4 text-center text-gray-500"
                >
                  No packages found matching "{{ packageSearchQuery }}"
                </div>
              </div>
              
              <p v-if="selectedPackageDisplay" class="text-xs text-green-600 mt-1 flex items-center">
                ✓ Selected: {{ selectedPackageDisplay.name }} - Rs. {{ formatCurrency(selectedPackageDisplay.face_value) }}
              </p>
              <p v-else-if="reloadPackages.length > 0" class="text-xs text-gray-500 mt-1">
                {{ reloadPackages.length }} package(s) available
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Mobile Number (MSISDN) *</label>
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
              <div class="space-y-1">
                <div class="flex justify-between text-sm">
                  <span class="text-blue-700">Face Value:</span>
                  <span class="font-semibold text-blue-900">Rs. {{ formatCurrency(quoteData.face_value) }}</span>
                </div>
                <div v-if="quoteData.commission_amount > 0" class="flex justify-between text-sm">
                  <span class="text-blue-700">Commission:</span>
                  <span class="font-semibold text-green-600">Rs. {{ formatCurrency(quoteData.commission_amount) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-blue-700 font-semibold">Net Cost:</span>
                  <span class="font-semibold text-blue-900">Rs. {{ formatCurrency(quoteData.net_cost) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-blue-700">Balance After:</span>
                  <span class="font-semibold" :class="quoteData.balance_after >= 0 ? 'text-green-600' : 'text-red-600'">
                    Rs. {{ formatCurrency(quoteData.balance_after) }}
                  </span>
                </div>
              </div>
              
              <div v-if="!quoteData.sufficient_balance" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-800 font-semibold">⚠️ Insufficient balance! Please deposit first.</p>
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

            <button 
              type="submit"
              :disabled="sellForm.processing || !quoteData?.sufficient_balance"
              class="w-full px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
            >
              {{ sellForm.processing ? 'Processing...' : '🚀 Confirm Sale' }}
            </button>
          </form>
        </div>
      </div>

      <!-- Recent Transactions Section -->
      <div class="bg-white rounded-2xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold text-gray-800">Recent Transactions</h2>
          <button
            @click="exportTransactionsPDF"
            :disabled="filteredTransactions.length === 0"
            class="px-4 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 flex items-center space-x-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span>Export PDF</span>
          </button>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
            <input 
              v-model="transactionFilters.from_date"
              type="date"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
            <input 
              v-model="transactionFilters.to_date"
              type="date"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Operator</label>
            <select 
              v-model="transactionFilters.operator_id"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Operators</option>
              <option v-for="operator in operators" :key="operator.id" :value="operator.id">
                {{ operator.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
            <select 
              v-model="transactionFilters.type"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Types</option>
              <option value="deposit">Deposit</option>
              <option value="sale">Sale</option>
              <option value="adjustment">Adjustment</option>
              <option value="refund">Refund</option>
            </select>
          </div>
        </div>

        <div class="flex items-center justify-between mb-4">
          <p class="text-sm text-gray-600">
            Showing {{ filteredTransactions.length }} of {{ recentTransactions.length }} transactions
          </p>
          <button
            v-if="transactionFilters.from_date || transactionFilters.to_date || transactionFilters.operator_id || transactionFilters.type"
            @click="clearFilters"
            class="text-sm text-blue-600 hover:text-blue-700 font-medium"
          >
            Clear Filters
          </button>
        </div>
        
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
              <tr v-for="transaction in filteredTransactions" :key="transaction.id" class="hover:bg-gray-50">
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

  <!-- Add/Edit SIM Provider Modal -->
  <Modal :show="showAddOperatorModal || showEditOperatorModal" @close="closeOperatorModal">
    <div class="p-6">
      <h3 class="text-2xl font-bold text-gray-900 mb-6">
        {{ showEditOperatorModal ? 'Edit SIM Provider' : 'Add New SIM Provider' }}
      </h3>
      
      <form @submit.prevent="submitOperator" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">SIM Provider Name *</label>
          <select 
            v-model="operatorForm.name"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">Choose SIM provider</option>
            <option value="Mobitel">Mobitel</option>
            <option value="Dialog">Dialog</option>
            <option value="Airtel">Airtel</option>
            <option value="Hutch">Hutch</option>
            <option value="SLT Mobitel">SLT Mobitel</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Business Model *</label>
          <select 
            v-model="operatorForm.business_model"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">Choose business model</option>
            <option value="deposit_bonus">Deposit Bonus</option>
            <option value="sale_commission">Sale Commission</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Percentage Rates *</label>
          <div class="space-y-2">
            <div v-for="(item, index) in operatorForm.percentages" :key="index" class="flex gap-2">
              <input 
                v-model="item.value"
                type="number"
                step="0.01"
                min="0"
                max="100"
                required
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="e.g., 4.00"
              />
              <button 
                v-if="operatorForm.percentages.length > 1"
                type="button"
                @click="removePercentage(index)"
                class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
              >
                ✕
              </button>
            </div>
            <button 
              type="button"
              @click="addPercentage"
              class="w-full px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors duration-200"
            >
              + Add Another Rate
            </button>
          </div>
          <p class="text-xs text-gray-500 mt-1">Add multiple rates (e.g., 4%, 5%, 6%) for flexible options</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
          <textarea 
            v-model="operatorForm.description"
            rows="2"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Add any notes..."
          ></textarea>
        </div>

        <div class="flex items-center">
          <input 
            v-model="operatorForm.is_active"
            type="checkbox"
            id="is_active"
            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
          />
          <label for="is_active" class="ml-2 text-sm font-medium text-gray-700">
            Active
          </label>
        </div>

        <div class="flex space-x-3 pt-4">
          <button 
            type="submit"
            :disabled="operatorForm.processing"
            class="flex-1 px-4 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
          >
            {{ operatorForm.processing ? 'Processing...' : (showEditOperatorModal ? 'Update Provider' : 'Add Provider') }}
          </button>
          <button 
            type="button"
            @click="closeOperatorModal"
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

const reloadPackages = ref([]);
const quoteData = ref(null);
const packageSearchQuery = ref('');
const showPackageDropdown = ref(false);
const selectedPackageDisplay = ref(null);

// Transaction filters
const transactionFilters = ref({
  from_date: '',
  to_date: '',
  operator_id: '',
  type: '',
});

const depositForm = ref({
  operator_id: null,
  amount: '',
  notes: '',
  selected_percentage: null, // Add selected percentage
  processing: false,
});

const sellForm = ref({
  operator_id: null,
  reload_package_id: '',
  msisdn: '',
  notes: '',
  processing: false,
});

// Operator management state
const showAddOperatorModal = ref(false);
const showEditOperatorModal = ref(false);
const selectedOperator = ref(null);

const operatorForm = ref({
  name: '',
  business_model: '',
  default_percentage: '',
  description: '',
  is_active: true,
  processing: false,
  percentages: [{ value: '' }], // Array for multiple percentages
});

// Helper methods to get wallet data for an operator
const getWallet = (operatorId) => {
  return props.wallets.find(w => w.operator_id === operatorId);
};

const getWalletBalance = (operatorId) => {
  const wallet = getWallet(operatorId);
  return wallet ? wallet.balance : 0;
};

const getSelectedDepositOperator = () => {
  return props.operators.find(op => op.id === depositForm.value.operator_id);
};

// Computed property for filtered packages
const filteredPackages = computed(() => {
  if (!packageSearchQuery.value) {
    return reloadPackages.value;
  }
  
  const query = packageSearchQuery.value.toLowerCase();
  return reloadPackages.value.filter(pkg => 
    pkg.name.toLowerCase().includes(query) || 
    pkg.face_value.toString().includes(query) ||
    (pkg.description && pkg.description.toLowerCase().includes(query))
  );
});

// Computed property for filtered transactions
const filteredTransactions = computed(() => {
  let transactions = [...props.recentTransactions];
  
  // Filter by date range
  if (transactionFilters.value.from_date) {
    transactions = transactions.filter(t => 
      new Date(t.transaction_date) >= new Date(transactionFilters.value.from_date)
    );
  }
  
  if (transactionFilters.value.to_date) {
    transactions = transactions.filter(t => 
      new Date(t.transaction_date) <= new Date(transactionFilters.value.to_date + ' 23:59:59')
    );
  }
  
  // Filter by operator
  if (transactionFilters.value.operator_id) {
    transactions = transactions.filter(t => 
      t.wallet_account.operator_id == transactionFilters.value.operator_id
    );
  }
  
  // Filter by type
  if (transactionFilters.value.type) {
    transactions = transactions.filter(t => 
      t.transaction_type === transactionFilters.value.type
    );
  }
  
  return transactions;
});

const clearFilters = () => {
  transactionFilters.value = {
    from_date: '',
    to_date: '',
    operator_id: '',
    type: '',
  };
};

const exportTransactionsPDF = async () => {
  try {
    const response = await axios.post('/api/wallet/transactions/export-pdf', {
      from_date: transactionFilters.value.from_date,
      to_date: transactionFilters.value.to_date,
      operator_id: transactionFilters.value.operator_id,
      type: transactionFilters.value.type,
    }, {
      responseType: 'blob'
    });
    
    // Create a blob from the PDF data
    const blob = new Blob([response.data], { type: 'application/pdf' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    
    // Generate filename with date range
    const filename = `wallet_transactions_${transactionFilters.value.from_date || 'all'}_to_${transactionFilters.value.to_date || 'now'}.pdf`;
    link.setAttribute('download', filename);
    
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    alert('Failed to export PDF: ' + (error.response?.data?.message || error.message));
  }
};

const loadPackagesForSell = async () => {
  if (!sellForm.value.operator_id) {
    reloadPackages.value = [];
    packageSearchQuery.value = ''; // Reset search
    selectedPackageDisplay.value = null;
    return;
  }
  
  try {
    const response = await axios.get(`/api/wallet/packages?operator_id=${sellForm.value.operator_id}`);
    reloadPackages.value = response.data.packages;
    packageSearchQuery.value = ''; // Reset search when loading new packages
    selectedPackageDisplay.value = null;
  } catch (error) {
    console.error('Failed to load packages:', error);
  }
};

const selectPackage = (pkg) => {
  sellForm.value.reload_package_id = pkg.id;
  selectedPackageDisplay.value = pkg;
  packageSearchQuery.value = pkg.name;
  showPackageDropdown.value = false;
  getQuote();
};

const hidePackageDropdown = () => {
  setTimeout(() => {
    showPackageDropdown.value = false;
  }, 200);
};

const submitDeposit = async () => {
  depositForm.value.processing = true;
  
  try {
    const response = await axios.post('/api/wallet/deposit', depositForm.value);
    
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
    const response = await axios.post('/api/wallet/quote', {
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
    const response = await axios.post('/api/wallet/sell', sellForm.value);
    
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

const getOperatorBadgeColor = (code) => {
  const colors = {
    'MOB': 'bg-yellow-100 text-yellow-800',
    'DIA': 'bg-red-100 text-red-800',
    'AIR': 'bg-red-100 text-red-900',
    'HUT': 'bg-purple-100 text-purple-800',
  };
  return colors[code] || 'bg-blue-100 text-blue-800';
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

const calculateTotalCredit = (amount, percentage) => {
  if (!amount || !percentage) return '0.00';
  
  const bonus = (parseFloat(amount) * parseFloat(percentage)) / 100;
  
  return formatCurrency(parseFloat(amount) + bonus);
};

// Get bonus percentage based on deposit amount (tiered rates)
const getBonusPercentageByAmount = (operatorId, amount) => {
  const operator = props.operators.find(op => op.id === operatorId);
  
  if (!operator || operator.business_model !== 'deposit_bonus') {
    return 0;
  }
  
  const depositAmount = parseFloat(amount || 0);
  
  // Tiered bonus rates
  if (depositAmount >= 5000) {
    return 6.00; // 6% for deposits >= 5000
  } else if (depositAmount >= 3000) {
    return 5.00; // 5% for deposits >= 3000
  } else if (depositAmount >= 1000) {
    return 4.00; // 4% for deposits >= 1000
  } else {
    return operator.default_percentage || 0; // Default rate for amounts < 1000
  }
};

// Calculate bonus amount based on tiered rates
const calculateBonusAmount = (operatorId, amount) => {
  if (!amount) return '0.00';
  
  const percentage = getBonusPercentageByAmount(operatorId, amount);
  const bonus = (parseFloat(amount) * percentage) / 100;
  
  return formatCurrency(bonus);
};

// Calculate total credit with tiered bonus
const calculateTotalCreditWithBonus = (operatorId, amount) => {
  if (!amount) return '0.00';
  
  const percentage = getBonusPercentageByAmount(operatorId, amount);
  const bonus = (parseFloat(amount) * percentage) / 100;
  
  return formatCurrency(parseFloat(amount) + bonus);
};

// Operator management functions
const addPercentage = () => {
  operatorForm.value.percentages.push({ value: '' });
};

const removePercentage = (index) => {
  operatorForm.value.percentages.splice(index, 1);
};

const openAddOperatorModal = () => {
  resetOperatorForm();
  showAddOperatorModal.value = true;
};

const openEditOperatorModal = (operator) => {
  selectedOperator.value = operator;
  
  // Load operator's existing rates
  const percentages = operator.rates && operator.rates.length > 0
    ? operator.rates.map(rate => ({ value: rate.percentage }))
    : [{ value: operator.default_percentage }];
  
  operatorForm.value = {
    name: operator.name,
    business_model: operator.business_model,
    default_percentage: operator.default_percentage,
    description: operator.description || '',
    is_active: operator.is_active,
    processing: false,
    percentages: percentages,
  };
  showEditOperatorModal.value = true;
};

const closeOperatorModal = () => {
  showAddOperatorModal.value = false;
  showEditOperatorModal.value = false;
  selectedOperator.value = null;
  resetOperatorForm();
};

const resetOperatorForm = () => {
  operatorForm.value = {
    name: '',
    business_model: '',
    default_percentage: '',
    description: '',
    is_active: true,
    processing: false,
    percentages: [{ value: '' }],
  };
};

const submitOperator = async () => {
  operatorForm.value.processing = true;
  
  try {
    // Extract percentage values and set default_percentage as the first one
    const percentageValues = operatorForm.value.percentages
      .map(p => parseFloat(p.value))
      .filter(p => !isNaN(p) && p > 0);
    
    if (percentageValues.length === 0) {
      alert('Please add at least one percentage rate');
      operatorForm.value.processing = false;
      return;
    }
    
    const dataToSubmit = {
      name: operatorForm.value.name,
      business_model: operatorForm.value.business_model,
      default_percentage: percentageValues[0], // First percentage as default
      description: operatorForm.value.description,
      is_active: operatorForm.value.is_active,
      percentages: percentageValues, // Send all percentages
    };
    
    const url = showEditOperatorModal.value 
      ? `/operators/${selectedOperator.value.id}` 
      : '/operators';
    
    const method = showEditOperatorModal.value ? 'put' : 'post';
    
    const response = await axios[method](url, dataToSubmit);
    
    if (response.data.success) {
      alert(response.data.message);
      window.location.reload();
    }
  } catch (error) {
    alert('Operation failed: ' + (error.response?.data?.message || error.message));
  } finally {
    operatorForm.value.processing = false;
  }
};

const confirmDeleteOperator = async (operator) => {
  if (!confirm(`Are you sure you want to delete ${operator.name}? This action cannot be undone.`)) {
    return;
  }
  
  try {
    const response = await axios.delete(`/operators/${operator.id}`);
    
    if (response.data.success) {
      alert(response.data.message);
      window.location.reload();
    }
  } catch (error) {
    alert('Delete failed: ' + (error.response?.data?.message || error.message));
  }
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
