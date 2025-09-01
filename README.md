## Installation

Rename configuration file (В env.example спеціально додав своі креди щоб можна було протестити функціонал)

```
.env.example to .env
```

First compile container with command:

```
docker-compose build
```

Start container with command:

```
docker-compose up -d
```

Install composer

```
docker-compose run composer install
```

Generate artisan key

```
docker-compose run artisan key:generate
```

Install migrations

```
docker-compose run artisan migrate:fresh --seed
```

Storage Path

```
docker-compose run artisan storage:link
```

## Run Unit Test
```
docker-compose run artisan test
```

## Run App
```
docker-compose run npm i
```
```
docker-compose run npm run build
```
```
https://localhost/
```




## URL
To open in browser use HTTPS **https://localhost**
