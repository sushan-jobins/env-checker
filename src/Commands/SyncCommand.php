<?php

namespace SushanJobins\EnvChecker\Commands;

use SushanJobins\EnvChecker\EnvChecker;
use SushanJobins\EnvChecker\Helpers\CliHelper;
use SushanJobins\EnvChecker\Helpers\TableHelper;

class SyncCommand
{
    public function __construct(
        private EnvChecker $checker,
        private int $maxLen = 40
    ) {}

    public function handle(array $argv, string $path): void
    {
        $path        = rtrim($path, DIRECTORY_SEPARATOR);
        $envFile     = $path . DIRECTORY_SEPARATOR . '.env';
        $exampleFile = $path . DIRECTORY_SEPARATOR . '.env.example';

        $statusFilter = CliHelper::getStatusFilter($argv);
        $allowedStatuses = ['all', 'added', 'changed', 'not_changed_on_env', 'only_on_env', 'same'];

        if ($statusFilter !== null && !in_array($statusFilter, $allowedStatuses, true)) {
            echo "\033[31mInvalid status filter: {$statusFilter}\033[0m" . PHP_EOL;
            echo 'Allowed values: ' . implode(', ', $allowedStatuses) . PHP_EOL;
            exit(1);
        }

        $currentEnvRaw = file_get_contents($envFile);
        $exampleRaw    = file_get_contents($exampleFile);

        if ($currentEnvRaw === false || $exampleRaw === false) {
            echo "\033[31mError: Unable to read environment files.\033[0m" . PHP_EOL;
            exit(1);
        }

        $oldValues     = $this->checker->parseEnvFile($envFile);
        $exampleValues = $this->checker->parseEnvFile($exampleFile);

        $newEnvLines = $this->buildNewEnvContent($exampleFile, $oldValues);
        if (empty($newEnvLines)) {
            echo "\033[31mError: Unable to generate new .env content.\033[0m" . PHP_EOL;
            exit(1);
        }

        $newEnvRaw = implode("\n", $newEnvLines) . "\n";

        // Generate status rows comparing old .env vs .env.example vs proposed synced values
        $rows          = $this->generateStatusRows($oldValues, $exampleValues, $statusFilter);
        $formattedRows = $this->formatRows($rows);

        TableHelper::render(
            ['env', 'value in example', 'previous value in env', 'current value in env', 'status'],
            $formattedRows
        );

        // Prompt for update ONLY if status is blank (null), 'all', 'added', or 'changed'
        $interactiveStatuses = [null, 'all', 'added', 'changed'];
        if (in_array($statusFilter, $interactiveStatuses, true)) {
            $this->confirmEnvUpdate($envFile, $newEnvRaw, $currentEnvRaw);
        }
    }

    private function buildNewEnvContent(string $exampleFile, array $currentEnvMap): array
    {
        $exampleContent = file_get_contents($exampleFile);
        if ($exampleContent === false) {
            return [];
        }

        $exampleLines = preg_split('/\r\n|\r|\n/', $exampleContent);
        $newEnvLines  = [];
        $matchedKeys  = [];

        foreach ($exampleLines as $line) {
            $trimmedLine = trim($line);

            if ($trimmedLine === '' || str_starts_with($trimmedLine, '#') || !str_contains($line, '=')) {
                $newEnvLines[] = $line;
                continue;
            }

            [$key, $exampleValue] = explode('=', $line, 2);
            $key          = trim($key);
            $exampleValue = trim($exampleValue);

            if ($key === '') {
                $newEnvLines[] = $line;
                continue;
            }

            $matchedKeys[$key] = true;

            if (array_key_exists($key, $currentEnvMap)) {
                $currentValue = trim($currentEnvMap[$key]);

                if ($currentValue === '' || $currentValue === '""' || $currentValue === "''") {
                    $finalValue = $exampleValue;
                } else {
                    $finalValue = $currentValue;
                }
            } else {
                $finalValue = $exampleValue;
            }

            $newEnvLines[] = "{$key}={$finalValue}";
        }

        $extraKeys = array_diff_key($currentEnvMap, $matchedKeys);
        if (!empty($extraKeys)) {
            $newEnvLines[] = '';
            $newEnvLines[] = '# --- Custom keys unique to your local .env configuration ---';

            foreach ($extraKeys as $extraKey => $extraValue) {
                $newEnvLines[] = "{$extraKey}={$extraValue}";
            }
        }

        return $newEnvLines;
    }

    private function generateStatusRows(array $oldValues, array $exampleValues, ?string $statusFilter): array
    {
        $allKeys = array_unique(array_merge(
            array_keys($oldValues),
            array_keys($exampleValues)
        ));

        $rows = [];
        $statusOrder = [
            'added'              => 1,
            'changed'            => 2,
            'not_changed_on_env' => 3,
            'only_on_env'        => 4,
            'same'               => 5,
        ];

        foreach ($allKeys as $key) {
            $existsInOld     = array_key_exists($key, $oldValues);
            $existsInExample = array_key_exists($key, $exampleValues);

            $oldVal     = $oldValues[$key] ?? null;
            $exampleVal = $exampleValues[$key] ?? null;

            $isOldEmpty     = $oldVal === null || $oldVal === '' || $oldVal === '""' || $oldVal === "''";
            $isExampleEmpty = $exampleVal === null || $exampleVal === '' || $exampleVal === '""' || $exampleVal === "''";

            if (!$existsInOld && $existsInExample) {
                $status = 'added';
            } elseif ($existsInOld && !$existsInExample) {
                $status = 'only_on_env';
            } elseif ($isOldEmpty && $isExampleEmpty) {
                $status = 'same';
            } elseif ($isOldEmpty && !$isExampleEmpty) {
                $status = 'changed';
            } elseif ($oldVal !== $exampleVal) {
                $status = 'not_changed_on_env';
            } else {
                $status = 'same';
            }

            $updatedVal = match ($status) {
                'added', 'changed'   => $exampleVal ?? '',
                'only_on_env'        => $oldVal ?? '',
                'not_changed_on_env' => $oldVal ?? '',
                'same'               => $oldVal ?? '',
                default              => '',
            };

            if ($statusFilter === null && $status === 'same') {
                continue;
            }

            if ($statusFilter !== null && $statusFilter !== 'all' && $statusFilter !== $status) {
                continue;
            }

            // If status is 'same' and value is empty, don't show '-'
            $previousDisplay = ($status === 'same' && $isOldEmpty) ? '' : ($oldVal ?? '-');
            $currentDisplay  = ($status === 'same' && $isOldEmpty) ? '' : ($updatedVal !== '' ? $updatedVal : '-');

            $rows[] = [
                'key'      => $key,
                'example'  => $exampleVal ?? '',
                'previous' => $previousDisplay,
                'current'  => $currentDisplay,
                'status'   => $status,
            ];
        }

        usort($rows, fn(array $a, array $b) => $statusOrder[$a['status']] <=> $statusOrder[$b['status']]);

        return $rows;
    }

    private function getStatusColor(string $status): string
    {
        return match ($status) {
            'added'              => "\033[32m", // Green
            'changed'            => "\033[33m", // Yellow
            'not_changed_on_env' => "\033[31m", // Red
            'only_on_env'        => "\033[36m", // Cyan
            'same'               => "\033[90m", // Gray
            default              => "\033[0m",
        };
    }

    private function formatRows(array $rows): array
    {
        return array_map(
            function (array $row): array {
                $color = $this->getStatusColor($row['status']);
                $reset = "\033[0m";

                return [
                    $color . CliHelper::truncateText($row['key'], $this->maxLen) . $reset,
                    $color . CliHelper::truncateText($row['example'], $this->maxLen) . $reset,
                    $color . CliHelper::truncateText($row['previous'], $this->maxLen) . $reset,
                    $color . CliHelper::truncateText($row['current'], $this->maxLen) . $reset,
                    $color . $row['status'] . $reset,
                ];
            },
            $rows
        );
    }

    private function confirmEnvUpdate(string $envFile, string $newEnvRaw, string $currentEnvRaw): void
    {
        if ($currentEnvRaw === $newEnvRaw) {
            echo PHP_EOL . "\033[32mYour .env file is already up to date!\033[0m" . PHP_EOL;
            return;
        }

        echo PHP_EOL . "\033[1;33m⚠ Changes from .env.example are available for your .env file.\033[0m" . PHP_EOL;
        echo "\033[36mApply these changes to .env? \033[33m(yes/no) [no]: \033[0m";

        $answer = fgets(STDIN);
        if ($answer === false) {
            echo PHP_EOL . "\033[33mSkipped: .env file was not updated.\033[0m" . PHP_EOL;
            return;
        }

        $answer = trim($answer);

        if (in_array(strtolower($answer), ['yes', 'y'], true)) {
            $result = file_put_contents($envFile, $newEnvRaw);

            if ($result === false) {
                echo PHP_EOL . "\033[31mError: Unable to update .env file.\033[0m" . PHP_EOL;
                return;
            }

            echo PHP_EOL . "\033[32mSuccess: .env file updated successfully.\033[0m" . PHP_EOL;
        } else {
            echo PHP_EOL . "\033[33mSkipped: .env file was not updated.\033[0m" . PHP_EOL;
        }
    }
}