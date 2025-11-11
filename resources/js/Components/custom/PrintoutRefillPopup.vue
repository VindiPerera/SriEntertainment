<template>
  <div class="modal-overlay" v-if="modelValue">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Select Product</h2>
        <div class="search-bar">
          <input v-model="search" type="text" placeholder="Search products..." @input="handleSearchInput" />
        </div>
        <button type="button" @click.stop="closeModal" class="close-button">×</button>
      </div>
      <div class="modal-body">
        <div v-if="!selectedProductId" class="filter-options">
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
            class="px-6 py-3 text-xl font-normal tracking-wider text-white text-center bg-blue-600 rounded-lg custom-select cursor-pointer"
          >
            Reset
          </span>
        </div>

        <div v-if="loading" class="loading-state">
          Loading products...
        </div>

        <div v-else-if="!selectedProductId" class="horizontal-product-scroll">
          <div v-if="products.length > 0" class="product-grid">
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
                  <div class="info-line">Code: {{ product.code }}</div>
                  <div class="info-line barcode">Barcode: {{ product.barcode }}</div>
                </div>
                <div class="select-circle">
                  <div class="inner-circle"></div>
                </div>
              </div>
            </label>
          </div>
          <div v-else class="no-products">
            No products found matching your criteria.
          </div>
        </div>

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
              min="1"
            />
          </div>

          <div class="form-actions">
            <button 
              class="submit-button" 
              :disabled="!stockQuantity || stockQuantity <= 0" 
              @click="submitStock"
            >
              Submit
            </button>
            <button type="button" class="cancel-button" @click.stop="closeModal">Cancel</button>
          </div>
        </div>

        <div v-if="products.length > 0 && !selectedProductId" class="pos-pagination">
          <button 
            class="pagination-button" 
            @click="goToPage(pagination.current_page - 1)" 
            :disabled="!pagination.prev_page_url"
          >
            Previous
          </button>
          <span class="pagination-info">
            Page {{ currentPage }} of {{ totalPages }}
          </span>
          <button 
            class="pagination-button" 
            @click="goToPage(pagination.current_page + 1)" 
            :disabled="!pagination.next_page_url"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, computed, watch, onMounted } from 'vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  }
});

const emit = defineEmits(['update:modelValue', 'refill-submitted', 'close']);

const search = ref("");
const products = ref([]);
const selectedProductId = ref(null);
const stockQuantity = ref(null);
const pagination = ref({
  current_page: 1,
  last_page: 1,
  prev_page_url: null,
  next_page_url: null,
  total: 0
});
const selectedCategory = ref("");
const stockStatus = ref("");
const sort = ref("");
const color = ref("");
const size = ref("");
const loading = ref(false);
const allcategories = ref([]);
const colors = ref([]);
const sizes = ref([]);

let searchTimeout = null;

const selectedProduct = computed(() => {
  return products.value.find((p) => p.id === selectedProductId.value) || null;
});

const currentPage = computed(() => pagination.value.current_page || 1);
const totalPages = computed(() => pagination.value.last_page || 1);

const closeModal = () => {
  console.log("closeModal triggered"); // Debugging log
  emit('update:modelValue', false);
  emit('close'); // Also emit close event for compatibility
  selectedProductId.value = null;
  stockQuantity.value = null;
  resetFilters();
};

const handleSearchInput = () => {
  console.log("Search input changed:", search.value); // Debugging log
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    console.log("Fetching products with search:", search.value); // Debugging log
    fetchProducts();
  }, 500);
};

const fetchProducts = async (page = 1) => {
  console.log("Fetching products for page:", page, "with filters:", {
    search: search.value.trim(),
    category: selectedCategory.value,
    stock: stockStatus.value,
    sort: sort.value,
    color: color.value,
    size: size.value,
  }); // Debugging log
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

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    fetchProducts(page);
  }
};

const resetFilters = () => {
  selectedCategory.value = "";
  stockStatus.value = "";
  sort.value = "";
  color.value = "";
  size.value = "";
  search.value = "";
};

const submitStock = async () => {
  if (!stockQuantity.value || stockQuantity.value <= 0) {
    alert("Please enter a valid stock quantity.");
    return;
  }

  try {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const response = await fetch(`/api/refill-printout`, {
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

    const result = await response.json();

    if (response.ok) {
      alert("Stock refilled successfully!");
      
      // Emit event and close modal
      emit('refill-submitted');
      closeModal();
      
   
    } else {
      console.error("Error refilling stock:", result);
      alert(result.message || result.error || "Failed to refill stock. Please try again.");
    }
  } catch (error) {
    console.error("Error submitting stock refill:", error);
    alert("An error occurred while refilling stock.");
  }
};
// Fetch products when modal opens
watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    fetchProducts();
  }
});

// Initialize when component mounts
onMounted(() => {
  if (props.modelValue) {
    fetchProducts();
  }
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

.modal-content {
  background: white;
  border-radius: 8px;
  width: 90%;
  max-width: 1200px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  border-bottom: 1px solid #e0e0e0;
}

.modal-header h2 {
  margin: 0;
  font-size: 20px;
  color: #333;
}

.search-bar input {
  width: 300px;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
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
  z-index: 10;
  position: relative;
}

.close-button:hover {
  background: #d32f2f;
}

.filter-options {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 20px;
  padding: 0 20px;
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
  margin: 0 20px;
  min-height: 400px;
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
  padding: 15px 20px;
  background-color: #f5f5f5;
  border-radius: 4px;
  margin: 0 20px;
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

.loading-state {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 200px;
  font-size: 18px;
  color: #666;
}

.no-products {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 200px;
  font-size: 18px;
  color: #666;
  text-align: center;
}

.refill-form-section {
  display: flex;
  flex-direction: column;
  gap: 20px;
  padding: 20px;
}

.selected-product-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.selected-product-header h3 {
  margin: 0;
  font-size: 18px;
  color: #333;
}

.change-product-button {
  padding: 8px 16px;
  background-color: #ffc107;
  border: none;
  border-radius: 4px;
  color: #212529;
  cursor: pointer;
  font-weight: 500;
}

.selected-product-details {
  background: #f8f9fa;
  padding: 15px;
  border-radius: 4px;
  border: 1px solid #e9ecef;
}

.info-row {
  display: flex;
  gap: 30px;
  margin-bottom: 8px;
  font-size: 14px;
}

.info-label {
  font-weight: 500;
  color: #495057;
}

.info-value {
  color: #212529;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-weight: 500;
  color: #495057;
}

.form-input {
  padding: 10px;
  border: 1px solid #ced4da;
  border-radius: 4px;
  font-size: 14px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding-top: 20px;
  border-top: 1px solid #e9ecef;
}

.submit-button {
  padding: 10px 20px;
  background-color: #28a745;
  border: none;
  border-radius: 4px;
  color: white;
  cursor: pointer;
  font-weight: 500;
}

.submit-button:disabled {
  background-color: #6c757d;
  cursor: not-allowed;
}

.cancel-button {
  padding: 10px 20px;
  background-color: #6c757d;
  border: none;
  border-radius: 4px;
  color: white;
  cursor: pointer;
  font-weight: 500;
}
</style>