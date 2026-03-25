<template>
  <Head title="User Management" />
  <Banner />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16">
    <Header />

    <div class="w-full md:w-5/6 py-12 space-y-8">
      <!-- Header Row -->
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <Link href="/">
            <img src="/images/back-arrow.png" class="w-14 h-14" alt="Back" />
          </Link>
          <p class="text-4xl font-bold tracking-wide text-black uppercase">User Management</p>
        </div>
        <p class="text-3xl italic font-bold text-black">
          <span class="px-4 py-1 mr-3 text-white bg-black rounded-xl">{{ users.length }}</span>
          <span class="text-xl">/ Total Users</span>
        </p>
      </div>

      <!-- Add User Button -->
      <div class="flex justify-end">
        <button
          @click="openCreateModal"
          class="md:px-12 py-4 px-4 md:text-2xl font-bold tracking-wider text-white uppercase bg-blue-600 rounded-xl hover:bg-blue-700"
        >
          <i class="md:pr-4 ri-add-circle-fill"></i> Add User
        </button>
      </div>

      <!-- Table -->
      <div v-if="users && users.length > 0" class="overflow-x-auto">
        <table class="w-full text-gray-700 bg-white border border-gray-300 rounded-lg shadow-md table-auto">
          <thead>
            <tr class="bg-gradient-to-r from-blue-600 via-blue-500 to-blue-600 text-[16px] text-white border-b border-blue-700 text-left">
              <th class="p-4 font-semibold tracking-wide uppercase">#</th>
              <th class="p-4 font-semibold tracking-wide uppercase">Name</th>
              <th class="p-4 font-semibold tracking-wide uppercase">Email</th>
              <th class="p-4 font-semibold tracking-wide uppercase">Role</th>
              <th class="p-4 font-semibold tracking-wide uppercase">Created At</th>
              <th class="p-4 font-semibold tracking-wide uppercase">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(user, index) in users" :key="user.id" class="hover:bg-gray-100 border-b border-gray-200">
              <td class="px-6 py-3">{{ index + 1 }}</td>
              <td class="px-6 py-3 font-medium">{{ user.name }}</td>
              <td class="px-6 py-3">{{ user.email }}</td>
              <td class="px-6 py-3">
                <span
                  :class="{
                    'bg-red-100 text-red-700': user.role_type === 'Admin',
                    'bg-blue-100 text-blue-700': user.role_type === 'Manager',
                    'bg-green-100 text-green-700': user.role_type === 'Cashier',
                  }"
                  class="px-3 py-1 rounded-full text-sm font-bold uppercase"
                >
                  {{ user.role_type }}
                </span>
              </td>
              <td class="px-6 py-3 text-sm text-gray-500">{{ formatDate(user.updated_at) }}</td>
              <td class="px-6 py-3 space-x-2">
                <button
                  @click="openEditModal(user)"
                  class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600"
                >
                  Edit
                </button>
                <button
                  @click="openDeleteModal(user)"
                  class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="text-center text-red-500 text-[17px]">
        No Users Available
      </div>
    </div>
  </div>

  <!-- Create Modal -->
  <div v-if="isCreateModalOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-8 space-y-6">
      <h2 class="text-2xl font-bold text-gray-800">Create New User</h2>
      <form @submit.prevent="submitCreate" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
          <input v-model="createForm.name" type="text" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
          <p v-if="createErrors.name" class="text-red-500 text-sm mt-1">{{ createErrors.name }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input v-model="createForm.email" type="email" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
          <p v-if="createErrors.email" class="text-red-500 text-sm mt-1">{{ createErrors.email }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
          <select v-model="createForm.role_type" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Select Role</option>
            <option value="Admin">Admin</option>
            <option value="Manager">Manager</option>
            <option value="Cashier">Cashier</option>
          </select>
          <p v-if="createErrors.role_type" class="text-red-500 text-sm mt-1">{{ createErrors.role_type }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input v-model="createForm.password" type="password" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
          <p v-if="createErrors.password" class="text-red-500 text-sm mt-1">{{ createErrors.password }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
          <input v-model="createForm.password_confirmation" type="password" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        <div class="flex justify-end space-x-3 pt-2">
          <button type="button" @click="closeCreateModal" class="px-6 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
          <button type="submit" :disabled="createLoading" class="px-6 py-2 rounded-lg bg-blue-600 text-white font-bold hover:bg-blue-700 disabled:opacity-60">
            {{ createLoading ? 'Creating...' : 'Create User' }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Modal -->
  <div v-if="isEditModalOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-8 space-y-6">
      <h2 class="text-2xl font-bold text-gray-800">Edit User</h2>
      <form @submit.prevent="submitEdit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
          <input v-model="editForm.name" type="text" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
          <p v-if="editErrors.name" class="text-red-500 text-sm mt-1">{{ editErrors.name }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input v-model="editForm.email" type="email" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
          <p v-if="editErrors.email" class="text-red-500 text-sm mt-1">{{ editErrors.email }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
          <select v-model="editForm.role_type" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="Admin">Admin</option>
            <option value="Manager">Manager</option>
            <option value="Cashier">Cashier</option>
          </select>
          <p v-if="editErrors.role_type" class="text-red-500 text-sm mt-1">{{ editErrors.role_type }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">New Password <span class="text-gray-400 font-normal">(leave blank to keep current)</span></label>
          <input v-model="editForm.password" type="password" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
          <p v-if="editErrors.password" class="text-red-500 text-sm mt-1">{{ editErrors.password }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
          <input v-model="editForm.password_confirmation" type="password" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        <div class="flex justify-end space-x-3 pt-2">
          <button type="button" @click="closeEditModal" class="px-6 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
          <button type="submit" :disabled="editLoading" class="px-6 py-2 rounded-lg bg-green-600 text-white font-bold hover:bg-green-700 disabled:opacity-60">
            {{ editLoading ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div v-if="isDeleteModalOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-8 space-y-6">
      <h2 class="text-2xl font-bold text-gray-800">Delete User</h2>
      <p class="text-gray-600">
        Are you sure you want to delete <span class="font-bold text-red-600">{{ selectedUser?.name }}</span>? This action cannot be undone.
      </p>
      <div class="flex justify-end space-x-3">
        <button @click="closeDeleteModal" class="px-6 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
        <button @click="submitDelete" :disabled="deleteLoading" class="px-6 py-2 rounded-lg bg-red-600 text-white font-bold hover:bg-red-700 disabled:opacity-60">
          {{ deleteLoading ? 'Deleting...' : 'Delete' }}
        </button>
      </div>
    </div>
  </div>

  <Footer />
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import Banner from '@/Components/Banner.vue';
import Header from '@/Components/custom/Header.vue';
import Footer from '@/Components/custom/Footer.vue';

const props = defineProps({
  users: Array,
});

// ── Create ───────────────────────────────────────────────
const isCreateModalOpen = ref(false);
const createLoading = ref(false);
const createErrors = ref({});

const createForm = ref({ name: '', email: '', role_type: '', password: '', password_confirmation: '' });

const openCreateModal = () => {
  createForm.value = { name: '', email: '', role_type: '', password: '', password_confirmation: '' };
  createErrors.value = {};
  isCreateModalOpen.value = true;
};
const closeCreateModal = () => { isCreateModalOpen.value = false; };

const submitCreate = () => {
  createLoading.value = true;
  createErrors.value = {};
  router.post(route('users.store'), createForm.value, {
    onSuccess: () => { closeCreateModal(); },
    onError: (errors) => { createErrors.value = errors; },
    onFinish: () => { createLoading.value = false; },
  });
};

// ── Edit ─────────────────────────────────────────────────
const isEditModalOpen = ref(false);
const editLoading = ref(false);
const editErrors = ref({});
const selectedUser = ref(null);

const editForm = ref({ name: '', email: '', role_type: '', password: '', password_confirmation: '' });

const openEditModal = (user) => {
  selectedUser.value = user;
  editForm.value = { name: user.name, email: user.email, role_type: user.role_type, password: '', password_confirmation: '' };
  editErrors.value = {};
  isEditModalOpen.value = true;
};
const closeEditModal = () => { isEditModalOpen.value = false; };

const submitEdit = () => {
  editLoading.value = true;
  editErrors.value = {};
  router.put(route('users.update', selectedUser.value.id), editForm.value, {
    onSuccess: () => { closeEditModal(); },
    onError: (errors) => { editErrors.value = errors; },
    onFinish: () => { editLoading.value = false; },
  });
};

// ── Delete ───────────────────────────────────────────────
const isDeleteModalOpen = ref(false);
const deleteLoading = ref(false);

const openDeleteModal = (user) => {
  selectedUser.value = user;
  isDeleteModalOpen.value = true;
};
const closeDeleteModal = () => { isDeleteModalOpen.value = false; };

const submitDelete = () => {
  deleteLoading.value = true;
  router.delete(route('users.destroy', selectedUser.value.id), {
    onSuccess: () => { closeDeleteModal(); },
    onFinish: () => { deleteLoading.value = false; },
  });
};

// ── Helpers ───────────────────────────────────────────────
const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  return new Date(dateStr).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};
</script>
