<template>
  <TransitionRoot as="template" :show="open">
    <Dialog as="div" class="relative z-10" @close="closeModal">
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
        <div class="fixed inset-0 bg-black bg-opacity-75 transition-opacity" />
      </TransitionChild>

      <!-- Modal Content -->
      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel
              class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6"
            >
              <!-- Confirmation Message -->
              <div class="sm:flex sm:items-start">
                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                  <i class="ri-delete-bin-line text-red-600"></i>
                </div>
                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                  <DialogTitle as="h3" class="text-base font-semibold leading-6 text-gray-900">
                    Delete Newspaper
                  </DialogTitle>
                  <div class="mt-2">
                    <p class="text-sm text-gray-500">
                      Are you sure you want to delete "{{ selectedNewspaper?.name || 'this newspaper' }}"?
                      This action cannot be undone.
                    </p>
                  </div>
                </div>
              </div>

              <!-- Modal Buttons -->
              <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                <button
                  type="button"
                  class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto"
                  @click="deleteItem"
                  :disabled="form.processing"
                >
                  <span v-if="form.processing">Deleting...</span>
                  <span v-else>Delete</span>
                </button>
                <button
                  type="button"
                  class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                  @click="closeModal"
                  :disabled="form.processing"
                >
                  Cancel
                </button>
              </div>

              <!-- Error Message -->
              <div v-if="form.hasErrors" class="mt-4">
                <p class="text-sm text-red-600">
                  {{ form.errors[Object.keys(form.errors)[0]] || "An error occurred while deleting the newspaper." }}
                </p>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
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
import { useForm } from "@inertiajs/vue3";
import { watch } from "vue";

const emit = defineEmits(["update:open"]);

const props = defineProps({
  open: {
    type: Boolean,
    required: true,
  },
  selectedNewspaper: {
    type: Object,
    default: null,
  },
});

const form = useForm({});

const playClickSound = () => {
  const clickSound = new Audio("/sounds/click-sound.mp3");
  clickSound.play().catch(e => console.log("Audio play failed:", e));
};

const closeModal = () => {
  playClickSound();
  emit("update:open", false);
};

// Delete the selected newspaper
const deleteItem = () => {
  if (!props.selectedNewspaper?.id) {
    console.error("No newspaper selected for deletion");
    return;
  }

  playClickSound();
  
  form.delete(route('newspapers.destroy', props.selectedNewspaper.id), {
    preserveScroll: true,
    onSuccess: () => {
      closeModal();
      // Reset the form
      form.reset();
    },
    onError: (errors) => {
      console.error("Delete failed:", errors);
    },
    onFinish: () => {
      // Form processing is complete
    },
  });
};

// Debug: log when props change
watch(() => props.open, (newVal) => {
  console.log('Modal open state:', newVal);
});

watch(() => props.selectedNewspaper, (newVal) => {
  console.log('Selected newspaper:', newVal);
});
</script>