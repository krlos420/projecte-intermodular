# Configuració inicial del projecte en el Backend 
Esta documentació serveix per a tindre el Backend del projecte funcional i que vaja sense errors.
## Index
- [Configuració inicial del projecte en el Backend](#configuració-inicial-del-projecte-en-el-backend)
    + [Clonar el repositori](#clonar-el-repositori)
    + [Configuració del projecte](#configuració-del-projecte)
    + [Possibles errors de la base de dades (SI APAREIXEN)](#possibles-errors-de-la-base-de-dades-si-apareixen)
        * [No apareix l'usuari](#no-apareix-lusuari)
        * [No s'ha creat la base de dades](#no-sha-creat-la-base-de-dades)

## Clonar el repositori
Primerament anem a clonar el repositori per a tindre el codi necessari per a que funcione:
```bash
git clone https://github.com/sergi1307/ProyectoIntermodular.git
```

Mirem on haviem descarregat el repositori per a vore que s'ha descarregat correctament.

## Configuració del projecte

Primerament anem a la carpeta de `Backend`:

```bash
cd ./Backend
```

I després executem el `docker-compose.yml` per a tindre els contenidors preparats:

```bash
docker compose up --build -d
```

Ara entrem al contenidor de php:

```bash
docker compose exec -it php bash
```

Per a executar les migracions del projecte:
```bash
php artisan migrate
```

## Possibles errors de la base de dades (SI APAREIXEN)
### No apareix l'usuari
En la terminal en el docker accedim al docker de la següent forma:

```bash
# Canvia "slim_mysql" pel nom del contenidor de la base de dades 
docker exec -it slim_mysql mysql -u root -p
```

Si voleu vore els usuaris que teniu en la base de dades els podeu vore amb este comand:
```bash
select user.user, user.host from mysql.user;
```

Si en el cas de que no aparega el usuari, en este el usuari `alumno` el crearem de la següent forma:

```bash
# Crea l'usuari "alumno" amb la contrasenya "alumno"
CREATE USER 'alumno'@'%' IDENTIFIED BY 'alumno';

#Li dona els permisos al usuari "alumno"
GRANT ALL PRIVILEGES ON *.* TO 'alumno'@'%';
GRANT ALL PRIVILEGES ON *.* to 'alumno'@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;
```

I ja estaria creat l'usuari "alumno" amb els permisos necessaris

### No s'ha creat la base de dades

Si al anar a la web i vos dona error paregut a:
```bash
Unknown databse 'test'
```

Simplement entrem al conenedor de mysql:
```bash
docker compose exec -it mysql mysql -u root -p 
```

I el crearem la base de dades de la següent forma:
```bash
CREATE DATABASE test;
```