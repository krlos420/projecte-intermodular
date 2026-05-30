<template>
  <div class="shopping-layout">
    <!-- Header simple con navegación de vuelta -->
    <header class="page-header">
      <div class="header-content">
        <button @click="$router.push('/dashboard')" class="btn-back">← Volver</button>
        <h1>🛒 Lista de la Compra</h1>
        <span class="pending-badge">{{ pendingCount }} pendientes</span>
      </div>
    </header>

    <main class="main-content">
      <!-- Input para añadir rápido -->
      <div class="add-item-container">
        <div class="input-group">
          <input 
            v-model="newItemName" 
            @keyup.enter="addNew"
            placeholder="¿Qué falta en la nevera? (Ej: Leche, Huevos...)" 
            class="input-main"
            ref="inputFocus"
          />
          <input 
            v-model="newItemQuantity" 
            @keyup.enter="addNew"
            placeholder="Cant. (Opcional)" 
            class="input-qty"
          />
          <button @click="addNew" class="btn-add" :disabled="!newItemName">
            <span class="icon">+</span>
          </button>
        </div>
      </div>

      <!-- Lista de Items -->
      <div class="items-list-container">
        <Spinner v-if="loading" />
        
        <div v-else-if="items.length === 0" class="empty-state">
          <div class="empty-icon">📝</div>
          <h3>Tu lista está vacía</h3>
          <p>¡Todo controlado! No falta nada en casa.</p>
        </div>

        <transition-group name="list" tag="ul" class="items-list" v-else>
          <li 
            v-for="item in items" 
            :key="item.id" 
            class="item-row"
            :class="{ 'completed': item.is_completed }"
          >
            <label class="checkbox-container">
              <input 
                type="checkbox" 
                :checked="item.is_completed" 
                @change="toggleItem(item)"
              >
              <span class="checkmark"></span>
            </label>

            <div class="item-details">
              <span class="item-name">{{ item.name }}</span>
              <span v-if="item.quantity" class="item-qty">{{ item.quantity }}</span>
              <span class="item-meta">Añadido por {{ item.user?.name }}</span>
            </div>

            <button @click="deleteItem(item.id)" class="btn-delete" title="Eliminar">🗑️</button>
          </li>
        </transition-group>
      </div>
    </main>

    <!-- Modal de Confirmación -->
    <ConfirmModal 
      :isOpen="mostrarConfirmacion"
      title="Eliminar producto"
      message="¿Estás seguro de eliminar este producto de la lista? Si ya lo has comprado, puedes marcar la casilla en su lugar."
      confirmText="Eliminar"
      confirmClass="btn-danger"
      @confirm="handleConfirmDelete"
      @cancel="mostrarConfirmacion = false"
    />
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { useShoppingStore } from '../stores/ShoppingStore'
import { storeToRefs } from 'pinia'
import Spinner from '../components/Spinner.vue'
import ConfirmModal from '../components/ConfirmModal.vue'

export default {
  components: { Spinner, ConfirmModal },
  setup() {
    const store = useShoppingStore()
    const { items, loading } = storeToRefs(store)
    const newItemName = ref('')
    const newItemQuantity = ref('')
    const inputFocus = ref(null)

    const pendingCount = computed(() => store.pendingCount)

    onMounted(async () => {
      await store.fetchItems()
      if(inputFocus.value) inputFocus.value.focus()
    })

    const addNew = async () => {
      if (!newItemName.value.trim()) return
      const success = await store.addItem(newItemName.value, newItemQuantity.value)
      if (success) {
        newItemName.value = ''
        newItemQuantity.value = ''
      }
    }

    const toggleItem = (item) => {
      store.toggleComplete(item.id, item.is_completed)
    }

    const mostrarConfirmacion = ref(false)
    const itemAEliminar = ref(null)

    const deleteItem = (id) => {
      itemAEliminar.value = id
      mostrarConfirmacion.value = true
    }

    const handleConfirmDelete = () => {
      store.removeItem(itemAEliminar.value)
      mostrarConfirmacion.value = false
    }

    return {
      items, loading, newItemName, newItemQuantity,
      addNew, toggleItem, deleteItem, pendingCount, inputFocus,
      mostrarConfirmacion, handleConfirmDelete
    }
  }
}
</script>

<style scoped>
.shopping-layout {
  min-height: 100vh;
  background: #f8fafc;
  font-family: 'Inter', sans-serif;
}

.page-header {
  background: white;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  position: sticky;
  top: 0;
  z-index: 10;
}

.header-content {
  max-width: 800px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  gap: 15px;
}

.header-content h1 {
  font-size: 1.5rem;
  margin: 0;
  color: #2d3748;
  flex-grow: 1;
}

.btn-back {
  background: none;
  border: none;
  font-size: 1rem;
  cursor: pointer;
  color: #718096;
}

.pending-badge {
  background: #ebf8ff;
  color: #2b6cb0;
  padding: 5px 10px;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 600;
}

.main-content {
  max-width: 800px;
  margin: 30px auto;
  padding: 0 20px;
}

.add-item-container {
  background: white;
  padding: 20px;
  border-radius: 16px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.02);
  margin-bottom: 30px;
}

.input-group {
  display: flex;
  gap: 10px;
}

.input-main {
  flex-grow: 2;
  padding: 12px 15px;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  font-size: 1rem;
  transition: border-color 0.2s;
}

.input-qty {
  flex-grow: 1;
  max-width: 120px;
  padding: 12px 15px;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  font-size: 1rem;
}

.input-main:focus, .input-qty:focus {
  outline: none;
  border-color: #42b983;
}

.btn-add {
  background: #42b983;
  color: white;
  border: none;
  width: 50px;
  border-radius: 10px;
  font-size: 1.5rem;
  cursor: pointer;
  transition: background 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-add:hover:not(:disabled) {
  background: #38a172;
}

.btn-add:disabled {
  background: #cbd5e0;
  cursor: not-allowed;
}

/* Lista Items */
.items-list-container {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.02);
  overflow: hidden;
}

.items-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.item-row {
  display: flex;
  align-items: center;
  padding: 15px 20px;
  border-bottom: 1px solid #edf2f7;
  transition: background 0.2s;
}

.item-row:last-child {
  border-bottom: none;
}

.item-row:hover {
  background: #f7fafc;
}

.item-row.completed {
  background: #fafafa;
  opacity: 0.7;
}

.item-row.completed .item-name {
  text-decoration: line-through;
  color: #a0aec0;
}

/* Checkbox bonito */
.checkbox-container {
  display: block;
  position: relative;
  padding-left: 35px;
  cursor: pointer;
  user-select: none;
}

.checkbox-container input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
}

.checkmark {
  position: absolute;
  top: -10px;
  left: 0;
  height: 24px;
  width: 24px;
  background-color: #eee;
  border-radius: 6px;
  transition: all 0.2s;
}

.checkbox-container:hover input ~ .checkmark {
  background-color: #ccc;
}

.checkbox-container input:checked ~ .checkmark {
  background-color: #42b983;
}

.checkmark:after {
  content: "";
  position: absolute;
  display: none;
}

.checkbox-container input:checked ~ .checkmark:after {
  display: block;
}

.checkbox-container .checkmark:after {
  left: 9px;
  top: 5px;
  width: 5px;
  height: 10px;
  border: solid white;
  border-width: 0 3px 3px 0;
  transform: rotate(45deg);
}

.item-details {
  flex-grow: 1;
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.item-name {
  font-size: 1.1rem;
  color: #2d3748;
  font-weight: 500;
}

.item-qty {
  background: #edf2f7;
  color: #4a5568;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 0.85rem;
}

.item-meta {
  font-size: 0.8rem;
  color: #a0aec0;
  margin-left: auto;
  margin-right: 15px;
}

.btn-delete {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.2rem;
  opacity: 0;
  transition: opacity 0.2s;
}

.item-row:hover .btn-delete {
  opacity: 1;
}

.empty-state {
  padding: 50px;
  text-align: center;
  color: #718096;
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 20px;
}

/* Transiciones de lista */
.list-enter-active,
.list-leave-active {
  transition: all 0.4s ease;
}
.list-leave-active {
  position: absolute;
  width: 100%;
}
.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateX(30px) scale(0.9);
}

@media (max-width: 600px) {
  .item-meta {
    display: none; /* Simplificar en móvil */
  }
}
</style>
