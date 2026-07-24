# BTM Translator Audit & Architectural Document

## Estado del Proyecto
* **Nombre del Plugin:** Break The Mold AI Translator (BTM AI Translator)
* **Autor:** Break The Mold
* **Versión:** 1.0.0
* **Descripción:** Traductor global inteligente español–inglés para WordPress y Elementor.

## Entorno y Versiones
* **WordPress:** 7.0.2
* **PHP:** 8.2.12
* **Database:** MySQL
* **Elementor Builder:** 4.1.5
* **Elementor Pro:** Instalado y activo (pro-elements)

## Estrategia de Integración
* **Autoloading:** PSR-4 manual para evitar dependencias externas pesadas.
* **Base de datos:** 10 tablas personalizadas optimizadas con prefijos y debidamente indexadas.
* **Caché:** Caché de diccionario por página y transitorios persistentes.
* **MutationObserver:** Para capturar y traducir de manera dinámica contenido AJAX y modales.

## Plan de Rollback
* **Procedimiento:** Desactivación y borrado del plugin por la interfaz estándar de WordPress. El script `uninstall.php` elimina todas las opciones (`btmat_*`), transitorios, capacidades de administración del usuario y tablas de base de datos creadas de forma segura.
