---
name: nova-feature
description: >
  Pipeline completo para criar uma nova feature na stack Angular + Laravel.
  Acione para qualquer nova implementação no projeto.
argument-hint: [descrição da feature]
disable-model-invocation: true
---

Feature: $ARGUMENTS

Stack: Angular (frontend) + Laravel REST API (backend).

## Fase 1 — Planejamento

Use @"plan (agent)" para analisar o projeto e criar:
- `.claude/plano-backend.md`
- `.claude/plano-frontend.md`
- `.claude/plano-resumo.md`

Aguarde o planner confirmar "Plano criado" antes de continuar.
Se houver dúvidas pendentes no plano, resolva-as comigo antes de avançar.

## Fase 2 — Implementação

Somente após o plano aprovado, execute na sequência:

1. @"backend (agent)" — cria Controller, FormRequest, Model e rota em api.php
2. @"frontend (agent)" — cria componente standalone, serviço e registra rota
3. @"code-reviewer (agent)" — revisa o código gerado
4. @"security-reviewer (agent)" — analisa vulnerabilidades
5. @"tester (agent)" — escreve e executa os testes
6. @"documentador (agent)" — gera a documentação

Regra: só avance para o próximo agente se o atual retornar
APROVADO, SEGURANÇA APROVADA ou TESTES APROVADOS.
Se algum retornar BLOQUEADO, pare e me avise o que precisa ser corrigido.
Nunca faça commit.
