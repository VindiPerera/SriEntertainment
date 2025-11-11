<template>
  <Transition name="slide-down">
    <div v-if="isVisible" class="notification-banner" :class="typeClass">
      <div class="notification-content">
        <div class="notification-icon">
          <i v-if="type === 'success'" class="ri-check-circle-line"></i>
          <i v-else-if="type === 'error'" class="ri-error-warning-line"></i>
          <i v-else-if="type === 'warning'" class="ri-alert-line"></i>
          <i v-else class="ri-information-line"></i>
        </div>
        <div class="notification-text">
          <span class="notification-message">{{ message }}</span>
        </div>
        <!-- <button @click="closeNotification" class="notification-close">
          <i class="ri-close-line"></i>
        </button> -->
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  type: {
    type: String,
    default: 'info', // success, error, warning, info
    validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
  },
  title: {
    type: String,
    default: 'Notification'
  },
  message: {
    type: String,
    required: true
  },
  autoClose: {
    type: Boolean,
    default: true
  },
  autoCloseDelay: {
    type: Number,
    default: 4000
  }
})

const emit = defineEmits(['close'])

const isVisible = ref(props.visible)

const typeClass = computed(() => {
  return {
    'notification-success': props.type === 'success',
    'notification-error': props.type === 'error',
    'notification-warning': props.type === 'warning',
    'notification-info': props.type === 'info'
  }
})

const buttonClass = computed(() => {
  return {
    'btn-success': props.type === 'success',
    'btn-error': props.type === 'error',
    'btn-warning': props.type === 'warning',
    'btn-info': props.type === 'info'
  }
})

watch(() => props.visible, (newValue) => {
  isVisible.value = newValue
  
  if (newValue && props.autoClose) {
    setTimeout(() => {
      closeNotification()
    }, props.autoCloseDelay)
  }
})

const closeNotification = () => {
  isVisible.value = false
  emit('close')
}
</script>

<style scoped>

.notification-banner {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 9999;
  
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.05);
}

.notification-success {
  background: linear-gradient(135deg, #6e67f1 0%, #6e70fc 100%);
  color: white;
}

.notification-error {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
}

.notification-warning {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  color: white;
}

.notification-info {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
}

.notification-content {
  display: flex;
  align-items: center;
  gap: 8px;
  max-width: 420px;
  width: 100%;
}

.notification-icon {
  font-size: 20px;
  flex-shrink: 0;
}

.notification-text {
  flex: 1;
  text-align: center;
}

.notification-message {
  font-size: 14px;
  font-weight: 500;
  margin: 0;
  white-space: pre-line;
}

.notification-close {
  background: none;
  border: none;
  color: currentColor;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s ease;
  flex-shrink: 0;
}

.notification-close:hover {
  background-color: rgba(255, 255, 255, 0.2);
}

.notification-close i {
  font-size: 18px;
}

/* Transition animations */
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.3s ease-out;
}

.slide-down-enter-from {
  transform: translateY(-100%);
  opacity: 0;
}

.slide-down-leave-to {
  transform: translateY(-100%);
  opacity: 0;
}

.slide-down-enter-to,
.slide-down-leave-from {
  transform: translateY(0);
  opacity: 1;
}
</style>