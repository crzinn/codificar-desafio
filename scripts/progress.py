#!/usr/bin/env python3
from __future__ import annotations

import re
import sys
from pathlib import Path

PROGRESS_FILE = Path(__file__).resolve().parents[1] / "PROJECT_PROGRESS.md"

TASK_RE = re.compile(r"^- \[([ xX])\] \((\d+)\) .+$", re.MULTILINE)

def progress_bar(percent: float, width: int = 20) -> str:
    filled = round(percent / 100 * width)
    return "[" + "█" * filled + "░" * (width - filled) + "]"

def main() -> int:
    if not PROGRESS_FILE.exists():
        print(f"Error: {PROGRESS_FILE} not found.", file=sys.stderr)
        return 1

    text = PROGRESS_FILE.read_text(encoding="utf-8")
    tasks = TASK_RE.findall(text)

    if not tasks:
        print("No weighted tasks found.")
        return 1

    total_weight = sum(int(weight) for _, weight in tasks)
    completed_weight = sum(
        int(weight) for checked, weight in tasks if checked.lower() == "x"
    )

    percent = (completed_weight / total_weight * 100) if total_weight else 0.0

    completed = sum(1 for checked, _ in tasks if checked.lower() == "x")
    total = len(tasks)

    print(f"Progress: {completed}/{total} tasks")
    print(f"Weighted progress: {percent:.1f}%")
    print(f"{progress_bar(percent)} {percent:.1f}%")

    # Print phase-level progress.
    current_phase = None
    phase_tasks: list[tuple[str, int, bool]] = []

    for line in text.splitlines():
        if line.startswith("# Phase "):
            if phase_tasks:
                done = sum(w for _, w, ok in phase_tasks if ok)
                total = sum(w for _, w, _ in phase_tasks)
                phase_percent = done / total * 100 if total else 0
                print(f"  {current_phase}: {phase_percent:.1f}%")
                phase_tasks = []
            current_phase = line.removeprefix("# ").strip()
        else:
            match = TASK_RE.match(line)
            if match and current_phase:
                checked, weight = match.groups()
                phase_tasks.append(("task", int(weight), checked.lower() == "x"))

    if phase_tasks:
        done = sum(w for _, w, ok in phase_tasks if ok)
        total = sum(w for _, w, _ in phase_tasks)
        phase_percent = done / total * 100 if total else 0
        print(f"  {current_phase}: {phase_percent:.1f}%")

    return 0

if __name__ == "__main__":
    raise SystemExit(main())
