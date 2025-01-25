<?php
// Network Tools Page

// Function to execute shell commands securely
function executeCommand($command, $input = '')
{
    $descriptors = [
        0 => ['pipe', 'r'], // stdin
        1 => ['pipe', 'w'], // stdout
        2 => ['pipe', 'w'], // stderr
    ];

    $process = proc_open($command, $descriptors, $pipes);

    if (is_resource($process)) {
        fwrite($pipes[0], $input);
        fclose($pipes[0]);

        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $error = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        proc_close($process);

        return $error ? $error : $output;
    }

    return 'Command execution failed.';
}

// Function to check OS
function isWindows()
{
    return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
}

// Ping Tool
function ping($host)
{
    $command = isWindows() ? 'ping -n 4 ' : 'ping -c 4 ';
    $command .= escapeshellarg($host);
    return executeCommand($command);
}

// Host Tool
function host($domain)
{
    if (isWindows()) {
        // Windows does not have 'host' command, use nslookup instead
        return dnsLookup($domain);
    }
    $command = 'host ' . escapeshellarg($domain);
    return executeCommand($command);
}

// Traceroute Tool
function traceroute($host)
{
    $command = isWindows() ? 'tracert ' : 'traceroute ';
    $command .= escapeshellarg($host);
    return executeCommand($command);
}

// DNS Lookup Tool
function dnsLookup($domain)
{
    $command = 'nslookup ' . escapeshellarg($domain);
    return executeCommand($command);
}

function getInputValue($field)
{
    return isset($_POST[$field]) ? htmlspecialchars($_POST[$field]) : '';
}

function isSelected($name, $value)
{
    return (isset($_POST[$name]) && $_POST[$name] === $value) ? 'selected' : '';
}

$tools = [
    'ping' => 'Ping',
    'host' => 'Host',
    'traceroute' => 'Traceroute',
    'dns_lookup' => 'DNS Lookup',
];

// Handle form submission
$result = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tool = $_POST['tool'];
    $input = $_POST['input'];

    switch ($tool) {
        case 'ping':
            $result = ping($input);
            break;
        case 'host':
            $result = host($input);
            break;
        case 'traceroute':
            $result = traceroute($input);
            break;
        case 'dns_lookup':
            $result = dnsLookup($input);
            break;
        default:
            $result = 'Invalid tool selected.';
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Network Tools</title>
    <link rel="stylesheet" href="css/bulma.min.css">
</head>

<body>
    <!-- Header Section -->
    <section class="hero is-medium is-primary is-bold">
        <div class="hero-body">
            <div class="container has-text-centered">
                <h1 class="title is-1 has-text-weight-bold">
                    Network Tools
                </h1>
                <p class="subtitle is-4">
                    Ping, Host, Traceroute, DNS Lookup, and Whois
                </p>
                <div class="buttons is-centered mt-4">
                    <a href="index.html" class="button is-light is-medium">
                        Home
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content Section -->
    <section class="section">
        <div class="container">
            <div class="columns is-centered">
                <div class="column is-half">
                    <div class="box">
                        <h2 class="title is-4">Select a Tool</h2>
                        <form method="POST">
                            <div class="field">
                                <label class="label">Tool</label>
                                <div class="control">
                                    <div class="select is-fullwidth">
                                        <select name="tool" required>
                                            <?php foreach ($tools as $tool => $label): ?>
                                                <option value="<?php echo $tool; ?>" <?php echo isSelected('tool', $tool); ?>>
                                                    <?php echo $label; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="field">
                                <label class="label">Input (Domain or IP)</label>
                                <div class="control">
                                    <input class="input" type="text" name="input" placeholder="e.g., google.com" value="<?php echo getInputValue('input'); ?>" required>
                                </div>
                            </div>
                            <div class="field">
                                <div class="control">
                                    <button type="submit" class="button is-primary is-fullwidth">Run Tool</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Results Section -->
            <?php if ($result): ?>
                <div class="columns is-centered">
                    <div class="column is-half">
                        <div class="box">
                            <h2 class="title is-4">Results</h2>
                            <pre><?php echo htmlspecialchars($result); ?></pre>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <footer class="footer has-background-dark has-text-light">
        <div class="content has-text-centered">
            <p class="is-size-5">
                <strong class="has-text-light">Network Tools</strong> by
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