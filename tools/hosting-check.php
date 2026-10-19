<?php
/**
 * Hosting Verification Script for Multi-Organizer Event Ticketing Platform
 * Phase 0 Step 4 — Hosting Check (implementation-plan.md §2)
 *
 * Checks cPanel hosting environment readiness for Laravel (v11/v12) and Supabase PostgreSQL.
 *
 * SECURITY NOTICE:
 * - Access requires a secret token (?token=...)
 * - Database credentials are read from environment variables or submitted via POST form
 * - Credentials and passwords are NEVER printed or displayed
 * - CRITICAL: DELETE THIS FILE IMMEDIATELY AFTER USE
 * 
 * to run from cPanel terminal: php tools/hosting-check.php --token=ticket_check_2026
 */

declare(strict_types=1);

// =============================================================================
// 1. CONFIGURATION & ACCESS CONTROL
// =============================================================================

// Default token (can be overridden by setting HOSTING_CHECK_TOKEN in environment)
define('DEFAULT_SECRET_TOKEN', 'ticket_check_2026');
$configuredToken = getenv('HOSTING_CHECK_TOKEN') ?: DEFAULT_SECRET_TOKEN;

$isCli = (php_sapi_name() === 'cli');

// Extract token from CLI arguments or Web request
$providedToken = '';
if ($isCli) {
    global $argv;
    foreach ($argv ?? [] as $arg) {
        if (str_starts_with($arg, '--token=')) {
            $providedToken = substr($arg, 8);
        }
    }
    if (empty($providedToken)) {
        $providedToken = getenv('HOSTING_CHECK_TOKEN') ?: '';
    }
} else {
    $providedToken = (string)($_GET['token'] ?? $_POST['token'] ?? '');
}

$tokenValid = (!empty($providedToken) && hash_equals($configuredToken, $providedToken));

// =============================================================================
// 2. CREDENTIAL GATHERING (POST Form or Environment)
// =============================================================================

// Helper to safely fetch environment or POST values without echoing passwords
$dbDirectHost = trim((string)($_POST['db_direct_host'] ?? getenv('SUPABASE_DIRECT_HOST') ?? getenv('DB_HOST_DIRECT') ?? ''));
$dbPoolerHost = trim((string)($_POST['db_pooler_host'] ?? getenv('SUPABASE_POOLER_HOST') ?? getenv('DB_HOST_POOLER') ?? ''));
$dbPortDirect = (int)($_POST['db_port_direct'] ?? getenv('DB_PORT_DIRECT') ?? 5432);
$dbPortPooler = (int)($_POST['db_port_pooler'] ?? getenv('DB_PORT_POOLER') ?? 6543);
$dbName       = trim((string)($_POST['db_name'] ?? getenv('DB_DATABASE') ?? 'postgres'));
$dbUser       = trim((string)($_POST['db_user'] ?? getenv('DB_USERNAME') ?? ''));
$dbPass       = (string)($_POST['db_password'] ?? getenv('DB_PASSWORD') ?? '');

// If single DB_HOST was provided in env, assign sensibly
if (empty($dbDirectHost) && empty($dbPoolerHost)) {
    $envHost = (string)getenv('DB_HOST');
    if (!empty($envHost)) {
        if (str_contains($envHost, 'pooler')) {
            $dbPoolerHost = $envHost;
        } else {
            $dbDirectHost = $envHost;
        }
    }
}

// =============================================================================
// 3. EXECUTE CHECKS
// =============================================================================

$results = [
    'summary' => ['go' => true, 'conditional' => false, 'reasons' => []],
    'php_version' => [],
    'extensions' => [],
    'functions' => [],
    'filesystem' => [],
    'network_ipv6' => [],
    'database' => [],
];

if ($tokenValid) {
    // -------------------------------------------------------------------------
    // A. PHP Version Check
    // Laravel 11/12 baseline: PHP >= 8.2.0. Project target (composer.json): PHP >= 8.3.0
    // -------------------------------------------------------------------------
    $currentPhpVersion = PHP_VERSION;
    $minLaravelPhp = '8.2.0';
    $projectPhp = '8.3.0';

    $phpPass = version_compare($currentPhpVersion, $minLaravelPhp, '>=');
    $phpTargetPass = version_compare($currentPhpVersion, $projectPhp, '>=');

    $phpStatus = $phpPass ? ($phpTargetPass ? 'PASS' : 'WARN') : 'FAIL';
    $phpNotes = "Current: {$currentPhpVersion}. Minimum required for Laravel 11/12 is {$minLaravelPhp}; project composer.json targets {$projectPhp}+.";

    if (!$phpPass) {
        $results['summary']['go'] = false;
        $results['summary']['reasons'][] = "PHP version ({$currentPhpVersion}) is below minimum requirement ({$minLaravelPhp}).";
    } elseif (!$phpTargetPass) {
        $results['summary']['conditional'] = true;
        $results['summary']['reasons'][] = "PHP version ({$currentPhpVersion}) meets Laravel 11 minimum (8.2) but is below project composer requirement (8.3).";
    }

    $results['php_version'] = [
        'name' => 'PHP Version',
        'current' => $currentPhpVersion,
        'required' => ">= {$minLaravelPhp} (Recommended: >= {$projectPhp})",
        'status' => $phpStatus,
        'notes' => $phpNotes,
    ];

    // -------------------------------------------------------------------------
    // B. Required PHP Extensions Check
    // Required per prompt: pdo_pgsql, mbstring, openssl, bcmath, intl, fileinfo, gd
    // Plus core standard dependencies: pdo, json, tokenizer, ctype
    // -------------------------------------------------------------------------
    $requiredExtensions = [
        'pdo_pgsql' => 'PostgreSQL PDO driver (Database connectivity to Supabase)',
        'mbstring'  => 'Multibyte string handling for UTF-8 input',
        'openssl'   => 'Cryptographic operations, HTTPS, and token encryption',
        'bcmath'    => 'Arbitrary precision mathematics for integer minor monetary units',
        'intl'      => 'Internationalization and locale formatting',
        'fileinfo'  => 'MIME-type detection for asset uploads',
        'gd'        => 'Image processing for QR code generation and ticket posters',
        'pdo'       => 'PHP Data Objects core extension',
        'json'      => 'JSON payload parsing for APIs and webhooks',
        'tokenizer' => 'PHP source tokenizing for Laravel framework internals',
        'ctype'     => 'Character type validation for sanitization',
    ];

    foreach ($requiredExtensions as $ext => $description) {
        $loaded = extension_loaded($ext);
        $extraNotes = '';
        if ($ext === 'gd' && $loaded) {
            $gdInfo = function_exists('gd_info') ? gd_info() : [];
            $hasJpeg = !empty($gdInfo['JPEG Support']);
            if (!$hasJpeg) {
                $loaded = false;
                $results['summary']['go'] = false;
                $results['summary']['reasons'][] = "GD extension is loaded but lacks JPEG Support (required for event image processing).";
                $extraNotes = ' (FAIL: Missing JPEG Support)';
            } else {
                $extraNotes = ' (JPEG Support verified)';
            }
        }

        $results['extensions'][] = [
            'name' => $ext,
            'description' => $description,
            'status' => $loaded ? 'PASS' : 'FAIL',
            'notes' => ($loaded ? 'Loaded' : 'Not installed / not enabled in php.ini') . $extraNotes,
        ];
    }

    // -------------------------------------------------------------------------
    // B2. PHP Configuration Limits (Memory & Upload Size for Event Images)
    // -------------------------------------------------------------------------
    $parseBytes = function (string $val): int {
        $val = trim($val);
        if ($val === '' || $val === '-1') return -1;
        $unit = strtolower(substr($val, -1));
        $num = (int) $val;
        return match ($unit) {
            'g' => $num * 1024 * 1024 * 1024,
            'm' => $num * 1024 * 1024,
            'k' => $num * 1024,
            default => (int) $val,
        };
    };

    $memoryLimitRaw = ini_get('memory_limit') ?: '';
    $uploadMaxRaw = ini_get('upload_max_filesize') ?: '';
    $postMaxRaw = ini_get('post_max_size') ?: '';

    $memBytes = $parseBytes($memoryLimitRaw);
    $uploadBytes = $parseBytes($uploadMaxRaw);
    $postBytes = $parseBytes($postMaxRaw);

    $memPass = ($memBytes === -1 || $memBytes >= 256 * 1024 * 1024);
    $uploadPass = ($uploadBytes >= 10 * 1024 * 1024 || $uploadBytes === -1);
    $postPass = ($postBytes >= 10 * 1024 * 1024 || $postBytes === -1);

    $results['php_limits'] = [
        [
            'name' => 'memory_limit',
            'current' => $memoryLimitRaw,
            'required' => '>= 256M',
            'status' => $memPass ? 'PASS' : 'WARN',
            'notes' => $memPass ? 'Sufficient memory for GD image decoding' : 'Below 256M recommendation; high megapixel images may cause OOM',
        ],
        [
            'name' => 'upload_max_filesize',
            'current' => $uploadMaxRaw,
            'required' => '>= 10M',
            'status' => $uploadPass ? 'PASS' : 'FAIL',
            'notes' => $uploadPass ? 'Accepts images up to 10 MB' : 'Below 10M requirement for event image uploads',
        ],
        [
            'name' => 'post_max_size',
            'current' => $postMaxRaw,
            'required' => '>= 10M',
            'status' => $postPass ? 'PASS' : 'FAIL',
            'notes' => $postPass ? 'Accepts POST payload up to 10 MB' : 'Below 10M requirement for multipart image uploads',
        ],
    ];

    if (!$uploadPass) {
        $results['summary']['go'] = false;
        $results['summary']['reasons'][] = "upload_max_filesize ({$uploadMaxRaw}) is below 10M requirement.";
    }
    if (!$postPass) {
        $results['summary']['go'] = false;
        $results['summary']['reasons'][] = "post_max_size ({$postMaxRaw}) is below 10M requirement.";
    }
    if (!$memPass) {
        $results['summary']['conditional'] = true;
        $results['summary']['reasons'][] = "memory_limit ({$memoryLimitRaw}) is below recommended 256M for image processing.";
    }

    // -------------------------------------------------------------------------
    // C. Process & Function Availability (Scheduler and Queue Workers)
    // -------------------------------------------------------------------------
    $disabledRaw = (string)ini_get('disable_functions');
    $disabledList = array_filter(array_map('trim', explode(',', strtolower($disabledRaw))));

    $processFunctions = [
        'proc_open'      => ['required' => false, 'critical' => true, 'desc' => 'Spawning child processes; needed for daemon queue workers (queue:work)'],
        'proc_close'     => ['required' => false, 'critical' => false, 'desc' => 'Closing process handles'],
        'proc_get_status'=> ['required' => false, 'critical' => false, 'desc' => 'Process health telemetry'],
        'proc_terminate' => ['required' => false, 'critical' => false, 'desc' => 'Graceful worker termination'],
        'exec'           => ['required' => false, 'critical' => false, 'desc' => 'Command execution'],
        'shell_exec'     => ['required' => false, 'critical' => false, 'desc' => 'Shell command invocation'],
    ];

    $procOpenDisabled = in_array('proc_open', $disabledList, true);

    foreach ($processFunctions as $fn => $meta) {
        $isDisabled = in_array(strtolower($fn), $disabledList, true);
        $status = $isDisabled ? ($meta['critical'] ? 'WARN' : 'INFO') : 'PASS';
        $note = $isDisabled
            ? "Disabled in php.ini (disable_functions)."
            : "Available for execution.";

        if ($fn === 'proc_open' && $isDisabled) {
            $results['summary']['conditional'] = true;
            $results['summary']['reasons'][] = "proc_open is disabled: daemon queue workers cannot run continuously. Queued jobs must run via cron (queue:work --stop-when-empty) or hosting must move to a VPS.";
            $note .= " Daemon workers unavailable; must use cron-driven queue runner.";
        }

        $results['functions'][] = [
            'name' => $fn,
            'description' => $meta['desc'],
            'status' => $status,
            'notes' => $note,
        ];
    }

    // -------------------------------------------------------------------------
    // D. Writable Storage & Cache Directories
    // -------------------------------------------------------------------------
    // Determine candidate directories to test
    $baseDir = dirname(__DIR__);
    $candidateDirs = [
        'Script Root' => __DIR__,
        'Project Root' => $baseDir,
        'Storage Directory' => $baseDir . '/storage',
        'Storage Framework Cache' => $baseDir . '/storage/framework/cache',
        'Storage Framework Sessions' => $baseDir . '/storage/framework/sessions',
        'Storage Framework Views' => $baseDir . '/storage/framework/views',
        'Storage Logs' => $baseDir . '/storage/logs',
        'Bootstrap Cache' => $baseDir . '/bootstrap/cache',
    ];

    // Also test a temporary scratch dir in current folder if project folders do not exist yet
    $allWritable = true;
    foreach ($candidateDirs as $label => $path) {
        $exists = file_exists($path);
        $writable = false;
        $note = '';

        if ($exists) {
            $testFile = $path . '/.write_test_' . bin2hex(random_bytes(6)) . '.tmp';
            $writeCheck = @file_put_contents($testFile, 'test_' . time());
            if ($writeCheck !== false) {
                $writable = true;
                @unlink($testFile);
                $note = "Directory exists and test file write/delete succeeded.";
            } else {
                $writable = false;
                $note = "Directory exists but failed to create test file (check UNIX permissions/ownership).";
            }
        } else {
            // Attempt to create it to see if parent allows creation
            $testDir = $path;
            $created = @mkdir($testDir, 0775, true);
            if ($created) {
                $testFile = $testDir . '/.write_test_' . bin2hex(random_bytes(6)) . '.tmp';
                if (@file_put_contents($testFile, 'test') !== false) {
                    $writable = true;
                    @unlink($testFile);
                    $note = "Directory did not exist; created and verified writable.";
                }
            } else {
                $note = "Directory does not exist yet. Ensure permissions 0775/0755 once deployed.";
                $writable = true; // non-fatal if deploying into new directory
            }
        }

        if (!$writable && $exists) {
            $allWritable = false;
            $results['summary']['go'] = false;
            $results['summary']['reasons'][] = "Path '{$path}' ({$label}) is not writable.";
        }

        $results['filesystem'][] = [
            'label' => $label,
            'path' => $path,
            'status' => $writable ? 'PASS' : ($exists ? 'FAIL' : 'INFO'),
            'notes' => $note,
        ];
    }

    // -------------------------------------------------------------------------
    // E. Network & IPv6 Connectivity Check
    // -------------------------------------------------------------------------
    $hasIpv6Support = false;
    $ipv6Notes = [];

    if (defined('AF_INET6')) {
        $ipv6Notes[] = "PHP socket API supports AF_INET6.";
    } else {
        $ipv6Notes[] = "PHP AF_INET6 constant not defined.";
    }

    // Test DNS AAAA resolution
    $dnsAaaa = @dns_get_record('google.com', DNS_AAAA);
    $canResolveAaaa = (!empty($dnsAaaa));
    if ($canResolveAaaa) {
        $ipv6Notes[] = "DNS resolver successfully returned IPv6 AAAA records.";
    } else {
        $ipv6Notes[] = "DNS resolver returned no AAAA records or DNS check failed.";
    }

    // Test active outbound connection to public IPv6 socket (Google DNS [2001:4860:4860::8888]:53)
    $ipv6SocketSuccess = false;
    $ctx = stream_context_create();
    $sTime = microtime(true);
    $ipv6Socket = @stream_socket_client('tcp://[2001:4860:4860::8888]:53', $errno, $errstr, 2.0, STREAM_CLIENT_CONNECT, $ctx);
    if ($ipv6Socket) {
        $ipv6SocketSuccess = true;
        $hasIpv6Support = true;
        $dur = round((microtime(true) - $sTime) * 1000, 1);
        fclose($ipv6Socket);
        $ipv6Notes[] = "Outbound IPv6 socket connection succeeded ({$dur} ms).";
    } else {
        $ipv6Notes[] = "Outbound IPv6 connection unreachable (Error {$errno}: {$errstr}). Server is IPv4-only.";
    }

    $results['network_ipv6'] = [
        'has_ipv6' => $hasIpv6Support,
        'status' => $hasIpv6Support ? 'PASS' : 'WARN',
        'notes' => implode(' ', $ipv6Notes),
    ];

    if (!$hasIpv6Support) {
        $results['summary']['reasons'][] = "Server lacks outbound IPv6 routing. Direct Supabase connections may fail if hostname resolves only to IPv6; the IPv4 Supabase Pooler (port 6543) will be required.";
    }

    // -------------------------------------------------------------------------
    // F. PostgreSQL Connection with SSL Required (Direct vs Pooler)
    // -------------------------------------------------------------------------
    if (empty($dbPass) && empty($dbUser)) {
        $results['database']['tested'] = false;
        $results['database']['message'] = "Database credentials not provided. Enter credentials below to test Supabase connection.";
    } else {
        $results['database']['tested'] = true;

        // Function to test PDO connection safely without leaking secrets
        $testPgConnection = function (string $host, int $port, string $name, string $user, string $pass): array {
            if (empty($host)) {
                return [
                    'status' => 'INFO',
                    'time_ms' => 0,
                    'notes' => 'Host not specified; skipped.',
                    'version' => null,
                ];
            }

            // Enforce sslmode=require as demanded by Supabase and specification
            $dsn = "pgsql:host={$host};port={$port};dbname={$name};sslmode=require";
            $startTime = microtime(true);

            try {
                $options = [
                    PDO::ATTR_TIMEOUT => 6,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ];
                $pdo = new PDO($dsn, $user, $pass, $options);
                $latencyMs = round((microtime(true) - $startTime) * 1000, 2);

                $stmt = $pdo->query("SELECT version() AS ver");
                $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
                $dbVer = $row['ver'] ?? 'PostgreSQL Connected';

                return [
                    'status' => 'PASS',
                    'time_ms' => $latencyMs,
                    'notes' => "SSL connection established in {$latencyMs} ms.",
                    'version' => $dbVer,
                ];
            } catch (\Throwable $e) {
                $latencyMs = round((microtime(true) - $startTime) * 1000, 2);
                // Sanitize error message to ensure password is never reflected
                $rawMsg = $e->getMessage();
                $sanitizedMsg = str_replace($pass, '********', $rawMsg);

                return [
                    'status' => 'FAIL',
                    'time_ms' => $latencyMs,
                    'notes' => "Connection failed ({$latencyMs} ms): " . htmlspecialchars($sanitizedMsg),
                    'version' => null,
                ];
            }
        };

        // Test Direct Host
        $directResult = $testPgConnection($dbDirectHost, $dbPortDirect, $dbName, $dbUser, $dbPass);
        $results['database']['direct'] = [
            'host' => $dbDirectHost ?: '(not provided)',
            'port' => $dbPortDirect,
            'ssl' => 'require',
            'result' => $directResult,
        ];

        // Test Pooler Host
        $poolerResult = $testPgConnection($dbPoolerHost, $dbPortPooler, $dbName, $dbUser, $dbPass);
        $results['database']['pooler'] = [
            'host' => $dbPoolerHost ?: '(not provided)',
            'port' => $dbPortPooler,
            'ssl' => 'require',
            'result' => $poolerResult,
        ];

        $directOk = ($directResult['status'] === 'PASS');
        $poolerOk = ($poolerResult['status'] === 'PASS');

        if (!$directOk && !$poolerOk) {
            $results['summary']['go'] = false;
            $results['summary']['reasons'][] = "Failed to connect to Supabase over SSL on both Direct Host and Pooler Host.";
        } elseif (!$directOk && $poolerOk) {
            $results['summary']['conditional'] = true;
            $results['summary']['reasons'][] = "Direct Supabase connection failed, but Transaction Pooler (port {$dbPortPooler}) succeeded. Use Supabase pooler connection string in Laravel .env.";
        }
    }
}

// Determine final decision verdict
$finalVerdict = 'NO-GO';
$verdictBadge = 'badge-fail';
if ($tokenValid) {
    if ($results['summary']['go']) {
        if ($results['summary']['conditional']) {
            $finalVerdict = 'CONDITIONAL GO';
            $verdictBadge = 'badge-warn';
        } else {
            $finalVerdict = 'GO';
            $verdictBadge = 'badge-pass';
        }
    } else {
        $finalVerdict = 'NO-GO';
        $verdictBadge = 'badge-fail';
    }
}

// =============================================================================
// 4. OUTPUT RENDERING (CLI or HTML)
// =============================================================================

if ($isCli) {
    echo "====================================================================\n";
    echo " HOSTING READINESS VERIFICATION SCRIPT (CLI MODE)\n";
    echo " Phase 0 Step 4 — implementation-plan.md\n";
    echo "====================================================================\n";

    if (!$tokenValid) {
        echo "[ERROR] Access Denied. Valid token required.\n";
        echo "Usage: php " . basename(__FILE__) . " --token=YOUR_SECRET_TOKEN\n";
        echo "Or set HOSTING_CHECK_TOKEN in environment.\n";
        exit(1);
    }

    echo "\nOVERALL VERDICT: {$finalVerdict}\n";
    if (!empty($results['summary']['reasons'])) {
        echo "Notes/Reasons:\n";
        foreach ($results['summary']['reasons'] as $r) {
            echo "  - {$r}\n";
        }
    }

    echo "\n[1] PHP VERSION:\n";
    echo "  Status:   {$results['php_version']['status']}\n";
    echo "  Version:  {$results['php_version']['current']}\n";
    echo "  Required: {$results['php_version']['required']}\n";

    echo "\n[2] REQUIRED EXTENSIONS:\n";
    foreach ($results['extensions'] as $ext) {
        printf("  %-12s [%-4s] %s\n", $ext['name'], $ext['status'], $ext['notes']);
    }

    echo "\n[2b] PHP CONFIGURATION LIMITS (EVENT IMAGES):\n";
    foreach ($results['php_limits'] ?? [] as $lim) {
        printf("  %-22s [%-4s] Current: %-8s Required: %-8s (%s)\n", $lim['name'], $lim['status'], $lim['current'], $lim['required'], $lim['notes']);
    }

    echo "\n[3] FUNCTIONS (QUEUE & SCHEDULER):\n";
    foreach ($results['functions'] as $fn) {
        printf("  %-15s [%-4s] %s\n", $fn['name'], $fn['status'], $fn['notes']);
    }

    echo "\n[4] FILESYSTEM WRITABILITY:\n";
    foreach ($results['filesystem'] as $fs) {
        printf("  %-25s [%-4s] %s\n", $fs['label'], $fs['status'], $fs['notes']);
    }

    echo "\n[5] NETWORK & IPV6:\n";
    echo "  IPv6 Status: [{$results['network_ipv6']['status']}] {$results['network_ipv6']['notes']}\n";

    echo "\n[6] DATABASE CONNECTION (SUPABASE SSL):\n";
    if (!$results['database']['tested']) {
        echo "  [SKIPPED] " . $results['database']['message'] . "\n";
    } else {
        $d = $results['database']['direct'];
        echo "  Direct Host ({$d['host']}:{$d['port']}): [{$d['result']['status']}] {$d['result']['notes']}\n";
        $p = $results['database']['pooler'];
        echo "  Pooler Host ({$p['host']}:{$p['port']}): [{$p['result']['status']}] {$p['result']['notes']}\n";
    }

    echo "\nCRITICAL: REMOVE THIS FILE FROM THE SERVER AFTER COMPLETING TESTS.\n";
    echo "====================================================================\n";
    exit($results['summary']['go'] ? 0 : 2);
}

// -----------------------------------------------------------------------------
// Web HTML Interface
// -----------------------------------------------------------------------------
header('Content-Type: text/html; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow, noarchive');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>cPanel Hosting Verification & Readiness Report</title>
    <style>
        :root {
            --bg: #0f172a;
            --card-bg: #1e293b;
            --border: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --pass-bg: #064e3b;
            --pass-text: #34d399;
            --fail-bg: #7f1d1d;
            --fail-text: #f87171;
            --warn-bg: #78350f;
            --warn-text: #fbbf24;
            --info-bg: #1e3a8a;
            --info-text: #60a5fa;
            --accent: #6366f1;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg);
            color: var(--text-main);
            padding: 24px;
            line-height: 1.5;
        }
        .container { max-width: 1040px; margin: 0 auto; }
        .danger-banner {
            background: #991b1b;
            color: #fee2e2;
            padding: 14px 20px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.2);
        }
        h1 { font-size: 1.75rem; margin-bottom: 6px; font-weight: 700; }
        h2 { font-size: 1.25rem; margin-bottom: 16px; font-weight: 600; color: #e2e8f0; border-bottom: 1px solid var(--border); padding-bottom: 8px; }
        p.subtitle { color: var(--text-muted); margin-bottom: 20px; font-size: 0.95rem; }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .badge-pass { background: var(--pass-bg); color: var(--pass-text); border: 1px solid var(--pass-text); }
        .badge-fail { background: var(--fail-bg); color: var(--fail-text); border: 1px solid var(--fail-text); }
        .badge-warn { background: var(--warn-bg); color: var(--warn-text); border: 1px solid var(--warn-text); }
        .badge-info { background: var(--info-bg); color: var(--info-text); border: 1px solid var(--info-text); }

        .verdict-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #0f172a;
            border: 2px solid var(--border);
            border-radius: 10px;
            padding: 20px 24px;
            margin-bottom: 24px;
        }
        .verdict-title { font-size: 1.1rem; color: var(--text-muted); font-weight: 600; }
        .verdict-large { font-size: 1.8rem; font-weight: 800; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 0.92rem; }
        th, td { padding: 12px 14px; text-align: left; border-bottom: 1px solid var(--border); }
        th { background: #0f172a; color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; }
        tr:hover { background: rgba(255,255,255,0.02); }

        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 16px; }
        .form-group { display: flex; flex-direction: column; }
        label { font-size: 0.85rem; color: var(--text-muted); margin-bottom: 6px; font-weight: 600; }
        input[type="text"], input[type="password"], input[type="number"] {
            background: #0f172a;
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 10px 14px;
            color: #fff;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s;
        }
        input:focus { border-color: var(--accent); }
        button.btn {
            background: var(--accent);
            color: #fff;
            border: none;
            padding: 12px 24px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.95rem;
            transition: opacity 0.2s;
        }
        button.btn:hover { opacity: 0.9; }
        .alert-box {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 0.9rem;
        }
        .alert-danger { background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #fca5a5; }
        .alert-warning { background: rgba(245, 158, 11, 0.15); border: 1px solid #f59e0b; color: #fcd34d; }
    </style>
</head>
<body>
<div class="container">

    <div class="danger-banner">
        <div>
            <span>WARNING: Sensitive Environment Diagnostic Tool</span> &mdash;
            <span style="font-weight: 400; font-size: 0.9rem;">Delete this file from your public web root immediately after testing.</span>
        </div>
    </div>

    <div class="card">
        <h1>Hosting Readiness &amp; Compatibility Check</h1>
        <p class="subtitle">Platform Architecture Phase 0 Step 4 &bull; Laravel + Supabase PostgreSQL on cPanel</p>

        <?php if (!$tokenValid): ?>
            <div class="alert-box alert-danger">
                <strong>Access Restricted:</strong> A valid security token is required to execute hosting diagnostics.
            </div>
            <form method="GET" action="">
                <div class="form-group" style="max-width: 400px; margin-bottom: 16px;">
                    <label for="token">Enter Security Token</label>
                    <input type="text" id="token" name="token" placeholder="e.g. ticket_check_2026" required autocomplete="off">
                </div>
                <button type="submit" class="btn">Authenticate &amp; Run Checks</button>
            </form>
            <p style="margin-top: 16px; font-size: 0.85rem; color: var(--text-muted);">
                Tip: You can pass the token directly in the URL: <code>?token=ticket_check_2026</code>.
            </p>
    </div>
</div>
</body>
</html>
<?php
exit;
endif;
?>

        <!-- VERDICT BAR -->
        <div class="verdict-box">
            <div>
                <div class="verdict-title">Hosting Decision Verdict</div>
                <div class="verdict-large"><?= htmlspecialchars($finalVerdict) ?></div>
            </div>
            <div>
                <span class="badge <?= htmlspecialchars($verdictBadge) ?>" style="font-size: 1.1rem; padding: 8px 18px;">
                    <?= htmlspecialchars($finalVerdict) ?>
                </span>
            </div>
        </div>

        <?php if (!empty($results['summary']['reasons'])): ?>
            <div class="alert-box <?= $results['summary']['go'] ? 'alert-warning' : 'alert-danger' ?>">
                <strong>Key Findings &amp; Infrastructure Constraints:</strong>
                <ul style="margin-top: 6px; padding-left: 20px;">
                    <?php foreach ($results['summary']['reasons'] as $reason): ?>
                        <li><?= htmlspecialchars($reason) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- SECTION 1: PHP VERSION -->
        <h2>1. PHP Runtime Version</h2>
        <table>
            <thead>
                <tr>
                    <th>Component</th>
                    <th>Current Value</th>
                    <th>Requirement</th>
                    <th>Status</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong><?= htmlspecialchars($results['php_version']['name']) ?></strong></td>
                    <td><code><?= htmlspecialchars($results['php_version']['current']) ?></code></td>
                    <td><?= htmlspecialchars($results['php_version']['required']) ?></td>
                    <td><span class="badge badge-<?= strtolower($results['php_version']['status']) ?>"><?= $results['php_version']['status'] ?></span></td>
                    <td><?= htmlspecialchars($results['php_version']['notes']) ?></td>
                </tr>
            </tbody>
        </table>

        <!-- SECTION 2: EXTENSIONS -->
        <h2>2. Required PHP Extensions</h2>
        <table>
            <thead>
                <tr>
                    <th>Extension</th>
                    <th>Platform Purpose</th>
                    <th>Status</th>
                    <th>Inspection Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results['extensions'] as $ext): ?>
                <tr>
                    <td><code><?= htmlspecialchars($ext['name']) ?></code></td>
                    <td><?= htmlspecialchars($ext['description']) ?></td>
                    <td><span class="badge badge-<?= strtolower($ext['status']) ?>"><?= $ext['status'] ?></span></td>
                    <td><?= htmlspecialchars($ext['notes']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- SECTION 3: QUEUE & SCHEDULER FUNCTIONS -->
        <h2>3. Scheduler &amp; Worker Functions (disable_functions)</h2>
        <table>
            <thead>
                <tr>
                    <th>Function</th>
                    <th>Purpose / Framework Requirement</th>
                    <th>Status</th>
                    <th>Operational Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results['functions'] as $fn): ?>
                <tr>
                    <td><code><?= htmlspecialchars($fn['name']) ?></code></td>
                    <td><?= htmlspecialchars($fn['description']) ?></td>
                    <td><span class="badge badge-<?= strtolower($fn['status']) ?>"><?= $fn['status'] ?></span></td>
                    <td><?= htmlspecialchars($fn['notes']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- SECTION 4: FILESYSTEM -->
        <h2>4. Directory Writability (Storage &amp; Cache)</h2>
        <table>
            <thead>
                <tr>
                    <th>Directory Target</th>
                    <th>Resolved Path</th>
                    <th>Status</th>
                    <th>Diagnostic Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results['filesystem'] as $fs): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($fs['label']) ?></strong></td>
                    <td><code style="font-size: 0.85rem;"><?= htmlspecialchars($fs['path']) ?></code></td>
                    <td><span class="badge badge-<?= strtolower($fs['status']) ?>"><?= $fs['status'] ?></span></td>
                    <td><?= htmlspecialchars($fs['notes']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- SECTION 5: NETWORK & IPV6 -->
        <h2>5. Network &amp; Outbound IPv6 Routing</h2>
        <table>
            <thead>
                <tr>
                    <th>Check</th>
                    <th>Status</th>
                    <th>Inspection Result</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Outbound IPv6 Capability</strong></td>
                    <td><span class="badge badge-<?= strtolower($results['network_ipv6']['status']) ?>"><?= $results['network_ipv6']['status'] ?></span></td>
                    <td><?= htmlspecialchars($results['network_ipv6']['notes']) ?></td>
                </tr>
            </tbody>
        </table>

        <!-- SECTION 6: SUPABASE POSTGRESQL SSL CONNECTION -->
        <h2>6. PostgreSQL Supabase Connectivity (SSL Enforced)</h2>

        <?php if ($results['database']['tested']): ?>
            <table>
                <thead>
                    <tr>
                        <th>Target Connection</th>
                        <th>Host / Endpoint</th>
                        <th>Port</th>
                        <th>SSL Mode</th>
                        <th>Latency</th>
                        <th>Status</th>
                        <th>Result</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $d = $results['database']['direct'];
                    $p = $results['database']['pooler'];
                    ?>
                    <tr>
                        <td><strong>Direct Host</strong></td>
                        <td><code><?= htmlspecialchars($d['host']) ?></code></td>
                        <td><?= htmlspecialchars((string)$d['port']) ?></td>
                        <td><code>require</code></td>
                        <td><?= $d['result']['time_ms'] ? $d['result']['time_ms'] . ' ms' : '-' ?></td>
                        <td><span class="badge badge-<?= strtolower($d['result']['status']) ?>"><?= $d['result']['status'] ?></span></td>
                        <td><?= htmlspecialchars($d['result']['notes']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Session / Transaction Pooler</strong></td>
                        <td><code><?= htmlspecialchars($p['host']) ?></code></td>
                        <td><?= htmlspecialchars((string)$p['port']) ?></td>
                        <td><code>require</code></td>
                        <td><?= $p['result']['time_ms'] ? $p['result']['time_ms'] . ' ms' : '-' ?></td>
                        <td><span class="badge badge-<?= strtolower($p['result']['status']) ?>"><?= $p['result']['status'] ?></span></td>
                        <td><?= htmlspecialchars($p['result']['notes']) ?></td>
                    </tr>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: var(--text-muted); margin-bottom: 16px;">
                <em><?= htmlspecialchars($results['database']['message']) ?></em>
            </p>
        <?php endif; ?>

        <!-- DATABASE CREDENTIAL FORM (NEVER ECHOES PASSWORDS) -->
        <div style="background: #0f172a; padding: 20px; border-radius: 8px; border: 1px solid var(--border); margin-top: 16px;">
            <h3 style="font-size: 1rem; margin-bottom: 12px; color: #cbd5e1;">Test Supabase Database Credentials</h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 16px;">
                Credentials submitted here are executed in-memory only and never logged or displayed.
            </p>
            <form method="POST" action="?token=<?= htmlspecialchars($providedToken) ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($providedToken) ?>">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="db_direct_host">Direct Host (IPv6 / standard)</label>
                        <input type="text" id="db_direct_host" name="db_direct_host" placeholder="db.xxxx.supabase.co" value="<?= htmlspecialchars($dbDirectHost) ?>">
                    </div>
                    <div class="form-group">
                        <label for="db_port_direct">Direct Port</label>
                        <input type="number" id="db_port_direct" name="db_port_direct" value="<?= htmlspecialchars((string)$dbPortDirect) ?>">
                    </div>
                    <div class="form-group">
                        <label for="db_pooler_host">Pooler Host (IPv4 / Supabase Pooler)</label>
                        <input type="text" id="db_pooler_host" name="db_pooler_host" placeholder="aws-0-xx.pooler.supabase.com" value="<?= htmlspecialchars($dbPoolerHost) ?>">
                    </div>
                    <div class="form-group">
                        <label for="db_port_pooler">Pooler Port</label>
                        <input type="number" id="db_port_pooler" name="db_port_pooler" value="<?= htmlspecialchars((string)$dbPortPooler) ?>">
                    </div>
                    <div class="form-group">
                        <label for="db_name">Database Name</label>
                        <input type="text" id="db_name" name="db_name" value="<?= htmlspecialchars($dbName) ?>">
                    </div>
                    <div class="form-group">
                        <label for="db_user">Database Username</label>
                        <input type="text" id="db_user" name="db_user" placeholder="postgres or postgres.xxxx" value="<?= htmlspecialchars($dbUser) ?>">
                    </div>
                    <div class="form-group">
                        <label for="db_password">Database Password (Never Printed)</label>
                        <input type="password" id="db_password" name="db_password" placeholder="Supabase database password" autocomplete="new-password">
                    </div>
                </div>
                <button type="submit" class="btn">Execute PostgreSQL SSL Connection Test</button>
            </form>
        </div>

    </div>
</div>
</body>
</html>
