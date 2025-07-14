<?php
session_start();
date_default_timezone_set('America/Lima');

// Parámetros de conexión
$host = 'your-db-host';
$port = `your-db-port`;
$dbname = 'your-db-name';
$username = 'your-db-user';
$password = 'your-db-password';

// Crear conexión
$conn = new mysqli($host, $username, $password, $dbname, $port);

// Verificar conexión
if ($conn->connect_error) {
    http_response_code(500);
    echo "<div style='color:red'>Conexión fallida: " . $conn->connect_error . "</div>";
    exit();
}

$conn->set_charset("utf8mb4");

// Consulta a la tabla "streams"
$sql = "SELECT id, stream_display_name FROM streams WHERE category_id = 1 ORDER BY id ASC";
$result = $conn->query($sql);

$canales = [];
$canal_ids = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['stream_display_name'] = json_decode('"' . $row['stream_display_name'] . '"');
        $canales[] = $row;
        $canal_ids[] = $row['id'];
    }
}

// Obtener estados ONLINE/OFFLINE de streams_sys
$estados = [];
if (count($canal_ids) > 0) {
    $placeholders = implode(',', array_fill(0, count($canal_ids), '?'));
    $sql_status = "SELECT stream_id, stream_status FROM streams_sys WHERE stream_id IN ($placeholders)";
    $stmt_status = $conn->prepare($sql_status);
    $types = str_repeat('i', count($canal_ids));
    $stmt_status->bind_param($types, ...$canal_ids);
    $stmt_status->execute();
    $result_status = $stmt_status->get_result();
    while ($row = $result_status->fetch_assoc()) {
        $estados[$row['stream_id']] = $row['stream_status'];
    }
    $stmt_status->close();
}
$conn->close();

// Contadores
$total = count($canales);
$online = 0;
$offline = 0;
foreach ($canales as $c) {
    $estado = $estados[$c['id']] ?? 1;
    if ($estado == 0) {
        $online++;
    } else {
        $offline++;
    }
}

// Incluir PHPMailer
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Array para alertas visuales
$alertas = [];

// Función para enviar correo
function enviarAlertaCanal($canal, $estado, &$alertas) {
    // IMPORTANTE: Configura estos valores en variables de entorno o en un archivo de configuración externo
    $correo_origen     = 'your-email@example.com';
    $correo_destino    = 'your-email@example.com';
    $nombre_remitente  = '📡 Your System Name';
    $token_aplicacion  = 'your-app-token-here';
    $hora              = date('d/m/Y H:i:s');

    if ($estado === 'OFFLINE') {
        $asunto = "📡🚨 Canal \"$canal\" caído";
        $cuerpo = "🕒 Canal \"$canal\" caído a las $hora";
        $tipo_alerta = 'error';
        $msg_alerta = "Se envió alerta: Canal <b>$canal</b> está <span style='color:#ff5252;'>CAÍDO</span> a las $hora.";
    } else {
        $asunto = "📡✅ Canal \"$canal\" restablecido";
        $cuerpo = "🕒 Canal \"$canal\" restablecido a las $hora";
        $tipo_alerta = 'ok';
        $msg_alerta = "Se envió alerta: Canal <b>$canal</b> fue <span style='color:#4caf50;'>RESTABLECIDO</span> a las $hora.";
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $correo_origen;
        $mail->Password = $token_aplicacion;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->Timeout = 10; // Timeout más corto para respuesta rápida

        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        $mail->setFrom($correo_origen, $nombre_remitente);
        $mail->addAddress($correo_destino);

        $mail->isHTML(false);
        $mail->Subject = $asunto;
        $mail->Body    = $cuerpo;

        $mail->send();
        $alertas[] = [
            'tipo' => $tipo_alerta,
            'msg' => $msg_alerta
        ];
    } catch (Exception $e) {
        $alertas[] = [
            'tipo' => 'error',
            'msg' => "Error al enviar correo para <b>$canal</b>: " . $mail->ErrorInfo
        ];
    }
}

// Archivo de estado
define('ESTADO_FILE', __DIR__ . '/estado_canales.json');

// Leer estado anterior
$estado_anterior = [];
if (file_exists(ESTADO_FILE)) {
    $json = file_get_contents(ESTADO_FILE);
    $estado_anterior = json_decode($json, true) ?: [];
}

// Detectar cambios y enviar correos
$estado_actual = [];
$cambios_detectados = 0;

foreach ($canales as $canal) {
    $id = $canal['id'];
    $nombre = $canal['stream_display_name'];
    $estado = $estados[$id] ?? 1;
    $status = ($estado == 0) ? 'ONLINE' : 'OFFLINE';
    $estado_actual[$id] = $status;
    
    $anterior = $estado_anterior[$id] ?? null;
    
    // Detectar cualquier cambio de estado (ONLINE ↔ OFFLINE)
    if ($anterior !== null && $anterior !== $status) {
        $cambios_detectados++;
        enviarAlertaCanal($nombre, $status, $alertas);
    }
    // Si es la primera vez y está offline, enviar alerta inmediatamente
    elseif ($anterior === null && $status === 'OFFLINE') {
        $cambios_detectados++;
        enviarAlertaCanal($nombre, $status, $alertas);
    }
}

// Guardar el estado actual (siempre se actualiza)
file_put_contents(ESTADO_FILE, json_encode($estado_actual));

// Log para debugging
$log_file = __DIR__ . '/monitor_log.txt';
$log_entry = date('Y-m-d H:i:s') . " - Canales: $total, Online: $online, Offline: $offline, Cambios: $cambios_detectados, Alertas: " . count($alertas) . "\n";
file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor de Canales IPTV</title>
    <style>
        body {
            background: linear-gradient(135deg, #232526 0%, #414345 100%);
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            background: rgba(30,30,40,0.98);
            border-radius: 16px;
            box-shadow: 0 8px 32px 0 rgba(31,38,135,0.37);
            padding: 32px 24px 24px 24px;
        }
        h1 {
            color: #fff;
            text-align: center;
            margin-bottom: 24px;
        }
        .stats {
            color: #fff;
            margin-bottom: 18px;
            font-size: 18px;
            text-align: center;
            padding: 15px;
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
        }
        .alert {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 8px;
            font-size: 16px;
        }
        .alert.error {
            background: #ffebee;
            color: #c62828;
        }
        .alert.ok {
            background: #e8f5e9;
            color: #388e3c;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
            background: rgba(255,255,255,0.03);
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 12px 8px;
            text-align: left;
        }
        th {
            background: #2d2d44;
            color: #fff;
            font-weight: 600;
        }
        td {
            color: #e0e0e0;
            border-bottom: 1px solid #33334d;
        }
        tr:last-child td {
            border-bottom: none;
        }
        .no-results {
            color: #ff6b6b;
            text-align: center;
            margin-top: 24px;
            font-size: 18px;
        }
        .debug-info {
            margin-top: 20px;
            padding: 15px;
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
            color: #ccc;
            font-size: 12px;
        }
        .status-change {
            background: rgba(255,193,7,0.1);
            border-left: 4px solid #ffc107;
            padding: 8px;
            margin: 5px 0;
            color: #ffc107;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📡 Monitor de Canales IPTV</h1>
        
        <?php if (!empty($alertas)): ?>
            <div style="margin-bottom: 20px;">
                <h3 style="color: #fff; margin-bottom: 10px;">🚨 Alertas Recientes:</h3>
                <?php foreach ($alertas as $alert): ?>
                    <div class="alert <?php echo $alert['tipo']; ?>">
                        <?php echo $alert['msg']; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="stats">
            📊 <b>Total:</b> <?php echo $total; ?> |
            🟢 <b style="color:#4caf50;">Online:</b> <?php echo $online; ?> |
            🔴 <b style="color:#ff5252;">Offline:</b> <?php echo $offline; ?> |
            🔄 <b style="color:#ffc107;">Cambios:</b> <?php echo $cambios_detectados; ?>
        </div>
        
        <?php if (count($canales) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>CANAL</th>
                    <th>STATUS</th>
                    <th>ESTADO ANTERIOR</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($canales as $canal): ?>
                <tr>
                    <td><?php echo htmlspecialchars($canal['id']); ?></td>
                    <td><?php echo htmlspecialchars($canal['stream_display_name']); ?></td>
                    <td>
                        <?php 
                        $estado = $estados[$canal['id']] ?? 1;
                        if ($estado == 0) {
                            echo '<span style="color:#4caf50;font-weight:bold;">🟢 ONLINE</span>';
                        } else {
                            echo '<span style="color:#ff5252;font-weight:bold;">🔴 OFFLINE</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <?php 
                        $anterior = $estado_anterior[$canal['id']] ?? 'N/A';
                        if ($anterior !== 'N/A') {
                            if ($anterior === 'ONLINE') {
                                echo '<span style="color:#4caf50;">🟢 ONLINE</span>';
                            } else {
                                echo '<span style="color:#ff5252;">🔴 OFFLINE</span>';
                            }
                        } else {
                            echo '<span style="color:#999;">Primera vez</span>';
                        }
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="no-results">No hay canales disponibles.</div>
        <?php endif; ?>
        
        <div class="debug-info">
            <strong>Debug Info:</strong><br>
            Última ejecución: <?php echo date('d/m/Y H:i:s'); ?><br>
            Archivo de estado: <?php echo file_exists(ESTADO_FILE) ? '✅ Existe' : '❌ No existe'; ?><br>
            Cambios detectados: <?php echo $cambios_detectados; ?><br>
            Canales monitoreados: <?php echo count($canales); ?><br>
            Tamaño del archivo JSON: <?php echo file_exists(ESTADO_FILE) ? filesize(ESTADO_FILE) . ' bytes' : 'N/A'; ?>
        </div>
    </div>
</body>
</html> 