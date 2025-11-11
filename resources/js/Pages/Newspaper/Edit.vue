<template>
  <AdminLayout>
    <div class="min-h-screen p-6 bg-gray-100">
      <div class="max-w-4xl min-h-screen p-6 mx-auto bg-gray-100">
        <h1 class="mb-6 text-2xl font-bold text-gray-800">Edit Newspaper</h1>
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Name Input -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input
              v-model="form.name"
              type="text"
              id="name"
              required
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
            />
            <span v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</span>
          </div>

          <!-- Product Code Input -->
          <div>
            <label for="productcode" class="block text-sm font-medium text-gray-700">Product Code</label>
            <input
              v-model="form.productcode"
              type="text"
              id="productcode"
              required
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
            />
            <span v-if="form.errors.productcode" class="text-sm text-red-500">{{ form.errors.productcode }}</span>
          </div>

          <!-- Other Inputs -->
          <!-- Repeat similar blocks for other fields like barcode, batch_no, supplier, etc. -->

          <!-- Submit Button -->
          <div>
            <button
              type="submit"
              class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400"
            >
              Save
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { defineProps } from "vue";

const props = defineProps({
  newspaper: Object,
});

const form = useForm({
  ...props.newspaper,
});

const submit = () => {
  form.put(`/newspapers/${props.newspaper.id}`, {
    onSuccess: () => {
      console.log("Newspaper updated successfully!");
    },
    onError: (errors) => {
      console.error("Form submission failed:", errors);
    },
  });
};
</script>