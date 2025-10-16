# Debug & Test Files

This folder contains debugging and testing files created during development.

## Structure

```
debug/
└── test-files/
    ├── compare_expiry_display.php
    ├── visual_expiry_guide.php
    ├── visual_expiry_demo.php
    ├── preview_table.php
    └── update_view.php
```

## Files Description

### Test Files (`test-files/`)

1. **compare_expiry_display.php** - Testing file for comparing expiry date displays
2. **visual_expiry_guide.php** - Visual guide for expiry date functionality
3. **visual_expiry_demo.php** - Demo file for expiry functionality testing
4. **preview_table.php** - Table preview and testing utility
5. **update_view.php** - View update testing utility

## ⚠️ Important Notes

- These files were created for development and testing purposes
- They are **NOT** part of the production application
- They can be safely deleted once testing is complete
- Do **NOT** deploy these files to production

## Cleanup

To remove these files when no longer needed:

```powershell
# Remove all test files
Remove-Item -Path .\debug\test-files\* -Force

# Remove the entire debug folder
Remove-Item -Path .\debug\ -Recurse -Force
```

Or add to `.gitignore`:
```
/debug/
```

---

*Files moved here on October 16, 2025*
