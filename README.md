# Salah Times Plugin

A WordPress plugin for displaying Islamic prayer times with real-time updates and countdown timers.

## Features

- **Configurable API Endpoint**: No hardcoded URLs - connect to any compatible prayer times API
- **Public Display**: Beautiful, responsive prayer times table with current prayer highlighting
- **Real-Time Countdown**: Live countdown timer to the next prayer (updates every second)
- **Automatic Updates**: CRON-based automated fetching on configured days
- **Manual Control**: Admin bar button for instant manual updates
- **Smart Caching**: 1-hour API response caching for improved performance
- **Admin Tools**: Test API connection and clear cache with one click
- **Dark Mode Support**: Automatically adapts to system color scheme preferences
- **Responsive Design**: Works seamlessly on desktop, tablet, and mobile devices

## Installation

1. Upload the `salah-times-plugin` folder to `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **Admin Menu → Salah Times** to configure settings

## Configuration

### Required Settings

1. **API Base URL**: Enter the base URL for your prayer times API
   - Example: `https://example.com`
   - The plugin will append `/mtws-iqaamah-times/all` to fetch all prayer times

2. **Location Name**: Display name for your location (e.g., "Durham", "New York")

### Optional Settings

- **Fetch Days**: Select which days of the week to automatically fetch prayer times
- **Enable CRON Job**: Toggle daily automated updates on/off

## Usage

### Display Prayer Times on Your Site

Add the shortcode to any page or post:

```
[salah_times]
```

This will display a table showing:
- All prayer times (Fajr, Sunrise, Dhuhr, Asr, Maghrib, Isha)
- Current prayer highlighted in blue
- Real-time countdown to next prayer
- Location name and current date

### Manual Updates

Use the **"Manual Update"** button in the WordPress admin bar to fetch the latest prayer times immediately.

### Admin Tools

The settings page includes helpful tools:
- **Test API Connection**: Verify your API configuration is working
- **Clear API Cache**: Force refresh of cached data

## API Endpoints

The plugin supports the following API endpoints:

- `/mtws-iqaamah-times/all` - Get all prayer times
- `/mtws-iqaamah-times/fajr` - Get Fajr time
- `/mtws-iqaamah-times/dhuhr` - Get Dhuhr time
- `/mtws-iqaamah-times/asr` - Get Asr time
- `/mtws-iqaamah-times/maghrib` - Get Maghrib time
- `/mtws-iqaamah-times/isha` - Get Isha time
- `/mtws-iqaamah-times/shurooq` - Get sunrise time

All endpoints support an optional `date` parameter in ISO 8601 format.

## Technical Details

- **Version**: 1.2
- **Requires WordPress**: 5.0 or higher
- **PHP Version**: 7.0 or higher
- **Cache Duration**: 1 hour (WordPress transients)
- **Auto-refresh**: Midnight daily for new prayer times

## Development

For development guidelines and architecture details, see [CLAUDE.md](CLAUDE.md).

### Building for Distribution

#### Automated (GitHub Actions)

The plugin automatically creates release packages when you push a version tag:

```bash
git tag v1.2.0
git push origin v1.2.0
```

This will:
- Create a zip package excluding development files
- Generate SHA256 checksum
- Create a GitHub release with downloadable assets

#### Manual (Local Build)

Use the included packaging script:

```bash
# Build with specific version
./package.sh 1.2.0

# Build with dev timestamp
./package.sh
```

The script will create a `dist/` directory with:
- `salah-times-plugin-{version}.zip` - Ready for WordPress upload
- `salah-times-plugin-{version}.zip.sha256` - SHA256 checksum
- `salah-times-plugin-{version}.zip.md5` - MD5 checksum

## Author

**Abdur-Rahman Bilal (MTWSNC)**
- GitHub: [@aramb-dev](https://github.com/aramb-dev)

## License

This plugin is developed for [MTWS](https://mtws.org) website iqaamah times functionality.

## Support

For issues and feature requests, please use the [GitHub repository](https://github.com/mtwsnc/salah-times-plugin/issues).
