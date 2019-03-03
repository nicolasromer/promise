<?php
/**
 * This file is part of the Common package, a StreamCommon open software project.
 *
 * @copyright (c) 2019 StreamCommon Team.
 * @see https://github.com/streamcommon/promise
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Streamcommon\Promise;

/**
 * Interface PromiseInterface
 *
 * @package Streamcommon\Promise
 * @see https://promisesaplus.com
 */
interface PromiseInterface
{
    const STATE_PENDING = 1;
    const STATE_FULFILLED = 0;
    const STATE_REJECTED = -1;

    /**
     * It be called after promise change stage
     *
     * @param callable|null $onFulfilled called after promise is fulfilled
     * @param callable|null $onRejected called after promise is rejected
     * @return PromiseInterface
     *
     * @see https://promisesaplus.com/#the-then-method
     */
    public function then(?callable $onFulfilled = null, ?callable $onRejected = null): PromiseInterface;
}
