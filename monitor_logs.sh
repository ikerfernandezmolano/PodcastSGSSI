#!/bin/bash
LOG_DIR="/home/lucia/PodcastSGSSI/app/logs"
ALERT_FILE="$LOG_DIR/alertas.log"

# Crear el archivo de alertas si no existe
mkdir -p "$LOG_DIR"
touch "$ALERT_FILE"

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Monitor iniciado" >> "$ALERT_FILE"

# Monitorizar auditoría y errores en tiempo real
tail -Fn0 "$LOG_DIR/auditoria.log" "$LOG_DIR/errores.log" | while read -r line; do
    if echo "$line" | grep -q "login_fallido"; then
        COUNT=$(grep -c "login_fallido" "$LOG_DIR/auditoria.log")
        if [ "$COUNT" -ge 5 ]; then
            echo "[$(date '+%Y-%m-%d %H:%M:%S')] ALERTA: Demasiados intentos de login fallidos (últimos 20: $COUNT)" >> "$ALERT_FILE"
        fi
    fi
    if echo "$line" | grep -q "ERROR:"; then
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] ALERTA: Error crítico detectado: $line" >> "$ALERT_FILE"
    fi
done

