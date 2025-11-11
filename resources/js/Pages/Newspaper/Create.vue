<template>
  <AdminLayout>
    <div class="min-h-screen p-6 bg-gray-100">
      <div class="max-w-4xl min-h-screen p-6 mx-auto bg-gray-100">
        <h1 class="mb-6 text-2xl font-bold text-gray-800">Create Newspaper2</h1>
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Name Input -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700"
              >Name</label
            >
            <input
              v-model="form.name"
              type="text"
              id="name"
              required
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
            />
            <span v-if="form.errors.name" class="text-sm text-red-500">{{
              form.errors.name
            }}</span>
          </div>

          <!-- Product Code Input -->
          <div>
            <label for="productcode" class="block text-sm font-medium text-gray-700"
              >Product Code</label
            >
            <input
              v-model="form.productcode"
              type="text"
              id="productcode"
              required
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
            />
            <span v-if="form.errors.productcode" class="text-sm text-red-500">{{
              form.errors.productcode
            }}</span>
          </div>

          <!-- Barcode Input -->
          <div>
            <label for="barcode" class="block text-sm font-medium text-gray-700"
              >Barcode</label
            >
            <input
              v-model="form.barcode"
              type="text"
              id="barcode"
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
            />
            <span v-if="form.errors.barcode" class="text-sm text-red-500">{{
              form.errors.barcode
            }}</span>
          </div>

          <!-- Batch No Input -->
          <div>
            <label for="batch_no" class="block text-sm font-medium text-gray-700"
              >Batch No</label
            >
            <input
              v-model="form.batch_no"
              type="text"
              id="batch_no"
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
            />
            <span v-if="form.errors.batch_no" class="text-sm text-red-500">{{
              form.errors.batch_no
            }}</span>
          </div>

          <!-- Supplier Input -->
          <div>
            <label for="supplier" class="block text-sm font-medium text-gray-700"
              >Supplier</label
            >
            <input
              v-model="form.supplier"
              type="text"
              id="supplier"
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
            />
            <span v-if="form.errors.supplier" class="text-sm text-red-500">{{
              form.errors.supplier
            }}</span>
          </div>

          <!-- Duration Input -->
          <div>
            <label for="duration" class="block text-sm font-medium text-gray-700"
              >Duration</label
            >
            <select
              v-model="form.duration"
              id="duration"
              required
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
            >
              <option value="monthly">Monthly</option>
              <option value="weekly">Weekly</option>
            </select>
            <span v-if="form.errors.duration" class="text-sm text-red-500">{{
              form.errors.duration
            }}</span>
          </div>

          <!-- Publication Date Input -->
          <div>
            <label
              for="publication_date"
              class="block text-sm font-medium text-gray-700"
              >Publication Date</label
            >
            <input
              v-model="form.publication_date"
              type="date"
              id="publication_date"
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
              
            />
            <span
              v-if="form.errors.publication_date"
              class="text-sm text-red-500"
              >{{ form.errors.publication_date }}</span
            >
          </div>

          <!-- Expire Date Input -->
          <div>
            <label for="expire_date" class="block text-sm font-medium text-gray-700"
              >Expire Date</label
            >
            <input
              v-model="form.expire_date"
              type="date"
              id="expire_date"
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
            />
            <span v-if="form.errors.expire_date" class="text-sm text-red-500">{{
              form.errors.expire_date
            }}</span>
          </div>

          <!-- Language Input -->
          <div>
            <label for="language" class="block text-sm font-medium text-gray-700"
              >Language</label
            >
            <input
              v-model="form.language"
              type="text"
              id="language"
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
            />
            <span v-if="form.errors.language" class="text-sm text-red-500">{{
              form.errors.language
            }}</span>
          </div>

        
          <!-- Stock Quantity Input -->
          <div>
            <label
              for="stock_quantity"
              class="block text-sm font-medium text-gray-700"
              >Stock Quantity</label
            >
            <input
              v-model="form.stock_quantity"
              type="number"
              id="stock_quantity"
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
              required
            />
            <span
              v-if="form.errors.stock_quantity"
              class="text-sm text-red-500"
              >{{ form.errors.stock_quantity }}</span
            >
          </div>

          <!-- Cost Price Input -->
          <div>
            <label for="cost_price" class="block text-sm font-medium text-gray-700"
              >Cost Price</label
            >
            <input
              v-model="form.cost_price"
              type="number"
              step="0.01"
              id="cost_price"
              required
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
            />
            <span v-if="form.errors.cost_price" class="text-sm text-red-500">{{
              form.errors.cost_price
            }}</span>
          </div>

          <!-- Selling Price Input -->
          <div>
            <label for="selling_price" class="block text-sm font-medium text-gray-700"
              >Selling Price</label
            >
            <input
              v-model="form.selling_price"
              type="number"
              step="0.01"
              id="selling_price"
              required
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
            />
            <span v-if="form.errors.selling_price" class="text-sm text-red-500">{{
              form.errors.selling_price
            }}</span>
          </div>

          <!-- Discount Input -->
          <div>
            <label for="discount" class="block text-sm font-medium text-gray-700"
              >Discount</label
            >
            <input
              v-model="form.discount"
              type="number"
              step="0.01"
              id="discount"
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
            />
            <span v-if="form.errors.discount" class="text-sm text-red-500">{{
              form.errors.discount
            }}</span>
          </div>

          <!-- Discount Price Input -->
          <div>
            <label
              for="discount_price"
              class="block text-sm font-medium text-gray-700"
              >Discount Price</label
            >
            <input
              v-model="form.discount_price"
              type="number"
              step="0.01"
              id="discount_price"
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
            />
            <span v-if="form.errors.discount_price" class="text-sm text-red-500">{{
              form.errors.discount_price
            }}</span>
          </div>

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

const form = useForm({
  name: "",
  publisher: "",
  language: null,
  stock_quantity: "",
  publication_date: "",
  barcode:null,
  productcode: "",
  batch_no: null,
  supplier: null,
  duration: "monthly",
  expire_date: "",
  cost_price: "",
  selling_price: "",
  discount: null,
  discount_price: null,
});

const submit = () => {
  form.post("/newspapers", {
    preserveScroll: true,
    onSuccess: () => {
      console.log("Newspaper created successfully!");
      form.reset();
    },
    onError: (errors) => {
      console.error("Form submission failed:", errors);
    },
  });
};
</script>