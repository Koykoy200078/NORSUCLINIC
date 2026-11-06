import { execSync } from "node:child_process";
import fs from "node:fs";
import path from "node:path";

const readStdin = async () => {
    return new Promise((resolve) => {
        let data = "";
        process.stdin.setEncoding("utf8");
        process.stdin.on("data", (chunk) => {
            data += chunk;
        });
        process.stdin.on("end", () => {
            resolve(data);
        });
        process.stdin.on("error", () => {
            resolve("");
        });
    });
};

const normalizePath = (value) => value.replace(/\\/g, "/");

const ensureMemoryLog = (memoryPath) => {
    if (!fs.existsSync(memoryPath)) {
        fs.mkdirSync(path.dirname(memoryPath), { recursive: true });
        fs.writeFileSync(memoryPath, "# DiagCare AI Memory Log\n", "utf8");
    }
};

const appendAutoEntry = (memoryPath, dateStamp) => {
    const current = fs.readFileSync(memoryPath, "utf8");
    const autoLine = `${dateStamp} - Auto: workspace changes detected; add details.\n`;
    if (current.includes(autoLine)) {
        return;
    }
    const needsNewline = current.length > 0 && !current.endsWith("\n");
    const prefix = needsNewline ? "\n" : current.endsWith("\n\n") ? "" : "\n";
    fs.appendFileSync(memoryPath, `${prefix}${autoLine}`, "utf8");
};

const main = async () => {
    const input = await readStdin();
    let payload = {};
    try {
        payload = input ? JSON.parse(input) : {};
    } catch {
        payload = {};
    }

    const workspacePaths = Array.isArray(payload.workspacePaths)
        ? payload.workspacePaths
        : [];
    const workspaceRoot = workspacePaths[0] || process.cwd();
    const memoryPath = path.join(workspaceRoot, "docs", "ai-memory.md");

    ensureMemoryLog(memoryPath);

    let statusOutput = "";
    try {
        statusOutput = execSync("git status --porcelain", {
            cwd: workspaceRoot,
            encoding: "utf8",
            stdio: ["ignore", "pipe", "ignore"],
        });
    } catch {
        process.stdout.write(JSON.stringify(payload));
        return;
    }

    const lines = statusOutput
        .split("\n")
        .map((line) => line.trim())
        .filter(Boolean);
    if (lines.length === 0) {
        process.stdout.write(JSON.stringify(payload));
        return;
    }

    const changedPaths = lines
        .map((line) => line.slice(3).trim())
        .filter(Boolean)
        .map((entry) =>
            entry.includes(" -> ") ? entry.split(" -> ").pop() : entry,
        )
        .map((entry) => normalizePath(entry));

    const memoryRel = normalizePath(path.join("docs", "ai-memory.md"));
    const memoryChanged = changedPaths.includes(memoryRel);
    const hasOtherChanges = changedPaths.some((entry) => entry !== memoryRel);

    if (hasOtherChanges && !memoryChanged) {
        const dateStamp = new Date().toISOString().slice(0, 10);
        appendAutoEntry(memoryPath, dateStamp);
    }

    process.stdout.write(JSON.stringify(payload));
};

main();
