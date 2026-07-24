# CLAUDE.md — ProEnem

# Mapa do projeto — ProEnem

`<obrigatório>` Memorize estes caminhos antes de qualquer ação. Nunca escreva código fora dos caminhos definidos abaixo. Se não tiver certeza do caminho correto, **pare e pergunte** — nunca adivinhe. `</obrigatório>`

---

## Repositórios

| Camada             | Caminho absoluto                                                    |
| ------------------ | ------------------------------------------------------------------ |
| Frontend (Angular) | `C:\Users\vinicius.silva\Documents\Projetos Dev\ProEnemFront`      |
| Backend (Laravel)  | `C:\Users\vinicius.silva\Documents\Projetos Dev\ProEnemBack`       |

> A raiz do Laravel **não** é a raiz do repositório backend: o projeto Laravel vive dentro de `ProEnemBack\app-laravel\` (a raiz contém `docker-compose.yml`, `nginx\` e o wrapper Docker).

---

## Onde cada agente escreve

| Agente      | Caminho de trabalho                                                                       | Proibido escrever em |
| ----------- | ---------------------------------------------------------------------------------------- | -------------------- |
| `@frontend` | `C:\Users\vinicius.silva\Documents\Projetos Dev\ProEnemFront\src\app\`                    | qualquer outra pasta |
| `@backend`  | `C:\Users\vinicius.silva\Documents\Projetos Dev\ProEnemBack\app-laravel\app\` e `database\` | qualquer outra pasta |

---

## Estrutura do frontend

```
C:\Users\vinicius.silva\Documents\Projetos Dev\ProEnemFront\
├── src\
│   └── app\
│       ├── core\
│       │   ├── config\          ← configuração (ex.: api.config.ts)
│       │   ├── guards\          ← route guards
│       │   ├── interceptors\    ← HTTP interceptors
│       │   ├── models\          ← modelos/tipos de domínio
│       │   └── services\        ← serviços singleton (providedIn: 'root')
│       ├── features\            ← módulos de feature (um por domínio)
│       ├── pages\
│       └── shared\              ← reutilizáveis (common, pipe, model, sidebar, settings…)
└── .claude\
    ├── agents\                  ← agentes especializados
    └── commands\                ← comandos/skills de desenvolvimento
```

---

## Estrutura do backend

```
C:\Users\vinicius.silva\Documents\Projetos Dev\ProEnemBack\
├── app-laravel\                 ← raiz do projeto Laravel
│   ├── app\
│   │   ├── DTO\                  ← Data Transfer Objects (entrada/saída de dados)
│   │   ├── Http\
│   │   │   ├── Controllers\      ← controllers organizados por domínio (Auth\, Cronogramas\, Disciplinas\, Users\)
│   │   │   └── Requests\         ← Form Requests
│   │   ├── Models\              ← Eloquent models
│   │   ├── Providers\
│   │   └── Services\            ← lógica de negócio
│   ├── database\
│   │   ├── migrations\
│   │   └── seeders\
│   └── routes\
│       ├── api.php              ← rotas da API (prefixos por domínio, protegidas por auth:sanctum)
│       ├── console.php
│       └── web.php
├── docker-compose.yml
└── nginx\
```

---

## Regras de caminho — invioláveis

- `@frontend` **NUNCA** cria arquivos fora de `C:\Users\vinicius.silva\Documents\Projetos Dev\ProEnemFront\src\`
- `@backend` **NUNCA** cria arquivos fora de `C:\Users\vinicius.silva\Documents\Projetos Dev\ProEnemBack\app-laravel\app\` ou `database\`
- Features novas do frontend sempre em `src\app\features\{nome-do-modulo}\`
- Controllers sempre em `app\Http\Controllers\` (dentro de `app-laravel\`), agrupados por domínio
- Se não tiver certeza do caminho, **pare e pergunte** — nunca adivinhe

---

`<obrigatório>`

## Checklist de início de sessão

- **CRÍTICO** — Adicione cada item desta lista à sua lista de tarefas com TodoWrite. O último item DEVE ser "Finalizar com verificações finais". NÃO SEJA PREGUIÇOSO.
- Anuncie "Seguindo o fluxo de trabalho do ProEnem..." ao usuário.
- `<lembrete>` Não pule nenhuma etapa. Mesmo que ache que já sabe, leia os arquivos de skills. `</lembrete>`
- Se esta for uma sessão pós-compactação: releia todas as skills do bloco `<obrigatório>` — você perde contexto crítico após compactação.
- **NUNCA faça nenhum Commit**
- `<lembrete>` Você está no diretório do projeto. NÃO saia dele. `</lembrete>`
- Pesquise a melhor abordagem SEM fazer alterações no código ainda:
  - Pesquise skills/commands relevantes em `.claude/`
- Apresente o plano e aguarde aprovação:
  - Se houver feedback, ajuste o plano. Repita até aprovação explícita.
- `<lembrete>` Não pare aqui. Continue para o próximo item da checklist. `</lembrete>`
- Vá imediatamente para a próxima etapa. NÃO apresente o trabalho e fique esperando.
- Atualize a documentação, incluindo docs desatualizados.
- `<lembrete>` NUNCA diga "Você está absolutamente certo!" ou qualquer equivalente bajulador. `</lembrete>`

`</obrigatório>`

---

## Banco de dados — CRÍTICO

`<obrigatório>`

- As conexões são as definidas em `app-laravel\config\database.php`; a conexão padrão vem de `DB_CONNECTION` no `.env`. Nunca hardcode credenciais.
- **NUNCA** rode migrations, seeders ou statements destrutivos contra dados de produção.
- Migrations são **imutáveis após deploy** — nunca edite uma migration já aplicada; crie uma nova.
- Antes de rodar `php artisan migrate`, confira com `php artisan migrate:status` que **apenas** as migrations novas da tarefa atual estão pendentes.

`</obrigatório>`

---

# Tom

Não seja deferente. Nem sempre estou certo.
Sinalize quando não souber de algo. Sinalize más ideias, expectativas irreais e erros.
Se discordar — mesmo que seja só um pressentimento — **revide**. Diga claramente.
Pare e peça esclarecimentos quando a tarefa estiver ambígua.

`<obrigatório>` Jamais diga "Você está absolutamente certo!" ou equivalente. NUNCA. `</obrigatório>`

---

# Independência

- Não faça alterações em dados de produção.
- Não faça commit ou push direto em `main`, `master` ou `dev`.
- Não altere variáveis de ambiente de produção.
- Não instale dependências sem perguntar antes.

Fora isso, você tem autonomia total para atingir o objetivo declarado.

`<lembrete>` Corrija qualquer falha de CI que encontrar, mesmo que não tenha sido você quem a causou. `</lembrete>`

---

# Diretrizes de código

- **YAGNI** — não adicione nada que não foi explicitamente pedido.
- Comentários documentam o _porquê_, não o _o quê_. Nunca adicione comentários dizendo que algo é uma "melhoria" de algo anterior.
- Testes documentam comportamento, não implementação. Trate o interior como caixa-preta.
- Prefira bibliotecas existentes. Pergunte antes de instalar qualquer nova.
- Corrija todos os testes que falharem, mesmo que não seja seu código que os quebrou.
- Sempre encontre a **causa raiz** dos bugs. Não corrija apenas sintomas.
- Use `try/catch` apenas nos limites do sistema. Deixe erros propagarem em chamadas intermediárias.
- **Nomeação sempre descritiva** — variáveis, parâmetros e constantes devem ter nomes que revelam sua intenção. Nunca use nomes genéricos como `$a`, `$b`, `$x`, `const a`, `function fn(a, b)` etc. Um nome curto só é aceitável em contextos convencionais (`$i` em loop de índice, `$e` em catch). Fora isso, o nome precisa comunicar o que o valor representa no domínio do problema.

---

# Padrões Angular + TypeScript

`<obrigatório>` Estas regras se aplicam a TODO código Angular gerado ou modificado. `</obrigatório>`

### TypeScript

- Strict mode obrigatório.
- Prefira inferência de tipo quando óbvio. Evite `any` — use `unknown` quando incerto.

### Componentes

- Standalone por padrão. **Nunca** escreva `standalone: true` (é o padrão no Angular v20+).
- `ChangeDetectionStrategy.OnPush` em todo componente — sem exceção.
- `input()` e `output()` — nunca `@Input` / `@Output`.
- `inject()` — nunca constructor injection.
- `computed()` para estado derivado.
- Templates: `@if`, `@for`, `@switch` — nunca `*ngIf`, `*ngFor`, `*ngSwitch`.
- `class` bindings — nunca `ngClass`.
- `style` bindings — nunca `ngStyle`.
- Prefira templates inline para componentes pequenos.
- Prefira Reactive Forms. Template-driven apenas se explicitamente pedido.

### State

- Signals para estado local.
- `update()` ou `set()` — nunca `mutate()`.
- Mantenha transformações de estado puras e previsíveis.

### Services

- `providedIn: 'root'` para singletons.
- Um service = uma responsabilidade.

### Performance

- Lazy loading obrigatório em feature routes.
- Subscriptions sem `unsubscribe` = memory leak. Use `takeUntilDestroyed()` ou `async pipe`.
- `NgOptimizedImage` para imagens estáticas (não funciona com base64 inline).
- **Nunca dispare uma requisição HTTP por item de uma lista/loop** — `forkJoin(itens.map(item => svc.criar(item)))`, `.map()` seguido de subscribe, ou qualquer padrão que gere 1 request por item. Se a lista pode ter dezenas/centenas de itens (ex.: salvar N itens de um formulário, associar N registros), isso derruba o backend em produção. Peça/crie um endpoint batch no backend (array no payload, 1 única requisição) — nunca aceite ou proponha o padrão N-requisições como solução definitiva.

### Acessibilidade

- Todo código deve passar nas verificações AXE.
- WCAG AA mínimo: foco, contraste de cores, ARIA.

---

# Padrões Laravel + PHP

`<obrigatório>` Estas regras se aplicam a TODO código PHP/Laravel gerado ou modificado. `</obrigatório>`

### PHP Puro

- PHP 8.1+ obrigatório — use typed properties, enums, fibers e match expressions quando aplicável
- Declare sempre `strict_types=1` no topo de cada arquivo
- Tipagem explícita em todos os parâmetros e retornos de função — nunca omita o tipo de retorno
- Evite `mixed` — prefira union types: `int|string`
- Nunca use `@` para suprimir erros — trate a causa raiz
- Evite funções globais — encapsule em classes com responsabilidade única
- Exceções devem ser tipadas e específicas — nunca `catch (\Exception $e)` genérico sem justificativa

### Laravel — Estrutura

- Siga rigorosamente a estrutura de diretórios do Laravel — não invente pastas fora do padrão
- Controllers **apenas** recebem a request, delegam para um Service e retornam a response — sem lógica de negócio
- Regras de negócio vivem em `app/Services/`
- Transferência de dados entre camadas via `app/DTO/` (padrão do projeto — ex.: `CronogramaInputDTO`)
- Validação via `Form Requests` — nunca `$request->validate()` direto no Controller
- Eventos e listeners para operações com side effects (email, log, notificação)

### Laravel — Eloquent

- Defina `$fillable` explicitamente em todo Model — nunca use `$guarded = []`
- Relacionamentos devem ter tipos de retorno declarados: `HasMany`, `BelongsTo`, etc.
- Evite N+1: use `with()` para eager loading sempre que iterar relacionamentos
- Nunca faça queries dentro de loops — carregue antes e filtre em memória
- Migrations são imutáveis após deploy — nunca edite uma migration existente em produção

### Laravel — API

- Nunca exponha o Model Eloquent diretamente na resposta — transforme os dados (DTO ou array estruturado) antes de retornar
- Use HTTP status codes semânticos: `201` para criação, `204` para deleção, `422` para validação
- Autenticação via Sanctum para SPAs — nunca exponha rotas autenticadas sem o middleware `auth:sanctum`
- Rotas da API vivem em `routes/api.php`, agrupadas por prefixo/domínio — mantenha o padrão de agrupamento existente
- Trate erros de forma centralizada — não deixe exceções vazar para o cliente sem tratamento
- Para qualquer operação que o front precise aplicar a **vários itens de uma vez** (criar, associar, atualizar, excluir em massa), exponha um endpoint batch que receba um array no payload e resolva tudo numa única transação — nunca force o cliente a fazer N requisições individuais

### Laravel — Segurança

- Nunca construa queries com interpolação de string — use bindings do Eloquent ou Query Builder
- Sanitize inputs antes de persistir quando não usar Eloquent
- Nunca armazene senhas em texto plano — use `Hash::make()`
- Variáveis de ambiente sensíveis apenas no `.env` — nunca hardcoded
- CSRF protection ativa em todas as rotas web — não desabilite sem justificativa documentada

### Interoperabilidade Angular + Laravel

- O agente `@backend` atua em `app/` (Laravel, dentro de `app-laravel\`) e o agente `@frontend` em `src/app/` (Angular)
- Tipos TypeScript de request/response devem espelhar os contratos da API Laravel correspondentes
- Erros da API Laravel no formato `{ message: string, errors: Record<string, string[]> }` — o frontend espera exatamente esse contrato

---

## Regras de Code Review

`<obrigatório>`

- **NUNCA aplique automaticamente as correções sugeridas por um code review** (agente `code-reviewer` ou equivalente).
- Rodar a revisão e **apresentar** os achados priorizados é permitido — editar o código com base neles, não.
- Após a revisão, liste os problemas e **PARE**. Aguarde o usuário decidir, item a item, o que (se algo) deve ser corrigido.

`</obrigatório>`

---

## Regras de Git

- NÃO faça commits automáticos
- NÃO faça push
- NÃO abra PRs
- Ao terminar a implementação, liste os arquivos alterados e aguarde revisão humana
