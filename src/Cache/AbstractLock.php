<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Xin\Cache;

use Xin\Contracts\Cache\Lock as LockContract;
use Xin\Contracts\Cache\LockTimeoutException;
use Xin\Support\Str;
use Xin\Support\Traits\InteractsWithTime;

abstract class AbstractLock implements LockContract
{

	use InteractsWithTime;

	/**
	 * The name of the lock.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The number of seconds the lock should be maintained.
	 *
	 * @var int
	 */
	protected $seconds;

	/**
	 * The scope identifier of this lock.
	 *
	 * @var string
	 */
	protected $owner;

	/**
	 * Create a new lock instance.
	 *
	 * @param string $name
	 * @param int $seconds
	 * @param string|null $owner
	 * @return void
	 */
	public function __construct($name, $seconds, $owner = null)
	{
		if (is_null($owner)) {
			$owner = Str::random();
		}

		$this->name = $name;
		$this->owner = $owner;
		$this->seconds = $seconds;
	}

	/**
	 * Attempt to acquire the lock.
	 *
	 * @return bool
	 */
	abstract public function acquire();

	/**
	 * Release the lock.
	 *
	 * @return void
	 */
	abstract public function release();

	/**
	 * Returns the owner value written into the driver for this lock.
	 *
	 * @return string
	 */
	abstract protected function getCurrentOwner();

	/**
	 * Attempt to acquire the lock.
	 *
	 * @param callable|null $callback
	 * @return mixed
	 */
	public function get($callback = null)
	{
		$result = $this->acquire();

		if ($result && is_callable($callback)) {
			try {
				return $callback();
			} finally {
				$this->release();
			}
		}

		return $result;
	}

	/**
	 * Attempt to acquire the lock for the given number of seconds.
	 *
	 * @param int $seconds
	 * @param callable|null $callback
	 * @return bool
	 * @throws \Xin\Contracts\Cache\LockTimeoutException
	 */
	public function block($seconds, $callback = null)
	{
		$starting = $this->currentTime();

		while (!$this->acquire()) {
			usleep(250 * 1000);

			if ($this->currentTime() - $seconds >= $starting) {
				throw new LockTimeoutException;
			}
		}

		if (is_callable($callback)) {
			try {
				return $callback();
			} finally {
				$this->release();
			}
		}

		return true;
	}

	/**
	 * Returns the current owner of the lock.
	 *
	 * @return string
	 */
	public function owner()
	{
		return $this->owner;
	}

	/**
	 * Determines whether this lock is allowed to release the lock in the driver.
	 *
	 * @return bool
	 */
	protected function isOwnedByCurrentProcess()
	{
		return $this->getCurrentOwner() === $this->owner;
	}

}
