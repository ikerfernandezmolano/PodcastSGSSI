#!/bin/bash
set -e  # Detener solo si hay errores graves

mkdir -p /security/log

PUBLIC_DIR="/var/www/html/public"
CHECKSUM_FILE="/security/checksum.txt"

# Limpiar archivo anterior
> "$CHECKSUM_FILE"

# Generar checksums solo si existe el directorio
if [ -d "$PUBLIC_DIR" ]; then
    find "$PUBLIC_DIR" -type f \( -name "*.js" -o -name "*.css" \) | sort | while read -r file; do
        sha256sum "$file" >> "$CHECKSUM_FILE"
    done
fi

# Lanzar verificación periódica solo si el script existe
if [ -x /security/verificar_integridad.sh ]; then
    (
        while true; do
            /security/verificar_integridad.sh >> /security/log/checksum.log 2>&1
            sleep 900 # Cada 15 minutos
        done
    ) &
fi

# Fin del script
exit 0

