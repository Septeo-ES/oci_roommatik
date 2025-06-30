# Roommatik OCI API Module

Este módulo es una API REST PHP independiente para la integración con Roommatik OCI. Está diseñado para funcionar como un submódulo dentro de un proyecto tud-checkin, sin interferir con la web principal ni su `public/`.

## Estructura recomendada

```
proyecto-principal/
  public/           # Web principal
    api/
      api-roommatik/    # Módulo REST de Roommatik (independiente)
        public/
        src/
        vendor/
        ...
```

## Instalación y despliegue

1. **Ubica el módulo**
   - Copia todo el contenido de la carpeta `api-roommatik` dentro de tu proyecto principal dentro de api/api-roommatik.

2. **Dependencias**
   - Incluye la carpeta `vendor/` generada por Composer. No necesitas Composer en el servidor.

3. **Configuración**
   - Crea y edita el archivo `.env` en `api-roommatik/` según tu entorno (`.env.development` o `.env.production`).
   - Configura la base de datos y las claves API en el `.env`.

4. **Acceso a la API**
   - Accede a la API por la ruta:
     - `https://tudominio.com/api/api-roommatik/public/index.php/api/v1/health`
   - El `.htaccess` de `public/` permite URLs limpias si el servidor lo soporta.

5. **Base de datos**
   - El módulo crea automáticamente las tablas necesarias al primer acceso si no existen (`logs_api`, `reserva_localizador`, ...).

6. **Autenticación**
   - Todas las peticiones protegidas requieren la cabecera:
     - `Authorization: <tu_clave_api>`
   - La clave se define en el `.env` como `ROOMMATIK_INCOMING_API_KEY`.

7. **Logs**
   - Todas las llamadas (exitosas o fallidas) quedan registradas en la tabla `logs_api`.

## Endpoints públicos (PreCheckin)

- **GET /api/v1/health**: Healthcheck del módulo.
- **GET /api/v1/reservation?bookingCode=...&hotel_id=...**: Devuelve la reserva y acompañantes a partir de bookingCode (id_reserva) y hotel_id (idCamping).
- **POST /api/v1/newguest**: Añade un acompañante a una reserva. Body: `{ reservationId, guest }`.
- **GET /api/v1/reservationsbydate?hotel_id=...&dateFrom=YYYYMMDD&dateTo=YYYYMMDD**: Devuelve reservas en rango de fechas.

## Endpoints privados (Webhooks Roommatik)

Todos requieren autenticación por token en cabecera `Authorization` (ver .env: `ROOMMATIK_OUTCOMING_API_KEY`).

- **GET /api/v1/webhook/health**: Healthcheck contra la API externa Roommatik.
- **POST /api/v1/webhook/booking**: Envía reserva a Roommatik (alta o actualización). Body: `{ localizador, campingId }`.
- **PUT /api/v1/webhook/booking**: Igual que POST.
- **PATCH /api/v1/webhook/booking**: Actualiza reserva en Roommatik. Body: `{ localizador, campingId }`. Busca el reservationCode (id_reserva) y lo usa en la URL `/api/v1/Booking/{reservationCode}`.
- **DELETE /api/v1/webhook/booking**: Elimina reserva en Roommatik. Body: `{ localizador, campingId }`. Busca id_reserva y reserva, y llama a `/api/v1/Booking/{id_reserva}/{reserva}`.

## Seguridad en endpoints internos (webhook)

Los endpoints bajo `/api/v1/webhook/*` están protegidos mediante autenticación por token. Para acceder a estos endpoints debes enviar la cabecera:

    Authorization: <token configurado en .env>

Si el token no es correcto o falta, la API responderá con 401 Unauthorized.

> **Nota:** Esta protección permite exponer endpoints internos solo a servicios autorizados de la compañía, no a terceros ni al público general.

## Actualizaciones
- Para actualizar el módulo, reemplaza los archivos de `api-roommatik/` excepto tu `.env` y la base de datos.

## Notas
- No es necesario modificar el `public/` ni el autoload del proyecto principal.
- El módulo es totalmente autónomo y puede convivir con otros sistemas PHP.

---

**Desarrollado por SEPTEO Hospitality Solutions SP s.l.u.**
