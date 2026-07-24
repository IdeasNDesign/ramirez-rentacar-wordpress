# Auditoría de Integración PayPal - Ramirez Rent A Car

Este documento presenta la auditoría técnica detallada (Fase 1) para la integración de la pasarela de pago PayPal con el sistema de reservas existente de Ramírez Rent A Car.

---

## 1. Plugin a Extender
El plugin de reservas existente e identificado en el sistema es **Ramirez Rent A Car Manager** (`wp-content/plugins/ramirez-rent-a-car-manager/`). 

El nuevo plugin **Ramirez PayPal Booking Gateway** (`wp-content/plugins/ramirez-paypal-booking-gateway/`) actuará como una pasarela especializada e independiente sin WooCommerce, desacoplada mediante adaptadores, pero interactuando de forma segura con la base de datos y los servicios del plugin core.

---

## 2. Tablas Existentes Relacionadas
El plugin core de reservas ya define e instala las siguientes tablas (con prefijo dinámico `$wpdb->prefix . 'rrc_'`):

1. **`rrc_reservations`**: Tabla principal de reservas. Contiene columnas específicas para el depósito del 10%:
   - `deposit_type` (por defecto 'percentage')
   - `deposit_percentage` (por defecto 10.00)
   - `deposit_amount` (monto calculado del depósito)
   - `deposit_paid_amount` (monto del depósito realmente pagado)
   - `remaining_balance` (saldo pendiente)
   - `payment_status` (estado del pago)
   - `reservation_status` (estado de la reserva)
2. **`rrc_payments`**: Registro individual de intentos de transacciones y capturas.
   - `provider_order_id` (PayPal Order ID)
   - `provider_capture_id` (PayPal Capture ID)
   - `status` (APPROVED, COMPLETED, etc.)
   - `expected_amount` y `amount` (cálculo en centavos/decimales)
3. **`rrc_refunds`**: Registro de reembolsos (totales o parciales).
4. **`rrc_webhook_events`**: Registro e idempotencia de webhooks de PayPal.
5. **`rrc_unit_day_locks`**: Registro de bloqueos diarios por unidad física para prevenir sobre-reservas (overbooking).

---

## 3. Cómo se Calcula Hoy el Total
- **Cálculo del Total**: Se extrae directamente desde el servidor leyendo la reserva cargada por su token público (`$res->total_amount`).
- **Cálculo del Depósito**: Se realiza de manera exclusiva en el servidor aplicando la fórmula:
  ```php
  $total_amount   = (float) $res->total_amount;
  $deposit_amount = round( $total_amount * 0.10, 2 );
  ```
  El cliente no puede alterar ni enviar montos desde el cliente/navegador; el frontend solo transmite el identificador o token seguro de la reserva.

---

## 4. Dónde se Guardará el Depósito del 10%
El depósito del 10% y el desglose financiero se guardarán en las siguientes tablas:
- **`rrc_reservations`**: Se actualizarán las columnas `deposit_paid_amount`, `amount_paid`, y se reajustará el `remaining_balance` (restando el depósito pagado al total original).
- **`rrc_payments`**: Se insertará un registro con estado `ORDER_CREATED` al crear la orden y se actualizará a `COMPLETED` tras la captura exitosa.

---

## 5. Cómo se Bloqueará el Vehículo
Cuando un pago es capturado y verificado de forma segura (status `COMPLETED`):
- Se consulta la unidad asignada a la reserva (`assigned_unit_id` en `rrc_reservations`).
- Se insertan o consolidan los bloqueos por día en la tabla `rrc_unit_day_locks` para el rango de fechas `pickup_at` y `return_at`, evitando que la unidad física quede disponible para otras cotizaciones durante ese intervalo.

---

## 6. Cómo se Notificará al Cliente
El plugin utilizará la infraestructura de correo existente:
- **Servicio**: `\RamirezRentACar\Infrastructure\Notifications\EmailNotificationService::send_reservation_confirmation( $reservation_id )`.
- **Estructura del Mensaje**:
  - Referencia de la reserva
  - Desglose detallado: Total Alquiler, Depósito Cobrado (10%), Saldo Pendiente al Recibir (90%)
  - Fechas y lugares de entrega/devolución.

---

## 7. Cómo se Notificará a la App
La aplicación externa de operaciones (`operations-app/index.html`) lee las reservas consumiendo el endpoint `/wp-json/ramirez-rent-a-car/v1/app/reservations`. 
Al actualizar el estado de la reserva a `DEPOSIT_PAID` o `CONFIRMED` y el estado de pago a `COMPLETED` en la base de datos, la interfaz Kanban de la app de operaciones reflejará instantáneamente el cambio de columna y los importes actualizados en tiempo real.

---

## 8. Qué Rutas REST se Crearán
Se implementará el namespace `ramirez-paypal/v1` con los siguientes endpoints controlados:
- **Públicos**:
  - `POST /reservations/{token}/order`: Genera la orden en PayPal usando la Orders API v2.
  - `POST /reservations/{token}/capture`: Captura el pago tras la aprobación del cliente.
  - `GET /reservations/{token}/status`: Consulta el estado de pago actual de la reserva.
  - `POST /webhook`: Recibe los eventos de PayPal con verificación de firmas obligatoria.
- **Administrativos (con control de permisos)**:
  - `GET /admin/payments`: Lista de pagos y filtros.
  - `POST /admin/payments/{id}/refund`: Ejecución de reembolso total o parcial.
  - `GET /admin/health`: Estado de conexiones, credenciales y webhooks.

---

## 9. Qué Archivos se Modificarán
### Archivos Nuevos (dentro del nuevo plugin)
- `wp-content/plugins/ramirez-paypal-booking-gateway/ramirez-paypal-booking-gateway.php`
- `wp-content/plugins/ramirez-paypal-booking-gateway/uninstall.php`
- `wp-content/plugins/ramirez-paypal-booking-gateway/includes/` (Estructura modular completa de clases especificadas en la arquitectura)
- `wp-content/plugins/ramirez-paypal-booking-gateway/templates/` (Plantillas de correos y pasarela de checkout)
- `wp-content/plugins/ramirez-paypal-booking-gateway/assets/` (Estilos y scripts JS del SDK v6 y wizard)

### Archivos a Modificar (Plugin Core o Configuración)
- No se realizarán modificaciones destructivas en el plugin core `ramirez-rent-a-car-manager`. Se extenderá la funcionalidad mediante hooks de WordPress o endpoints alternativos de pasarelas. Si es necesario, se ajustarán filtros de anulación/prioridad de REST.

---

## 10. Qué Pruebas Sandbox se Ejecutarán
1. **Creación de Orden**: Validar que una reserva de $800 genere una orden en PayPal de exactamente $80 USD (10%).
2. **Flujo de Captura**: Aprobar el pago en Sandbox con cuenta personal de prueba y verificar la captura exitosa desde el servidor.
3. **Idempotencia**: Reintentar la captura de una orden ya procesada y asegurar que retorne el estado existente sin duplicar cobros.
4. **Verificación de Webhooks**: Simular eventos `PAYMENT.CAPTURE.COMPLETED` con firmas válidas e inválidas.
5. **Reembolsos**: Realizar un reembolso Sandbox parcial y verificar que se actualice `amount_refunded` correctamente.

---

## 11. Qué Riesgos Existen
- **Doble Procesamiento (Concurrencia)**: Posibilidad de que la captura del checkout en el frontend y el webhook de PayPal ocurran simultáneamente, causando duplicación de confirmaciones. Se solucionará con bloqueos de concurrencia e idempotencia a nivel de base de datos.
- **Falta de Credenciales en Producción**: Activación accidental del entorno Live sin verificación previa. Se requerirá una confirmación administrativa explícita y pruebas obligatorias superadas en Sandbox.
- **Exposición de Credenciales**: Fuga del Client Secret en llamadas de red o repositorios Git. Se implementará un Credentials Provider que de prioridad a variables de entorno del sistema (`PAYPAL_CLIENT_SECRET`) y cifre los datos si se almacenan en la base de datos de WordPress.

---

## 12. Cómo se Realizará el Rollback
1. **Desactivación del Plugin**: Desactivar `ramirez-paypal-booking-gateway` desde el administrador de WordPress. Las reservas volverán a su flujo estándar sin procesamiento de pasarela en línea.
2. **Restauración de API**: Los endpoints REST de `ramirez-rent-a-car-manager` volverán a tener prioridad automática.
3. **Integridad de Datos**: Ningún registro histórico de reservas o pagos existentes será eliminado o modificado. Las tablas nativas permanecen intactas.
