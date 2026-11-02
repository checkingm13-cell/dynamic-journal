<?php
/**
 * Master Registry & Security Configuration for WWJ Journals
 * Connected directly to Supabase via REST API (Compatible with all Shared Hosting / cPanel environments)
 */

declare(strict_types=1);

if (!defined('WWJ_EXEC')) {
    define('WWJ_EXEC', true);
}

// -----------------------------------------------------------------------------
// Database Connection Configuration (cPanel MySQL & Supabase Cloud Fallback)
// -----------------------------------------------------------------------------
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', (int)(getenv('DB_PORT') ?: 3306));

// Journal-Specific Database Credentials (Synchronized with live cPanel servers)
$DB_CONFIGS = [
    'gjra' => [
        'host' => DB_HOST,
        'user' => 'fulltext_wwjgjra',
        'pass' => 'i[baeFV5g%7nt',
        'name' => 'fulltext_wwjgjra',
        'port' => DB_PORT
    ],
    'paripex' => [
        'host' => DB_HOST,
        'user' => 'fulltext_wwjpijr',
        'pass' => 'i[baeFV5g%7nt',
        'name' => 'fulltext_wwjpijr',
        'port' => DB_PORT
    ],
    'ijsr' => [
        'host' => DB_HOST,
        'user' => 'fulltext_wwjijsr',
        'pass' => 'i[baeFV5g%7nt',
        'name' => 'fulltext_wwjijsr',
        'port' => DB_PORT
    ],
    'ijar' => [
        'host' => DB_HOST,
        'user' => 'fulltext_wwjijar',
        'pass' => 'ODJ4-[3ETO=e',
        'name' => 'fulltext_wwjijar',
        'port' => DB_PORT
    ]
];

// Supabase Cloud Configuration (Active for cloud/local API fallback)
define('SUPABASE_URL', 'https://wzgzgqtegiqwagxvhxku.supabase.co');
define('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Ind6Z3pncXRlZ2lxd2FneHZoeGt1Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3OTM2MDgzMDYsImV4cCI6MjEwOTE4NDMwNn0.rK9Cz1-_biOM5p4gjqs0fY-0pWQs3_8RRyuf017KUPU');

// Master Journal Definitions (Synchronized with Supabase 'journals' table)
$JOURNALS = [
    'gjra' => [
        'code'        => 'GJRA',
        'title'       => 'Global Journal for Research Analysis',
        'tagline'     => 'An International Peer-Reviewed Journal',
        'issn'        => '2277-8160',
        'impact'      => '8.01',
        'email'       => 'gjra@worldwidejournals.com',
        'prefix'      => 'GJRA_',
        'upload_dir'  => __DIR__ . '/storage/uploads/gjra/',
        'subjects'    => [
            'Accounting', 'Agriculture', 'Anatomy', 'Anesthesiology', 'Anthropology',
            'Applied Sciences', 'Architecture', 'Biochemistry', 'Botany', 'Cardiology',
            'Chemistry', 'Commerce', 'Computer Science', 'Dentistry', 'Dermatology',
            'Economics', 'Education', 'Engineering', 'Genetics', 'Management',
            'Medical Science', 'Microbiology', 'Pathology', 'Pharmacy', 'Physics'
        ]
    ],
    'paripex' => [
        'code'        => 'PARIPEX',
        'title'       => 'Paripex - Indian Journal of Research',
        'tagline'     => 'A Double-Blind Peer-Reviewed Refereed Journal',
        'issn'        => '2250-1991',
        'impact'      => '8.21',
        'email'       => 'paripex@worldwidejournals.com',
        'prefix'      => 'PIJR_',
        'upload_dir'  => __DIR__ . '/storage/uploads/paripex/',
        'subjects'    => [
            'Accounting', 'Agriculture', 'Anatomy', 'Anesthesiology', 'Anthropology',
            'Biochemistry', 'Botany', 'Cardiology', 'Chemistry', 'Commerce',
            'Economics', 'Education', 'Engineering', 'Genetics', 'Management',
            'Medical Science', 'Microbiology', 'Pathology', 'Pharmacy', 'Social Sciences'
        ]
    ],
    'ijsr' => [
        'code'        => 'IJSR',
        'title'       => 'International Journal of Scientific Research',
        'tagline'     => 'A Monthly Multidisciplinary Peer Reviewed Journal',
        'issn'        => '2277-8179',
        'impact'      => '7.64',
        'email'       => 'ijsr@worldwidejournals.com',
        'prefix'      => 'IJSR_',
        'upload_dir'  => __DIR__ . '/storage/uploads/ijsr/',
        'subjects'    => [
            'Aerospace', 'Agriculture', 'Applied Physics', 'Biotechnology',
            'Civil Engineering', 'Computer Science', 'Dairy Technology', 'Electronics',
            'Environmental Science', 'Fisheries', 'Food Technology', 'Forestry',
            'Information Technology', 'Life Sciences', 'Mathematics', 'Mechanical',
            'Nanotechnology', 'Oceanography', 'Statistics', 'Veterinary'
        ]
    ],
    'ijar' => [
        'code'        => 'IJAR',
        'title'       => 'Indian Journal of Applied Research',
        'tagline'     => 'A Listed and Indexed Peer-Reviewed International Journal',
        'issn'        => '2249-555X',
        'impact'      => '8.58',
        'email'       => 'ijar@worldwidejournals.com',
        'prefix'      => 'IJAR_',
        'upload_dir'  => __DIR__ . '/storage/uploads/ijar/',
        'subjects'    => [
            'Applied Chemistry', 'Applied Economics', 'Applied Physics', 'Arts',
            'Banking', 'Biotechnology', 'Business Administration', 'Chemistry',
            'Clinical Research', 'Commerce', 'Computer Science', 'Economics',
            'Engineering', 'Finance', 'Humanities', 'Information Technology',
            'Law', 'Management', 'Mass Communication', 'Pharmaceutical', 'Zoology'
        ]
    ]
];

// Optional Domain Mapping for Multi-Domain Deployments
$DOMAIN_MAP = [
    'globaljournalforresearchanalysis.com' => 'gjra',
    'www.globaljournalforresearchanalysis.com' => 'gjra',
    'paripex.in'                           => 'paripex',
    'www.paripex.in'                       => 'paripex',
    'worldwidejournals.com'                => 'ijsr',
    'www.worldwidejournals.com'            => 'ijsr',
    'worldwidejournalspublication.com'     => 'gjra',
    'www.worldwidejournalspublication.com' => 'gjra',
    'apexjournal.store'                    => 'gjra',
    'authorattract.store'                  => 'paripex',
    'journexa.store'                       => 'ijsr'
];

// Master Resolver: 1. ENV -> 2. .journal_lock -> 3. Host Map
$lock_file = __DIR__ . '/.journal_lock';
$active_key = getenv('JOURNAL_ID') ?: null;

if (!$active_key && file_exists($lock_file)) {
    $active_key = trim((string)file_get_contents($lock_file));
}

if (!$active_key && isset($_SERVER['HTTP_HOST'])) {
    $host = strtolower(explode(':', $_SERVER['HTTP_HOST'])[0]);
    $active_key = $DOMAIN_MAP[$host] ?? null;
}

// Default fallback to gjra if unresolved
if (!$active_key || !isset($JOURNALS[$active_key])) {
    $active_key = 'gjra';
}

$current_journal = $JOURNALS[$active_key];
$current_journal['key'] = $active_key;
$current_journal['db'] = $DB_CONFIGS[$active_key] ?? null;

/**
 * Universal Database Connection Helper
 * Connects to cPanel MySQL database via PDO if available, else returns null
 */
function get_db_connection(?string $journal_key = null): ?PDO {
    global $current_journal, $DB_CONFIGS;
    $key = $journal_key ?? ($current_journal['key'] ?? 'gjra');
    $cfg = $DB_CONFIGS[$key] ?? null;
    if (!$cfg) return null;

    try {
        $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT            => 3
        ]);
        return $pdo;
    } catch (Throwable $e) {
        error_log("MySQL Connection Notice for {$key}: " . $e->getMessage());
        return null;
    }
}

/**
 * Universal Supabase Client via cURL & Pure PHP Stream Fallback
 * Guarantees 100% operation across any shared hosting, cPanel, or local environment.
 */
function supabase_request(string $endpoint, string $method = 'GET', array $data = [], array $headers = []): array {
    $url = rtrim(SUPABASE_URL, '/') . '/rest/v1/' . ltrim($endpoint, '/');
    
    $default_headers = [
        'apikey: ' . SUPABASE_ANON_KEY,
        'Authorization: Bearer ' . SUPABASE_ANON_KEY,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ];
    
    $all_headers = array_merge($default_headers, $headers);
    
    // 1. Try native cURL if available
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $all_headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }
        
        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            error_log("Supabase cURL Error: " . $curlError);
            return ['status' => 'error', 'code' => 500, 'error' => $curlError];
        }
        
        $decoded = json_decode((string)$response, true);
        return [
            'status' => ($httpCode >= 200 && $httpCode < 300) ? 'success' : 'error',
            'code'   => $httpCode,
            'data'   => $decoded
        ];
    }
    
    // 2. Pure PHP stream HTTP fallback (works on any server where curl extension is absent)
    $opts = [
        'http' => [
            'method'        => $method,
            'header'        => implode("\r\n", $all_headers),
            'timeout'       => 15,
            'ignore_errors' => true
        ],
        'ssl' => [
            'verify_peer'      => true,
            'verify_peer_name' => true
        ]
    ];
    
    if (!empty($data) && $method !== 'GET') {
        $opts['http']['content'] = json_encode($data);
    }
    
    $context = stream_context_create($opts);
    $response = @file_get_contents($url, false, $context);
    
    $httpCode = 200;
    if (isset($http_response_header) && is_array($http_response_header)) {
        foreach ($http_response_header as $hdr) {
            if (preg_match('#HTTP/[0-9\.]+\s+([0-9]+)#', $hdr, $m)) {
                $httpCode = (int)$m[1];
            }
        }
    }
    
    $decoded = json_decode((string)$response, true);
    return [
        'status' => ($httpCode >= 200 && $httpCode < 300) ? 'success' : 'error',
        'code'   => $httpCode,
        'data'   => $decoded
    ];
}
