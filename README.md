# PODCAST

## Integrantes
Iker Fernández, Aitzol Rivera, Paula Tapias, Nahía Galván, María Fernández y Lucía Molinero.

# ATAQUE

## Web Víctima
Se ha atacado la rama entrega_1 del Allianz. Repositorio:

https://github.com/pollitoDestructor/sgssi-allianz

## Programas Utilizados
Además, de ZAP, se han utilizado en el ataque:

# SQLMAP 
Sirve para identificar posibles casos de inyección SQL.
  
```bash
$ sudo snap install sqlmap
```

# Hashcat
Para una vez obtenido un hash, intentar obtener de dónde viene:

```bash
$ sudo apt install hashcat
```
# Seclists
Como diccionario para los ataques por fuerza bruta:

```bash
$ sudo snap install seclists
```

Obtenemos el diccionario "rockyou.txt":

```bash
$ cp /snap/seclists/1214/Passwords/Leaked-Databases/rockyou.txt.tar.gz ~/
$ tar -xzvf rockyou.txt.tar.gz
```

