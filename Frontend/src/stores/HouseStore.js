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
                    return this.house
                }
            } catch (err) {
                this.error = err.response?.data?.message || 'Error al crear casa'
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
                    return true
                }
            } catch (err) {
                this.error = err.response?.data?.message || 'Error al unirse a casa'
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
                    return true
                }
            } catch (err) {
                this.error = err.response?.data?.message || 'Error al salir de casa'
                throw err
            } finally {
                this.loading = false
            }
        }
    }
})
