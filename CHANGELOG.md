# Changelog

All notable changes to the Salah Times Plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.2.2] - 2025-11-06

### Changed
- Improved admin menu structure with submenu organization
- Settings and Testing Page now appear as submenu items on hover
- Added clock icon (dashicons-clock) to main Salah Times menu
- Makes navigation more intuitive and cleaner

## [0.2.1] - 2025-11-06

### Added
- **Configurable API System**: No more hardcoded URLs - connect to any compatible prayer times API
- **Prayer Times Display**: Beautiful `[salah_times]` shortcode with responsive table layout
- **Current Prayer Highlighting**: Automatically highlights the active prayer in blue
- **Real-Time Countdown**: Live countdown timer to next prayer (updates every second)
- **API Service Module**: Complete API client with caching for all prayer endpoints
  - All prayer times endpoint
  - Individual endpoints: Fajr, Dhuhr, Asr, Maghrib, Isha, Shurooq
- **Smart Caching**: 1-hour transient cache for improved performance
- **Admin Tools**:
  - Test API Connection button
  - Clear API Cache button
  - Manual update via admin bar
- **Visual Testing Page**: Comprehensive pre-deployment testing interface
  - System status dashboard
  - Individual endpoint testing
  - Live frontend preview
  - Responsive design testing (Desktop/Tablet/Mobile)
  - Testing checklist
- **Dark Mode Support**: Automatic adaptation to system color scheme
- **Responsive Design**: Works seamlessly on all device sizes
- **Auto-Refresh**: Automatic page reload at midnight for new prayer times
- **GitHub Actions**: Automated release packaging and checksums
- **Local Build Script**: `package.sh` for manual packaging
- **Comprehensive Documentation**:
  - Installation and usage guide (README.md)
  - Architecture documentation (CLAUDE.md)
  - Release procedures (RELEASE.md)
  - Implementation status comparison

### Changed
- Refactored API calls to use centralized service class
- Updated admin settings page with API configuration fields
- Improved error handling with user-friendly messages
- Enhanced AJAX responses with proper success/error states

### Security
- Removed all hardcoded API URLs and sensitive endpoints
- Implemented proper input sanitization and validation
- Used WordPress security best practices (nonces, capability checks)
- Secure GitHub Actions workflow (no injection vulnerabilities)

### Technical Details
- Package size: 20KB (lightweight!)
- Cache duration: 1 hour
- Minimum WordPress: 5.0
- Minimum PHP: 7.0
- License: GPL v2 or later

## [0.1.0] - Previous Version

### Initial Features
- Basic API fetching from remote endpoint
- Local JSON storage
- CRON job scheduling
- Manual update functionality
- Compare local vs remote data

---

## Release Notes

### Upgrading from 0.1.0 to 0.2.1

**Important**: After upgrading, you must configure the API base URL:

1. Go to **Admin Menu → Salah Times**
2. Enter your API Base URL in the settings
3. Click "Test API Connection" to verify
4. Save settings and fetch prayer times

**New Shortcode**: Add `[salah_times]` to any page or post to display prayer times.

**Testing**: Visit **Admin Menu → Salah Times → Testing Page** to validate all features before deploying on your live site.

### Breaking Changes

- API endpoint must now be configured (no default URL)
- Shortcode is new - old installations won't display frontend automatically

### Migration Guide

If you're upgrading from the previous version:

1. **Backup** your existing `salah.json` file
2. **Update** the plugin files
3. **Configure** API base URL in settings
4. **Test** using the new Testing Page
5. **Deploy** shortcode on desired pages

---

For detailed documentation, see [README.md](README.md)
