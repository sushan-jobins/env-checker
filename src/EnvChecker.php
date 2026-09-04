<?php

namespace SushanJobins\EnvChecker;

class EnvChecker
{
    /**
     * Check for the existence of required environment files.
     */
    public function check(string $path): array
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR);

        return [
            '.env.example' => is_file($path . DIRECTORY_SEPARATOR . '.env.example'),
            '.env'         => is_file($path . DIRECTORY_SEPARATOR . '.env'),
        ];
    }

    /**
     * Parse an environment file into a key-value array.
     */
    public function parseEnvFile(string $file): array
    {
        if (!is_file($file) || !is_readable($file)) {
            return [];
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return [];
        }

        $variables = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);

            $key   = trim($key);
            $value = trim($value);

            if ($key === '') {
                continue;
            }

            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"'))
                || (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            $variables[$key] = $value;
        }

        return $variables;
    }

    /**
     * Retrieve keys present in .env.example but missing from .env.
     */
    public function getMissingKeys(string $path): array
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR);

        $example = $this->parseEnvFile($path . DIRECTORY_SEPARATOR . '.env.example');
        $env     = $this->parseEnvFile($path . DIRECTORY_SEPARATOR . '.env');

        $missing = [];

        foreach ($example as $key => $value) {
            if (!array_key_exists($key, $env)) {
                $missing[] = [
                    'key'   => $key,
                    'value' => $value,
                ];
            }
        }

        return $missing;
    }
}