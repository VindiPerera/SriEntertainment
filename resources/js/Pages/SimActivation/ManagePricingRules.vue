<template>
  <Head title="Manage Pricing Rules" />
  
  <Header />
  <Banner />

  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Operator Pricing Rules</h1>
          <p class="mt-1 text-sm text-gray-600">Configure seller discounts and wallet calculations</p>
        </div>
        <div class="flex gap-3">
          <button @click="showGuide = !showGuide" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
            {{ showGuide ? 'Hide' : 'Show' }} Guide
          </button>
          <Link :href="route('sim-activation-packages.index')" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            ← Back to Packages
          </Link>
          <button @click="openAddModal" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            + Add New Rule
          </button>
        </div>
      </div>

      <!-- Guide -->
      <div v-if="showGuide" class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
        <h2 class="text-xl font-bold text-blue-900 mb-4">📚 How to Add Pricing Rules (Simple Guide)</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
          <div class="bg-white p-4 rounded">
            <h3 class="font-bold mb-2">🎯 What is a Pricing Rule?</h3>
            <p class="text-gray-700">
              It defines <strong>how much the seller earns</strong> and <strong>how much deducts from wallet</strong> when selling a package.
            </p>
          </div>
          
          <div class="bg-white p-4 rounded">
            <h3 class="font-bold mb-2">📝 Two Types:</h3>
            <ul class="space-y-1 text-gray-700">
              <li><strong>Specific Amount:</strong> Enter face value (e.g., 100, 699, 1249)</li>
              <li><strong>Default Rule:</strong> Leave face value empty - applies to all</li>
            </ul>
          </div>

          <div class="bg-white p-4 rounded col-span-2">
            <h3 class="font-bold mb-2">💰 Fields Explained:</h3>
            <ul class="space-y-2 text-gray-700">
              <li><strong>Seller Discount (Fixed):</strong> Fixed amount seller gets (e.g., Rs. 80)</li>
              <li><strong>Seller Discount (%):</strong> Percentage of package value (e.g., 50 = 50%)</li>
              <li><strong>Extra Benefit:</strong> Additional bonus on top (e.g., Rs. 100 extra)</li>
            </ul>
          </div>

          <div class="bg-yellow-50 p-3 rounded border border-yellow-200 col-span-2">
            <p class="text-sm text-yellow-800">
              <strong>💡 Quick Example:</strong> Mobitel Rs. 100 package → Seller gets Rs. 80 discount<br>
              → Enter: Face Value = 100, Seller Discount (Fixed) = 80<br>
              → Result: Seller keeps Rs. 80, Wallet deducts Rs. 20
            </p>
          </div>
        </div>
      </div>

      <!-- Rules Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Operator</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Face Value</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transaction Type</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Discount (Fixed)</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Discount (%)</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Extra Benefit</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="rule in rules" :key="rule.id" :class="{ 'opacity-50': !rule.is_active }">
              <td class="px-4 py-4 whitespace-nowrap font-medium text-gray-900">{{ rule.operator.name }}</td>
              <td class="px-4 py-4 whitespace-nowrap">
                <span v-if="rule.face_value" class="text-gray-900 font-semibold">Rs. {{ rule.face_value }}</span>
                <span v-else class="text-gray-500 italic">Default (All)</span>
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600">{{ rule.transaction_type }}</td>
              <td class="px-4 py-4 whitespace-nowrap text-gray-900">Rs. {{ rule.seller_discount_flat }}</td>
              <td class="px-4 py-4 whitespace-nowrap text-gray-900">{{ rule.seller_discount_percent }}%</td>
              <td class="px-4 py-4 whitespace-nowrap text-gray-900">Rs. {{ rule.extra_benefit }}</td>
              <td class="px-4 py-4 whitespace-nowrap">
                <span :class="rule.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ rule.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-sm space-x-2">
                <button @click="openEditModal(rule)" class="text-blue-600 hover:text-blue-900">Edit</button>
                <button @click="toggleActive(rule)" class="text-yellow-600 hover:text-yellow-900">
                  {{ rule.is_active ? 'Deactivate' : 'Activate' }}
                </button>
                <button @click="deleteRule(rule)" class="text-red-600 hover:text-red-900">Delete</button>
              </td>
            </tr>
            <tr v-if="rules.length === 0">
              <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                No pricing rules found. Click "Add New Rule" to create one.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Add/Edit Modal -->
  <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-2xl my-8">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">{{ editingRule ? 'Edit Pricing Rule' : 'Add New Pricing Rule' }}</h2>
      
      <form @submit.prevent="saveRule">
        <div class="space-y-5">
          <!-- Operator -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Operator *</label>
            <select v-model="form.operator_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              <option value="">Select Operator</option>
              <option v-for="op in operators" :key="op.id" :value="op.id">{{ op.name }}</option>
            </select>
          </div>

          <!-- Face Value -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Face Value (Rs.)</label>
            <input v-model="form.face_value" type="number" step="0.01" min="0" 
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                   placeholder="100, 699, or leave empty for all packages">
            <p class="text-xs text-gray-500 mt-1.5">Leave empty to apply this rule to all package amounts</p>
          </div>

          <!-- Transaction Type -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Transaction Type *</label>
            <select v-model="form.transaction_type" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              <option value="sim_activation">SIM Activation</option>
              <option value="package">Package</option>
              <option value="reload">Reload</option>
            </select>
          </div>

          <!-- Discounts Row -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Fixed Discount (Rs.)</label>
              <input v-model="form.seller_discount_flat" type="number" step="0.01" min="0" 
                     class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                     placeholder="80">
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Percentage Discount (%)</label>
              <input v-model="form.seller_discount_percent" type="number" step="0.01" min="0" max="100" 
                     class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                     placeholder="10">
            </div>
          </div>

          <!-- Extra Benefit -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Extra Benefit (Rs.)</label>
            <input v-model="form.extra_benefit" type="number" step="0.01" min="0" 
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                   placeholder="100">
          </div>

          <!-- Active Status -->
          <div class="flex items-center">
            <input v-model="form.is_active" type="checkbox" id="is_active" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
            <label for="is_active" class="ml-2 text-sm font-medium text-gray-700">Active</label>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Description (Optional)</label>
            <textarea v-model="form.rule_description" rows="2" 
                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                      placeholder="Brief description of this rule"></textarea>
          </div>
        </div>

        <div class="flex justify-end gap-3 mt-8 pt-6 border-t">
          <button type="button" @click="closeModal" class="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium transition">
            Cancel
          </button>
          <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition" :disabled="saving">
            {{ saving ? 'Saving...' : 'Save Rule' }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <Footer />
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Header from '@/Components/custom/Header.vue';
import Footer from '@/Components/custom/Footer.vue';
import Banner from '@/Components/Banner.vue';
import axios from 'axios';

const props = defineProps({
  rules: Array,
  operators: Array,
});

const showGuide = ref(false);
const showModal = ref(false);
const editingRule = ref(null);
const saving = ref(false);

const form = ref({
  operator_id: '',
  face_value: null,
  rule_type: 'exact', // Auto-set based on face_value
  transaction_type: 'package',
  seller_discount_flat: 0,
  seller_discount_percent: 0,
  extra_benefit: 0,
  package_cost_override: null,
  is_active: true,
  rule_description: '',
});

const openAddModal = () => {
  editingRule.value = null;
  form.value = {
    operator_id: '',
    face_value: null,
    rule_type: 'exact',
    transaction_type: 'package',
    seller_discount_flat: 0,
    seller_discount_percent: 0,
    extra_benefit: 0,
    package_cost_override: null,
    is_active: true,
    rule_description: '',
  };
  showModal.value = true;
};

const openEditModal = (rule) => {
  editingRule.value = rule;
  form.value = {
    operator_id: rule.operator_id,
    face_value: rule.face_value,
    rule_type: rule.rule_type,
    transaction_type: rule.transaction_type,
    seller_discount_flat: rule.seller_discount_flat,
    seller_discount_percent: rule.seller_discount_percent,
    extra_benefit: rule.extra_benefit,
    package_cost_override: rule.package_cost_override,
    is_active: rule.is_active,
    rule_description: rule.rule_description,
  };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  editingRule.value = null;
};

const saveRule = async () => {
  saving.value = true;
  try {
    // Auto-set rule_type based on face_value
    const ruleData = { ...form.value };
    ruleData.rule_type = ruleData.face_value ? 'exact' : 'default';
    
    const url = editingRule.value 
      ? `/api/operator-pricing-rules/${editingRule.value.id}`
      : '/api/operator-pricing-rules';
    
    const method = editingRule.value ? 'put' : 'post';
    
    const response = await axios[method](url, ruleData);
    
    if (response.data.success) {
      alert(response.data.message);
      window.location.reload();
    }
  } catch (error) {
    alert('Error: ' + (error.response?.data?.message || 'Failed to save pricing rule'));
  } finally {
    saving.value = false;
  }
};

const toggleActive = async (rule) => {
  if (!confirm(`Are you sure you want to ${rule.is_active ? 'deactivate' : 'activate'} this pricing rule?`)) {
    return;
  }
  
  try {
    const response = await axios.post(`/api/operator-pricing-rules/${rule.id}/toggle-active`);
    if (response.data.success) {
      alert(response.data.message);
      window.location.reload();
    }
  } catch (error) {
    alert('Error: ' + (error.response?.data?.message || 'Failed to update pricing rule'));
  }
};

const deleteRule = async (rule) => {
  if (!confirm('Are you sure you want to delete this pricing rule? This cannot be undone.')) {
    return;
  }
  
  try {
    const response = await axios.delete(`/api/operator-pricing-rules/${rule.id}`);
    if (response.data.success) {
      alert(response.data.message);
      window.location.reload();
    }
  } catch (error) {
    alert('Error: ' + (error.response?.data?.message || 'Failed to delete pricing rule'));
  }
};
</script>
