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
        <div class="grafico-container">
          <Doughnut v-if="chartData" :data="chartData" :options="chartOptions" />
          <p v-else class="vacio">No hay datos suficientes para el gráfico.</p>
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

import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import Spinner from '../components/Spinner.vue'
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'
import { Doughnut } from 'vue-chartjs'

ChartJS.register(ArcElement, Tooltip, Legend)

export default {
  components: {
    Spinner,
    Doughnut
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

    const chartData = computed(() => {
      if (!estadisticas.value || !estadisticas.value.payments_by_user) return null

      // Colores de la paleta del proyecto
      const defaultColors = ['#42b983', '#ff9800', '#2b6cb0', '#f56565', '#ed8936', '#48bb78', '#9f7aea']
      
      return {
        labels: estadisticas.value.payments_by_user.map(p => p.user_name),
        datasets: [
          {
            backgroundColor: defaultColors.slice(0, estadisticas.value.payments_by_user.length),
            data: estadisticas.value.payments_by_user.map(p => parseFloat(p.total_paid))
          }
        ]
      }
    })

    const chartOptions = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            font: {
              size: 14,
              family: "'Inter', sans-serif"
            },
            padding: 20
          }
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              let label = context.label || '';
              if (label) {
                label += ': ';
              }
              if (context.parsed !== null) {
                label += context.parsed.toFixed(2) + '€';
              }
              return label;
            }
          }
        }
      }
    }

    const volverDashboard = () => {
      router.push('/dashboard')
    }

    onMounted(cargarEstadisticas)

    return {
      estadisticas,
      cargando,
      chartData,
      chartOptions,
      calcularPromedio,
      volverDashboard
    }
  }
}
</script>

<style scoped>
.contenedor-estadisticas {
  max-width: 1000px;
  margin: 0 auto;
  padding: 30px 20px;
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
}

header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 35px;
  padding: 20px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

header h1 {
  margin: 0;
  background: linear-gradient(135deg, #ff9800, #f57c00);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-size: 1.8em;
}

.btn-volver {
  padding: 12px 24px;
  background: linear-gradient(135deg, #42b983, #35a372);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-volver:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(66, 185, 131, 0.3);
}

.error {
  text-align: center;
  padding: 60px;
  color: #f44336;
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  font-size: 1.1em;
}

/* Sección Resumen */
.seccion-resumen {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;
  margin-bottom: 35px;
}

.card-resumen {
  background: white;
  border: none;
  border-radius: 16px;
  padding: 25px;
  display: flex;
  align-items: center;
  gap: 18px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  transition: transform 0.3s, box-shadow 0.3s;
}

.card-resumen:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}

.card-resumen .icono {
  font-size: 3.2em;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
}

.card-resumen .info {
  flex: 1;
}

.card-resumen .label {
  margin: 0;
  color: #777;
  font-size: 0.9em;
  font-weight: 500;
}

.card-resumen .valor {
  margin: 8px 0 0 0;
  font-size: 2em;
  font-weight: bold;
  background: linear-gradient(135deg, #42b983, #35a372);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Secciones */
.seccion {
  background: white;
  border: none;
  border-radius: 16px;
  padding: 28px;
  margin-bottom: 25px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.seccion h2 {
  margin-top: 0;
  margin-bottom: 25px;
  font-size: 1.5em;
  color: #333;
}

/* Estilos Gráfico Doughnut */
.grafico-container {
  position: relative;
  width: 100%;
  max-width: 400px;
  height: 400px;
  margin: 0 auto;
  padding: 20px;
  background: linear-gradient(135deg, #fafafa, #f5f5f5);
  border-radius: 20px;
  box-shadow: inset 0 2px 10px rgba(0,0,0,0.02);
}

/* Lista de Balances */
.lista-balances {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.item-balance {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-radius: 12px;
  border: 2px solid transparent;
  transition: transform 0.2s, box-shadow 0.2s;
}

.item-balance:hover {
  transform: translateY(-2px);
}

.item-balance.positivo {
  background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
  border-color: #4caf50;
  box-shadow: 0 4px 12px rgba(76, 175, 80, 0.15);
}

.item-balance.positivo:hover {
  box-shadow: 0 6px 16px rgba(76, 175, 80, 0.25);
}

.item-balance.negativo {
  background: linear-gradient(135deg, #ffebee, #ffcdd2);
  border-color: #f44336;
  box-shadow: 0 4px 12px rgba(244, 67, 54, 0.15);
}

.item-balance.negativo:hover {
  box-shadow: 0 6px 16px rgba(244, 67, 54, 0.25);
}

.info-balance strong {
  display: block;
  margin-bottom: 8px;
  font-size: 1.15em;
  color: #333;
}

.texto-balance {
  margin: 0;
  color: #666;
  font-size: 0.95em;
}

.monto {
  font-weight: bold;
  font-size: 1.2em;
}

.item-balance.positivo .monto {
  color: #2e7d32;
}

.item-balance.negativo .monto {
  color: #c62828;
}

.indicador-balance {
  font-size: 2.5em;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
}

.vacio {
  text-align: center;
  color: #999;
  padding: 50px 30px;
  background: linear-gradient(135deg, #fafafa, #f5f5f5);
  border-radius: 12px;
  font-size: 1.05em;
}

/* Responsive para tablets */
@media (max-width: 768px) {
  .contenedor-estadisticas {
    padding: 20px 15px;
  }
  
  header {
    padding: 15px;
    flex-direction: column;
    gap: 15px;
    text-align: center;
  }
  
  header h1 {
    font-size: 1.5em;
  }
  
  .seccion-resumen {
    grid-template-columns: 1fr;
  }
  
  .grafico-container {
    height: 350px;
  }
  
  .item-balance {
    flex-direction: column;
    gap: 12px;
    text-align: center;
  }
}

/* Responsive para móviles */
@media (max-width: 480px) {
  .contenedor-estadisticas {
    padding: 15px 10px;
  }
  
  header h1 {
    font-size: 1.3em;
  }
  
  .btn-volver {
    padding: 10px 20px;
    font-size: 0.95em;
  }
  
  .seccion {
    padding: 20px;
  }
  
  .card-resumen {
    padding: 20px;
  }
  
  .card-resumen .icono {
    font-size: 2.5em;
  }
  
  .card-resumen .valor {
    font-size: 1.6em;
  }
  
  .seccion h2 {
    font-size: 1.3em;
  }
  
  .grafico-container {
    height: 300px;
    padding: 10px;
  }
  
  .indicador-balance {
    font-size: 2em;
  }
}
</style>
