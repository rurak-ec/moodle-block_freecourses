# Mapa técnico del plugin `block_freecourses`

Directorio de desarrollo recomendado: `workspace/block_freecourses`

## Objetivo

Bloque mínimo que lista cursos de libre acceso con autoinscripción `self` abierta y sin barreras:
- búsqueda
- tarjetas
- botón **Inscribirse al Curso**

## Archivos principales

| Archivo | Descripción |
|---------|-------------|
| `version.php` | Versión del plugin y componente (`block_freecourses`) |
| `block_freecourses.php` | Clase principal del bloque |
| `classes/output/main.php` | Filtrado de cursos libres + datos para template |
| `classes/output/renderer.php` | Renderer del plugin |
| `templates/main.mustache` | UI final del bloque |
| `db/access.php` | Capability `block/freecourses:myaddinstance` |
| `lang/es/block_freecourses.php` | Strings en español |
| `lang/en/block_freecourses.php` | Strings en inglés |

## Estructura actual

- `block_freecourses/`: plugin listo
- `workspace/block_freecourses/`: copia de workspace
- `docs/`: documentación
- `scripts/`: utilidades

## Notas

No se usa la arquitectura antigua de `myoverview` (filtros, ordenamiento, cambio de vista, progreso, AMD heredado).
