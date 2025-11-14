# PODCAST

## Integrantes
Iker Fernández, Aitzol Rivera, Paula Tapias, Nahía Galván, María Fernández y Lucía Molinero.

# ATAQUE

## Web Víctima
Se ha atacado la rama entrega_1 del Allianz. Repositorio:

https://github.com/pollitoDestructor/sgssi-allianz

# Pasos para ponerlo en marcha

Se clona el repositorio con SSH, en la versión de la rama entrega_1:
  
```bash
$ git clone -b entrega_1 git@github.com:pollitoDestructor/sgssi-allianz.git
```

Se accede al directorio y se inicia docker-compose:
  
```bash
$ cd sgssi-allianz/
$ docker-compose up -d
```

Se importa la base de datos, mediante phpMyAdmin desde:

http://localhost:8890

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

