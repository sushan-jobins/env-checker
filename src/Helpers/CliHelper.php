<?php

namespace SushanJobins\EnvChecker\Helpers;

class CliHelper
{
    public static function isValidCommand(array $argv): bool
    {
        $args = array_slice($argv, 1);

        foreach ($args as $arg) {
            if (self::isSupportedOption($arg)) {
                continue;
            }

            echo PHP_EOL;
            echo "\033[1;31mInvalid argument: {$arg}\033[0m" . PHP_EOL;
            echo PHP_EOL;

            return false;
        }

        return true;
    }

    private static function isSupportedOption(string $arg): bool
    {
        return $arg === '--dry'
            || $arg === 'dry'
            || $arg === '--info-status'
            || $arg === '--help'
            || $arg === '-h'
            || str_starts_with($arg, '--status=');
    }

    public static function isHelp(array $argv): bool
    {
        return in_array('--help', $argv, true) || in_array('-h', $argv, true);
    }

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