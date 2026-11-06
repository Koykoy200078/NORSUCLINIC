---
description: "Append a short entry to docs/ai-memory.md after workspace changes"
name: "Update Memory Log"
argument-hint: "Describe the changes to record"
---

Update [docs/ai-memory.md](docs/ai-memory.md) with a new entry for the latest workspace changes.

Rules:

- Only update if there were workspace changes (file add/edit/delete).
- Use date format YYYY-MM-DD.
- One short summary sentence.
- Optional list of touched files as Markdown links with workspace-relative paths.
- Do not include secrets (APP_KEY, passwords, tokens, private keys).
