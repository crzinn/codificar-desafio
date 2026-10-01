# Central de Chamados Internos

Aplicação web para registrar, distribuir e acompanhar solicitações internas. O projeto foi desenvolvido como um MVP para o desafio técnico da Codificar, com foco nas regras de negócio, organização do código, testes e execução local simples.

## Problema e solução

Pedidos internos recebidos por canais diferentes são difíceis de acompanhar e distribuir. Esta aplicação centraliza os chamados em um board com três etapas e deixa explícitos prioridade, solicitante e responsável.

O sistema permite:

- cadastrar, visualizar e editar chamados;
- filtrar por status, prioridade e responsável;
- atribuir um responsável manualmente;
- atribuir automaticamente segundo a carga atual da equipe;
- avançar chamados pelo fluxo `TODO → DOING → DONE`;
- impedir transições de status inválidas no backend.

## Stack

- PHP 8.3 ou superior e Laravel 13;
- Vue 3 e TypeScript;
- Inertia.js;
- Tailwind CSS 4;
- PostgreSQL 17;
- Docker Compose para o banco local;
- PHPUnit, PHPStan, Laravel Pint e Vite+ para qualidade.

## Arquitetura

O projeto é um monólito Laravel + Inertia + Vue. Não existe uma API REST separada para a própria interface.

```text
Vue 3 + TypeScript
        ↓
     Inertia
        ↓
Controller → Form Request → Service → Eloquent → PostgreSQL
```

Responsabilidades principais:

- `TicketController`: entrada HTTP e respostas Inertia;
- `StoreTicketRequest`, `UpdateTicketRequest` e `UpdateTicketStatusRequest`: validação;
- `TicketService`: criação, edição e atribuição manual;
- `TicketAssignmentService`: seleção automática do responsável;
- `TicketWorkflowService`: transições de status;
- Eloquent: persistência, casts e relacionamentos;
- Vue: apresentação e interação, sem decidir regras centrais.

As entidades do MVP são `responsibles` e `tickets`. O histórico `ticket_events` ficou fora do escopo inicial por ser opcional.

## Regras de domínio

### Prioridades

| Prioridade | Peso |
| ---------- | ---- |
| `LOW`      | 1    |
| `MEDIUM`   | 2    |
| `HIGH`     | 3    |

O peso é derivado do enum de prioridade e não é informado pelo usuário.

### Status

| Backend       | Board |
| ------------- | ----- |
| `OPEN`        | TODO  |
| `IN_PROGRESS` | DOING |
| `DONE`        | DONE  |

Somente `OPEN` e `IN_PROGRESS` contam como chamados em aberto. As transições permitidas são:

```text
OPEN → IN_PROGRESS → DONE
```

Saltos, retrocessos e mudanças após `DONE` são rejeitados pelo `TicketWorkflowService`.

## Atribuição automática

Para cada responsável ativo, o sistema calcula a quantidade e a carga ponderada dos chamados `OPEN` e `IN_PROGRESS`.

A ordenação é aplicada nesta sequência:

1. menor quantidade de chamados em aberto;
2. menor soma dos pesos de prioridade;
3. `last_assigned_at` mais antigo — `null` significa que a pessoa nunca recebeu um chamado;
4. menor `id` como desempate determinístico.

O critério principal continua sendo a quantidade de chamados em aberto, conforme o desafio. O peso serve apenas para desempatar quantidades iguais. Chamados `DONE` e responsáveis inativos não participam do cálculo.

A criação automática ocorre em uma transação. Os responsáveis ativos são bloqueados em ordem determinística antes do cálculo, impedindo que duas requisições simultâneas tomem a mesma decisão com base em uma carga desatualizada. Essa serialização é simples e adequada ao volume esperado do MVP.

## Pré-requisitos

- PHP 8.3 ou superior com `pdo_pgsql`, `pdo_sqlite`, `sqlite3` e `intl`;
- Composer 2;
- Node.js `^22.18` ou `>=24.11` e npm;
- Docker com Docker Compose.

O Docker Compose deste projeto executa somente o PostgreSQL. PHP, Composer e Node são executados no host.

## Instalação

Após clonar o repositório, entre na pasta do projeto e inicie o banco:

```bash
docker compose up -d
```

Depois execute o setup automatizado:

```bash
composer run setup
```

O script:

1. instala as dependências PHP;
2. executa migrations e seeders;
3. instala as dependências frontend;
4. gera o build de produção.

O `.env` local está versionado para facilitar a avaliação do desafio. Ele já contém a chave da aplicação e as mesmas credenciais locais definidas no Docker Compose, portanto o setup não o recria nem altera.

### Instalação manual

Os mesmos passos podem ser executados separadamente:

```bash
composer install
npm install
php artisan migrate --seed
npm run build
```

Se o `.env` tiver sido removido localmente, restaure-o a partir do modelo e gere uma nova chave antes de executar as migrations:

```bash
cp .env.example .env
php artisan key:generate
```

As credenciais locais do PostgreSQL já coincidem entre `.env.example` e `compose.yaml`:

```text
database: chamados
username: chamados
password: chamados
port: 5432
```

Essas credenciais e a chave versionada são exclusivamente para o ambiente local deste desafio. Antes de qualquer uso fora de desenvolvimento, substitua a chave, as credenciais e os demais valores sensíveis por configuração própria do ambiente.

## Execução

Com o PostgreSQL ativo, inicie os processos de desenvolvimento:

```bash
composer run dev
```

A aplicação ficará disponível em `http://localhost:8000`.

Como alternativa, execute em terminais separados:

```bash
php artisan serve
npm run dev
```

Para encerrar o banco:

```bash
docker compose down
```

Os dados permanecem no volume `postgres_data`. Para remover também o volume, use `docker compose down -v`.

## Dados demonstrativos

O seeder cria três responsáveis ativos:

- Arthur;
- Maria;
- João.

Também cria chamados em diferentes prioridades e estados, distribuídos de forma desigual para demonstrar a atribuição automática.

O fluxo de chamados não exige autenticação. O usuário `test@example.com` com senha `password` pertence ao starter kit preservado e não é necessário para utilizar o board.

Para popular um banco já migrado:

```bash
php artisan db:seed
```

O seeder de responsáveis é idempotente. Os chamados demonstrativos só são criados quando a tabela de chamados está vazia.

## Testes e qualidade

A suíte automatizada usa SQLite em memória para isolamento e velocidade. A aplicação local continua usando PostgreSQL, e os fluxos com locks também foram validados nesse banco.

Execute toda a validação PHP:

```bash
composer test
```

Esse comando limpa o cache de configuração e executa Pint, PHPStan com limite de 512 MB e PHPUnit.

Validações frontend:

```bash
npm run check
npm run types:check
npm run build
```

Também é possível executar apenas os testes:

```bash
php artisan test
```

Os testes priorizam:

- todos os critérios e desempates da atribuição automática;
- exclusão de responsáveis inativos;
- ausência de responsável disponível sem criação parcial;
- atribuição manual;
- CRUD e validação dos chamados;
- transições válidas e inválidas de status;
- dados enviados ao board.

## Estrutura relevante

```text
app/
├── Enums/
├── Exceptions/
├── Http/Controllers/TicketController.php
├── Http/Requests/
├── Models/
└── Services/
    ├── TicketService.php
    ├── TicketAssignmentService.php
    └── TicketWorkflowService.php

resources/js/
├── components/tickets/
├── layouts/TicketLayout.vue
├── pages/tickets/
└── types/ticket.ts

tests/
├── Feature/
└── Unit/
```

## Decisões e trade-offs

- **Laravel + Inertia + Vue:** reduz o atrito entre frontend e backend e evita uma API separada para um projeto pequeno.
- **Sem Go ou microserviços:** a complexidade operacional não traria benefício para este escopo e prazo.
- **Três estados:** cobrem o fluxo necessário e produzem um board direto.
- **Responsáveis via seeder:** o desafio não exige um CRUD administrativo completo.
- **Sem autenticação no fluxo principal:** autenticação não é requisito do desafio; o código do starter foi preservado, mas não ampliado.
- **Sem drag-and-drop:** a interação por botão mantém acessibilidade e deixa a validação do workflow explícita.
- **Sem histórico de eventos:** funcionalidade opcional deixada como evolução para priorizar regras obrigatórias e testes.
- **SQLite nos testes:** torna a suíte rápida e isolada; a integração PostgreSQL foi validada separadamente para migrations, agregação e locks.
- **Lock dos responsáveis ativos:** serializa atribuições automáticas concorrentes. É uma solução simples para o volume do MVP.

## Possíveis evoluções

- histórico de alterações por chamado;
- cadastro administrativo de responsáveis;
- busca textual;
- drag-and-drop com confirmação no backend;
- autenticação e autorização, caso se tornem requisitos;
- testes de concorrência dedicados executados diretamente no PostgreSQL.

## Bibliotecas e referências

Além do framework Laravel, foram utilizados os adaptadores oficiais do Inertia para Laravel e Vue, Vue 3, Tailwind CSS, Lucide Vue para ícones, Reka UI e os componentes do starter kit oficial do Laravel. As versões efetivas estão registradas em `composer.lock` e `package-lock.json`.

As decisões funcionais e arquiteturais completas estão documentadas em `PROJECT_SPEC_V2.md`, e o andamento da implementação está registrado em `PROJECT_PROGRESS.md`.
