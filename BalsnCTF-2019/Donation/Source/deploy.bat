:: Copy files
copy hosts C:\Windows\System32\drivers\etc\
xcopy wwwroot\* C:\inetpub\wwwroot\ /E
xcopy nginx-1.16.1 C:\nginx-1.16.1\ /E
xcopy wkhtmltopdf "C:\Program Files\wkhtmltopdf\" /E

:: Setup and restart IIS
sc start aspnet_state
C:\Windows\System32\inetsrv\appcmd.exe set site /site.name:"Default Web Site" /-bindings.[protocol='http',bindingInformation='127.0.0.1:80:']
C:\Windows\System32\inetsrv\appcmd.exe set site /site.name:"Default Web Site" /+bindings.[protocol='http',bindingInformation='127.0.0.1:8080:']
C:\Windows\System32\inetsrv\appcmd.exe stop site /site.name:"Default Web Site"
C:\Windows\System32\inetsrv\appcmd.exe start site /site.name:"Default Web Site"

:: Install php-cgi as Windows Service
C:\nginx-1.16.1\php\php-cgid.exe install
C:\nginx-1.16.1\php\php-cgid.exe start

:: Install nginx as Windows Service
C:\nginx-1.16.1\nginxd.exe install
C:\nginx-1.16.1\nginxd.exe start