<template>
  <div class="content-wrapper">
    <div class="content-container">
      <div class="page-header">
        <h2>Printout Services</h2>
      </div>
      <div class="page-content">
        <!-- Add Button and Search Bar in One Row -->
        <div class="controls-row">
          <div class="search-bar">
            <input v-model="search" type="text" placeholder="Search printout services..." />
          </div>
          <button @click="openCreateForm" class="add-button">Add New Printout Service</button>
          <button @click="openRefillPopup" class="add-button">Refill</button>
        </div>

        <table class="service-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Size</th>
              <th>Side</th>
              <th>Pages</th>
              <th>Color</th>
              <th>Price</th>
              <th>Service Charge</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(service, index) in filteredServices" :key="service.id">
              <td>{{ index + 1 }}</td>
              <td>{{ service.name }}</td>
              <td>{{ service.size }}</td>
              <td>{{ service.side }}</td>
              <td>{{ service.pages }}</td>
              <td>{{ service.color }}</td>
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
              <h2>Create Printout Service</h2>
              <button @click="closeCreateModal" class="close-button">&times;</button>
            </div>
            <div class="modal-body">
              <div class="form-group">
                <label for="name">Name</label>
                <input v-model="createForm.name" type="text" id="name" placeholder="Enter service name" />
                <span v-if="createForm.errors.name" class="error">{{ createForm.errors.name }}</span>
              </div>
              <div class="form-group">
                <label for="size">Size</label>
                <select v-model="createForm.size" id="size">
                  <option value="">Select Size</option>
                  <option value="A4">A4</option>
                  <option value="A3">A3</option>
                  <option value="LG">LG</option>
                  
                </select>
                <span v-if="createForm.errors.size" class="error">{{ createForm.errors.size }}</span>
              </div>
              <div class="form-group">
                <label for="side">Side</label>
                <select v-model="createForm.side" id="side">
                  <option value="">Select Side</option>
                  <option value="Single">Single</option>
                  <option value="Double">Double</option>
                </select>
                <span v-if="createForm.errors.side" class="error">{{ createForm.errors.side }}</span>
              </div>
              <div class="form-group">
                <label for="pages">Pages</label>
                <select v-model="createForm.pages" id="pages">
                   <option value="">Select Pages</option>
                 <option value="one">One</option>
                  <option value="1-20">1-20</option>
                  <option value="20-50">20-50</option>
                  <option value="More than 50">More than 50</option>
                </select>
                <span v-if="createForm.errors.pages" class="error">{{ createForm.errors.pages }}</span>
              </div>
              <div class="form-group">
                <label for="color">Color</label>
                <select v-model="createForm.color" id="color">
                  <option value="">Select Color</option>
                  <option value="Black & White">Black & White</option>
                  <option value="Color">Color</option>
                </select>
                <span v-if="createForm.errors.color" class="error">{{ createForm.errors.color }}</span>
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
              <h2>Edit Printout Service</h2>
              <button @click="closeEditModal" class="close-button">&times;</button>
            </div>
            <div class="modal-body">
              <div class="form-group">
                <label for="edit-name">Name</label>
                <input v-model="editForm.name" type="text" id="edit-name" placeholder="Enter service name" />
                <span v-if="editForm.errors.name" class="error">{{ editForm.errors.name }}</span>
              </div>
              <div class="form-group">
                <label for="edit-size">Size</label>
                <select v-model="editForm.size" id="edit-size">
                  <option value="">Select Size</option>
                  <option value="A4">A4</option>
                  <option value="A3">A3</option>
                  <option value="LG">LG</option>
                </select>
                <span v-if="editForm.errors.size" class="error">{{ editForm.errors.size }}</span>
              </div>
              <div class="form-group">
                <label for="edit-side">Side</label>
                <select v-model="editForm.side" id="edit-side">
                  <option value="">Select Side</option>
                  <option value="Single">Single</option>
                  <option value="Double">Double</option>
                </select>
                <span v-if="editForm.errors.side" class="error">{{ editForm.errors.side }}</span>
              </div>
              <div class="form-group">
                <label for="edit-pages">Pages</label>
                <select v-model="editForm.pages" id="edit-pages">
                   <option value="">Select Pages</option>
                 <option value="one">One</option>
                  <option value="1-20">1-20</option>
                  <option value="20-50">20-50</option>
                  <option value="More than 50">More than 50</option>
                </select>
                <span v-if="editForm.errors.pages" class="error">{{ editForm.errors.pages }}</span>
              </div>
              <div class="form-group">
                <label for="edit-color">Color</label>
                <select v-model="editForm.color" id="edit-color">
                  <option value="">Select Color</option>
                  <option value="Black & White">Black & White</option>
                  <option value="Color">Color</option>
                </select>
                <span v-if="editForm.errors.color" class="error">{{ editForm.errors.color }}</span>
              </div>
              <div class="form-group">
                <label for="edit-price">Price</label>
                <input v-model="editForm.price" type="number" id="edit-price" placeholder="Enter price" step="0.01" />
                <span v-if="editForm.errors.price" class="error">{{ editForm.errors.price }}</span>
              </div>
              <div class="form-group">
                <label for="edit-service_charge">Service Charge </label>
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

        <!-- Refill Modal -->
        <PrintoutRefillPopup 
          v-if="isRefillPopupOpen" 
          @close="handleCloseRefillPopup" 
          :isOpen="isRefillPopupOpen" 
          :modelValue="isRefillPopupOpen" 
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
import axios from "axios";
import PrintoutRefillPopup from './PrintoutRefillPopup.vue';
import NotificationAlert from "./NotificationAlert.vue";

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isRefillPopupOpen = ref(false);
const search = ref("");
const editingService = ref(null);

// Category and product selection variables
const categories = ref([]);
const products = ref([]);
const selectedCategoryId = ref(null);
const selectedProductId = ref(null);
const selectedProducts = ref([]);

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

// Computed property for total price
const totalPriceDisplay = computed(() => {
  const price = Number(createForm.price) || 0;
  const service = Number(createForm.service_charge) || 0;
  return (price + service).toFixed(2);
});

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
    const response = await fetch('/api/printout/low-stock', {
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

// Create form
const createForm = useForm({
  name: "",
  size: "",
  side: "",
  pages: "",
  color: "",
  price: "",
  service_charge: "",
  products: [],
});

// Edit form
const editForm = useForm({
  _method: 'PUT',  // Required for Laravel to recognize this as a PUT request
  name: "",
  size: "",
  side: "",
  pages: "",
  color: "",
  price: "",
  service_charge: "",
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
    const response = await fetch('/printout-services', {
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

// Fetch categories from API
const fetchCategories = async () => {
  try {
    const response = await fetch("/api/categories", {
      headers: {
        Accept: "application/json",
      },
    });
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const data = await response.json();
    console.log('Categories fetched:', data); // Debug log
    categories.value = Array.isArray(data.categories) ? data.categories : [];
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
    console.log('Fetching products for category:', categoryId);
    
    // Use axios with dedicated printout endpoint
    const response = await axios.get('/api/printout/products', {
      params: { category_id: categoryId },
      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest"
      }
    });
    
    console.log('Products response:', response);
    console.log('Products data:', response.data);
    
    products.value = Array.isArray(response.data) ? response.data : [];
    console.log('Products set to:', products.value.length, 'items');
  } catch (error) {
    console.error("Error fetching products:", error);
    if (error.response) {
      console.error('Error response:', error.response.data);
      console.error('Error status:', error.response.status);
    }
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
    showNotification('warning', 'Validation Error', 'Please select at least one product before creating the service.');
    return;
  }
  
  // Validate basic form fields
  if (!createForm.name || !createForm.size || !createForm.side || !createForm.pages || !createForm.color || !createForm.price || !createForm.service_charge) {
    showNotification('warning', 'Validation Error', 'Please fill in all required fields.');
    return;
  }
  
  // Prepare form data with products as array of IDs
  const productsArray = selectedProducts.value.map(p => p.id);
  
  // Log what we're about to submit
  console.log('Submitting form with data:', {
    name: createForm.name,
    size: createForm.size,
    side: createForm.side,
    pages: createForm.pages,
    color: createForm.color,
    price: createForm.price,
    service_charge: createForm.service_charge,
    products: productsArray
  });
  
  // Set the products array on the form
  createForm.products = productsArray;
  
  createForm.post("/printout-services", {
    onSuccess: (response) => {
      console.log('Printout service created successfully:', response);
      showNotification('success', 'Success', 'Printout service created successfully!');
      fetchServices(); // Refresh the list
      closeCreateModal();
      createForm.reset();
      // Reset product selections
      selectedProducts.value = [];
      selectedCategoryId.value = null;
      selectedProductId.value = null;
      products.value = [];
    },
    onError: (errors) => {
      console.error('Validation errors:', errors);
      const errorMessages = Object.values(errors).flat().join('\n');
      showNotification('error', 'Validation Error', 'Error creating printout service:\n' + errorMessages);
    }
  });
};

const editService = (service) => {
  editingService.value = service;
  editForm.name = service.name;
  editForm.size = service.size;
  editForm.side = service.side;
  editForm.pages = service.pages;
  editForm.color = service.color;
  editForm.price = service.price;
  editForm.service_charge = service.service_charge;
  isEditModalOpen.value = true;
};

const updateForm = () => {
  if (!editingService.value) return;
  
  editForm.post(`/printout-services/${editingService.value.id}`, {
    onSuccess: () => {
      fetchServices(); // Refresh the list
      closeEditModal();
    },
    preserveScroll: true,
  });
};

const deleteService = (id) => {
  if (confirm('Are you sure you want to delete this service?')) {
    useForm({}).delete(`/printout-services/${id}`, {
      onSuccess: () => {
        fetchServices(); // Refresh the list
      },
    });
  }
};

// Add debugging logs to openRefillPopup and @close event
const openRefillPopup = () => {
  console.log("Opening refill popup...");
  isRefillPopupOpen.value = true;
};

const handleCloseRefillPopup = () => {
  console.log("Closing refill popup...");
  isRefillPopupOpen.value = false;
};
</script>

<style scoped>
.content-wrapper {
  width: 100%;
  min-height: 100%;
  background-color: #fff;
  margin-bottom: 400px;
}

.content-container {
  background-color: #fff;
  padding: 20px;
  border-radius: 8px;
  width: 100%;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 10px;
  border-bottom: 1px solid #ddd;
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