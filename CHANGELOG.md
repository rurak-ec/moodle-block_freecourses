# Changelog

All notable changes to **block_freecourses** (Free courses) are documented here.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

[1.0.0]: https://github.com/rurak-ec/moodle-block_freecourses/releases/tag/v1.0.0
