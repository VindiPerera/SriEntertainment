<template>
  <Head title="Manage SIM Activation Packages" />
  
  <Header />
  <Banner />

  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Manage SIM Activation Packages</h1>
          <p class="mt-1 text-sm text-gray-600">Add and manage packages for SIM activation transactions</p>
        </div>
        <div class="flex gap-3">
          <button @click="showGuide = !showGuide" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
            {{ showGuide ? 'Hide' : 'Show' }} Guide
          </button>
          <Link :href="route('operator-pricing-rules.index')" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
            Pricing Rules
          </Link>
          <Link :href="route('sim-activation.index')" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            Back to Transactions
          </Link>
          <button @click="openAddModal" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            + Add New Package
          </button>
        </div>
      </div>

      <!-- Business Rules Guide -->
      <div v-if="showGuide" class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
        <h2 class="text-xl font-bold text-blue-900 mb-4">📚 Package Setup Guide</h2>
        <div class="space-y-4 text-sm">
          <div class="bg-white p-4 rounded border border-blue-100">
            <h3 class="font-bold text-gray-900 mb-2">Important Notes:</h3>
            <ul class="list-disc list-inside space-y-1 text-gray-700">
              <li>Packages define what customers can purchase (face value amounts)</li>
              <li>Actual discounts and wallet calculations are controlled by <strong>Operator Pricing Rules</strong></li>
              <li>Make sure to configure pricing rules for each operator to control seller benefits</li>
              <li>The description field helps you remember the business logic for each package</li>
            </ul>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white p-4 rounded border border-green-100">
              <h3 class="font-bold text-green-700 mb-2">✅ Example: Sale Commission Model</h3>
              <div class="space-y-2 text-gray-700">
                <p><strong>Package:</strong> Rs. 100 Package</p>
                <p><strong>Face Value:</strong> 100</p>
                <p><strong>Description:</strong> "Seller gets Rs. 80 discount. Wallet deducts Rs. 20"</p>
                <p class="text-xs text-gray-500">→ Configure this in Operator Pricing Rules table</p>
              </div>
            </div>

            <div class="bg-white p-4 rounded border border-purple-100">
              <h3 class="font-bold text-purple-700 mb-2">✅ Example: Deposit Bonus Model</h3>
              <div class="space-y-2 text-gray-700">
                <p><strong>Package:</strong> Rs. 100 Reload</p>
                <p><strong>Face Value:</strong> 100</p>
                <p><strong>Description:</strong> "Seller gets Rs. 200 benefit. Wallet credits +Rs. 200"</p>
                <p class="text-xs text-gray-500">→ Configure this in Operator Pricing Rules table</p>
              </div>
            </div>
          </div>

          <div class="bg-yellow-50 p-3 rounded border border-yellow-200">
            <p class="text-sm text-yellow-800">
              <strong>⚠️ Reminder:</strong> After adding packages here, configure the corresponding pricing rules 
              in the database to define seller discounts, wallet credits, and profit calculations.
            </p>
          </div>
        </div>
      </div>

      <!-- Packages Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operator</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Package Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Face Value</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Selling Price</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="pkg in packages" :key="pkg.id" :class="{ 'opacity-50': !pkg.is_active }">
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="font-medium text-gray-900">{{ pkg.operator.name }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ pkg.package_name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-gray-900">Rs. {{ formatCurrency(pkg.face_value) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                {{ pkg.selling_price ? 'Rs. ' + formatCurrency(pkg.selling_price) : 'Default' }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ pkg.description || '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="pkg.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ pkg.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                <button @click="openEditModal(pkg)" class="text-blue-600 hover:text-blue-900">Edit</button>
                <button @click="toggleActive(pkg)" class="text-yellow-600 hover:text-yellow-900">
                  {{ pkg.is_active ? 'Deactivate' : 'Activate' }}
                </button>
                <button @click="deletePackage(pkg)" class="text-red-600 hover:text-red-900">Delete</button>
              </td>
            </tr>
            <tr v-if="packages.length === 0">
              <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                No packages found. Click "Add New Package" to create one.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Add/Edit Modal -->
  <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl">
      <h2 class="text-2xl font-bold mb-4">{{ editingPackage ? 'Edit Package' : 'Add New Package' }}</h2>
      
      <form @submit.prevent="savePackage">
        <div class="grid grid-cols-2 gap-4">
          <!-- Operator -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Operator *</label>
            <select v-model="form.operator_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
              <option value="">Select Operator</option>
              <option v-for="op in operators" :key="op.id" :value="op.id">{{ op.name }}</option>
            </select>
          </div>

          <!-- Package Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Package Name *</label>
            <input v-model="form.package_name" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-lg" 
                   placeholder="e.g., Rs. 100 Package">
          </div>

          <!-- Face Value -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Face Value (Rs.) *</label>
            <input v-model="form.face_value" type="number" step="0.01" min="0" required 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="100.00">
          </div>

          <!-- Selling Price -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Selling Price (Rs.)</label>
            <input v-model="form.selling_price" type="number" step="0.01" min="0" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg" 
                   placeholder="Leave empty to use face value">
          </div>

          <!-- Sort Order -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
            <input v-model="form.sort_order" type="number" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="0">
          </div>

          <!-- Active Status -->
          <div class="flex items-center pt-7">
            <input v-model="form.is_active" type="checkbox" id="is_active" class="mr-2">
            <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
          </div>

          <!-- Description -->
          <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Description 
              <span class="text-xs text-gray-500">(Business logic reminder)</span>
            </label>
            <textarea v-model="form.description" rows="3" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg" 
                      placeholder="Example: Seller gets Rs. 80 discount. Wallet deducts Rs. 20&#10;Or: Seller gets Rs. 200 benefit. Wallet credits +Rs. 200"></textarea>
            <p class="text-xs text-gray-500 mt-1">
              Note: Actual calculations are controlled by Operator Pricing Rules, not this description.
            </p>
          </div>
        </div>

        <div class="flex justify-end gap-3 mt-6">
          <button type="button" @click="closeModal" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
            Cancel
          </button>
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700" :disabled="saving">
            {{ saving ? 'Saving...' : 'Save Package' }}
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
  packages: Array,
  operators: Array,
});

const showGuide = ref(false);
const showModal = ref(false);
const editingPackage = ref(null);
const saving = ref(false);

const form = ref({
  operator_id: '',
  package_name: '',
  face_value: '',
  selling_price: null,
  description: '',
  is_active: true,
  sort_order: 0,
});

const openAddModal = () => {
  editingPackage.value = null;
  form.value = {
    operator_id: '',
    package_name: '',
    face_value: '',
    selling_price: null,
    description: '',
    is_active: true,
    sort_order: 0,
  };
  showModal.value = true;
};

const openEditModal = (pkg) => {
  editingPackage.value = pkg;
  form.value = {
    operator_id: pkg.operator_id,
    package_name: pkg.package_name,
    face_value: pkg.face_value,
    selling_price: pkg.selling_price,
    description: pkg.description,
    is_active: pkg.is_active,
    sort_order: pkg.sort_order,
  };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  editingPackage.value = null;
};

const savePackage = async () => {
  saving.value = true;
  try {
    const url = editingPackage.value 
      ? `/api/sim-activation-packages/${editingPackage.value.id}`
      : '/api/sim-activation-packages';
    
    const method = editingPackage.value ? 'put' : 'post';
    
    const response = await axios[method](url, form.value);
    
    if (response.data.success) {
      alert(response.data.message);
      window.location.reload();
    }
  } catch (error) {
    alert('Error: ' + (error.response?.data?.message || 'Failed to save package'));
  } finally {
    saving.value = false;
  }
};

const toggleActive = async (pkg) => {
  if (!confirm(`Are you sure you want to ${pkg.is_active ? 'deactivate' : 'activate'} this package?`)) {
    return;
  }
  
  try {
    const response = await axios.post(`/api/sim-activation-packages/${pkg.id}/toggle-active`);
    if (response.data.success) {
      alert(response.data.message);
      window.location.reload();
    }
  } catch (error) {
    alert('Error: ' + (error.response?.data?.message || 'Failed to update package'));
  }
};

const deletePackage = async (pkg) => {
  if (!confirm('Are you sure you want to delete this package? This cannot be undone.')) {
    return;
  }
  
  try {
    const response = await axios.delete(`/api/sim-activation-packages/${pkg.id}`);
    if (response.data.success) {
      alert(response.data.message);
      window.location.reload();
    }
  } catch (error) {
    alert('Error: ' + (error.response?.data?.message || 'Cannot delete package with existing transactions'));
  }
};

const formatCurrency = (amount) => {
  return parseFloat(amount || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
};
</script>
