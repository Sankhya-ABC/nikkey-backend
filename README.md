# Portal Nikkey — Backend

API REST desenvolvida com **Laravel 10** que serve o Portal Nikkey — sistema de gestão de controle de pragas. Integra-se ao ERP **Sankhya** para sincronizar dados de clientes, ordens de serviço, produtos, metodologias e pragas, expondo endpoints para o frontend consumir.

---

## 🛠️ Tecnologias

- **PHP 8** + **Laravel 10**
- **Laravel Sanctum** (autenticação por token)
- **MySQL** (banco de dados)
- **Docker + Nginx** (containerização)
- **Guzzle HTTP** (comunicação com Sankhya)

---

## 📋 Pré-requisitos

- PHP 8.1+
- Composer
- MySQL
- Docker e Docker Compose (opcional, para execução containerizada)

---

## ⚙️ Configuração

```bash
cp .env.example .env
php artisan key:generate
```

Edite o `.env` com as configurações do banco e da integração Sankhya:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nikkey
DB_USERNAME=root
DB_PASSWORD=

# URL base do Sankhya (adicionar conforme ambiente)
SANKHYA_HOST=
SANKHYA_TOKEN=
```

---

## 🚀 Instalação e execução

**Local:**
```bash
composer install
php artisan migrate
php artisan db:seed
php artisan serve
```

**Docker:**
```bash
docker-compose up -d
# API disponível em http://localhost:61872
```

---

## 🔄 Sincronização com Sankhya

Os dados do Sankhya são importados por comandos Artisan agendados. Para rodar manualmente:

```bash
php artisan sankhya:sync-all          # Roda todas as sincronizações
php artisan sankhya:buscar-clientes
php artisan sankhya:buscar-ordens-servico
php artisan sankhya:buscar-produtos
php artisan sankhya:buscar-pragas
php artisan sankhya:buscar-metodologias
php artisan sankhya:buscar-tecnicos
# ... demais comandos
```

---

## 📦 Módulos e Recursos

| Recurso | Endpoints |
|---|---|
| **Auth** | `POST /login`, `POST /logout`, `GET /me` |
| **Clientes** | CRUD completo em `/clientes` |
| **Usuários** | CRUD + status em `/usuarios` |
| **Ordens de Serviço** | Listagem e detalhe em `/ordens-servico` |
| **Visitas** | Calendário e cronograma em `/visitas` |
| **Certificados** | Listagem e impressão em `/certificados` |
| **Dashboard Admin** | Gráficos e KPIs em `/dashboard/admin/*` |
| **Dashboard Comum** | Dados do cliente em `/dashboard/common/*` |
| **Relatórios** | 11 tipos de relatório em `/relatorios/common/*` |
| **Relatórios de Produtividade** | `/relatorios-produtividade` |

Todos os endpoints (exceto `/login`) exigem autenticação via **Bearer Token** (Laravel Sanctum).

---

## 🧩 Estrutura de Jobs

| Job | Descrição |
|---|---|
| `SyncClientesPageJob` | Sincroniza clientes paginados |
| `SyncMetodologiasJob` | Sincroniza metodologias |
| `SyncPragasJob` | Sincroniza pragas |
| `SyncProdutosJob` | Sincroniza produtos |
| `SyncBaseJob` | Job base para sincronizações |

---

## 🗄️ Banco de Dados

As migrations criam as tabelas necessárias para clientes, ordens de serviço, visitas, ambientes, pragas, produtos, técnicos, metodologias e endereços. Execute `php artisan migrate` para aplicá-las.
