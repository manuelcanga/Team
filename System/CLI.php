<?php
/**
 * This file is part of TEAM.
 *
 * TEAM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License.
 *
 * TEAM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with TEAM.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Team\System;

use Team\System\CLI\Options;

/**
 * Class CLI
 */
class CLI
{
    /**
     * List of ignored commands.
     * Example: php ./upgrade.php users
     * The command is './upgrade.php' and not 'php'
     */
    private const COMMANDS_TO_IGNORE = ['php'];
    /**
     * Value for PHP_SAPI for indicating cli environment
     */
    private const CLI_SAPI = 'cli';

    /**
     * Is current process a CLI process ?.
     * @var bool
     */
    private $is_cli = false;

    /**
     * Command which is being ran.
     * @var string
     */
    private $command = '';

    /**
     * Arguments count.
     * @var int
     */
    private $argc = 0;

    /**
     * Arguments without parsing
     * @var array
     */
    private $raw_argv = [];

    /**
     * Arguments after parsing
     * @var array|void
     */
    private $options = [];

    /**
     * CLI constructor.
     */
    public function __construct()
    {
        global $argc, $argv;

        if (!\is_int($argc) || !\is_array($argv)) {
            return;
        }

        $this->is_cli = self::CLI_SAPI === \PHP_SAPI || !empty($argv) || 0 !== $argc;

        if (!$this->is_cli) {
            return;
        }

        $this->command = $this->extractCommand($argv, $argc);
        $this->argc = $argc;
        $this->raw_argv = $argv;
        $this->options = $this->extractOptions($this->raw_argv);
    }

    /**
     * First argument is name of command( ej: php ./site/index.php -> command: index )
     * This funciton extract that argument
     *
     * @param array $argv
     * @param int $argc
     *
     * @return string
     */
    private function extractCommand(array & $argv, int & $argc): string
    {
        $command_with_path = \array_shift($argv);
        $command = FileSystem::basename($command_with_path);

        //minus because command is out now
        --$argc;

        if (\in_array($command, self::COMMANDS_TO_IGNORE, true)) {
            $command = $this->extractCommand($argv, $argc);
        }

        return $command;
    }

    /**
     * Extract CLI options from CLI arguments
     *
     * @param array $args CLI arguments where options will be extracted
     *
     * @return array
     */
    private function extractOptions(array $args): array
    {
        $options = new Options($args);

        return $options->extract();
    }

    /**
     * Retrieve if current process a CLI process ?
     *
     * @return boolean
     */
    public function isCli(): bool
    {
        return $this->is_cli;
    }

    /** Return a line input from terminal
     *  Very useful for data request
     *
     * @return string
     */
    public static function getLine(): string
    {
        return trim(fgets(STDIN));
    }

    /**
     * Display a new line for standar out
     *
     * @param string $out String to display in terminal
     *
     * @return void
     */
    public static function putLine(string $out): void
    {
        echo $out . "\n\r";
    }

    public function getRawArgs(): array
    {
        return $this->raw_argv;
    }

    public function countArgs(): int
    {
        return $this->argc;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getCommand(): string
    {
        return $this->command;
    }
}
