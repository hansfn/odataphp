@echo off
IF EXIST %windir%\microsoft.net\framework\v3.5 
(
call "C:\Windows\Microsoft.NET\Framework\v3.5\MSBuild.exe" WCFDataServices.sln
)
Else 
(
echo Please Install .NET Framework 3.5
)
goto EOF
:EOF