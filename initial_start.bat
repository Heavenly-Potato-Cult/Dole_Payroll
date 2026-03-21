@echo off
REM Build and start containers
docker compose up --build

REM Show the app logs
docker compose logs -f app