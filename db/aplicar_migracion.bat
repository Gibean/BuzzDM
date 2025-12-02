@echo off
REM Script para aplicar la migración de la tabla backups
REM Ejecutar este archivo haciendo doble clic o desde CMD

echo Aplicando migración a la base de datos dbuzz...
c:\xampp\mysql\bin\mysql.exe -u root -p35128 dbuzz < "c:\xampp\htdocs\BuzzDM\db\migrate_backups.sql"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ✓ Migración aplicada correctamente!
    echo.
) else (
    echo.
    echo ✗ Error al aplicar la migración
    echo.
)

pause
