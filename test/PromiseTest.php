<?php
/**
 * This file is part of the Promise package, a StreamCommon open software project.
 *
 * @copyright (c) 2019 StreamCommon Team.
 * @see https://github.com/streamcommon/promise
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Streamcommon\Test\Promise;

use PHPUnit\Framework\TestCase;
use Streamcommon\Promise\{Promise, ExtSwoolePromise, PromiseInterface};
use Streamcommon\Promise\Exception\RuntimeException;

/**
 * Class PromiseTest
 *
 * @package Streamcommon\Test\Promise
 */
class PromiseTest extends TestCase
{
    /**
     * Test sync promise
     *
     * @return void
     */
    public function testPromise(): void
    {
        $promise  = Promise::create(function ($resolver) {
            $resolver(41);
        });
        $promise2 = $promise->then(function ($value) {
            return $value + 1;
        });
        $promise3 = $promise->then();
        $promise->then(function ($value) {
            $this->assertEquals(41, $value);
        });
        $promise2->then(function ($value) {
            $this->assertEquals(43, $value);
        });
        $promise3->then(function ($value) {
            $this->assertEquals(41, $value);
        });
        $promise->wait();
    }

    /**
     * Test sync promise
     *
     * @return void
     */
    public function testPromiseResolve(): void
    {
        $promise = Promise::resolve(42);
        $promise->then(function ($value) {
            $this->assertEquals(42, $value);
        });
        $promise->wait();
    }


    /**
     * Test sync promise
     *
     * @return void
     */
    public function testPromiseReject(): void
    {
        $promise = Promise::reject(42);
        $promise->then(null, function ($value) {
            $this->assertEquals(42, $value);
        });
        $promise->wait();
    }

    /**
     * Test throw
     *
     * @return void
     */
    public function testPromiseThrow(): void
    {
        $promise = Promise::create(function ($resolver) {
            throw new RuntimeException();
        });
        $promise->then(null, function ($value) {
            $this->assertInstanceOf(RuntimeException::class, $value);
        });
        $promise->wait();
    }

    /**
     * Test sub promise
     *
     * @return void
     */
    public function testSubPromise(): void
    {
        $promise  = Promise::create(function (callable $resolve) {
            $promise = Promise::create(function (callable $resolve) {
                $resolve(41);
            });
            $promise->then(function ($value) use ($resolve) {
                $resolve($value);
            });
            $promise->wait();
        });
        $promise2 = $promise->then(function ($value) {
            return Promise::create(function (callable $resolve) use ($value) {
                $resolve($value + 1);
            });
        });
        $promise3 = $promise->then(function ($value) {
            return Promise::create(function (callable $resolve) use ($value) {
                $resolve($value + 1);
            })->then(function ($value) {
                return $value + 1;
            });
        });
        $promise->then(function ($value) {
            $this->assertEquals(41, $value);
        });
        $promise2->then(function ($value) {
            $this->assertEquals(42, $value);
        });
        $promise3->then(function ($value) {
            $this->assertEquals(43, $value);
        });
        $promise->wait();
    }

    /**
     * Test sub promise instance exception
     *
     * @return void
     */
    public function testSubPromiseException(): void
    {
        $promise = Promise::create(function (callable $resolve) {
            $resolve(new class implements PromiseInterface {
                /**
                 * @param callable|null $onFulfilled
                 * @param callable|null $onRejected
                 * @return PromiseInterface
                 */
                public function then(?callable $onFulfilled = null, ?callable $onRejected = null): PromiseInterface
                {
                    return $this;
                }

                /**
                 * @param callable $onRejected
                 * @return PromiseInterface
                 */
                public function catch(callable $onRejected): PromiseInterface
                {
                    return $this->then(null, $onRejected);
                }

                /**
                 * @param callable $promise
                 * @return PromiseInterface
                 */
                public static function create(callable $promise): PromiseInterface
                {
                    return new self();
                }

                /**
                 * @param mixed $value
                 * @return PromiseInterface
                 */
                public static function resolve($value): PromiseInterface
                {
                    return new self();
                }

                /**
                 * @param mixed $value
                 * @return PromiseInterface
                 */
                public static function reject($value): PromiseInterface
                {
                    return new self();
                }

                /**
                 * @param mixed $value
                 * @return PromiseInterface
                 */
                public static function all($value): PromiseInterface
                {
                    return new self();
                }
            });
        });
        $promise->then(null, function ($value) {
            $this->assertInstanceOf(RuntimeException::class, $value);
        });
        $promise->wait();
    }

    /**
     * Test promise all method
     *
     * @return void
     */
    public function testExtSwooleExtParallelPromisel(): void
    {
        $promise1 = Promise::create(function (callable $resolve) {
            $resolve(41);
        });
        $promise2 = Promise::create(function (callable $resolve) {
            $resolve(42);
        });
        /** @var Promise $ExtSwooleExtParallelPromisel */
        $ExtSwooleExtParallelPromisel = Promise::all([$promise1, $promise2]);
        $ExtSwooleExtParallelPromisel->then(function ($value) {
            $this->assertIsArray($value);
        });
        $ExtSwooleExtParallelPromisel->then(function ($value) {
            $this->assertEquals([41, 42], $value);
        });
        $ExtSwooleExtParallelPromisel->then(function ($value) {
            $this->assertEquals(41, $value[0]);
            $this->assertEquals(42, $value[1]);
        });
        $ExtSwooleExtParallelPromisel->wait();
    }

    /**
     * Test promise all method exception
     *
     * @return void
     */
    public function testExtSwooleExtParallelPromiselException(): void
    {
        $promise1 = Promise::create(function (callable $resolve) {
            $resolve(41);
        });
        $promise2 = ExtSwoolePromise::create(function (callable $resolve) {
            $resolve(42);
        });
        /** @var Promise $ExtSwooleExtParallelPromisel */
        $ExtSwooleExtParallelPromisel = Promise::all([$promise1, $promise2]);
        $ExtSwooleExtParallelPromisel->then(null, function ($value) {
            $this->assertInstanceOf(RuntimeException::class, $value);
        });
        $ExtSwooleExtParallelPromisel->wait();
    }
}
