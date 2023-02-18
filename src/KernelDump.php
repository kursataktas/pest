<?php

declare(strict_types=1);

namespace Pest;

use Pest\Support\View;
use Symfony\Component\Console\Output\OutputInterface;

final class KernelDump
{
    /**
     * The output buffer, if any.
     */
    private string $buffer = '';

    /**
     * Creates a new Kernel Dump instance.
     */
    public function __construct(
        private readonly OutputInterface $output,
    ) {
        // ...
    }

    /**
     * Enable the output buffering.
     */
    public function enable(): void
    {
        ob_start(function (string $message): string {
            $this->buffer .= $message;

            return '';
        });
    }

    /**
     * Disable the output buffering.
     */
    public function disable(): void
    {
        @ob_clean(); // @phpstan-ignore-line

        if ($this->buffer !== '') {
            $this->flush('INFO');
        }
    }

    /**
     * Shutdown the output buffering.
     */
    public function shutdown(): void
    {
        @ob_clean(); // @phpstan-ignore-line

        if ($this->buffer !== '') {
            $this->flush('ERROR');
        }
    }

    /**
     * Flushes the buffer.
     */
    private function flush(string $type): void
    {
        View::renderUsing($this->output);

        if ($this->isOpeningHeadline($this->buffer)) {
            $this->buffer = implode(PHP_EOL, array_slice(explode(PHP_EOL, $this->buffer), 2));
        }

        $this->buffer = trim($this->buffer);
        $this->buffer = rtrim($this->buffer, '.').'.';

        View::render('components.badge', [
            'type' => $type,
            'content' => $this->buffer,
        ]);

        $this->buffer = '';
    }

    /**
     * Checks if the given output contains an opening headline.
     */
    private function isOpeningHeadline(string $output): bool
    {
        return str_contains($output, 'by Sebastian Bergmann and contributors.');
    }
}