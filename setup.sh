#!/bin/bash
mkdir -p mysqlcerts
cd mysqlcerts

# Generar CA si no existe
if [ ! -f ca-cert.pem ]; then
    openssl genrsa 2048 > ca-key.pem
    openssl req -new -x509 -nodes -days 365 -key ca-key.pem -out ca-cert.pem -subj "/CN=devCA"
fi

# Generar certificado de servidor si no existe
if [ ! -f server-key.pem ]; then
    openssl genrsa 2048 > server-key.pem
    openssl req -new -key server-key.pem -out server.csr -subj "/CN=db"
    openssl x509 -req -in server.csr -CA ca-cert.pem -CAkey ca-key.pem -CAcreateserial -out server-cert.pem -days 365
fi

rm -f ca-cert.srl server.csr ca-key.pem

echo "Certificados generados en ./mysqlcerts"

