# Repository Guidelines

## Project Structure & Module Organization
- `salah-times-plugin.php` boots the WordPress plugin, registers hooks, and loads helper modules.
- `includes/` holds feature modules: API access (`api-service.php`), data ingestion (`fetch-api.php`, `compare-json.php`), scheduling (`cron-handler.php`), frontend rendering (`prayer-times-display.php`), and the admin visual test harness (`testing-page.php`).
- `assets/css/` and `assets/js/` provide the public and admin UI; update these alongside PHP changes that touch markup.
- `salah.json` caches the last successfully fetched payload; keep sample data realistic when sharing fixtures.
- `package.sh` produces distributable zips in `dist/` (git-ignored). Do not commit build outputs.

## Build, Test, and Development Commands
- `./package.sh [version]` creates a release bundle with checksums; omit the version to append a dev timestamp.
- `wp cron event run salah_cron_handler_include` (within a WP shell) exercises the scheduled fetch manually.
- `wp option get salah_plugin_settings --format=json` helps verify persisted configuration while debugging.

## Coding Style & Naming Conventions
- Follow WordPress PHP standards: 4-space indentation, Yoda-safe comparisons, escaped output via `esc_html()`, `esc_attr()`, etc.
- Prefix global functions with `salah_` and name classes `Salah_*` to avoid collisions.
- Keep arrays and JSON responses prettified for readability (`JSON_PRETTY_PRINT`) and document complex logic with short inline comments.
- For assets, mirror PHP naming (e.g., `salah-times.css`, `salah-countdown.js`) and avoid bundlers unless justified.

## Testing Guidelines
- Use the **Salah Times → Testing Page** admin submenu (`includes/testing-page.php`) to preview API connectivity, cached data, and the frontend card.
- After API changes, confirm `salah.json` refreshes, the countdown renders, and cron status updates as expected.
- Aim to reproduce QA steps on a staging WordPress install; automated PHPUnit suites are not yet in place, so document manual test evidence in PRs.

## Commit & Pull Request Guidelines
- Match the existing Conventional Commit pattern (`feat(display): …`, `fix(api): …`, `chore(release): …`); limit scopes to module names.
- Commits should stay focused: PHP, CSS, and JS edits belong in separate commits when practical.
- Pull requests must describe the change, list manual test steps (with screenshots for UI tweaks), and reference GitHub issues where relevant.
- Before requesting review, ensure `package.sh` still succeeds and note any configuration prerequisites for reviewers.
