# Comp-Together 🏠

Aplicación web para la gestión de pisos compartidos. Permite a los inquilinos llevar un control de los gastos del hogar, repartirlos de forma justa y liquidar deudas entre compañeros.

## Autores

- **Arnau Payà**
- **Carlos Mogort**

CFGS Desarrollo de Aplicaciones Web · 2025–2026

---

## Stack tecnológico

**Frontend:** Vue.js 3 (Composition API), Vite, Pinia, Vue Router, Axios, Leaflet.js, Chart.js  
**Backend:** Laravel 11 (PHP 8.2), Laravel Sanctum, Eloquent ORM, MySQL 8  
**Entorno:** Docker + Docker Compose + Nginx

---

## Puesta en marcha

### Backend

```bash
cd Backend
cp src/.env.example src/.env
# Configura las credenciales de la base de datos en .env
docker-compose up -d
docker-compose exec php php artisan key:generate
docker-compose exec php php artisan migrate
```

La API queda disponible en `http://localhost:8000/api`

### Frontend

```bash
cd Frontend
npm install
npm run dev
```

La aplicación queda disponible en `http://localhost:5173`

---

## Funcionalidades principales

- Registro e inicio de sesión con autenticación por token (Sanctum)
- Crear pisos o unirse mediante código de invitación / solicitud desde el mapa
- Gestión de gastos del hogar (registro, reparto automático, liquidación de deudas)
- Lista de la compra compartida en tiempo real
- Mapa interactivo de pisos disponibles (OpenStreetMap + Leaflet.js)
- Estadísticas mensuales con gráficas (Chart.js)
- Roles diferenciados: Administrador e Inquilino

---

## Estructura del proyecto

```
projecte-intermodular/
├── Backend/          # API Laravel (PHP)
│   └── src/
│       ├── app/Http/Controllers/
│       ├── app/Models/
│       ├── database/migrations/
│       └── routes/api.php
└── Frontend/         # SPA Vue.js
    └── src/
        ├── views/
        ├── stores/
        ├── components/
        └── services/
```
