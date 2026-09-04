<?php

namespace SushanJobins\EnvChecker\Commands;

class InfoStatusCommand
{
    public function handle(): void
    {
        $statuses = [
            'added' => [
                'color' => "\033[32m",
                'description' => 'Key did not exist in the previous .env but exists in the current .env.',
            ],
            'changed' => [
                'color' => "\033[33m",
                'description' => 'Key existed before, but its value has changed in the current .env.',
            ],
            'not_changed_on_env' => [
                'color' => "\033[31m",
                'description' => '.env value has not changed, but it differs from .env.example.',
            ],
            'only_on_env' => [
                'color' => "\033[36m",
                'description' => 'Environment variable exists in .env but is not defined in .env.example.',
            ],
            'same' => [
                'color' => "\033[90m",
                'description' => 'Current .env value is the same as the value in .env.example.',
            ],
        ];

        $reset = "\033[0m";

        echo PHP_EOL;
        echo "\033[1;36mEnvironment Variable Status Information{$reset}" . PHP_EOL;
        echo PHP_EOL;

        foreach ($statuses as $status => $info) {
            echo $info['color'] . strtoupper($status) . $reset . PHP_EOL;
            echo "  -> " . $info['description'] . PHP_EOL . PHP_EOL;
        }
    }
}