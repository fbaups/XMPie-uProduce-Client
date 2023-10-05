::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
::
:: PackageBuilder is a Windows batch script for building a Package of the App
:: It using the batch script will uninstall all the dev tools before creating
:: the package. You can run the plain PHP file to get everything.
::
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

@echo off

SET app=%0
SET lib=%~dp0

cmd /C composer81 install --no-dev --no-scripts --ignore-platform-reqs
cmd /C composer81 dump-autoload --ignore-platform-reqs

echo.

"C:\Program Files\PHP\v8.1\php.exe" "%lib%PackageBuilder.php"

echo.

cmd /C composer81 install --no-scripts --ignore-platform-reqs
cmd /C composer81 dump-autoload --ignore-platform-reqs

echo.

exit /B %ERRORLEVEL%
