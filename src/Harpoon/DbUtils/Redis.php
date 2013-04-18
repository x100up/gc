<?php
namespace Harpoon\DbUtils;

use Harpoon\Common\Lang\StringUtils;
use Harpoon\Common\Lang\Time\DateUtils;
use Harpoon\DbUtils\Redis\LockWaitingException;

/**
 * @method keys($pattern)
 * @method delete($key)
 * @method lRemove($key, $value, $count)
 * @method lRem($key, $value, $count)
 * @method exists($key)
 * @method expire($key, $expire)
 * @method expireAt($key, $expireAt)
 * @method pexpire($key, $expireInMilliseconds)
 * @method pexpireAt($key, $expireAtInMilliseconds)
 * @method ttl($key)
 * @method pttl($key)
 * @method lSize($key)
 * @method lGet($key, $index)
 * @method lLen($key)
 * @method lPop($key)
 * @method sAdd($key, $index)
 * @method sRemove($key, $index)
 * @method sMembers($key)
 * @method hGetAll($key)
 * @method hLen($key)
 * @method rPush($key, $index)
 * @method lPush($key, $index)
 * @method lTrim($key, $start, $stop)
 * @method lRange($key, $index, $offset)
 * @method lGetRange($key, $index, $offset)
 * @method multi()
 * @method exec()
 *
 * @method connect($host, $port)
 * @method auth($password)
 * @method select($dbIndex)
 */
class Redis extends \Redis {

	/**
	 * @param string $host
	 * @param int    $port
	 * @param string $password
	 * @param int    $dbIndex
	 */
	public function __construct($host, $port, $password, $dbIndex) {
		parent::__construct();

		$this->connect($host, $port);
		$this->auth($password);
		$this->select($dbIndex);
	}

	/**
	 * @param string $hashName
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function hGet($hashName, $key) {
		$value  = parent::hGet($hashName, $key);
		$result = StringUtils::jsonDecode($value);

		if ($result) {
			return $result;
		} else {
			return $value;
		}
	}

	/**
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get($key) {
		$value  = parent::get($key);
		$result = StringUtils::jsonDecode($value);

		if ($result) {
			return $result;
		} else {
			return $value;
		}
	}

	/**
	 * Просто устанавливает значение для ключа
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return bool
	 */
	public function set($key, $value) {
		$value = $this->jsonEncodeIfNeed($value);

		return parent::set($key, $value);
	}

	/**
	 * Записывает значение и устанавливает время жизни в секундах
	 *
	 * @param string $key
	 * @param int    $seconds
	 * @param mixed  $value
	 *
	 * @return bool
	 */
	public function setex($key, $seconds, $value) {
		$value = $this->jsonEncodeIfNeed($value);

		return parent::setex($key, $seconds, $value);
	}

	/**
	 * Записывает значение и устанавливает время жизни в миллисекундах (1/1000 секунды)
	 *
	 * @param string $key
	 * @param int    $milliseconds
	 * @param mixed  $value
	 *
	 * @return bool
	 */
	public function psetex($key, $milliseconds, $value) {
		$value = $this->jsonEncodeIfNeed($value);

		return parent::psetex($key, $milliseconds, $value);
	}

	/**
	 * Устанавливает значение только если указанного ключа не существует.
	 * Возвращает true или false в зависимости от результата установки
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return bool
	 */
	public function setnx($key, $value) {
		$value = $this->jsonEncodeIfNeed($value);

		return parent::setnx($key, $value);
	}

	/**
	 * Хеш - это по сути ассоциативный массив
	 * А $key - это ключ к одному из его элементов
	 *
	 * @param string $hashName
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return int|bool
	 */
	public function hSet($hashName, $key, $value) {
		$value = $this->jsonEncodeIfNeed($value);

		return parent::hSet($hashName, $key, $value);
	}

	/**
	 * @param mixed $value
	 *
	 * @return string
	 */
	private function jsonEncodeIfNeed($value) {
		if (is_array($value)) {
			$value = StringUtils::jsonEncode($value);
		}

		return $value;
	}

	/**
	 * TODO: Comment it!
	 *
	 * @param callback   $callback
	 * @param string     $lockKey
	 * @param int        $lockTTLInMilliseconds
	 * @param int        $lockWaitingTimeoutInSeconds
	 * @param \Exception $customException
	 *
	 * @throws \Exception|null|LockWaitingException
	 * @return mixed
	 */
	public function tryLockWithCallback(
		$callback, $lockKey, $lockTTLInMilliseconds, $lockWaitingTimeoutInSeconds, $customException = null
	) {
		$stopTime = time() + $lockWaitingTimeoutInSeconds;

		try {
			while ($this->setnx($lockKey, 1) === false) {
				if ($stopTime - time() > 0) {
					usleep($lockTTLInMilliseconds * 1000);
				} else {
					throw new Redis\LockWaitingException;
				}
			}
		} catch (Redis\LockWaitingException $e) {
			throw (is_null($customException) ? $e : $customException);
		}

		$lockExpireAt = DateUtils::getMilliseconds() + $lockTTLInMilliseconds;
		$this->pexpireAt($lockKey, $lockExpireAt);

		$result = call_user_func($callback, $this);

		if (DateUtils::getMilliseconds() < $lockExpireAt) {
			$this->delete($lockKey);
		}

		return $result;
	}
}
