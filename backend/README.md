# Backend API REST - "CAMPUS CONNECT"

API REST desarrollada con **Laravel 12**, **Laravel Sanctum** y base de datos relacional para el soporte del módulo móvil universitario.

---

## 🚀 Requisitos Previos
- PHP 8.2 o superior (con extensiones `pdo`, `mbstring`, `openssl`, `fileinfo`)
- Composer 2.x
- Base de Datos: PostgreSQL o MySQL

---

## ⚙️ Instalación y Configuración

1. **Instalar dependencias:**
   ```bash
   composer install
   ```

2. **Copiar archivo de entorno y generar clave de aplicación:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Ejecutar migraciones y seeders:**
   ```bash
   php artisan migrate
   ```

4. **Crear enlace simbólico de almacenamiento para evidencias públicas:**
   ```bash
   php artisan storage:link
   ```

5. **Iniciar el servidor local:**
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```

---

## 🧪 Ejecución de Pruebas Automatizadas

Para ejecutar la suite de 5 pruebas obligatorias de integración y reglas de negocio:
```bash
php artisan test --filter=CampusConnectApiTest
```

---

## 📌 Endpoints Principales

| Método | Endpoint | Descripción | Autenticación |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/v1/auth/login` | Login de estudiantes y retorno de token | Pública |
| `POST` | `/api/v1/auth/logout` | Revocación de token actual | Bearer Token |
| `GET` | `/api/v1/auth/profile` | Datos del perfil de estudiante | Bearer Token |
| `GET` | `/api/v1/solicitudes` | Listado paginado con filtros por estado | Bearer Token |
| `POST` | `/api/v1/solicitudes` | Crear solicitud con evidencia multipart | Bearer Token |
| `GET` | `/api/v1/solicitudes/{id}` | Detalle completo de solicitud | Bearer Token |
| `PUT` | `/api/v1/solicitudes/{id}` | Editar solicitud (solo 'Pendiente') | Bearer Token |
| `DELETE` | `/api/v1/solicitudes/{id}` | Cancelar solicitud (solo 'Pendiente') | Bearer Token |
| `POST` | `/api/v1/solicitudes/{id}/evidencias` | Adjuntar evidencia complementaria | Bearer Token |
| `GET` | `/api/v1/solicitudes/{id}/seguimiento` | Historial y trazabilidad de estados | Bearer Token |
| `GET` | `/api/v1/solicitudes/{id}/comentarios` | Listar comentarios y respuestas | Bearer Token |
| `POST` | `/api/v1/solicitudes/{id}/comentarios` | Registrar nuevo comentario | Bearer Token |
