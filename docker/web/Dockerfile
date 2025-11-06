# Imagen base actualizada y segura de PHP con Apache
FROM php:8.4-apache

# Actualizar sistema e instalar módulos de Apache y extensiones PHP necesarias
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        libapache2-mod-security2 \
        libapache2-mod-security2-dbg \
        openssl \
        ca-certificates && \
    docker-php-ext-install mysqli && \
    a2enmod rewrite headers ssl deflate expires security2 && \
    rm -rf /var/lib/apt/lists/*

# Copiar (opcional) reglas básicas de seguridad de ModSecurity si existen
# Si no tienes un archivo de configuración, se usará el que viene por defecto
RUN cp /etc/modsecurity/modsecurity.conf-recommended /etc/modsecurity/modsecurity.conf && \
    sed -i 's/SecRuleEngine DetectionOnly/SecRuleEngine On/' /etc/modsecurity/modsecurity.conf

# Exponer el puerto HTTPS (443)
EXPOSE 443

# Comando para iniciar Apache
CMD ["apache2-foreground"]
