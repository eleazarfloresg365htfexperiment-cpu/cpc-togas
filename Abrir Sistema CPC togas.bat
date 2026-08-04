@echo off
title Sistema de control - Togas CPC
color 0A

cd /d C:\TOGAS_PROGRAM_CPC\cpc-togas

start "Servidor Togas CPC" /min cmd /k "php artisan serve --host=127.0.0.1 --port=8000"

timeout /t 3 /nobreak >nul

start http://127.0.0.1:8000

exit