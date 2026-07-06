@echo off
REM Add Laragon PHP to PATH
set PATH=C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64;%PATH%
REM Add Laragon to PATH for other tools
set PATH=C:\laragon\bin;%PATH%

REM Run npm build
npm run build
