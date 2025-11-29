@echo off
echo ====================================
echo RESTORING FILES FROM HTTRACK CACHE
echo ====================================
echo.
echo This will re-download the website with proper encoding...
echo.

cd /d "%~dp0"

echo Running HTTrack to restore files...
echo.

httrack --update

echo.
echo ====================================
echo Files restored from HTTrack cache
echo ====================================
echo.
echo Now removing dollar signs...
pause

