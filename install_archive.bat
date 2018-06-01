@echo off
mkdir temp
robocopy upload temp /E
cd temp
set PATH=%PATH%;%ProgramFiles%\7-Zip\
7z a -mx0 -r -tzip -aoa telegram_posting.zip *
cd ..
copy /Y temp\telegram_posting.zip install.zip
rd /s /q temp
exit;