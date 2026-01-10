# ERP Pedidos - Sistema de Gestão de Pedidos para Fornecedores

Sistema ERP para gestão de pedidos de fornecedores desenvolvido em Laravel 12 com Vue.js e Inertia.js.

## 🛠️ Tecnologias Utilizadas

- **Backend**: Laravel 12
- **Frontend**: Vue.js 3 + Inertia.js + DaisyUI
- **Banco de Dados**: MySQL 8.0
- **Cache**: Redis
- **Email Testing**: Mailpit
- **Container**: Docker & Docker Compose

## 📋 Pré-requisitos

- Docker
- Docker Compose
- Git

## 🚀 Como Rodar o Projeto

### 1. Clone o repositório

```bash
git clone -
cd erp-pedidos
```

### 2. Configure as permissões (se necessário)

```bash
sudo chown -R $USER:$USER .
chmod -R 755 storage bootstrap/cache
```

### 3. Suba os containers Docker

```bash
sudo docker compose up -d
```

### 4. Instale as dependências do PHP

```bash
sudo docker compose exec app composer install
```

### 5. Configure as permissões do Laravel

```bash
sudo docker compose exec app chown -R erp:erp /var/www
sudo docker compose exec app chmod -R 775 /var/www/storage /var/www/bootstrap/cache
```

### 6. Gere a chave da aplicação (se ainda não foi gerada)

```bash
sudo docker compose exec app php artisan key:generate
```

### 7. Execute as migrations

```bash
sudo docker compose exec app php artisan migrate
```

### 8. (Opcional) Execute os seeders

```bash
sudo docker compose exec app php artisan db:seed
```

## 🌐 Acessando a Aplicação

Após subir os containers, a aplicação estará disponível em:

- **Aplicação Web**: http://localhost:8000
- **Mailpit (Interface de Email)**: http://localhost:8025
- **Vite Dev Server**: http://localhost:5173 (usado automaticamente pelo Laravel)

## 📦 Serviços Docker

| Serviço | Container | Porta | Descrição |
|---------|-----------|-------|-----------|
| Nginx | erp_nginx | 8000 | Servidor web |
| PHP-FPM | erp_app | 9000 | Aplicação Laravel |
| MySQL | erp_mysql | 3306 | Banco de dados |
| Redis | erp_redis | 6379 | Cache e filas |
| Mailpit | erp_mailpit | 8025 (Web), 1025 (SMTP) | Servidor de email para testes |
| Node | erp_node | 5173 | Vite dev server |
