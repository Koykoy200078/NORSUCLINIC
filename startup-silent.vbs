Set WshShell = CreateObject("WScript.Shell")
' Run the batch file hidden (0 = hidden window, True = wait for completion set to False for background)
WshShell.Run chr(34) & "D:\Projects\NORSUCLINIC\startup-dev-environment.bat" & Chr(34), 0, False
Set WshShell = Nothing
