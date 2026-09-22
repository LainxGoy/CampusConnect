# Campus Connect - Guía de Integración API Móvil (Desarrollador B)

Esta guía contiene la documentación de los endpoints RESTful para la aplicación móvil estudiantil y las instrucciones para sincronizar el proyecto sin conflictos en Git.

---

## 1. Conexión de Red hacia el Backend

Cuando el backend se ejecuta con `php artisan serve --host=0.0.0.0 --port=8000`, la URL base depende del entorno de prueba de la app móvil:

| Entorno Móvil | Base URL de la API | Observación |
|---|---|---|
| **Emulador Android** | `http://10.0.2.2:8000/api/v1` | Android mapea `10.0.2.2` a la máquina host. |
| **Simulador iOS** | `http://127.0.0.1:8000/api/v1` | Comparte el localhost de la Mac. |
| **Celular Físico (Wi-Fi)** | `http://<TU_IP_LOCAL>:8000/api/v1` | Misma red Wi-Fi (ej: `192.168.1.50`). |
| **Acceso Remoto (Recomendado)** | `https://xxxx.ngrok-free.app/api/v1` | Con túnel Ngrok (`ngrok http 8000`). |

### Headers Obligatorios en cada petición
```http
Accept: application/json
Content-Type: application/json
```
*(Para subir evidencias con archivos multipart, omitir `Content-Type: application/json` para que la librería móvil genere el boundary multipart automáticamente).*

---

## 2. Catálogo de Endpoints para la Aplicación Móvil

### Módulo A: Catálogo de Recursos (Para Dropdowns / Selectores)
- **Endpoint:** `GET /recursos`
- **Uso:** Llenar el selector de infraestructura o equipos cuando el estudiante va a reportar un problema.
- **Respuesta 200 OK:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "codigo": "AUL-101",
      "nombre": "Aula Magna 101",
      "tipo": "infraestructura",
      "categoria": "Aulas",
      "ubicacion": "Edificio Central, Piso 1",
      "estado": "operativo"
    },
    {
      "id": 3,
      "codigo": "EQ-PROY-01",
      "nombre": "Proyector Láser Epson 4K",
      "tipo": "equipamiento",
      "categoria": "Audiovisual",
      "ubicacion": "Aula Magna 101",
      "estado": "mantenimiento"
    }
  ]
}
```

---

### Módulo B: Registro de Solicitudes
- **Endpoint:** `POST /solicitudes`
- **Uso:** El estudiante envía un nuevo ticket de soporte/infraestructura.
- **Body (JSON):**
```json
{
  "titulo": "Falla en proyector de Aula 101",
  "descripcion": "El equipo parpadea y no detecta el cable HDMI.",
  "tipo": "equipamiento",
  "prioridad": "alta",
  "recurso_id": 3,
  "user_id": 3
}
```
*Valores permitidos:*
- `tipo`: `mantenimiento`, `soporte`, `infraestructura`, `equipamiento`
- `prioridad`: `baja`, `media`, `alta`, `urgente`
- **Respuesta 201 Created:**
```json
{
  "success": true,
  "message": "Solicitud registrada correctamente.",
  "data": {
    "id": 4,
    "codigo_ticket": "TKT-202609-0004",
    "titulo": "Falla en proyector de Aula 101",
    "estado": "pendiente",
    "tipo": "equipamiento",
    "prioridad": "alta"
  }
}
```

---

### Módulo C: Listar Solicitudes del Estudiante
- **Endpoint:** `GET /solicitudes?user_id=3&estado=en_proceso`
- **Query Params opcionales:**
  - `user_id`: ID del estudiante (filtra solicitudes propias).
  - `estado`: `pendiente`, `en_revision`, `en_proceso`, `resuelto`, `cancelado`.
  - `tipo`: `mantenimiento`, `soporte`, `infraestructura`, `equipamiento`.
  - `search`: Búsqueda por palabra clave o código de ticket.
  - `per_page`: Cantidad por página (default 10).
- **Respuesta 200 OK:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "codigo_ticket": "TKT-202609-0001",
        "titulo": "Falla en proyección HDMI en Aula Magna 101",
        "tipo": "equipamiento",
        "prioridad": "alta",
        "estado": "en_proceso",
        "created_at": "2026-09-22T17:44:43.000000Z",
        "recurso": {
          "id": 3,
          "codigo": "EQ-PROY-01",
          "nombre": "Proyector Láser Epson 4K",
          "ubicacion": "Aula Magna 101"
        }
      }
    ],
    "current_page": 1,
    "total": 1
  }
}
```

---

### Módulo D: Gestión de Solicitudes Propias (Editar y Eliminar)

#### 1. Editar solicitud propia (solo si está pendiente)
- **Endpoint:** `PUT /solicitudes/{id}`
- **Body (JSON):**
```json
{
  "titulo": "Falla grave en proyector HDMI Aula 101",
  "descripcion": "Actualizo descripción: Ya no enciende ninguna luz.",
  "prioridad": "urgente"
}
```
- **Respuesta 200 OK:** Objeto actualizado.
- **Respuesta 400 Bad Request:** Si el ticket ya inició atención técnica o está resuelto.

#### 2. Eliminar / Cancelar solicitud propia
- **Endpoint:** `DELETE /solicitudes/{id}`
- **Respuesta 200 OK:**
```json
{
  "success": true,
  "message": "Solicitud TKT-202609-0004 eliminada correctamente."
}
```

---

### Módulo E: Seguimiento y Trazabilidad (Timeline Detallado)
- **Endpoint:** `GET /solicitudes/{id}/seguimiento`
- **Uso:** Pantalla de detalle de ticket donde el estudiante ve la línea de tiempo, fotos adjuntas y comentarios.
- **Respuesta 200 OK:**
```json
{
  "success": true,
  "data": {
    "ticket": {
      "id": 1,
      "codigo_ticket": "TKT-202609-0001",
      "titulo": "Falla en proyección HDMI",
      "estado": "en_proceso",
      "fecha_creacion": "2026-09-22T17:44:43Z",
      "responsable_asignado": {
        "id": 2,
        "name": "Ing. Carlos Mendoza (Soporte TI)",
        "email": "tecnico@campusconnect.edu"
      }
    },
    "trazabilidad": [
      {
        "id": 1,
        "estado_anterior": null,
        "estado_nuevo": "pendiente",
        "nota": "Solicitud registrada desde la aplicación móvil.",
        "usuario": "Ana Morales Gómez",
        "tiempo_relativo": "hace 2 horas"
      },
      {
        "id": 2,
        "estado_anterior": "pendiente",
        "estado_nuevo": "en_proceso",
        "nota": "Ticket asignado al técnico de turno.",
        "usuario": "Ing. Carlos Mendoza (Soporte TI)",
        "tiempo_relativo": "hace 1 hora"
      }
    ],
    "evidencias": [
      {
        "id": 1,
        "nombre": "foto_conector.jpg",
        "url": "http://127.0.0.1:8000/storage/evidencias/foto_conector.jpg",
        "mime_type": "image/jpeg",
        "tamanio": "1.20 MB"
      }
    ],
    "comentarios": [
      {
        "id": 1,
        "autor": "Ing. Carlos Mendoza (Soporte TI)",
        "mensaje": "Ya solicitamos el repuesto del cable.",
        "tiempo_relativo": "hace 45 minutos"
      }
    ]
  }
}
```

---

### Módulo F: Actualización de Estados y Asignación Técnica
- **Endpoint:** `PATCH /solicitudes/{id}/estado`
- **Uso:** Usado por el perfil técnico/administrativo desde la app para avanzar el ticket.
- **Body (JSON):**
```json
{
  "estado": "resuelto",
  "responsable_id": 2,
  "nota": "Se reemplazó el puerto HDMI. Operatividad al 100%.",
  "user_id": 2
}
```
- **Respuesta 200 OK:** Estado actualizado y guardado en la bitácora de auditoría.

---

### Módulo G: Subir Evidencias y Generar Reportes

#### 1. Subir Evidencia Multimedia (Cámara / Galería)
- **Endpoint:** `POST /solicitudes/{id}/evidencias`
- **Content-Type:** `multipart/form-data`
- **Form-Data:**
  - `archivo`: Archivo binario (JPG, PNG, PDF, MP4 hasta 15MB).
  - `user_id`: ID del usuario que sube el archivo.
- **Respuesta 201 Created:**
```json
{
  "success": true,
  "message": "Evidencia adjuntada exitosamente a la solicitud.",
  "data": {
    "id": 5,
    "nombre_original": "evidencia_daño.jpg",
    "url": "http://127.0.0.1:8000/storage/evidencias/hash_nombre.jpg",
    "tamanio": "840.50 KB"
  }
}
```

#### 2. Reporte y Resumen Estadístico para Móvil
- **Endpoint:** `GET /solicitudes/reportes/resumen?user_id=3`
- **Uso:** Vista de tarjetas y resumen gráfico en la app móvil.
- **Respuesta 200 OK:**
```json
{
  "success": true,
  "message": "Reporte consolidado para la aplicación móvil.",
  "data": {
    "resumen": {
      "total": 5,
      "pendientes": 1,
      "en_proceso": 2,
      "resueltas": 2
    },
    "distribucion_por_tipo": {
      "equipamiento": 3,
      "mantenimiento": 1,
      "infraestructura": 1
    },
    "recientes": [...]
  }
}
```

---

## 3. Ejemplo de Integración en Flutter (Dart)

Puedes utilizar este servicio base en tu proyecto de Flutter (`lib/services/api_service.dart`):

```dart
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;

class ApiService {
  // Ajustar según el entorno (Android Emulator usa 10.0.2.2, iOS usa localhost, Físico usa IP local)
  static final String baseUrl = Platform.isAndroid 
      ? 'http://10.0.2.2:8000/api/v1' 
      : 'http://127.0.0.1:8000/api/v1';

  // 1. Obtener catálogo de recursos
  static Future<List<dynamic>> getRecursos() async {
    final response = await http.get(
      Uri.parse('$baseUrl/recursos'),
      headers: {'Accept': 'application/json'},
    );
    if (response.statusCode == 200) {
      final json = jsonDecode(response.body);
      return json['data'];
    }
    throw Exception('Error al cargar recursos');
  }

  // 2. Crear nueva solicitud estudiantil
  static Future<Map<String, dynamic>> crearSolicitud({
    required String titulo,
    required String descripcion,
    required String tipo,
    required String prioridad,
    int? recursoId,
    required int userId,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/solicitudes'),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({
        'titulo': titulo,
        'descripcion': descripcion,
        'tipo': tipo,
        'prioridad': prioridad,
        'recurso_id': recursoId,
        'user_id': userId,
      }),
    );
    return jsonDecode(response.body);
  }

  // 3. Consultar seguimiento y trazabilidad del ticket
  static Future<Map<String, dynamic>> getSeguimiento(int solicitudId) async {
    final response = await http.get(
      Uri.parse('$baseUrl/solicitudes/$solicitudId/seguimiento'),
      headers: {'Accept': 'application/json'},
    );
    return jsonDecode(response.body);
  }

  // 4. Subir fotografía o evidencia desde la cámara/galería
  static Future<bool> subirEvidencia({
    required int solicitudId,
    required File imageFile,
    required int userId,
  }) async {
    final request = http.MultipartRequest(
      'POST',
      Uri.parse('$baseUrl/solicitudes/$solicitudId/evidencias'),
    );
    request.headers['Accept'] = 'application/json';
    request.fields['user_id'] = userId.toString();
    request.files.add(await http.MultipartFile.fromPath('archivo', imageFile.path));

    final streamedResponse = await request.send();
    return streamedResponse.statusCode == 201;
  }
}
```

