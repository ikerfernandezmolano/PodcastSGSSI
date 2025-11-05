#!/bin/bash

mkdir -p mysqlcerts
cd mysqlcerts

# Generar CA si no existe
if [ ! -f ca-cert.pem ]; then
    echo " Generando autoridad certificadora (CA) para MySQL..."
    openssl genrsa 2048 > ca-key.pem
    openssl req -new -x509 -nodes -days 365 -key ca-key.pem -out ca-cert.pem -subj "/CN=devCA"
fi

# Generar certificado de servidor si no existe
if [ ! -f server-key.pem ]; then
    echo "Generando certificado SSL para el servidor de base de datos..."
    openssl genrsa 2048 > server-key.pem
    openssl req -new -key server-key.pem -out server.csr -subj "/CN=db"
    openssl x509 -req -in server.csr -CA ca-cert.pem -CAkey ca-key.pem -CAcreateserial -out server-cert.pem -days 365
fi

rm -f ca-cert.srl server.csr ca-key.pem
cd ..

echo "Certificados para MySQL generados en ./mysqlcerts"


mkdir -p ssl

# Solo generar si no existen
if [ ! -f ssl/server.crt ] || [ ! -f ssl/server.key ]; then
    echo "Generando certificados SSL autofirmados para Apache..."
    openssl req -x509 -newkey rsa:2048 \
      -keyout ssl/server.key \
      -out ssl/server.crt \
      -days 365 \
      -nodes \
      -subj "/CN=localhost"
    echo "Certificados creados en ./ssl"
else
    echo "Certificados SSL de Apache ya existen, no se regeneran."
fi




