@echo off
setlocal

set "RUTA_PROYECTO=%~dp0proyecto\public"
set "PUERTO=8000"
set "PHP_EJECUTABLE="

if exist "C:\xampp\php\php.exe" set "PHP_EJECUTABLE=C:\xampp\php\php.exe"
if not defined PHP_EJECUTABLE if exist "D:\xampp\php\php.exe" set "PHP_EJECUTABLE=D:\xampp\php\php.exe"

if not defined PHP_EJECUTABLE (
    where php >nul 2>nul
    if %errorlevel%==0 set "PHP_EJECUTABLE=php"
)

if not defined PHP_EJECUTABLE (
    echo.
    echo No se encontro PHP en la computadora.
    echo Instala XAMPP y vuelve a abrir este archivo.
    echo.
    echo Descarga oficial: https://www.apachefriends.org/
    echo.
    pause
    exit /b 1
)

echo.
echo Iniciando el proyecto...
echo No cierres la ventana llamada "Servidor PHP" mientras lo pruebas.
echo.

start "Servidor PHP" cmd /k ""%PHP_EJECUTABLE%" -S localhost:%PUERTO% -t "%RUTA_PROYECTO%""
timeout /t 2 /nobreak >nul
start "" "http://localhost:%PUERTO%/login.php"

endlocal
