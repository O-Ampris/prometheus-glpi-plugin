<?php

include('../../inc/includes.php');


$pluginName = "prometheus";
$pluginHandler = new Plugin();

if (!$pluginHandler->isInstalled($pluginName)) {
    http_response_code(404);
    exit;
}

header('Content-Type: text/plain;');

if (!Plugin::isPluginActive($pluginName)) {
    echo "Plugin '{$pluginName}' is inactive!";
    exit;
}

global $DB;

// $sql = "
// SELECT
//     COUNT(*) AS total_notifications,
//     SUM(CASE WHEN sent_time IS NULL AND is_deleted = 0 THEN 1 ELSE 0 END) AS total_pending_notifications,
//     SUM(
//         CASE 
//             WHEN sent_time IS NULL 
//                 AND is_deleted = 0
//                 AND TIMESTAMPDIFF(SECOND, create_time, NOW()) > 1200
//             THEN 1 
//             ELSE 0 
//         END
//     ) AS high_age_notifications,
//     SUM(
//         CASE 
//             WHEN sent_time IS NULL 
//                 AND is_deleted = 0
//                 AND sent_try > 3
//             THEN 1 
//             ELSE 0 
//         END
//     ) AS high_try_notifications
// FROM glpi_queuednotifications
// WHERE recipient LIKE '%@%'
// ";

// $result = $DB->query($sql);

// if ($result && $result->num_rows > 0) {
//     $row = $result->fetch_assoc();

//     echo "# HELP glpi_notifications_total Total de notificações\n";
//     echo "# TYPE glpi_notifications_total gauge\n";
//     echo "glpi_notifications_total {$row['total_notifications']}\n\n";

//     echo "# HELP glpi_notifications_pending Notificações pendentes\n";
//     echo "# TYPE glpi_notifications_pending gauge\n";
//     echo "glpi_notifications_pending {$row['total_pending_notifications']}\n\n";

//     echo "# HELP glpi_notifications_high_age Notificações pendentes há mais de 20min\n";
//     echo "# TYPE glpi_notifications_high_age gauge\n";
//     echo "glpi_notifications_high_age {$row['high_age_notifications']}\n\n";

//     echo "# HELP glpi_notifications_high_try Notificações com mais de 3 tentativas\n";
//     echo "# TYPE glpi_notifications_high_try gauge\n";
//     echo "glpi_notifications_high_try {$row['high_try_notifications']}\n\n";
// } else {
//     http_response_code(500);
//     echo "Erro ao executar consulta de métricas";
// }

// $DBread = DBConnection::getReadConnection();

$sql = "
SELECT
    COUNT(*) AS total_notifications,
    COALESCE(SUM(CASE WHEN sent_time IS NULL AND is_deleted = 0 THEN 1 ELSE 0 END), 0) AS total_pending_notifications,
    COALESCE(SUM(
        CASE 
            WHEN sent_time IS NULL 
                 AND is_deleted = 0
                 AND TIMESTAMPDIFF(SECOND, create_time, NOW()) > 1200
            THEN 1 
            ELSE 0 
        END
    ), 0) AS high_age_notifications,
    COALESCE(SUM(
        CASE 
            WHEN sent_time IS NULL 
                 AND is_deleted = 0
                 AND sent_try > 3
            THEN 1 
            ELSE 0 
        END
    ), 0) AS high_try_notifications
FROM glpi_queuednotifications
WHERE recipient LIKE '%@%';
";

$result = $DB->query($sql);
$data = $result->fetch_assoc();


echo "# HELP glpi_notifications_total Total de notificações na fila\n";
echo "# TYPE glpi_notifications_total gauge\n";
echo "glpi_notifications_total {$data['total_notifications']}\n\n";

echo "# HELP glpi_notifications_pending Notificações pendentes\n";
echo "# TYPE glpi_notifications_pending gauge\n";
echo "glpi_notifications_pending {$data['total_pending_notifications']}\n\n";

echo "# HELP glpi_notifications_high_age Notificações com mais de 20 minutos\n";
echo "# TYPE glpi_notifications_high_age gauge\n";
echo "glpi_notifications_high_age {$data['high_age_notifications']}\n\n";

echo "# HELP glpi_notifications_high_try Notificações com mais de 3 tentativas\n";
echo "# TYPE glpi_notifications_high_try gauge\n";
echo "glpi_notifications_high_try {$data['high_try_notifications']}\n\n";