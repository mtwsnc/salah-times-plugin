# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a WordPress plugin that fetches and manages Islamic prayer (Salah) times from a configurable API. The plugin provides:
- Configurable API endpoint (no hardcoded URLs)
- Public-facing prayer times display with current prayer highlighting
- Real-time countdown to next prayer
- Manual and automated (CRON-based) updates
- API caching for performance
- Admin tools for testing and cache management

## Architecture

The plugin follows a modular architecture with clear separation of concerns:

- **Main plugin file** (`salah-times-plugin.php`): Entry point that handles WordPress hooks, admin UI, AJAX endpoints, and CRON scheduling
- **Includes directory** (`includes/`): Core functionality modules
  - `api-service.php`: API client class with caching, all prayer endpoints, and connection testing
  - `fetch-api.php`: Fetches prayer times using API service and saves to `salah.json`
  - `compare-json.php`: Compares local and remote JSON data to detect differences
  - `cron-handler.php`: Handles CRON job execution based on configured fetch days
  - `prayer-times-display.php`: Frontend display with shortcode, current prayer detection, and countdown logic
- **Assets directory**:
  - `assets/css/`: Responsive styles with dark mode support
  - `assets/js/admin.js`: Admin panel AJAX functionality
  - `assets/js/salah-countdown.js`: Real-time countdown timer and auto-refresh
- **Data storage**: `salah.json` stores the fetched prayer times locally

### Key Data Flow

1. **Manual Update**: Admin bar button → AJAX call → `salah_manual_update()` → `Salah_API_Service::get_all_prayer_times()` → Updates `salah.json`
2. **Automated Update**: WordPress CRON → `salah_cron_job` hook → `salah_cron_handler_include()` → Checks if current day matches configured fetch days → API service → Updates `salah.json`
3. **Frontend Display**: Page renders → `[salah_times]` shortcode → Reads `salah.json` → Calculates current prayer & countdown → JavaScript updates every second → Auto-refresh at midnight
4. **Caching**: API requests → Check transient cache (1 hour) → If expired, fetch from API → Store in cache → Return data

### Important Constants

- `SALAH_PLUGIN_DIR`: Plugin directory path
- `SALAH_JSON_FILE`: Path to local salah.json file
- API endpoint: Configurable via plugin settings (base URL + `/mtws-iqaamah-times/all`)

## Development Workflow

### Testing the Plugin

Since this is a WordPress plugin, it must be tested in a WordPress environment:

1. Place the plugin in `wp-content/plugins/salah-times-plugin/`
2. Activate via WordPress admin dashboard
3. Access plugin settings at **Admin Menu → Salah Times**
4. Test manual updates via the admin bar "Manual Update" button

### Key Configuration

Plugin settings stored in WordPress options table under `salah_plugin_settings`:
- `api_base_url`: Base URL for the prayer times API (required)
- `location_name`: Display name for the location
- `fetch_days`: Array of day indices (0=Sunday, 6=Saturday) when CRON should fetch
- `cron_enabled`: Boolean to enable/disable daily CRON job

### CRON Job Management

- CRON schedule is managed by WordPress's `wp_schedule_event()` system
- Hook name: `salah_cron_job`
- Frequency: daily
- The CRON only executes fetch if current day matches configured `fetch_days`
- CRON is automatically scheduled/unscheduled when settings are updated

### Admin Interface

- **Settings Page** (Admin Menu → Salah Times):
  - API base URL configuration (required)
  - Location name for display
  - Fetch days selection (which days to auto-update)
  - CRON job enable/disable toggle
  - Test API Connection button
  - Clear API Cache button
  - Shortcode usage instructions
- **Admin Bar**: Manual update button for users with `manage_options` capability
- **AJAX Responses**: Success/error messages via JavaScript alerts

### Frontend Display

- **Shortcode**: `[salah_times]` - Displays prayer times table on any page/post
- **Features**:
  - Current prayer highlighted in blue
  - Countdown timer to next prayer (updates every second)
  - Responsive design with dark mode support
  - Automatic page refresh at midnight for new prayer times
  - Handles page visibility changes (tab switching)

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
- **Co-Author**: When co-authoring commits, use:
  ```
  Co-Authored-By: MTWS Admin <tech@mtws.org>
  ```
