# moodle-free_courses

Repositorio de desarrollo para el bloque de Moodle **`block_freecourses`**.

## Objetivo funcional

El bloque muestra únicamente cursos de libre acceso con autoinscripción `self` abierta, sin clave ni barreras adicionales, en una UI mínima:
- barra de búsqueda
- tarjetas fijas
- botón **Inscribirse al Curso**

## Estado del proyecto

| Campo | Valor |
|-------|-------|
| Componente técnico | `block_freecourses` |
| Directorio plugin (raíz) | `block_freecourses/` |
| Directorio plugin (workspace) | `workspace/block_freecourses/` |
| Moodle requerido | 5.1+ (`2025092600`) |
| Versión del plugin | `2026031901` |

## Estructura del repositorio

```
moodle-free_courses/
├── block_freecourses/          # Plugin listo para empaquetar/instalar
├── workspace/block_freecourses/# Copia de trabajo en workspace
├── scripts/                    # Utilidades de empaquetado y verificación
├── docs/                       # Documentación técnica
└── build/                      # ZIPs generados
```

## Quick start

```bash
cd /opt/moodle-dev/moodle-free_courses

# Validar workspace
./scripts/verify_isolation.sh

# Empaquetar ZIP instalable
./scripts/package_workspace.sh
```

## Instalación en Moodle (ZIP)

1. Ejecutar `./scripts/package_workspace.sh`
2. Extraer el ZIP generado en el directorio `blocks/` de Moodle
3. Ejecutar upgrade/purge cache en Moodle

## Documentación

- [PLUGIN_MAP.md](docs/PLUGIN_MAP.md) — Mapa técnico
- [WORKFLOW.md](docs/WORKFLOW.md) — Flujo de desarrollo
