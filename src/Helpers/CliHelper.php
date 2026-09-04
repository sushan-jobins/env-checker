<?php

namespace SushanJobins\EnvChecker\Helpers;

class CliHelper
{
    public static function isDryRun(array $argv): bool
    {
        return in_array('--dry', $argv, true) || in_array('dry', $argv, true);
    }

    public static function isInfoStatus(array $argv): bool
    {
        return in_array('--info-status', $argv, true);
    }

    public static function getStatusFilter(array $argv): ?string
    {
        foreach ($argv as $argument) {
            if (str_starts_with($argument, '--status=')) {
                return substr($argument, strlen('--status='));
            }
        }

        return null;
    }

    public static function truncateText(string $text, int $limit = 40): string
    {
        if (strlen($text) <= $limit) {
            return $text;
        }

        if ($limit <= 3) {
            return substr($text, 0, $limit);
        }

        return substr($text, 0, $limit - 3) . '...';
    }

    public static function stripAnsi(string $text): string
    {
        return preg_replace('/\033\[[0-9;]*m/', '', $text) ?? $text;
    }
}