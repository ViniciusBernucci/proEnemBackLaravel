---
name: nova-feature
description: >
  Pipeline completo para criar uma nova feature PHP monolítica com plano
  prévio. Acione para qualquer nova implementação no projeto.
argument-hint: [descrição da feature]
disable-model-invocation: true
---

Feature: $ARGUMENTS

Stack: PHP puro monolítico, sem framework.

## Fase 1 — Planejamento

Use @"plan (agent)" para analisar o projeto e criar o plano em
`.claude/plano-atual.md`.

Aguarde o planner confirmar "Plano criado" antes de continuar.
Se houver dúvidas pendentes no plano, resolva-as comigo antes de avançar.

## Fase 2 — Implementação

Somente após o plano aprovado, execute na sequência:

1. @"frontend (agent)" - Cria o front end
2. @"backend (agent)" — cria o processamento PHP
3. @"code-reviewer (agent)" — revisa o código gerado
4. @"security (agent)" — analisa vulnerabilidades
5. @"tester (agent)" — escreve e roda os testes
6. @"docs (agent)" — gera a documentação

Regra: só avance para o próximo agente se o atual retornar
APROVADO, SEGURANÇA APROVADA ou TESTES APROVADOS.
Se algum retornar BLOQUEADO, pare e me avise o que precisa ser corrigido.
Nunca faça nenhum commit.
