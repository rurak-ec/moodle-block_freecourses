# Free courses (block_freecourses)

A Moodle **block** that lists courses open for free self-enrolment, with search and category
filtering. Designed for the user **Dashboard**.

- **Component:** `block_freecourses`
- **Type:** Block
- **Supported Moodle:** 4.5, 5.0, 5.1, 5.2 (`$plugin->supported = [405, 502]`)
- **Maturity:** Stable · **Release:** 1.1.0
- **License:** GNU GPL v3 or later

> Bootstrap-dependent markup (screen-reader class, dropdown toggle, menu alignment) is chosen at
> render time: Bootstrap 4 names on Moodle 4.5, Bootstrap 5 names on Moodle 5.0+.
> El marcado que depende de Bootstrap se elige al renderizar: nombres de Bootstrap 4 en Moodle 4.5 y
> de Bootstrap 5 en Moodle 5.0+.

---

## English

### What it does
For the logged-in user, the block renders course cards for every **visible** course that has an
**enabled, fully open self-enrolment** instance. A self-enrolment instance counts as "free and open"
only when **all** of these hold:

| Condition | Self-enrolment field |
|-----------|----------------------|
| No enrolment key | `password` empty |
| No group enrolment key | `customint1` empty |
| Not restricted to a cohort | `customint5` empty |
| No enrolment start/end dates | `enrolstartdate` / `enrolenddate` empty |
| No capacity limit | `customint3` empty |
| New enrolments allowed | `customint6` set |

Courses the user is already enrolled in are excluded, and Moodle's own `can_self_enrol()` check is
applied per user.

Each card has an **Enrol** button that posts to `blocks/freecourses/enrol.php`. It enrols the user in
one click through the self-enrolment plugin's own API and then takes them **straight into the
course**. Everything is checked again against the database first. If the course is no longer freely
open (for example, a key was added), the user is sent to Moodle's enrolment page instead, which
explains why.

### How it is built
- **Output:** `\block_freecourses\output\main` (renderable + templatable) →
  `templates/main.mustache`, rendered by `\block_freecourses\output\renderer`.
- **Enrolment:** `\block_freecourses\local\enrolment` holds the "is this course free?" rules (shared
  by the listing and by `enrol.php`) and the one-click enrolment. The core enrolment page is not used
  directly for two reasons:
  - on Moodle 4.5 it asks for a second click;
  - after enrolling it returns to `$SESSION->wantsurl`, which OAuth2 logins leave pointing at the
    Dashboard.
- **JavaScript:** the search/filter behaviour is an AMD module, `amd/src/search.js`
  (built to `amd/build/`), wired from the block via `js_call_amd('block_freecourses/search', 'init')`.
  There is no inline JavaScript in the template.
- **Performance:** the user-independent scan of "which courses are free" is cached in an application
  cache (`db/caches.php`, definition `candidates`, short TTL). Per-user filtering (already enrolled /
  can self enrol) runs at render time.
- **Capabilities:** `block/freecourses:myaddinstance` (add to Dashboard).
- **Privacy:** `null_provider` — no personal data stored.

### Installation
Copy this folder to `blocks/freecourses` in your Moodle and complete the upgrade, or install the ZIP
produced by `scripts/package_workspace.sh` via **Site administration → Plugins → Install plugins**.

### Quality
Run the moodle-plugin-ci checks: `phpcs --max-warnings 0`, `phpdoc --max-warnings 0`, `mustache`,
`grunt --max-lint-warnings 0`, `phpunit`, `behat --profile chrome`.

### Languages
Ships **English only**. The Spanish translation is kept in the repository under `/translations` and
will be submitted to lang.moodle.org (AMOS) after approval.

---

## Español

### Qué hace
Para la persona usuaria autenticada, el bloque muestra tarjetas de curso para cada curso **visible**
que tenga una instancia de **autoinscripción habilitada y totalmente abierta**. Una instancia cuenta
como "gratuita y abierta" solo cuando se cumplen **todas** estas condiciones: sin clave de
inscripción (`password`), sin clave de grupo (`customint1`), sin restricción de cohorte
(`customint5`), sin fechas de inscripción (`enrolstartdate`/`enrolenddate`), sin límite de aforo
(`customint3`) y con nuevas inscripciones permitidas (`customint6`).

Se excluyen los cursos en los que la persona ya está inscrita y se aplica la comprobación
`can_self_enrol()` de Moodle por usuario.

Cada tarjeta tiene un botón **Inscribirse** que envía a `blocks/freecourses/enrol.php`. Ese script
inscribe en un solo clic con la API del propio método de autoinscripción y **lleva directamente al
curso**. Antes vuelve a comprobarlo todo contra la base de datos. Si el curso ya no está libremente
abierto (por ejemplo, se le puso clave), envía a la página de inscripción de Moodle, que explica el
motivo.

### Cómo está construido
- **Salida:** `\block_freecourses\output\main` (renderable + templatable) →
  `templates/main.mustache`, renderizado por `\block_freecourses\output\renderer`.
- **Inscripción:** `\block_freecourses\local\enrolment` contiene las reglas de "¿es gratis este
  curso?" (compartidas por el listado y por `enrol.php`) y la inscripción en un clic. No se usa
  directamente la página de inscripción de core por dos motivos:
  - en Moodle 4.5 pide un segundo clic;
  - después de inscribir vuelve a `$SESSION->wantsurl`, que el inicio de sesión OAuth2 deja apuntando
    al Tablero.
- **JavaScript:** la búsqueda/filtrado es un módulo AMD, `amd/src/search.js` (compilado a
  `amd/build/`), invocado desde el bloque con `js_call_amd('block_freecourses/search', 'init')`. No
  hay JavaScript en línea en la plantilla.
- **Rendimiento:** el escaneo (independiente del usuario) de "qué cursos son gratis" se cachea en una
  caché de aplicación (`db/caches.php`, definición `candidates`, TTL corto). El filtrado por usuario
  (ya inscrito / puede autoinscribirse) se ejecuta al renderizar.
- **Capacidades:** `block/freecourses:myaddinstance` (añadir al Tablero).
- **Privacidad:** `null_provider` — no almacena datos personales.

### Instalación
Copia esta carpeta a `blocks/freecourses` en tu Moodle y completa la actualización, o instala el ZIP
generado por `scripts/package_workspace.sh` desde **Administración del sitio → Plugins → Instalar
plugins**.

### Idiomas
Se publica **solo en inglés**. La traducción al español se conserva en el repositorio bajo
`/translations` y se enviará a lang.moodle.org (AMOS) tras la aprobación.
