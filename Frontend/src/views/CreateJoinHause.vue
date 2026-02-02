<template>
    <div class="house-container">
      <h1>Gestionar Casa</h1>
      
      <div class="section">
        <h2>Crear Nueva Casa</h2>
        <input v-model="houseName" type="text" placeholder="Nombre de la casa" />
        <button @click="createHouse">Crear Casa</button>
      </div>
  
      <div class="section">
        <h2>Unirse a Casa</h2>
        <input v-model="inviteCode" type="text" placeholder="Código de invitación" />
        <button @click="joinHouse">Unirse</button>
      </div>
  
      <p v-if="message" :class="messageType">{{ message }}</p>
    </div>
  </template>
  
  <script>
  import { ref } from 'vue'
  import { useRouter } from 'vue-router'
  import api from '../services/api'
  
  export default {
    setup() {
      const router = useRouter()
      const houseName = ref('')
      const inviteCode = ref('')
      const message = ref('')
      const messageType = ref('')
  
      const createHouse = async () => {
        try {
          const response = await api.post('/houses/create', {
            name: houseName.value
          })
          if (response.data.status === 'true') {
            message.value = `Casa creada. Código: ${response.data.house.invite_code}`
            messageType.value = 'success'
            setTimeout(() => router.push('/dashboard'), 2000)
          }
        } catch (err) {
          message.value = err.response?.data?.message || 'Error al crear casa'
          messageType.value = 'error'
        }
      }
  
      const joinHouse = async () => {
        try {
          const response = await api.post('/houses/join', {
            invite_code: inviteCode.value
          })
          if (response.data.status === 'true') {
            message.value = 'Te has unido a la casa correctamente'
            messageType.value = 'success'
            setTimeout(() => router.push('/dashboard'), 1500)
          }
        } catch (err) {
          message.value = err.response?.data?.message || 'Código inválido'
          messageType.value = 'error'
        }
      }
  
      return { houseName, inviteCode, message, messageType, createHouse, joinHouse }
    }
  }
  </script>
  
  <style scoped>
  .house-container {
    max-width: 500px;
    margin: 50px auto;
    padding: 20px;
  }
  .section {
    margin: 30px 0;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
  }
  input {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
  }
  button {
    width: 100%;
    padding: 10px;
    background: #42b983;
    color: white;
    border: none;
    cursor: pointer;
  }
  .success { color: green; }
  .error { color: red; }
  </style>