<template>
  <Dialog :open="open" @close="closeModal" class="relative z-10">
    <div class="fixed inset-0 bg-black bg-opacity-25" />

    <div class="fixed inset-0 overflow-y-auto">
      <div class="flex min-h-full items-center justify-center p-4 text-center">
        <DialogPanel class="w-full max-w-6xl transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
          <div class="flex justify-between items-center mb-4">
            <DialogTitle as="h3" class="text-2xl font-medium  leading-6 text-gray-900">
              Select Photocopy Services
            </DialogTitle>
            <div class="flex items-center space-x-2">
              <input
                type="text"
                v-model="searchQuery"
                placeholder="Search services..."
                class="px-3 py-2 border border-gray-300 rounded-md w-64"
              />
              <button
                @click="searchQuery = ''"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
              >
                Reset
              </button>
            </div>
          </div>

          <!-- Loading State -->
          <div v-if="loading" class="flex justify-center items-center py-8">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <div class="text-gray-500">Loading photocopy services...</div>
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

                <div class="flex items-start">
                  <div class="flex-1">
                    <h4 class="text-lg font-medium text-gray-900">{{ service.name }}</h4>
                    <p class="text-sm text-gray-600 mt-1">{{ service.description }}</p>
                    <div class="mt-2 space-y-1">
                      <p class="text-sm">pages: {{ service.pages }} </p>
                      <p class="text-sm">color: {{ service.color }} </p>
                      <p class="text-sm">side: {{ service.side }} </p>
                      <p class="text-sm">size: {{ service.size }} </p>
                      <p class="text-sm font-semibold text-blue-600">Total Price: {{ service.totalprice }} LKR</p>
                     
                      <p v-if="service.barcode" class="text-sm text-gray-500">Barcode: {{ service.barcode }}</p>
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

              <div class="flex items-center space-x-3">
                <button
                  type="button"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
                  @click="closeModal"
                >
                  Cancel
                </button>
                <button
                  type="button"
                  class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50"
                  @click="importSelected"
                  :disabled="selectedServices.length === 0"
                >
                  Import Selected ({{ selectedServices.length }})
                </button>
              </div>
            </div>
          </div>
        </DialogPanel>
      </div>
    </div>
  </Dialog>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { Dialog, DialogPanel, DialogTitle } from "@headlessui/vue";
import axios from "axios";

const props = defineProps({
  open: {
    type: Boolean,
    required: true
  }
});

const emit = defineEmits(["update:open", "import-photocopies"]);

const searchQuery = ref("");
const selectedServices = ref([]);
const photocopies = ref([]);
const loading = ref(false);
const error = ref(null);
const currentPage = ref(1);
const itemsPerPage = 6;

const fetchPhotocopies = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await axios.get("/photocopy-services", {
      headers: {
        "Accept": "application/json",
        "X-Requested-With": "XMLHttpRequest"
      }
    });
    
    if (response.data?.photocopyServices) {
      photocopies.value = response.data.photocopyServices;
    } else if (Array.isArray(response.data)) {
      photocopies.value = response.data;
    } else {
      throw new Error("Invalid data structure received");
    }
  } catch (err) {
    console.error("Error fetching services:", err);
    error.value = err.response?.data?.message || "Failed to load photocopy services";
  } finally {
    loading.value = false;
  }
};

const filteredServices = computed(() => {
  if (!searchQuery.value) return photocopies.value;
  const query = searchQuery.value.toLowerCase();
  return photocopies.value.filter(
    (service) =>
      service.name.toLowerCase().includes(query) ||
      service.description?.toLowerCase().includes(query)
  );
});

const totalPages = computed(() => {
  return Math.ceil(filteredServices.value.length / itemsPerPage);
});

const paginatedServices = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredServices.value.slice(start, end);
});

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
  emit("import-photocopies", selectedServices.value);
  closeModal();
};

onMounted(() => {
  if (props.open) {
    fetchPhotocopies();
  }
});

watch(() => props.open, (newValue) => {
  if (newValue) {
    fetchPhotocopies();
  }
});
</script>