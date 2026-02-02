<template>
    <div class="dashboard">
      <h1>Dashboard</h1>
      <p>Bienvenido a Comp-Together</p>
      <button @click="logout">Cerrar Sesión</button>
    </div>
  </template>
  
  <script>
  import { useRouter } from 'vue-router'
  
  export default {
    setup() {
      const router = useRouter()
  
      const logout = () => {
        localStorage.removeItem('token')
        router.push('/login')
      }
  
      return {
        logout
      }
    }
  }
  </script>
  
  <style scoped>
  .dashboard {
    padding: 20px;
  }
  
  button {
    padding: 10px 20px;
    background: #f44336;
    color: white;
    border: none;
    cursor: pointer;
  }
  </style>