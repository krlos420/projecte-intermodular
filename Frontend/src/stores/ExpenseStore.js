import { defineStore } from 'pinia'
import api from '../services/api'

export const useExpenseStore = defineStore('expense', {
    state: () => ({
        expenses: [],
        loading: false,
        error: null
    }),

    getters: {
        totalExpenses: (state) => state.expenses.length,
        recentExpenses: (state) => state.expenses.slice(0, 5) // Ejemplo de getter
    },

    actions: {
        async fetchExpenses() {
            this.loading = true
            this.error = null
            try {
                const response = await api.get('/expenses')
                if (response.data.status === 'true') {
                    this.expenses = response.data.expenses
                }
            } catch (err) {
                this.error = 'Error al cargar los gastos'
                if (window.mostrarNotificacion) window.mostrarNotificacion(this.error, 'error')
                console.error(err)
            } finally {
                this.loading = false
            }
        },

        async createExpense(expenseData) {
            this.loading = true
            this.error = null
            try {
                const response = await api.post('/expenses/store', expenseData)
                if (response.data.status === 'true') {
                    this.expenses.unshift(response.data.expense)
                    return true
                }
            } catch (err) {
                this.error = err.response?.data?.message || 'Error al crear gasto'
                if (window.mostrarNotificacion) window.mostrarNotificacion(this.error, 'error')
                throw err
            } finally {
                this.loading = false
            }
        },

        async updateExpense(id, expenseData) {
            this.loading = true
            this.error = null
            try {
                const response = await api.put(`/expenses/update/${id}`, expenseData)
                if (response.data.status === 'true') {
                    const index = this.expenses.findIndex(e => e.id === id)
                    if (index !== -1) {
                        this.expenses[index] = { ...this.expenses[index], ...response.data.expense }
                    }
                    return true
                }
            } catch (err) {
                this.error = err.response?.data?.message || 'Error al actualizar gasto'
                if (window.mostrarNotificacion) window.mostrarNotificacion(this.error, 'error')
                throw err
            } finally {
                this.loading = false
            }
        },

        async deleteExpense(id) {
            this.loading = true
            this.error = null
            try {
                const response = await api.delete(`/expenses/destroy/${id}`)
                if (response.data.status === 'true') {
                    this.expenses = this.expenses.filter(e => e.id !== id)
                    return true
                }
            } catch (err) {
                this.error = err.response?.data?.message || 'Error al eliminar gasto'
                if (window.mostrarNotificacion) window.mostrarNotificacion(this.error, 'error')
                throw err
            } finally {
                this.loading = false
            }
        }
    }
})
