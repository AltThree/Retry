<?php

/*
 * This file is part of Alt Three Retry.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AltThree\Tests\Retry;

use AltThree\Retry\RetryingMiddleware;
use Exception;
use PHPUnit_Framework_TestCase;

/**
 * This is the retrying middleware test class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class RetryingMiddlewareTest extends PHPUnit_Framework_TestCase
{
    public function testHandlesBasic()
    {
        $output = (new RetryingMiddleware())->handle(new CommandStub1(), function () {
            return 'foo';
        });

        $this->assertSame('foo', $output);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Foo!
     */
    public function testHandlesExceptionOnce()
    {
        $times = 0;

        try {
            (new RetryingMiddleware())->handle(new CommandStub1(), function () use (&$times) {
                $times++;

                throw new Exception('Foo!');
            });
        } finally {
            $this->assertSame(1, $times);
        }
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Foo!
     */
    public function testHandlesExceptionMultiple()
    {
        $times = 0;

        try {
            (new RetryingMiddleware())->handle(new CommandStub2(), function () use (&$times) {
                $times++;

                throw new Exception('Foo!');
            });
        } finally {
            $this->assertSame(5, $times);
        }
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Foo!
     */
    public function testHandlesExceptionBackoff()
    {
        $times = 0;
        $current = microtime(true);

        try {
            (new RetryingMiddleware())->handle(new CommandStub3(), function () use (&$times) {
                $times++;

                throw new Exception('Foo!');
            });
        } finally {
            $this->assertSame(3, $times);
            $this->assertGreaterThan(0.1, microtime(true) - $current);
        }
    }
}

class CommandStub1
{
    //
}

class CommandStub2
{
    public $attempts = 5;
}

class CommandStub3
{
    public $attempts = 3;
    public $backoff = 200;
}
