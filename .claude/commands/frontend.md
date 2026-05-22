Você está trabalhando no frontend do HelpDesk. Consulte `resources/views/CLAUDE.md` para todas as convenções de UI antes de agir.

Regras críticas de UI:
- Todos os componentes usam DaisyUI v5 (`card`, `btn`, `input`, `table`, `modal`, `badge`, `tabs`, `stat`)
- Formulários: `form-control` → `label` → `label-text` → campo → `label-text-alt text-error`
- Tabelas: `table table-zebra table-sm` + `whitespace-nowrap` em células de badge
- Títulos de linha são links (`hover:text-primary hover:underline`), nunca botões "Ver →"
- Modais: `modal modal-open` + `modal-backdrop` com `wire:click` para fechar
- Temas: `helpdesk` (claro) / `helpdesk-dark` (escuro) via Alpine + localStorage
- Sidebar translúcida: `bg-base-200/70 backdrop-blur-md`
- Topbar translúcida: `bg-base-100/70 backdrop-blur-md`

$ARGUMENTS
