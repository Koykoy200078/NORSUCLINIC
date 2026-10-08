@AGENTS.md

# Claude Code — additions to AGENTS.md

Everything in `AGENTS.md` (imported above) applies. Claude Code specifics:

- Skills are in **`.claude/skills/`** (same 16 as `.agents/skills/` for Codex). When you edit a skill, edit both copies.
- **Work inline:** use the normal tools yourself. Do not spawn sub-agents or run multi-agent workflows (owner's rule), even if
  a session setting suggests it.
- Library / framework docs: use Context7 for the versions in `AGENTS.md` §3.
- Start every session with the sync reads in `AGENTS.md` §1 (`git log origin/changes_v2` + `Handover.md`).
