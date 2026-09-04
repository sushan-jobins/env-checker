<?php

namespace SushanJobins\EnvChecker\Commands;

class HelpCommand
{
    public function handle(): void
    {
        echo PHP_EOL;
        echo "ENV Manager" . PHP_EOL;
        echo PHP_EOL;

        echo "Compare and sync environment variables between .env.example and .env." . PHP_EOL;
        echo PHP_EOL;

        echo "Usage:" . PHP_EOL;
        echo "  envm [option]" . PHP_EOL;
        echo PHP_EOL;

        echo "Options:" . PHP_EOL;
        echo "  --status=<status>   Show environment variables by status" . PHP_EOL;
        echo "  --info-status       Show description of all statuses" . PHP_EOL;
        echo "  --dry               Preview changes without updating .env" . PHP_EOL;
        echo "  --help, -h          Show this help message" . PHP_EOL;
        echo PHP_EOL;

        echo "Available statuses:" . PHP_EOL;
        echo "  all" . PHP_EOL;
        echo "  added" . PHP_EOL;
        echo "  changed" . PHP_EOL;
        echo "  not_changed_on_env" . PHP_EOL;
        echo "  only_on_env" . PHP_EOL;
        echo "  same" . PHP_EOL;
        echo "  unchanged" . PHP_EOL;
        echo PHP_EOL;

        echo "Examples:" . PHP_EOL;
        echo "  envm" . PHP_EOL;
        echo "  envm --status=all" . PHP_EOL;
        echo "  envm --status=added" . PHP_EOL;
        echo "  envm --status=changed" . PHP_EOL;
        echo "  envm --status=same" . PHP_EOL;
        echo "  envm --info-status" . PHP_EOL;
        echo "  envm --dry" . PHP_EOL;
    }
}