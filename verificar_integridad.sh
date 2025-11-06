#!/bin/bash
set -euo pipefail

# Nos movemos a la carpeta del proyecto
BASE_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$BASE_DIR"

# Comprobamos que existe el archivo de hashes
if [ ! -f checksums.txt ]; then
  echo "❌ ERROR: No se encontró el archivo checksums.txt"
  exit 2
fi

echo "🔍 Verificando integridad de archivos (SHA256)..."
sha256sum -c checksums.txt

STATUS=$?
if [ $STATUS -ne 0 ]; then
  echo "⚠️  VERIFICACIÓN FALLIDA: uno o más archivos fueron modificados."
  exit $STATUS
fi

echo "✅ VERIFICACIÓN OK: todos los archivos coinciden."
exit 0
