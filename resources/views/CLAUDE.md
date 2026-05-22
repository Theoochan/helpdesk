# UI Conventions — HelpDesk Views

## Stack de UI

- **Tailwind CSS v4** + **DaisyUI v5** + **Alpine.js**
- Layout autenticado: `resources/views/layouts/app.blade.php`
- Layout público: `resources/views/layouts/guest.blade.php`
- Componente blade global: `<x-badge :color="...">` em `resources/views/components/badge.blade.php`

---

## Padrões DaisyUI v5

### Containers de conteúdo
```html
<div class="card bg-base-100 shadow-sm">
    <div class="card-body">...</div>
</div>
```

### Tabelas
```html
<div class="overflow-x-auto">
    <table class="table table-zebra table-sm">...</table>
</div>
```
- Colunas de badge/status: sempre `<td class="whitespace-nowrap">` para evitar quebra
- Títulos de linha são links (`hover:text-primary hover:underline`), não botões "Ver →"

### Formulários
```html
<div class="form-control">
    <label class="label">
        <span class="label-text font-medium">Campo <span class="text-error">*</span></span>
    </label>
    <input class="input input-bordered w-full">
    @error('campo')
        <label class="label">
            <span class="label-text-alt text-error">{{ $message }}</span>
        </label>
    @enderror
</div>
```
- Inputs: `input input-bordered w-full`
- Selects: `select select-bordered w-full`
- Textareas: `textarea textarea-bordered w-full`
- Botão primário: `btn btn-primary btn-sm`
- Botão cancelar: `btn btn-ghost btn-sm`
- Botão destrutivo: `btn btn-error btn-outline btn-sm`
- Loading: `<span wire:loading class="loading loading-spinner loading-sm"></span>`

### Modais
```html
<div class="modal modal-open">
    <div class="modal-box max-w-sm">
        <h3 class="font-bold text-lg mb-2">Título</h3>
        <p class="text-sm text-base-content/70 mb-5">Mensagem</p>
        <div class="modal-action">
            <button wire:click="cancelar" class="btn btn-ghost btn-sm">Cancelar</button>
            <button wire:click="confirmar" class="btn btn-error btn-sm">Confirmar</button>
        </div>
    </div>
    <div class="modal-backdrop" wire:click="cancelar"></div>
</div>
```

### Badges (`x-badge`)
```html
<x-badge :color="$model->statusColor()">{{ $model->statusLabel() }}</x-badge>
```
Mapeamento de cores → classes DaisyUI:
- `blue` → `badge-info`
- `yellow` → `badge-warning`
- `green` → `badge-success`
- `red` → `badge-error`
- `purple` → `badge-secondary`
- default → `badge-ghost`

### Alertas
```html
<div role="alert" class="alert alert-success text-sm">...</div>
```
Variantes: `alert-success`, `alert-error`, `alert-info`, `alert-warning`, `alert-neutral`

### Abas
```html
<div role="tablist" class="tabs tabs-bordered mb-5">
    <button role="tab" wire:click="$set('tab', 'a')"
            class="tab {{ $tab === 'a' ? 'tab-active' : '' }}">Aba A</button>
</div>
```

### Stats (cards de métricas)
```html
<div class="stat bg-base-100 rounded-xl shadow-sm">
    <div class="stat-value text-2xl text-info">42</div>
    <div class="stat-desc">Abertos</div>
</div>
```

---

## Livewire v4 em Views

- URL persistence: `#[Url]` · Validação inline: `#[Validate(...)]`
- Dados derivados: `#[Computed]` (não computar no `render()`)
- JS externo (ex: ApexCharts): encapsular em `wire:ignore`
- Carregar libs externas: `@assets` / `@endassets`
- Scripts pós-mount: `@script` / `@endscript` com `$wire.on('evento', callback)`
- Estado Alpine: `x-data` no elemento; não misturar com estado Livewire salvo quando necessário

---

## Tailwind v4

- CSS: `@import 'tailwindcss'` (não usar `@tailwind` directives)
- DaisyUI tema customizado: `@plugin 'daisyui/theme' { name: "helpdesk"; --color-primary: oklch(...); }`
- Sem CSS customizado salvo se absolutamente necessário — apenas utility classes

---

## Sistema de Temas

Dois temas: `helpdesk` (claro) / `helpdesk-dark` (escuro).

- Controlado via Alpine.js + `localStorage('app_theme')`
- Toggle no rodapé da sidebar (ícone sol ☀️ / lua 🌙)
- Aplicação: `document.documentElement.setAttribute('data-theme', theme)` — sem reload
- Layout guest: lê `localStorage` via `x-init` para consistência na tela de login

---

## Navegação (Layout)

- Estrutura: DaisyUI `drawer` — sidebar esquerda + topbar
- Sidebar: `w-64` expandida / `w-16` recolhida; estado em `localStorage('sidebar_collapsed')`
- Topbar: `navbar bg-base-100/70 backdrop-blur-md` — translúcida com blur
- Sidebar: `bg-base-200/70 backdrop-blur-md` — translúcida com blur
- Item ativo: classe `active` nos itens DaisyUI `menu` via `$currentRoute`
- Badges de não lido: `badge badge-error badge-xs` nos links da sidebar
