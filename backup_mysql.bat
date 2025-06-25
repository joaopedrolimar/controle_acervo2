@echo off
set DB_NAME=controle_acervo
set DB_USER=root
set DB_PASS=
set BACKUP_DIR=C:\xampp\backup_mysql

if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

set DATE=%DATE:~6,4%-%DATE:~3,2%-%DATE:~0,2%
set TIME=%TIME:~0,2%-%TIME:~3,2%

set FILENAME=%DB_NAME%_%DATE%_%TIME%.sql

"C:\xampp\mysql\bin\mysqldump.exe" -u %DB_USER% %DB_NAME% > "%BACKUP_DIR%\%FILENAME%"

echo Backup do banco %DB_NAME% concluído em %DATE% %TIME%
