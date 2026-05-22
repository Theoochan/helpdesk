Você está revisando ou implementando regras de negócio do HelpDesk. A fonte autoritativa é `FEATURES.md`.

Domínios principais:
- **Tickets** (`FEATURES.md` §2): fluxo `open→in_progress→resolved→closed` (ou `cancelled`)
- **OS** (`FEATURES.md` §3): fluxo `pending→in_progress→done` (ou `cancelled`); transferência exige aprovação admin
- **Roles** (`FEATURES.md` §1): `collaborator` (só tickets), `technician` (tickets + OS), `admin` (tudo)
- **Prazo** (`FEATURES.md` §6): verde >2d, amarelo ≤2d, vermelho vencido
- **Não lido** (`FEATURES.md` §5): badge na sidebar por `updated_at` vs `*_reads`

Antes de implementar qualquer mudança de regra:
1. Verifique o comportamento atual em `FEATURES.md`
2. Identifique o Service afetado (`TicketService` ou `ServiceOrderService`)
3. Atualize `FEATURES.md` (incluindo Histórico de Alterações) ao concluir

$ARGUMENTS
