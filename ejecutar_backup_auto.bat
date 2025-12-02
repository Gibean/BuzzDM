@echo off
REM Ejecutor de backup automático
REM Para configurarlo en Task Scheduler, programa este archivo .bat cada 24 horas

cd /d "c:\xampp\htdocs\BuzzDM"
c:\xampp\php\php.exe php\auto_backup.php

REM Log de resultado (opcional)
echo Backup ejecutado el %date% %time% >> backups\auto_backup_log.txt
