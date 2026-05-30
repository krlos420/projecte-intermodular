<template>
  <transition name="modal">
    <div v-if="isOpen" class="modal-overlay" @click.self="cancel">
      <div class="modal-content">
        <div class="modal-header">
          <h3>{{ title }}</h3>
        </div>
        <div class="modal-body">
          <p>{{ message }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn-cancel" @click="cancel">{{ cancelText }}</button>
          <button class="btn-confirm" @click="confirm" :class="confirmClass">{{ confirmText }}</button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
export default {
  name: 'ConfirmModal',
  props: {
    isOpen: {
      type: Boolean,
      required: true
    },
    title: {
      type: String,
      default: 'Confirmación'
    },
    message: {
      type: String,
      required: true
    },
    confirmText: {
      type: String,
      default: 'Aceptar'
    },
    cancelText: {
      type: String,
      default: 'Cancelar'
    },
    confirmClass: {
      type: String,
      default: 'btn-primary'
    }
  },
  emits: ['confirm', 'cancel'],
  setup(props, { emit }) {
    const confirm = () => {
      emit('confirm');
    };

    const cancel = () => {
      emit('cancel');
    };

    return { confirm, cancel };
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(4px);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.modal-content {
  background: white;
  border-radius: 16px;
  width: 90%;
  max-width: 400px;
  padding: 24px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  text-align: center;
}

.modal-header h3 {
  margin: 0 0 10px;
  color: #2d3748;
  font-size: 1.3em;
}

.modal-body p {
  color: #4a5568;
  font-size: 1em;
  margin-bottom: 24px;
  line-height: 1.5;
}

.modal-footer {
  display: flex;
  justify-content: center;
  gap: 12px;
}

button {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  font-size: 1em;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-cancel {
  background: #edf2f7;
  color: #4a5568;
}

.btn-cancel:hover {
  background: #e2e8f0;
}

.btn-primary {
  background: #42b983;
  color: white;
}

.btn-primary:hover {
  background: #38a172;
}

.btn-danger {
  background: #f56565;
  color: white;
}

.btn-danger:hover {
  background: #e53e3e;
}

/* Animación */
.modal-enter-active, .modal-leave-active {
  transition: opacity 0.3s ease;
}
.modal-enter-from, .modal-leave-to {
  opacity: 0;
}
</style>
