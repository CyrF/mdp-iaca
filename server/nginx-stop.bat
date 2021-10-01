REM fc2021-09 arrete le service NGINX
"C:\Program Files\nginx\nginx.exe" -p "C:\Program Files\nginx" -s stop
taskkill /IM nginx.exe /F /T