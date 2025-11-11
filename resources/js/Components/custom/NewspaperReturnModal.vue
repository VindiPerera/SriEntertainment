<template>
  <Modal :show="open" @close="handleClose">
    <div class="p-6 w-full max-w-xl">
      <h2 class="text-lg font-medium text-gray-900">Return Newspaper</h2>

      <form @submit.prevent="submitReturn" class="mt-4 space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Select Newspaper</label>
          <select
            v-model="form.newspaper_id"
            @change="onSelectChange"
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
            required
          >
            <option value="" disabled>Select a newspaper</option>
            <option v-for="paper in newspapers" :key="paper.id" :value="paper.id">
              {{ paper.name }} — (In stock: {{ paper.stock_quantity ?? 0 }})
            </option>
          </select>
        </div>

        <div v-if="selectedStock !== null" class="text-sm text-gray-600">
          Available stock: <span class="font-semibold">{{ selectedStock }}</span>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Quantity to Return</label>
          <input
            type="number"
            v-model.number="form.quantity"
            :min="1"
            :max="selectedStock || 999999"
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
            required
          />
          <p v-if="maxExceeded" class="text-red-500 text-sm mt-1">Quantity exceeds available stock.</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Reason</label>
          <textarea
            v-model="form.reason"
            rows="3"
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
            required
          />
        </div>

        <div class="flex justify-end space-x-3 mt-4">
          <button type="button" @click="handleClose" class="px-4 py-2 bg-gray-100 rounded-md">Cancel</button>
          <button
            type="submit"
            class="px-4 py-2 bg-red-600 text-white rounded-md"
            :disabled="processing || !canSubmit"
          >
            Submit Return
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

const props = defineProps({
  open: Boolean,
  newspapers: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['close', 'update:open']);

const processing = ref(false);

const form = useForm({
  newspaper_id: '',
  quantity: 1,
  reason: '',
});

const selectedStock = ref(null);

watch(() => props.newspapers, (list) => {
  // if only one paper available, preselect it
  if (list && list.length === 1) {
    form.newspaper_id = list[0].id;
    selectedStock.value = Number(list[0].stock_quantity || 0);
  }
});

const onSelectChange = () => {
  const selected = props.newspapers.find(p => String(p.id) === String(form.newspaper_id));
  selectedStock.value = selected ? Number(selected.stock_quantity || 0) : null;
  if (form.quantity < 1) form.quantity = 1;
  if (selectedStock.value !== null && form.quantity > selectedStock.value) form.quantity = selectedStock.value;
};

const maxExceeded = computed(() => {
  return selectedStock.value !== null && form.quantity > selectedStock.value;
});

const canSubmit = computed(() => {
  return form.newspaper_id && form.quantity >= 1 && !maxExceeded.value && form.reason.trim().length > 0;
});

const submitReturn = () => {
  if (!canSubmit.value) return;
  processing.value = true;
  form.post(route('newspapers.return'), {
    preserveScroll: true,
    onSuccess: () => {
      processing.value = false;
      form.reset();
      selectedStock.value = null;
      emit('close');
    },
    onError: () => {
      processing.value = false;
    },
  });
};

const handleClose = () => {
  emit('close');
};
</script>
