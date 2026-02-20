<template>
  <div class="profile-layout">
    <nav class="navbar">
      <div class="nav-content">
        <h1 class="logo" @click="$router.push('/dashboard')" style="cursor: pointer;">Comp-Together</h1>
        <div class="user-menu">
          <button @click="$router.push('/dashboard')" class="btn-secondary">
            <span class="icon">⬅️</span> Volver al Dashboard
          </button>
        </div>
      </div>
    </nav>

    <main class="profile-content">
      <div class="form-card">
        <h2>Mi Perfil 👤</h2>
        <p class="subtitle">Actualiza tu información personal</p>
        
        <form @submit.prevent="guardarCambios">
          <div class="form-group">
            <label>Nombre Completo</label>
            <input v-model="form.name" type="text" placeholder="Tu nombre" required class="input-field" />
          </div>

          <div class="form-group">
            <label>Correo Electrónico</label>
            <input v-model="form.email" type="email" placeholder="ejemplo@correo.com" required class="input-field" />
          </div>

          <div class="form-group">
            <label>Teléfono</label>
            <input v-model="form.phone" type="text" placeholder="Tu teléfono" required class="input-field" />
          </div>

          <div class="actions">
            <button type="submit" class="btn-primary" :disabled="loading">
              {{ loading ? 'Actualizando...' : 'Guardar Cambios' }}
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../stores/UserStore'

export default {
  setup() {
    const router = useRouter()
    const userStore = useUserStore()
    
    const loading = ref(false)
    const form = ref({
      name: '',
      email: '',
      phone: ''
    })

    onMounted(async () => {
      // Pedir datos actualizados del backend via /users/me
      await userStore.fetchUser()
      
      const user = userStore.currentUser
      if (user) {
        form.value.name = user.name
        form.value.email = user.email
        form.value.phone = user.phone
      }
    })

    const guardarCambios = async () => {
      loading.value = true
      try {
        await userStore.updateUser(form.value)
      } catch (err) {
        console.error('Error al actualizar', err)
      } finally {
        loading.value = false
      }
    }

    return {
      form,
      loading,
      guardarCambios
    }
  }
}
</script>

<style scoped>
.profile-layout {
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

.btn-secondary {
  background: white;
  color: #4a5568;
  border: 1px solid #e2e8f0;
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 600;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-secondary:hover {
  background: #f7fafc;
  border-color: #cbd5e0;
}

/* Main Content */
.profile-content {
  max-width: 600px;
  margin: 40px auto;
  padding: 0 20px;
}

.form-card {
  background: white;
  padding: 40px;
  border-radius: 20px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.05);
}

.form-card h2 {
  font-size: 2em;
  color: #1a1a1a;
  margin-bottom: 10px;
  font-weight: 700;
}

.subtitle {
  color: #666;
  margin-bottom: 35px;
  font-size: 1.1em;
}

.form-group {
  margin-bottom: 25px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  color: #333;
  font-weight: 600;
  font-size: 0.95em;
}

.input-field {
  width: 100%;
  padding: 14px;
  border: 2px solid #e1e4e8;
  border-radius: 12px;
  font-size: 1em;
  transition: all 0.3s ease;
  background: #fdfdfd;
  box-sizing: border-box;
}

.input-field:focus {
  outline: none;
  border-color: #42b983;
  background: white;
  box-shadow: 0 0 0 4px rgba(66, 185, 131, 0.1);
}

.actions {
  margin-top: 35px;
}

.btn-primary {
  width: 100%;
  padding: 16px;
  background: #42b983;
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1.1em;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-primary:hover:not(:disabled) {
  background: #35a372;
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(66, 185, 131, 0.3);
}

.btn-primary:disabled {
  background: #a0dcb8;
  cursor: not-allowed;
  transform: none;
}

/* Responsive */
@media (max-width: 768px) {
  .profile-content {
    margin: 20px auto;
  }
  
  .form-card {
    padding: 25px 20px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
  }
  
  .form-card h2 {
    font-size: 1.5em;
  }
  
  .logo {
    font-size: 1.2rem;
  }
}
</style>
