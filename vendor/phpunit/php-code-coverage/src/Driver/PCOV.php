<?php declare(strict_types=1);
/*
 * This file is part of the php-code-coverage package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\CodeCoverage\Driver;

use function pcov\clear;
use function pcov\collect;
use function pcov\start;
use function pcov\stop;
use function pcov\waiting;
use const pcov\inclusive;

/**
 * Driver for PCOV code coverage functionality.
 *
 * @codeCoverageIgnore
 */
final class PCOV implements Driver
{
    /**
     * Start collection of code coverage information.
     */
    public function start(bool $determineUnusedAndDead = true): void
    {
        start();
    }

    /**
     * Stop collection of code coverage information.
     */
    public function stop(): array
    {
        stop();

        $waiting = waiting();
        $collect = [];

        if ($waiting) {
            $collect = collect( inclusive, $waiting);

            clear();
        }

        return $collect;
    }
}
