<template>
  <div class="contenedor-principal">
    <header>
      <h1>{{ nombreCasa || 'Mi Casa' }}</h1>
      <button @click="cerrarSesion" class="btn-salir">Cerrar Sesión</button>
    </header>

    <div class="seccion-gastos">
      <h2>Gastos Recientes</h2>
      <button @click="mostrarFormulario = true" class="btn-agregar">+ Añadir Gasto</button>
      
      <div v-if="gastos.length === 0" class="vacio">
        No hay gastos todavía
      </div>

      <div v-else class="lista-gastos">
        <div v-for="gasto in gastos" :key="gasto.id" class="item-gasto">
          <div>
            <strong>{{ gasto.title }}</strong>
            <p>{{ gasto.date }} - {{ gasto.payer?.name }}</p>
          </div>
          <div class="cantidad">{{ gasto.amount }}€</div>
        </div>
      </div>
    </div>

    <!-- Modal añadir gasto -->
    <div v-if="mostrarFormulario" class="modal">
      <div class="contenido-modal">
        <h3>Añadir Gasto</h3>
        <input v-model="nuevoGasto.title" placeholder="Título" />
        <input v-model="nuevoGasto.amount" type="number" placeholder="Cantidad (€)" />
        <input v-model="nuevoGasto.date" type="date" />
        <div class="acciones">
          <button @click="guardarGasto" class="btn-guardar">Guardar</button>
          <button @click="mostrarFormulario = false" class="btn-cancelar">Cancelar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

export default {
  setup() {
    const router = useRouter()
    const gastos = ref([])
    const nombreCasa = ref('')
    const mostrarFormulario = ref(false)
    const nuevoGasto = ref({
      title: '',
      amount: '',
      date: new Date().toISOString().split('T')[0]
    })

    const cargarDatos = async () => {
      try {
        const casaRes = await api.get('/houses/my-house')
        if (casaRes.data.status === 'true') {
          nombreCasa.value = casaRes.data.house.name
        }

        const gastosRes = await api.get('/expenses')
        if (gastosRes.data.status === 'true') {
          gastos.value = gastosRes.data.expenses
        }
      } catch (err) {
        if (err.response?.status === 404) {
          router.push('/create-join-house')
        }
      }
    }

    const guardarGasto = async () => {
      try {
        const response = await api.post('/expenses/store', nuevoGasto.value)
        if (response.data.status === 'true') {
          gastos.value.unshift(response.data.expense)
          mostrarFormulario.value = false
          nuevoGasto.value = { title: '', amount: '', date: new Date().toISOString().split('T')[0] }
        }
      } catch (err) {
        alert('Error al crear gasto')
      }
    }

    const cerrarSesion = () => {
      localStorage.removeItem('token')
      router.push('/login')
    }

    onMounted(cargarDatos)

    return {
      gastos,
      nombreCasa,
      mostrarFormulario,
      nuevoGasto,
      guardarGasto,
      cerrarSesion
    }
  }
}
</script>

<style scoped>
.contenedor-principal {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}
header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}
.btn-salir {
  padding: 10px 20px;
  background: #f44336;
  color: white;
  border: none;
  cursor: pointer;
  border-radius: 5px;
}
.btn-agregar {
  padding: 10px 20px;
  background: #42b983;
  color: white;
  border: none;
  cursor: pointer;
  margin: 10px 0;
  border-radius: 5px;
}
.lista-gastos {
  margin-top: 20px;
}
.item-gasto {
  display: flex;
  justify-content: space-between;
  padding: 15px;
  border: 1px solid #ddd;
  margin: 10px 0;
  border-radius: 8px;
}
.cantidad {
  font-size: 1.2em;
  font-weight: bold;
  color: #42b983;
}
.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
}
.contenido-modal {
  background: white;
  padding: 30px;
  border-radius: 10px;
  width: 400px;
}
.contenido-modal input {
  width: 100%;
  padding: 10px;
  margin: 10px 0;
  border: 1px solid #ddd;
  border-radius: 5px;
}
.acciones {
  display: flex;
  gap: 10px;
  margin-top: 20px;
}
.btn-guardar {
  flex: 1;
  padding: 10px;
  background: #42b983;
  color: white;
  border: none;
  cursor: pointer;
  border-radius: 5px;
}
.btn-cancelar {
  flex: 1;
  padding: 10px;
  background: #ccc;
  border: none;
  cursor: pointer;
  border-radius: 5px;
}
.vacio {
  text-align: center;
  color: #999;
  padding: 40px;
}
</style>