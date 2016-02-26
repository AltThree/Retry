<?php

/*
 * This file is part of Alt Three Retry.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AltThree\Retry;

use Closure;
use Exception;

/**
 * This is the command retrying middleware class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class RetryingMiddleware
{
    /**
     * Retry the command execution.
     *
     * @param object   $command
     * @param \Closure $next
     *
     * @return void
     */
    public function handle($command, Closure $next)
    {
        if (property_exists($command, 'attempts')) {
            if ($backoff = property_exists($command, 'backoff')) {
                $min = $command->backoff * 500;
                $max = $command->backoff * 1500;
            }

            $attempts = 0;

            while (++$attempts) {
                try {
                    return $next($command);
                } catch (Exception $e) {
                    if ($attempts >= $command->attempts) {
                        throw $e;
                    } elseif ($backoff) {
                        usleep(random_int($min, $max));
                    }
                }
            }
        }

        return $next($command);
    }
}
