# EDBank - Um Webapp Educacional para Simulação de Operações Bancárias e Educação Financeira

Software desenvolvido para o Trabalho de Conclusão de Curso (TCC), apresentado como exigência para obtenção do título de **Especialista em Desenvolvimento Full Stack** do **Instituto Federal do Sudeste de Minas Gerais – Campus Manhuaçu**.

**Henrique – 2025**

## Objetivo

Desenvolver uma aplicação web educacional que simule operações bancárias, permitindo que estudantes realizem transações financeiras fictícias, como transferências, pagamentos e contratação de empréstimos, com o objetivo de apoiar o ensino de educação financeira e a compreensão de conceitos como juros, saldo e planejamento financeiro.

---

## Tecnologias Utilizadas

- **PHP** – processamento e lógica de negócio  
- **HTML5** – estrutura das páginas  
- **CSS3** – estilização das interfaces  
- **Bootstrap** – layout responsivo e padronização visual  
- **JavaScript / jQuery** – interatividade e requisições assíncronas (AJAX)  
- **Banco de Dados Relacional** – persistência dos dados  

---

## Funcionalidades Principais

- Cadastro e autenticação de professores e estudantes  
- Criação de agências bancárias simuladas  
- Criação de contas bancárias vinculadas às agências  
- Realização de transferências simuladas (PIX)  
- Simulação de pagamentos de despesas  
- Simulação de contratação de empréstimos com juros  
- Cálculo automático de saldo e parcelas  
- Histórico de transações financeiras  
- Acompanhamento das movimentações pelo professor  

---

## Guia de Instalação

### Pré-requisitos

- Servidor web (Apache, Nginx ou similar)  
- PHP 8.0 ou superior com extenção mysqli, mb_string ativas
- Banco de dados relacional (MySQL ou compatível)  
- Navegador web atualizado  

### Passos para Instalação

1. Clone o repositório para a pasta do servidor
   ```bash
   git clone https://github.com/pos-FullStack/trabalho-de-conclus-o-de-curso-hcbravin
2. Configure a pasta public do servidor como a pasta public_html do repositório clonado
3. Rode o composer install para atualizar os componentes necessários
4. Importe o banco de dados da pasta Database
5. Configure os dados de acesso do banco de dados no arquivo ```config/database.php

### Uso da Aplicação

1. Ao acessar o sistema, o usuário deve realizar o cadastro como professor ou estudante.
2. O professor, após autenticação, cria uma agência bancária simulada.
3. O estudante cria uma conta bancária vinculada à agência criada pelo professor.
4. O estudante pode realizar operações financeiras simuladas, como transferências, pagamentos e contratação de empréstimos.
5. O sistema atualiza automaticamente o saldo e registra todas as movimentações.
6. O professor pode acompanhar o histórico de transações realizadas pelos estudantes, utilizando o sistema como apoio pedagógico.