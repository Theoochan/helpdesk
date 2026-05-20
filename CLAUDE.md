# HelpDesk – AI Guidelines

## Documentação — Regra Obrigatória

Toda alteração no sistema **deve** atualizar os arquivos de documentação antes de ser considerada concluída:

| Arquivo | Atualizar quando |
|---------|-----------------|
| `FEATURES.md` | Qualquer alteração em regra de negócio, novo fluxo, nova permissão, nova funcionalidade |
| `DATABASE.md` | Toda nova migration, alteração de coluna, nova tabela ou nova FK |
| `CLAUDE.md` | Mudança de stack, nova convenção de código, novo padrão arquitetural |

**Checklist antes de encerrar qualquer tarefa:**
- [ ] A lógica alterada está descrita no `FEATURES.md`?
- [ ] Houve migration? O `DATABASE.md` foi atualizado?
- [ ] O `FEATURES.md` tem entrada no Histórico de Alterações com data e descrição?

---

## Project Overview

**HelpDesk** is a modular SaaS platform built on the TALL stack, currently featuring a ticket management module and a service order (OS) module. It is designed to evolve into a multi-tenant system serving multiple companies.

- **Stack**: Laravel 13.9 · Livewire v4.3 · Tailwind CSS v4 · Alpine.js · Pest
- **Database**: MySQL 8.0 (SQLite `:memory:` for tests via `phpunit.xml`)
- **Auth**: Session-based · No self-registration (invite-only or admin-created)
- **Roles**: `collaborator`, `technician`, `admin`

---

## Architecture

### Service Layer Pattern

All business logic lives in `app/Services/`. Livewire components **never** touch Eloquent directly — they call services.

```
Livewire Component → Service → Eloquent Model
```

- `TicketService` — ticket lifecycle (open, assign, resolve, close, cancel)
- `ServiceOrderService` — OS lifecycle (create, start, finish, cancel, transfer)

### Authorization

All access control uses **Laravel Policies** registered in `AuthServiceProvider`.

- `TicketPolicy`
- `ServiceOrderPolicy`

Livewire components call `$this->authorize(...)` or `Gate::authorize(...)` at the top of every action method.

### Models

Key models: `User`, `Ticket`, `ServiceOrder`, `Category`, `TicketComment`, `ServiceOrderComment`, `TicketRead`, `ServiceOrderRead`.

`User` roles checked via: `$user->isAdmin()`, `$user->isTechnician()`, `$user->isCollaborator()`.

### File Structure

```
app/
  Livewire/
    Auth/          - Login only (no Register)
    Tickets/       - TicketList, TicketShow, CreateTicket
    ServiceOrders/ - OrderList, OrderShow, CreateOrder
    Reports/       - TicketReports
  Models/
  Policies/
  Services/
resources/views/livewire/
  tickets/
  service-orders/
  reports/
database/
  migrations/
  seeders/         - DatabaseSeeder with realistic demo data
tests/
  Feature/         - TicketTest.php, ServiceOrderTest.php, AuthTest.php
  Pest.php         - Helpers: colaborador(), tecnico(), admin(), ticket(), ordem()
```

---

## Coding Conventions

### PHP / Laravel

- `declare(strict_types=1)` at the top of every PHP file
- Use `readonly` constructor properties where applicable
- Prefer `match` over `switch`
- Use `filled()` / `blank()` instead of `!empty()` / `empty()`
- Eloquent scopes use `scopeXxx($query, ...)` naming
- Status constants on models: `const STATUS_PENDING = 'pending'`
- Dates: always cast with `'due_date' => 'date'` in `$casts`; compare with `->isPast()`, `->diffInDays()`
- Never expose raw SQL; use Eloquent scopes

### Livewire v4

- Component properties use `#[Url]` for URL persistence, `#[Validate(...)]` for inline validation
- Use `#[Computed]` for derived data instead of computing in `render()`
- Alpine.js state lives in `x-data` on the element; never mix with Livewire's state unless necessary
- JavaScript in Livewire views: use `@script`/`@endscript` and `@assets`/`@endassets`
- Use `wire:ignore` on containers managed by external JS (e.g., ApexCharts)
- Listen for Livewire events in `@script` with `$wire.on('eventName', callback)`

### Blade / Tailwind

- Tailwind v4: use `@import "tailwindcss"` in CSS, not `@tailwind` directives
- No custom CSS unless absolutely necessary — use Tailwind utility classes
- Status badges follow the pattern: `text-{color}-700 bg-{color}-100 ring-{color}-600/20`
- Dark mode not required at this stage

### Testing (Pest)

- All tests in `tests/Feature/`
- Use `RefreshDatabase` (configured globally in `Pest.php`)
- Test helpers defined in `Pest.php`: `colaborador()`, `tecnico()`, `admin()`, `ticket()`, `ordem()`
- Use `Livewire::actingAs($user)->test(Component::class)->...` for Livewire component tests
- Prefer model-level assertions over `assertDatabaseHas` for date columns (SQLite stores dates as datetime strings in tests)
- Group tests with comments: `// ─── Section name ─────`

---

## Business Rules

### Tickets

- Created by **collaborators** (or technicians on behalf)
- Assigned by **technicians**; due date set at assignment time (optional)
- Status flow: `open → in_progress → resolved → closed` (can be `cancelled`)
- Only the **technician** who assumed or an **admin** can resolve/close
- Due date badge: green (>2 days), yellow (≤2 days), red (overdue)
- Filter `overdue` shows non-terminal tickets past their due date

### Service Orders (OS)

- Created by **technicians** (requester) assigned to another **technician** (responsible)
- Status flow: `pending → in_progress → done` (can be `cancelled`)
- Only **responsible** can start; only **admin** can approve/reject transfers
- Only **requester** or **admin** can cancel; cannot cancel `done` orders
- Transfer: responsible requests → admin approves/rejects → `assigned_to_id` updated
- Tabs in list: `mine` (involved as requester or responsible) / `all` (read-only for technicians, full access for admin)
- Due date badge: same green/yellow/red logic as tickets

### Roles & Access

| Action                        | Collaborator | Technician | Admin |
|-------------------------------|:---:|:---:|:---:|
| View own tickets              | ✓   | ✓   | ✓   |
| Create tickets                | ✓   | ✓   | ✓   |
| Assign / manage tickets       | –   | ✓   | ✓   |
| View OS list                  | –   | ✓   | ✓   |
| Create OS                     | –   | ✓   | ✓   |
| Approve transfer              | –   | –   | ✓   |
| View reports                  | –   | ✓   | ✓   |

---

## Demo Credentials

All passwords: `password`

| Role         | Email               |
|--------------|---------------------|
| Admin        | admin@demo.com      |
| Admin        | admin2@demo.com     |
| Technician   | tecnico1@demo.com   |
| Technician   | tecnico2@demo.com   |
| Collaborator | colaborador1@demo.com |

Seed with: `php artisan migrate:fresh --seed`

---

## Laravel Boost MCP Server

This project has the **Laravel Boost MCP server** configured in `.mcp.json`.

Available tools (after Claude Code restart): Artisan runner, Tinker, log reader, query inspector, route list, and Laravel docs.

To verify: run `php artisan boost:mcp` — it should start the MCP server without errors.
