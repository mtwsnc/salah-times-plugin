# Release Guide

This document explains how to create and publish new releases of the Salah Times Plugin.

## Release Process

### Option 1: Automated Release (Recommended)

1. **Ensure all changes are committed and pushed to main**
   ```bash
   git status
   git push origin main
   ```

2. **Create and push a version tag**
   ```bash
   # Follow semantic versioning: MAJOR.MINOR.PATCH
   git tag -a v1.2.0 -m "Release version 1.2.0"
   git push origin v1.2.0
   ```

3. **GitHub Actions will automatically:**
   - Build the plugin package
   - Exclude development files (plan.md, .github, etc.)
   - Create checksums (SHA256)
   - Upload artifacts
   - Create a GitHub Release with download links

4. **Download from GitHub Releases**
   - Go to https://github.com/mtwsnc/salah-times-plugin/releases
   - Download `salah-times-plugin-{version}.zip`
   - Verify with SHA256 checksum if needed

### Option 2: Manual Build

If you need to build locally without pushing a tag:

```bash
# Build with specific version
./package.sh 1.2.0

# Build with development timestamp
./package.sh
```

Output will be in `dist/` directory:
- `salah-times-plugin-{version}.zip` - WordPress plugin package
- `salah-times-plugin-{version}.zip.sha256` - SHA256 checksum
- `salah-times-plugin-{version}.zip.md5` - MD5 checksum

## Version Numbering

Follow [Semantic Versioning](https://semver.org/):

- **MAJOR** version (1.0.0 → 2.0.0): Breaking changes
- **MINOR** version (1.0.0 → 1.1.0): New features, backward compatible
- **PATCH** version (1.0.0 → 1.0.1): Bug fixes, backward compatible

## Pre-Release Checklist

Before creating a release:

- [ ] All features tested via Testing Page (Admin → Salah Times → Testing Page)
- [ ] API connection verified
- [ ] Prayer times display correctly with highlighting
- [ ] Countdown timer working
- [ ] Responsive design tested (Desktop/Tablet/Mobile)
- [ ] All endpoints tested individually
- [ ] Documentation updated (README.md, CLAUDE.md)
- [ ] Version number updated in plugin header
- [ ] CHANGELOG.md updated (if exists)

## Testing the Package

After building:

1. **Install on test WordPress site**
   ```bash
   # Upload via WordPress Admin → Plugins → Add New → Upload
   ```

2. **Run through Testing Page**
   - Go to Admin Menu → Salah Times → Testing Page
   - Complete all tests in the checklist
   - Verify frontend display with shortcode

3. **Test on production-like environment**
   - Different PHP versions (7.0+)
   - Different WordPress versions (5.0+)
   - With/without caching plugins

## Rollback Procedure

If issues are found after release:

1. **Delete the problematic tag**
   ```bash
   git tag -d v1.2.0
   git push origin :refs/tags/v1.2.0
   ```

2. **Fix the issues on main branch**

3. **Create a new patch version**
   ```bash
   git tag -a v1.2.1 -m "Release version 1.2.1 - Hotfix"
   git push origin v1.2.1
   ```

## Distribution

### WordPress.org Plugin Repository

If submitting to WordPress.org:

1. Package must include `readme.txt` (WordPress format)
2. Follow [Plugin Handbook guidelines](https://developer.wordpress.org/plugins/)
3. Submit via SVN repository

### Direct Distribution

For direct distribution to users:

1. Share the GitHub Release URL
2. Provide installation instructions
3. Include SHA256 checksum for verification

## Support

For release questions or issues:
- Open an issue: https://github.com/mtwsnc/salah-times-plugin/issues
- Contact: Abdur-Rahman Bilal (MTWSNC)
