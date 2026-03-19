# moodle-block_myoverview

Workspace de desarrollo para el plugin de bloque de Moodle **`block_myoverview`** (Course overview / My overview).

## Estado del proyecto

| Campo | Valor |
|-------|-------|
| Componente técnico | `block_myoverview` |
| Directorio fuente | `workspace/myoverview` |
| Moodle requerido | 5.1+ (2025092600) |
| Versión del plugin | 2025100600 |

## Estructura del repositorio

```
moodle-block_myoverview/
├── workspace/myoverview/   # Código fuente del plugin (desarrollo activo)
├── scripts/                # Utilidades de empaquetado y verificación
├── docs/                   # Documentación técnica
├── build/                  # ZIPs generados (no es código fuente)
└── reference/              # Copia original sin modificar (gitignored)
```

## Quick start

```bash
# Validar workspace
./scripts/verify_isolation.sh

# Editar el código fuente en workspace/myoverview/

# Empaquetar ZIP instalable
./scripts/package_workspace.sh
```

## Instalación en Moodle

### Opción A: Desde ZIP

1. Ejecutar `./scripts/package_workspace.sh`
2. Extraer `build/myoverview_*.zip` en `{MOODLE_ROOT}/blocks/`
3. Ejecutar:
   ```bash
   php admin/cli/upgrade.php --non-interactive
   php admin/cli/purge_caches.php
   ```

### Opción B: Symlink (desarrollo)

```bash
ln -s /opt/moodle-dev/moodle-block_myoverview/workspace/myoverview {MOODLE_ROOT}/blocks/myoverview
php admin/cli/purge_caches.php
```

## Documentación

- [PLUGIN_MAP.md](docs/PLUGIN_MAP.md) — Mapa técnico del plugin
- [WORKFLOW.md](docs/WORKFLOW.md) — Flujo de desarrollo
