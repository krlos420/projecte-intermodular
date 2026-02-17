import { defineStore } from 'pinia'
import api from '../services/api'

export const useShoppingStore = defineStore('shopping', {
    state: () => ({
        items: [],
        loading: false,
        error: null
    }),

    actions: {
        async fetchItems() {
            this.loading = true
            try {
                const response = await api.get('/shopping-list')
                if (response.data.status === 'true') {
                    this.items = response.data.items
                }
            } catch (err) {
                this.error = 'Error al cargar la lista'
                console.error(err)
            } finally {
                this.loading = false
            }
        },

        async addItem(name, quantity) {
            if (!name) return
            this.loading = true
            try {
                const response = await api.post('/shopping-list/store', { name, quantity })
                if (response.data.status === 'true') {
                    // Añadimos al principio o recargamos
                    this.items.unshift(response.data.item)
                    return true
                }
            } catch (err) {
                this.error = err.response?.data?.message || 'Error al añadir producto'
                throw err
            } finally {
                this.loading = false
            }
        },

        async toggleComplete(id, currentState) {
            try {
                // Optimistic update
                const item = this.items.find(i => i.id === id)
                if (item) item.is_completed = !currentState

                await api.put(`/shopping-list/update/${id}`, { is_completed: !currentState })

                // Reordenar: completados al final
                this.items.sort((a, b) => a.is_completed - b.is_completed || new Date(b.created_at) - new Date(a.created_at))
            } catch (err) {
                console.error('Error actualizando estado', err)
                // Revertir en caso de error
                const item = this.items.find(i => i.id === id)
                if (item) item.is_completed = currentState
            }
        },

        async removeItem(id) {
            if (!confirm('¿Has comprado o quieres eliminar este producto?')) return
            try {
                await api.delete(`/shopping-list/destroy/${id}`)
                this.items = this.items.filter(i => i.id !== id)
            } catch (err) {
                console.error('Error eliminando producto', err)
            }
        }
    },

    getters: {
        pendingItems: (state) => state.items.filter(i => !i.is_completed),
        completedItems: (state) => state.items.filter(i => i.is_completed),
        pendingCount: (state) => state.items.filter(i => !i.is_completed).length
    }
})
