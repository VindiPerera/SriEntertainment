<template>
  <Dialog :open="open" @close="closeModal" class="relative z-50">
    <div class="fixed inset-0 bg-black/30" aria-hidden="true" />
    <div class="fixed inset-0 flex items-center justify-center p-4">
      <DialogPanel class="w-full max-w-4xl bg-white rounded-lg shadow-lg">
        <div class="p-6">
          <DialogTitle class="text-2xl font-medium text-gray-900 mb-4">
            Select Binding Services
          </DialogTitle>

          <!-- Search Bar -->
<div class="flex items-center space-x-2 mb-4">
  <input
    v-model="searchQuery"
    type="text"
    placeholder="Search binding services..."
    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
  />
  <button
    @click="searchQuery = ''"
    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
  >
    Reset
  </button>
</div>

          <!-- Loading State -->
          <div v-if="loading" class="flex justify-center items-center py-8">
            <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <div class="text-gray-500">Loading binding services...</div>
          </div>

          <div v-else-if="error" class="flex justify-center items-center py-8">
            <div class="text-red-500">{{ error }}</div>
          </div>

          <div v-else>
            <!-- Grid Layout -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div 
  v-for="service in paginatedServices" 
  :key="service.id" 
  class="border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer relative"
  :class="{
    'border-blue-500 bg-blue-50': isServiceSelected(service),
    'border-gray-200 bg-white': !isServiceSelected(service)
  }"
  @click="toggleServiceSelection(service)"
>
  <!-- Selection Indicator -->
  <div 
    class="absolute top-2 right-2 w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors"
    :class="{
      'bg-blue-500 border-blue-500': isServiceSelected(service),
      'border-gray-300': !isServiceSelected(service)
    }"
  >
    <svg 
      v-if="isServiceSelected(service)" 
      class="w-4 h-4 text-white" 
      fill="none" 
      stroke="currentColor" 
      viewBox="0 0 24 24"
    >
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
    </svg>
  </div>

  <div class="flex items-center">
    <div class="flex-1">
      <div class="flex items-center space-x-3">
        <h3 class="font-medium text-gray-900">{{ service.name }}</h3>
        <span v-if="service.binding_type" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
          {{ service.binding_type }}
        </span>
      </div>
      <div class="mt-2 space-y-1">
        <p class="text-sm text-gray-600">Pages: {{ service.pages }}</p>
        <p class="text-sm text-gray-600">Price: Rs. {{ service.price }}</p>
        <p class="text-sm text-gray-600">Service Charge: Rs. {{ service.service_charge }}</p>
        <p v-if="service.totalprice" class="text-sm font-medium text-green-600">Total: Rs. {{ service.totalprice }}</p>
      </div>
    </div>
  </div>
</div>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between border-t pt-4">
              <div class="flex items-center">
                <button
                  :disabled="currentPage === 1"
                  @click="currentPage--"
                  class="px-3 py-1 rounded-md text-sm font-medium text-gray-700 bg-gray-100 disabled:opacity-50"
                >
                  Previous
                </button>
                <span class="mx-4 text-sm text-gray-700">
                  Page {{ currentPage }} of {{ totalPages }}
                </span>
                <button
                  :disabled="currentPage === totalPages"
                  @click="currentPage++"
                  class="px-3 py-1 rounded-md text-sm font-medium text-gray-700 bg-gray-100 disabled:opacity-50"
                >
                  Next
                </button>
              </div>
              <div class="text-sm text-gray-500">
                {{ selectedServices.length }} service(s) selected
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-end space-x-3 mt-6">
            <button
              @click="closeModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md"
            >
              Cancel
            </button>
            <button
              @click="importSelected"
              :disabled="selectedServices.length === 0"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-md"
            >
              Import Selected ({{ selectedServices.length }})
            </button>
          </div>
        </div>
      </DialogPanel>
    </div>
  </Dialog>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { Dialog, DialogPanel, DialogTitle } from "@headlessui/vue";
import axios from "axios";

const props = defineProps({
  open: {
    type: Boolean,
    required: true
  }
});

const emit = defineEmits(["update:open", "import-bindings"]);

const searchQuery = ref("");
const selectedServices = ref([]);
const bindings = ref([]);
const loading = ref(false);
const error = ref(null);
const currentPage = ref(1);
const itemsPerPage = 6;

const fetchBindings = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await axios.get("/api/binding-services?show_all=true", {
      headers: {
        "Accept": "application/json",
        "X-Requested-With": "XMLHttpRequest"
      }
    });
    
    console.log("API Response:", response.data); // Debugging log
    
    if (response.data?.binding_services) {
      bindings.value = response.data.binding_services;
    } else if (Array.isArray(response.data)) {
      bindings.value = response.data;
    } else {
      throw new Error("Invalid data structure received");
    }
  } catch (err) {
    console.error("Error fetching binding services:", err);
    error.value = err.response?.data?.message || "Failed to load binding services";
  } finally {
    loading.value = false;
  }
};

const isServiceSelected = (service) => {
  return selectedServices.value.some(selected => selected.id === service.id);
};

const toggleServiceSelection = (service) => {
  const index = selectedServices.value.findIndex(selected => selected.id === service.id);
  if (index === -1) {
    selectedServices.value.push(service);
  } else {
    selectedServices.value.splice(index, 1);
  }
};

const filteredServices = computed(() => {
  if (!searchQuery.value) return bindings.value;
  const query = searchQuery.value.toLowerCase();
  return bindings.value.filter(
    (service) =>
      service.name?.toLowerCase().includes(query) ||
      service.binding_type?.toLowerCase().includes(query) ||
      service.pages?.toLowerCase().includes(query)
  );
});

const totalPages = computed(() => {
  return Math.ceil(filteredServices.value.length / itemsPerPage);
});

const paginatedServices = computed(() => {
  // Return all filtered services without pagination
  return filteredServices.value;
});

watch(searchQuery, () => {
  currentPage.value = 1;
});

const closeModal = () => {
  emit("update:open", false);
  selectedServices.value = [];
  searchQuery.value = "";
  currentPage.value = 1;
};

const importSelected = () => {
  emit("import-bindings", selectedServices.value);
  closeModal();
};

onMounted(() => {
  if (props.open) {
    fetchBindings();
  }
});

watch(() => props.open, (newValue) => {
  if (newValue) {
    fetchBindings();
  }
});
</script>