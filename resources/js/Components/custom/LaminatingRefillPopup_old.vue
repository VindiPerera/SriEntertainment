<template>
  <!-- Success Notification -->
  <div v-if="showSuccessMessage" class="success-notification">
    <div class="success-content">
      <div class="success-icon">✓</div>
      <span class="success-text">{{ successMessage }}</span>
    </div>
  </div>

  <div class="modal-overlay" v-if="isOpen" @click.self="closePopup">
    <div class="modal-content">
      <!-- Modern Header -->
      <div class="modal-header">
        <div class="header-left">
          <div class="header-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M20 6H16L14 4H10L8 6H4C2.9 6 2 6.9 2 8V18C2 19.1 2.9 20 4 20H20C21.1 20 22 19.1 22 18V8C22 6.9 21.1 6 20 6Z" fill="currentColor"/>
            </svg>
          </div>
          <div class="header-text">
            <h2>Select Product</h2>
            <p>Choose a product to refill laminating stock</p>
          </div>
        </div>
        <button type="button" @click.stop="closePopup" class="close-button">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </button>
      </div>

      <!-- Search Section -->
      <div class="search-section">
        <div class="search-container">
          <div class="search-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
              <path d="21 21L16.65 16.65" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </div>
          <input 
            v-model="search" 
            type="text" 
            placeholder="Search products by name, code, or barcode..." 
            @input="handleSearchInput"
            class="search-input"
          />
        </div>
      </div>
      <div class="modal-body">
        <!-- Filter Section -->
        <div v-if="!selectedProductId" class="filter-section">
          <div class="filter-grid">
            <div class="filter-item">
              <label class="filter-label">Category</label>
              <select v-model="selectedCategory" @change="fetchProducts" class="filter-select">
                <option value="">All Categories</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>

            <div class="filter-item">
              <label class="filter-label">Stock Status</label>
              <select v-model="stockStatus" @change="fetchProducts" class="filter-select">
                <option value="">All Stock</option>
                <option value="in">In Stock</option>
                <option value="out">Out of Stock</option>
              </select>
            </div>

            <div class="filter-item">
              <label class="filter-label">Price Order</label>
              <select v-model="sort" @change="fetchProducts" class="filter-select">
                <option value="">Default</option>
                <option value="asc">Low to High</option>
                <option value="desc">High to Low</option>
              </select>
            </div>

            <div class="filter-item">
              <label class="filter-label">Color</label>
              <select v-model="color" @change="fetchProducts" class="filter-select">
                <option value="">All Colors</option>
                <option v-for="colorOption in colors" :key="colorOption.id" :value="colorOption.name">
                  {{ colorOption.name }}
                </option>
              </select>
            </div>

            <div class="filter-item">
              <label class="filter-label">Size</label>
              <select v-model="size" @change="fetchProducts" class="filter-select">
                <option value="">All Sizes</option>
                <option v-for="sizeOption in sizes" :key="sizeOption.id" :value="sizeOption.name">
                  {{ sizeOption.name }}
                </option>
              </select>
            </div>

            <div class="filter-item">
              <button @click="resetFilters" class="reset-button">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M3 12A9 9 0 1 0 12 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  <path d="M3 3L12 12L8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Reset Filters
              </button>
            </div>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading-container">
          <div class="loading-spinner"></div>
          <p class="loading-text">Loading products...</p>
        </div>

        <!-- Product Grid -->
        <div v-else-if="!selectedProductId" class="products-container">
          <div v-if="laminatingProducts.length > 0" class="product-grid">
            <label v-for="product in laminatingProducts" :key="product.id" class="product-card" :class="{ 'selected': selectedProductId === product.id }">
              <input type="radio" :value="product.id" v-model="selectedProductId" class="product-radio">
              
              <div class="product-header">
                <h3 class="product-name">{{ product.name }}</h3>
                <div class="product-status" :class="{ 'in-stock': product.stock_quantity > 0, 'out-of-stock': product.stock_quantity === 0 }">
                  <div class="status-dot"></div>
                  {{ product.stock_quantity > 0 ? 'In Stock' : 'Out of Stock' }}
                </div>
              </div>

              <div class="product-details">
                <div class="detail-row">
                  <span class="detail-label">Price</span>
                  <span class="detail-value price">{{ formatPrice(product.selling_price) }} LKR</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Stock</span>
                  <span class="detail-value stock" :class="{ 'low-stock': product.stock_quantity < 10 }">
                    {{ product.stock_quantity }} units
                  </span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Code</span>
                  <span class="detail-value">{{ product.code }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Barcode</span>
                  <span class="detail-value barcode">{{ product.barcode || 'N/A' }}</span>
                </div>
              </div>

              <div class="selection-indicator">
                <div class="selection-circle">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </div>
              </div>
            </label>
          </div>
          
          <div v-else class="empty-state">
            <div class="empty-icon">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                <path d="21 21L16.65 16.65" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
            </div>
            <h3>No Products Found</h3>
            <p>Try adjusting your search criteria or filters</p>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="laminatingProducts.length > 0 && !selectedProductId" class="pagination">
          <button 
            class="pagination-btn" 
            @click="changePage(currentPage - 1)" 
            :disabled="currentPage === 1"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Previous
          </button>
          
          <div class="pagination-info">
            <span class="page-numbers">Page {{ currentPage }} of {{ totalPages }}</span>
            <span class="total-items">{{ pagination.total }} items</span>
          </div>
          
          <button 
            class="pagination-btn" 
            @click="changePage(currentPage + 1)" 
            :disabled="currentPage === totalPages"
          >
            Next
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </div>
        
        <!-- Refill Form -->
        <div v-else class="refill-section">
          <div class="refill-header">
            <button @click="selectedProductId = null" class="back-button">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M19 12H5M12 19L5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              Back to Products
            </button>
            <h3 class="refill-title">Add Stock: {{ selectedProduct?.name }}</h3>
          </div>

          <div class="selected-product-card">
            <div class="product-summary">
              <h4>{{ selectedProduct?.name }}</h4>
              <div class="summary-grid">
                <div class="summary-item">
                  <span class="summary-label">Current Stock</span>
                  <span class="summary-value">{{ selectedProduct?.stock_quantity || 0 }} units</span>
                </div>
                <div class="summary-item">
                  <span class="summary-label">Selling Price</span>
                  <span class="summary-value">{{ formatPrice(selectedProduct?.selling_price) }} LKR</span>
                </div>
                <div class="summary-item">
                  <span class="summary-label">Product Code</span>
                  <span class="summary-value">{{ selectedProduct?.code || '' }}</span>
                </div>
                <div class="summary-item">
                  <span class="summary-label">Barcode</span>
                  <span class="summary-value">{{ selectedProduct?.barcode || 'N/A' }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="refill-form">
            <div class="form-group">
              <label for="stock" class="form-label">Quantity to Add</label>
              <div class="quantity-input-group">
                <button type="button" @click="decreaseQuantity" class="quantity-btn" :disabled="!stockQuantity || stockQuantity <= 1">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                </button>
                <input 
                  v-model="stockQuantity" 
                  type="number" 
                  id="stock" 
                  placeholder="Enter quantity" 
                  class="quantity-input"
                  min="1"
                  max="10000"
                />
                <button type="button" @click="increaseQuantity" class="quantity-btn">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                </button>
              </div>
              <div v-if="quantityError" class="error-message">{{ quantityError }}</div>
            </div>

            <div class="form-actions">
              <button type="button" class="cancel-btn" @click.stop="closePopup">
                Cancel
              </button>
              <button 
                class="submit-btn" 
                :disabled="!canSubmit" 
                @click="submitRefill"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span v-if="submitting">Adding Stock...</span>
                <span v-else>Add Stock</span>
              </button>
            </div>
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
const colors = ref([]);
const sizes = ref([]);

// Success notification state
const showSuccessMessage = ref(false);
const successMessage = ref('');

let searchTimeout = null;

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
    showSuccessNotification('Laminating stock refilled successfully!');
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

// Handle search input with debouncing
const handleSearchInput = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchProducts();
  }, 500);
};

// Quantity control functions
const increaseQuantity = () => {
  stockQuantity.value = (stockQuantity.value || 0) + 1;
};

const decreaseQuantity = () => {
  if (stockQuantity.value > 1) {
    stockQuantity.value--;
  }
};

// Function to show success notification
const showSuccessNotification = (message) => {
  successMessage.value = message;
  showSuccessMessage.value = true;
  
  // Auto hide after 3 seconds
  setTimeout(() => {
    showSuccessMessage.value = false;
  }, 3000);
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
/* Success Notification */
.success-notification {
  position: fixed;
  top: 24px;
  right: 24px;
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  padding: 16px 24px;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
  z-index: 2000;
  animation: slideInRight 0.3s ease-out;
  backdrop-filter: blur(8px);
}

.success-content {
  display: flex;
  align-items: center;
  gap: 12px;
}

.success-icon {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 16px;
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Modal Overlay */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(8px);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  padding: 20px;
}

.modal-content {
  background: white;
  border-radius: 20px;
  width: 100%;
  max-width: 1400px;
  max-height: 90vh;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  display: flex;
  flex-direction: column;
}

/* Modal Header */
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px 32px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.header-icon {
  width: 48px;
  height: 48px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.header-text h2 {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
  line-height: 1.2;
}

.header-text p {
  margin: 4px 0 0 0;
  opacity: 0.9;
  font-size: 14px;
}

.close-button {
  width: 44px;
  height: 44px;
  background: rgba(255, 255, 255, 0.2);
  border: none;
  border-radius: 12px;
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.close-button:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: scale(1.05);
}

/* Search Section */
.search-section {
  padding: 24px 32px 0;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

.search-container {
  position: relative;
  max-width: 600px;
  margin: 0 auto;
}

.search-icon {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #64748b;
  z-index: 1;
}

.search-input {
  width: 100%;
  padding: 16px 16px 16px 48px;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  font-size: 16px;
  background: white;
  transition: all 0.2s ease;
}

.search-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Modal Body */
.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 24px 32px;
}

/* Filter Section */
.filter-section {
  margin-bottom: 24px;
}

.filter-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  align-items: end;
}

.filter-item {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.filter-label {
  font-size: 14px;
  font-weight: 600;
  color: #374151;
}

.filter-select {
  padding: 12px 16px;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  font-size: 14px;
  background: white;
  transition: all 0.2s ease;
  cursor: pointer;
}

.filter-select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.reset-button {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  height: fit-content;
}

.reset-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);
}

/* Loading */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 20px;
  gap: 16px;
}

.loading-spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #e5e7eb;
  border-top: 4px solid #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.loading-text {
  color: #64748b;
  font-size: 16px;
  margin: 0;
}

/* Products Container */
.products-container {
  min-height: 400px;
}

.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
}

.product-card {
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 16px;
  padding: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.product-card:hover {
  border-color: #667eea;
  transform: translateY(-4px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
}

.product-card.selected {
  border-color: #10b981;
  background: linear-gradient(135deg, #ecfdf5, #f0fdf4);
}

.product-radio {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.product-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.product-name {
  font-size: 18px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
  flex: 1;
  line-height: 1.4;
}

.product-status {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}

.product-status.in-stock {
  background: #dcfce7;
  color: #166534;
}

.product-status.out-of-stock {
  background: #fef2f2;
  color: #dc2626;
}

.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: currentColor;
}

.product-details {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.detail-label {
  font-size: 14px;
  color: #6b7280;
  font-weight: 500;
}

.detail-value {
  font-size: 14px;
  color: #1f2937;
  font-weight: 600;
}

.detail-value.price {
  color: #059669;
}

.detail-value.stock.low-stock {
  color: #dc2626;
}

.detail-value.barcode {
  font-family: 'Courier New', monospace;
  font-size: 12px;
}

.selection-indicator {
  position: absolute;
  top: 16px;
  right: 16px;
}

.selection-circle {
  width: 24px;
  height: 24px;
  border: 2px solid #d1d5db;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: white;
  transition: all 0.2s ease;
}

.product-card.selected .selection-circle {
  border-color: #10b981;
  background: #10b981;
  color: white;
}

/* Empty State */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 20px;
  text-align: center;
}

.empty-icon {
  width: 64px;
  height: 64px;
  color: #d1d5db;
  margin-bottom: 16px;
}

.empty-state h3 {
  font-size: 20px;
  color: #374151;
  margin: 0 0 8px 0;
}

.empty-state p {
  color: #6b7280;
  margin: 0;
}

/* Refill Section */
.refill-section {
  max-width: 800px;
  margin: 0 auto;
}

.refill-header {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 24px;
}

.back-button {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  background: #f3f4f6;
  border: none;
  border-radius: 8px;
  color: #374151;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s ease;
}

.back-button:hover {
  background: #e5e7eb;
}

.refill-title {
  font-size: 24px;
  color: #1f2937;
  margin: 0;
  font-weight: 700;
}

.selected-product-card {
  background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
  border: 2px solid #0ea5e9;
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 24px;
}

.product-summary h4 {
  font-size: 20px;
  color: #0c4a6e;
  margin: 0 0 16px 0;
  font-weight: 700;
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.summary-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.summary-label {
  font-size: 12px;
  color: #0369a1;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.summary-value {
  font-size: 16px;
  color: #0c4a6e;
  font-weight: 700;
}

/* Refill Form */
.refill-form {
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 16px;
  padding: 24px;
}

.form-group {
  margin-bottom: 24px;
}

.form-label {
  display: block;
  font-size: 16px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 12px;
}

.quantity-input-group {
  display: flex;
  align-items: center;
  background: #f9fafb;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
}

.quantity-btn {
  width: 48px;
  height: 48px;
  background: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6b7280;
  transition: all 0.2s ease;
}

.quantity-btn:hover:not(:disabled) {
  background: #f3f4f6;
  color: #374151;
}

.quantity-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.quantity-input {
  flex: 1;
  border: none;
  background: transparent;
  padding: 12px 16px;
  text-align: center;
  font-size: 16px;
  font-weight: 600;
}

.quantity-input:focus {
  outline: none;
}

.error-message {
  color: #dc2626;
  font-size: 14px;
  margin-top: 8px;
  display: block;
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.cancel-btn {
  padding: 12px 24px;
  background: #f3f4f6;
  border: none;
  border-radius: 10px;
  color: #374151;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.cancel-btn:hover {
  background: #e5e7eb;
}

.submit-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: linear-gradient(135deg, #10b981, #059669);
  border: none;
  border-radius: 10px;
  color: white;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.submit-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 32px;
  padding: 20px 0;
  border-top: 1px solid #e5e7eb;
}

.pagination-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  color: #374151;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s ease;
}

.pagination-btn:hover:not(:disabled) {
  border-color: #667eea;
  background: #667eea;
  color: white;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-info {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.page-numbers {
  font-weight: 600;
  color: #374151;
}

.total-items {
  font-size: 14px;
  color: #6b7280;
}

/* Responsive Design */
@media (max-width: 768px) {
  .modal-content {
    margin: 10px;
    max-width: none;
    width: calc(100% - 20px);
  }

  .modal-header {
    padding: 20px;
  }

  .modal-body {
    padding: 20px;
  }

  .filter-grid {
    grid-template-columns: 1fr;
  }

  .product-grid {
    grid-template-columns: 1fr;
  }

  .pagination {
    flex-direction: column;
    gap: 16px;
  }

  .summary-grid {
    grid-template-columns: 1fr;
  }

  .form-actions {
    flex-direction: column-reverse;
  }
}
</style>