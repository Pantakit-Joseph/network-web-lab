<?php
// Network Lab - Single File Implementation

// Function Definitions
function getNetworkInfo()
{
    $uptime = @shell_exec("uptime -p");
    $loadAvg = sys_getloadavg();
    return [
        'Hostname' => gethostname() ?: 'N/A',
        'Server IP' => $_SERVER['SERVER_ADDR'] ?? 'N/A',
        'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
        'PHP Version' => phpversion(),
        'Operating System' => php_uname('s') . ' ' . php_uname('r'),
        'Server Time' => date('Y-m-d H:i:s'),
        'Server Load (1m, 5m, 15m)' => $loadAvg ? implode(', ', $loadAvg) : 'N/A',
        'Uptime' => $uptime ?: 'N/A',
        'Document Root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
        'Script Name' => $_SERVER['SCRIPT_NAME'] ?? 'N/A',
        'Server Protocol' => $_SERVER['SERVER_PROTOCOL'] ?? 'N/A',
        'Request Method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
    ];
}

function getClientInfo()
{

    $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'N/A';


    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $clientIP = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $clientIP = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    }

    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'N/A';

    return [
        'Client IP' => $clientIP,
        'User Agent' => $userAgent,
        'Remote Port' => $_SERVER['REMOTE_PORT'] ?? 'N/A',
        'Browser Language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'N/A',
        'Server Time' => date('Y-m-d H:i:s'),
        'Connection Method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
        'Request URI' => $_SERVER['REQUEST_URI'] ?? 'N/A',
        'Protocol' => $_SERVER['SERVER_PROTOCOL'] ?? 'N/A',
    ];
}

function checkInternetConnection($domains, $timeout = 2)
{
    $results = [];

    foreach ($domains as $domain) {
        $connected = false;
        $startTime = microtime(true); // วัดเวลาในการเชื่อมต่อ
        $errorDetails = null;

        try {
            $connection = @fsockopen($domain, 80, $errno, $errstr, $timeout);
            if ($connection) {
                $connected = true;
                fclose($connection);
            } else {
                $errorDetails = $errstr ?: 'Unknown error';
            }
        } catch (Exception $e) {
            $errorDetails = $e->getMessage();
        }

        $results[$domain] = [
            'status' => $connected,
            'response_time' => $connected ? round((microtime(true) - $startTime) * 1000, 2) . ' ms' : 'N/A',
            'error' => $connected ? null : $errorDetails,
        ];
    }

    return $results;
}


function scanPorts($host, $startPort = 20, $endPort = 100)
{
    $openPorts = [];
    for ($port = $startPort; $port <= $endPort; $port++) {
        $connection = @fsockopen($host, $port, $errno, $errstr, 0.5);
        if ($connection) {
            $openPorts[] = $port;
            fclose($connection);
        }
    }
    return $openPorts;
}

function getSystemInfo()
{
    return [
        'CPU' => trim(shell_exec('cat /proc/cpuinfo | grep "model name" | uniq | cut -d: -f2')),
        'Memory' => trim(shell_exec('free -h | grep Mem:')),
        'Disk' => trim(shell_exec('df -h / | tail -1'))
    ];
}

function logEvent($message)
{
    error_log("[" . date('Y-m-d H:i:s') . "] " . $message);
}

// Log page visit
session_start();
logEvent('Visited Network Lab Page');

// Prepare data
$networkInfo = getNetworkInfo();
$clientInfo = getClientInfo();
$testDomains = [
    'www.google.com',
    'www.example.com',
];
// Check if internet connection check is disabled
$checkInternet = !isset($_GET['check_internet']) || $_GET['check_internet'] !== 'false';
$connectionStatus = $checkInternet ? checkInternetConnection($testDomains) : null;

$localHost = '127.0.0.1';
$openPorts = scanPorts($localHost);
$systemInfo = getSystemInfo();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Network Lab</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <style>
        body {
            background-color: #f5f5f5;
        }

        .box {
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .table tr:hover {
            background-color: #f0f8ff;
        }
    </style>
</head>

<body>
    <!-- Header Section -->
    <section class="hero is-primary is-bold">
        <div class="hero-body">
            <div class="container has-text-centered">
                <h1 class="title is-1 has-text-weight-bold">
                    Network Lab
                </h1>
                <p class="subtitle is-4">
                    Comprehensive Network Analysis Dashboard
                </p>
                <div class="buttons is-centered mt-4">
                    <a href="index.html" class="button is-light is-medium">
                        Home
                    </a>
                    <a href="network_tools.php" class="button is-light is-medium">
                        Go to Network Tools
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content Section -->
    <section class="section">
        <div class="container">
            <div class="columns is-multiline">
                <!-- Server Information -->
                <div class="column is-half">
                    <div class="box">
                        <h2 class="title is-4">Server Information</h2>
                        <table class="table is-striped is-fullwidth is-hoverable">
                            <tbody>
                                <?php foreach ($networkInfo as $key => $value): ?>
                                    <tr>
                                        <td><strong><?php echo $key; ?></strong></td>
                                        <td><?php echo $value; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Client Information -->
                <div class="column is-half">
                    <div class="box">
                        <h2 class="title is-4">Client Information</h2>
                        <table class="table is-striped is-fullwidth is-hoverable">
                            <tbody>
                                <?php
                                $clientInfo = getClientInfo();
                                foreach ($clientInfo as $key => $value): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($key); ?></strong></td>
                                        <td><?php echo htmlspecialchars($value); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Network Connectivity -->
                <div class="column is-half">
                    <div class="box">
                        <h2 class="title is-4">Internet Connection Status</h2>
                        <table class="table is-striped is-fullwidth is-hoverable">
                            <thead>
                                <tr>
                                    <th>Domain</th>
                                    <th>Status</th>
                                    <th>Response Time</th>
                                    <th>Error</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($connectionStatus as $domain => $info): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($domain); ?></td>
                                        <td>
                                            <?php if ($info['status']): ?>
                                                <span class="has-text-success">Connected</span>
                                            <?php else: ?>
                                                <span class="has-text-danger">Disconnected</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($info['response_time']); ?></td>
                                        <td><?php echo htmlspecialchars($info['error'] ?? ''); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Port Scanning -->
                <div class="column is-half">
                    <div class="box">
                        <h2 class="title is-4">Port Scanning</h2>
                        <p><strong>Open Ports:</strong>
                            <?php echo count($openPorts) > 0
                                ? implode(', ', $openPorts)
                                : '<span class="has-text-danger">No open ports found</span>';
                            ?>
                        </p>
                    </div>
                </div>

                <!-- System Analysis -->
                <div class="column is-full">
                    <div class="box">
                        <h2 class="title is-4">System Analysis</h2>
                        <table class="table is-striped is-fullwidth is-hoverable">
                            <tbody>
                                <?php foreach ($systemInfo as $key => $value): ?>
                                    <tr>
                                        <td><strong><?php echo $key; ?></strong></td>
                                        <td><?php echo $value; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <footer class="footer has-background-dark has-text-light">
        <div class="content has-text-centered">
            <p class="is-size-5">
                <strong class="has-text-light">Network Lab</strong> by
                <a href="https://github.com/Pantakit-Joseph" class="has-text-primary" target="_blank">
                    Pantakit Malitong (Joseph)
                </a>.
            </p>
            <p class="mt-4 is-size-7">
                <a href="index.html" class="has-text-primary">Home</a> |
                <a href="https://github.com/Pantakit-Joseph/network-web-lab.git" class="has-text-primary" target="_blank">
                    View on GitHub
                </a>
            </p>
            <p class="mt-4 is-size-7">
                &copy; <?php echo date('Y'); ?> All rights reserved.
            </p>
        </div>
    </footer>
</body>

</html>