<?php

declare(strict_types=1);

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function clean_float(mixed $value, float $default = 0.0): float
{
    if ($value === null || $value === '') {
        return $default;
    }

    return abs((float) $value);
}

function clean_string(mixed $value, int $maxLength = 255): string
{
    $value = trim((string) ($value ?? ''));
    return mb_substr($value, 0, $maxLength);
}

function money(float $value): string
{
    return number_format($value, 2, '.', '');
}

function minutes_from_time(?string $time): int
{
    if (!$time) {
        return 0;
    }

    [$hour, $minute] = array_map('intval', explode(':', substr($time, 0, 5)));
    return ($hour * 60) + $minute;
}

function circular_minutes_diff(int $a, int $b): int
{
    $diff = abs($a - $b);
    return min($diff, 1440 - $diff);
}

function scalar_similarity(float $a, float $b, float $maxDiff = 4.0): float
{
    $diff = min(abs($a - $b), $maxDiff);
    return max(0.0, 1.0 - ($diff / $maxDiff));
}

function sleep_similarity(?string $startA, ?string $startB, ?string $endA, ?string $endB): float
{
    $startDiff = circular_minutes_diff(minutes_from_time($startA), minutes_from_time($startB));
    $endDiff = circular_minutes_diff(minutes_from_time($endA), minutes_from_time($endB));

    $avgDiff = ($startDiff + $endDiff) / 2;
    return max(0.0, 1.0 - min($avgDiff, 720) / 720);
}

function tag_overlap_ratio(array $targetTags, array $candidateTags): float
{
    $targetTags = array_values(array_unique(array_filter(array_map('trim', $targetTags))));
    $candidateTags = array_values(array_unique(array_filter(array_map('trim', $candidateTags))));

    $union = array_unique(array_merge($targetTags, $candidateTags));
    if (count($union) === 0) {
        return 1.0;
    }

    $shared = array_intersect($targetTags, $candidateTags);
    return count($shared) / count($union);
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function post_value(string $key, mixed $default = ''): mixed
{
    return $_POST[$key] ?? $default;
}
