# HelpDesk — Documentação do Banco de Dados

> **Engine:** MySQL 8.0  
> **Gerado em:** 2026-05-20  
> **Atualizar sempre que:** adicionar migration, alterar coluna, criar nova tabela.

---

## Visão Geral — Diagrama de Relacionamentos

```
users ──────────────────────────────────────────────────────────┐
  │                                                              │
  ├─< tickets (user_id)           tickets >─── categories        │
  │     ├─< ticket_comments                                      │
  │     └─< ticket_reads                                         │
  │                                                              │
  └─< service_orders (requester_id / assigned_to_id)            │
        ├─< service_order_comments                               │
        └─< service_order_reads                                  │
                                                                 │
  (todas as FK de usuário apontam para users ──────────────────)
```

---

## Tabelas do Domínio

### `users`
Usuários do sistema. Papéis controlam o acesso a funcionalidades.

| Coluna | Tipo | Nulo | Padrão | Descrição |
|--------|------|------|--------|-----------|
| `id` | bigint unsigned | ✗ | auto | PK |
| `name` | varchar(255) | ✗ | — | Nome completo |
| `email` | varchar(255) | ✗ | — | E-mail (único) |
| `email_verified_at` | timestamp | ✓ | null | Verificação de e-mail |
| `password` | varchar(255) | ✗ | — | Hash bcrypt |
| `role` | enum | ✗ | `collaborator` | `collaborator` · `technician` · `admin` |
| `remember_token` | varchar(100) | ✓ | null | Token "lembrar sessão" |
| `created_at` / `updated_at` | timestamp | ✓ | null | Timestamps Laravel |

**Índices:** `users_email_unique`  
**Regras de negócio:**
- `collaborator` → abre chamados, não acessa OS
- `technician` → gerencia chamados e OS
- `admin` → acesso total + aprovar transferências

---

### `categories`
Categorias de chamados (ex: Rede, Hardware, Software). Configurável por admin.

| Coluna | Tipo | Nulo | Padrão | Descrição |
|--------|------|------|--------|-----------|
| `id` | bigint unsigned | ✗ | auto | PK |
| `name` | varchar(255) | ✗ | — | Nome da categoria |
| `color` | varchar(7) | ✗ | `#6b7280` | Cor hex para badge |
| `created_at` / `updated_at` | timestamp | ✓ | null | Timestamps Laravel |

---

### `tickets`
Chamados abertos por colaboradores e gerenciados por técnicos.

| Coluna | Tipo | Nulo | Padrão | Descrição |
|--------|------|------|--------|-----------|
| `id` | bigint unsigned | ✗ | auto | PK |
| `title` | varchar(255) | ✗ | — | Título do chamado |
| `description` | text | ✗ | — | Descrição detalhada |
| `status` | enum | ✗ | `open` | `open` · `in_progress` · `resolved` · `closed` · `cancelled` |
| `priority` | enum | ✗ | `medium` | `low` · `medium` · `high` |
| `user_id` | bigint unsigned | ✗ | — | FK → users (solicitante) — cascade delete |
| `technician_id` | bigint unsigned | ✓ | null | FK → users (técnico responsável) — set null |
| `category_id` | bigint unsigned | ✓ | null | FK → categories — set null |
| `due_date` | date | ✓ | null | Prazo (definido ao assumir, opcional) |
| `resolved_at` | timestamp | ✓ | null | Data de resolução |
| `closed_at` | timestamp | ✓ | null | Data de fechamento |
| `created_at` / `updated_at` | timestamp | ✓ | null | Timestamps Laravel |

**Fluxo de status:** `open → in_progress → resolved → closed` (ou `cancelled`)  
**Badge de prazo:** verde (>2d) · amarelo (≤2d) · vermelho (atrasado)

---

### `ticket_comments`
Comentários em chamados. Suporta notas internas (visíveis apenas para técnicos).

| Coluna | Tipo | Nulo | Padrão | Descrição |
|--------|------|------|--------|-----------|
| `id` | bigint unsigned | ✗ | auto | PK |
| `ticket_id` | bigint unsigned | ✗ | — | FK → tickets — cascade delete |
| `user_id` | bigint unsigned | ✗ | — | FK → users — cascade delete |
| `body` | text | ✗ | — | Corpo do comentário |
| `is_internal` | tinyint(1) | ✗ | `0` | `1` = nota interna (só técnicos) |
| `created_at` / `updated_at` | timestamp | ✓ | null | Timestamps Laravel |

---

### `ticket_reads`
Controle de leitura por usuário. Permite indicar chamados não lidos.

| Coluna | Tipo | Nulo | Padrão | Descrição |
|--------|------|------|--------|-----------|
| `id` | bigint unsigned | ✗ | auto | PK |
| `user_id` | bigint unsigned | ✗ | — | FK → users — cascade delete |
| `ticket_id` | bigint unsigned | ✗ | — | FK → tickets — cascade delete |
| `read_at` | timestamp | ✗ | — | Momento da leitura |

**Índice único:** `(user_id, ticket_id)`

---

### `service_orders`
Ordens de serviço criadas e gerenciadas exclusivamente por técnicos.

| Coluna | Tipo | Nulo | Padrão | Descrição |
|--------|------|------|--------|-----------|
| `id` | bigint unsigned | ✗ | auto | PK |
| `title` | varchar(255) | ✗ | — | Título da OS |
| `description` | text | ✗ | — | Descrição detalhada |
| `status` | enum | ✗ | `pending` | `pending` · `in_progress` · `done` · `cancelled` |
| `priority` | enum | ✗ | `medium` | `low` · `medium` · `high` |
| `requester_id` | bigint unsigned | ✗ | — | FK → users (solicitante) — cascade delete |
| `assigned_to_id` | bigint unsigned | ✗ | — | FK → users (responsável) — cascade delete |
| `due_date` | date | ✓ | null | Prazo (definido na criação, opcional) |
| `done_at` | timestamp | ✓ | null | Data de conclusão |
| `transfer_requested_to_id` | bigint unsigned | ✓ | null | FK → users (destino da transferência) — set null |
| `transfer_note` | text | ✓ | null | Justificativa da transferência |
| `created_at` / `updated_at` | timestamp | ✓ | null | Timestamps Laravel |

**Fluxo de status:** `pending → in_progress → done` (ou `cancelled`)  
**Transferência:** responsável solicita → admin aprova/rejeita → `assigned_to_id` atualizado  
**Regras de cancelamento:** apenas solicitante ou admin; não cancela OS `done`

---

### `service_order_comments`
Comentários em ordens de serviço.

| Coluna | Tipo | Nulo | Padrão | Descrição |
|--------|------|------|--------|-----------|
| `id` | bigint unsigned | ✗ | auto | PK |
| `service_order_id` | bigint unsigned | ✗ | — | FK → service_orders — cascade delete |
| `user_id` | bigint unsigned | ✗ | — | FK → users — cascade delete |
| `body` | text | ✗ | — | Corpo do comentário |
| `created_at` / `updated_at` | timestamp | ✓ | null | Timestamps Laravel |

---

### `service_order_reads`
Controle de leitura de ordens de serviço por usuário.

| Coluna | Tipo | Nulo | Padrão | Descrição |
|--------|------|------|--------|-----------|
| `id` | bigint unsigned | ✗ | auto | PK |
| `user_id` | bigint unsigned | ✗ | — | FK → users — cascade delete |
| `service_order_id` | bigint unsigned | ✗ | — | FK → service_orders — cascade delete |
| `read_at` | timestamp | ✗ | — | Momento da leitura |

**Índice único:** `(user_id, service_order_id)`

---

## Tabelas de Infraestrutura Laravel

| Tabela | Finalidade |
|--------|-----------|
| `sessions` | Sessões de usuário (driver `database`) |
| `cache` / `cache_locks` | Cache da aplicação (driver `database`) |
| `jobs` / `job_batches` / `failed_jobs` | Fila de jobs assíncronos |
| `password_reset_tokens` | Tokens de redefinição de senha |
| `migrations` | Controle de migrations executadas |

---

## Roadmap do Banco — Próximas Evoluções

### Fase 1 — Setores (transversal a todos os módulos)
```sql
sectors: id, name, color, is_active, created_at, updated_at
-- users.sector_id (nullable FK)
-- tickets.sector_id (nullable FK)
-- service_orders.sector_id (nullable FK)
```

### Fase 2 — Multi-tenancy (stancl/tenancy)
```
helpdesk_central          ← banco central
  tenants                 (id, name, domain, plan, data jsonb)
  tenant_modules          (tenant_id, module, enabled_at)

helpdesk_{tenant_slug}    ← banco por empresa (todas as tabelas acima)
```

### Fase 3 — Módulo Checklist/Contabilidade
```sql
purchase_requests, checklist_templates, checklist_items,
checklist_executions, erp_staging_*
```

### Fase 4 — Módulo Jurídico
```sql
contracts, legal_processes, legal_documents, legal_deadlines
```

### Fase 5 — Módulo Financeiro
```sql
cash_flow_entries, erp_conciliation_items, cost_centers
```
