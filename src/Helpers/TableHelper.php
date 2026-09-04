<?php

namespace SushanJobins\EnvChecker\Helpers;

class TableHelper
{
    public static function render(array $headers, array $rows): void
    {
        if (empty($rows)) {
            echo "No environment variables found for the selected status." . PHP_EOL;
            return;
        }

        $headerColor = "\033[1;36m";
        $reset       = "\033[0m";

        $widths = [];

        foreach ($headers as $index => $header) {
            $widths[$index] = strlen(CliHelper::stripAnsi((string) $header));

            foreach ($rows as $row) {
                $plainValue = CliHelper::stripAnsi((string) ($row[$index] ?? ''));

                $widths[$index] = max(
                    $widths[$index],
                    strlen($plainValue)
                );
            }
        }

        $separator = '+';
        foreach ($widths as $width) {
            $separator .= str_repeat('-', $width + 2) . '+';
        }

        echo $separator . PHP_EOL . '|';

        foreach ($headers as $index => $header) {
            $headerStr   = (string) $header;
            $plainHeader = CliHelper::stripAnsi($headerStr);
            $padding     = $widths[$index] - strlen($plainHeader);

            echo ' ' . $headerColor . $headerStr . str_repeat(' ', max(0, $padding)) . $reset . ' |';
        }

        echo PHP_EOL . $separator . PHP_EOL;

        foreach ($rows as $row) {
            echo '|';

            foreach ($widths as $index => $width) {
                $cellValue = (string) ($row[$index] ?? '');
                $plainText = CliHelper::stripAnsi($cellValue);
                $padding   = $width - strlen($plainText);

                echo ' ' . $cellValue . str_repeat(' ', max(0, $padding)) . ' |';
            }

            echo PHP_EOL;
        }

        echo $separator . PHP_EOL;
    }
}