# Free courses block (block_freecourses)

A Moodle dashboard block that lists the courses a user can join **for free** — those with an open,
key-less **self-enrolment** method — with a live search box and a category filter.

- **Component:** `block_freecourses`
- **Supported Moodle:** 4.5, 5.0, 5.1, 5.2, 5.3 (LTS)
- **License:** GNU GPL v3 or later
- **Issues:** <https://github.com/rurak-ec/moodle-block_freecourses/issues>

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
already enrolled in are excluded. Each card's **Enrol** button enrols the user in one click and takes
them straight into the course.

Users can search the listed courses (accent- and case-insensitive) and filter them by category.

### High-performance architecture (zero dashboard overhead)
- **Single indexed SQL JOIN:** Scans open self-enrolment courses in a single indexed query instead of $N+1$ table operations.
- **Site-wide MUC caching:** The candidate list and course image URLs are cached in MUC (`candidates`) with a short TTL, eliminating database and file-storage operations on subsequent Dashboard pageviews.
- **In-memory category memoization:** Category name resolution and formatting are cached during render time.
- **Conditional JS loading:** The `block_freecourses/search` AMD module is only registered if there is at least one course to display.
- **CSS containment & GPU rendering:** Uses `contain: layout;`, `contain: layout style;`, and `content-visibility: auto;` to eliminate layout thrashing across the Dashboard.

### Installation
1. Build the ZIP: `./scripts/package_workspace.sh`
2. Install it via **Site administration → Plugins → Install plugins**, or unzip into
   `blocks/freecourses` and run the upgrade.

### Quality & Standards
CI runs official `moodle-plugin-ci` (phplint, phpcs, phpdoc, mustache, grunt, PHPUnit, Behat) on every supported
branch: Moodle 4.5, 5.0, 5.1, 5.2 and 5.3 (LTS), each at its lowest and highest supported PHP (8.1–8.4), on
PostgreSQL and MariaDB, strictly following [moodledev.io](https://moodledev.io) specifications.

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
inscripciones permitidas. Se excluyen los cursos en los que el usuario ya está inscrito. El botón
**Inscribirse** de cada tarjeta inscribe en un solo clic y lleva directamente al curso.

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
