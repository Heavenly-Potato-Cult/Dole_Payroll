# Quick Docker Commands - DOLE Payroll

## First Time Setup

```bat
.\initial_start.bat
```

## Start / Stop

docker compose up -d # Start in background
docker compose logs -f app # Follow app logs
docker compose down # Stop & remove containers

## Laravel Commands

docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan key:generate --force

## Database GUI

Adminer: http://localhost:8080
Login: Server=db, Username=root, Password=root, Database=dole_payroll

## Export Database from Docker

docker exec dole_payroll_db mysqldump -u root -proot --add-drop-table --disable-keys dole_payroll -r /dole_payroll.sql
docker cp dole_payroll_db:/dole_payroll.sql ./dole_payroll.sql

## to test it

findstr /i "INSERT INTO" dole_payroll.sql
