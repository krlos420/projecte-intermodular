<template>
  <div class="dashboard-layout">
    <!-- Navbar Superior -->
    <nav class="navbar">
      <div class="nav-content">
        <h1 class="logo">Comp-Together</h1>
        <div class="user-menu">
          <span class="user-greeting">Hola, {{ user?.name }} 👋</span>
          <button @click="$router.push('/profile')" class="btn-icon-nav" title="Mi Perfil">👤</button>
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
              <div class="invite-section" v-if="esAdmin">
                <span>Código de invitación:</span>
                <div class="code-box">
                  <strong>{{ infoCasa?.invite_code }}</strong>
                  <button @click="copiarCodigo" class="btn-copy">📋</button>
                </div>
              </div>

              <div class="house-details" style="margin-top: 15px; font-size: 0.9em; background: #f7fafc; padding: 10px; border-radius: 8px; position: relative;">
                <button v-if="esAdmin" @click="abrirModalConfig" class="btn-icon-sm" style="position: absolute; right: 10px; top: 10px;" title="Editar Configuración">⚙️</button>
                <p style="margin:0 0 5px 0;"><strong>Aforo Máximo:</strong> {{ infoCasa?.max_capacity || 4 }} personas</p>
                <p style="margin:0;"><strong>Alquiler Total:</strong> {{ infoCasa?.total_rent ? infoCasa.total_rent + '€' : '0€' }}</p>
                <p style="margin:8px 0 0 0; font-size:0.78em; color:#718096; font-style: italic;">ℹ️ El administrador no cuenta como ocupante, ya que no reside en la casa que arrienda.</p>
              </div>

              <div class="members-section">
                <h3>Compañeros</h3>
                <ul class="members-list">
                  <li v-for="companero in infoCasa?.users" :key="companero.id_user">
                    <div class="member-avatar">{{ companero.name.charAt(0) }}</div>
                    <span class="member-name">{{ companero.name }}</span>
                    <span v-if="companero.id_user === infoCasa?.creator_id" class="badge-admin">Admin</span>
                    <button v-if="esAdmin && companero.id_user !== infoCasa?.creator_id" @click="expulsarUsuario(companero)" class="btn-icon-sm" style="background:none;border:none;color:#c53030;cursor:pointer;margin-left:auto;" title="Expulsar">❌</button>
                  </li>
                </ul>
              </div>

              <button @click="salirDeCasa" class="btn-danger-outline">Salir de la casa</button>
            </div>
          </div>

          <!-- Widget de Peticiones de Unión (Solo Admin) -->
          <div class="card requests-card" v-if="esAdmin && joinRequests.length > 0">
            <h3>🔔 Peticiones Pendientes</h3>
            <ul class="requests-list">
              <li v-for="req in joinRequests" :key="req.id">
                <div class="req-info">
                  <span class="req-name">{{ req.user?.name }}</span>
                  <span class="req-phone" v-if="req.user?.phone">📞 {{ req.user.phone }}</span>
                </div>
                <div class="req-actions">
                  <button @click="responderPeticion(req.id, 'accept')" class="btn-accept" title="Aceptar">✓</button>
                  <button @click="responderPeticion(req.id, 'reject')" class="btn-reject" title="Rechazar">✗</button>
                </div>
              </li>
            </ul>
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

          <!-- Widget de Saldos Activos -->
          <div class="card balances-card" v-if="balances.length > 0">
            <h3>Saldos Activos</h3>
            <ul class="balances-list">
              <li v-for="bal in balances" :key="bal.user_id">
                <span class="bal-name">{{ bal.user_name }}</span>
                <div class="bal-info-actions">
                  <span v-if="bal.balance > 0" class="bal-positivo">Le deben {{ bal.balance }}€</span>
                  <span v-else-if="bal.balance < 0" class="bal-negativo">Debe {{ Math.abs(bal.balance) }}€</span>
                  <span v-else class="bal-neutro">Al día ✅</span>
                  <button 
                    v-if="bal.balance < 0 && user?.id_user === bal.user_id && companerosAcreedores.length > 0" 
                    @click="abrirModalLiquidacion(bal)" 
                    class="btn-icon-sm btn-pay" 
                    title="Pagar deuda a compañero">💸</button>
                </div>
              </li>
            </ul>
            <p v-if="companerosAcreedores.length === 0 && hayGastosPendientes" class="balances-hint">
              ⚠️ Gastos pendientes: usa el botón <strong>Pagar</strong> en el gasto para asignarlo a quien lo pagó
            </p>
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
            <button v-if="!esAdmin" @click="abrirModalNuevo" class="btn-primary">+ Añadir Gasto</button>
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
                    <span v-if="gasto.payer" class="badge-user">{{ gasto.payer.name }}</span>
                    <span v-else class="badge-user pending">Pendiente de pago</span>
                  </td>
                  <td class="text-right amount">{{ gasto.amount }}€</td>
                  <td class="text-right actions-cell">
                    <button v-if="esAdmin" @click="eliminarGasto(gasto.id)" class="btn-delete-expense" title="Eliminar">🗑️ Eliminar</button>
                    <button v-if="!esAdmin && !gasto.payer" @click="pagarGastoPendiente(gasto)" class="btn-primary" style="padding: 4px 8px; font-size: 0.8rem;" title="Yo he pagado esto">Pagar</button>
                  </td>
                </tr>
              </tbody>
            </table>
            
            <div v-else class="empty-state">
              <div class="empty-icon">💸</div>
              <p>No hay gastos registrados aún.</p>
              <button v-if="!esAdmin" @click="abrirModalNuevo" class="btn-primary">Añadir el primero</button>
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
                  <span class="card-payer" v-if="gasto.payer">Paga: {{ gasto.payer.name }}</span>
                  <span class="card-payer pending" v-else>Pendiente de pago</span>
                </div>
                <div class="card-actions">
                  <button v-if="esAdmin" @click="eliminarGasto(gasto.id)" class="btn-delete-expense">🗑️ Eliminar</button>
                  <button v-if="!esAdmin && !gasto.payer" @click="pagarGastoPendiente(gasto)" class="btn-primary" style="padding: 4px 8px; font-size: 0.8rem; margin-top: 5px;">Pagar</button>
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
          <h3>Nuevo Gasto</h3>
          <button @click="cerrarModal" class="btn-close">×</button>
        </div>
        
        <div class="modal-body">
          <label>Concepto</label>
          <input v-model="gastoActual.title" placeholder="Ej: Compra semanal" class="input-form"/>
          
          <label>Cantidad (€)</label>
          <input v-model="gastoActual.amount" type="number" step="0.01" placeholder="0.00" class="input-form"/>
          
          <label>Fecha</label>
          <input v-model="gastoActual.date" type="date" class="input-form"/>

          <label class="checkbox-container" style="margin-top: 15px; display: block;">
            <input type="checkbox" v-model="gastoActual.is_pending" />
            <span style="margin-left: 8px;">Aún no está pagado (Pendiente por todos)</span>
          </label>
        </div>

        <div class="modal-footer">
          <button @click="cerrarModal" class="btn-secondary">Cancelar</button>
          <button @click="preGuardarGasto" class="btn-primary">Guardar</button>
        </div>
      </div>
    </div>

    <!-- Modal Configuración Casa -->
    <div v-if="mostrarModalConfig" class="modal-overlay" @click.self="cerrarModalConfig">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Configuración de la Casa</h3>
          <button @click="cerrarModalConfig" class="btn-close">×</button>
        </div>
        
        <div class="modal-body">
          <label>Aforo Máximo</label>
          <input v-model="configActual.max_capacity" type="number" min="1" class="input-form"/>
          
          <label>Alquiler Total (€)</label>
          <input v-model="configActual.total_rent" type="number" step="10" min="0" class="input-form"/>
        </div>

        <div class="modal-footer">
          <button @click="cerrarModalConfig" class="btn-secondary">Cancelar</button>
          <button @click="guardarConfig" class="btn-primary">Guardar Cambios</button>
        </div>
      </div>
    </div>

    <!-- Modal Liquidar Deuda -->
    <div v-if="mostrarModalLiquidacion" class="modal-overlay" @click.self="cerrarModalLiquidacion">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Pagar mi parte</h3>
          <button @click="cerrarModalLiquidacion" class="btn-close">×</button>
        </div>
        
        <div class="modal-body">
          <p style="margin-bottom:12px; font-size:0.9rem; color:#4a5568;">
            Selecciona el compañero al que le debes dinero (quien pagó el gasto) y confirma la cantidad.
          </p>
          <label>¿A quién le estás pagando?</label>
          <select v-model="liquidacionActual.receiver_id" class="input-form">
            <option value="">Selecciona el compañero que pagó</option>
            <option v-for="c in companerosAcreedores" :key="c.user_id" :value="c.user_id">
              {{ c.user_name }} — le debes {{ c.balance }}€
            </option>
          </select>
          
          <label>Cantidad (€)</label>
          <input v-model="liquidacionActual.amount" type="number" step="0.01" min="0.01" class="input-form"/>
        </div>

        <div class="modal-footer">
          <button @click="cerrarModalLiquidacion" class="btn-secondary">Cancelar</button>
          <button @click="guardarLiquidacion" class="btn-primary" :disabled="!liquidacionActual.receiver_id || !liquidacionActual.amount">Confirmar Pago</button>
        </div>
      </div>
    </div>

    <!-- Modal Confirmación Global -->
    <ConfirmModal 
      :isOpen="mostrarConfirmacion"
      :title="modalOptions.titulo"
      :message="modalOptions.mensaje"
      :confirmClass="modalOptions.confirmClass"
      @confirm="confirmarAccion"
      @cancel="cerrarConfirmacion"
    />
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useUserStore } from '../stores/UserStore'
import { useHouseStore } from '../stores/HouseStore'
import { useExpenseStore } from '../stores/ExpenseStore'
import { useShoppingStore } from '../stores/ShoppingStore'
import Spinner from '../components/Spinner.vue'
import ConfirmModal from '../components/ConfirmModal.vue'
import api from '../services/api'

export default {
  components: { Spinner, ConfirmModal },
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
    const mostrarModalConfig = ref(false)
    const mostrarModalLiquidacion = ref(false)
    const cargando = ref(true)
    const gastoActual = ref({ id: null, title: '', amount: '', date: '', is_pending: false })
    const configActual = ref({ max_capacity: 4, total_rent: 0 })
    const liquidacionActual = ref({ receiver_id: '', amount: '' })
    
    // Variables para el modal de confirmación
    const mostrarConfirmacion = ref(false)
    const tipoConfirmacion = ref('')
    const modalOptions = ref({ titulo: '', mensaje: '', confirmClass: 'btn-primary' })
    const itemAEliminar = ref(null)
    
    // Variables para los saldos y peticiones
    const balances = ref([])
    const joinRequests = ref([])

    const nombreCasa = computed(() => house.value?.name || '')
    const infoCasa = computed(() => house.value)
    const gastos = computed(() => expenses.value)
    const esAdmin = computed(() => user.value?.id_user === infoCasa.value?.creator_id)
    
    // Total de gastos simple
    const totalGastos = computed(() => {
      return expenses.value.reduce((acc, curr) => acc + parseFloat(curr.amount || 0), 0).toFixed(2)
    })

    const companerosAcreedores = computed(() => {
      return balances.value.filter(b => b.balance > 0)
    })

    const hayGastosPendientes = computed(() => {
      return expenses.value.some(g => !g.payer)
    })

    const cargarDatos = async (silent = false) => {
      if (!silent) cargando.value = true
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
        
        // Cargar estadísticas para obtener balances
        const statsResponse = await api.get('/expenses/statistics')
        if (statsResponse.data?.status === 'true') {
          balances.value = statsResponse.data.statistics.balances || []
        }

        // Si es admin, cargar peticiones
        if (esAdmin.value) {
          const reqRes = await api.get('/houses/join-requests')
          if (reqRes.data?.status === 'true') {
            joinRequests.value = reqRes.data.requests || []
          }
        }
      } catch (err) {
        console.error('Error cargando dashboard:', err)
      } finally {
        if (!silent) cargando.value = false
      }
    }

    const formatearFecha = (fecha) => {
      if (!fecha) return ''
      return new Date(fecha).toLocaleDateString()
    }

    const abrirModalNuevo = () => {
      gastoActual.value = { id: null, title: '', amount: '', date: new Date().toISOString().split('T')[0], is_pending: false }
      mostrarFormulario.value = true
    }

    const cerrarModal = () => {
      mostrarFormulario.value = false
    }

    const cerrarConfirmacion = () => {
      mostrarConfirmacion.value = false
    }

    const preGuardarGasto = () => {
      if (!gastoActual.value.title || !gastoActual.value.amount) return
      modalOptions.value = { 
        titulo: 'Confirmar Gasto', 
        mensaje: '¿Estás seguro de registrar este gasto? Una vez creado, no podrá ser modificado.',
        confirmClass: 'btn-primary'
      }
      tipoConfirmacion.value = 'crear'
      mostrarConfirmacion.value = true
    }

    const eliminarGasto = (id) => {
      itemAEliminar.value = id
      modalOptions.value = { 
        titulo: 'Eliminar Gasto', 
        mensaje: '¿Estás seguro de que quieres eliminar este gasto permanentemente?',
        confirmClass: 'btn-danger'
      }
      tipoConfirmacion.value = 'eliminar'
      mostrarConfirmacion.value = true
    }

    const pagarGastoPendiente = (gasto) => {
      gastoActual.value = gasto
      modalOptions.value = { 
        titulo: 'Pagar Gasto Pendiente', 
        mensaje: `¿Confirmas que has pagado "${gasto.title}" (${gasto.amount}€)? Se te asignará este gasto a ti.`,
        confirmClass: 'btn-primary'
      }
      tipoConfirmacion.value = 'pagarGasto'
      mostrarConfirmacion.value = true
    }

    const salirDeCasa = () => {
      modalOptions.value = { 
        titulo: 'Salir de la casa', 
        mensaje: '¿Estás seguro de abandonar esta casa? Perderás acceso a los gastos y listas compartidas.',
        confirmClass: 'btn-danger'
      }
      tipoConfirmacion.value = 'salir'
      mostrarConfirmacion.value = true
    }

    const expulsarUsuario = (companero) => {
      itemAEliminar.value = companero.id_user
      
      const userBalance = balances.value.find(b => b.user_id === companero.id_user)?.balance || 0
      
      let mensaje = `¿Estás seguro de que quieres expulsar a ${companero.name} de la casa?`
      if (userBalance < 0) {
        mensaje = `¡ATENCIÓN! ${companero.name} tiene una deuda pendiente de ${Math.abs(userBalance)}€. ¿Estás seguro de que quieres expulsarlo de la casa?`
      } else if (userBalance > 0) {
        mensaje = `¡ATENCIÓN! A ${companero.name} se le deben ${userBalance}€. ¿Estás seguro de que quieres expulsarlo de la casa?`
      }

      modalOptions.value = { 
        titulo: 'Expulsar Usuario', 
        mensaje: mensaje,
        confirmClass: 'btn-danger'
      }
      tipoConfirmacion.value = 'expulsar'
      mostrarConfirmacion.value = true
    }

    const confirmarAccion = async () => {
      mostrarConfirmacion.value = false
      try {
        if (tipoConfirmacion.value === 'crear') {
          await expenseStore.createExpense(gastoActual.value)
          cerrarModal()
          // Recargar datos silenciadamente
          await cargarDatos(true)
        } else if (tipoConfirmacion.value === 'eliminar') {
          await expenseStore.deleteExpense(itemAEliminar.value)
          itemAEliminar.value = null
          // Recargar datos silenciadamente
          await cargarDatos(true)
        } else if (tipoConfirmacion.value === 'pagarGasto') {
          const payload = {
            title: gastoActual.value.title,
            amount: gastoActual.value.amount,
            date: gastoActual.value.date.split('T')[0],
            is_pending: false
          }
          await expenseStore.updateExpense(gastoActual.value.id, payload)
          if (window.mostrarNotificacion) window.mostrarNotificacion('Gasto marcado como pagado por ti', 'exito')
          await cargarDatos(true)
        } else if (tipoConfirmacion.value === 'expulsar') {
          try {
            const res = await api.delete(`/houses/remove-user/${itemAEliminar.value}`)
            if (res.data?.status === 'true') {
              if (window.mostrarNotificacion) window.mostrarNotificacion('Usuario expulsado', 'exito')
            }
          } catch (err) {
            const msg = err.response?.data?.message || 'Error al expulsar'
            if (window.mostrarNotificacion) window.mostrarNotificacion(msg, 'error')
          }
          itemAEliminar.value = null
          await cargarDatos(true)
        } else if (tipoConfirmacion.value === 'salir') {
          await houseStore.leaveHouse()
          router.push('/create-join-house')
        }
      } catch (err) {
        console.error(err)
      }
    }

    const copiarCodigo = () => {
      if (house.value?.invite_code) navigator.clipboard.writeText(house.value.invite_code)
    }

    const cerrarSesion = async () => {
      await userStore.logout()
      router.push('/login')
    }

    const abrirModalConfig = () => {
      configActual.value = {
        max_capacity: infoCasa.value?.max_capacity || 4,
        total_rent: infoCasa.value?.total_rent || 0
      }
      mostrarModalConfig.value = true
    }

    const cerrarModalConfig = () => mostrarModalConfig.value = false

    const guardarConfig = async () => {
      try {
        await houseStore.updateHouseDetails(configActual.value)
        cerrarModalConfig()
        await cargarDatos(true)
      } catch (err) {
        console.error(err)
      }
    }

    const abrirModalLiquidacion = (bal) => {
      // Pre-rellenar con la deuda exacta del usuario
      const miDeuda = bal ? Math.abs(bal.balance) : ''
      liquidacionActual.value = { receiver_id: '', amount: miDeuda }
      mostrarModalLiquidacion.value = true
    }
    
    const cerrarModalLiquidacion = () => mostrarModalLiquidacion.value = false

    const guardarLiquidacion = async () => {
      try {
        await expenseStore.createSettlement({
          receiver_id: liquidacionActual.value.receiver_id,
          amount: parseFloat(liquidacionActual.value.amount)
        })
        cerrarModalLiquidacion()
        await cargarDatos(true) // recargar estadísticas y saldos
      } catch (err) {
        console.error(err)
      }
    }

    const responderPeticion = async (id, accion) => {
      try {
        const res = await api.put(`/houses/join-requests/${id}`, { action: accion })
        if (res.data?.status === 'true') {
          if (window.mostrarNotificacion) window.mostrarNotificacion('Petición procesada', 'exito')
          await cargarDatos(true)
        }
      } catch (err) {
        if (window.mostrarNotificacion) window.mostrarNotificacion('Error al procesar petición', 'error')
      }
    }

    const irEstadisticas = () => router.push('/estadisticas')

    let pollingInterval = null
    
    onMounted(async () => {
      await cargarDatos()
      pollingInterval = setInterval(() => {
        cargarDatos(true)
      }, 5000)
    })

    onUnmounted(() => {
      if (pollingInterval) clearInterval(pollingInterval)
    })

    return {
      user, nombreCasa, infoCasa, gastos, totalGastos, balances, companerosAcreedores, hayGastosPendientes,
      pendingCount, previewItems,
      mostrarInfoCasa, mostrarFormulario, mostrarModalConfig, mostrarModalLiquidacion, cargando, gastoActual, configActual, liquidacionActual,
      mostrarConfirmacion, modalOptions,
      esAdmin, joinRequests, responderPeticion,
      abrirModalNuevo, cerrarModal, preGuardarGasto, pagarGastoPendiente,
      abrirModalConfig, cerrarModalConfig, guardarConfig,
      abrirModalLiquidacion, cerrarModalLiquidacion, guardarLiquidacion,
      eliminarGasto, expulsarUsuario, confirmarAccion, cerrarConfirmacion,
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

.btn-icon-nav {
  background: #f7fafc;
  border: 1px solid #e2e8f0;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 1.1rem;
}

.btn-icon-nav:hover {
  background: #edf2f7;
  transform: scale(1.05);
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
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 15px rgba(0,0,0,0.05);
}

.card-header h2 {
  font-size: 1.25rem;
  margin: 0;
  color: #2d3748;
}

.btn-icon.mobile-only {
  background: #f7fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #4a5568;
  transition: background 0.2s;
}

.btn-icon.mobile-only:hover {
  background: #e2e8f0;
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

/* Balances Widget */
.balances-card h3 {
  margin-top: 0;
  margin-bottom: 12px;
  font-size: 1.1rem;
  color: #2d3748;
}

.balances-hint {
  margin-top: 10px;
  font-size: 0.82rem;
  color: #975a16;
  background: #fefcbf;
  border: 1px solid #f6e05e;
  border-radius: 8px;
  padding: 8px 12px;
  line-height: 1.4;
}

.balances-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.balances-list li {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 10px;
  border-bottom: 1px solid #edf2f7;
}

.balances-list li:last-child {
  border-bottom: none;
}

.bal-name {
  font-weight: 600;
  color: #2d3748;
}

.bal-info-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.bal-positivo {
  color: #38a169;
  font-weight: 600;
  background: #f0fff4;
  padding: 4px 8px;
  border-radius: 6px;
}

.bal-negativo {
  color: #c53030;
  background: #fff5f5;
  padding: 4px 8px;
  border-radius: 6px;
  font-weight: 600;
}

.bal-neutro {
  color: #38a169;
  font-weight: 600;
}

/* Requests Widget */
.requests-card h3 {
  margin-top: 0;
  margin-bottom: 12px;
  font-size: 1.1rem;
  color: #2d3748;
}

.requests-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.requests-list li {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px;
  background: #f7fafc;
  border-radius: 8px;
  margin-bottom: 8px;
}

.req-info {
  display: flex;
  flex-direction: column;
}

.req-name {
  font-weight: 600;
  color: #2d3748;
}

.card-payer {
  font-size: 0.85rem;
  color: #718096;
}

.card-payer.pending {
  color: #c53030;
  font-weight: 500;
}

.req-phone {
  font-size: 0.8rem;
  color: #718096;
}

.req-actions {
  display: flex;
  gap: 5px;
}

.btn-accept {
  background: #48bb78;
  color: white;
  border: none;
  border-radius: 6px;
  width: 32px;
  height: 32px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

.btn-reject {
  background: #f56565;
  color: white;
  border: none;
  border-radius: 6px;
  width: 32px;
  height: 32px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
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
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.btn-delete-expense {
  background: #fc8181;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 600;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.btn-delete-expense:hover {
  background: #e53e3e;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(229, 62, 62, 0.2);
}

.text-right { text-align: right; }
.font-medium { font-weight: 500; color: #2d3748; }

.badge-user {
  background: #edf2f7;
  color: #4a5568;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 500;
}

.badge-user.pending {
  background: #fed7d7;
  color: #c53030;
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
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
  background: white;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 4px 6px rgba(0,0,0,0.02);
  border: 1px dashed #e2e8f0;
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 15px;
  opacity: 0.8;
}

.empty-state p {
  color: #718096;
  margin-bottom: 20px;
  font-size: 1.1rem;
}

@media (max-width: 768px) {
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

@media (min-width: 769px) {
  .mobile-only {
    display: none !important;
  }
}
</style>