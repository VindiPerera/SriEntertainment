<template>
  <div class="content-wrapper">
    <div class="content-container">
      <div class="page-header">
        <h2>Binding Services</h2>
      </div>
      <div class="page-content">
        <!-- Stock 0 Notification for all unique product codes -->
        <NotificationAlert
          v-if="lowStockProducts.length > 0"
          :visible="true"
          type="warning"
          title="Stock Alert"
          :message="lowStockMessage"
          :auto-close="false"
        />
        <!-- Add Button and Search Bar in One Row -->
        <div class="controls-row">
          <div class="search-bar">
            <input v-model="search" type="text" placeholder="Search binding services..." />
          </div>
          <button @click="openCreateForm" class="add-button">Add New Binding Service</button>
          <button @click="openRefillPopup" class="add-button">Refill</button>
        </div>

        <table class="service-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Binding Type</th>
              <th>Pages</th>
              <th>Price</th>
              <th>Service Charge</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(service, index) in filteredServices" :key="service.id">
              <td>{{ index + 1 }}</td>
              <td>{{ service.name }}</td>
              <td>{{ service.binding_type || 'N/A' }}</td>
              <td>{{ service.pages }}</td>
              <td>{{ service.price }}</td>
              <td>{{ service.service_charge }}</td>
              <td>
                <button @click="editService(service)" class="edit-button">Edit</button>
                <button @click="deleteService(service.id)" class="delete-button">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Create Modal -->
        <div v-if="isCreateModalOpen" class="modal-overlay">
          <div class="create-modal-content">
            <div class="modal-header">
              <h2>Create Binding Service</h2>
              <button @click="closeCreateModal" class="close-button">&times;</button>
            </div>
            <div class="modal-body">
              <div class="form-group">
                <label for="name">Name</label>
                <input v-model="createForm.name" type="text" id="name" placeholder="Enter service name" />
                <span v-if="createForm.errors.name" class="error">{{ createForm.errors.name }}</span>
              </div>
              <div class="form-group">
                <label for="binding_type">Binding Type</label>
                <select v-model="createForm.binding_type" id="binding_type">
                  <option value="">Select Binding Type</option>
                  <option value="spiral">Spiral</option>
                  <option value="tape">Tape</option>
                </select>
                <span v-if="createForm.errors.binding_type" class="error">{{ createForm.errors.binding_type }}</span>
              </div>
              <div class="form-group">
                <label for="pages">Pages</label>
                <select v-model="createForm.pages" id="pages">
                  <option value="">Select Pages</option>
                  <option value="Less than 50">Less than 50</option>
                  <option value="50-100">50-100</option>
                  <option value="100-200">100-200</option>
                  <option value="More than 200">More than 200</option>
                </select>
                <span v-if="createForm.errors.pages" class="error">{{ createForm.errors.pages }}</span>
              </div>
              <div class="form-group">
                <label for="price">Price</label>
                <input v-model.number="createForm.price" type="number" id="price" placeholder="Enter price" step="0.01" />
                <span v-if="createForm.errors.price" class="error">{{ createForm.errors.price }}</span>
              </div>
              <div class="form-group">
                <label for="service_charge">Service Charge</label>
                <input v-model.number="createForm.service_charge" type="number" id="service_charge" placeholder="Enter service charge" step="0.01" />
                <span v-if="createForm.errors.service_charge" class="error">{{ createForm.errors.service_charge }}</span>
              </div>
              <div class="form-group" style="display: flex; gap: 12px; align-items: center;">
              <label style="margin: 0; font-weight: bold;">Total Price :</label>
              <div class="info-value" style="font-weight: bold; color: #2563eb;">{{ totalPriceDisplay }}</div>
            </div>
              
              <!-- Category Selection -->
              <div class="form-group">
                <label for="category">Category</label>
                <select v-model="selectedCategoryId" id="category" @change="fetchProductsByCategory(selectedCategoryId)">
                  <option value="">Select Category</option>
                  <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.hierarchy_string ? category.hierarchy_string + " → " + category.name : category.name }}
                  </option>
                </select>
              </div>

              <!-- Product Selection -->
              <div class="form-group" v-if="selectedCategoryId">
                <label for="product">Add Products</label>
                <select v-model="selectedProductId" id="product" @change="addProduct">
                  <option value="">Select Product to Add</option>
                  <option v-for="product in availableProducts" :key="product.id" :value="product.id">
                    {{ product.name }} - {{ product.code || product.barcode || 'N/A' }}
                  </option>
                </select>
              </div>

              <!-- Selected Products Display -->
              <div class="form-group">
                <label>Selected Products: <span class="product-count">({{ selectedProducts.length }} selected)</span></label>
                <div v-if="selectedProducts.length === 0" class="no-products-selected">
                  No products selected. Please select a category and add products.
                </div>
                <div v-else class="selected-products-list">
                  <div v-for="product in selectedProducts" :key="product.id" class="selected-product-item">
                    <span>{{ product.name }} - {{ product.code || product.barcode || 'N/A' }}</span>
                    <button type="button" @click="removeProduct(product.id)" class="remove-product-btn">&times;</button>
                  </div>
                </div>
                <span v-if="createForm.errors.products" class="error">{{ createForm.errors.products }}</span>
              </div>

              <div class="form-actions">
                <button @click="submitForm" class="submit-button" :disabled="createForm.processing">
                  {{ createForm.processing ? 'Creating...' : 'Create Service' }}
                </button>
                <button @click="closeCreateModal" class="cancel-button">Cancel</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="isEditModalOpen" class="modal-overlay">
          <div class="create-modal-content">
            <div class="modal-header">
              <h2>Edit Binding Service</h2>
              <button @click="closeEditModal" class="close-button">&times;</button>
            </div>
            <div class="modal-body">
              <div class="form-group">
                <label for="edit-name">Name</label>
                <input v-model="editForm.name" type="text" id="edit-name" placeholder="Enter service name" />
                <span v-if="editForm.errors.name" class="error">{{ editForm.errors.name }}</span>
              </div>
              <div class="form-group">
                <label for="edit-binding_type">Binding Type</label>
                <select v-model="editForm.binding_type" id="edit-binding_type">
                  <option value="">Select Binding Type</option>
                  <option value="spiral">Spiral</option>
                  <option value="tape">Tape</option>
                </select>
                <span v-if="editForm.errors.binding_type" class="error">{{ editForm.errors.binding_type }}</span>
              </div>
              <div class="form-group">
                <label for="edit-pages">Pages</label>
                <select v-model="editForm.pages" id="edit-pages">
                  <option value="">Select Pages</option>
                  <option value="Less than 50">Less than 50</option>
                  <option value="50-100">50-100</option>
                  <option value="100-200">100-200</option>
                  <option value="More than 200">More than 200</option>
                </select>
                <span v-if="editForm.errors.pages" class="error">{{ editForm.errors.pages }}</span>
              </div>
              <div class="form-group">
                <label for="edit-price">Price</label>
                <input v-model="editForm.price" type="number" id="edit-price" placeholder="Enter price" step="0.01" />
                <span v-if="editForm.errors.price" class="error">{{ editForm.errors.price }}</span>
              </div>
              <div class="form-group">
                <label for="edit-service_charge">Service Charge</label>
                <input v-model="editForm.service_charge" type="number" id="edit-service_charge" placeholder="Enter service charge" step="0.01" />
                <span v-if="editForm.errors.service_charge" class="error">{{ editForm.errors.service_charge }}</span>
              </div>
              <div class="form-actions">
                <button @click="updateForm" class="submit-button" :disabled="editForm.processing">
                  {{ editForm.processing ? 'Updating...' : 'Update Service' }}
                </button>
                <button @click="closeEditModal" class="cancel-button">Cancel</button>
              </div>
            </div>
          </div>
        </div>

        <!-- BindingRefillPopup Component -->
        <BindingRefillPopup
          v-model="isRefillPopupVisible"
          @refill-submitted="handleRefillSubmitted"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useForm } from "@inertiajs/vue3";
import BindingRefillPopup from './BindingRefillPopup.vue';
import NotificationAlert from './NotificationAlert.vue';

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isRefillPopupVisible = ref(false);
const search = ref("");
const editingService = ref(null);

// Category and Product Selection
const categories = ref([]);
const products = ref([]);
const selectedCategoryId = ref(null);
const selectedProductId = ref(null);
const selectedProducts = ref([]);

// Low stock management
const lowStockProducts = ref([]);

// Computed property to get available products (not already selected)
const availableProducts = computed(() => {
  return products.value.filter(product => 
    !selectedProducts.value.some(selected => selected.id === product.id)
  );
});

// Computed property for low stock message
const lowStockMessage = computed(() => {
  if (lowStockProducts.value.length === 0) return '';
  
  const uniqueProducts = [...new Set(lowStockProducts.value.map(item => item.product_code))];
  
  if (uniqueProducts.length === 1) {
    return `${uniqueProducts[0]} is out of stock and needs to be refilled.`;
  }
  
  return `${uniqueProducts.length} products are out of stock: ${uniqueProducts.join(', ')}. Please refill these items.`;
});

const openCreateForm = () => {
  isCreateModalOpen.value = true;
  fetchCategories();
};

// Computed property for total price
const totalPriceDisplay = computed(() => {
  const price = Number(createForm.price) || 0;
  const service = Number(createForm.service_charge) || 0;
  return (price + service).toFixed(2);
});

const closeCreateModal = () => {
  isCreateModalOpen.value = false;
  createForm.reset();
  // Reset product selection
  selectedProducts.value = [];
  selectedCategoryId.value = null;
  selectedProductId.value = null;
  products.value = [];
};

const closeEditModal = () => {
  isEditModalOpen.value = false;
  editingService.value = null;
  editForm.reset();
};

const openRefillPopup = () => {
  isRefillPopupVisible.value = true;
};

const handleRefillSubmitted = () => {
  isRefillPopupVisible.value = false;
  fetchServices(); // Refresh the services list if needed
  console.log('Binding refill submitted successfully');
};

// Create form
const createForm = useForm({
  name: "",
  binding_type: "",
  pages: "",
  price: "",
  service_charge: "",
  products: [],
});

// Edit form
const editForm = useForm({
  name: "",
  binding_type: "",
  pages: "",
  price: "",
  service_charge: "",
});

// Mock data - replace with actual data from API
const services = ref([]);

// Fetch services from API
const fetchServices = async () => {
  try {
    const response = await fetch('/binding-services', {
      headers: {
        'Accept': 'application/json',
      },
    });
    const data = await response.json();
    services.value = data;
  } catch (error) {
    console.error('Error fetching services:', error);
  }
};

// Fetch low stock products
const fetchLowStockProducts = async () => {
  try {
    console.log('Fetching binding low stock products...');
    const response = await fetch('/api/binding/low-stock', {
      headers: {
        'Accept': 'application/json',
      },
    });
    console.log('Binding low stock response status:', response.status);
    const data = await response.json();
    console.log('Binding low stock data received:', data);
    
    if (data && data.success && Array.isArray(data.low_stock_products)) {
      lowStockProducts.value = data.low_stock_products;
    } else {
      lowStockProducts.value = [];
    }
    console.log('Binding lowStockProducts.value set to:', lowStockProducts.value);
  } catch (error) {
    console.error('Error fetching binding low stock products:', error);
    lowStockProducts.value = [];
  }
};

// Fetch categories from API
const fetchCategories = async () => {
  try {
    console.log('Fetching categories from /api/categories');
    
    // Use the existing PhotocopyServiceController endpoint
    const response = await fetch('/api/categories', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });
    
    console.log('Categories response status:', response.status);
    
    if (!response.ok) {
      const errorText = await response.text();
      console.error('Categories error:', errorText);
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const data = await response.json();
    console.log('Categories raw data:', data);
    
    // Handle different response formats
    if (Array.isArray(data)) {
      categories.value = data;
    } else if (data.categories && Array.isArray(data.categories)) {
      categories.value = data.categories;
    } else if (data.data && Array.isArray(data.data)) {
      categories.value = data.data;
    } else {
      console.error('Unexpected categories format:', data);
      categories.value = [];
    }
    
    console.log('Categories loaded:', categories.value.length);
  } catch (error) {
    console.error('Error fetching categories:', error);
    categories.value = [];
    alert('Failed to load categories. Please try again.');
  }
};

const fetchProductsByCategory = async (categoryId) => {
  if (!categoryId) {
    products.value = [];
    return;
  }
  
  try {
    console.log('Fetching products for category:', categoryId);
    
    // Use the new binding products endpoint
    const response = await fetch(`/api/binding/products?category_id=${categoryId}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
    });
    
    console.log('Response status:', response.status);
    
    if (!response.ok) {
      const errorText = await response.text();
      console.error('Error response:', errorText);
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const data = await response.json();
    console.log('Products loaded:', data);
    
    products.value = Array.isArray(data) ? data : [];
    
  } catch (error) {
    console.error("Error fetching products:", error);
    products.value = [];
    alert('Failed to load products. Please try again.');
  }
};
// Add product to selected list
const addProduct = () => {
  if (selectedProductId.value) {
    const product = products.value.find(p => p.id === selectedProductId.value);
    if (product && !selectedProducts.value.some(p => p.id === product.id)) {
      selectedProducts.value.push(product);
      selectedProductId.value = null; // Reset selection
    }
  }
};

// Remove product from selected list
const removeProduct = (productId) => {
  selectedProducts.value = selectedProducts.value.filter(p => p.id !== productId);
};

onMounted(() => {
  fetchServices();
  fetchLowStockProducts();
});

const filteredServices = computed(() => {
  return services.value.filter((service) =>
    service.name.toLowerCase().includes(search.value.toLowerCase())
  );
});

const submitForm = () => {
  // Validate that at least one product is selected
  if (selectedProducts.value.length === 0) {
    alert('Please select at least one product');
    return;
  }
  
  // Prepare form data with products as array of IDs
  const productsArray = selectedProducts.value.map(p => p.id);
  
  // Log what we're about to submit
  console.log('Submitting form with data:', {
    name: createForm.name,
    binding_type: createForm.binding_type,
    pages: createForm.pages,
    price: createForm.price,
    service_charge: createForm.service_charge,
    products: productsArray
  });
  
  // Set the products array on the form
  createForm.products = productsArray;
  
  createForm.post("/binding-services", {
    onSuccess: () => {
      fetchServices(); // Refresh the list
      closeCreateModal();
      createForm.reset();
    },
  });
};

const editService = async (service) => {
  try {
    const response = await fetch(`/binding-services/${service.id}`, {
      headers: {
        'Accept': 'application/json',
      },
    });
    const data = await response.json();

    editingService.value = data;
    editForm.name = data.name;
    editForm.binding_type = data.binding_type;
    editForm.pages = data.pages;
    editForm.price = data.price;
    editForm.service_charge = data.service_charge;

    isEditModalOpen.value = true;
  } catch (error) {
    console.error('Error fetching service details:', error);
  }
};

const updateForm = () => {
  if (!editingService.value) return;
  
  editForm.put(`/binding-services/${editingService.value.id}`, {
    onSuccess: () => {
      fetchServices(); // Refresh the list
      closeEditModal();
    },
  });
};

const deleteService = (id) => {
  if (confirm('Are you sure you want to delete this service?')) {
    useForm({}).delete(`/binding-services/${id}`, {
      onSuccess: () => {
        fetchServices(); // Refresh the list
      },
    });
  }
};
</script>

<style scoped>
.content-wrapper {
  width: 100%;
  min-height: 100%;
  background-color: #fff;
  margin-bottom: 500px;
}

.page-header h2 {
  font-size: 28px;
  font-weight: bold;
  color: #333;
  margin: 0;
}

.content-container {
  background-color: #fff;
  padding: 20px;
  border-radius: 8px;
  width: 100%;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 10px;
  border-bottom: 1px solid #ddd;
}

.modal-header h2 {
  font-size: 24px;
  font-weight: bold;
  color: #333;
  margin: 0;
}

.close-button {
  background: #ff4d4d;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
}

.close-button:hover {
  background: #ff3333;
}

.controls-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  gap: 20px;
}

.controls-row .search-bar {
  flex: 1;
}

.controls-row .add-button {
  margin-bottom: 0;
  flex-shrink: 0;
  padding: 10px 20px;
  font-size: 14px;
}

.add-button {
  background-color: #4CAF50;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 14px;
  font-weight: bold;
}

.add-button:hover {
  background-color: #45a049;
}

.search-bar input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
}

.service-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

.service-table th,
.service-table td {
  border: 1px solid #ddd;
  padding: 12px;
  text-align: left;
}

.service-table th {
  background-color: #f2f2f2;
  font-weight: bold;
}

.edit-button {
  background-color: #2196F3;
  color: white;
  padding: 6px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  margin-right: 5px;
}

.edit-button:hover {
  background-color: #0b7dda;
}

.delete-button {
  background-color: #f44336;
  color: white;
  padding: 6px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.delete-button:hover {
  background-color: #da190b;
}

/* Create/Edit Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2000;
}

.create-modal-content {
  background-color: #fff;
  padding: 30px;
  border-radius: 8px;
  width: 90%;
  max-width: 500px;
  max-height: 80vh;
  overflow-y: auto;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
  color: #333;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #4CAF50;
  box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
}

.error {
  color: #f44336;
  font-size: 12px;
  margin-top: 5px;
  display: block;
}

.form-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 30px;
}

.submit-button {
  background-color: #4CAF50;
  color: white;
  padding: 12px 24px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.submit-button:hover:not(:disabled) {
  background-color: #45a049;
}

.submit-button:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.cancel-button {
  background-color: #6c757d;
  color: white;
  padding: 12px 24px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.cancel-button:hover {
  background-color: #5a6268;
}

/* Product Selection Styles */
.product-count {
  color: #666;
  font-weight: normal;
  font-size: 12px;
}

.no-products-selected {
  color: #999;
  font-style: italic;
  padding: 10px;
  text-align: center;
  border: 1px dashed #ddd;
  border-radius: 4px;
  background-color: #f9f9f9;
}

.selected-products-list {
  max-height: 150px;
  overflow-y: auto;
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 8px;
  background-color: #f9f9f9;
}

.selected-product-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 6px 8px;
  margin-bottom: 4px;
  background-color: #fff;
  border: 1px solid #e0e0e0;
  border-radius: 3px;
  font-size: 14px;
}

.selected-product-item:last-child {
  margin-bottom: 0;
}

.remove-product-btn {
  background-color: #ff4444;
  color: white;
  border: none;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  font-size: 12px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
}

.remove-product-btn:hover {
  background-color: #cc0000;
}
</style>