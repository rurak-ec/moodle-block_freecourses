# Flujo de desarrollo

## 1) Validar estructura del workspace

```bash
cd /opt/moodle-dev/moodle-block_myoverview
./scripts/verify_isolation.sh
```

## 2) Editar el plugin

Trabajar en: `workspace/myoverview`

Archivos editados frecuentemente:
- `workspace/myoverview/block_myoverview.php` — clase principal del bloque
- `workspace/myoverview/lib.php` — funciones de librería y constantes
- `workspace/myoverview/classes/output/main.php` — lógica de renderizado
- `workspace/myoverview/settings.php` — configuración del admin
- `workspace/myoverview/version.php` — versión del plugin (actualizar en cada release)
- `workspace/myoverview/amd/src/*.js` — módulos JavaScript

### Desarrollo con symlink (recomendado)

Para desarrollo activo contra una instancia de Moodle, crear un symlink en vez de copiar:

```bash
ln -s /opt/moodle-dev/moodle-block_myoverview/workspace/myoverview {MOODLE_ROOT}/blocks/myoverview
```

Esto permite ver los cambios en tiempo real sin necesidad de empaquetar.

### Recompilar JavaScript AMD

Si se modifican archivos en `amd/src/`, regenerar los builds:

```bash
cd {MOODLE_ROOT}
npx grunt amd --root=blocks/myoverview
```

## 3) Empaquetar ZIP para instalación

```bash
./scripts/package_workspace.sh
```

Salida: `build/myoverview_YYYYMMDD_HHMMSS.zip`

## 4) Instalar/probar en Moodle

Extraer el ZIP en el directorio `blocks/` de Moodle:

```bash
unzip build/myoverview_*.zip -d {MOODLE_ROOT}/blocks/
```

Luego ejecutar:

```bash
php admin/cli/upgrade.php --non-interactive
php admin/cli/purge_caches.php
```

Verificar en: Administración del sitio > Plugins > Bloques.

## 5) Publicar cambios

```bash
git add .
git commit -m "feat: descripción de los cambios"
git push
```
