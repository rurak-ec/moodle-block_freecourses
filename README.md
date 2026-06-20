# Free courses block (block_freecourses)

A Moodle dashboard block that lists the courses a user can join **for free** — those with an open,
key-less **self-enrolment** method — with a live search box and a category filter.

- **Component:** `block_freecourses`
- **Supported Moodle:** 5.0 – 5.2
- **License:** GNU GPL v3 or later
- **Issues:** <https://github.com/rurak-ec/moodle-free_courses/issues>

> Development repository. The installable plugin lives in
> [`workspace/block_freecourses`](workspace/block_freecourses) (the canonical source) and is mirrored
> at the repository root in [`block_freecourses`](block_freecourses). See the
> [plugin README](workspace/block_freecourses/README.md) for full details.

---

## English

### What it does
The block shows, as course cards on the Dashboard, every **visible** course that has an **enabled
self-enrolment** instance which is fully open — no enrolment key, no group key, no cohort
restriction, no enrolment dates, no capacity limit, and new enrolments allowed. Courses the user is
already enrolled in are excluded. Each card links to the course's enrolment page ("Enrol").

Users can search the listed courses (accent- and case-insensitive) and filter them by category.

### Highlights for site administrators
- Appears on the **Dashboard** only.
- Stores **no personal data** (Privacy API `null_provider`).
- The expensive "which courses are free" scan is **cached site-wide** (short TTL), so adding the
  block does not scan every course on every page load.

### Installation
1. Build the ZIP: `./scripts/package_workspace.sh`
2. Install it via **Site administration → Plugins → Install plugins**, or unzip into
   `blocks/freecourses` and run the upgrade.

### Quality
CI runs `moodle-plugin-ci` (phpcs, phpdoc, mustache, grunt, PHPUnit, Behat) across PHP 8.2–8.4 and
Moodle 5.0/5.1/5.2 on PostgreSQL and MariaDB.

### Languages
The plugin ships **English only**, per the plugins-directory policy. The Spanish translation is kept
under [`/translations`](translations/) and will be contributed to lang.moodle.org (AMOS) after
approval.

---

## Español

### Qué hace
El bloque muestra, como tarjetas de curso en el Tablero, todos los cursos **visibles** que tienen una
instancia de **autoinscripción habilitada** y totalmente abierta: sin clave de inscripción, sin clave
de grupo, sin restricción por cohorte, sin fechas de inscripción, sin límite de aforo y con nuevas
inscripciones permitidas. Se excluyen los cursos en los que el usuario ya está inscrito. Cada tarjeta
enlaza a la página de inscripción del curso ("Inscribirse").

Las personas usuarias pueden buscar entre los cursos listados (sin distinguir mayúsculas ni acentos) y
filtrarlos por categoría.

### Aspectos para administradores
- Aparece únicamente en el **Tablero (Dashboard)**.
- **No almacena datos personales** (API de Privacidad `null_provider`).
- El escaneo costoso de "qué cursos son gratis" se **cachea a nivel de sitio** (TTL corto), de modo
  que el bloque no recorre todos los cursos en cada carga de página.

### Instalación
1. Generar el ZIP: `./scripts/package_workspace.sh`
2. Instalarlo desde **Administración del sitio → Plugins → Instalar plugins**, o descomprimir en
   `blocks/freecourses` y ejecutar la actualización.

### Idiomas
El plugin se publica **solo en inglés**, según la política del directorio de plugins. La traducción al
español se conserva en [`/translations`](translations/) y se subirá a lang.moodle.org (AMOS) tras la
aprobación.
