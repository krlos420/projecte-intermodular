<template>
  <div class="contenedor-estadisticas">
    <header>
      <h1>📊 Estadísticas del Mes</h1>
      <button @click="volverDashboard" class="btn-volver">← Volver</button>
    </header>

    <Spinner v-if="cargando" mensaje="Cargando estadísticas..." />

    <div v-else-if="estadisticas" class="contenido">
      <!-- Resumen general -->
      <div class="seccion-resumen">
        <div class="card-resumen">
          <div class="icono">💰</div>
          <div class="info">
            <p class="label">Total del mes</p>
            <p class="valor">{{ estadisticas.total_month }}€</p>
          </div>
        </div>
        
        <div class="card-resumen">
          <div class="icono">📝</div>
          <div class="info">
            <p class="label">Número de gastos</p>
            <p class="valor">{{ estadisticas.expenses_count }}</p>
          </div>
        </div>

        <div class="card-resumen">
          <div class="icono">👥</div>
          <div class="info">
            <p class="label">Promedio por persona</p>
            <p class="valor">{{ calcularPromedio() }}€</p>
          </div>
        </div>
      </div>

      <!-- Cuánto pagó cada uno -->
      <div class="seccion">
        <h2>💳 Pagos realizados</h2>
        <div class="lista-pagos">
          <div 
            v-for="pago in estadisticas.payments_by_user" 
            :key="pago.user_id" 
            class="item-pago"
          >
            <div class="nombre-usuario">{{ pago.user_name }}</div>
            <div class="barra-progreso">
              <div 
                class="barra-relleno" 
                :style="{ width: calcularPorcentaje(pago.total_paid) + '%' }"
              ></div>
            </div>
            <div class="cantidad-pagada">{{ pago.total_paid }}€</div>
          </div>
        </div>
      </div>

      <!-- Balances (quién debe a quién) -->
      <div class="seccion">
        <h2>⚖️ Balances</h2>
        <div v-if="estadisticas.balances && estadisticas.balances.length > 0" class="lista-balances">
          <div 
            v-for="balance in estadisticas.balances" 
            :key="balance.user_id" 
            class="item-balance"
            :class="balance.balance >= 0 ? 'positivo' : 'negativo'"
          >
            <div class="info-balance">
              <strong>{{ balance.user_name }}</strong>
              <p v-if="balance.balance > 0" class="texto-balance">
                Le deben <span class="monto">{{ balance.balance }}€</span>
              </p>
              <p v-else-if="balance.balance < 0" class="texto-balance">
                Debe <span class="monto">{{ Math.abs(balance.balance) }}€</span>
              </p>
              <p v-else class="texto-balance">
                Está al día ✅
              </p>
            </div>
            <div class="indicador-balance">
              <span v-if="balance.balance > 0" class="icono-positivo">📈</span>
              <span v-else-if="balance.balance < 0" class="icono-negativo">📉</span>
              <span v-else class="icono-neutro">✅</span>
            </div>
          </div>
        </div>
        <div v-else class="vacio">
          No hay datos de balances disponibles
        </div>
      </div>
    </div>

    <div v-else class="error">
      No se pudieron cargar las estadísticas
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import Spinner from '../components/Spinner.vue'

export default {
  components: {
    Spinner
  },
  setup() {
    const router = useRouter()
    const estadisticas = ref(null)
    const cargando = ref(true)

    const cargarEstadisticas = async () => {
      try {
        const response = await api.get('/expenses/statistics')
        if (response.data.status === 'true') {
          estadisticas.value = response.data.statistics
        }
      } catch (err) {
        console.error('Error al cargar estadísticas:', err)
      } finally {
        cargando.value = false
      }
    }

    const calcularPromedio = () => {
      if (!estadisticas.value || !estadisticas.value.payments_by_user) return 0
      const numPersonas = estadisticas.value.payments_by_user.length
      if (numPersonas === 0) return 0
      return (estadisticas.value.total_month / numPersonas).toFixed(2)
    }

    const calcularPorcentaje = (cantidad) => {
      if (!estadisticas.value || estadisticas.value.total_month === 0) return 0
      return (cantidad / estadisticas.value.total_month * 100).toFixed(1)
    }

    const volverDashboard = () => {
      router.push('/dashboard')
    }

    onMounted(cargarEstadisticas)

    return {
      estadisticas,
      cargando,
      calcularPromedio,
      calcularPorcentaje,
      volverDashboard
    }
  }
}
</script>

<style scoped>
.contenedor-estadisticas {
  max-width: 900px;
  margin: 0 auto;
  padding: 20px;
}

header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

header h1 {
  margin: 0;
}

.btn-volver {
  padding: 10px 20px;
  background: #42b983;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.btn-volver:hover {
  background: #3aa876;
}

.cargando {
  text-align: center;
  padding: 50px;
  font-size: 1.2em;
  color: #666;
}

.error {
  text-align: center;
  padding: 50px;
  color: #f44336;
}

.seccion-resumen {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.card-resumen {
  background: white;
  border: 1px solid #ddd;
  border-radius: 10px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 15px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card-resumen .icono {
  font-size: 3em;
}

.card-resumen .info {
  flex: 1;
}

.card-resumen .label {
  margin: 0;
  color: #666;
  font-size: 0.9em;
}

.card-resumen .valor {
  margin: 5px 0 0 0;
  font-size: 1.8em;
  font-weight: bold;
  color: #42b983;
}

.seccion {
  background: white;
  border: 1px solid #ddd;
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 20px;
}

.seccion h2 {
  margin-top: 0;
  margin-bottom: 20px;
  font-size: 1.4em;
}

.lista-pagos {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.item-pago {
  display: grid;
  grid-template-columns: 150px 1fr 100px;
  align-items: center;
  gap: 15px;
}

.nombre-usuario {
  font-weight: bold;
}

.barra-progreso {
  background: #e0e0e0;
  border-radius: 10px;
  height: 25px;
  overflow: hidden;
}

.barra-relleno {
  background: linear-gradient(90deg, #42b983, #35a372);
  height: 100%;
  transition: width 0.3s ease;
}

.cantidad-pagada {
  text-align: right;
  font-weight: bold;
  font-size: 1.1em;
  color: #42b983;
}

.lista-balances {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.item-balance {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  border-radius: 8px;
  border: 2px solid #ddd;
}

.item-balance.positivo {
  background: #e8f5e9;
  border-color: #4caf50;
}

.item-balance.negativo {
  background: #ffebee;
  border-color: #f44336;
}

.info-balance strong {
  display: block;
  margin-bottom: 5px;
  font-size: 1.1em;
}

.texto-balance {
  margin: 0;
  color: #666;
}

.monto {
  font-weight: bold;
  font-size: 1.1em;
}

.item-balance.positivo .monto {
  color: #4caf50;
}

.item-balance.negativo .monto {
  color: #f44336;
}

.indicador-balance {
  font-size: 2em;
}

.vacio {
  text-align: center;
  color: #999;
  padding: 30px;
}

@media (max-width: 600px) {
  .item-pago {
    grid-template-columns: 1fr;
    gap: 10px;
  }
  
  .cantidad-pagada {
    text-align: left;
  }
}
</style>
