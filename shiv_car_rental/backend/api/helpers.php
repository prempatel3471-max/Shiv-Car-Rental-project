<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

function json_input(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '{}', true);
    return is_array($data) ? $data : [];
}

function respond(array $data, int $status = 200): never {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function required_string(array $data, string $key): string {
    $value = trim((string)($data[$key] ?? ''));
    if ($value === '') respond(['success'=>false,'message'=>"Missing {$key}."], 422);
    return $value;
}
