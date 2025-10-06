# Auto-Approved Local Commands

This reference tracks the shell commands that we routinely execute while maintaining the Blu stack. Add new entries whenever you introduce a repeatable workflow so the approval list stays in sync with day-to-day operations.

## PowerShell Scripts

These commands manage the full-stack developer environment on Windows.

| Command | Purpose |
| --- | --- |
| `powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\restart-stack.ps1 -FreshDatabase` | Terminates dev servers, recreates the SQLite database, runs migrations + seeders, and relaunches backend/frontend processes. |
| `powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\restart-stack.ps1` | Restarts the stack without wiping data. |
| `powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\start-stack.ps1` | Launches the Laravel API and Next.js frontend in dedicated terminals. |

## Laravel Utilities

Run from `backend/` unless noted otherwise.

| Command | Purpose |
| --- | --- |
| `php artisan config:clear` | Clears cached configuration after env/config edits. |
| `php artisan cache:clear` | Flushes application cache. |
| `php artisan route:clear` | Clears cached routes (if we enable route caching later). |
| `php artisan migrate` | Applies pending migrations without seeding. |
| `php artisan migrate:fresh --seed` | Rebuilds schema and reseeds demo data. |
| `php artisan test` | Executes the backend PHPUnit suite. |

## Frontend Tooling

Run from `bluwebnext/` unless noted otherwise.

| Command | Purpose |
| --- | --- |
| `npm run dev` | Starts the Next.js development server. |
| `npm run lint` | Runs ESLint checks. |
| `npm run test` | Executes Jest + React Testing Library suites. |
| `npm run build` | Produces a production build (useful before deployments). |
| `npm run type-check` | Runs the standalone TypeScript compiler. |

## Quick Diagnostics

| Command | Purpose |
| --- | --- |
| `Invoke-WebRequest -Uri http://127.0.0.1:8000/api/auth/sign-in -Method Post -ContentType 'application/json' -Body $body` | Verifies the login endpoint responds successfully (replace `$body` with appropriate JSON payload). |
| `Get-Content storage/logs/laravel.log -Tail 40` | Streams the latest Laravel log entries for debugging. |

---

**Maintenance Notes**
- Keep this list aligned with any new scripts or CLI utilities that become part of routine workflows.
- If you add commands that require elevated privileges or external services, document prerequisites and safety considerations alongside the entry.
