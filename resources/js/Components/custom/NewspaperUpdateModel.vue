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
            class="bg-black border-4 border-blue-600 rounded-[20px] shadow-xl w-5/6 lg:w-3/6 p-10 text-center"
          >
            <!-- Modal Title -->
            <DialogTitle class="text-xl font-bold text-white"
              >Edit Newspaper</DialogTitle
            >
            <form @submit.prevent="submit">
              <!-- Modal Form -->
              <div class="mt-6 space-y-4 text-left">
                <div class="flex items-center gap-8 mt-6">
                  <!-- Name input -->
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Name:</label
                    >
                    <input
                      v-model="form.name"
                      type="text"
                      id="name"
                      required
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.name" class="mt-4 text-red-500">{{
                      form.errors.name
                    }}</span>
                  </div>
                  <!-- Product Code input -->
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Product Code:</label
                    >
                    <input
                      v-model="form.productcode"
                      type="text"
                      id="productcode"
                      required
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.productcode" class="mt-4 text-red-500">{{
                      form.errors.productcode
                    }}</span>
                  </div>
                </div>

                <div class="flex items-center gap-8 mt-6">
                  <!-- Barcode input -->
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Barcode:</label
                    >
                    <input
                      v-model="form.barcode"
                      type="text"
                      id="barcode"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.barcode" class="mt-4 text-red-500">{{
                      form.errors.barcode
                    }}</span>
                  </div>
                  <!-- Batch No input -->
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Batch No:</label
                    >
                    <input
                      v-model="form.batch_no"
                      type="text"
                      id="batch_no"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.batch_no" class="mt-4 text-red-500">{{
                      form.errors.batch_no
                    }}</span>
                  </div>
                </div>

                <div class="flex items-center gap-8 mt-6">
                  <!-- Supplier input -->
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Supplier:</label
                    >
                    <input
                      v-model="form.supplier"
                      type="text"
                      id="supplier"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.supplier" class="mt-4 text-red-500">{{
                      form.errors.supplier
                    }}</span>
                  </div>
                  <!-- Duration input -->
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Duration:</label
                    >
                    <select
                      v-model="form.duration"
                      id="duration"
                      class="w-full px-4 py-2 mt-2 text-black bg-white rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                      required
                    >
                      <option value="monthly">Monthly</option>
                      <option value="weekly">Weekly</option>
                    </select>
                    <span v-if="form.errors.duration" class="mt-4 text-red-500">{{
                      form.errors.duration
                    }}</span>
                  </div>
                </div>

                <div class="flex items-center gap-8 mt-6">
                  <!-- Publication Date input -->
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Publication Date:</label
                    >
                    <input
                      v-model="form.publication_date"
                      type="date"
                      id="publication_date"
                      required
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.publication_date" class="mt-4 text-red-500">{{
                      form.errors.publication_date
                    }}</span>
                  </div>
                  <!-- Expire Date input -->
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Expire Date:</label
                    >
                    <input
                      v-model="form.expire_date"
                      type="date"
                      id="expire_date"
                      required
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.expire_date" class="mt-4 text-red-500">{{
                      form.errors.expire_date
                    }}</span>
                  </div>
                </div>

                <div class="flex items-center gap-8 mt-6">
                  <!-- Language input -->
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Language:</label
                    >
                    <input
                      v-model="form.language"
                      type="text"
                      id="language"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.language" class="mt-4 text-red-500">{{
                      form.errors.language
                    }}</span>
                  </div>
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Stock Quantity:</label
                    >
                    <input
                      v-model="form.stock_quantity"
                      type="number"
                      id="stock_quantity"
                      required
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.stock_quantity" class="mt-4 text-red-500">{{
                      form.errors.stock_quantity
                    }}</span>
                  </div>
                 
                </div>

                <div class="flex items-center gap-8 mt-6">
                  <!-- Selling Price input -->
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Selling Price:</label
                    >
                    <input
                      v-model="form.selling_price"
                      type="number"
                      id="selling_price"
                      required
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.selling_price" class="mt-4 text-red-500">{{
                      form.errors.selling_price
                    }}</span>
                  </div>
                  <!-- Cost Price input -->
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Cost Price:</label
                    >
                    <input
                      v-model="form.cost_price"
                      type="number"
                      id="cost_price"
                      required
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.cost_price" class="mt-4 text-red-500">{{
                      form.errors.cost_price
                    }}</span>
                  </div>
                </div>

                <div class="flex items-center gap-8 mt-6">
                  <!-- Return Count input -->
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
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.return" class="mt-4 text-red-500">{{
                      form.errors.return
                    }}</span>
                  </div>
                </div>

                <div class="flex items-center gap-8 mt-6">
                   <!-- Discount input -->
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Discount:</label
                    >
                    <input
                      v-model="form.discount"
                      type="number"
                      id="discount"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.discount" class="mt-4 text-red-500">{{
                      form.errors.discount
                    }}</span>
                  </div>
                  <!-- Discount Price input -->
                  <div class="w-full">
                    <label class="block text-sm font-medium text-gray-300"
                      >Discount Price:</label
                    >
                    <input
                      v-model="form.discount_price"
                      type="number"
                      id="discount_price"
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.discount_price" class="mt-4 text-red-500">{{
                      form.errors.discount_price
                    }}</span>
                  </div>
                </div>
              </div>

              <!-- Modal Buttons -->
              <div class="mt-6 space-x-4">
                <button
                  class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700"
                  type="submit"
                >
                  Save
                </button>
                <button
                  type="button"
                  class="px-4 py-2 text-gray-700 bg-gray-300 rounded hover:bg-gray-400"
                  @click="$emit('update:open', false)"
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
</template>

<script setup>
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import { ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";

const emit = defineEmits(["update:open"]);

const props = defineProps({
  open: Boolean,
  newspaper: Object,
});

const form = useForm({
  name: "",
  productcode: "",
  barcode: "",
  batch_no: "",
  supplier: "",
  duration: "monthly",
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

watch(
  () => props.newspaper,
  (newValue) => {
    if (newValue) {
      form.name = newValue.name || "";
      form.productcode = newValue.productcode || "";
      form.barcode = newValue.barcode || "";
      form.batch_no = newValue.batch_no || "";
      form.supplier = newValue.supplier || "";
      form.duration = newValue.duration || "monthly";
      form.publication_date = newValue.publication_date
        ? new Date(newValue.publication_date).toISOString().split("T")[0]
        : "";
      form.expire_date = newValue.expire_date
        ? new Date(newValue.expire_date).toISOString().split("T")[0]
        : "";
      form.language = newValue.language || "";
      form.stock_quantity = newValue.stock_quantity || "";
      form.cost_price = newValue.cost_price || "";
      form.selling_price = newValue.selling_price || "";
      form.discount = newValue.discount || "";
      form.discount_price = newValue.discount_price || "";
      form.return = newValue.return || 0;
    }
  },
  { immediate: true }
);

const submit = () => {
  form.put(`/newspapers/${props.newspaper.id}`, {
    onSuccess: () => {
      emit("update:open", false);
    },
  });
};
</script>

<style scoped>
/* Add any custom styles here */
</style>