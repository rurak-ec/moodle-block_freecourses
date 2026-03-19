# Mapa técnico del plugin block_myoverview

Directorio de desarrollo: `workspace/myoverview`

## Archivos principales

| Archivo | Descripción |
|---------|-------------|
| `version.php` | Versión del plugin y componente (`block_myoverview`, v2025100600, Moodle 5.1+) |
| `block_myoverview.php` | Clase principal del bloque (`block_myoverview extends block_base`) |
| `lib.php` | Funciones de librería, constantes (agrupación, orden, vista, paginación), preferencias de usuario |
| `settings.php` | Página de configuración del administrador |
| `styles.css` | Estilos CSS del bloque |

## Clases PHP (namespace `block_myoverview\`)

| Clase | Archivo | Descripción |
|-------|---------|-------------|
| `output\main` | `classes/output/main.php` | Clase renderable/templatable - lógica de renderizado |
| `output\renderer` | `classes/output/renderer.php` | Renderer del plugin |
| `privacy\provider` | `classes/privacy/provider.php` | Implementación de Privacy API |

## Módulos AMD JavaScript (`amd/src/`)

| Módulo | Descripción |
|--------|-------------|
| `main.js` | Punto de entrada; inicializa ViewNav y View |
| `view.js` | Renderizado de cursos (tarjetas, lista, resumen) |
| `view_nav.js` | Controles de navegación/filtrado |
| `repository.js` | Llamadas AJAX a servicios web de Moodle |
| `selectors.js` | Constantes de selectores DOM |

Los archivos compilados están en `amd/build/` (`.min.js` + `.min.js.map`).

## Templates Mustache (`templates/`)

14 plantillas: main, zero-state, courses-view, view-cards, view-list, view-summary, nav-grouping-selector, nav-sort-selector, nav-display-selector, nav-search-widget, course-action-menu, progress-bar, placeholders, placeholder-course-list-item.

## Base de datos (`db/`)

| Archivo | Descripción |
|---------|-------------|
| `access.php` | Capability: `block/myoverview:myaddinstance` |
| `upgrade.php` | Pasos de actualización de BD |

## Tests

| Archivo | Tipo |
|---------|------|
| `tests/myoverview_test.php` | PHPUnit |
| `tests/privacy/provider_test.php` | PHPUnit (Privacy) |
| `tests/behat/*.feature` (10 archivos) | Behat BDD |

## Reglas de diseño

- No modificar `reference/myoverview`; usarlo solo como línea base de comparación.
- Implementar todos los cambios en `workspace/myoverview`.
- El nombre interno del componente es `block_myoverview` pero la carpeta de instalación es `myoverview` (dentro de `blocks/` de Moodle).
