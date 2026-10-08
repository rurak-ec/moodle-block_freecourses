# Changelog

All notable changes to **block_freecourses** (Free courses) are documented here.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.2] - 2026-10-08

### Added
- Support for **Moodle 5.3 (LTS)** (`$plugin->supported = [405, 503]`).
- CI matrix covering `MOODLE_503_STABLE` on PHP 8.3 (pgsql) and PHP 8.4 (mariadb).
- High-performance CSS containment (`contain: layout style;`, `content-visibility: auto;`) and `touch-action: manipulation;` for fluid 60fps scrolling and instant touch response.
- In-memory category formatting memoization map.

### Changed & Optimized
- **Zero-impact Dashboard loading**: Replaced $O(N)$ candidate queries (`get_courses()` + $N \times$ `enrol_get_instances()`) with a single indexed SQL JOIN query between `{course}` and `{enrol}`.
- Pre-computed course images during MUC candidate caching, completely avoiding filesystem/storage calls on user dashboard visits.
- Conditional AMD module loading: `block_freecourses/search` is only loaded and initialized when the user actually has courses to display and filter.

## [1.1.0] - 2026-10-05

### Added
- Support for **Moodle 4.5**. The plugin now supports Moodle 4.5, 5.0, 5.1 and 5.2
  (`$plugin->supported = [405, 502]`).
- One-click enrolment endpoint `blocks/freecourses/enrol.php` and the shared helper
  `\block_freecourses\local\enrolment`.
- PHPUnit tests for the enrolment helper and for the per-branch markup. A Behat scenario checks that
  enrolling lands the user in the course.
- CI matrix covering `MOODLE_405_STABLE`, `MOODLE_500_STABLE`, `MOODLE_501_STABLE` and
  `MOODLE_502_STABLE`, each on its lowest and highest supported PHP.

### Changed
- The **Enrol** button is now a POST form (sesskey in the body, no longer in the URL). After enrolling,
  it lands the user **inside the course**.
- Bootstrap-dependent markup (`sr-only`/`visually-hidden`, `data-toggle`/`data-bs-toggle`,
  `dropdown-menu-right`/`dropdown-menu-end`) is chosen from the running Moodle branch.

### Fixed
- Clicking **Enrol** sent the user back to the Dashboard instead of into the course when they had
  signed in with OAuth2 (e.g. Google). Core's enrolment page redirects to `$SESSION->wantsurl`, and
  OAuth2 logins leave it pointing at `/my/`.
- On Moodle 4.5, clicking **Enrol** only opened the enrolment page and required a second click.
- If two open self-enrolment instances existed, a single click could enrol the user through both.

## [1.0.0] - 2026-06-20

First public release prepared for the Moodle plugins directory.

### Added
- Dashboard block that lists courses open for **free, key-less self-enrolment**, with a live
  search box and a category filter.
- Client-side search delivered as a proper **AMD module** (`block_freecourses/search`), built with
  grunt/rollup — no inline JavaScript in the template.
- **Application cache** (`db/caches.php`) for the site-wide, user-independent "candidate" course
  scan, so the block no longer scans every course on every dashboard load; per-user "already
  enrolled" filtering happens at render time.
- Privacy API `null_provider` (the block stores no personal data).
- PHPUnit tests for the free-course detection, category filtering and caching, plus a Behat feature
  covering listing, category filtering and search.
- GitHub Actions CI (`moodlehq/moodle-plugin-ci`) across PHP 8.2–8.4 × Moodle 5.0/5.1/5.2 with
  PostgreSQL and MariaDB.
- Top-level `LICENSE` (GNU GPL v3), `CONTRIBUTING.md` and this changelog.

### Changed
- Declared `$plugin->supported = [500, 502]` (Moodle 5.0–5.2), set `$plugin->requires` to Moodle
  5.0.0, and added `$plugin->release` / `$plugin->maturity`.
- Professional UI/UX pass: theme-variable (light/dark-mode safe) styling, RTL-friendly logical
  properties, visible keyboard focus, and a monochrome `currentColor` block icon.

### Fixed
- The English language pack now ships **English** strings (the plugin name and capability text were
  previously Spanish). The Spanish translation is kept under [`/translations`](translations/) and
  will be contributed to lang.moodle.org (AMOS) after approval, per the plugins-directory policy.

[1.1.2]: https://github.com/rurak-ec/moodle-block_freecourses/releases/tag/v1.1.2
[1.1.0]: https://github.com/rurak-ec/moodle-block_freecourses/releases/tag/v1.1.0
[1.0.0]: https://github.com/rurak-ec/moodle-block_freecourses/releases/tag/v1.0.0
