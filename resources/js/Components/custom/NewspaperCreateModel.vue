<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-10" @close="$emit('update:open', false)">
      <!-- Modal Overlay -->
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div
          class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
        />
      </TransitionChild>
      <!-- Modal Content -->
      <div class="fixed inset-0 z-10 flex items-center justify-center">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100"
          leave="ease-in duration-200"
          leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95"
        >
          <DialogPanel
            class="bg-black border-4 border-blue-600 rounded-[20px] shadow-xl w-5/6 lg:w-4/6 p-10 text-center max-h-[90vh] overflow-y-auto"
          >
            <div class="flex justify-between items-center">
              <DialogTitle class="text-xl font-bold text-white"
                >Create Newspaper</DialogTitle
              >
              <button
                type="button"
                @click="showAddNewspaperModal"
                class="px-4 py-2 text-white bg-green-600 rounded hover:bg-green-700"
              >
                Add Newspaper
              </button>
            </div>

            <p v-if="successMessage" class="text-lg text-green-400 mt-2">
              {{ successMessage }}
            </p>

            <form @submit.prevent="submit">
              <!-- Modal Form -->
              <div class="mt-6 space-y-4 text-left">
                <!-- Row 1: Name and Product Code -->
                <div class="flex items-center gap-8">
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Name:</label
                    >
                    <select
                      v-model="form.name"
                      id="name"
                      required
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    >
                      <option value="">Select Newspaper</option>
                      <option v-for="newspaper in storeNewspapers" :key="newspaper.id" :value="newspaper.name">
                        {{ newspaper.name }}
                      </option>
                    </select>
                    <span v-if="form.errors.name" class="mt-4 text-red-500">{{
                      form.errors.name
                    }}</span>
                  </div>
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Product Code:</label
                    >
                    <input
                      v-model="form.productcode"
                      type="text"
                      id="productcode"
                      required
                      placeholder="Enter product code"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span
                      v-if="form.errors.productcode"
                      class="mt-4 text-red-500"
                      >{{ form.errors.productcode }}</span
                    >
                  </div>
                </div>

                <!-- Row 2: Barcode and Batch No -->
                <div class="flex items-center gap-8">
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Barcode:</label
                    >
                    <input
                      v-model="form.barcode"
                      type="text"
                      id="barcode"
                      placeholder="Enter barcode"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span
                      v-if="form.errors.barcode"
                      class="mt-4 text-red-500"
                      >{{ form.errors.barcode }}</span
                    >
                  </div>
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Batch No:</label
                    >
                    <input
                      v-model="form.batch_no"
                      type="text"
                      id="batch_no"
                      placeholder="Enter batch no"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span
                      v-if="form.errors.batch_no"
                      class="mt-4 text-red-500"
                      >{{ form.errors.batch_no }}</span
                    >
                  </div>
                </div>

                <!-- Row 3: Supplier and Duration -->
                <div class="flex items-center gap-8">
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Supplier:</label
                    >
                    <select
                      v-model="form.supplier"
                      id="supplier"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    >
                      <option value="">Select Supplier</option>
                      <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.name">
                        {{ supplier.name }}
                      </option>
                    </select>
                    <span
                      v-if="form.errors.supplier"
                      class="mt-4 text-red-500"
                      >{{ form.errors.supplier }}</span
                    >
                  </div>
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Duration:</label
                    >
                    <select
                      v-model="form.duration"
                      id="duration"
                      required
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    >
                      <option value="">Select Duration</option>
                      <option value="monthly">Monthly</option>
                      <option value="weekly">Weekly</option>
                    </select>
                    <span
                      v-if="form.errors.duration"
                      class="mt-4 text-red-500"
                      >{{ form.errors.duration }}</span
                    >
                  </div>
                </div>

                <!-- Day of Week (conditional) -->
                <div v-if="form.duration === 'weekly'" class="flex items-center gap-8">
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Day of Week:</label
                    >
                    <select
                      v-model="form.day_of_week"
                      id="day_of_week"
                      required
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    >
                      <option value="">Select Day</option>
                      <option value="monday">Monday</option>
                      <option value="tuesday">Tuesday</option>
                      <option value="wednesday">Wednesday</option>
                      <option value="thursday">Thursday</option>
                      <option value="friday">Friday</option>
                      <option value="saturday">Saturday</option>
                      <option value="sunday">Sunday</option>
                    </select>
                    <span
                      v-if="form.errors.day_of_week"
                      class="mt-4 text-red-500"
                      >{{ form.errors.day_of_week }}</span
                    >
                  </div>
                  <div class="w-full">
                    <!-- Empty div for alignment -->
                  </div>
                </div>

              
                <!-- Row 5: Publication Date and Expire Date -->
                <div class="flex items-center gap-8">
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Publication Date:</label
                    >
                    <input
                      v-model="form.publication_date"
                      type="date"
                      id="publication_date"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                      required
                      />
                    <span
                      v-if="form.errors.publication_date"
                      class="mt-4 text-red-500"
                      >{{ form.errors.publication_date }}</span
                    >
                  </div>
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Expire Date:</label
                    >
                    <input
                      v-model="form.expire_date"
                      type="date"
                      id="expire_date"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                      required
                      />
                    <span
                      v-if="form.errors.expire_date"
                      class="mt-4 text-red-500"
                      >{{ form.errors.expire_date }}</span
                    >
                  </div>
                </div>

                <!-- Row 6: Price and Stock Quantity -->
                <div class="flex items-center gap-8">
                  
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Stock Quantity:</label
                    >
                    <input
                      v-model="form.stock_quantity"
                      type="number"
                      id="stock_quantity"
                      required
                      placeholder="Enter stock quantity"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span
                      v-if="form.errors.stock_quantity"
                      class="mt-4 text-red-500"
                      >{{ form.errors.stock_quantity }}</span
                    >
                  </div>
                   <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Language:</label
                    >
                    <select
                      v-model="form.language"
                      id="language"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    >
                      <option value="">Select Language</option>
                      <option value="tamil">Tamil</option>
                      <option value="sinhala">Sinhala</option>
                      <option value="english">English</option>
                    </select>
                    <span
                      v-if="form.errors.language"
                      class="mt-4 text-red-500"
                      >{{ form.errors.language }}</span
                    >
                  </div>
                </div>

                <!-- Row 7: Cost Price and Selling Price -->
                <div class="flex items-center gap-8">
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Cost Price:</label
                    >
                    <input
                      v-model="form.cost_price"
                      type="number"
                      step="0.01"
                      id="cost_price"
                      required
                      placeholder="Enter cost price"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span
                      v-if="form.errors.cost_price"
                      class="mt-4 text-red-500"
                      >{{ form.errors.cost_price }}</span
                    >
                  </div>
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Selling Price:</label
                    >
                    <input
                      v-model="form.selling_price"
                      type="number"
                      step="0.01"
                      id="selling_price"
                      required
                      placeholder="Enter selling price"
                      @blur="updateDiscountedPrice"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span
                      v-if="form.errors.selling_price"
                      class="mt-4 text-red-500"
                      >{{ form.errors.selling_price }}</span
                    >
                  </div>
                </div>

                <!-- Row 8: Discount and Discount Price -->
                <div class="flex items-center gap-8">
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Discount (%):</label
                    >
                    <input
                      v-model="form.discount"
                      type="number"
                      step="0.01"
                      id="discount"
                      placeholder="Enter discount percentage"
                      @blur="updateDiscountedPrice"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span
                      v-if="form.errors.discount"
                      class="mt-4 text-red-500"
                      >{{ form.errors.discount }}</span
                    >
                  </div>
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Discount Price:</label
                    >
                    <input
                      v-model="form.discount_price"
                      type="number"
                      step="0.01"
                      id="discount_price"
                      placeholder="Discount price will be calculated"
                      @blur="updateDiscount"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span
                      v-if="form.errors.discount_price"
                      class="mt-4 text-red-500"
                      >{{ form.errors.discount_price }}</span
                    >
                  </div>
                </div>

                <!-- Row 9: Return -->
                <!-- <div class="flex items-center gap-8">
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Return Count:</label
                    >
                    <input
                      v-model="form.return"
                      type="number"
                      min="0"
                      id="return"
                      required
                      placeholder="Enter number of returned newspapers"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span
                      v-if="form.errors.return"
                      class="mt-4 text-red-500"
                      >{{ form.errors.return }}</span
                    >
                  </div>
                </div> -->
              </div>

              <!-- Modal Buttons -->
              <div class="mt-6 space-x-4">
                <button
                  class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700"
                  type="submit"
                  :disabled="form.processing"
                >
                  <span v-if="form.processing">Saving...</span>
                  <span v-else>Save</span>
                </button>
                <button
                  class="px-4 py-2 text-gray-700 bg-gray-300 rounded hover:bg-gray-400"
                  @click="
                    () => {
                      emit('update:open', false);
                    }
                  "
                  type="button"
                  :disabled="form.processing"
                >
                  Cancel
                </button>
              </div>
            </form>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>

  <!-- Store Newspaper Modal -->
  <TransitionRoot as="template" :show="showStoreModal">
    <Dialog class="relative z-20" @close="showStoreModal = false">
      <!-- Modal Overlay -->
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" />
      </TransitionChild>

      <!-- Modal Content -->
      <div class="fixed inset-0 z-10 flex items-center justify-center">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100"
          leave="ease-in duration-200"
          leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95"
        >
          <DialogPanel
            class="bg-black border-4 border-green-600 rounded-[20px] shadow-xl w-3/6 lg:w-2/6 p-8 text-center"
          >
            <DialogTitle class="text-xl font-bold text-white mb-6"
              >Add Newspaper to Store</DialogTitle
            >
            
            <!-- Add Newspaper Form -->
            <div class="text-left">
              <label class="block text-sm font-medium text-gray-300 mb-2"
                >Newspaper Name:</label
              >
              <input
                v-model="newNewspaperName"
                type="text"
                placeholder="Enter newspaper name"
                class="w-full px-4 py-2 text-black rounded-md focus:outline-none focus:ring focus:ring-green-600"
              />
            </div>

            <!-- Modal Buttons -->
            <div class="mt-6 space-x-4">
              <button
                type="button"
                @click="addNewspaperToStore"
                class="px-4 py-2 text-white bg-green-600 rounded hover:bg-green-700"
              >
                Add to Store
              </button>
              <button
                type="button"
                class="px-4 py-2 text-gray-700 bg-gray-300 rounded hover:bg-gray-400"
                @click="showStoreModal = false"
              >
                Cancel
              </button>
            </div>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import { ref, watch, onMounted } from "vue";
import { useForm } from "@inertiajs/vue3";

const emit = defineEmits(["update:open"]);

const props = defineProps({
  open: {
    type: Boolean,
    required: true,
  },
  suppliers: {
    type: Array,
    default: () => [],
  },

});

const successMessage = ref("");
const showStoreModal = ref(false);
const storeNewspapers = ref([]);
const newNewspaperName = ref("");
const suppliers = ref([]);

// Utility function to limit to 2 decimal points
function limitToTwoDecimals(value) {
  if (value === null || value === undefined) return value;
  const strValue = value.toString();
  const match = strValue.match(/^(\d+)(\.\d{0,2})?/);
  return match ? parseFloat(match[0]) : value;
}

// Function to update discounted price based on selling price and discount
function updateDiscountedPrice() {
  if (form.selling_price && form.discount) {
    const discountAmount = (form.selling_price * form.discount) / 100;
    form.discount_price = limitToTwoDecimals(
      form.selling_price - discountAmount
    );
  }
}

// Function to update discount based on selling price and discounted price
function updateDiscount() {
  if (form.selling_price && form.discount_price) {
    const discountAmount = form.selling_price - form.discount_price;
    form.discount = limitToTwoDecimals(
      (discountAmount / form.selling_price) * 100
    );
  }
}

const form = useForm({
  name: "",
  productcode: "",
  barcode: "",
  batch_no: "",
  supplier: "",
  duration: "",
  day_of_week: "",
  publication_date: "",
  expire_date: "",
  language: null,
  stock_quantity: null,
  cost_price: "",
  selling_price: "",
  discount: null,
  discount_price: null,
  return: 0,
});

// Function to fetch next batch number
async function fetchBatchNumber(productcode) {
  try {
    const response = await fetch(`/newspapers/batch?productcode=${encodeURIComponent(productcode)}`);
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }
    const data = await response.json();
    form.batch_no = data.batch_no.toString();
  } catch (error) {
    console.error('Error fetching batch number:', error);
    form.batch_no = "1"; // Default to 1 if there's an error
  }
}

// Function to populate form from existing newspaper
async function populateFromExisting() {
  if (!selectedExistingNewspaper.value) {
    form.reset();
    return;
  }
  
  try {
    const response = await fetch(`/newspapers/${selectedExistingNewspaper.value}`);
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }
    const newspaper = await response.json();
    
    // Populate form with existing newspaper data (except unique fields)
    form.supplier = newspaper.supplier || "";
    form.duration = newspaper.duration || "";
    form.day_of_week = newspaper.day_of_week || "";
    form.language = newspaper.language || "";
    form.cost_price = newspaper.cost_price || "";
    form.selling_price = newspaper.selling_price || "";
    form.discount = newspaper.discount || "";
    form.discount_price = newspaper.discount_price || "";
  } catch (error) {
    console.error('Error fetching newspaper data:', error);
  }
}

// Function to add newspaper to store
async function addNewspaperToStore() {
  if (!newNewspaperName.value.trim()) {
    alert('Please enter a newspaper name');
    return;
  }
  
  try {
    // Get CSRF token from meta tag or use Inertia's approach
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    console.log('Adding newspaper:', newNewspaperName.value);
    
    const response = await fetch('/api/store-newspapers', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        name: newNewspaperName.value.trim()
      })
    });
    
    console.log('Response status:', response.status);
    
    if (response.ok) {
      const result = await response.json();
      console.log('Success result:', result);
      await fetchStoreNewspapers();
      showStoreModal.value = false;
      successMessage.value = 'Newspaper added to store successfully!';
      setTimeout(() => {
        successMessage.value = '';
      }, 3000);
    } else if (response.status === 422) {
      // Handle validation errors (422 Unprocessable Entity)
      const errorData = await response.json();
      console.error('Validation errors:', errorData);
      
      // Extract validation errors
      if (errorData.errors && errorData.errors.name) {
        alert(`Error: ${errorData.errors.name[0]}`);
      } else if (errorData.message) {
        alert(`Error: ${errorData.message}`);
      } else {
        alert('The name has already been taken.');
      }
    } else {
      const errorData = await response.text();
      console.error('Server response:', errorData);
      alert(`Error adding newspaper to store: ${response.status}`);
    }
  } catch (error) {
    console.error('Error adding newspaper to store:', error);
    alert(`Error adding newspaper to store: ${error.message}`);
  }
}

// Watch for changes in product code
watch(() => form.productcode, (newValue) => {
  if (newValue && newValue.trim() !== '') {
    fetchBatchNumber(newValue);
  } else {
    form.batch_no = ""; // Clear batch number if product code is empty
  }
});

// Watch for changes in duration
watch(() => form.duration, (newValue) => {
  if (newValue !== 'weekly') {
    form.day_of_week = ""; // Clear day_of_week if duration is not weekly
  }
});

// Function to show add newspaper modal
function showAddNewspaperModal() {
  showStoreModal.value = true;
  newNewspaperName.value = "";
}

// Function to fetch store newspapers
async function fetchStoreNewspapers() {
  try {
    const response = await fetch('/api/store-newspapers');
    if (response.ok) {
      const data = await response.json();
      storeNewspapers.value = data;
    }
  } catch (error) {
    console.error('Error fetching store newspapers:', error);
  }
}

// Function to fetch suppliers
async function fetchSuppliers() {
  try {
    const response = await fetch('/newspapers/suppliers');
    if (response.ok) {
      const data = await response.json();
      suppliers.value = data;
    }
  } catch (error) {
    console.error('Error fetching suppliers:', error);
  }
}

// Load store newspapers on component mount
watch(() => props.open, (newValue) => {
  if (newValue) {
    fetchStoreNewspapers();
    fetchSuppliers();
  }
});

const submit = () => {
  form.post("/newspapers", {
    preserveScroll: true,
    onSuccess: () => {
      console.log("Newspaper created successfully!");
      form.reset();
      emit('update:open', false);
    },
    onError: (errors) => {
      console.error("Form submission failed:", errors);
    },
  });
};
</script>