<?php
/**
 * Created by Claude 2026-08-19
 *
 * Approximate session durations from an Apache access log (Combined Log Format)
 * by grouping requests by IP + User-Agent, then splitting into sessions
 * whenever the gap between two consecutive requests exceeds a timeout.
 *
 * Usage:
 *   php session_duration.php /path/to/access.log > sessions.csv
 *   php session_duration.php /path/to/access.log 15   (custom 15-min timeout)
 *
 * Output: CSV with ip, user_agent, session_start, session_end, duration_seconds, request_count
 */

if ($argc < 2) {
    fwrite(STDERR, "Usage: php session_duration.php <logfile> [timeout_minutes=30]\n");
    exit(1);
}

$logfile        = $argv[1];
$timeoutMinutes = isset($argv[2]) ? (int) $argv[2] : 30;
$timeoutSeconds = $timeoutMinutes * 60;

if (!is_readable($logfile)) {
    fwrite(STDERR, "Cannot read file: $logfile\n");
    exit(1);
}

// Combined Log Format:
// %h %l %u %t "%r" %>s %b "%{Referer}i" "%{User-agent}i"
$pattern = '/^(\S+) \S+ \S+ \[([^\]]+)\] "[^"]*" \d+ \S+ "[^"]*" "([^"]*)"/';

$requests = []; // key = ip|user-agent, value = array of unix timestamps

$handle = fopen($logfile, 'r');
if (!$handle) {
    fwrite(STDERR, "Failed to open file.\n");
    exit(1);
}

while (($line = fgets($handle)) !== false) {
    if (!preg_match($pattern, $line, $m)) {
        continue; // skip lines that don't match (malformed / different log format)
    }

    $ip        = $m[1];
    $timeStr   = $m[2]; // e.g. 31/Jul/2026:12:08:17 -0400
    $userAgent = $m[3] !== '' ? $m[3] : '(unknown)';

    // Parse Apache log time format: d/M/Y:H:i:s O
    $dt = DateTime::createFromFormat('d/M/Y:H:i:s O', $timeStr);
    if (!$dt) {
        continue; // skip unparseable timestamps
    }

    $key = $ip . '|' . $userAgent;
    $requests[$key][] = $dt->getTimestamp();
}
fclose($handle);

// Output CSV header
echo "ip,user_agent,session_start,session_end,duration_seconds,request_count\n";

foreach ($requests as $key => $timestamps) {
    [$ip, $userAgent] = explode('|', $key, 2);
    sort($timestamps);

    $sessionStart = $timestamps[0];
    $prevTime     = $timestamps[0];
    $requestCount = 1;

    $flushSession = function ($start, $end, $count) use ($ip, $userAgent) {
        $duration = $end - $start;
        $startFmt = date('Y-m-d H:i:s', $start);
        $endFmt   = date('Y-m-d H:i:s', $end);
        $uaEscaped = str_replace('"', '""', $userAgent);
        echo "\"$ip\",\"$uaEscaped\",\"$startFmt\",\"$endFmt\",$duration,$count\n";
    };

    for ($i = 1; $i < count($timestamps); $i++) {
        $gap = $timestamps[$i] - $prevTime;

        if ($gap > $timeoutSeconds) {
            // Gap too large: close out the current session, start a new one
            $flushSession($sessionStart, $prevTime, $requestCount);
            $sessionStart = $timestamps[$i];
            $requestCount = 0;
        }

        $prevTime = $timestamps[$i];
        $requestCount++;
    }

    // Flush the final session for this ip+user-agent group
    $flushSession($sessionStart, $prevTime, $requestCount);
}
