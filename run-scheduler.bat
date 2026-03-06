@echo off
REM Run Laravel scheduler - set this up in Windows Task Scheduler to run every minute
REM Task Scheduler: Action = Start a Program, Program = this .bat file
REM Trigger: Every 1 minute (or every 4 hours for just backups)

cd /d "%~dp0"
"C:\Users\mirod\AppData\Local\Programs\PHP\current\php.exe" artisan schedule:run >> storage\logs\scheduler.log 2>&1
