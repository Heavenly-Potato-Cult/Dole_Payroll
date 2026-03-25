@echo off
REM Build and start containers
docker compose up --build

REM Run setup manually
docker compose exec app sh /var/www/setup.sh

REM Show the app logs
docker compose logs -f app