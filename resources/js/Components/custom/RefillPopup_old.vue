<template>
  <div class="modal-overlay" v-if="modelValue">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Refill Stock</h2>
        <button @click="openProductSelection" class="user-manual-button">User Manual</button>
        <button type="button" @click.stop="closeModal" class="close-button" aria-label="Close modal">×</button>
      </div>
      <div class="modal-body">
        <div class="selected-product-info">
          <h3>Selected Product</h3>
          <div class="info-grid">
            <div class="info-row">
              <span class="info-label">Name:</span>
              <span class="info-value">{{ selectedProduct?.name || '' }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Current Stock:</span>
              <span class="info-value">{{ selectedProduct?.stock_quantity || '' }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Price:</span>
              <span class="info-value">{{ selectedProduct?.selling_price || '' }}.00 LKR</span>
            </div>
            <div class="info-row">
              <span class="info-label">Barcode:</span>
              <span class="info-value">{{ selectedProduct?.barcode || '' }}</span>
            </div>
          </div>
          <div class="form-group">
            <label for="quantity">Add Stock Quantity</label>
            <input 
              v-model="quantity" 
              type="number" 
              id="quantity" 
              class="form-input"
              :class="{ 'error': showError }"
            />
            <span v-if="showError" class="error-message">The stock field is required.</span>
          </div>
        </div>
        <div class="form-actions">
          <button 
            @click="submitRefill" 
            class="add-stock-button"
          >
            Add Stock
          </button>
          <button @click="closeModal" class="cancel-button">Cancel</button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Combined Product Selection and Refill Modal -->
  <div v-if="isRefillModalOpen" class="modal-overlay">
    <div class="pos-modal-content">
        <div class="modal-header">
        <h2>Select Product</h2>
        <div class="search-bar">
          <input v-model="search" type="text" placeholder="Search products..." @input="fetchProducts" />
        </div>
        <button type="button" @click.stop="$emit('close')" class="close-button" aria-label="Close modal">×</button>
      </div>
      <div class="modal-body">
        <div class="pos-layout">
          <!-- Product Selection Section -->
          <div v-if="!selectedProductId" class="product-selection-section">
            <div class="filter-options">
              <select
                v-model="selectedCategory"
                @change="fetchProducts"
                class="px-6 py-3 text-xl font-normal tracking-wider text-blue-600 bg-white rounded-lg cursor-pointer custom-select"
              >
                <option value="">Filter by Category</option>
                <option
                  v-for="category in allcategories"
                  :key="category.id"
                  :value="category.id"
                >
                  {{
                    category.hierarchy_string
                      ? category.hierarchy_string + " ----> " + category.name
                      : category.name
                  }}
                </option>
              </select>

              <select
                v-model="stockStatus"
                @change="fetchProducts"
                class="px-6 py-3 text-xl font-normal tracking-wider text-blue-600 bg-white rounded-lg cursor-pointer custom-select"
              >
                <option value="">Filter by Stock</option>
                <option value="in">In Stock</option>
                <option value="out">Out of Stock</option>
              </select>

              <select
                v-model="sort"
                @change="fetchProducts"
                class="px-6 py-3 text-xl font-normal tracking-wider text-blue-600 bg-white rounded-lg cursor-pointer custom-select"
              >
                <option value="">Filter by Price</option>
                <option value="asc">Ascending</option>
                <option value="desc">Descending</option>
              </select>

              <select
                v-model="color"
                @change="fetchProducts"
                class="px-6 py-3 text-xl font-normal tracking-wider text-blue-600 bg-white rounded-lg cursor-pointer custom-select"
              >
                <option value="">Filter by Color</option>
                <option
                  v-for="colorOption in colors"
                  :key="colorOption.id"
                  :value="colorOption.name"
                >
                  {{ colorOption.name }}
                </option>
              </select>

              <select
                v-model="size"
                @change="fetchProducts"
                class="px-6 py-3 text-xl font-normal tracking-wider text-blue-600 bg-white rounded-lg cursor-pointer custom-select"
              >
                <option value="">Filter by Size</option>
                <option
                  v-for="sizeOption in sizes"
                  :key="sizeOption.id"
                  :value="sizeOption.name"
                >
                  {{ sizeOption.name }}
                </option>
              </select>

              <span
                @click="resetFilters"
                class="px-6 py-3 text-xl font-normal tracking-wider text-white text-center bg-blue-600 rounded-lg custom-select"
              >
                Reset
              </span>
            </div>

            <div class="horizontal-product-scroll">
              <div class="product-grid">
                <label 
                  v-for="product in products" 
                  :key="product.id" 
                  class="product-card"
                >
                  <input 
                    type="radio" 
                    :value="product.id" 
                    v-model="selectedProductId" 
                    :name="'product-select'"
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
            <div class="pos-pagination">
              <button 
                class="pagination-button" 
                @click="fetchProducts(pagination.prev_page_url)" 
                :disabled="!pagination.prev_page_url"
              >
                Previous
              </button>
              <span class="pagination-info">
                Page {{ currentPage }} of {{ totalPages }}
              </span>
              <button 
                class="pagination-button" 
                @click="fetchProducts(pagination.next_page_url)" 
                :disabled="!pagination.next_page_url"
              >
                Next
              </button>
            </div>
          </div>
          
          <!-- Refill Stock Form Section -->
          <div v-else class="refill-form-section">
            <div class="selected-product-header">
              <h3>Add Stock for {{ selectedProduct?.name }}</h3>
              <button @click="selectedProductId = null" class="change-product-button">
                Change Product
              </button>
            </div>
            
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
              />
            </div>
            
            <div class="form-actions">
              <button 
                class="submit-button" 
                :disabled="!stockQuantity" 
                @click="submitStock"
              >
                Submit
              </button>
              <button class="cancel-button" @click="$emit('close')">Cancel</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch, computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  },
  selectedProduct: {
    type: Object,
    default: null
  },
  isRefillModalOpen: Boolean,
});

const emit = defineEmits(['update:modelValue', 'open-product-selection', 'refill-submitted', 'close']);

const showError = ref(false);
const quantity = ref('');
const search = ref("");
const products = ref([]);
const selectedProductId = ref(null);
const pagination = ref({});
const stockQuantity = ref(null);
const selectedCategory = ref("");
const stockStatus = ref("");
const sort = ref("");
const color = ref("");
const size = ref("");

const allcategories = ref([]);
const colors = ref([]);
const sizes = ref([]);

const selectedProduct = computed(() => {
  return products.value.find((p) => p.id === selectedProductId.value) || null;
});

const currentPage = computed(() => {
  return pagination.value.current_page || 1;
});

const totalPages = computed(() => {
  return pagination.value.last_page || 1;
});

const closeModal = () => {
  emit('update:modelValue', false);
  resetForm();
};

const openProductSelection = () => {
  emit('open-product-selection');
};

const resetForm = () => {
  quantity.value = '';
  showError.value = false;
};

const submitRefill = () => {
  if (!quantity.value) {
    showError.value = true;
    return;
  }
  
  showError.value = false;
  emit('refill-submitted', {
    product_id: props.selectedProduct?.id,
    product_name: props.selectedProduct?.name,
    quantity: parseInt(quantity.value),
    current_stock: props.selectedProduct?.stock
  });
  
  resetForm();
};

const fetchProducts = async (url = "/api/products") => {
  try {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const response = await fetch(typeof url === "string" ? url : "/api/products", {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": token,
      },
      body: JSON.stringify({
        search: search.value.trim(), // Ensure search input is trimmed
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
        current_page: data.products.current_page,
        last_page: data.products.last_page,
        prev_page_url: data.products.prev_page_url,
        next_page_url: data.products.next_page_url,
      };
    }
  } catch (error) {
    console.error("Error fetching products:", error);
    alert("Failed to load products. Please try refreshing the page.");
  }
};

const submitStock = async () => {
  if (!stockQuantity.value || stockQuantity.value <= 0) {
    alert("Please enter a valid stock quantity.");
    return;
  }

  const payload = {
    product_id: selectedProduct.value.id,
    product_name: selectedProduct.value.name,
    quantity: parseInt(stockQuantity.value),
    stock: selectedProduct.value.stock_quantity ?? 0,
  };

  console.log("Submitting payload:", payload);

  try {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const response = await fetch("/refillphotocopy", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        "X-CSRF-TOKEN": token,
      },
      body: JSON.stringify(payload),
    });

    if (response.ok) {
      emit("refill-submitted");
      selectedProductId.value = null;
      stockQuantity.value = null;
      alert("Stock added successfully!");
    } else {
      const errorData = await response.json();
      console.error("Error:", errorData);
      if (errorData.errors && errorData.errors.stock) {
        alert(`Error: ${errorData.errors.stock.join(", ")}`);
      }
    }
  } catch (error) {
    console.error("Error submitting stock:", error);
  }
};

const resetFilters = () => {
  selectedCategory.value = "";
  stockStatus.value = "";
  sort.value = "";
  color.value = "";
  size.value = "";
  fetchProducts();
};

// Reset form when modal is closed
watch(() => props.modelValue, (newValue) => {
  if (!newValue) {
    resetForm();
  }
});

watch(() => props.isRefillModalOpen, (newVal) => {
  if (newVal) {
    selectedProductId.value = null;
    stockQuantity.value = null;
    fetchProducts();
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
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 8px;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  border-bottom: 1px solid #e0e0e0;
  position: relative; /* allow absolute close btn */
}

.modal-header h2 {
  margin: 0;
  font-size: 20px;
  color: #333;
}

.user-manual-button {
  background-color: #28a745;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.close-button {
  position: absolute;
  right: 12px;
  top: 12px;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background-color: #ff4d4d;
  color: #fff;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  cursor: pointer;
  z-index: 30;
}

.selected-product-info {
  padding: 20px;
}

.selected-product-info h3 {
  margin: 0 0 20px 0;
  color: #333;
  font-size: 18px;
}

.info-grid {
  margin-bottom: 20px;
}

.info-row {
  display: grid;
  grid-template-columns: 120px 1fr;
  align-items: center;
  margin-bottom: 10px;
}

.info-label {
  color: #666;
  font-weight: 500;
}

.info-value {
  color: #333;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  color: #333;
}

.form-input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.form-input.error {
  border-color: #dc3545;
}

.error-message {
  color: #dc3545;
  font-size: 14px;
  margin-top: 5px;
  display: block;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 15px 20px;
  border-top: 1px solid #e0e0e0;
}

.add-stock-button {
  background-color: #28a745;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.cancel-button {
  background-color: #6c757d;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.pos-modal-content {
  background-color: #fff;
  padding: 20px;
  border-radius: 8px;
  width: 95%;
  height: 90vh;
  max-width: 1200px;
  overflow-y: auto;
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

.filter-options {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 20px;
}

.custom-select {
  flex: 1;
  min-width: 150px;
}

.horizontal-product-scroll {
  overflow-x: auto;
  padding: 15px;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
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
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  padding: 15px;
  cursor: pointer;
  position: relative;
  transition: all 0.2s ease;
  min-width: 250px;
  flex-shrink: 0;
}

.radio-input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
}

.product-card:hover {
  border-color: #2196F3;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.product-card-content {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.product-card-content h3 {
  margin: 0;
  font-size: 16px;
  color: #333;
  font-weight: 500;
}

.product-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
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
  width: 20px;
  height: 20px;
  border: 2px solid #ccc;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.radio-input:checked + .product-card-content .select-circle {
  border-color: #2196F3;
}

.radio-input:checked + .product-card-content .select-circle .inner-circle {
  width: 12px;
  height: 12px;
  background-color: #2196F3;
  border-radius: 50%;
}

.pos-pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  background-color: #f5f5f5;
  border-radius: 4px;

}

.pagination-button {
  padding: 8px 16px;
  background-color: #fff;
  border: 1px solid #ddd;
  border-radius: 4px;
  color: #333;
  cursor: pointer;
  font-weight: 500;
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
}

.refill-form-section {
  padding: 20px;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  background-color: #f9f9f9;
}

.selected-product-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.selected-product-header h3 {
  margin: 0;
  color: #333;
  font-size: 18px;
}

.change-product-button {
  background-color: #ffc107;
  color: #212529;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.change-product-button:hover {
  background-color: #e0a800;
}

.selected-product-details {
  margin-bottom: 20px;
  padding: 15px;
  background-color: #fff;
  border-radius: 4px;
  border: 1px solid #e0e0e0;
}

.submit-button {
  background-color: #4CAF50;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 14px;
}

.submit-button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}
</style>