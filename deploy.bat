@echo off
setlocal enabledelayedexpansion

:: Настройки
set FTPSession=""
set TempScript="%TEMP%\winscp_script.txt"
set TempFileList="%TEMP%\changed_files.txt"
set TempDeletedList="%TEMP%\deleted_files.txt"
set TempSelectedFiles="%TEMP%\selected_files.txt"

:: Получение списка изменённых файлов
git diff --name-only --diff-filter=AM HEAD > %TempFileList%
if %ERRORLEVEL% neq 0 (
  echo Error: failed to get list of modified files using Git.
  pause
  exit /b 1
)

:: Получение списка удалённых файлов
git diff --name-only --diff-filter=D HEAD > %TempDeletedList%
if %ERRORLEVEL% neq 0 (
  echo Error: failed to get list of deleted files using Git.
  pause
  exit /b 1
)

:: Проверка, есть ли изменения
if not exist %TempFileList% if not exist %TempDeletedList% (
  echo No changes to upload or remove.
  pause
  exit /b 0
)

:: Меню выбора файлов
echo Select files to upload:
echo ----------------------------
set FileIndex=1
set FileChoices=

for /f "usebackq delims=" %%F in (%TempFileList%) do (
  echo [!FileIndex!] %%F
  set "FileChoices=!FileChoices!%%F|"
  set /a FileIndex+=1
)

echo ----------------------------
echo Enter numbers separated by space (e.g., 1 3 5) or press Enter to upload all:
set /p SelectedFiles=Files:

:: Формирование списка выбранных файлов
> %TempSelectedFiles% (
  if "%SelectedFiles%"=="" (
    :: Если пользователь нажал Enter, копируем все файлы
    for /f "usebackq delims=" %%F in (%TempFileList%) do echo %%F
  ) else (
    :: Если введены номера файлов
    for %%I in (%SelectedFiles%) do (
      set /a LineNumber=1
      for /f "usebackq delims=" %%F in (%TempFileList%) do (
        if %%I==!LineNumber! echo %%F
        set /a LineNumber+=1
      )
    )
  )
)

:: Генерация скрипта для WinSCP
(
  echo open %FTPSession%
  if exist %TempSelectedFiles% for /f "usebackq delims=" %%F in (%TempSelectedFiles%) do (
    set "FilePath=%%F"
    setlocal enabledelayedexpansion
    set "FilePath=!FilePath:/=\!"
    echo put "!FilePath!" "%%F"
    endlocal
  )

  if exist %TempDeletedList% for /f "usebackq delims=" %%F in (%TempDeletedList%) do (
    echo rm "%%F"
  )

  echo exit
) > %TempScript%

if %ERRORLEVEL% neq 0 (
  echo Error: failed to create script for WinSCP.
  pause
  exit /b 1
)

:: Запуск WinSCP
winscp.com /script=%TempScript%
if %ERRORLEVEL% neq 0 (
  echo Error: an error occurred while executing the WinSCP command.
  pause
  exit /b 1
)

:: Очистка временных файлов
del %TempScript%
del %TempFileList%
del %TempDeletedList%
del %TempSelectedFiles%

echo Synchronization completed successfully.
pause
