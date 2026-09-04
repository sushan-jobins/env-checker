<?php

namespace SushanJobins\EnvChecker\Commands;

use SushanJobins\EnvChecker\EnvChecker;
use SushanJobins\EnvChecker\Helpers\CliHelper;
use SushanJobins\EnvChecker\Helpers\TableHelper;

class DryRunCommand
{
    public function __construct(
        private EnvChecker $checker,
        private int $maxLen = 40
    ) {}

    public function handle(string $path): void
    {
        echo "\033[33m[DRY RUN] Checking missing environment keys...\033[0m" . PHP_EOL;
        echo PHP_EOL;

        $missingKeys = $this->checker->getMissingKeys($path);

        if (!empty($missingKeys)) {
            echo "\033[1;33mThe following keys are missing from your .env file:\033[0m" . PHP_EOL;
            echo PHP_EOL;

            $rows = array_map(
                fn(array $missing): array => [
                    CliHelper::truncateText($missing['key'], $this->maxLen),
                    CliHelper::truncateText($missing['value'], $this->maxLen),
                ],
                $missingKeys
            );

            TableHelper::render(
                [
                    '.env.example',
                    'value in .env.example',
                ],
                $rows
            );
        } else {
            echo "\033[32mClean match! No missing environment keys found.\033[0m" . PHP_EOL;
        }

        echo PHP_EOL;
        echo "\033[33m[DRY RUN] No files were modified.\033[0m" . PHP_EOL;
    }
}