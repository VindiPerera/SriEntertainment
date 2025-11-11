<template>
  <div class="page-content">
    <div class="content-wrapper">
      <div class="page-header">
        <h2>Laminating Services</h2>
      </div>
      <div class="page-body">
        <!-- Add Button and Search Bar in One Row -->
        <div class="controls-row">
          <div class="search-bar">
            <input v-model="search" type="text" placeholder="Search laminating services..." />
          </div>
          <button @click="openCreateForm" class="add-button">Add New Laminating Service</button>
           <button @click="openRefillPopup" class="add-button">Add Refill</button>
        </div>

        <table class="service-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Pouch Size</th>
              <th>Price</th>
              <th>Service Amount</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(service, index) in filteredServices" :key="service.id">
              <td>{{ index + 1 }}</td>
              <td>{{ service.name }}</td>
              <td>{{ service.pouch_size }}</td>
              <td>{{ service.price }}</td>
              <td>{{ service.service_amount }}</td>
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
              <h2>Create Laminating Service</h2>
              <button @click="closeCreateModal" class="close-button">&times;</button>
            </div>
            <div class="modal-body">
              <div class="form-group">
                <label for="name">Name</label>
                <input v-model="createForm.name" type="text" id="name" placeholder="Enter service name" />
                <span v-if="createForm.errors.name" class="error">{{ createForm.errors.name }}</span>
              </div>
              <div class="form-group">
                <label for="pouch_size">Pouch Size</label>
                <select v-model="createForm.pouch_size" id="pouch_size">
                  <option value="">Select Pouch Size</option>
                  <option value="Id">Id</option>
                  <option value="4R">4R</option>
                  <option value="A5">A5</option>
                  <option value="A4">A4</option>
                  <option value="LG">LG</option>
                  <option value="Certificate">Certificate</option>
                  <option value="A3">A3</option>
                </select>
                <span v-if="createForm.errors.pouch_size" class="error">{{ createForm.errors.pouch_size }}</span>
              </div>
              <div class="form-group">
                <label for="price">Price</label>
                <input v-model.number="createForm.price" type="number" id="price" placeholder="Enter price" step="0.01" />
                <span v-if="createForm.errors.price" class="error">{{ createForm.errors.price }}</span>
              </div>
              <div class="form-group">
                <label for="service_amount">Service Amount</label>
                <input v-model.number="createForm.service_amount" type="number" id="service_amount" placeholder="Enter service amount" step="0.01" />
                <span v-if="createForm.errors.service_amount" class="error">{{ createForm.errors.service_amount }}</span>
              </div>
              
              <!-- Total Price Display -->
              <div class="form-group" style="display: flex; gap: 12px; align-items: center;">
                <label style="margin: 0; font-weight: bold;">Total Price:</label>
                <div class="info-value" style="font-weight: bold; color: #2563eb;">{{ totalPriceDisplay }}</div>
              </div>

              <!-- Category and Product Selection -->
              <div class="form-group">
                <label for="category">Category</label>
                <select v-model="selectedCategoryId" id="category" @change="fetchProductsByCategory(selectedCategoryId)">
                  <option value="">Select Category</option>
                  <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.name }}
                  </option>
                </select>
              </div>

              <div class="form-group" v-if="selectedCategoryId">
                <label for="product">Add Products</label>
                <select v-model="selectedProductId" id="product" @change="addProduct">
                  <option value="">Select Product to Add</option>
                  <option v-for="product in availableProducts" :key="product.id" :value="product.id">
                    {{ product.name }} - {{ product.code }} 
                  </option>
                </select>
              </div>

              <!-- Selected Products List -->
              <div class="form-group">
                <label>Selected Products: <span class="product-count">({{ selectedProducts.length }} selected)</span></label>
                <div v-if="selectedProducts.length === 0" class="no-products-selected">
                  No products selected. Please select a category and add products.
                </div>
                <div v-else class="selected-products-list">
                  <div v-for="product in selectedProducts" :key="product.id" class="selected-product-item">
                    <span>{{ product.name }} - {{ product.code || 'N/A' }}</span>
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
              <h2>Edit Laminating Service</h2>
              <button @click="closeEditModal" class="close-button">&times;</button>
            </div>
            <div class="modal-body">
              <div class="form-group">
                <label for="edit-name">Name</label>
                <input v-model="editForm.name" type="text" id="edit-name" placeholder="Enter service name" />
                <span v-if="editForm.errors.name" class="error">{{ editForm.errors.name }}</span>
              </div>
              <div class="form-group">
                <label for="edit-pouch_size">Pouch Size</label>
                <select v-model="editForm.pouch_size" id="edit-pouch_size">
                  <option value="">Select Pouch Size</option>
                  <option value="Id">Id</option>
                  <option value="4R">4R</option>
                  <option value="A5">A5</option>
                  <option value="A4">A4</option>
                  <option value="LG">LG</option>
                  <option value="Certificate">Certificate</option>
                  <option value="A3">A3</option>
                </select>
                <span v-if="editForm.errors.pouch_size" class="error">{{ editForm.errors.pouch_size }}</span>
              </div>
              <div class="form-group">
                <label for="edit-price">Price</label>
                <input v-model.number="editForm.price" type="number" id="edit-price" placeholder="Enter price" step="0.01" />
                <span v-if="editForm.errors.price" class="error">{{ editForm.errors.price }}</span>
              </div>
              <div class="form-group">
                <label for="edit-service_amount">Service Amount</label>
                <input v-model.number="editForm.service_amount" type="number" id="edit-service_amount" placeholder="Enter service amount" step="0.01" />
                <span v-if="editForm.errors.service_amount" class="error">{{ editForm.errors.service_amount }}</span>
              </div>
              
              <!-- Total Price Display for Edit -->
              <div class="form-group" style="display: flex; gap: 12px; align-items: center;">
                <label style="margin: 0; font-weight: bold;">Total Price:</label>
                <div class="info-value" style="font-weight: bold; color: #2563eb;">{{ editTotalPriceDisplay }}</div>
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

        <!-- Refill Popup -->
        <LaminatingRefillPopup
          :isOpen="isRefillPopupOpen"
          @close="closeRefillPopup"
          @refill-submitted="fetchServices"
        />

        <!-- Notification Alert -->
        <NotificationAlert
          :visible="notification.visible"
          :type="notification.type"
          :title="notification.title"
          :message="notification.message"
          :auto-close="notification.autoClose"
          @close="closeNotification"
        />
        <!-- Stock 0 Notification for all unique product codes -->
        <NotificationAlert
          v-if="lowStockProducts.length > 0"
          :visible="true"
          type="warning"
          title="Stock Alert"
          :message="lowStockMessage"
          :auto-close="false"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useForm } from "@inertiajs/vue3";
import LaminatingRefillPopup from './LaminatingRefillPopup.vue';
import NotificationAlert from "./NotificationAlert.vue";

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isRefillPopupOpen = ref(false);
const search = ref("");
const editingService = ref(null);

// Notification state
const notification = ref({
  visible: false,
  type: 'info',
  title: 'Notification',
  message: '',
  autoClose: true
});

// Helper function to show notifications
const showNotification = (type, title, message, autoClose = true) => {
  notification.value = {
    visible: true,
    type,
    title,
    message,
    autoClose
  };
};

const closeNotification = () => {
  notification.value.visible = false;
};

// Low stock products fetched from API
const lowStockProducts = ref([]);
const lowStockMessage = computed(() => {
  if (!lowStockProducts.value.length) return '';
  return lowStockProducts.value.map(item =>
    `Stock for ${item.product_name || item.name || 'Product'} (Code: ${item.product_code || item.code || item.id}) is 0. Please refill.`
  ).join('\n');
});

// Fetch low stock products from API
const fetchLowStockProducts = async () => {
  try {
    const response = await fetch('/api/laminating/low-stock', {
      headers: { 'Accept': 'application/json' }
    });
    const data = await response.json();
    if (data && data.success && Array.isArray(data.low_stock_products)) {
      lowStockProducts.value = data.low_stock_products;
    } else {
      lowStockProducts.value = [];
    }
  } catch (error) {
    lowStockProducts.value = [];
  }
};

// Category and product selection variables
const categories = ref([]);
const products = ref([]);
const selectedCategoryId = ref(null);
const selectedProductId = ref(null);
const selectedProducts = ref([]);

// Computed property for total price in create form
const totalPriceDisplay = computed(() => {
  const price = parseFloat(createForm.price) || 0;
  const serviceAmount = parseFloat(createForm.service_amount) || 0;
  const total = price + serviceAmount;
  return `${total.toFixed(2)}`;
});

// Computed property for total price in edit form
const editTotalPriceDisplay = computed(() => {
  const price = parseFloat(editForm.price) || 0;
  const serviceAmount = parseFloat(editForm.service_amount) || 0;
  const total = price + serviceAmount;
  return `$${total.toFixed(2)}`;
});

const openCreateForm = () => {
  isCreateModalOpen.value = true;
};

const closeCreateModal = () => {
  isCreateModalOpen.value = false;
  createForm.reset();
  // Reset category and product selections
  selectedCategoryId.value = null;
  selectedProductId.value = null;
  selectedProducts.value = [];
  products.value = [];
};

const closeEditModal = () => {
  isEditModalOpen.value = false;
  editingService.value = null;
  editForm.reset();
};

const closeRefillPopup = () => {
  isRefillPopupOpen.value = false;
};

// Create form
const createForm = useForm({
  name: "",
  pouch_size: "",
  price: "",
  service_amount: "",
  products: [],
});

// Edit form
const editForm = useForm({
  name: "",
  pouch_size: "",
  price: "",
  service_amount: "",
});

// Mock data - replace with actual data from API
const services = ref([]);

// Computed property to get available products (not already selected)
const availableProducts = computed(() => {
  return products.value.filter(product => 
    !selectedProducts.value.some(selected => selected.id === product.id)
  );
});

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

// Fetch services from API
const fetchServices = async () => {
  try {
    const response = await fetch('/laminating-services', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin', // Include cookies for authentication
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    services.value = data;
  } catch (error) {
    console.error('Error fetching services:', error);
    showNotification('error', 'Error', 'Failed to load services. Please refresh the page.');
  }
};

// Fetch categories from API
const fetchCategories = async () => {
  try {
    const response = await fetch("/api/laminating/categories", {
      headers: {
        Accept: "application/json",
      },
    });
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const data = await response.json();
    console.log('Categories fetched:', data); // Debug log
    categories.value = Array.isArray(data) ? data : [];
  } catch (error) {
    console.error("Error fetching categories:", error);
    categories.value = [];
  }
};

const fetchProductsByCategory = async (categoryId) => {
  if (!categoryId) {
    products.value = [];
    return;
  }
  
  try {
    const response = await fetch(`/api/laminating/products?category_id=${categoryId}`, {
      headers: {
        Accept: "application/json",
      },
    });
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const data = await response.json();
    console.log('Products fetched:', data); // Debug log
    products.value = Array.isArray(data) ? data : [];
  } catch (error) {
    console.error("Error fetching products:", error);
    products.value = [];
  }
};

onMounted(() => {
  fetchServices();
  fetchCategories();
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
    showNotification('warning', 'Validation Error', 'Please select at least one product');
    return;
  }
  
  // Prepare form data with products as array of IDs
  const productsArray = selectedProducts.value.map(p => p.id);
  
  // Log what we're about to submit
  console.log('Submitting form with data:', {
    name: createForm.name,
    pouch_size: createForm.pouch_size,
    price: createForm.price,
    service_amount: createForm.service_amount,
    products: productsArray
  });
  
  // Set the products array on the form
  createForm.products = productsArray;
  
  createForm.post("/laminating-services", {
    onSuccess: () => {
      fetchServices(); // Refresh the list
      closeCreateModal();
      createForm.reset();
    },
    onError: (errors) => {
      console.error('Validation errors:', errors);
    },
  });
};

const openRefillPopup = () => {
  console.log("Opening refill popup...");
  isRefillPopupOpen.value = true;
};

const editService = async (service) => {
  try {
    const response = await fetch(`/laminating-services/${service.id}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    editingService.value = data;
    editForm.name = data.name;
    editForm.pouch_size = data.pouch_size;
    editForm.price = data.price;
    editForm.service_amount = data.service_amount;

    isEditModalOpen.value = true;
  } catch (error) {
    console.error('Error fetching service details:', error);
    showNotification('error', 'Error', 'Failed to load service details.');
  }
};

const updateForm = () => {
  if (!editingService.value) return;
  
  editForm.put(`/laminating-services/${editingService.value.id}`, {
    onSuccess: () => {
      fetchServices(); // Refresh the list
      closeEditModal();
    },
    onError: (errors) => {
      console.error('Validation errors:', errors);
    },
  });
};

const deleteService = (id) => {
  if (confirm('Are you sure you want to delete this service?')) {
    useForm({}).delete(`/laminating-services/${id}`, {
      onSuccess: () => {
        fetchServices(); // Refresh the list
      },
      onError: (error) => {
        console.error('Delete error:', error);
        alert('Failed to delete service.');
      },
    });
  }
};
</script>

<style scoped>
.page-content {
  width: 100%;
  margin-bottom: 400px;
}

.content-wrapper {
  background-color: #fff;
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  padding-bottom: 15px;
  border-bottom: 2px solid #e0e0e0;
}

.page-header h2 {
  font-size: 28px;
  font-weight: bold;
  color: #333;
  margin: 0;
}

.modal-content {
  background-color: #fff;
  padding: 20px;
  border-radius: 8px;
  width: 90%;
  max-width: 1000px;
  max-height: 90vh;
  overflow-y: auto;
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

/* Product selection styles */
.product-count {
  color: #666;
  font-weight: normal;
  font-size: 0.9em;
}

.no-products-selected {
  color: #999;
  font-style: italic;
  padding: 10px;
  border: 1px dashed #ddd;
  border-radius: 4px;
  text-align: center;
}

.selected-products-list {
  border: 1px solid #ddd;
  border-radius: 4px;
  max-height: 150px;
  overflow-y: auto;
}

.selected-product-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 12px;
  border-bottom: 1px solid #eee;
  background-color: #f8f9fa;
}

.selected-product-item:last-child {
  border-bottom: none;
}

.remove-product-btn {
  background-color: #dc3545;
  color: white;
  border: none;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  cursor: pointer;
  font-size: 16px;
  line-height: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.remove-product-btn:hover {
  background-color: #c82333;
}
</style>