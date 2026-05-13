Set WshShell = CreateObject("WScript.Shell")
' Run the batch file hidden (0 = hidden window, True = wait for completion set to False for background)
scriptDir = CreateObject("Scripting.FileSystemObject").GetParentFolderName(WScript.ScriptFullName)
WshShell.Run Chr(34) & scriptDir & "\startup-dev-environment.bat" & Chr(34), 0, False
Set WshShell = Nothing
