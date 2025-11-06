# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a WordPress plugin that fetches and manages Islamic prayer (Salah) times from a remote API for the MTWS (Muslim Thinkers and Writers Society) website. The plugin stores prayer times locally and provides both manual and automated (CRON-based) updates.

## Architecture

The plugin follows a modular architecture with clear separation of concerns:

- **Main plugin file** (`salah-times-plugin.php`): Entry point that handles WordPress hooks, admin UI, AJAX endpoints, and CRON scheduling
- **Includes directory** (`includes/`): Core functionality modules
  - `fetch-api.php`: Fetches prayer times from remote API and saves to `salah.json`
  - `compare-json.php`: Compares local and remote JSON data to detect differences
  - `cron-handler.php`: Handles CRON job execution based on configured fetch days
- **Assets directory** (`assets/js/`): Frontend JavaScript for admin functionality
- **Data storage**: `salah.json` stores the fetched prayer times locally

### Key Data Flow

1. **Manual Update**: Admin bar button → AJAX call → `salah_manual_update()` → `salah_fetch_api()` → Updates `salah.json`
2. **Automated Update**: WordPress CRON → `salah_cron_job` hook → `salah_cron_handler_include()` → Checks if current day matches configured fetch days → `salah_fetch_api()` → Updates `salah.json`

### Important Constants

- `SALAH_PLUGIN_DIR`: Plugin directory path
- `SALAH_JSON_FILE`: Path to local salah.json file
- API endpoint: `https://northerly-robin-8705.dataplicity.io/mtws-iqaamah-times/all`

## Development Workflow

### Testing the Plugin

Since this is a WordPress plugin, it must be tested in a WordPress environment:

1. Place the plugin in `wp-content/plugins/salah-times-plugin/`
2. Activate via WordPress admin dashboard
3. Access plugin settings at **Admin Menu → Salah Times**
4. Test manual updates via the admin bar "Manual Update" button

### Key Configuration

Plugin settings stored in WordPress options table under `salah_plugin_settings`:
- `fetch_days`: Array of day indices (0=Sunday, 6=Saturday) when CRON should fetch
- `cron_enabled`: Boolean to enable/disable daily CRON job

### CRON Job Management

- CRON schedule is managed by WordPress's `wp_schedule_event()` system
- Hook name: `salah_cron_job`
- Frequency: daily
- The CRON only executes fetch if current day matches configured `fetch_days`
- CRON is automatically scheduled/unscheduled when settings are updated

### Admin Interface

- Admin menu page provides checkboxes for selecting fetch days and enabling CRON
- Manual update button appears in WordPress admin bar for users with `manage_options` capability
- AJAX response shows success/error message via JavaScript alert

## WordPress-Specific Considerations

- Uses WordPress HTTP API (`wp_remote_get()`) for API calls
- Uses WordPress AJAX system (`wp_ajax_*` actions)
- Settings managed via WordPress Settings API
- Admin UI uses WordPress admin bar and menu system
- File operations use direct PHP file functions with plugin constants

## Development Practices

### Git Workflow

- **IMPORTANT**: Always commit after every todo task is completed
- Use conventional commits format for all commit messages: `type(scope): description`
  - `feat(scope): description` for new features
  - `fix(scope): description` for bug fixes
  - `refactor(scope): description` for code refactoring
  - `docs(scope): description` for documentation changes
  - `chore(scope): description` for maintenance tasks
- Examples:
  - `feat(cron): add hourly fetch option`
  - `fix(api): handle timeout errors gracefully`
  - `refactor(admin): simplify settings page logic`
- Commit messages should be concise and describe what was accomplished
