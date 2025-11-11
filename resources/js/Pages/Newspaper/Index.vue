<template>
  <Head title="Newspapers" />
  <Banner />
  <div
    class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16"
  >
    <!-- Include the Header -->
    <Header />
    <div class="w-full md:w-5/6 py-12 space-y-16">
      <div class="flex items-center justify-between">
        <div class="flex items-center justify-center space-x-4"></div>
        <p class="text-3xl italic font-bold text-black">
          <span class="px-4 py-1 mr-3 text-white bg-black rounded-xl">{{
            totalNewspapers
          }}</span>
          <span class="text-xl">/ Total Newspapers</span>
        </p>
      </div>
      <div class="flex items-center justify-between">
        <div class="flex items-center justify-center space-x-4">
          <Link href="/">
            <img src="/images/back-arrow.png" class="w-14 h-14" />
          </Link>
          <p class="text-4xl font-bold tracking-wide text-black uppercase">
            Newspapers
          </p>
        </div>



 <p
          @click="
            () => {
              if (HasRole(['Admin'])) {
                isReturnModalOpen = true;
              }
            }
          "
          :class="
            HasRole(['Admin'])
              ? 'md:px-12 py-4 px-4 md:text-2xl font-bold tracking-wider text-white uppercase bg-red-600 rounded-xl'
              : 'md:px-12 py-4 px-4 md:text-2xl font-bold tracking-wider text-white uppercase bg-red-600 cursor-not-allowed rounded-xl'
          "
          :title="
            HasRole(['Admin'])
              ? ''
              : 'You do not have permission to add more Newspapers'
          "
        >
          <i class="md:pr-4 ri-add-circle-fill"></i> Return Newspaper
        </p>


        <p
          @click="
            () => {
              if (HasRole(['Admin'])) {
                isCreateModalOpen = true;
              }
            }
          "
          :class="
            HasRole(['Admin'])
              ? 'md:px-12 py-4 px-4 md:text-2xl font-bold tracking-wider text-white uppercase bg-blue-600 rounded-xl'
              : 'md:px-12 py-4 px-4 md:text-2xl font-bold tracking-wider text-white uppercase bg-blue-600 cursor-not-allowed rounded-xl'
          "
          :title="
            HasRole(['Admin'])
              ? ''
              : 'You do not have permission to add more Newspapers'
          "
        >
          <i class="md:pr-4 ri-add-circle-fill"></i> Add More Newspaper
        </p>
      </div>

      <div class="flex items-center space-x-4">
        <!-- Search Input on the Left -->
        <div class="md:w-1/4 w-full">
          <input
            v-model="search"
            @input="performSearch"
            type="text"
            placeholder="Search ..."
            class="w-full custom-input"
          />
        </div>
      </div>

      <div class="grid md:grid-cols-4 grid-cols-1 gap-8">
        <template v-if="newspapers && newspapers.data && newspapers.data.length > 0">
          <div
            v-for="newspaper in newspapers.data"
            :key="newspaper.id"
            class="space-y-4 text-white transition-transform duration-300 transform bg-black border-4 border-black shadow-lg hover:-translate-y-4"
          >
            <div class="px-2 py-4 space-y-4">
              <div
                class="flex items-start space-x-3 justify-between text-[11px] font-bold tracking-wide"
              >
                <p class="text-justify">{{ newspaper.name || "N/A" }}</p>
                <p
                  class="px-3 text-white bg-green-700 py-2 rounded-full flex items-center"
                >
                  {{ newspaper.price || "N/A" }}
                </p>
              </div>

              <div class="flex items-center justify-center w-full space-x-4">
                <p
                  class="flex items-center space-x-2 text-justify text-gray-400"
                >
                  Publisher :

                  <b> &nbsp; {{ newspaper.publisher || "N/A" }} </b>
                </p>
              </div>
              <div class="flex items-center justify-between">
                <p
                  v-if="newspaper.stock_quantity > 0"
                  class="text-xl font-bold tracking-wider text-green-500"
                >
                  <i class="ri-checkbox-blank-circle-fill"></i> In Stock ({{ newspaper.stock_quantity }})
                </p>
                <p v-else class="text-xl font-bold tracking-wider text-red-500">
                  <i class="ri-checkbox-blank-circle-fill"></i> Out of Stock
                </p>
              </div>

              <div class="flex items-center justify-between">
                <p class="text-sm font-bold tracking-wider text-gray-400">
                  Returns: <span class="text-white">{{ newspaper.return || 0 }}</span>
                </p>
              </div>

              <div class="flex items-center justify-right space-x-2">
  <button
    :disabled="!HasRole(['Admin'])"
    @click="
      () => {
        if (HasRole(['Admin'])) {
          editNewspaper(newspaper);
        }
      }
    "
    :class="{
      'cursor-not-allowed opacity-50': !HasRole(['Admin']),
      'cursor-pointer hover:bg-green-600': HasRole(['Admin']),
    }"
    class="flex items-center justify-center w-10 h-10 text-gray-800 transition duration-200 bg-gray-100 rounded-full hover:text-white"
    :title="!HasRole(['Admin']) ? 'You do not have permission to edit newspapers' : 'Edit newspaper'"
  >
    <i class="ri-pencil-line"></i>
  </button>
<button
  :disabled="!HasRole(['Admin'])"
  @click="() => {
    if (HasRole(['Admin'])) {
      console.log('Setting delete target:', newspaper);
      deleteTargetNewspaper = newspaper;
      isDeleteModalOpen = true;
      console.log('Modal should open:', isDeleteModalOpen);
    }
  }"
  :class="{
    'cursor-not-allowed opacity-50': !HasRole(['Admin']),
    'cursor-pointer hover:bg-red-600': HasRole(['Admin']),
  }"
  class="flex items-center justify-center w-10 h-10 text-gray-800 transition duration-200 bg-gray-100 rounded-full hover:text-white"
  :title="!HasRole(['Admin']) ? 'You do not have permission to delete newspapers' : 'Delete newspaper'"
>
  <i class="ri-delete-bin-line"></i>
</button>
</div>
            </div>
          </div>
        </template>
        <template v-else>
          <div class="col-span-4 text-center text-gray-500">
            <p class="text-center text-red-500 text-[17px]">
              No Newspapers Available
            </p>
          </div>
        </template>
      </div>

      <!-- Pagination Controls -->
      <div v-if="newspapers && newspapers.data && newspapers.data.length > 0" class="flex items-center justify-center mt-8 space-x-4">
        <template v-for="link in newspapers.links" :key="link.label">
          <Link
            v-if="link.url"
            :href="link.url"
            class="px-4 py-2 text-sm font-medium rounded-md"
            :class="{
              'bg-blue-600 text-white': link.active,
              'bg-gray-200 text-gray-700 hover:bg-gray-300': !link.active
            }"
            v-html="link.label"
          />
          <span
            v-else
            class="px-4 py-2 text-sm font-medium text-gray-500 bg-gray-100 rounded-md"
            v-html="link.label"
          />
        </template>
      </div>

      <!-- Items per page selector -->
      <div class="flex items-center justify-end mt-4">
        <select
          v-model="perPage"
          @change="changeItemsPerPage"
          class="px-4 py-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="8">8 per page</option>
          <option value="12">12 per page</option>
          <option value="16">16 per page</option>
          <option value="24">24 per page</option>
        </select>
      </div>
    </div>
  </div>
  <NewspaperCreateModel v-model:open="isCreateModalOpen" />
  <NewspaperReturnModel
    v-model:open="isReturnModalOpen"
    :newspapers="newspapers ? newspapers.data : []"
    @close="isReturnModalOpen = false"
  />

  <NewspaperUpdateModel
    v-model:open="isUpdateModalOpen"
    :newspaper="selectedNewspaper"
    @close="isUpdateModalOpen = false"
    @update="handleUpdate"
  />

 <NewspaperDeleteModel
  v-model:open="isDeleteModalOpen"
  :selectedNewspaper="deleteTargetNewspaper"
  @update:open="isDeleteModalOpen = $event"
/>

  <Footer />
</template>

<script setup>
import { ref } from "vue";
import { Head } from "@inertiajs/vue3";
import { Link, router } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import { debounce } from "lodash";
import { HasRole } from "@/Utils/Permissions";
import NewspaperCreateModel from "@/Components/custom/NewspaperCreateModel.vue";

import NewspaperReturnModel from "@/Components/custom/NewspaperReturnModal.vue";


import NewspaperUpdateModel from "@/Components/custom/NewspaperUpdateModel.vue";
import NewspaperDeleteModel from "@/Components/custom/DeleteNewspaperModal.vue";

const isCreateModalOpen = ref(false);
const isReturnModalOpen = ref(false);
const isUpdateModalOpen = ref(false);
const selectedNewspaper = ref(null);
const isDeleteModalOpen = ref(false);
const deleteTargetNewspaper = ref(null);

const props = defineProps({
  newspapers: Object,
  totalNewspapers: Number,
  search: String,
});

const search = ref(props.search || "");
const perPage = ref('12'); // Default items per page

const performSearch = debounce(() => {
  applyFilters();
}, 500);

const changeItemsPerPage = () => {
  applyFilters();
};

const applyFilters = (page) => {
  router.get(
    route("newspapers.index"),
    {
      search: search.value,
      perPage: perPage.value,
    },
    { preserveState: true }
  );
};

const editNewspaper = (newspaper) => {
  selectedNewspaper.value = newspaper;
  isUpdateModalOpen.value = true;
};

const handleUpdate = (updatedData) => {
  router.put(route('newspapers.update', selectedNewspaper.value.id), updatedData, {
    onSuccess: () => {
      isUpdateModalOpen.value = false;
      selectedNewspaper.value = null;
    },
  });
};

const deleteNewspaper = (newspaperId) => {
  router.delete(route('newspapers.destroy', newspaperId), {
    onSuccess: () => {
      console.log('Newspaper deleted successfully');
    },
    onError: (error) => {
      console.error('Failed to delete newspaper:', error);
    },
  });
};
</script>

<style lang="css">
.pagination-nav {
  display: inline-flex;
  margin: -1px;
}

.pagination-link {
  padding: 0.75rem 1rem;
  font-size: 0.875rem;
  line-height: 1.25;
  color: #6b7280;
  background-color: white;
  border: 1px solid #d1d5db;
}

.pagination-link:hover {
  color: #2563eb;
  background-color: #eff6ff;
}

.pagination-link.active {
  color: white;
  background-color: #2563eb;
  border-color: #2563eb;
}

.pagination-link:first-child {
  border-top-left-radius: 0.375rem;
  border-bottom-left-radius: 0.375rem;
}

.pagination-link:last-child {
  border-top-right-radius: 0.375rem;
  border-bottom-right-radius: 0.375rem;
}

.items-per-page {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  background-color: white;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.items-per-page:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}

/* Dark theme adjustments */
.dark .pagination-link {
  color: #9ca3af;
  background-color: #1f2937;
  border-color: #4b5563;
}

.dark .pagination-link:hover {
  color: #60a5fa;
  background-color: #374151;
}

.dark .pagination-link.active {
  color: white;
  background-color: #2563eb;
  border-color: #2563eb;
}

.dark .items-per-page {
  background-color: #1f2937;
  border-color: #4b5563;
  color: #d1d5db;
}
</style>