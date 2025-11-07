#!/bin/bash

mkdir -p /security/log

# Directorio donde están los archivos públicos
PUBLIC_DIR="/var/www/html/public"

# Archivo donde se guardará el checksum
CHECKSUM_FILE="/security/checksums.txt"

# Limpiar archivo anterior
> "$CHECKSUM_FILE"

# Buscar todos JS y CSS en public y generar SHA256
find "$PUBLIC_DIR" -type f \( -name "*.js" -o -name "*.css" \) | sort | while read file; do
    # Guardar la ruta relativa desde la raíz del proyecto
    rel_path="${file#./}"  
    sha256sum "$file" >> "$CHECKSUM_FILE"
done

# Lanzar verificación periódica en background
(while true; do
    /security/verificar_integridad.sh >> /security/log/checksum.log 2>&1
    sleep 900 # 15 minutos
done) &

# Arrancar Apache en primer plano
apache2-foreground

