# Plano de Refatoração — Nikkey Backend

> **Branch de trabalho:** `feature/refact-relatorios-db-local`
> **Objetivo:** Migrar todos os endpoints de DBExplorer direto (Sankhya) para banco local (MySQL), com sync incremental e repositório desacoplado.

---

## Status Geral

| Etapa | Descrição | Status |
|---|---|---|
| 0 | Fundação Técnica (token cache + retry) | ✅ Concluído |
| 1 | RangeTypeHelper + eliminação de código duplicado | ✅ Concluído |
| 2 | Repository Pattern | ✅ Concluído |
| 3 | Migração Dashboard Admin → banco local | ✅ Concluído |
| 4 | Migração Dashboard Cliente → banco local | ✅ Concluído |
| 5 | Migração Relatórios Cliente → banco local | ✅ Concluído |
| 6 | Robustez do Sync (sync_logs + incremental) | ⬜ Pendente |
| 7 | Near Real-Time (sync 3min + on-demand) | ⬜ Pendente |

---

## Etapa 0 — Fundação Técnica

**Objetivo:** Corrigir os problemas críticos da camada Sankhya antes de qualquer refatoração.

**Arquivos:**
- Criar `app/Services/Sankhya/SankhyaTokenService.php`
- Modificar `app/Services/Sankhya/SankhyaDbExplorerSPService.php`
- Modificar `app/Services/Sankhya/SankhyaLoadRecordsService.php`
- Modificar `app/Services/Sankhya/SankhyaLoadViewService.php`
- Modificar `config/services.php` (credenciais Sankhya saem do `env()` inline)
- Todos os Commands passam a injetar `SankhyaTokenService`

**O que implementar:**
- [ ] `SankhyaTokenService` com `Cache::remember('snk_token', 55*60, fn() => $this->authenticate())`
- [ ] Retry com backoff exponencial (3 tentativas: 8s / 16s / 32s)
- [ ] Verificação de `status === '1'` em todas as respostas do Sankhya
- [ ] Tratamento explícito de `CORE_E04064` (campo AD_ inexistente)
- [ ] Mover `env('SNK_*')` para `config('services.sankhya.*')`

**Critérios de aceite:**
- [ ] Apenas 1 autenticação HTTP por hora, mesmo com N requests simultâneos
- [ ] Erro de negócio do Sankhya (`status: "0"`) lança exceção com `statusMessage`
- [ ] Nenhum `env('SNK_*')` nos arquivos de `app/`

---

## Etapa 1 — RangeTypeHelper + Limpeza de Duplicações

**Objetivo:** Eliminar os 13+ blocos `if rangeType === 'month'` copiados nos controllers.

**Arquivos:**
- Criar `app/Helpers/RangeTypeHelper.php`
- Modificar `app/Http/Controllers/RelatorioCommonController.php`
- Modificar `app/Http/Controllers/DashboardAdminController.php`
- Modificar `app/Http/Controllers/DashboardCommonController.php`

**O que implementar:**
- [ ] `RangeTypeHelper::resolve(string $campo, string $rangeType): array` retornando `[select, groupBy, orderBy]`
- [ ] Remover dead code de `DashboardCommonController::getUltimaVisita` (segundo `return` inacessível)
- [ ] Implementar `DashboardCommonController::getConsumoProdutos` (atualmente retorna string `'consumo-produtos'`)

**Critérios de aceite:**
- [ ] Nenhum bloco `if ($rangeType === 'month')` nos controllers
- [ ] `getConsumoProdutos` do Common retorna dados reais

---

## Etapa 2 — Repository Pattern

**Objetivo:** Desacoplar controllers da fonte de dados para viabilizar a troca DBExplorer → banco local.

**Arquivos a criar:**
```
app/Repositories/
├── Contracts/
│   ├── DashboardAdminRepositoryInterface.php
│   ├── DashboardCommonRepositoryInterface.php
│   └── RelatorioCommonRepositoryInterface.php
├── Sankhya/
│   ├── DashboardAdminSankhyaRepository.php   ← lógica atual do controller
│   ├── DashboardCommonSankhyaRepository.php
│   └── RelatorioCommonSankhyaRepository.php
└── Local/
    ├── DashboardAdminLocalRepository.php      ← lê MySQL local (criar na Etapa 3)
    ├── DashboardCommonLocalRepository.php     ← criar na Etapa 4
    └── RelatorioCommonLocalRepository.php     ← criar na Etapa 5
```

**Arquivos modificados:**
- `app/Providers/AppServiceProvider.php` (binding por feature flag `services.sankhya.use_local_db`)
- Controllers passam a injetar a interface via construtor

**Feature flag no `.env`:**
```
SANKHYA_USE_LOCAL_DB=false
```

**Critérios de aceite:**
- [ ] Controllers sem `new SankhyaDbExplorerSPService()` inline
- [ ] Trocar `SANKHYA_USE_LOCAL_DB=true` não altera comportamento observável
- [ ] Repositórios Sankhya e Local coexistem

---

## Etapa 3 — Dashboard Admin → Banco Local

**Objetivo:** Migrar os 5 endpoints do `DashboardAdminController` para MySQL local.

**Tabelas locais necessárias:** `ordens_servico` (já existe), `produtos_utilizados` (verificar)

**Migrations:**
- [ ] Índice em `ordens_servico(dhprevista)`
- [ ] Índice em `ordens_servico(cliente_id, dhprevista)`
- [ ] Índice em `ordens_servico(tecnico_id)`

**Mapeamento:**
| Endpoint | Tabelas locais |
|---|---|
| `getBasicData` | `ordens_servico` |
| `getOrdensServico` | `ordens_servico` |
| `getAtendimentosTecnico` | `ordens_servico` + `tecnicos` |
| `getConsumoProdutos` | `produtos_utilizados` + `produtos` |
| `getProximasVisitas` | `ordens_servico` + `clientes` |

**Critérios de aceite:**
- [ ] Todos os 5 endpoints funcionando com `SANKHYA_USE_LOCAL_DB=true`
- [ ] Dashboard Admin abre com Sankhya offline
- [ ] Tempo de resposta < 200ms

---

## Etapa 4 — Dashboard Common → Banco Local

**Objetivo:** Migrar os 4 endpoints implementados do `DashboardCommonController` para MySQL local.

**Tabelas locais necessárias:** `ordens_servico`, `evidencias_pragas`, `produtos_utilizados`

**Migrations:**
- [ ] Índice em `evidencias_pragas(numos, dtev, tippraga)`
- [ ] Índice em `evidencias_pragas(codpraga)`
- [ ] Verificar se `BuscarEvidenciasPragasSankhya` está populando `individuo` e `codpraga`

**Mapeamento:**
| Endpoint | Tabelas locais |
|---|---|
| `getUltimaVisita` | `ordens_servico` WHERE `cliente_id = $id` AND `hrfin IS NOT NULL` |
| `getProximasVisitas` | `ordens_servico` WHERE `cliente_id = $id` AND `hrfin IS NULL` |
| `getFocoPragasEncontradas` | `evidencias_pragas` + `ordens_servico` WHERE `tippraga <> 'R'` |
| `getRoedoresCapturados` | `evidencias_pragas` + `produtos_utilizados` + `ordens_servico` |

**Critérios de aceite:**
- [ ] Todos os 4 endpoints funcionando com banco local
- [ ] Portal do cliente abre com Sankhya offline
- [ ] `getRoedoresCapturados` sem subquery N+1

---

## Etapa 5 — Relatórios Common → Banco Local

**Objetivo:** Migrar os 10 endpoints do `RelatorioCommonController` para MySQL local.

**Tabelas locais existentes:** `ordens_servico`, `evidencias_pragas`, `produtos_utilizados`, `pragas`, `ordens_servico_ambientes`

**Tabelas a criar:**
- [ ] Migration `nao_conformidades` + Command `BuscarNaoConformidadesSankhya`
- [ ] Migration `dominios` + Command `BuscarDominiosSankhya` (de `TDDOPC`/`TDDCAM`)

**Mapeamento de views Sankhya → tabelas locais:**
| View Sankhya | Tabela local |
|---|---|
| `AD_VGFOSE` | `ordens_servico` |
| `AD_VGFOSEEV` | `evidencias_pragas` |
| `AD_VGFOSESERPRGPRDUTIL` | `produtos_utilizados` |
| `AD_VGFOSESERPRGPTMON` | `ordens_servico_ambientes` |
| `AD_VGFOSENC` | `nao_conformidades` |
| `AD_TABPRAGAS` | `pragas` |
| `TDDOPC` / `TDDCAM` | `dominios` |

**Critérios de aceite:**
- [ ] Todos os 10 endpoints funcionando via banco local
- [ ] Nenhum `SankhyaDbExplorerSPService` nos controllers
- [ ] Tempo de resposta < 200ms por endpoint

---

## Etapa 6 — Robustez do Sync

**Objetivo:** Rastreabilidade e sync incremental por timestamp.

**Arquivos:**
- Migration `sync_logs` (`entidade`, `status`, `started_at`, `finished_at`, `total_registros`, `erro`)
- Criar `app/Services/SyncStateService.php`
- Modificar todos os 15 Commands para usar `SyncStateService`

**O que implementar:**
- [ ] Tabela `sync_logs` com rastreabilidade por entidade
- [ ] Sync incremental: `WHERE DHALTER > :last_sync_at` no LoadViewService
- [ ] Transações por página de sync (falha parcial não apaga dados)
- [ ] Command de status: `php artisan sync:status` mostra última execução de cada entidade

**Critérios de aceite:**
- [ ] Cada execução de sync registrada em `sync_logs`
- [ ] Sync incremental só busca registros alterados desde o último sucesso
- [ ] Falha em página do meio não apaga dados já inseridos

---

## Etapa 7 — Near Real-Time

**Objetivo:** Reduzir lag das OSs de 15 minutos para ~3 minutos sem infraestrutura adicional.

**Arquivos:**
- Modificar `app/Console/Kernel.php` (`BuscarOrdensServicoSankhya` de 15min → 3min)
- Criar `app/Http/Controllers/SyncController.php` (endpoint on-demand throttled)
- Adicionar rota `POST /api/sync/os/{numos}`
- Frontend: polling de 60s nos endpoints TIER 1 + header `X-Data-Age`

**O que implementar:**
- [ ] Sync de OS a cada 3 minutos com `modifiedSince` (incremental)
- [ ] Endpoint `POST /api/sync/os/{numos}` com throttle de 1x/5min por OS
- [ ] Header `X-Data-Age: Xmin` nas respostas dos endpoints de dashboard
- [ ] Frontend polling 60s para dados TIER 1 (próximas visitas, status OS)

**Critérios de aceite:**
- [ ] OS finalizada no Sankhya aparece no portal em até 4 minutos
- [ ] Sync incremental de OS transfere < 10% do volume atual
- [ ] Endpoint on-demand retorna erro 429 se chamado mais de 1x por 5min para mesma OS

---

## Arquitetura de Dados por Tier

```
TIER 1 — Sync a cada 3min    → ordens_servico (status, próximas visitas)
TIER 2 — Sync a cada 30min   → evidencias_pragas, produtos_utilizados, ambientes
TIER 3 — Sync diário (03h)   → pragas, produtos, clientes, metodologias, catálogos
```

---

## Notas de Implementação

- **Controllers Admin e Common não serão unificados** — têm colunas, JOINs e escopo distintos. Admin: visão agregada de todos os clientes. Common: sempre filtrado por `CODPARC` do cliente autenticado.
- **WebSocket/SSE não está no plano atual** — a combinação sync 3min + polling 60s + on-demand entrega lag máximo de ~4min sem infra adicional. Reverb pode ser adicionado depois sobre esta arquitetura sem alterar controllers.
- **Feature flag `SANKHYA_USE_LOCAL_DB`** permite migrar endpoint por endpoint sem alterar controllers.
- **SQL Server-specific syntax** (`FORMAT`, `ISNULL`, `CONVERT`, `TOP`) fica nos repositórios Sankhya e não precisa ser portado para o banco local — Eloquent/MySQL substitui naturalmente.
