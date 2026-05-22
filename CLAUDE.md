# HelpDesk – AI Guidelines

## Documentação — Regra Obrigatória

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

**HelpDesk** é uma plataforma SaaS modular no TALL stack. Projetada para evoluir para multi-tenant.

- **Stack**: Laravel 13.9 · Livewire v4.3 · Tailwind CSS v4 · DaisyUI v5 · Alpine.js · Pest
- **Database**: MySQL 8.0 (SQLite `:memory:` em testes via `phpunit.xml`)
- **Auth**: Session-based · Sem auto-registro (apenas admin cria usuários)
- **Roles**: `collaborator`, `technician`, `admin`
- **Regras de negócio**: ver `FEATURES.md`
- **Convenções de UI**: ver `resources/views/CLAUDE.md`
- **Detalhes do módulo Chamados**: ver `app/Modules/Chamados/CLAUDE.md`

---

## Arquitetura

### Estrutura Modular (DDD)

```
app/Modules/
  Chamados/           → Tickets + ServiceOrders (ver CLAUDE.md próprio)
  Core/               → Auth, Admin, UserProfile
  Reports/            → TicketReports
resources/views/livewire/
  tickets/ · service-orders/ · admin/ · reports/ · technician/ · profile/
```

### Service Layer

```
Livewire Component → Service → Eloquent Model
```

Livewire **nunca** toca Eloquent diretamente. Toda lógica de negócio fica em `*/Services/`.

### Authorization

Policies Laravel registradas em `AuthServiceProvider` (`TicketPolicy`, `ServiceOrderPolicy`).
Todo action method em Livewire começa com `$this->authorize(...)` ou `Gate::authorize(...)`.

### Modelos principais

`User`, `Ticket`, `ServiceOrder`, `Category`, `TicketComment`, `ServiceOrderComment`, `TicketRead`, `ServiceOrderRead`

Roles via: `$user->isAdmin()` · `$user->isTechnician()` · `$user->isCollaborator()`

---

## Convenções PHP / Laravel

- `declare(strict_types=1)` em todo arquivo PHP
- `readonly` em constructor properties onde aplicável
- `match` sobre `switch`; `filled()` / `blank()` sobre `!empty()` / `empty()`
- Scopes: `scopeXxx($query, ...)` · Constantes de status: `const STATUS_OPEN = 'open'`
- Datas: cast `'due_date' => 'date'`; comparar com `->isPast()`, `->diffInDays()`
- Nunca SQL raw — usar Eloquent scopes

## Convenções de Teste (Pest)

- Todos os testes em `tests/Feature/`; `RefreshDatabase` global via `Pest.php`
- Helpers: `colaborador()`, `tecnico()`, `admin()`, `ticket()`, `ordem()`
- `Livewire::actingAs($user)->test(Component::class)->...`
- Agrupar com comentários: `// ─── Nome da seção ─────`
- Preferir assertions no modelo sobre `assertDatabaseHas` para colunas de data (SQLite)

---

## Demo Credentials

Senha de todos: `password`

| Role | Email |
|------|-------|
| Admin | admin@demo.com · admin2@demo.com |
| Técnico | tecnico1@demo.com · tecnico2@demo.com |
| Colaborador | colaborador1@demo.com |

Seed: `php artisan migrate:fresh --seed`

---

## Laravel Boost MCP Server

Configurado em `.mcp.json`. Ferramentas: Artisan, Tinker, log reader, query inspector, route list, Laravel docs.
Verificar: `php artisan boost:mcp`
