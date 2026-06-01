@echo off
title StudyGenie AI Server
echo ============================================
echo   StudyGenie AI Server
echo ============================================
echo.

REM Kill any existing Python server on port 5000
for /f "tokens=5" %%a in ('netstat -ano 2^>nul ^| findstr ":5000 "') do (
    taskkill /PID %%a /F >nul 2>&1
)

REM Use Python 3.11 (has all required packages: flask, groq, sklearn, fitz)
set PYTHON=C:\Users\KIIT0001\AppData\Local\Programs\Python\Python311\python.exe

if not exist "%PYTHON%" (
    echo Python 3.11 not found at expected path.
    echo Trying system python...
    set PYTHON=python
)

echo Starting server at http://localhost:5000
echo All PDFs will be auto-indexed from disk cache (fast).
echo.
cd /d "%~dp0"
"%PYTHON%" app.py
pause
