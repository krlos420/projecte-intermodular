import { createRouter, createWebHistory } from 'vue-router'

// Lazy Loading de vistas para un bundle inicial más pequeño
const Login = () => import('../views/Login.vue')
const Dashboard = () => import('../views/Dashboard.vue')
const Register = () => import('../views/Register.vue')
const CreateJoinHouse = () => import('../views/CreateJoinHouse.vue')
const Estadisticas = () => import('../views/Estadisticas.vue')
const ShoppingList = () => import('../views/ShoppingList.vue')
const Profile = () => import('../views/Profile.vue')
const MapHouses = () => import('../views/MapHouses.vue')

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
            path: '/map',
            name: 'MapHouses',
            component: MapHouses,
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
        },
        {
            path: '/profile',
            name: 'Profile',
            component: Profile,
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