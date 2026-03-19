# Flujo de desarrollo (`block_freecourses`)

## 1) Validar estructura del workspace

```bash
cd /opt/moodle-dev/moodle-free_courses
./scripts/verify_isolation.sh
```

## 2) Editar el plugin

Trabajar en: `workspace/block_freecourses`

Archivos clave:
- `workspace/block_freecourses/block_freecourses.php` — clase principal
- `workspace/block_freecourses/classes/output/main.php` — lógica de cursos libres
- `workspace/block_freecourses/templates/main.mustache` — búsqueda + tarjetas + botón
- `workspace/block_freecourses/db/access.php` — capability
- `workspace/block_freecourses/version.php` — versión/componente

## 3) Empaquetar ZIP

```bash
./scripts/package_workspace.sh
```

Salida: `build/block_freecourses_YYYYMMDD_HHMMSS.zip`

## 4) Instalar/probar en Moodle

Extraer ZIP en `blocks/`, ejecutar upgrade y purge caches.

Validar en Dashboard:
- solo cursos `self` abiertos sin barreras
- búsqueda funcionando
- tarjetas fijas
- botón **Inscribirse al Curso**

## 5) Publicar cambios

```bash
git add .
git commit -m "feat: ..."
git push
```
