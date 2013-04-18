<?php
namespace Harpoon\Common\Lang\Time;

class Timer {

	const MICROSECONDS_IN_SECOND = 1000000;

	private $start;

	private $stop;

	private $total;


	public function start() {
		$this->start = microtime(true);
	}

	/**
	 * @return mixed
	 */
	public function stop() {
		$this->stop = microtime(true);
		$diff = $this->stop - $this->start;
		$this->total += $diff;

		return $diff;
	}

	public function getTotal() {
		return $this->total;
	}

	/**
	 * @param string $text
	 */
	public function stopAndPrint($text) {
		echo $text.' '.$this->stop()."\n";
	}

	/**
	 * @param int $microSeconds
	 * @return bool
	 */
	public function timeIsOver($microSeconds) {
		$nowMicroseconds   = (int)(microtime(true) * self::MICROSECONDS_IN_SECOND);
		$startMicroseconds = (int)($this->start * self::MICROSECONDS_IN_SECOND);
		$timeLeftInMicroseconds  = $nowMicroseconds - $startMicroseconds;
		$timeIsOver = $timeLeftInMicroseconds >= $microSeconds;

		return $timeIsOver;
	}
}
