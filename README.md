# ERP Pedidos

Sistema de gestão de pedidos para fornecedores com Laravel 12 + Vue.js 3 + Inertia.js.

## Stack

**Backend:** Laravel 12 (PHP 8.3) • MySQL 8.0 • Redis  
**Frontend:** Vue.js 3 • Inertia.js • Vite • Tailwind CSS
**Infra:** Docker • Nginx • Supervisor • Mailpit

## Pré-requisitos

- Docker Compose
- Git

## Instalação

**1. Clone e configure o ambiente:**
```bash
git clone git@github.com:gustavobotti/erp-pedidos.git
cd erp-pedidos
cp .env.example .env
```

**2. Edite o `.env` com as credenciais do Docker:**
```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=erp_pedidos
DB_USERNAME=erp_user
DB_PASSWORD=erp_password

REDIS_HOST=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis

MAIL_HOST=mailpit
MAIL_PORT=1025
```

**3. Suba os containers:**
```bash
docker compose up -d
```

**4. Instale dependências e configure:**
```bash
# Dependências PHP
docker compose exec app composer install

# Gerar APP_KEY
docker compose exec app php artisan key:generate

# Rodar migrations
docker compose exec app php artisan migrate

# Seeders (opcional - cria dados de exemplo)
docker compose exec app php artisan db:seed
```

**5. Ajustar permissões (Linux/Mac):**
```bash
docker compose exec app chown -R erp:erp /var/www
docker compose exec app chmod -R 775 /var/www/storage /var/www/bootstrap/cache
```

## Acesso

| Serviço | URL |
|---------|-----|
| **Aplicação** | http://localhost:8000 |
| **Mailpit** | http://localhost:8025 |
| **Vite (dev)** | http://localhost:5173 |

## Comandos Úteis

```bash
# Logs
docker compose logs -f app

# Artisan
docker compose exec app php artisan [comando]

# Composer
docker compose exec app composer [comando]

# Limpar cache
docker compose exec app php artisan cache:clear

# Resetar banco com seeders
docker compose exec app php artisan migrate:fresh --seed

# Parar containers
docker compose stop

# Remover tudo
docker compose down -v
```

## Containers

| Container | Função |
|-----------|--------|
| **erp_app** | PHP 8.3-FPM + Supervisor (workers) |
| **erp_nginx** | Servidor web (porta 8000) |
| **erp_mysql** | Banco de dados |
| **erp_redis** | Cache e filas |
| **erp_mailpit** | Captura emails (dev) |
| **erp_node** | Vite dev server (hot reload) |

## Recursos

- Supervisor configurado para Laravel Queue Workers
- Redis para cache e filas
- Volumes persistentes (mysql_data, vendor_data, node_modules_data)
- Hot reload automático com Vite
