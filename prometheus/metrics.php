<?php

include('../../inc/includes.php');

$pluginName = "prometheus";
$pluginHandler = new Plugin();

if (!$pluginHandler->isInstalled($pluginName)) {
    http_response_code(404);
    exit;
}

header('Content-Type: text/plain; version=0.0.4; charset=utf-8; escaping=underscores');

if (!Plugin::isPluginActive($pluginName)) {
    echo "Plugin '{$pluginName}' is inactive!";
    exit;
}

function get_data() {
    global $DB;
    
    $sql = "
        SELECT
            COUNT(*) AS total,
            COALESCE(SUM(CASE WHEN sent_time IS NULL AND is_deleted = 0 THEN 1 ELSE 0 END), 0) AS pending,
            COALESCE(SUM(
                CASE 
                    WHEN sent_time IS NULL 
                        AND is_deleted = 0
                        AND TIMESTAMPDIFF(SECOND, create_time, NOW()) > 1200
                    THEN 1 
                    ELSE 0 
                END
            ), 0) AS high_age,
            COALESCE(SUM(
                CASE 
                    WHEN sent_time IS NULL 
                        AND is_deleted = 0
                        AND sent_try > 3
                    THEN 1 
                    ELSE 0 
                END
            ), 0) AS high_try
        FROM glpi_queuednotifications
        WHERE recipient LIKE '%@%';
    ";
    
    $result = $DB->query($sql);
    $notifications = $result->fetch_assoc();
    
    $sql = "
        SELECT
            SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS new,
            SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) AS atending_assigned,
            SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) AS atending_planned,
            SUM(CASE WHEN status = 4 THEN 1 ELSE 0 END) AS pending,
            SUM(CASE WHEN status = 5 THEN 1 ELSE 0 END) AS resolved,
            SUM(CASE WHEN status = 6 THEN 1 ELSE 0 END) AS closed
        FROM glpi_tickets;
    ";
    
    $result = $DB->query($sql);
    $tickets = $result->fetch_assoc();
    
    $sql = "
        SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS new,
            SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) AS atending_assigned,
            SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) AS atending_planned,
            SUM(CASE WHEN status = 4 THEN 1 ELSE 0 END) AS pending,
            SUM(CASE WHEN status = 5 THEN 1 ELSE 0 END) AS resolved,
            SUM(CASE WHEN status = 6 THEN 1 ELSE 0 END) AS closed
        FROM glpi_tickets;
    ";
    
    $result = $DB->query($sql);
    $tickets = $result->fetch_assoc();

    $sql = "
        SELECT
            COUNT(*) AS total,
            SUM(is_active) AS active
        FROM glpi_users;
    ";

    $result = $DB->query($sql);
    $users_data = $result->fetch_assoc();

    $sql = "
        SELECT 
            u.id AS user_id,
            GROUP_CONCAT(p.interface SEPARATOR ', ') AS profiles_interfaces
        FROM 
            glpi_users u
        JOIN 
            glpi_profiles_users pu ON u.id = pu.users_id
        JOIN 
            glpi_profiles p ON pu.profiles_id = p.id
        WHERE
            u.is_active = 1
        GROUP BY 
            u.id;
    ";

    $result = $DB->query($sql);
    $users = $result->fetch_all(MYSQLI_ASSOC);

    $sql = "
        SELECT id, name, frequency, lastrun, state FROM glpi_crontasks;
    ";

    $result = $DB->query($sql);
    $crons = $result->fetch_all(MYSQLI_ASSOC);

    $default_view_user_count = 0;
    $basic_view_user_count = 0;
    foreach ($users as $user) {
        if (strpos($user['profiles_interfaces'], 'central') !== false) {
            $default_view_user_count++;
        } else {
            $basic_view_user_count++;
        }
    }
    
    $total_plugins = countElementsInTable('glpi_plugins');
    $total_entities = countElementsInTable('glpi_entities');
    $total_docs = countElementsInTable('glpi_documents');
    $total_categories = countElementsInTable('glpi_itilcategories');

    return [
        "tickets" => $tickets,
        "notifications" => $notifications,
        "users" => [
            "total" => $users_data['total'],
            "active" => $users_data['active'],
            "default_view_count" => $default_view_user_count,
            "basic_view_count" => $basic_view_user_count
        ],
        "total_plugins" => $total_plugins,
        "total_entities" => $total_entities,
        "total_docs" => $total_docs,
        "total_categories" => $total_categories,
        "cron_jobs" => $crons
    ];
}

$data = get_data();

echo "# HELP glpi_total_entities Número total de entidades\n";
echo "# TYPE glpi_total_entities counter\n";
echo "glpi_total_entities {$data['total_entities']}\n\n";

echo "# HELP glpi_total_documents Número total de documentos\n";
echo "# TYPE glpi_total_documents counter\n";
echo "glpi_total_documents {$data['total_docs']}\n\n";

echo "# HELP glpi_total_categories Número total de categorias\n";
echo "# TYPE glpi_total_categories counter\n";
echo "glpi_total_categories {$data['total_categories']}\n\n";

echo "# HELP glpi_users_total Número total de usuários\n";
echo "# TYPE glpi_users_total counter\n";
echo "glpi_users_total {$data['users']['total']}\n\n";

echo "# HELP glpi_users_active_total Número total de usuários ativos\n";
echo "# TYPE glpi_users_active_total counter\n";
echo "glpi_users_active_total {$data['users']['active']}\n\n";

echo "# HELP glpi_users_default_count_total Número total de usuários com interface padrão\n";
echo "# TYPE glpi_users_default_count_total counter\n";
echo "glpi_users_default_count_total {$data['users']['default_view_count']}\n\n";

echo "# HELP glpi_users_not_central_total Número total de usuários com interface simplificada\n";
echo "# TYPE glpi_users_not_central_total counter\n";
echo "glpi_users_not_central_total {$data['users']['basic_view_count']}\n\n";

echo "# HELP glpi_tickets_total Número total de chamados\n";
echo "# TYPE glpi_tickets_total counter\n";
echo "glpi_tickets_total {$data['tickets']['total']}\n\n";

echo "# HELP glpi_new_tickets_total Número total de chamados com status: `Novo`\n";
echo "# TYPE glpi_new_tickets_total counter\n";
echo "glpi_new_tickets_total {$data['tickets']['new']}\n\n";

echo "# HELP glpi_atending_assigned_tickets_total Número total de chamados com status: `Em atendimento (atribuído)`\n";
echo "# TYPE glpi_atending_assigned_tickets_total counter\n";
echo "glpi_atending_assigned_tickets_total {$data['tickets']['atending_assigned']}\n\n";

echo "# HELP glpi_atending_planned_tickets_total Número total de chamados com status: `Em atendimento (planejado)`\n";
echo "# TYPE glpi_atending_planned_tickets_total counter\n";
echo "glpi_atending_planned_tickets_total {$data['tickets']['atending_planned']}\n\n";

echo "# HELP glpi_pending_tickets_total Número total de chamados com status: `Pendente`\n";
echo "# TYPE glpi_pending_tickets_total counter\n";
echo "glpi_pending_tickets_total {$data['tickets']['pending']}\n\n";

echo "# HELP glpi_resolved_tickets_total Número total de chamados com status: `Solucionado`\n";
echo "# TYPE glpi_resolved_tickets_total counter\n";
echo "glpi_resolved_tickets_total {$data['tickets']['resolved']}\n\n";

echo "# HELP glpi_closed_tickets_total Número total de chamados com status: `Fechado`\n";
echo "# TYPE glpi_closed_tickets_total counter\n";
echo "glpi_closed_tickets_total {$data['tickets']['closed']}\n\n";

echo "# HELP glpi_plugins_total Número total de plugins\n";
echo "# TYPE glpi_plugins_total counter\n";
echo "glpi_plugins_total {$data['total_plugins']}\n\n";

echo "# HELP glpi_notifications_total Total de notificações na fila\n";
echo "# TYPE glpi_notifications_total counter\n";
echo "glpi_notifications_total {$data['notifications']['total']}\n\n";

echo "# HELP glpi_notifications_pending Notificações pendentes\n";
echo "# TYPE glpi_notifications_pending gauge\n";
echo "glpi_notifications_pending {$data['notifications']['pending']}\n\n";

echo "# HELP glpi_notifications_high_age Notificações com mais de 20 minutos\n";
echo "# TYPE glpi_notifications_high_age gauge\n";
echo "glpi_notifications_high_age {$data['notifications']['high_age']}\n\n";

echo "# HELP glpi_notifications_high_try Notificações com mais de 3 tentativas\n";
echo "# TYPE glpi_notifications_high_try gauge\n";
echo "glpi_notifications_high_try {$data['notifications']['high_try']}\n\n";

foreach ($data['cron_jobs'] as $cron) {
    echo "# HELP glpi_{$cron['name']}_state Estado do cronjob `{$cron['name']}` (ativo = 1, inativo = 0)\n";
    echo "# TYPE glpi_{$cron['name']}_state gauge\n";
    echo "glpi_{$cron['name']}_state {$cron['state']}\n\n";
    
    echo "# HELP glpi_{$cron['name']}_frequency_seconds Frequência do cronjob `{$cron['name']}` em segundos\n";
    echo "# TYPE glpi_{$cron['name']}_frequency_seconds gauge\n";
    echo "glpi_{$cron['name']}_frequency_seconds {$cron['frequency']}\n\n";
    
    echo "# HELP glpi_{$cron['name']}_frequency_minutes Frequência do cronjob `{$cron['name']}` em minutos\n";
    echo "# TYPE glpi_{$cron['name']}_frequency_minutes gauge\n";
    echo "glpi_{$cron['name']}_frequency_minutes " . ($cron['frequency'] / 60) . "\n\n";
    
    echo "# HELP glpi_{$cron['name']}_frequency_hours Frequência do cronjob `{$cron['name']}` em horas\n";
    echo "# TYPE glpi_{$cron['name']}_frequency_hours gauge\n";
    echo "glpi_{$cron['name']}_frequency_hours " . ($cron['frequency'] / 60 / 60) . "\n\n";
    
    echo "# HELP glpi_{$cron['name']}_last_run_seconds Última vez que o cronjob `{$cron['name']}` rodou em segundos\n";
    echo "# TYPE glpi_{$cron['name']}_last_run_seconds gauge\n";
    echo "glpi_{$cron['name']}_last_run_seconds " . strtotime($cron['lastrun']) * 1 . "\n\n";
    
    echo "# HELP glpi_{$cron['name']}_last_run_minutes Última vez que o cronjob `{$cron['name']}` rodou em minutos\n";
    echo "# TYPE glpi_{$cron['name']}_last_run_minutes gauge\n";
    echo "glpi_{$cron['name']}_last_run_minutes " . strtotime($cron['lastrun']) * 1 / 60 . "\n\n";
    
    echo "# HELP glpi_{$cron['name']}_last_run_hours Última vez que o cronjob `{$cron['name']}` rodou em horas\n";
    echo "# TYPE glpi_{$cron['name']}_last_run_hours gauge\n";
    echo "glpi_{$cron['name']}_last_run_hours " . strtotime($cron['lastrun']) * 1 / 60 / 60 . "\n\n";
    
    echo "# HELP glpi_{$cron['name']}_last_run_days Última vez que o cronjob `{$cron['name']}` rodou em dias\n";
    echo "# TYPE glpi_{$cron['name']}_last_run_days gauge\n";
    echo "glpi_{$cron['name']}_last_run_days " . strtotime($cron['lastrun']) * 1 / 60 / 60 / 24 . "\n\n";
    
    echo "# HELP glpi_{$cron['name']}_run_state Dito se o cronjob `{$cron['name']}` rodou no período esperado (sim = 1, não = 0)\n";
    echo "# TYPE glpi_{$cron['name']}_run_state gauge\n";
    echo "glpi_{$cron['name']}_run_state " . intval((strtotime($cron['lastrun']) * 1 + $cron['frequency']) >= time()) . "\n\n";
}