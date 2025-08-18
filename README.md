# Prometheus Plugin GLPI

Plugin para obter métricas do GLPI para utilização em Query's do Prometheus, o plugin como um todo pode ser encontrado na pasta `prometheus`, e ser diretamente importado para dentro de um GLPI, esse projeto também conta com uma instância local do GLPI que pode ser rodada com `Docker Compose`, porém não é necessário para funcionamento do plugin.

## Métricas Coletadas

O plugin coleta informações de tickets, usuários, notificações, entidades, documentos, categorias, plugins e cron jobs do GLPI.

### Tickets (`glpi_tickets_*`)

* `glpi_tickets_total` → Número total de chamados
* `glpi_new_tickets_total` → Chamados com status Novo
* `glpi_atending_assigned_tickets_total` → Chamados Em atendimento (atribuído)
* `glpi_atending_planned_tickets_total` → Chamados Em atendimento (planejado)
* `glpi_pending_tickets_total` → Chamados Pendentes
* `glpi_resolved_tickets_total` → Chamados Solucionados
* `glpi_closed_tickets_total` → Chamados Fechados

### Usuários (`glpi_users_*`)

* `glpi_users_total` → Número total de usuários
* `glpi_users_active_total` → Usuários ativos
* `glpi_users_default_count_total` → Usuários com interface central (padrão)
* `glpi_users_not_central_total` → Usuários com interface simplificada

### Notificações (glpi_notifications_*)

* `glpi_notifications_total` → Total de notificações na fila
* `glpi_notifications_pending` → Notificações pendentes
* `glpi_notifications_high_age` → Notificações com mais de 20 minutos
* `glpi_notifications_high_try` → Notificações com mais de 3 tentativas

### Recursos gerais

* `glpi_total_entities` → Número total de entidades
* `glpi_total_documents` → Número total de documentos
* `glpi_total_categories` → Número total de categorias ITIL
* `glpi_plugins_total` → Número total de plugins instalados

### Cron Jobs (`glpi_<cron_name>_*`)

Para cada cron job configurado no GLPI são exportadas as métricas:

* `glpi_<cron_name>_state` → Estado do cron (ativo=1, inativo=0)
* `glpi_<cron_name>_frequency_seconds` → Frequência em segundos
* `glpi_<cron_name>_frequency_minutes` → Frequência em minutos
* `glpi_<cron_name>_frequency_hours` → Frequência em horas
* `glpi_<cron_name>_last_run_seconds` → Última execução (em segundos)
* `glpi_<cron_name>_last_run_minutes` → Última execução (em minutos)
* `glpi_<cron_name>_last_run_hours` → Última execução (em horas)
* `glpi_<cron_name>_last_run_days` → Última execução (em dias)
* `glpi_<cron_name>_run_state` → Se o cron rodou dentro do período esperado (1 = sim, 0 = não)

## Como usar
Copie a pasta prometheus para o diretório `plugins/` ou `marketplace/` do GLPI, dependendo da forma de instalação desejada.

Ative o plugin no painel do GLPI.

Acesse a rota:
```
http://<glpi_url>/plugins/prometheus/metrics.php
```
ou
```
http://<glpi_url>/marketplace/prometheus/metrics.php
```

para visualizar as métricas. Configure o Prometheus adicionando um novo scrape_config no `prometheus.yml`:
```yml
scrape_configs:
  - job_name: '<job_name>'
    metrics_path: '/<plugins|marketplace>/prometheus/metrics.php'
    static_configs:
      - targets: ['<glpi_dns>:<glpi_porta>']
```