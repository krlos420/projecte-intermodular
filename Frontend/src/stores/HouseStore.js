import { defineStore } from 'pinia'
import api from '../services/api'

export const useHouseStore = defineStore('house', {
    state: () => ({
        house: null,
        loading: false,
        error: null
    }),

    getters: {
        hasHouse: (state) => !!state.house,
        isCreator: (state) => (userId) => state.house?.creator_id === userId,
        houseName: (state) => state.house?.name || ''
    },

    actions: {
        async fetchMyHouse() {
            this.loading = true
            this.error = null
            try {
                const response = await api.get('/houses/my-house')
                if (response.data.status === 'true') {
                    this.house = response.data.house
                    return this.house
                }
            } catch (err) {
                // 404 significa que no tiene casa, no es un error critico
                if (err.response?.status === 404) {
                    this.house = null
                } else {
                    this.error = 'Error al cargar info de la casa'
                    if (window.mostrarNotificacion) window.mostrarNotificacion(this.error, 'error')
                    throw err
                }
            } finally {
                this.loading = false
            }
        },

        async createHouse(name) {
            this.loading = true
            this.error = null
            try {
                const response = await api.post('/houses/create', { name })
                if (response.data.status === 'true') {
                    this.house = response.data.house
                    if (window.mostrarNotificacion) window.mostrarNotificacion('Casa creada correctamente', 'exito')
                    return this.house
                }
            } catch (err) {
                this.error = err.response?.data?.message || 'Error al crear casa'
                if (window.mostrarNotificacion) window.mostrarNotificacion(this.error, 'error')
                throw err
            } finally {
                this.loading = false
            }
        },

        async joinHouse(inviteCode) {
            this.loading = true
            this.error = null
            try {
                const response = await api.post('/houses/join', { invite_code: inviteCode })
                if (response.data.status === 'true') {
                    // Despues de unirse, cargamos la info completa
                    await this.fetchMyHouse()
                    if (window.mostrarNotificacion) window.mostrarNotificacion('Te has unido a la casa con éxito', 'exito')
                    return true
                }
            } catch (err) {
                this.error = err.response?.data?.message || 'Error al unirse a casa'
                if (window.mostrarNotificacion) window.mostrarNotificacion(this.error, 'error')
                throw err
            } finally {
                this.loading = false
            }
        },

        async leaveHouse() {
            this.loading = true
            this.error = null
            try {
                const response = await api.post('/houses/leave')
                if (response.data.status === 'true') {
                    this.house = null
                    if (window.mostrarNotificacion) window.mostrarNotificacion('Has salido de la casa', 'info')
                    return true
                }
            } catch (err) {
                this.error = err.response?.data?.message || 'Error al salir de casa'
                if (window.mostrarNotificacion) window.mostrarNotificacion(this.error, 'error')
                throw err
            } finally {
                this.loading = false
            }
        }
    }
})
