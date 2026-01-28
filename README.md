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

# 3. Suba os containers (tudo é configurado automaticamente!)
docker-compose up -d
```

> **✨ Pronto!** O Docker irá automaticamente:
> - Instalar dependências PHP (composer)
> - Instalar dependências Node.js (npm)
> - Gerar chave da aplicação
> - Criar e popular o banco de dados
> - Configurar rotas Ziggy
> - Compilar assets Vue.js com Vite
> - Ajustar permissões

Aguarde alguns instantes e acesse: **http://localhost:8000**

### Primeiro Acesso

**Credenciais padrão:**
- Email: `admin@erp.com`
- Senha: `password`

## Acessar

- **App:** http://localhost:8000
- **Emails (Mailpit):** http://localhost:8025

---

## Tecnologias

- **Backend:** Laravel 12 (PHP 8.3)
- **Frontend:** Vue.js 3 + Inertia.js
- **Styling:** Tailwind CSS + DaisyUI
- **Database:** MySQL 8.0
- **Cache/Queue:** Redis
- **Build Tool:** Vite
- **Email Testing:** Mailpit

---

## Comandos Úteis

```bash
# Ver logs em tempo real
docker-compose logs -f app      # Laravel/PHP
docker-compose logs -f node     # Vite/Vue.js
docker-compose logs -f nginx    # Web server

# Reiniciar do zero (apaga TODOS os dados!)
docker-compose down -v
docker-compose up -d
```

