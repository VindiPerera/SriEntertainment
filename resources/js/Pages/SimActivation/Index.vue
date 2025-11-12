<template>
  <Head title="SIM Activation" />
  <Banner />

  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-24 px-16">
    <Header />

    <div class="w-full md:w-5/6 py-12 space-y-8 md:px-0 px-6 mx-auto">
      <!-- Page Header -->
      <div class="flex items-center justify-between mb-8">
        <div class="flex items-center space-x-4">
          <Link href="/">
            <img src="/images/back-arrow.png" class="w-14 h-14" alt="Back" />
          </Link>
          <h1 class="text-4xl font-bold tracking-wide text-gray-800">
            📱 SIM Activation & Reload
          </h1>
        </div>
      </div>

      <!-- Tab Navigation -->
      <div class="flex space-x-2 border-b-2 border-gray-300 mb-8">
        <button
          @click="activeTab = 'transaction'"
          :class="[
            'px-8 py-4 text-xl font-bold tracking-wide transition-all duration-200',
            activeTab === 'transaction'
              ? 'bg-blue-600 text-white border-b-4 border-blue-800'
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          ]"
        >
          🔄 New Transaction
        </button>
        <button
          @click="activeTab = 'packages'"
          :class="[
            'px-8 py-4 text-xl font-bold tracking-wide transition-all duration-200',
            activeTab === 'packages'
              ? 'bg-orange-600 text-white border-b-4 border-orange-800'
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          ]"
        >
          📦 SIM Activation Packages
        </button>
      </div>

      <!-- Transaction Tab Content -->
      <div v-show="activeTab === 'transaction'">
      <div class="bg-white rounded-2xl shadow-xl p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">New Transaction</h2>

        <form @submit.prevent="processTransaction" class="space-y-6">
          <!-- Select Operator -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Operator *</label>
            <select 
              v-model="form.operator_name"
              @change="onOperatorChange"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">Choose an operator...</option>
              <option v-for="operator in uniqueOperators" :key="operator.id" :value="operator.name">
                {{ operator.name }}
              </option>
            </select>
          </div>

          <!-- Select SIM (Optional) -->
          <div v-if="form.operator_name">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Select SIM (Optional - for activation only)
            </label>
            <select 
              v-model="form.sim_stock_id"
              @change="onSimChange"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">No SIM (Reload only)</option>
              <option v-for="sim in filteredSims" :key="sim.id" :value="sim.id">
                {{ sim.sim_name }}
              </option>
            </select>
          </div>

          <!-- Select Package -->
          <div v-if="form.operator_name">
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Package *</label>
            <select 
              v-model="form.pricing_rule_id"
              @change="getPreview"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">Choose a package...</option>
              <option v-for="rule in availablePackages" :key="rule.id" :value="rule.id">
                Rs. {{ formatCurrency(rule.face_value) }}
                <template v-if="rule.seller_discount_percent > 0"> - {{ rule.seller_discount_percent }}% discount</template>
                <template v-if="rule.extra_benefit > 0"> + Rs. {{ rule.extra_benefit }} benefit</template>
              </option>
            </select>
          </div>

          <!-- Mobile Number -->
          <div v-if="form.pricing_rule_id">
            <label class="block text-sm font-medium text-gray-700 mb-2">Mobile Number</label>
            <input 
              v-model="form.mobile_number"
              type="text"
              maxlength="10"
              placeholder="07XXXXXXXX"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
            <textarea 
              v-model="form.notes"
              rows="3"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Add any notes..."
            ></textarea>
          </div>

          <!-- Preview Box -->
          <div v-if="preview" class="bg-gradient-to-br from-blue-50 to-green-50 rounded-xl p-6 border-2 border-blue-200">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
              <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              Transaction Preview
            </h3>

            <div class="space-y-3">
              <!-- Ledger Lines -->
              <div v-for="(line, index) in preview.ledger_lines" :key="index" class="flex justify-between items-center py-2 border-b border-blue-100 last:border-b-0">
                <span class="text-sm font-medium" :class="{
                  'text-gray-700': line.line_type === 'info',
                  'text-green-700': line.line_type === 'discount' || line.line_type === 'wallet_change' && line.amount > 0,
                  'text-red-700': line.line_type === 'wallet_change' && line.amount < 0,
                  'text-blue-700': line.line_type === 'profit'
                }">
                  {{ line.description }}
                </span>
                <span v-if="line.amount !== null" class="text-sm font-bold" :class="{
                  'text-green-600': line.amount > 0 && line.line_type !== 'profit',
                  'text-red-600': line.amount < 0,
                  'text-blue-600': line.line_type === 'profit'
                }">
                  Rs. {{ formatCurrency(Math.abs(line.amount)) }}
                </span>
              </div>

              <!-- Summary -->
              <div class="mt-4 pt-4 border-t-2 border-blue-300">
                <div class="grid grid-cols-2 gap-4">
                  <div class="text-center p-3 bg-white rounded-lg">
                    <p class="text-xs text-gray-600 mb-1">Package Profit</p>
                    <p class="text-lg font-bold text-blue-600">Rs. {{ formatCurrency(preview.package_profit) }}</p>
                  </div>
                  <div v-if="form.sim_stock_id" class="text-center p-3 bg-white rounded-lg">
                    <p class="text-xs text-gray-600 mb-1">SIM Profit</p>
                    <p class="text-lg font-bold text-green-600">Rs. {{ formatCurrency(preview.sim_profit) }}</p>
                  </div>
                  <div class="text-center p-3 bg-gradient-to-r from-blue-500 to-green-500 rounded-lg text-white" :class="form.sim_stock_id ? '' : 'col-span-2'">
                    <p class="text-xs mb-1">Total Profit</p>
                    <p class="text-2xl font-bold">Rs. {{ formatCurrency(preview.total_profit) }}</p>
                  </div>
                  <div v-if="form.sim_stock_id" class="text-center p-3 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg text-white">
                    <p class="text-xs mb-1">Wallet Change</p>
                    <p class="text-2xl font-bold">{{ preview.wallet_change >= 0 ? '+' : '' }}Rs. {{ formatCurrency(preview.wallet_change) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex space-x-4">
            <button
              type="submit"
              :disabled="processing || !form.pricing_rule_id"
              class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-green-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-green-700 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
            >
              <svg v-if="!processing" class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              <span v-if="processing">Processing...</span>
              <span v-else>{{ form.sim_stock_id ? 'Activate SIM & Process' : 'Process Reload' }}</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Recent Transactions -->
      <div class="bg-white rounded-2xl shadow-xl p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Recent Transactions</h2>
        
        <div v-if="recentTransactions.length === 0" class="text-center py-12 text-gray-500">
          <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          <p class="text-lg">No transactions yet</p>
        </div>

        <div v-else class="space-y-4">
          <div 
            v-for="txn in recentTransactions.slice(0, 10)" 
            :key="txn.id"
            class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200"
          >
            <div class="flex justify-between items-start mb-3">
              <div>
                <p class="font-semibold text-gray-900">{{ txn.transaction_number }}</p>
                <p class="text-sm text-gray-600">{{ txn.operator?.name }} - Rs. {{ formatCurrency(txn.package_face_value) }}</p>
                <p class="text-xs text-gray-500">{{ formatDate(txn.transaction_date) }}</p>
              </div>
              <div class="text-right">
                <p class="text-sm font-semibold text-blue-600">Profit: Rs. {{ formatCurrency(txn.total_profit) }}</p>
                <p class="text-xs" :class="txn.wallet_change >= 0 ? 'text-green-600' : 'text-red-600'">
                  Wallet: {{ txn.wallet_change >= 0 ? '+' : '' }}Rs. {{ formatCurrency(txn.wallet_change) }}
                </p>
              </div>
            </div>
            <p class="text-xs text-gray-600 italic">{{ txn.matched_rule_description }}</p>
          </div>
        </div>
      </div>
      </div>
      <!-- End Transaction Tab -->

      <!-- Packages Tab Content -->
      <div v-show="activeTab === 'packages'" class="space-y-6">
        <!-- Add Rule Button -->
        <div class="flex justify-end">
          <button @click="openAddRuleModal" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium shadow-lg transition-all duration-200 hover:shadow-xl">
            + Add New Pricing Rule
          </button>
        </div>

        <!-- Operator Tabs -->
        <div class="flex space-x-2 border-b border-gray-200">
          <button 
            v-for="op in ['Dialog', 'Mobitel', 'Airtel', 'Hutch']" 
            :key="op"
            @click="selectedOperator = op"
            :class="selectedOperator === op 
              ? 'border-b-2 border-red-600 text-red-600 font-semibold' 
              : 'text-gray-600 hover:text-gray-800'"
            class="px-6 py-3 font-medium transition-colors"
          >
            {{ op }}
          </button>
        </div>

        <!-- Empty State -->
        <div v-if="getOperatorRules(selectedOperator).length === 0" class="text-center py-16">
          <svg class="w-20 h-20 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
          </svg>
          <p class="text-lg font-medium text-gray-900">No pricing rules for {{ selectedOperator }}</p>
          <p class="text-sm text-gray-500 mt-2">Click "Add New Pricing Rule" to create one.</p>
        </div>

        <!-- Pricing Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div 
            v-for="rule in getOperatorRules(selectedOperator)" 
            :key="rule.id"
            :class="!rule.is_active ? 'opacity-60' : ''"
            class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition-shadow duration-200"
          >
            <!-- Card Header with Gradient -->
            <div :class="getOperatorGradient(selectedOperator)" class="px-4 py-2 text-white">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <div class="bg-white bg-opacity-20 p-1.5 rounded">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                  </div>
                  <h3 class="text-sm font-bold">{{ selectedOperator }}</h3>
                </div>
                <span :class="rule.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                      class="px-2 py-0.5 text-xs font-semibold rounded-full">
                  {{ rule.is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>
            </div>

            <!-- Card Body -->
            <div class="p-3 space-y-2">
              <!-- Face Value -->
              <div class="text-center pb-2 border-b border-gray-200">
                <p class="text-xs text-gray-500">Face Value</p>
                <p v-if="rule.face_value" class="text-xl font-bold text-gray-900">Rs. {{ rule.face_value }}</p>
                <p v-else class="text-base font-semibold text-gray-500 italic">Default</p>
              </div>

              <!-- Pricing Details Grid -->
              <div class="grid grid-cols-2 gap-2">
                <div class="bg-red-50 rounded p-2">
                  <p class="text-xs text-gray-600">Fixed</p>
                  <p class="text-sm font-bold text-red-600">Rs. {{ rule.seller_discount_flat }}</p>
                </div>
                <div class="bg-orange-50 rounded p-2">
                  <p class="text-xs text-gray-600">Discount</p>
                  <p class="text-sm font-bold text-orange-600">{{ rule.seller_discount_percent }}%</p>
                </div>
              </div>

              <!-- Extra Benefit -->
              <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded p-2">
                <p class="text-xs text-gray-600">Extra Benefit</p>
                <p class="text-sm font-bold text-green-600">Rs. {{ rule.extra_benefit }}</p>
              </div>

              <!-- Description if exists -->
              <div v-if="rule.rule_description" class="text-xs text-gray-600 italic bg-gray-50 p-2 rounded">
                {{ rule.rule_description }}
              </div>
            </div>

            <!-- Card Footer Actions -->
            <div class="px-3 py-2 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
              <button 
                @click="openEditRuleModal(rule)" 
                class="flex items-center space-x-1 text-blue-600 hover:text-blue-800 text-xs font-medium"
              >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span>Edit</span>
              </button>
              
              <button 
                @click="toggleRuleActive(rule)" 
                :class="rule.is_active ? 'text-yellow-600 hover:text-yellow-800' : 'text-green-600 hover:text-green-800'"
                class="flex items-center space-x-1 text-xs font-medium"
              >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                <span>{{ rule.is_active ? 'Off' : 'On' }}</span>
              </button>
              
              <button 
                @click="deleteRule(rule)" 
                class="flex items-center space-x-1 text-red-600 hover:text-red-800 text-xs font-medium"
              >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <span>Del</span>
              </button>
            </div>
          </div>
        </div>
      </div>
      <!-- End Packages Tab -->
    </div>
  </div>

  <!-- Add/Edit Pricing Rule Modal -->
  <div v-if="showRuleFormModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[110] p-4">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-2xl">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">{{ editingRule ? 'Edit Pricing Rule' : 'Add New Pricing Rule' }}</h2>
      
      <form @submit.prevent="saveRule">
        <div class="space-y-5">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Operator (from SIM Stock) *</label>
            <select v-model="ruleForm.operator_name" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
              <option value="">Select Operator</option>
              <option v-for="sim in simStocks" :key="sim.id" :value="sim.sim_name">{{ sim.sim_name }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Face Value (Rs.)</label>
            <input v-model="ruleForm.face_value" type="number" step="0.01" min="0" 
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" 
                   placeholder="100, 699, or leave empty for all packages">
            <p class="text-xs text-gray-500 mt-1.5">Leave empty to apply this rule to all package amounts</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Fixed Discount (Rs.)</label>
              <input v-model="ruleForm.seller_discount_flat" type="number" step="0.01" min="0" 
                     class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" 
                     placeholder="80">
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Percentage Discount (%)</label>
              <input v-model="ruleForm.seller_discount_percent" type="number" step="0.01" min="0" max="100" 
                     class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" 
                     placeholder="10">
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Extra Benefit (Rs.)</label>
            <input v-model="ruleForm.extra_benefit" type="number" step="0.01" min="0" 
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" 
                   placeholder="100">
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Description (Optional)</label>
            <textarea v-model="ruleForm.rule_description" rows="2" 
                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" 
                      placeholder="Brief description of this rule"></textarea>
          </div>

          <div class="flex items-center">
            <input v-model="ruleForm.is_active" type="checkbox" id="rule_active" class="w-4 h-4 text-blue-600 border-gray-300 rounded">
            <label for="rule_active" class="ml-2 text-sm font-medium text-gray-700">Active</label>
          </div>
        </div>

        <div class="flex justify-end gap-3 mt-8 pt-6 border-t">
          <button type="button" @click="closeRuleFormModal" class="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
            Cancel
          </button>
          <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium" :disabled="savingRule">
            {{ savingRule ? 'Saving...' : 'Save Rule' }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Success Modal -->
  <SimActivationSuccessModal 
    v-if="completedTransaction"
    :open="showSuccessModal" 
    @update:open="showSuccessModal = $event"
    :transaction="completedTransaction"
    :cashier="$page.props.auth.user"
  />

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
          :class="confirmModalData.type === 'danger' ? 'bg-gradient-to-r from-pink-400 to-pink-500 hover:from-pink-500 hover:to-pink-600' : 'bg-gradient-to-r from-pink-400 to-pink-500 hover:from-pink-500 hover:to-pink-600'"
          class="px-8 py-3 text-white rounded-full font-medium transition-all duration-200 shadow-lg"
        >
          {{ confirmModalData.confirmText }}
        </button>
      </div>
    </div>
  </div>

  <Footer />
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Header from '@/Components/custom/Header.vue';
import Footer from '@/Components/custom/Footer.vue';
import Banner from '@/Components/Banner.vue';
import SimActivationSuccessModal from '@/Components/custom/SimActivationSuccessModal.vue';
import axios from 'axios';

const props = defineProps({
  simStocks: Array,
  wallets: Object,
});

const form = ref({
  operator_name: '',
  sim_stock_id: '',
  pricing_rule_id: '',
  mobile_number: '',
  package_revenue: null,
  sim_cost: null,
  sim_revenue: null,
  notes: '',
});

const preview = ref(null);
const processing = ref(false);
const recentTransactions = ref([]);

// Tab state
const activeTab = ref('transaction');
const selectedOperator = ref('Dialog');

// Modal states
const showRuleFormModal = ref(false);
const showSuccessModal = ref(false);
const completedTransaction = ref(null);

// Confirmation modal states
const showConfirmModal = ref(false);
const confirmModalData = ref({
  title: '',
  message: '',
  onConfirm: null,
  confirmText: 'OK',
  cancelText: 'Cancel',
  type: 'warning' // warning, danger, info
});

// Data states
const pricingRules = ref([]);
const operatorNames = ref([]);
const editingRule = ref(null);
const savingRule = ref(false);

// Rule form
const ruleForm = ref({
  operator_name: '',
  face_value: null,
  transaction_type: 'sim_activation',
  seller_discount_flat: 0,
  seller_discount_percent: 0,
  extra_benefit: 0,
  package_cost_override: null,
  is_active: true,
  rule_description: '',
});

// Get unique operators from simStocks
const uniqueOperators = computed(() => {
  const unique = [];
  const seen = new Set();
  props.simStocks.forEach(sim => {
    if (!seen.has(sim.sim_name)) {
      seen.add(sim.sim_name);
      unique.push({ id: sim.id, name: sim.sim_name });
    }
  });
  return unique;
});

const availablePackages = computed(() => {
  if (!form.value.operator_name) return [];
  // Filter pricing rules by selected operator name
  return pricingRules.value.filter(rule => 
    rule.operator_name === form.value.operator_name && rule.is_active
  );
});

const filteredSims = computed(() => {
  if (!form.value.operator_name) return [];
  
  // Filter SIMs by sim_name matching selected operator and stock > 0
  return props.simStocks.filter(sim => 
    sim.sim_name === form.value.operator_name && sim.stock > 0
  );
});

const getWalletBalance = (operatorId) => {
  const wallet = props.wallets[operatorId];
  return wallet ? wallet.balance : 0;
};

const formatCurrency = (amount) => {
  return parseFloat(amount || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
};

const formatDate = (date) => {
  return new Date(date).toLocaleString();
};

// Helper functions for operator cards
const getOperatorRules = (operatorName) => {
  return pricingRules.value.filter(rule => rule.operator_name === operatorName);
};

const getOperatorGradient = (operatorName) => {
  const gradients = {
    'Dialog': 'bg-gradient-to-r from-red-600 to-pink-600',
    'Mobitel': 'bg-gradient-to-r from-blue-600 to-indigo-600',
    'Airtel': 'bg-gradient-to-r from-red-500 to-orange-500',
    'Hutch': 'bg-gradient-to-r from-yellow-500 to-amber-600'
  };
  return gradients[operatorName] || 'bg-gradient-to-r from-gray-600 to-gray-700';
};

const onSimChange = () => {
  // Auto-populate SIM cost and revenue from stock when SIM is selected
  if (form.value.sim_stock_id) {
    const selectedSim = props.simStocks.find(sim => sim.id == form.value.sim_stock_id);
    if (selectedSim) {
      form.value.sim_cost = parseFloat(selectedSim.cost_price || 0);
      form.value.sim_revenue = parseFloat(selectedSim.selling_price || 0);
    }
  } else {
    form.value.sim_cost = null;
    form.value.sim_revenue = null;
  }
  getPreview();
};

const onOperatorChange = () => {
  form.value.sim_stock_id = '';
  form.value.pricing_rule_id = '';
  preview.value = null;
};

const getPreview = async () => {
  if (!form.value.pricing_rule_id) {
    preview.value = null;
    return;
  }

  try {
    const response = await axios.post('/api/sim-activation/preview', form.value);
    if (response.data.success) {
      preview.value = response.data.preview;
    }
  } catch (error) {
    console.error('Preview failed:', error);
    alert('Failed to get preview: ' + (error.response?.data?.message || error.message));
  }
};

const processTransaction = async () => {
  processing.value = true;
  
  try {
    const response = await axios.post('/api/sim-activation', form.value);
    
    if (response.data.success) {
      // Store the completed transaction
      completedTransaction.value = response.data.transaction;
      
      // Show success modal
      showSuccessModal.value = true;
      
      // Reset form
      form.value = {
        operator_name: '',
        sim_stock_id: '',
        pricing_rule_id: '',
        mobile_number: '',
        package_revenue: null,
        sim_cost: null,
        sim_revenue: null,
        notes: '',
      };
      preview.value = null;
      
      // Reload recent transactions
      await loadRecentTransactions();
    }
  } catch (error) {
    alert('Transaction failed: ' + (error.response?.data?.message || error.message));
  } finally {
    processing.value = false;
  }
};

// Load recent transactions on mount
const loadRecentTransactions = async () => {
  try {
    const response = await axios.get('/api/sim-activation/history?per_page=10');
    recentTransactions.value = response.data.data || [];
  } catch (error) {
    console.error('Failed to load transactions:', error);
  }
};

// Load pricing rules
const loadPricingRules = async () => {
  try {
    const response = await axios.get('/api/operator-pricing-rules');
    pricingRules.value = response.data.rules || [];
    operatorNames.value = response.data.operatorNames || [];
  } catch (error) {
    console.error('Failed to load pricing rules:', error);
  }
};

// Pricing rule management functions
const openAddRuleModal = () => {
  editingRule.value = null;
  ruleForm.value = {
    operator_name: '',
    face_value: null,
    transaction_type: 'sim_activation',
    seller_discount_flat: 0,
    seller_discount_percent: 0,
    extra_benefit: 0,
    package_cost_override: null,
    is_active: true,
    rule_description: '',
  };
  showRuleFormModal.value = true;
};

const openEditRuleModal = (rule) => {
  editingRule.value = rule;
  ruleForm.value = {
    operator_name: rule.operator_name,
    face_value: rule.face_value,
    transaction_type: rule.transaction_type,
    seller_discount_flat: rule.seller_discount_flat,
    seller_discount_percent: rule.seller_discount_percent,
    extra_benefit: rule.extra_benefit,
    package_cost_override: rule.package_cost_override,
    is_active: rule.is_active,
    rule_description: rule.rule_description,
  };
  showRuleFormModal.value = true;
};

const closeRuleFormModal = () => {
  showRuleFormModal.value = false;
  editingRule.value = null;
};

const saveRule = async () => {
  savingRule.value = true;
  try {
    // Auto-set rule_type based on face_value
    const ruleData = { ...ruleForm.value };
    ruleData.rule_type = ruleData.face_value ? 'exact' : 'default';
    
    const url = editingRule.value 
      ? `/api/operator-pricing-rules/${editingRule.value.id}`
      : '/api/operator-pricing-rules';
    
    const method = editingRule.value ? 'put' : 'post';
    
    const response = await axios[method](url, ruleData);
    
    if (response.data.success) {
      alert(response.data.message);
      closeRuleFormModal();
      await loadPricingRules();
    }
  } catch (error) {
    alert('Error: ' + (error.response?.data?.message || 'Failed to save pricing rule'));
  } finally {
    savingRule.value = false;
  }
};

const toggleRuleActive = async (rule) => {
  confirmModalData.value = {
    title: 'Confirm Action',
    message: `Are you sure you want to ${rule.is_active ? 'deactivate' : 'activate'} this pricing rule?`,
    confirmText: 'OK',
    cancelText: 'Cancel',
    type: 'warning',
    onConfirm: async () => {
      try {
        const response = await axios.post(`/api/operator-pricing-rules/${rule.id}/toggle-active`);
        if (response.data.success) {
          await loadPricingRules();
        }
      } catch (error) {
        console.error('Error toggling rule:', error);
      }
    }
  };
  showConfirmModal.value = true;
};

const deleteRule = async (rule) => {
  confirmModalData.value = {
    title: 'Confirm Delete',
    message: 'Are you sure you want to delete this pricing rule? This cannot be undone.',
    confirmText: 'OK',
    cancelText: 'Cancel',
    type: 'danger',
    onConfirm: async () => {
      try {
        const response = await axios.delete(`/api/operator-pricing-rules/${rule.id}`);
        if (response.data.success) {
          await loadPricingRules();
        }
      } catch (error) {
        console.error('Error deleting rule:', error);
      }
    }
  };
  showConfirmModal.value = true;
};

loadRecentTransactions();
loadPricingRules();
</script>
