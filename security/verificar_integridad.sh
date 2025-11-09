#!/bin/bash
set -euo pipefail

# Directorio base dentro del contenedor donde están los archivos públicos
BASE_DIR="/var/www/html"
CHECKSUM_FILE="/security/checksum.txt"

# Comprobamos que existe el archivo de hashes
if [ ! -f "$CHECKSUM_FILE" ]; then
  echo "❌ ERROR: No se encontró el archivo $CHECKSUM_FILE"
  exit 2
fi

# Cambiamos al directorio base para que las rutas relativas de checksums.txt funcionen
cd "$BASE_DIR"

# Imprimir fecha y hora como título
echo "============================="
echo "🔍 Verificación de integridad: $(date '+%Y-%m-%d %H:%M:%S')"
echo "============================="

# Verificamos los archivos según checksums.txt
sha256sum -c "$CHECKSUM_FILE"

STATUS=$?
if [ $STATUS -ne 0 ]; then
  echo "⚠️  VERIFICACIÓN FALLIDA: uno o más archivos fueron modificados."
  exit $STATUS
fi

echo "✅ VERIFICACIÓN OK: todos los archivos coinciden."
exit 0

