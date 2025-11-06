
# PODCAST

## Integrantes
Iker Fernández, Aitzol Rivera, Paula Tapias, Nahía Galván, María Fernández y Lucía Molinero.

# Docker

## Instrucciones

Inserta el siguiente comando para clonar el repositorio en tu dispositivo:
```bash
$ git clone git@github.com:ikerfernandezmolano/PodcastSGSSI.git
```

Inserta el siguiente comando para acceder a la carpeta del repositorio:
```bash
$ cd PodcastSGSSI
```

Inserta el siguiente comando dentro del repositorio para acceder a la rama:
```bash
$ git checkout entrega_3
```

Inserta el siguiente comando dentro del repositorio para crear los certificados TLS/SSL:
```bash
$ ./setup.sh
```

Importa la base de datos con nombre database.sql en la base de datos.

Inserta el siguiente comando para iniciar el contenedor:
```bash
$ docker-compose up -d
```

##OJO!! El acceso a la web sólo se puede hacer mediante HTTPS.

Para pararlo:
```bash
$ docker-compose stop
```
