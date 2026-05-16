# Sistema de Gestão de Oportunidades — PixelLab

> Documento de especificação técnica e funcional para o sistema de cadastro e gestão de oportunidades de projetos.

---

## Sumário

1. [Visão Geral](#visão-geral)
2. [Stack Tecnológica](#stack-tecnológica)
3. [Módulos do Sistema](#módulos-do-sistema)
4. [Entidades e Banco de Dados](#entidades-e-banco-de-dados)
5. [Pipeline de Etapas](#pipeline-de-etapas)
6. [Funcionalidades por Módulo](#funcionalidades-por-módulo)
7. [Notificações](#notificações)
8. [Dashboard Kanban](#dashboard-kanban)
9. [Estrutura de Arquivos Laravel](#estrutura-de-arquivos-laravel)
10. [Regras de Negócio](#regras-de-negócio)
11. [Rotas da Aplicação](#rotas-da-aplicação)

---

## Visão Geral

O sistema tem como objetivo centralizar o registro, acompanhamento e gestão de oportunidades de projetos da **PixelLab**. Cada oportunidade percorre um ciclo de vida definido por etapas, com controle de acesso por usuários cadastrados no sistema e notificações automáticas em momentos-chave do fluxo.

---

## Stack Tecnológica

| Camada | Tecnologia |
|---|---|
| Backend | Laravel 13 |
| Frontend | Inertia.js + Vue 3 (Composition API) |
| Estilização | Tailwind CSS v4 |
| Banco de Dados | MySQL / PostgreSQL |
| Autenticação | Laravel Breeze (stack Inertia/Vue) |
| Notificações | Laravel Notifications (Mail + Database) |
| Kanban UI | Vue Draggable Next (drag-and-drop) |
| Estado Global | Pinia |
| Filas | Laravel Queue (database ou Redis) |
| Testes | Pest (backend) + Vitest (frontend) |

---

## Módulos do Sistema

```
┌─────────────────────────────────────────┐
│              PixelLab System            │
├──────────────┬──────────────────────────┤
│  Usuários    │  Oportunidades           │
│  - CRUD      │  - CRUD                  │
│  - Perfis    │  - Pipeline / Kanban     │
│  - Auth      │  - Histórico de etapas   │
│              │  - Notificações          │
└──────────────┴──────────────────────────┘
```

---

## Entidades e Banco de Dados

### Tabela: `users`

```sql
id              BIGINT UNSIGNED PK
name            VARCHAR(255)
email           VARCHAR(255) UNIQUE
password        VARCHAR(255)
role            ENUM('admin', 'manager', 'viewer')
is_active       BOOLEAN DEFAULT true
email_verified_at TIMESTAMP NULL
remember_token  VARCHAR(100)
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

### Tabela: `opportunities`

```sql
id              BIGINT UNSIGNED PK
title           VARCHAR(255)
description     TEXT
client_name     VARCHAR(255)
client_email    VARCHAR(255) NULL
client_phone    VARCHAR(30)  NULL
estimated_value DECIMAL(15,2) NULL
expected_start  DATE NULL
expected_end    DATE NULL
status          ENUM(
                  'nova',
                  'validacao',
                  'aguardando_cliente',
                  'aprovada',
                  'em_execucao',
                  'finalizada',
                  'reprovada',
                  'cancelada'
                ) DEFAULT 'nova'
responsible_id  BIGINT UNSIGNED FK → users.id
notes           TEXT NULL
created_by      BIGINT UNSIGNED FK → users.id
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

### Tabela: `opportunity_status_histories`

Registra cada mudança de etapa para rastreabilidade.

```sql
id              BIGINT UNSIGNED PK
opportunity_id  BIGINT UNSIGNED FK → opportunities.id
from_status     VARCHAR(50) NULL
to_status       VARCHAR(50)
changed_by      BIGINT UNSIGNED FK → users.id
notes           TEXT NULL
created_at      TIMESTAMP
```

### Tabela: `notifications` (nativa Laravel)

Gerenciada automaticamente pelo Laravel Notifications com driver `database`.

---

## Pipeline de Etapas

As oportunidades seguem o fluxo abaixo. Transições marcadas com `→` são permitidas:

```
┌──────────┐     ┌────────────┐     ┌───────────────────┐
│   Nova   │ →   │  Validação │ →   │ Aguardando Cliente│
└──────────┘     └────────────┘     └─────────┬─────────┘
                                              │
                          ┌───────────────────▼───────────────────┐
                          │              Aprovada  🔔              │
                          └───────────────┬───────────────────────┘
                                          │
                          ┌───────────────▼───────────────────────┐
                          │            Em Execução                │
                          └───────────────┬───────────────────────┘
                                          │
                          ┌───────────────▼───────────────────────┐
                          │             Finalizada                │
                          └───────────────────────────────────────┘

A qualquer momento → Reprovada  ou  Cancelada
```

> 🔔 Ao mover para **Aprovada**, uma notificação é disparada a todos os usuários ativos do sistema.

### Transições de Status Permitidas

| De | Para |
|---|---|
| Nova | Validação, Cancelada |
| Validação | Aguardando Cliente, Reprovada, Cancelada |
| Aguardando Cliente | Aprovada, Reprovada, Cancelada |
| Aprovada | Em Execução, Cancelada |
| Em Execução | Finalizada, Cancelada |
| Finalizada | — (estado terminal) |
| Reprovada | — (estado terminal) |
| Cancelada | — (estado terminal) |

---

## Funcionalidades por Módulo

### Módulo: Usuários

- **Listar** usuários com paginação e filtro por nome, e-mail e perfil
- **Criar** usuário com nome, e-mail, senha e perfil (`admin`, `manager`, `viewer`)
- **Editar** dados do usuário, incluindo redefinição de senha
- **Ativar / Desativar** usuário (soft disable via `is_active`)
- **Excluir** com verificação de vínculo em oportunidades
- Controle de acesso baseado em perfil (`role`)

#### Perfis de Acesso

| Perfil | Permissões |
|---|---|
| `admin` | Acesso total: usuários, oportunidades, pipeline |
| `manager` | CRUD de oportunidades, visualização de usuários |
| `viewer` | Somente leitura: oportunidades e dashboard |

---

### Módulo: Oportunidades

- **Listar** com filtros por status, responsável, período e cliente
- **Criar** preenchendo todos os campos da entidade
- **Editar** informações da oportunidade (exceto quando em estado terminal)
- **Visualizar** detalhes com histórico completo de mudanças de status
- **Mudar status** via botão de ação ou drag-and-drop no Kanban
- **Excluir** apenas oportunidades em status `Nova` (ou somente `admin`)
- Exportação futura para PDF/Excel (planejada)

---

## Notificações

### Evento: Oportunidade Aprovada

**Gatilho:** mudança de qualquer status para `aprovada`

**Destinatários:** todos os usuários com `is_active = true`

**Canais:**
- `database` — notificação na sineta dentro do sistema
- `mail` — e-mail com resumo da oportunidade

**Conteúdo da notificação:**

```
Assunto: [PixelLab] Nova oportunidade aprovada: {título}

A oportunidade "{título}" foi aprovada e está pronta para execução.
Cliente: {client_name}
Valor estimado: R$ {estimated_value}
Responsável: {responsible.name}
```

### Implementação Laravel

```php
// app/Notifications/OpportunityApproved.php
class OpportunityApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Opportunity $opportunity) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[PixelLab] Oportunidade aprovada: {$this->opportunity->title}")
            ->line("A oportunidade **{$this->opportunity->title}** foi aprovada.")
            ->line("Cliente: {$this->opportunity->client_name}")
            ->action('Ver Oportunidade', route('opportunities.show', $this->opportunity));
    }

    public function toArray($notifiable): array
    {
        return [
            'opportunity_id'    => $this->opportunity->id,
            'opportunity_title' => $this->opportunity->title,
            'message'           => "Oportunidade aprovada: {$this->opportunity->title}",
        ];
    }
}
```

```php
// Disparo no Service ou Controller
User::where('is_active', true)->each(
    fn($user) => $user->notify(new OpportunityApproved($opportunity))
);
```

---

## Dashboard Kanban

O dashboard exibe todas as oportunidades organizadas em colunas por status, com suporte a **drag-and-drop** para mover entre etapas.

### Colunas do Kanban

```
[ Nova ] [ Validação ] [ Ag. Cliente ] [ Aprovada ] [ Em Execução ] [ Finalizada ] [ Reprovada ] [ Cancelada ]
```

### Comportamento

- Cada card exibe: título, cliente, responsável e valor estimado
- Ao arrastar um card para outra coluna, abre um modal de confirmação com campo de observação opcional
- A transição é validada no backend (regras de negócio aplicadas via `OpportunityService`)
- Colunas terminais (`Finalizada`, `Reprovada`, `Cancelada`) não permitem saída via drag-and-drop
- Contador de cards por coluna exibido no cabeçalho de cada coluna

### Tecnologia

- **Inertia.js + Vue 3** com `vue-draggable-next` para drag-and-drop reativo sem page reload
- Estado do Kanban gerenciado via **Pinia store**
- Comunicação com backend via `router.patch()` do Inertia ao soltar o card

---

## Estrutura de Arquivos Laravel

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── OpportunityController.php
│   │   ├── OpportunityStatusController.php
│   │   └── UserController.php
│   ├── Requests/
│   │   ├── StoreOpportunityRequest.php
│   │   ├── UpdateOpportunityRequest.php
│   │   └── StoreUserRequest.php
│   └── Middleware/
│       └── CheckRole.php
├── Models/
│   ├── Opportunity.php
│   ├── OpportunityStatusHistory.php
│   └── User.php
├── Notifications/
│   └── OpportunityApproved.php
├── Services/
│   └── OpportunityService.php          ← lógica de transição e negócio
├── Enums/
│   └── OpportunityStatus.php           ← enum PHP 8.1+
└── Policies/
    ├── OpportunityPolicy.php
    └── UserPolicy.php

database/
├── migrations/
│   ├── create_opportunities_table.php
│   └── create_opportunity_status_histories_table.php
└── seeders/
    ├── UserSeeder.php
    └── OpportunitySeeder.php

resources/js/
├── Pages/
│   ├── Dashboard/
│   │   └── Kanban.vue
│   ├── Opportunities/
│   │   ├── Index.vue
│   │   ├── Create.vue
│   │   ├── Edit.vue
│   │   └── Show.vue
│   └── Users/
│       ├── Index.vue
│       ├── Create.vue
│       └── Edit.vue
├── Components/
│   ├── Kanban/
│   │   ├── KanbanBoard.vue
│   │   ├── KanbanColumn.vue
│   │   └── OpportunityCard.vue
│   └── Notifications/
│       └── NotificationBell.vue
└── Stores/
    ├── opportunityStore.js    ← Pinia
    └── notificationStore.js   ← Pinia
```

---

## Regras de Negócio

1. Somente `admin` pode excluir oportunidades e gerenciar usuários.
2. A mudança de status deve respeitar a matriz de transições definida.
3. Toda mudança de status gera um registro em `opportunity_status_histories`.
4. Ao aprovar uma oportunidade, a notificação é disparada via fila (`queue`).
5. Oportunidades em estado terminal não podem ser editadas (somente leitura).
6. Um usuário desativado não pode ser vinculado como responsável em novas oportunidades.
7. O campo `estimated_value` é opcional no cadastro, mas obrigatório para mover para `aprovada`.

---

## Rotas da Aplicação

```php
// routes/web.php

Route::middleware(['auth'])->group(function () {

    // Dashboard Kanban
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Oportunidades
    Route::resource('opportunities', OpportunityController::class);
    Route::patch('opportunities/{opportunity}/status', [OpportunityStatusController::class, 'update'])
        ->name('opportunities.status.update');

    // Usuários — somente admin
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])
            ->name('users.toggle-active');
    });

    // Notificações
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
});
```

---

## Enum de Status (PHP 8.1+)

```php
// app/Enums/OpportunityStatus.php

enum OpportunityStatus: string
{
    case Nova               = 'nova';
    case Validacao          = 'validacao';
    case AguardandoCliente  = 'aguardando_cliente';
    case Aprovada           = 'aprovada';
    case EmExecucao         = 'em_execucao';
    case Finalizada         = 'finalizada';
    case Reprovada          = 'reprovada';
    case Cancelada          = 'cancelada';

    public function label(): string
    {
        return match($this) {
            self::Nova              => 'Nova',
            self::Validacao         => 'Validação',
            self::AguardandoCliente => 'Aguardando Cliente',
            self::Aprovada          => 'Aprovada',
            self::EmExecucao        => 'Em Execução',
            self::Finalizada        => 'Finalizada',
            self::Reprovada         => 'Reprovada',
            self::Cancelada         => 'Cancelada',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Finalizada, self::Reprovada, self::Cancelada]);
    }

    public function allowedTransitions(): array
    {
        return match($this) {
            self::Nova              => [self::Validacao, self::Cancelada],
            self::Validacao         => [self::AguardandoCliente, self::Reprovada, self::Cancelada],
            self::AguardandoCliente => [self::Aprovada, self::Reprovada, self::Cancelada],
            self::Aprovada          => [self::EmExecucao, self::Cancelada],
            self::EmExecucao        => [self::Finalizada, self::Cancelada],
            default                 => [],
        };
    }
}
```

---

## Próximos Passos

- [ ] Configurar autenticação com Laravel Breeze stack Inertia/Vue (`php artisan breeze:install vue --inertia`)
- [ ] Criar migrations e rodar `php artisan migrate`
- [ ] Implementar `OpportunityService` com validação de transições
- [ ] Implementar componentes Vue do Kanban (`KanbanBoard.vue`, `KanbanColumn.vue`, `OpportunityCard.vue`)
- [ ] Integrar `vue-draggable-next` e conectar ao `router.patch()` do Inertia
- [ ] Configurar filas (`QUEUE_CONNECTION=database`) e worker
- [ ] Configurar envio de e-mail (SMTP / Mailgun / SES)
- [ ] Escrever testes com Pest para fluxo de oportunidades e notificações
- [ ] Deploy com Forge / Vapor / servidor próprio

---

*Documento gerado para uso interno da PixelLab. Versão 1.0.*
