<?php
// data.php — persistence layer for database.json
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function defaultData(): array {
    $routine = [];
    foreach (DAYS as $d) { $routine[$d] = []; }
    return [
        'meta' => [
            'className' => 'CSE60F',
            'semester' => 'Summer 2026',
            'brandImageUrl' => '',
            'lastUpdated' => '',
            'adminPasswordHash' => password_hash(DEFAULT_PASSWORD, PASSWORD_DEFAULT),
        ],
        'classRoutine' => $routine,
        'examRoutine' => [],
        'faculty' => [],
        'attendance' => [],
        'roster' => [],
        'tasks' => [],
        'notices' => [],
    ];
}

/** Make sure every expected key/shape exists. */
function normalizeData(array $data): array {
    if (!isset($data['meta']) || !is_array($data['meta'])) $data['meta'] = [];
    $metaDefaults = ['className' => 'CSE60F', 'semester' => 'Summer 2026', 'brandImageUrl' => '', 'lastUpdated' => ''];
    foreach ($metaDefaults as $k => $v) {
        if (!isset($data['meta'][$k])) $data['meta'][$k] = $v;
    }
    if (empty($data['meta']['adminPasswordHash'])) {
        $data['meta']['adminPasswordHash'] = password_hash(DEFAULT_PASSWORD, PASSWORD_DEFAULT);
    }

    if (!isset($data['classRoutine']) || !is_array($data['classRoutine'])) {
        $data['classRoutine'] = [];
    }
    foreach (DAYS as $d) {
        if (!isset($data['classRoutine'][$d]) || !is_array($data['classRoutine'][$d])) {
            $data['classRoutine'][$d] = [];
        }
    }

    foreach (['examRoutine', 'faculty', 'attendance', 'roster', 'tasks', 'notices'] as $k) {
        if (!isset($data[$k]) || !is_array($data[$k])) $data[$k] = [];
    }

    foreach ($data['tasks'] as &$t) {
        if (!isset($t['note'])) {
            $t['note'] = '';
        }
        unset($t['resource']);
    }
    unset($t);

    foreach ($data['examRoutine'] as &$e) {
        if (!isset($e['topic'])) {
            $e['topic'] = '';
        }
    }
    unset($e);

    foreach ($data['notices'] as &$n) {
        if (!isset($n['isPinned'])) {
            $n['isPinned'] = true;
        }
    }
    unset($n);

    return $data;
}

/** Load the current state from disk. */
function loadData(): array {
    if (!file_exists(DATA_FILE)) {
        $def = defaultData();
        saveData($def);
        return $def;
    }
    $raw = file_get_contents(DATA_FILE);
    $parsed = json_decode($raw ?: '', true);
    if (!is_array($parsed)) {
        $parsed = defaultData();
    }
    return normalizeData($parsed);
}

/** Persist state to disk with an exclusive lock. */
function saveData(array $data): void {
    $data = normalizeData($data);
    $data['meta']['lastUpdated'] = gmdate('c');
    $fp = fopen(DATA_FILE, 'c+');
    if ($fp === false) {
        throw new RuntimeException('Unable to open data file for writing.');
    }
    flock($fp, LOCK_EX);
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
}

/** Returns a copy of state safe to send to the browser. */
function publicData(array $data): array {
    unset($data['meta']['adminPasswordHash']);
    return $data;
}