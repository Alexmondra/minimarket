---
name: mini-solu
description: Reusable skill for finding, diagnosing, testing, and fixing code errors and bugs in the Laravel Minimarket project.
---

# Skill: mini-solu

This skill equips the agent with diagnostic and troubleshooting capabilities to locate, analyze, test, and resolve issues within the Minimarket application.

## Core Capabilities

1. **Error Diagnosis**: Inspecting Laravel logs (`storage/logs/laravel.log`), Artisan outputs, queue failures, and web console errors to identify root causes of failures.
2. **PHPUnit Testing**: Writing unit, integration, and feature tests in the `tests/` directory to reproduce issues and assert their resolution.
3. **Interactive Debugging**: Utilizing Laravel Tinker and shell tools to execute code snippets, check state models, and verify DB queries or external API integrations in isolation.
4. **Code Correction**: Refactoring buggy code, fixing database constraints, resolving syntax errors, and fixing controller or service logic issues while maintaining architectural integrity.
5. **Regression Verification**: Running the full test suite (`php artisan test` or equivalent) to verify that a bug fix does not break other parts of the application.

## Guidelines

- **Reproduce First**: Never attempt a blind fix. Always reproduce the bug first, preferably via an automated test or a reproducible script in `tests/` or the `scratch/` directory.
- **Log Inspection**: Make extensive use of grep/find to trace exception origins in log files and stack traces.
- **Side-Effects Checks**: Verify that modifications to database schemas (migrations), models, or service providers do not break dependency relationships.
- **Commit Safety**: Verify that only the relevant bug-fixing code is modified using `git diff` before reporting completion.
