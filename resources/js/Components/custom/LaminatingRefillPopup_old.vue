<template>
  <div v-if="isOpen" class="modal-overlay">
    <div class="pos-modal-content">
      <div class="modal-header">
        <h2>{{ selectedProductId ? 'Refill Laminating Stock' : 'Select Product for Laminating' }}</h2>
        <div v-if="!selectedProductId" class="search-bar">
          <input v-model="search" type="text" placeholder="Search products..." @input="handleSearch" />
        </div>
        <button type="button" @click.stop="closePopup" class="close-button" aria-label="Close modal">×</button>
      </div>
      
      <div class="modal-body">
        <!-- Product Selection View -->
        <div v-if="!selectedProductId" class="product-selection-section">
          <div class="filter-options">
            <select v-model="selectedCategory" @change="fetchProducts" class="filter-select">
              <option value="">Filter by Category</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>

            <select v-model="stockStatus" @change="fetchProducts" class="filter-select">
              <option value="">Filter by Stock</option>
              <option value="in">In Stock</option>
              <option value="out">Out of Stock</option>
            </select>

            <select v-model="sort" @change="fetchProducts" class="filter-select">
              <option value="">Filter by Price</option>
              <option value="asc">Price: Low to High</option>
              <option value="desc">Price: High to Low</option>
            </select>

            <select v-model="color" @change="fetchProducts" class="filter-select">
              <option value="">Filter by Color</option>
              <option v-for="colorOption in colors" :key="colorOption.id" :value="colorOption.name">
                {{ colorOption.name }}
              </option>
            </select>

            <select v-model="size" @change="fetchProducts" class="filter-select">
              <option value="">Filter by Size</option>
              <option v-for="sizeOption in sizes" :key="sizeOption.id" :value="sizeOption.name">
                {{ sizeOption.name }}
              </option>
            </select>

            <button @click="resetFilters" class="reset-button">Reset</button>
            </div>

          <!-- Loading State -->
          <div v-if="loading" class="loading-state">
            <p>Loading products...</p>
          </div>

          <!-- Products Grid -->
          <div v-else-if="laminatingProducts.length > 0" class="horizontal-product-scroll">
            <div class="product-grid">
              <label v-for="product in laminatingProducts" :key="product.id" class="product-card">
                <input 
                  type="radio" 
                  :value="product.id" 
                  v-model="selectedProductId" 
                  name="product-select"
                  class="radio-input"
                >
                <div class="product-card-content">
                  <h3>{{ product.name }}</h3>
                  <div class="product-info">
                    <div class="info-line">Price: {{ formatPrice(product.selling_price) }} LKR</div>
                    <div class="info-line">Stock: {{ product.stock_quantity }}</div>
                    <div class="info-line barcode">Barcode: {{ product.barcode || 'N/A' }}</div>
                  </div>
                  <div class="select-circle">
                    <div class="inner-circle"></div>
                  </div>
                </div>
              </label>
            </div>
          </div>

          <!-- No Products -->
          <div v-else class="no-products">
            <p>No products found</p>
          </div>

          <div class="pos-pagination">
            <button 
              class="pagination-button" 
              @click="changePage(currentPage - 1)" 
              :disabled="currentPage === 1"
            >
              Previous
            </button>
            <span class="pagination-info">
              Page {{ currentPage }} of {{ totalPages }}
            </span>
            <button 
              class="pagination-button" 
              @click="changePage(currentPage + 1)" 
              :disabled="currentPage === totalPages"
            >
              Next
            </button>
          </div>
        </div>
        
        <!-- Stock Refill Form -->
        <div v-if="selectedProductId" class="refill-form-section">
            <div class="selected-product-header">
              <h3>Add Stock for {{ selectedProduct?.name }}</h3>
              <button @click="selectedProductId = null" class="change-product-button">
                Change Product
              </button>
            </div>
            
            <div class="selected-product-details">
              <div class="info-row">
                <span class="info-label">Current Stock:</span>
                <span class="info-value" :class="getStockClass(selectedProduct?.stock_quantity)">
                  {{ selectedProduct?.stock_quantity || 0 }}
                </span>
              </div>
              <div class="info-row">
                <span class="info-label">Price:</span>
                <span class="info-value">{{ formatPrice(selectedProduct?.selling_price) }} LKR</span>
              </div>
              <div class="info-row">
                <span class="info-label">Barcode:</span>
                <span class="info-value">{{ selectedProduct?.barcode || 'N/A' }}</span>
              </div>
              <div class="info-row">
                <span class="info-label">Category:</span>
                <span class="info-value">{{ selectedProduct?.category?.name || 'N/A' }}</span>
              </div>
            </div>
            
            <div class="form-group">
              <label for="stockQuantity" class="form-label">
                Stock Quantity to Add <span class="required">*</span>
              </label>
              <input 
                v-model.number="stockQuantity" 
                type="number" 
                id="stockQuantity"
                placeholder="Enter quantity to add"
                class="form-input"
                :class="{ 'error': quantityError }"
                min="1"
                max="10000"
              />
              <div v-if="quantityError" class="error-message">{{ quantityError }}</div>
            </div>

           
            
            <div class="form-actions">
              <button 
                class="submit-button" 
                :disabled="!canSubmit" 
                @click="submitRefill"
                :class="{ 'loading': submitting }"
              >
                <span v-if="submitting">Adding Stock...</span>
                <span v-else>Add Stock</span>
              </button>
              <button class="cancel-button" @click="closePopup" :disabled="submitting">
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  }
});

const emit = defineEmits(['close', 'refill-submitted']);

// Reactive data
const search = ref("");
const products = ref([]);
const categories = ref([]);
const selectedProductId = ref(null);
const pagination = ref({});
const stockQuantity = ref(null);
const selectedCategory = ref("");
const stockStatus = ref("");
const loading = ref(false);
const submitting = ref(false);
const currentPage = ref(1);

// Fixed: Define missing variables
const sort = ref("");
const color = ref("");
const size = ref("");

// Computed properties
const laminatingProducts = computed(() => {
  return products.value; // Display all products without filtering
});

const selectedProduct = computed(() => {
  return products.value.find((p) => p.id === selectedProductId.value) || null;
});

const totalPages = computed(() => {
  return pagination.value.last_page || 1;
});

const quantityError = computed(() => {
  if (!stockQuantity.value) return 'Quantity is required';
  if (stockQuantity.value < 1) return 'Quantity must be at least 1';
  if (stockQuantity.value > 10000) return 'Quantity is too large';
  return null;
});

const canSubmit = computed(() => {
  return stockQuantity.value && stockQuantity.value > 0 && !quantityError.value && !submitting.value;
});

// Methods
const formatPrice = (price) => {
  return price ? Number(price).toLocaleString() : '0';
};

const getStockClass = (stock) => {
  if (stock === 0) return 'out-of-stock';
  if (stock < 10) return 'low-stock';
  return 'in-stock';
};

const selectProduct = (product) => {
  selectedProductId.value = product.id;
  stockQuantity.value = null;
};

const fetchProducts = async (page = 1) => {
  console.log("Fetching products for page:", page, "with filters:", {
    search: search.value.trim(),
    category: selectedCategory.value,
    stock: stockStatus.value,
    sort: sort.value,
    color: color.value,
    size: size.value,
  });
  loading.value = true;
  try {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    const response = await fetch(`/api/products`, {
      method: "POST",
      headers: {
        "Accept": "application/json",
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": token || '',
      },
      body: JSON.stringify({
        page,
        search: search.value.trim(),
        category: selectedCategory.value,
        stock: stockStatus.value,
        sort: sort.value,
        color: color.value,
        size: size.value,
      }),
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    
    if (data && data.products) {
      products.value = data.products.data || [];
      pagination.value = {
        current_page: data.products.current_page || 1,
        last_page: data.products.last_page || 1,
        prev_page_url: data.products.prev_page_url,
        next_page_url: data.products.next_page_url,
        total: data.products.total || 0,
      };
    } else {
      console.error("Unexpected API response structure:", data);
      products.value = [];
    }
  } catch (error) {
    console.error("Error fetching products:", error);
    alert("Failed to load products. Please try again.");
  } finally {
    loading.value = false;
  }
};

const fetchCategories = async () => {
  try {
    const response = await fetch('/api/categories');
    
    // Check if response is HTML instead of JSON
    const contentType = response.headers.get('content-type');
    if (!contentType || !contentType.includes('application/json')) {
      console.warn('Categories endpoint returned non-JSON response. Using empty categories array.');
      categories.value = [];
      return;
    }
    
    if (!response.ok) {
      throw new Error('Failed to fetch categories');
    }
    
    categories.value = await response.json();
  } catch (error) {
    console.error('Error fetching categories:', error);
    // Use empty array as fallback
    categories.value = [];
  }
};

const closePopup = () => {
  selectedProductId.value = null;
  stockQuantity.value = null;
  resetFilters();
  emit('close');
};

const submitRefill = async () => {
  if (!canSubmit.value) return;

  submitting.value = true;
  try {
    const response = await fetch('/api/refill-laminating', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        product_id: selectedProduct.value.id,
        quantity: stockQuantity.value
      })
    });

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.message || 'Failed to submit refill');
    }

    const result = await response.json();
    alert('Laminating stock refilled successfully!');
    emit('refill-submitted', {
      product: selectedProduct.value,
      quantity: stockQuantity.value,
      newStock: result.new_stock
    });
    closePopup();
  } catch (error) {
    console.error('Error submitting laminating refill:', error);
    alert(error.message || 'Failed to submit refill. Please try again.');
  } finally {
    submitting.value = false;
  }
};

const resetFilters = () => {
  selectedCategory.value = "";
  stockStatus.value = "";
  search.value = "";
  currentPage.value = 1;
  fetchProducts();
};

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    fetchProducts(page);
  }
};

const handleSearch = () => {
  // Debounce search
  clearTimeout(search.timeout);
  search.timeout = setTimeout(() => {
    fetchProducts();
  }, 500);
};

// Watchers
watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    selectedProductId.value = null;
    stockQuantity.value = null;
    resetFilters();
  }
});

// Lifecycle hooks
onMounted(() => {
  fetchCategories();
});
</script>

<style scoped>
/* Your existing styles remain the same */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.pos-modal-content {
  background-color: #fff;
  padding: 20px;
  border-radius: 8px;
  width: 95%;
  height: 90vh;
  max-width: 1200px;
  overflow-y: auto;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  border-bottom: 1px solid #e0e0e0;
  background-color: #f8f9fa;
  border-radius: 8px 8px 0 0;
  position: relative; /* allow absolute close button */
}

.modal-header h2 {
  margin: 0;
  font-size: 24px;
  color: #333;
  font-weight: 600;
}

.close-button {
  position: absolute;
  right: 16px;
  top: 12px;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: #ff4d4d;
  color: white;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  cursor: pointer;
  z-index: 30;
}

.modal-body {
  padding: 20px 0;
}

.pos-layout {
  display: flex;
  flex-direction: column;
  gap: 20px;
  height: 100%;
}

.product-selection-section {
  display: flex;
  flex-direction: column;
  gap: 20px;
  height: 100%;
}

.search-bar {
  margin-bottom: 15px;
}

.search-input {
  width: 100%;
  padding: 12px 15px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 16px;
  transition: border-color 0.2s;
}

.search-input:focus {
  border-color: #4dabf7;
  outline: none;
  box-shadow: 0 0 0 3px rgba(77, 171, 247, 0.2);
}

.filter-options {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 20px;
}

.filter-select {
  flex: 1;
  min-width: 180px;
  padding: 10px 15px;
  border: 1px solid #ddd;
  border-radius: 6px;
  background-color: white;
  font-size: 14px;
  color: #495057;
  cursor: pointer;
  transition: border-color 0.2s;
}

.filter-select:focus {
  border-color: #4dabf7;
  outline: none;
}

.reset-button {
  padding: 10px 20px;
  background-color: #6c757d;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: background-color 0.2s;
  min-width: 140px;
}

.reset-button:hover {
  background-color: #5a6268;
}

.product-list-container {
  flex-grow: 1;
  overflow: hidden;
}

.loading-state, .empty-state {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 200px;
  color: #666;
  font-size: 16px;
}

.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 15px;
  max-height: 500px;
  overflow-y: auto;
  padding: 10px;
}

.product-card {
  background-color: #fff;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  padding: 15px;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
}

.product-card:hover {
  border-color: #2196F3;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.product-card.selected {
  border-color: #2196F3;
  background-color: #f0f8ff;
}

.product-card-content {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.product-card-content h3 {
  margin: 0;
  font-size: 16px;
  color: #333;
  font-weight: 600;
}

.product-info {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.info-line {
  display: flex;
  justify-content: between;
  gap: 8px;
}

.info-line .label {
  color: #666;
  font-size: 14px;
  min-width: 60px;
}

.info-line .value {
  color: #333;
  font-size: 14px;
  font-weight: 500;
}

.barcode .value {
  font-family: monospace;
  font-size: 13px;
}

.out-of-stock {
  color: #dc3545;
  font-weight: bold;
}

.low-stock {
  color: #ffc107;
  font-weight: bold;
}

.in-stock {
  color: #28a745;
  font-weight: bold;
}

.select-indicator {
  position: absolute;
  top: 15px;
  right: 15px;
  width: 24px;
  height: 24px;
  border: 2px solid #ccc;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.product-card.selected .select-indicator {
  border-color: #2196F3;
  background-color: #2196F3;
}

.checkmark {
  color: white;
  font-size: 14px;
  font-weight: bold;
}

.pos-pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  background-color: #f5f5f5;
  border-radius: 6px;
  margin-top: 10px;
}

.pagination-button {
  padding: 8px 16px;
  background-color: #fff;
  border: 1px solid #ddd;
  border-radius: 4px;
  color: #333;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
}

.pagination-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-button:not(:disabled):hover {
  background-color: #e0e0e0;
}

.pagination-info {
  font-size: 14px;
  color: #666;
  font-weight: 500;
}

.refill-form-section {
  padding: 20px;
  border: 1px solid #e0e0e0;
  border-radius: 6px;
  background-color: #f9f9f9;
}

.selected-product-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid #e0e0e0;
}

.selected-product-header h3 {
  margin: 0;
  color: #333;
  font-size: 20px;
  font-weight: 600;
}

.change-product-button {
  background-color: #ffc107;
  color: #212529;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: background-color 0.2s;
}

.change-product-button:hover {
  background-color: #e0a800;
}

.selected-product-details {
  margin-bottom: 20px;
  padding: 15px;
  background-color: #fff;
  border-radius: 6px;
  border: 1px solid #e0e0e0;
}

.info-row {
  display: grid;
  grid-template-columns: 140px 1fr;
  align-items: center;
  margin-bottom: 10px;
}

.info-label {
  color: #666;
  font-weight: 500;
}

.info-value {
  color: #333;
  font-weight: 500;
}

.form-group {
  margin-bottom: 25px;
}

.form-label {
  display: block;
  margin-bottom: 8px;
  color: #333;
  font-weight: 500;
  font-size: 16px;
}

.required {
  color: #dc3545;
}

.form-input {
  width: 100%;
  padding: 12px 15px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 16px;
  transition: border-color 0.2s;
}

.form-input:focus {
  border-color: #4dabf7;
  outline: none;
  box-shadow: 0 0 0 3px rgba(77, 171, 247, 0.2);
}

.form-input.error {
  border-color: #dc3545;
}

.error-message {
  color: #dc3545;
  font-size: 14px;
  margin-top: 5px;
}

.summary-section {
  background-color: #fff;
  padding: 15px;
  border-radius: 6px;
  border: 1px solid #e0e0e0;
  margin-bottom: 20px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
  padding: 5px 0;
}

.summary-row.total {
  border-top: 1px solid #e0e0e0;
  padding-top: 10px;
  margin-top: 5px;
  font-weight: bold;
  color: #333;
}

.summary-label {
  color: #666;
}

.summary-value {
  color: #333;
  font-weight: 500;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 15px;
}

.submit-button {
  background-color: #28a745;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 500;
  transition: background-color 0.2s;
  min-width: 140px;
}

.submit-button:disabled {
  background-color: #6c757d;
  cursor: not-allowed;
}

.submit-button:not(:disabled):hover {
  background-color: #218838;
}

.submit-button.loading {
  background-color: #6c757d;
  cursor: not-allowed;
}

.cancel-button {
  background-color: #6c757d;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 500;
  transition: background-color 0.2s;
  min-width: 100px;
}

.cancel-button:hover:not(:disabled) {
  background-color: #5a6268;
}

.cancel-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .pos-modal-content {
    width: 98%;
    height: 95vh;
    padding: 10px;
  }
  
  .modal-header {
    flex-direction: column;
    gap: 10px;
    align-items: flex-start;
  }
  
  .filter-options {
    flex-direction: column;
  }
  
  .filter-select, .reset-button {
    min-width: 100%;
  }
  
  .product-grid {
    grid-template-columns: 1fr;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .submit-button, .cancel-button {
    width: 100%;
  }
  
  .info-row {
    grid-template-columns: 1fr;
    gap: 5px;
  }
}
</style>