# ERP Pedidos

Sistema de gestão de pedidos - Laravel + Vue.js + Docker

## Pré-requisitos

```bash
# Instalar Docker e Docker Compose
sudo apt update
sudo apt install docker.io docker-compose -y
sudo usermod -aG docker $USER
```

> **Importante:** Reinicie o terminal após adicionar seu usuário ao grupo docker

## Como Rodar

```bash
# 1. Clone o projeto
git clone git@github.com:gustavobotti/erp-pedidos.git
cd erp-pedidos

# 2. Configure o ambiente
cp .env.example .env

# 3. Suba os containers
docker-compose up -d

# 4. Instale dependências
docker-compose exec app composer install

# 5. Configure o Laravel
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --seed

# 6. Ajuste permissões
docker-compose exec app chown -R erp:erp /var/www
docker-compose exec app chmod -R 775 /var/www/storage /var/www/bootstrap/cache
```

## Acessar

- **App:** http://localhost:8000
- **Emails:** http://localhost:8025

## Comandos Úteis

```bash
# Ver logs
docker-compose logs -f app

# Reiniciar do zero
docker-compose down -v
docker-compose up -d
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --seed

# Limpar cache
docker-compose exec app php artisan cache:clear
```

