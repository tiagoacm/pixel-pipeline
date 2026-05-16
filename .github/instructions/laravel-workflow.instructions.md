---
name: Laravel Workflow Guardrails
description: "Use when modifying Laravel or Inertia/Vue code in this workspace. Applies a preferred workflow for docs lookup, focused testing, and formatting with justified exceptions."
applyTo:
  - "app/**/*.php"
  - "routes/**/*.php"
  - "tests/**/*.php"
  - "database/**/*.php"
  - "resources/js/**/*.vue"
  - "resources/js/**/*.ts"
  - "resources/js/**/*.js"
---
# Laravel Workflow Guardrails

- Prefer searching project-version docs with `mcp_laravel-boost_search-docs` before substantial Laravel/Inertia changes; exceptions are allowed when the change is trivial or purely mechanical.
- Prefer existing project conventions and framework idioms over custom patterns.
- When behavior changes, prefer adding or updating a Pest test and run the smallest relevant test scope using `php artisan test --compact`; if skipped, explain why.
- After editing PHP files, prefer running `vendor/bin/pint --dirty --format agent`.
- Do not add or change dependencies unless explicitly requested.
