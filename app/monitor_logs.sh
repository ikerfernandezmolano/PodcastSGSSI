#!/bin/bash

# === Monitorización en tiempo real de logs ===
AUDIT_LOG="/home/lucia/PodcastSGSSI/app/logs/auditoria.log"
ERROR_LOG="/home/lucia/PodcastSGSSI/app/logs/errores.log"
ALERT_LOG="/home/lucia/PodcastSGSSI/app/logs/alertas.log"

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Monitor iniciado" >> "$ALERT_LOG"

tail -Fn0 "$AUDIT_LOG" "$ERROR_LOG" | \
while read line; do
    # Detectar errores críticos
    if echo "$line" | grep -q "ERROR"; then
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] ALERTA: Error crítico detectado: $line" >> "$ALERT_LOG"
    fi

    # Detectar demasiados intentos fallidos de login
    if echo "$line" | grep -q "login_fallido"; then
        count=$(grep -c "login_fallido" "$AUDIT_LOG")
        if [ "$count" -ge 5 ]; then
            echo "[$(date '+%Y-%m-%d %H:%M:%S')] ALERTA: Demasiados intentos de login fallidos (últimos 20: $count)" >> "$ALERT_LOG"
        fi
    fi
done
