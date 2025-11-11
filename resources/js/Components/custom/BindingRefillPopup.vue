<template>
  <!-- Single Unified Modal -->
  <div v-if="modelValue" class="modal-overlay">
    <div class="pos-modal-content">
      <div class="modal-header">
        <h2>{{ selectedProductId ? 'Refill Binding Stock' : 'Select Product for Binding' }}</h2>
        <div v-if="!selectedProductId" class="search-bar">
          <input v-model="search" type="text" placeholder="Search products..." @input="fetchProducts" />
        </div>
        <button @click="closeModal" class="close-button" title="Close">×</button>
      </div>
      
      <div class="modal-body">
        <!-- Product Selection View -->
        <div v-if="!selectedProductId" class="product-selection-section">
          <div class="filter-options">
            <select v-model="selectedCategory" @change="fetchProducts" class="filter-select">
              <option value="">Filter by Category</option>
              <option v-for="category in allcategories" :key="category.id" :value="category.id">
                {{ category.hierarchy_string ? category.hierarchy_string + " → " + category.name : category.name }}
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
          <div v-else-if="products.length > 0" class="horizontal-product-scroll">
            <div class="product-grid">
              <label v-for="product in products" :key="product.id" class="product-card">
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
                    <div class="info-line">Price: {{ product.selling_price }}.00 LKR</div>
                    <div class="info-line">Stock: {{ product.stock_quantity }}</div>
                    <div class="info-line barcode">Barcode: {{ product.barcode }}</div>
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

          <!-- Pagination -->
          <div v-if="products.length > 0" class="pos-pagination">
            <button 
              class="pagination-button" 
              @click="changePage(pagination.current_page - 1)" 
              :disabled="!pagination.prev_page_url"
            >
              Previous
            </button>
            <span class="pagination-info">
              Page {{ currentPage }} of {{ totalPages }}
            </span>
            <button 
              class="pagination-button" 
              @click="changePage(pagination.current_page + 1)" 
              :disabled="!pagination.next_page_url"
            >
              Next
            </button>
          </div>
        </div>

        <!-- Stock Refill Form -->
        <div v-else class="refill-form-section">
          <button @click="selectedProductId = null" class="back-button">
            ← Change Product
          </button>

          <div class="selected-product-info">
            <h3>{{ selectedProduct?.name }}</h3>
            
            <div class="selected-product-details">
              <div class="info-row">
                <span class="info-label">Current Stock:</span>
                <span class="info-value">{{ selectedProduct?.stock_quantity || 0 }}</span>
              </div>
              <div class="info-row">
                <span class="info-label">Price:</span>
                <span class="info-value">{{ selectedProduct?.selling_price || 0 }}.00 LKR</span>
              </div>
              <div class="info-row">
                <span class="info-label">Barcode:</span>
                <span class="info-value">{{ selectedProduct?.barcode || '' }}</span>
              </div>
            </div>

            <div class="form-group">
              <label for="stock">Stock Quantity to Add</label>
              <input 
                v-model="stockQuantity" 
                type="number" 
                id="stock" 
                placeholder="Enter stock quantity" 
                class="form-input"
                min="1"
              />
              <span v-if="showError" class="error-message">Please enter a valid quantity</span>
            </div>

            <div class="form-actions">
              <button class="submit-button" :disabled="!stockQuantity" @click="submitStock">
                Add Stock
              </button>
              <button class="cancel-button" @click="closeModal">Cancel</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  }
});

const emit = defineEmits(['update:modelValue', 'refill-submitted']);

// State
const search = ref("");
const products = ref([]);
const selectedProductId = ref(null);
const stockQuantity = ref(null);
const loading = ref(false);
const showError = ref(false);

// Filters
const selectedCategory = ref("");
const stockStatus = ref("");
const sort = ref("");
const color = ref("");
const size = ref("");

// Filter options
const allcategories = ref([]);
const colors = ref([]);
const sizes = ref([]);

// Pagination
const pagination = ref({
  current_page: 1,
  last_page: 1,
  prev_page_url: null,
  next_page_url: null,
  total: 0
});

// Computed
const selectedProduct = computed(() => {
  return products.value.find((p) => p.id === selectedProductId.value) || null;
});

const currentPage = computed(() => {
  return pagination.value.current_page || 1;
});

const totalPages = computed(() => {
  return pagination.value.last_page || 1;
});

// Methods
const closeModal = () => {
  emit('update:modelValue', false);
  resetState();
};

const resetState = () => {
  selectedProductId.value = null;
  stockQuantity.value = null;
  showError.value = false;
  search.value = "";
};

const resetFilters = () => {
  selectedCategory.value = "";
  stockStatus.value = "";
  sort.value = "";
  color.value = "";
  size.value = "";
  search.value = "";
  fetchProducts();
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchProducts(page);
  }
};

const fetchProducts = async (page = 1) => {
  loading.value = true;
  try {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    const response = await fetch(`/api/products?page=${page}`, {
      method: "POST",
      headers: {
        "Accept": "application/json",
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": token || '',
      },
      body: JSON.stringify({
        page: page,
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
    console.log("API Response:", data);
    
    if (data && data.products) {
      products.value = data.products.data || [];
      pagination.value = {
        current_page: data.products.current_page || 1,
        last_page: data.products.last_page || 1,
        prev_page_url: data.products.prev_page_url,
        next_page_url: data.products.next_page_url,
        total: data.products.total || 0,
      };

      // Load filter options on first load
      if (data.categories) allcategories.value = data.categories;
      if (data.colors) colors.value = data.colors;
      if (data.sizes) sizes.value = data.sizes;
    }
  } catch (error) {
    console.error("Error fetching products:", error);
    alert("Failed to load products. Please try again.");
  } finally {
    loading.value = false;
  }
};

const submitStock = async () => {
  if (!stockQuantity.value || stockQuantity.value <= 0) {
    showError.value = true;
    return;
  }

  showError.value = false;

  try {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const response = await fetch("/api/refill-binding", {
      method: "POST",
      headers: {
        "Accept": "application/json",
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": token || '',
      },
      body: JSON.stringify({
        product_id: selectedProductId.value,
        product_name: selectedProduct.value.name,
        quantity: parseInt(stockQuantity.value),
      }),
    });

    if (!response.ok) {
      const errorData = await response.json();
      console.error("Error:", errorData);
      alert(errorData.message || "Failed to refill stock. Please try again.");
      return;
    }

    const result = await response.json();
    alert("Binding stock refilled successfully!");
    emit('refill-submitted');
    closeModal();
  } catch (error) {
    console.error("Error submitting stock refill:", error);
    alert("An error occurred while refilling stock.");
  }
};

// Watch for modal open
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    resetState();
    fetchProducts(1);
  }
});
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.pos-modal-content {
  background-color: #fff;
  padding: 20px;
  border-radius: 12px;
  width: 95%;
  height: 90vh;
  max-width: 1200px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 15px;
  border-bottom: 2px solid #e0e0e0;
  margin-bottom: 20px;
}

.modal-header h2 {
  margin: 0;
  font-size: 22px;
  color: #333;
  font-weight: 600;
}

.search-bar {
  flex: 1;
  max-width: 400px;
  margin: 0 20px;
}

.search-bar input {
  width: 100%;
  padding: 10px 16px;
  border: 2px solid #ddd;
  border-radius: 8px;
  font-size: 15px;
}

.search-bar input:focus {
  outline: none;
  border-color: #4CAF50;
}

.close-button {
  background: #f44336;
  color: white;
  border: none;
  width: 35px;
  height: 35px;
  border-radius: 50%;
  font-size: 24px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.close-button:hover {
  background: #d32f2f;
}

.modal-body {
  flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
}

.product-selection-section {
  display: flex;
  flex-direction: column;
  height: 100%;
  gap: 15px;
}

.filter-options {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 10px;
}

.filter-select {
  flex: 1;
  min-width: 150px;
  padding: 10px 14px;
  border: 2px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  background: white;
  cursor: pointer;
}

.filter-select:focus {
  outline: none;
  border-color: #4CAF50;
}

.reset-button {
  padding: 10px 20px;
  background: #2196F3;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  flex-shrink: 0;
}

.reset-button:hover {
  background: #1976D2;
}

.loading-state, .no-products {
  text-align: center;
  padding: 60px 20px;
  color: #666;
  font-size: 16px;
}

.horizontal-product-scroll {
  overflow-x: auto;
  padding: 15px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  background-color: #f9f9f9;
  flex-grow: 1;
}

.product-grid {
  display: flex;
  gap: 15px;
  width: max-content;
  min-width: 100%;
}

.product-card {
  display: block;
  background-color: #fff;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  padding: 15px;
  cursor: pointer;
  position: relative;
  transition: all 0.3s ease;
  min-width: 260px;
  flex-shrink: 0;
}

.radio-input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
}

.product-card:hover {
  border-color: #4CAF50;
  box-shadow: 0 4px 12px rgba(76, 175, 80, 0.2);
  transform: translateY(-2px);
}

.radio-input:checked + .product-card-content {
  background-color: #f0f8f0;
}

.radio-input:checked ~ .product-card {
  border-color: #4CAF50;
  background-color: #f0f8f0;
}

.product-card-content {
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding-right: 30px;
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
  color: #666;
  font-size: 14px;
}

.barcode {
  color: #888;
  font-size: 13px;
}

.select-circle {
  position: absolute;
  top: 50%;
  right: 15px;
  transform: translateY(-50%);
  width: 22px;
  height: 22px;
  border: 2px solid #ccc;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
}

.radio-input:checked + .product-card-content .select-circle {
  border-color: #4CAF50;
  background-color: #4CAF50;
}

.inner-circle {
  width: 0;
  height: 0;
  background-color: white;
  border-radius: 50%;
  transition: all 0.3s;
}

.radio-input:checked + .product-card-content .select-circle .inner-circle {
  width: 10px;
  height: 10px;
}

.pos-pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  background-color: #f5f5f5;
  border-radius: 8px;
  margin-top: auto;
}

.pagination-button {
  padding: 10px 20px;
  background-color: #fff;
  border: 2px solid #ddd;
  border-radius: 6px;
  color: #333;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s;
}

.pagination-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-button:not(:disabled):hover {
  background-color: #4CAF50;
  color: white;
  border-color: #4CAF50;
}

.pagination-info {
  font-size: 14px;
  color: #666;
  font-weight: 500;
}

.refill-form-section {
  padding: 20px;
}

.back-button {
  padding: 10px 16px;
  background: #6c757d;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  margin-bottom: 20px;
}

.back-button:hover {
  background: #5a6268;
}

.selected-product-info h3 {
  margin: 0 0 20px 0;
  font-size: 20px;
  color: #333;
  font-weight: 600;
}

.selected-product-details {
  background: #f9f9f9;
  padding: 20px;
  border-radius: 8px;
  border: 2px solid #e0e0e0;
  margin-bottom: 24px;
}

.info-row {
  display: grid;
  grid-template-columns: 140px 1fr;
  align-items: center;
  padding: 10px 0;
  border-bottom: 1px solid #e0e0e0;
}

.info-row:last-child {
  border-bottom: none;
}

.info-label {
  color: #666;
  font-weight: 500;
  font-size: 14px;
}

.info-value {
  color: #333;
  font-weight: 600;
  font-size: 14px;
}

.form-group {
  margin-bottom: 24px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  color: #333;
  font-weight: 600;
  font-size: 15px;
}

.form-input {
  width: 100%;
  padding: 12px 16px;
  border: 2px solid #ddd;
  border-radius: 8px;
  font-size: 15px;
}

.form-input:focus {
  outline: none;
  border-color: #4CAF50;
}

.error-message {
  color: #f44336;
  font-size: 13px;
  margin-top: 6px;
  display: block;
  font-weight: 500;
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.submit-button {
  padding: 12px 28px;
  background: #4CAF50;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 15px;
}

.submit-button:hover:not(:disabled) {
  background: #45a049;
}

.submit-button:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.cancel-button {
  padding: 12px 28px;
  background: #6c757d;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 15px;
}

.cancel-button:hover {
  background: #5a6268;
}
</style>