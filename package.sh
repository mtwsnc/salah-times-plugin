#!/bin/bash
# Package Salah Times Plugin for distribution
# Usage: ./package.sh [version]

set -e

# Get version from argument or use "dev"
VERSION="${1:-dev-$(date +%Y%m%d-%H%M%S)}"
PLUGIN_NAME="salah-times-plugin"
OUTPUT_DIR="dist"
TEMP_DIR="${OUTPUT_DIR}/${PLUGIN_NAME}"
ZIP_FILE="${OUTPUT_DIR}/${PLUGIN_NAME}-${VERSION}.zip"

echo "📦 Packaging ${PLUGIN_NAME} v${VERSION}"

# Clean and create directories
rm -rf "${OUTPUT_DIR}"
mkdir -p "${TEMP_DIR}"

echo "📋 Copying files..."

# Copy all necessary files
rsync -av --progress \
  --exclude='.git*' \
  --exclude='node_modules' \
  --exclude='.DS_Store' \
  --exclude='dist' \
  --exclude='plan.md' \
  --exclude='IMPLEMENTATION_STATUS.md' \
  --exclude='package.sh' \
  --exclude='.github' \
  ./ "${TEMP_DIR}/"

echo "🗜️  Creating zip archive..."

# Create zip from within dist directory
cd "${OUTPUT_DIR}"
zip -r "${PLUGIN_NAME}-${VERSION}.zip" "${PLUGIN_NAME}"
cd ..

# Create checksums
echo "🔐 Generating checksums..."
cd "${OUTPUT_DIR}"
sha256sum "${PLUGIN_NAME}-${VERSION}.zip" > "${PLUGIN_NAME}-${VERSION}.zip.sha256"
md5sum "${PLUGIN_NAME}-${VERSION}.zip" > "${PLUGIN_NAME}-${VERSION}.zip.md5"
cd ..

# Clean up temp directory
rm -rf "${TEMP_DIR}"

# Display results
echo ""
echo "✅ Package created successfully!"
echo ""
echo "📁 Output files:"
ls -lh "${OUTPUT_DIR}"
echo ""
echo "📊 Package details:"
unzip -l "${ZIP_FILE}" | head -n 20
echo ""
echo "🔐 SHA256: $(cat ${OUTPUT_DIR}/${PLUGIN_NAME}-${VERSION}.zip.sha256 | cut -d' ' -f1)"
echo ""
echo "🚀 Ready for deployment!"
echo "   Upload ${ZIP_FILE} to WordPress"
