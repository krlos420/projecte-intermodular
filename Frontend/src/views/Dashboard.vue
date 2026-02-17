<template>
  <div class="dashboard-layout">
    <!-- Navbar Superior -->
    <nav class="navbar">
      <div class="nav-content">
        <h1 class="logo">Comp-Together</h1>
        <div class="user-menu">
          <span class="user-greeting">Hola, {{ user?.name }} 👋</span>
          <button @click="cerrarSesion" class="btn-logout">
            <span class="icon">🚪</span> Salir
          </button>
        </div>
      </div>
    </nav>

    <main class="dashboard-content">
      <Spinner v-if="cargando" mensaje="Cargando tu casa..." />

      <div v-else class="grid-container">
        <!-- Panel Izquierdo: Info Casa -->
        <aside class="sidebar-info">
          <div class="card info-card">
            <div class="card-header">
              <h2>🏠 {{ nombreCasa || 'Mi Casa' }}</h2>
              <button @click="mostrarInfoCasa = !mostrarInfoCasa" class="btn-icon mobile-only">
                {{ mostrarInfoCasa ? '▼' : '▶' }}
              </button>
            </div>
            
            <div class="card-body" :class="{ 'show-mobile': mostrarInfoCasa }">
              <div class="invite-section">
                <span>Código de invitación:</span>
                <div class="code-box">
                  <strong>{{ infoCasa?.invite_code }}</strong>
                  <button @click="copiarCodigo" class="btn-copy">📋</button>
                </div>
              </div>

              <div class="members-section">
                <h3>Compañeros</h3>
                <ul class="members-list">
                  <li v-for="companero in infoCasa?.users" :key="companero.id_user">
                    <div class="member-avatar">{{ companero.name.charAt(0) }}</div>
                    <span class="member-name">{{ companero.name }}</span>
                    <span v-if="companero.id_user === infoCasa?.creator_id" class="badge-admin">Admin</span>
                  </li>
                </ul>
              </div>

              <button @click="salirDeCasa" class="btn-danger-outline">Salir de la casa</button>
            </div>
          </div>

          <!-- Widget Estadísticas Rápidas (Nuevo) -->
          <div class="card stats-card">
            <h3>Resumen Gastos</h3>
            <div class="stats-row">
              <div class="stat-item">
                <span class="stat-label">Total Gasto</span>
                <span class="stat-value">{{ totalGastos }}€</span>
              </div>
              <div class="stat-item">
                <button @click="irEstadisticas" class="btn-stats">Ver Gráficas 📊</button>
              </div>
            </div>
          </div>

          <!-- Widget Lista de la Compra (Nuevo) -->
          <div class="card shopping-card">
            <div class="card-header-small">
              <h3>🛒 Lista Compra</h3>
              <span v-if="pendingCount > 0" class="badge-count">{{ pendingCount }}</span>
            </div>
            
            <div class="shopping-preview">
              <p v-if="pendingCount === 0" class="text-muted">¡Nevera llena! ✅</p>
              <ul v-else class="preview-list">
                <li v-for="item in previewItems" :key="item.id">
                  • {{ item.name }}
                </li>
              </ul>
              <button @click="$router.push('/shopping-list')" class="btn-link-action">
                Ver lista completa →
              </button>
            </div>
          </div>
        </aside>

        <!-- Panel Principal: Gastos -->
        <section class="main-panel">
          <div class="panel-header">
            <h2>Movimientos Recientes</h2>
            <button @click="abrirModalNuevo" class="btn-primary">+ Añadir Gasto</button>
          </div>

          <!-- Tabla para Desktop -->
          <div class="desktop-table-container">
            <table class="expenses-table" v-if="gastos.length > 0">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Título</th>
                  <th>Pagado por</th>
                  <th class="text-right">Cantidad</th>
                  <th class="text-right">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="gasto in gastos" :key="gasto.id">
                  <td>{{ formatearFecha(gasto.date) }}</td>
                  <td class="font-medium">{{ gasto.title }}</td>
                  <td>
                    <span class="badge-user">{{ gasto.payer?.name || 'Usuario' }}</span>
                  </td>
                  <td class="text-right amount">{{ gasto.amount }}€</td>
                  <td class="text-right actions-cell">
                    <button @click="abrirModalEditar(gasto)" class="btn-icon-sm edit" title="Editar">✏️</button>
                    <button @click="eliminarGasto(gasto.id)" class="btn-icon-sm delete" title="Eliminar">🗑️</button>
                  </td>
                </tr>
              </tbody>
            </table>
            
            <div v-else class="empty-state">
              <div class="empty-icon">💸</div>
              <p>No hay gastos registrados aún.</p>
              <button @click="abrirModalNuevo" class="btn-link">Añadir el primero</button>
            </div>
          </div>

          <!-- Lista Tarjetas para Móvil -->
          <div class="mobile-list-container">
            <div v-if="gastos.length === 0" class="empty-state">
              <p>No hay gastos registrados.</p>
            </div>
            <div v-else class="expense-cards">
              <div v-for="gasto in gastos" :key="gasto.id" class="mobile-card">
                <div class="card-top">
                  <span class="card-date">{{ formatearFecha(gasto.date) }}</span>
                  <span class="card-amount">{{ gasto.amount }}€</span>
                </div>
                <div class="card-main">
                  <h4>{{ gasto.title }}</h4>
                  <span class="card-payer">Paga: {{ gasto.payer?.name }}</span>
                </div>
                <div class="card-actions">
                  <button @click="abrirModalEditar(gasto)" class="btn-text edit">Editar</button>
                  <button @click="eliminarGasto(gasto.id)" class="btn-text delete">Eliminar</button>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </main>

    <!-- Modal Formulario -->
    <div v-if="mostrarFormulario" class="modal-overlay" @click.self="cerrarModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>{{ modoEdicion ? 'Editar Gasto' : 'Nuevo Gasto' }}</h3>
          <button @click="cerrarModal" class="btn-close">×</button>
        </div>
        
        <div class="modal-body">
          <label>Concepto</label>
          <input v-model="gastoActual.title" placeholder="Ej: Compra semanal" class="input-form"/>
          
          <label>Cantidad (€)</label>
          <input v-model="gastoActual.amount" type="number" step="0.01" placeholder="0.00" class="input-form"/>
          
          <label>Fecha</label>
          <input v-model="gastoActual.date" type="date" class="input-form"/>
        </div>

        <div class="modal-footer">
          <button @click="cerrarModal" class="btn-secondary">Cancelar</button>
          <button @click="guardarGasto" class="btn-primary">
            {{ modoEdicion ? 'Actualizar' : 'Guardar' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useUserStore } from '../stores/UserStore'
import { useHouseStore } from '../stores/HouseStore'
import { useExpenseStore } from '../stores/ExpenseStore'
import { useShoppingStore } from '../stores/ShoppingStore'
import Spinner from '../components/Spinner.vue'

export default {
  components: { Spinner },
  setup() {
    const router = useRouter()
    const userStore = useUserStore()
    const houseStore = useHouseStore()
    const expenseStore = useExpenseStore()
    
    const { house } = storeToRefs(houseStore)
    const { user } = storeToRefs(userStore)
    const { expenses } = storeToRefs(expenseStore)
    
    // Shopping Store
    const shoppingStore = useShoppingStore()
    const { items: shoppingItems } = storeToRefs(shoppingStore) 
    const pendingCount = computed(() => shoppingStore.pendingCount)
    const previewItems = computed(() => shoppingItems.value.filter(i => !i.is_completed).slice(0, 3))

    const mostrarInfoCasa = ref(false) // Para móvil
    const mostrarFormulario = ref(false)
    const modoEdicion = ref(false)
    const cargando = ref(true)
    const gastoActual = ref({ id: null, title: '', amount: '', date: '' })

    const nombreCasa = computed(() => house.value?.name || '')
    const infoCasa = computed(() => house.value)
    const gastos = computed(() => expenses.value)
    
    // Total de gastos simple
    const totalGastos = computed(() => {
      return expenses.value.reduce((acc, curr) => acc + parseFloat(curr.amount || 0), 0).toFixed(2)
    })

    const cargarDatos = async () => {
      cargando.value = true
      try {
        if (!user.value) await userStore.fetchUser()
        await houseStore.fetchMyHouse()
        
        if (!house.value) {
          router.push('/create-join-house')
          return
        }
        await expenseStore.fetchExpenses()
        // Cargar lista de compra sin bloquear (background)
        shoppingStore.fetchItems()
      } catch (err) {
        console.error('Error cargando dashboard:', err)
      } finally {
        cargando.value = false
      }
    }

    const formatearFecha = (fecha) => {
      if (!fecha) return ''
      return new Date(fecha).toLocaleDateString()
    }

    const abrirModalNuevo = () => {
      modoEdicion.value = false
      gastoActual.value = { id: null, title: '', amount: '', date: new Date().toISOString().split('T')[0] }
      mostrarFormulario.value = true
    }

    const abrirModalEditar = (gasto) => {
      modoEdicion.value = true
      gastoActual.value = { ...gasto }
      mostrarFormulario.value = true
    }

    const cerrarModal = () => {
      mostrarFormulario.value = false
      modoEdicion.value = false
    }

    const guardarGasto = async () => {
      if (!gastoActual.value.title || !gastoActual.value.amount) return
      
      try {
        if (modoEdicion.value) {
          await expenseStore.updateExpense(gastoActual.value.id, gastoActual.value)
        } else {
          await expenseStore.createExpense(gastoActual.value)
        }
        cerrarModal()
      } catch (err) {
        console.error(err)
      }
    }

    const eliminarGasto = async (id) => {
      if (confirm('¿Eliminar gasto?')) {
        await expenseStore.deleteExpense(id)
      }
    }

    const copiarCodigo = () => {
      if (house.value?.invite_code) navigator.clipboard.writeText(house.value.invite_code)
    }

    const salirDeCasa = async () => {
      if (confirm('¿Salir de la casa?')) {
        await houseStore.leaveHouse()
        router.push('/create-join-house')
      }
    }

    const cerrarSesion = async () => {
      await userStore.logout()
      router.push('/login')
    }

    const irEstadisticas = () => router.push('/estadisticas')

    onMounted(cargarDatos)

    return {
      user, nombreCasa, infoCasa, gastos, totalGastos,
      pendingCount, previewItems,
      mostrarInfoCasa, mostrarFormulario, modoEdicion, cargando, gastoActual,
      abrirModalNuevo, abrirModalEditar, cerrarModal, guardarGasto, eliminarGasto,
      copiarCodigo, salirDeCasa, cerrarSesion, irEstadisticas, formatearFecha
    }
  }
}
</script>

<style scoped>
.dashboard-layout {
  min-height: 100vh;
  background-color: #f3f4f6;
  font-family: 'Inter', sans-serif;
}

/* Navbar */
.navbar {
  background: white;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  position: sticky;
  top: 0;
  z-index: 100;
  padding: 0 20px;
}

.nav-content {
  max-width: 1200px;
  margin: 0 auto;
  height: 64px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo {
  font-size: 1.5rem;
  font-weight: 800;
  background: linear-gradient(135deg, #42b983, #2e7d5a);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  margin: 0;
}

.user-menu {
  display: flex;
  align-items: center;
  gap: 15px;
}

.user-greeting {
  font-weight: 500;
  color: #4a5568;
}

.btn-logout {
  background: none;
  border: 1px solid #e2e8f0;
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  color: #e53e3e;
  font-size: 0.9rem;
  transition: all 0.2s;
}

.btn-logout:hover {
  background: #fff5f5;
  border-color: #feb2b2;
}

/* Grid Principal */
.dashboard-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 30px 20px;
}

.grid-container {
  display: grid;
  grid-template-columns: 300px 1fr;
  gap: 30px;
}

/* Sidebar / Info Card */
.sidebar-info {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.02);
  padding: 24px;
}

.card-header h2 {
  font-size: 1.25rem;
  margin: 0;
  color: #2d3748;
}

.invite-section {
  margin: 20px 0;
  background: #f7fafc;
  padding: 15px;
  border-radius: 12px;
}

.code-box {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 5px;
  font-family: monospace;
  font-size: 1.2rem;
  color: #2d3748;
}

.btn-copy {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.2rem;
}

.members-list {
  list-style: none;
  padding: 0;
  margin: 15px 0;
}

.members-list li {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 0;
  border-bottom: 1px solid #edf2f7;
}

.member-avatar {
  width: 32px;
  height: 32px;
  background: #42b983;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

.badge-admin {
  background: #fefcbf;
  color: #975a16;
  font-size: 0.7rem;
  padding: 2px 6px;
  border-radius: 4px;
  margin-left: auto;
}

.btn-danger-outline {
  width: 100%;
  margin-top: 10px;
  padding: 10px;
  border: 1px solid #fc8181;
  color: #c53030;
  background: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-danger-outline:hover {
  background: #fff5f5;
}

/* Stats Widget */
.stats-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 15px;
}

.stat-value {
  display: block;
  font-size: 1.8rem;
  font-weight: 700;
  color: #2d3748;
}

.btn-stats {
  background: #42b983;
  color: white;
  border: none;
  padding: 8px 12px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.9rem;
}

/* Shopping Widget */
.shopping-card .card-header-small {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.shopping-card h3 {
  margin: 0;
  font-size: 1.1rem;
  color: #2d3748;
}

.badge-count {
  background: #e53e3e;
  color: white;
  border-radius: 12px;
  padding: 2px 8px;
  font-size: 0.8rem;
  font-weight: bold;
}

.preview-list {
  list-style: none;
  padding: 0;
  margin: 10px 0;
  font-size: 0.9rem;
  color: #4a5568;
}

.preview-list li {
  margin-bottom: 5px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.btn-link-action {
  background: none;
  border: none;
  color: #42b983;
  font-weight: 600;
  cursor: pointer;
  padding: 0;
  font-size: 0.9rem;
}

.btn-link-action:hover {
  text-decoration: underline;
}

.text-muted {
  color: #a0aec0;
  font-size: 0.9rem;
  font-style: italic;
}

/* Panel Principal (Gastos) */
.main-panel {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.02);
  padding: 0; /* Padding controlado internamente */
  display: flex;
  flex-direction: column;
}

.panel-header {
  padding: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 15px;
  border-bottom: 1px solid #edf2f7;
}

.panel-header h2 {
  margin: 0;
  font-size: 1.5rem;
  color: #2d3748;
}

.btn-primary {
  background: #42b983;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: background 0.2s;
}

.btn-primary:hover {
  background: #38a172;
}

/* Tabla Desktop */
.desktop-table-container {
  overflow-x: auto;
}

.expenses-table {
  width: 100%;
  border-collapse: collapse;
}

.expenses-table th, 
.expenses-table td {
  padding: 16px 24px;
  text-align: left;
  border-bottom: 1px solid #edf2f7;
}

.expenses-table th {
  background: #f8fafc;
  color: #718096;
  font-weight: 600;
  font-size: 0.9rem;
}

.text-right { text-align: right; }
.font-medium { font-weight: 500; color: #2d3748; }

.badge-user {
  background: #ebf8ff;
  color: #2b6cb0;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.85rem;
}

.amount {
  font-weight: 700;
  color: #2d3748;
}

.btn-icon-sm {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.1rem;
  padding: 4px;
  margin-left: 8px;
  opacity: 0.6;
  transition: opacity 0.2s;
}

.btn-icon-sm:hover { opacity: 1; }

/* Mobile List (Oculto en desktop) */
.mobile-list-container {
  display: none;
  padding: 20px;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 30px;
  border-radius: 16px;
  width: 100%;
  max-width: 500px;
  box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.input-form {
  width: 100%;
  padding: 12px;
  margin-bottom: 15px;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  box-sizing: border-box;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}

.btn-secondary {
  background: #edf2f7;
  color: #4a5568;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
}

/* Responsive */
@media (max-width: 900px) {
  .grid-container {
    grid-template-columns: 1fr;
  }
  
  .desktop-table-container {
    display: none;
  }
  
  .mobile-list-container {
    display: block;
  }
  
  .card-body {
    display: none;
  }
  
  .card-body.show-mobile {
    display: block;
  }
  
  .mobile-only {
    display: inline-block;
  }
  
  /* Estilos Mobile Cards */
  .mobile-card {
    background: #f7fafc;
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 15px;
  }
  
  .card-top {
    display: flex;
    justify-content: space-between;
    font-size: 0.9rem;
    color: #718096;
    margin-bottom: 8px;
  }
}

@media (min-width: 901px) {
  .mobile-only {
    display: none;
  }
}
</style>