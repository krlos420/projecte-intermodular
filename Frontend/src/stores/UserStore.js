import { defineStore } from 'pinia'
import api from '../services/api'

export const useUserStore = defineStore('user', {
    state: () => ({
        user: null,
        token: localStorage.getItem('token') || null,
        loading: false,
        error: null
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        currentUser: (state) => state.user
    },

    actions: {
        async register(userData) {
            this.loading = true
            this.error = null
            try {
                const response = await api.post('/auth/register', userData)
                if (response.data.status === 'true') {
                    this.token = response.data.token
                    this.user = response.data.user
                    localStorage.setItem('token', this.token)
                    return true
                }
            } catch (err) {
                this.error = err.response?.data?.message || 'Error en registro'
                if (window.mostrarNotificacion) window.mostrarNotificacion(this.error, 'error')
                throw err
            } finally {
                this.loading = false
            }
        },

        async login(credentials) {
            this.loading = true
            this.error = null
            try {
                const response = await api.post('/auth/login', credentials)
                if (response.data.status === 'true') {
                    this.token = response.data.token
                    this.user = response.data.user
                    localStorage.setItem('token', this.token)
                    return true
                }
            } catch (err) {
                this.error = err.response?.data?.message || 'Error al iniciar sesión'
                if (window.mostrarNotificacion) window.mostrarNotificacion(this.error, 'error')
                throw err
            } finally {
                this.loading = false
            }
        },

        async logout() {
            try {
                await api.post('/auth/logout')
            } catch (err) {
                console.error('Error al cerrar sesión', err)
                if (window.mostrarNotificacion) window.mostrarNotificacion('Error al cerrar sesión', 'error')
            } finally {
                this.token = null
                this.user = null
                localStorage.removeItem('token')
            }
        },

        async fetchUser() {
            if (!this.token) return
            this.loading = true
            try {
                // Asumiendo que existe un endpoint /user o /auth/me
                // Si no existe, usamos el stored user o lo implementaremos después
                const response = await api.get('/users/me')
                this.user = response.data
            } catch (err) {
                this.logout()
            } finally {
                this.loading = false
            }
        },

        async updateUser(userData) {
            this.loading = true
            this.error = null
            try {
                const response = await api.put('/users/update', userData)
                this.user = response.data.user
                if (window.mostrarNotificacion) window.mostrarNotificacion('Perfil actualizado', 'exito')
                return true
            } catch (err) {
                this.error = err.response?.data?.message || 'Error al actualizar perfil'
                if (window.mostrarNotificacion) window.mostrarNotificacion(this.error, 'error')
                throw err
            } finally {
                this.loading = false
            }
        }
    }
})
