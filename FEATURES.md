# HelpDesk — Documentação de Funcionalidades

> **Regra:** toda alteração de comportamento, nova feature ou correção de lógica  
> **deve** atualizar este arquivo antes de ser considerada concluída.

---

## Índice

1. [Autenticação e Controle de Acesso](#1-autenticação-e-controle-de-acesso)
2. [Módulo de Chamados](#2-módulo-de-chamados)
3. [Módulo de Ordens de Serviço (OS)](#3-módulo-de-ordens-de-serviço-os)
4. [Relatórios](#4-relatórios)
5. [Notificações de Não Lido](#5-notificações-de-não-lido)
6. [Controle de Prazo](#6-controle-de-prazo)

---

## 1. Autenticação e Controle de Acesso

### Autenticação
- Login via e-mail e senha (sessão PHP)
- **Sem auto-registro** — usuários são criados apenas por admins
- Rota `/register` retorna 404
- Senha padrão de demonstração: `password`

### Papéis (`users.role`)

| Papel | Constante | Descrição |
|-------|-----------|-----------|
| `collaborator` | `isCollaborator()` | Abre chamados, acompanha os próprios |
| `technician` | `isTechnician()` | Gerencia chamados e OS |
| `admin` | `isAdmin()` | Acesso total + ações exclusivas |

### Matriz de Permissões

| Funcionalidade | Collaborator | Technician | Admin |
|----------------|:---:|:---:|:---:|
| Abrir chamado | ✓ | ✓ | ✓ |
| Ver chamados próprios | ✓ | ✓ | ✓ |
| Assumir / gerenciar chamados | — | ✓ | ✓ |
| Ver lista de OS | — | ✓ | ✓ |
| Criar OS | — | ✓ | ✓ |
| Cancelar OS (solicitante) | — | ✓ | ✓ |
| Aprovar transferência de OS | — | — | ✓ |
| Ver relatórios | — | ✓ | ✓ |
| Gerenciar categorias | — | — | ✓ |
| Gerenciar usuários | — | — | ✓ |

### Edição de Perfil (self-service)

Disponível no rodapé da sidebar via dropdown → modais centrais com fundo translúcido.

| Campo | Quem pode alterar | Restrição |
|-------|-------------------|-----------|
| Nome | Próprio usuário | Mínimo 2 caracteres |
| Senha | Próprio usuário | Exige senha atual (`Hash::check`) + mínimo 8 chars |
| E-mail | Apenas admin | Via painel de administração |

Componente: `UserProfile` (Livewire) — escuta evento Alpine `open-profile-modal` via `#[On]`.

### Edição de Usuários pelo Admin

Admin pode editar qualquer usuário via `/admin` (painel de gerenciamento):
- **Nome** e **E-mail**: editáveis inline na tabela
- **Senha**: reset opcional (campo vazio = mantém senha atual)
- Não exige senha atual do admin para redefinir senha de outro usuário

### Implementação
- Todas as permissões via **Laravel Policies** (`TicketPolicy`, `ServiceOrderPolicy`)
- Livewire components chamam `$this->authorize(...)` no início de cada action
- Retorna HTTP 403 (Forbidden) em violações

---

## 2. Módulo de Chamados

### Visão Geral
Chamados são solicitações de suporte abertas por colaboradores (ou técnicos em nome deles) e atendidas por técnicos.

### Fluxo de Status

```
open ──► in_progress ──► resolved ──► closed
  └─────────────────────────────────► cancelled
```

| Status | Label | Cor | Descrição |
|--------|-------|-----|-----------|
| `open` | Aberto | azul | Aguardando técnico |
| `in_progress` | Em andamento | amarelo | Técnico assumiu |
| `resolved` | Resolvido | verde | Técnico marcou como resolvido |
| `closed` | Fechado | cinza | Solicitante ou admin confirmou |
| `cancelled` | Cancelado | vermelho | Cancelado antes de resolver |

### Criação de Chamado
- **Quem pode:** qualquer usuário autenticado
- **Campos obrigatórios:** título, descrição, categoria, prioridade
- **Campos opcionais:** nenhum na criação
- Chamado criado com `status = open`, sem técnico atribuído

### Assumir Chamado (in_progress)
- **Quem pode:** técnico ou admin
- Técnico se torna `technician_id`
- Status muda para `in_progress`
- **Prazo opcional:** ao assumir, técnico pode definir `due_date`
- O prazo pode ser editado posteriormente (inline na tela de detalhe)

### Resolver Chamado
- **Quem pode:** técnico responsável (`technician_id`) ou admin
- Status muda para `resolved`
- `resolved_at` é preenchido com o timestamp atual

### Fechar Chamado
- **Quem pode:** solicitante original (`user_id`) ou admin
- Status muda para `closed`
- `closed_at` é preenchido com o timestamp atual
- Só pode fechar chamados `resolved`

### Cancelar Chamado
- **Quem pode:** solicitante (`user_id`) ou admin
- Pode cancelar chamados `open` ou `in_progress`
- Não cancela chamados `resolved` ou `closed`

### Listagem de Chamados

**Para colaborador:**
- Vê apenas os próprios chamados (`user_id = auth`)
- Filtros: status, prioridade, categoria
- Filtro especial `overdue`: chamados não terminais com prazo vencido

**Para técnico:**
- Vê chamados onde é responsável (`technician_id = auth`) + chamados abertos
- Mesmos filtros do colaborador

**Para admin:**
- Vê todos os chamados
- Mesmos filtros

### Comentários
- Qualquer participante do chamado pode comentar
- `is_internal = true` → nota interna, visível apenas para técnicos e admins
- Ao adicionar comentário, `tickets.updated_at` é tocado (via `$touches`)

### Prioridades
| Valor | Label | Cor badge |
|-------|-------|-----------|
| `low` | Baixa | verde |
| `medium` | Média | amarelo |
| `high` | Alta | vermelho |

---

## 3. Módulo de Ordens de Serviço (OS)

### Visão Geral
OS são tarefas técnicas criadas por um técnico (solicitante) e atribuídas a outro técnico (responsável). Colaboradores não têm acesso a este módulo.

### Fluxo de Status

```
pending ──► in_progress ──► done
  └──────────────────────► cancelled
```

| Status | Label | Cor | Descrição |
|--------|-------|-----|-----------|
| `pending` | Pendente | azul | Aguardando início |
| `in_progress` | Em andamento | amarelo | Responsável iniciou |
| `done` | Concluída | verde | Responsável finalizou |
| `cancelled` | Cancelada | vermelho | Cancelada |

### Criação de OS
- **Quem pode:** técnico ou admin
- **Campos obrigatórios:** título, descrição, prioridade, responsável (`assigned_to_id`)
- **Campos opcionais:** prazo (`due_date`)
- `requester_id` = usuário autenticado
- `assigned_to_id` deve ser um técnico diferente do solicitante
- Status inicial: `pending`

### Iniciar OS (pending → in_progress)
- **Quem pode:** responsável (`assigned_to_id`) ou admin
- Status muda para `in_progress`
- Solicitante **não pode** iniciar

### Finalizar OS (in_progress → done)
- **Quem pode:** responsável (`assigned_to_id`) ou admin
- Status muda para `done`
- `done_at` é preenchido com o timestamp atual

### Cancelar OS
- **Quem pode:** solicitante (`requester_id`) ou admin
- Pode cancelar OS `pending` ou `in_progress`
- **Não pode** cancelar OS `done`
- Responsável **não pode** cancelar (apenas o solicitante ou admin)

### Transferência de Responsabilidade
Fluxo em 3 etapas:

```
1. Responsável solicita → preenche (transfer_requested_to_id + transfer_note)
2. Admin aprova ou rejeita
3a. Aprovação → assigned_to_id = transfer_requested_to_id, campos limpos
3b. Rejeição → campos limpos, assigned_to_id mantido
```

**Regras:**
- Apenas o responsável atual pode solicitar transferência
- Apenas admin pode aprovar ou rejeitar
- Não pode solicitar transferência em OS terminais (`done` / `cancelled`)
- Não pode solicitar nova transferência enquanto há uma pendente

### Listagem de OS — Abas

**Aba "Minhas OS" (`mine`):**
- OS onde o usuário é solicitante OU responsável
- Técnico tem acesso de escrita (pode executar ações)
- Admin tem acesso total

**Aba "Todas as OS" (`all`):**
- Todas as OS do sistema
- Técnico: **somente leitura** (banner informativo exibido)
- Admin: acesso total com ações disponíveis

### Filtros de OS
- Por status: `pending`, `in_progress`, `done`, `cancelled`
- Filtro especial `overdue`: OS não terminais com prazo vencido
- Indicador visual de transferência pendente na lista

### Comentários em OS
- Qualquer participante da OS pode comentar
- Ao adicionar comentário, `service_orders.updated_at` é tocado (via `$touches`)

---

## 4. Relatórios

### Acesso
- Disponível para técnicos e admins
- Rota: `/reports`

### Métricas Disponíveis
| Métrica | Descrição |
|---------|-----------|
| Total de chamados | Contagem geral |
| Chamados abertos | Status `open` |
| Chamados em andamento | Status `in_progress` |
| Chamados resolvidos | Status `resolved` ou `closed` |
| Tempo médio de resolução | Média de `created_at` → `resolved_at` |

### Gráficos
| Gráfico | Tipo | Dados |
|---------|------|-------|
| Chamados por status | Donut | Distribuição de status |
| Chamados por categoria | Barra horizontal | Volume por categoria |
| Chamados por prioridade | Barra | Volume por prioridade |
| Evolução no tempo | Linha | Chamados abertos vs. resolvidos por período |

### Implementação
- Componente Livewire: `TicketReports`
- Biblioteca de gráficos: **ApexCharts** (carregada via `@assets`)
- Dados passados via evento Livewire `chartsUpdated` para re-renderizar gráficos
- `wire:ignore` nos containers de gráfico para evitar morphing

---

## 5. Notificações de Não Lido

### Funcionamento
- Ícone no menu lateral mostra badge com contagem de itens não lidos
- Um item é considerado **não lido** se `updated_at` > último registro em `*_reads`
- Ao abrir o detalhe, registra leitura automaticamente

### Tabelas envolvidas
- `ticket_reads` — leituras de chamados por usuário
- `service_order_reads` — leituras de OS por usuário

### Escopo de visibilidade
- Colaborador: conta apenas chamados próprios não lidos
- Técnico: conta chamados atribuídos + OS participantes não lidos
- Admin: conta todos não lidos

---

## 6. Controle de Prazo

### Aplicação
Funciona da mesma forma em **chamados** e **ordens de serviço**.

### Badge de Prazo

| Condição | Cor | Label exemplo |
|----------|-----|---------------|
| Sem prazo definido | — | (não exibe) |
| Status terminal | — | (não exibe) |
| Mais de 2 dias restantes | 🟢 verde | "5d restantes" |
| 0–2 dias restantes | 🟡 amarelo | "Vence hoje" / "Vence em 1d" |
| Data já passou | 🔴 vermelho | "Atrasado 3d" |

### Método `dueBadge()`
Disponível em `Ticket` e `ServiceOrder`. Retorna:
```php
null                                        // sem prazo ou status terminal
['label' => '5d restantes', 'color' => 'green']
['label' => 'Vence hoje',   'color' => 'yellow']
['label' => 'Atrasado 3d',  'color' => 'red']
```

### Filtro "Atrasados" (`overdue`)
- Disponível nas listagens de chamados e OS
- Critério: `due_date < today` AND status não terminal
- Implementado via scope `scopeByStatusOrOverdue($query, $status)`

### Definição do Prazo
| Módulo | Quem define | Quando |
|--------|-------------|--------|
| Chamados | Técnico | Ao assumir o chamado (ou editar inline depois) |
| OS | Solicitante (técnico) | Na criação da OS (opcional) |

---

## 7. Interface e Navegação

### Layout
- Sidebar colapsável esquerda (DaisyUI `drawer`): `w-64` expandida / `w-16` recolhida
- Estado de colapso persistido em `localStorage('sidebar_collapsed')`
- Topbar `navbar` com blur translúcido (`bg-base-100/70 backdrop-blur-md`)
- Sidebar com blur translúcido (`bg-base-200/70 backdrop-blur-md`)

### Temas
- Dois temas: `helpdesk` (claro) e `helpdesk-dark` (escuro)
- Toggle sol/lua no rodapé da sidebar
- Preferência persistida em `localStorage('app_theme')`
- Aplicado via `data-theme` no `<html>` controlado por Alpine.js

### Administração de Categorias
- Extraída do `AdminManager` para componente dedicado: `CategoryManager`
- Rota: `/categorias` → `categories.index` (admin only)
- Link na seção Administração da sidebar

### Listagens
- Títulos dos itens nas tabelas são links para a view de detalhe
- Badges de prazo e status com `whitespace-nowrap` para evitar quebra de linha

---

## Histórico de Alterações

| Data | Versão | Alteração |
|------|--------|-----------|
| 2026-05-20 | 1.0 | Documentação inicial — módulos Chamados, OS, Relatórios, Prazo, Não Lido |
| 2026-05-22 | 1.1 | Reestruturação modular DDD (`app/Modules/Chamados/`), CategoryManager separado |
| 2026-05-22 | 1.2 | DaisyUI v5 — migração completa das views, sidebar, temas claro/escuro |
| 2026-05-22 | 1.3 | Edição de perfil: self-service nome/senha via modal; admin edita nome/email/senha de qualquer usuário |
