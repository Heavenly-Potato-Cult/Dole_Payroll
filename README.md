# DOLE Payroll System — Docker Setup

This project runs the **Laravel Payroll System** using Docker. All dependencies like PHP, Composer, Laravel, and MySQL are included in the containers — no need to install them on your local machine.

---

## **1. Requirements**

- Docker Desktop (Windows, macOS, Linux)  
> Docker Desktop must be **downloaded and installed** on your machine. Once installed, make sure it is **running and properly set up** before starting the system.  

> You **do not need PHP, Composer, or Laravel** installed locally — everything runs inside the Docker containers.

---

## **2. Starting the System**

### Windows
After cloning the repo, you need to run the batch script in Vscode terminal to start the system and view logs:

```powershell
.\initial_start.bat
```

## Starting the program after the initial setup
> docker compose up