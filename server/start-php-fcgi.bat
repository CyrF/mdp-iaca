@ECHO OFF
REM fc2021-09 lance le service php
ECHO Starting PHP FastCGI...
set PATH=C:\PHP;%PATH%
"c:\Program Files\Nginx\RunHiddenConsole.exe" C:\PHP\php-cgi.exe -b 127.0.0.1:9123