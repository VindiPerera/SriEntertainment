<template>
  <Modal :show="open" @close="handleClose">
    <div class="p-6 w-full max-w-2xl">
      <h2 class="text-lg font-medium text-gray-900">Return Newspaper</h2>

      <form @submit.prevent="submitReturn" class="mt-4 space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Select Newspapers to Return</label>
          
          <!-- Search box -->
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search newspapers..."
            class="mb-3 block w-full border border-gray-300 rounded-md shadow-sm p-2"
          />

          <!-- Select All Checkbox -->
          <div class="mb-2 pb-2 border-b">
            <label class="flex items-center">
              <input
                type="checkbox"
                @change="toggleSelectAll"
                :checked="isAllSelected"
                class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50"
              />
              <span class="ml-2 text-sm font-medium text-gray-700">Select All</span>
            </label>
          </div>

          <!-- Newspaper List with Checkboxes -->
          <div class="max-h-64 overflow-y-auto border border-gray-300 rounded-md p-3 space-y-2">
            <div v-if="filteredNewspapers.length === 0" class="text-gray-500 text-sm text-center py-4">
              No newspapers available for return
            </div>
            <label
              v-for="paper in filteredNewspapers"
              :key="paper.id"
              class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer"
            >
              <input
                type="checkbox"
                :value="paper.id"
                v-model="selectedNewspapers"
                class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50"
              />
              <span class="ml-3 text-sm text-gray-900">
                {{ paper.name }}
                <span class="text-gray-500">(Stock: {{ paper.stock_quantity }})</span>
                <span v-if="paper.day_of_week" class="text-gray-500">(Day: {{ paper.day_of_week.charAt(0).toUpperCase() + paper.day_of_week.slice(1) }})</span>
              </span>
            </label>
          </div>
          
          <p v-if="selectedNewspapers.length > 0" class="text-sm text-green-600 mt-2">
            {{ selectedNewspapers.length }} newspaper(s) selected
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Reason for Return</label>
          <textarea
            v-model="form.reason"
            rows="3"
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
            placeholder="Enter reason for returning these newspapers..."
            required
          />
          <p v-if="form.errors.reason" class="text-red-500 text-sm mt-1">{{ form.errors.reason }}</p>
        </div>

        <!-- Display general errors -->
        <div v-if="form.errors.newspaper_ids" class="text-red-500 text-sm">
          {{ form.errors.newspaper_ids }}
        </div>
        <div v-if="Object.keys(form.errors).length > 0 && !form.errors.reason && !form.errors.newspaper_ids" class="text-red-500 text-sm">
          <p v-for="(error, key) in form.errors" :key="key">{{ error }}</p>
        </div>

        <div class="flex justify-end space-x-3 mt-4">
          <button type="button" @click="handleClose" class="px-4 py-2 bg-gray-100 rounded-md hover:bg-gray-200">
            Cancel
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:bg-gray-300 disabled:cursor-not-allowed"
            :disabled="processing || !canSubmit"
          >
            Submit Return ({{ selectedNewspapers.length }})
          </button>
        </div>
      </form>
    </div>
  </Modal>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  open: Boolean,
});

const emit = defineEmits(['close', 'update:open']);

const processing = ref(false);
const availableNewspapers = ref([]);
const selectedNewspapers = ref([]);
const searchQuery = ref('');

const form = useForm({
  newspaper_ids: [],
  reason: '',
});

// Fetch available newspapers when modal opens
watch(() => props.open, async (isOpen) => {
  if (isOpen) {
    try {
      const response = await axios.get(route('newspapers.availableForReturn'));
      availableNewspapers.value = response.data;
      selectedNewspapers.value = [];
      searchQuery.value = '';
    } catch (error) {
      console.error('Error fetching available newspapers:', error);
    }
  }
});

// Filter newspapers based on search
const filteredNewspapers = computed(() => {
  if (!searchQuery.value) {
    return availableNewspapers.value;
  }
  const query = searchQuery.value.toLowerCase();
  return availableNewspapers.value.filter(paper =>
    paper.name.toLowerCase().includes(query)
  );
});

// Check if all filtered newspapers are selected
const isAllSelected = computed(() => {
  return filteredNewspapers.value.length > 0 &&
    filteredNewspapers.value.every(paper => selectedNewspapers.value.includes(paper.id));
});

// Toggle select all
const toggleSelectAll = () => {
  if (isAllSelected.value) {
    // Deselect all filtered newspapers
    const filteredIds = filteredNewspapers.value.map(p => p.id);
    selectedNewspapers.value = selectedNewspapers.value.filter(id => !filteredIds.includes(id));
  } else {
    // Select all filtered newspapers
    const filteredIds = filteredNewspapers.value.map(p => p.id);
    const uniqueIds = new Set([...selectedNewspapers.value, ...filteredIds]);
    selectedNewspapers.value = Array.from(uniqueIds);
  }
};

const canSubmit = computed(() => {
  return selectedNewspapers.value.length > 0 && form.reason.trim().length > 0;
});

const submitReturn = () => {
  if (!canSubmit.value) {
    console.log('Cannot submit - validation failed');
    return;
  }
  
  console.log('Submitting return with:', {
    newspaper_ids: selectedNewspapers.value,
    reason: form.reason
  });
  
  processing.value = true;
  form.newspaper_ids = selectedNewspapers.value;
  
  form.post(route('newspapers.return'), {
    preserveScroll: true,
    onSuccess: (response) => {
      console.log('Return successful:', response);
      processing.value = false;
      form.reset();
      selectedNewspapers.value = [];
      searchQuery.value = '';
      emit('close');
    },
    onError: (errors) => {
      console.error('Return failed with errors:', errors);
      processing.value = false;
    },
    onFinish: () => {
      console.log('Request finished');
    },
  });
};

const handleClose = () => {
  form.reset();
  selectedNewspapers.value = [];
  searchQuery.value = '';
  emit('close');
};
</script>