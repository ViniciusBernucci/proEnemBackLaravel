---
name: new-feature
description: >
  Executa o pipeline completo para criar uma nova feature PHP monolítica:
  backend-writer, code-reviewer, security-reviewer, tester e documentador
  em sequência. Use quando precisar implementar uma nova funcionalidade.
argument-hint: "descrição da feature"
disable-model-invocation: true
---

Preciso criar: $ARGUMENTS

Stack: PHP puro monolítico, sem framework.

Execute na sequência:

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
