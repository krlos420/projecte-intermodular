import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'
import Dashboard from '../views/Dashboard.vue'
import Register from '../views/Register.vue'
import CreateJoinHouse from '../views/CreateJoinHouse.vue'
import Estadisticas from '../views/Estadisticas.vue'
import ShoppingList from '../views/ShoppingList.vue'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            redirect: '/login'
        },
        {
            path: '/login',
            name: 'Login',
            component: Login
        },
        {
            path: '/dashboard',
            name: 'Dashboard',
            component: Dashboard,
            meta: { requiresAuth: true }
        },
        {
            path: '/register',
            name: 'Register',
            component: Register
        },
        {
            path: '/create-join-house',
            name: 'CreateJoinHouse',
            component: CreateJoinHouse,
            meta: { requiresAuth: true }
        },
        {
            path: '/estadisticas',
            name: 'Estadisticas',
            component: Estadisticas,
            meta: { requiresAuth: true }
        },
        {
            path: '/shopping-list',
            name: 'ShoppingList',
            component: ShoppingList,
            meta: { requiresAuth: true }
        }
    ]
})

// Guard para rutas protegidas
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('token')

    if (to.meta.requiresAuth && !token) {
        next('/login')
    } else {
        next()
    }
})

export default router