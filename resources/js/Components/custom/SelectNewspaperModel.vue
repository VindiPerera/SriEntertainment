<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-3/4 max-h-[90vh] overflow-y-auto p-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Select Newspapers</h2>
        <button @click="closeModal" class="text-red-500 hover:text-red-700">
          <i class="ri-close-line text-2xl"></i>
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-8">
        <p class="text-lg text-gray-500">Loading newspapers...</p>
      </div>

      <!-- Content -->
      <template v-else>
        <div class="flex items-center space-x-4 mb-4">
          <input
            v-model="search"
            type="text"
            placeholder="Search newspapers..."
            class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          <button 
            @click="resetFilters" 
            class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700 whitespace-nowrap"
          >
            Reset
          </button>
        </div>

        <!-- Selected Items Display -->
        <div v-if="selectedNewspapers.length > 0" class="mb-4 p-3 bg-blue-50 rounded-md">
          <p class="text-sm font-semibold mb-2">Selected: {{ selectedNewspapers.length }} newspaper(s)</p>
          <div class="flex flex-wrap gap-2">
            <span 
              v-for="newspaper in selectedNewspapers" 
              :key="newspaper.id"
              class="px-3 py-1 bg-blue-600 text-white rounded-full text-sm flex items-center gap-2"
            >
              {{ newspaper.name }}
              <button @click="deselectNewspaper(newspaper)" class="hover:text-red-200">
                <i class="ri-close-line"></i>
              </button>
            </span>
          </div>
        </div>

        <!-- No Data State -->
        <div v-if="paginatedNewspapers.length === 0" class="text-center py-8">
          <p class="text-lg text-gray-500">No newspapers available</p>
        </div>

        <!-- Newspaper Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
          <div
            v-for="newspaper in paginatedNewspapers"
            :key="newspaper.id"
            :class="[
              'border rounded-lg p-4 shadow hover:shadow-lg cursor-pointer transition-all',
              isSelected(newspaper) ? 'border-blue-500 bg-blue-50' : 'border-gray-200'
            ]"
            @click="toggleNewspaper(newspaper)"
          >
            <div class="flex justify-between items-start mb-2">
              <h3 class="text-lg font-bold">{{ newspaper.name }}</h3>
              <i 
                v-if="isSelected(newspaper)" 
                class="ri-checkbox-circle-fill text-blue-600 text-xl"
              ></i>
              <i 
                v-else 
                class="ri-checkbox-blank-circle-line text-gray-400 text-xl"
              ></i>
            </div>
            <p class="text-sm text-gray-600">Price: {{ newspaper.selling_price }} LKR</p>
            <p class="text-sm text-gray-600">Stock: {{ newspaper.stock_quantity }}</p>
            <p v-if="newspaper.barcode" class="text-xs text-gray-400 mt-1">
              Barcode: {{ newspaper.barcode }}
            </p>
          </div>
        </div>

        <!-- Pagination -->
        <div class="flex justify-between items-center mt-4 pt-4 border-t">
          <button 
            @click="previousPage" 
            :disabled="currentPage === 1" 
            :class="[
              'px-4 py-2 rounded-md',
              currentPage === 1 
                ? 'bg-gray-200 text-gray-400 cursor-not-allowed' 
                : 'bg-gray-300 hover:bg-gray-400'
            ]"
          >
            Previous
          </button>
          
          <span class="text-sm text-gray-600">
            Page {{ currentPage }} of {{ totalPages }}
          </span>
          
          <button 
            @click="nextPage" 
            :disabled="currentPage === totalPages" 
            :class="[
              'px-4 py-2 rounded-md',
              currentPage === totalPages 
                ? 'bg-gray-200 text-gray-400 cursor-not-allowed' 
                : 'bg-gray-300 hover:bg-gray-400'
            ]"
          >
            Next
          </button>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-3 mt-6">
          <button 
            @click="closeModal" 
            class="px-6 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
          >
            Cancel
          </button>
          <button 
            @click="importSelected" 
            :disabled="selectedNewspapers.length === 0"
            :class="[
              'px-6 py-2 text-white rounded-md',
              selectedNewspapers.length === 0
                ? 'bg-gray-400 cursor-not-allowed'
                : 'bg-blue-600 hover:bg-blue-700'
            ]"
          >
            Import Selected ({{ selectedNewspapers.length }})
          </button>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  open: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits(['update:open', 'import-newspapers']);

const newspapers = ref([]);
const search = ref('');
const selectedNewspapers = ref([]);
const currentPage = ref(1);
const itemsPerPage = 6;
const loading = ref(false);

// Fetch newspapers from API
const fetchNewspapers = async () => {
  loading.value = true;
  try {
    console.log('Fetching newspapers from API...');
    const response = await axios.get('/api/newspapers');
    console.log('API Response:', response.data);
    
    if (response.data && response.data.newspapers) {
      newspapers.value = response.data.newspapers;
      console.log('Newspapers loaded:', newspapers.value.length);
    } else {
      console.error('Invalid response format:', response.data);
      newspapers.value = [];
    }
  } catch (error) {
    console.error('Error fetching newspapers:', error);
    newspapers.value = [];
  } finally {
    loading.value = false;
  }
};

// Filter newspapers based on search
const filteredNewspapers = computed(() => {
  if (!search.value) {
    return newspapers.value;
  }
  return newspapers.value.filter((newspaper) =>
    newspaper.name.toLowerCase().includes(search.value.toLowerCase())
  );
});

// Paginate filtered newspapers
const paginatedNewspapers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredNewspapers.value.slice(start, end);
});

// Calculate total pages
const totalPages = computed(() => {
  return Math.ceil(filteredNewspapers.value.length / itemsPerPage);
});

// Check if newspaper is selected
const isSelected = (newspaper) => {
  return selectedNewspapers.value.some(n => n.id === newspaper.id);
};

// Toggle newspaper selection
const toggleNewspaper = (newspaper) => {
  const index = selectedNewspapers.value.findIndex(n => n.id === newspaper.id);
  if (index > -1) {
    selectedNewspapers.value.splice(index, 1);
  } else {
    selectedNewspapers.value.push(newspaper);
  }
};

// Deselect newspaper
const deselectNewspaper = (newspaper) => {
  const index = selectedNewspapers.value.findIndex(n => n.id === newspaper.id);
  if (index > -1) {
    selectedNewspapers.value.splice(index, 1);
  }
};

// Pagination controls
const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
  }
};

// Reset filters
const resetFilters = () => {
  search.value = '';
  currentPage.value = 1;
};

// Close modal
const closeModal = () => {
  emit('update:open', false);
  // Reset state when closing
  setTimeout(() => {
    selectedNewspapers.value = [];
    search.value = '';
    currentPage.value = 1;
  }, 300);
};

// Import selected newspapers
const importSelected = () => {
  if (selectedNewspapers.value.length > 0) {
    console.log('Importing newspapers:', selectedNewspapers.value);
    emit('import-newspapers', selectedNewspapers.value);
    closeModal();
  }
};

// Watch for modal open state
watch(() => props.open, (newValue) => {
  if (newValue) {
    console.log('Modal opened, fetching newspapers...');
    fetchNewspapers();
  }
});
</script>

<style scoped>
/* Scrollbar styling */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>