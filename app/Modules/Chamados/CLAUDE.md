# Módulo Chamados — Referência Técnica

## Estrutura

```
app/Modules/Chamados/
  Tickets/
    Livewire/    TicketList, TicketShow, CreateTicket, CategoryManager, TechnicianDashboard
    Models/      Ticket, TicketComment, TicketRead, Category
    Policies/    TicketPolicy
    Services/    TicketService
  ServiceOrders/
    Livewire/    OrderList, OrderShow, CreateOrder
    Models/      ServiceOrder, ServiceOrderComment, ServiceOrderRead
    Policies/    ServiceOrderPolicy
    Services/    ServiceOrderService
```

## Services

### TicketService
`open()` · `assign()` · `resolve()` · `close()` · `cancel()` · `setDueDate()` · `addComment()`
`reportByTechnician($from, $to)` · `reportByCollaborator($from, $to)`

### ServiceOrderService
`create()` · `start()` · `finish()` · `cancel()` · `addComment()`
`requestTransfer()` · `approveTransfer()` · `rejectTransfer()`

## Modelos

### Ticket
- Constantes: `STATUS_OPEN`, `STATUS_IN_PROGRESS`, `STATUS_RESOLVED`, `STATUS_CLOSED`, `STATUS_CANCELLED`
- Métodos: `isTerminal()`, `priorityColor()`, `priorityLabel()`, `statusColor()`, `statusLabel()`, `dueBadge()`
- Relations: `user` (solicitante), `technician` (responsável), `category`, `comments`, `reads`
- `$touches` em comments atualiza `updated_at` do ticket

### ServiceOrder
- Constantes: `STATUS_PENDING`, `STATUS_IN_PROGRESS`, `STATUS_DONE`, `STATUS_CANCELLED`
- Métodos: `isTerminal()`, `hasPendingTransfer()`, `priorityColor()`, `priorityLabel()`, `statusColor()`, `statusLabel()`, `dueBadge()`
- Relations: `requester`, `assignedTo`, `transferRequestedTo`, `comments`, `reads`
- `$touches` em comments atualiza `updated_at` da OS

### TicketRead / ServiceOrderRead
- `unreadCountFor(User $user)` — contagem para badge da sidebar
- `markRead(User $user, int $id)` — chamado nos componentes de detalhe

### Category
- Usada apenas em Tickets
- Gerenciada via `CategoryManager` (rota `/categorias`, admin only)

## Rotas

```php
// Tickets
tickets.index   GET /chamados         TicketList
tickets.create  GET /chamados/novo    CreateTicket
tickets.show    GET /chamados/{id}    TicketShow
dashboard       GET /dashboard        TechnicianDashboard (tecnico/admin) ou TicketList (colaborador)

// Service Orders
orders.index    GET /ordens           OrderList
orders.create   GET /ordens/nova      CreateOrder
orders.show     GET /ordens/{id}      OrderShow

// Admin (Chamados)
categories.index GET /categorias      CategoryManager
```

## Helpers de Teste (Pest.php)

```php
ticket(array $attrs = [])  // cria Ticket com colaborador como solicitante
ordem(array $attrs = [])   // cria ServiceOrder com dois técnicos
```

## Regras de negócio

Ver `FEATURES.md` §2 (Chamados) e §3 (OS).
