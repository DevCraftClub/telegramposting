<<<<<<< HEAD
@echo off
mkdir temp
robocopy upload temp /E
cd temp
set PATH=%PATH%;%ProgramFiles%\7-Zip\
7z a -mx0 -r -tzip -aoa telegram_posting.zip *
cd ..
copy /Y temp\telegram_posting.zip install.zip
rd /s /q temp
=======
@echo off
mkdir temp
robocopy upload temp /E
cd temp
set PATH=%PATH%;%ProgramFiles%\7-Zip\
7z a -mx0 -r -tzip -aoa telegram_posting.zip *
cd ..
copy /Y temp\telegram_posting.zip install.zip
rd /s /q temp
>>>>>>> 268f80438a471068af16b806ae7d3fdc3f38b55e
exit;