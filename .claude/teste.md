# Plano: Wizard de Cadastro de Clientes
Data: 2026-03-26

## Objetivo
Criar um formulário em etapas (wizard) para cadastro de clientes no módulo proposta-negocio integrando as lógicas de backend e frontend.

## Arquivos a criar
- proposta-negocio/cliente-wizard.php: Interface HTML com as etapas do formulário.
- proposta-negocio/js/wizard.js: Controle visual das etapas, validações no cliente e envio via AJAX.

## Arquivos a modificar
- proposta-negocio/controller.php: Adicionar action `salvarClienteWizard` para lidar com a requisição POST AJAX.
- funcaosql/sqlPropostaLocacao.php: Adicionar comando `SQLInserirClienteWizard` para o SQL Server.

## Fluxo
1. Usuário acessa cliente-wizard.php e navega entre as etapas preenchendo os campos.
2. Cada avanço valida localmente os inputs via wizard.js.
3. No fim, wizard.js compila todas as informações via serialização e envia num POST AJAX estruturado.
4. O controller.php recebe, sanitiza e repassa a nova query para o `$Conn->executarQueryIUD()`.
5. Backend responde em JSON com status e a UI exibe a mensagem correspondente via SweetAlert/jQuery.

## Validações necessárias
- CPF / CNPJ: Obrigatório e com verificação prévia no banco se já existe.
- Razão Social / Nome: Obrigatório (não vazio).
- E-mail e Telefone Principal: Regex para forçar formato válido.

## Dúvidas pendentes
- Os dados desse cliente novo na proposta também precisam ser exportados como lead para o funil de vendas (módulo CRM)?
