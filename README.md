# Payment API

API de processamento de pagamentos desenvolvida em **Laravel**, com suporte a múltiplos gateways, filas assíncronas, controle de estoque e sistema de reembolso.

O objetivo do projeto é simular uma **plataforma de pagamentos resiliente**, capaz de:

* Processar compras
* Utilizar múltiplos gateways de pagamento
* Utilizar **fila para processamento assíncrono**
* Controlar estoque de produtos
* Registrar transações
* Realizar **reembolsos**
* Autenticação de usuários via token

---

# Tecnologias Utilizadas

* PHP 8.2
* Laravel
* MySQL
* Docker
* Laravel Queue
* Laravel Sanctum
* HTTP Client (Laravel)
* Gateways Mock

---

# Arquitetura

A aplicação foi organizada utilizando **separação de responsabilidades**, com serviços responsáveis pela lógica de negócio.

Estrutura principal:

Controllers
Responsáveis por receber requisições HTTP e retornar respostas.

Services
Contêm a lógica de negócio do sistema.

Jobs
Responsáveis por executar pagamentos de forma assíncrona.

Gateways
Implementação da estratégia para integração com diferentes gateways.

Models
Representação das entidades do banco de dados.

---

Controle de Acesso (RBAC)

A API implementa controle de acesso baseado em papéis (Role Based Access Control - RBAC) para garantir que apenas usuários autorizados possam acessar determinadas funcionalidades do sistema.

Cada usuário possui um campo role que define seu nível de permissão.

Papéis disponíveis:

ADMIN
MANAGER
FINANCE
USER

---

# Fluxo de Pagamento

1. Cliente envia requisição de compra
2. Sistema valida estoque
3. Transação é criada com status **pending**
4. Job é enviado para **fila de processamento**
5. Worker tenta processar pagamento nos gateways disponíveis
6. Se aprovado:

   * status → approved
   * external_id salvo
7. Se todos falharem:

   * status → failed

Essa estratégia permite **alta resiliência**, pois se um gateway falhar o sistema tenta outro automaticamente.

---

# Sistema de Gateways

A aplicação possui suporte para múltiplos gateways utilizando o padrão **Strategy**.

GatewayFactory cria dinamicamente o serviço correto:

* Gateway1Service
* Gateway2Service

A prioridade de execução é definida na tabela **gateways**.

---

# Processamento Assíncrono

O pagamento não é processado diretamente na requisição HTTP.

Em vez disso:

1. A transação é criada
2. Um **ProcessPaymentJob** é enviado para a fila
3. O worker processa a cobrança

Configuração do Job:

* 5 tentativas
* backoff progressivo

```
public $tries = 5;
public $backoff = [10, 30, 60];
```

---

# Banco de Dados

Principais tabelas:

users
Controle de acesso ao sistema.

clients
Clientes que realizam compras.

products
Produtos disponíveis para compra.

transactions
Registro das transações de pagamento.

transaction_products
Relacionamento entre produtos e transações.

gateways
Gateways de pagamento disponíveis.

---

# Como Rodar o Projeto

### 1 - Clonar repositório

```
git clone <repo>
cd payment-api
```

---

### 2 - Subir containers

```
docker-compose up -d
```

---

### 3 - Instalar dependências

```
docker exec -it payment_api_app composer install
```

---

### 4 - Configurar ambiente

Copiar arquivo:

```
cp .env.example .env
```

Gerar chave:

```
php artisan key:generate
```

---

### 5 - Rodar migrations

```
php artisan migrate
```

---

### 6 - Inserir gateways

Exemplo via tinker:

```
php artisan tinker
```

```
Gateway::create([
"name" => "gateway1",
"priority" => 1,
"is_active" => true
]);

Gateway::create([
"name" => "gateway2",
"priority" => 2,
"is_active" => true
]);
```

---

### 7 - Iniciar worker da fila

```
php artisan queue:work
```

---

# Autenticação

A autenticação é feita utilizando **Laravel Sanctum**.

Login retorna um token Bearer.

Exemplo de resposta:

```
{
"access_token": "token",
"token_type": "Bearer"
}
```

O token deve ser enviado no header:

```
Authorization: Bearer TOKEN
```

---

# Endpoints

## Auth

POST /login

Realiza autenticação do usuário.

---

## Users

GET /users
Lista usuários

POST /users
Cria usuário

GET /users/{id}

PUT /users/{id}

DELETE /users/{id}

---

## Products

GET /products

POST /products

GET /products/{id}

PUT /products/{id}

DELETE /products/{id}

---

## Clients

GET /clients

GET /clients/{id}

Retorna cliente com histórico de transações.

---

## Transactions

GET /transactions

GET /transactions/{id}

---

## Purchase

POST /purchase

Exemplo:

```
{
"name": "Kalebe",
"email": "kalebe@email.com",
"cardNumber": "4111111111111111",
"cvv": "123",
"products": [
{
"product_id": 1,
"quantity": 2
}
]
}
```

Resposta:

```
{
"status": "processing",
"transaction_id": 1
}
```

---

## Refund

POST /transactions/{id}/refund

Realiza reembolso da transação.

---

# Controle de Estoque

Ao realizar uma compra:

1. O sistema valida o estoque
2. Após a criação da transação
3. O estoque do produto é decrementado

```
$product->decrement('amount', $item['quantity']);
```

---

# Estratégias de Resiliência

O sistema implementa:

* Failover entre gateways
* Processamento assíncrono
* Retry automático
* Separação de responsabilidades
* Logs de processamento

---

# Melhorias Futuras

* Implementar circuit breaker para gateways
* Monitoramento de filas
* Cache de gateways ativos
* Rate limit de pagamentos
* Dashboard administrativo
* Testes automatizados

---

# Considerações Finais

Todas as funcionalidades principais do teste foram implementadas:

* Autenticação
* CRUD de usuários
* CRUD de produtos
* Controle de clientes
* Sistema de pagamentos
* Integração com gateways
* Processamento em fila
* Reembolso de transações

Caso algum ponto não tenha sido completamente explorado, ele foi documentado na seção de melhorias futuras.

---

# Autor

Kalebe Felix
Desenvolvedor Full Stack
