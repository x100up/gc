<?php
namespace Harpoon\Common\Lang\Time;

use DateTime;

class DateUtils {

	/**
	 * @param DateTime $datetime
	 *
	 * @return DateTime
	 */
	public static function calcMonthStart(DateTime $datetime) {
		$calcResult = clone $datetime;

		$calcResult->modify('midnight first day of this month');

		return $calcResult;
	}

	/**
	 * @param DateTime $datetime
	 *
	 * @return DateTime
	 */
	public static function calcMonthEnd(DateTime $datetime) {
		$calcResult = clone $datetime;

		$calcResult->modify('midnight first day of next month -1 second');

		return $calcResult;
	}

	/**
	 * @param DateTime $datetime
	 *
	 * @return DateTime
	 */
	public static function calcNextMonthStart(DateTime $datetime) {
		$calcResult = clone $datetime;

		$calcResult->modify('midnight first day of next month');

		return $calcResult;
	}

	/**
	 * @param DateTime $datetime
	 *
	 * @return DateTime
	 */
	public static function calcPreviousMonthEnd(DateTime $datetime) {
		$calcResult = clone $datetime;

		$calcResult->modify('midnight first day of this month -1 second');

		return $calcResult;
	}

	/**
	 * @param DateTime $datetime
	 *
	 * @return DateTime
	 */
	public static function calcWeekStart(DateTime $datetime) {
		$calcResult = clone $datetime;
		$whichWeek  = 'this';

		if ($calcResult->format('w') == 0) {
			$whichWeek = 'previous';
		}

		$calcResult->modify("midnight Monday {$whichWeek} week");

		return $calcResult;
	}

	/**
	 * @param DateTime $datetime
	 *
	 * @return DateTime
	 */
	public static function calcWeekEnd(DateTime $datetime) {
		$calcResult = clone $datetime;
		$whichWeek  = 'next';

		if ($calcResult->format('w') == 0) {
			$whichWeek = 'this';
		}

		$calcResult->modify("midnight Monday {$whichWeek} week -1 second");

		return $calcResult;
	}

	/**
	 * @param DateTime $datetime
	 *
	 * @return DateTime
	 */
	public static function calcNextWeekStart(DateTime $datetime) {
		$calcResult = clone $datetime;
		$whichWeek  = 'next';

		if ($calcResult->format('w') == 0) {
			$whichWeek = 'this';
		}

		$calcResult->modify("midnight Monday {$whichWeek} week");

		return $calcResult;
	}

	/**
	 * @param DateTime $datetime
	 *
	 * @return DateTime
	 */
	public static function calcPreviousWeekEnd(DateTime $datetime) {
		$calcResult = clone $datetime;
		$whichWeek  = 'this';

		if ($calcResult->format('w') == 0) {
			$whichWeek = 'previous';
		}

		$calcResult->modify("midnight Monday {$whichWeek} week -1 second");

		return $calcResult;
	}

	/**
	 * @param DateTime $datetime
	 *
	 * @return DateTime
	 */
	public static function calcDayStart(DateTime $datetime) {
		$calcResult = clone $datetime;

		$calcResult->modify('midnight');

		return $calcResult;
	}

	/**
	 * @param DateTime $datetime
	 *
	 * @return DateTime
	 */
	public static function calcDayEnd(DateTime $datetime) {
		$calcResult = clone $datetime;

		$calcResult->modify('midnight next day -1 second');

		return $calcResult;
	}

	/**
	 * @return int
	 */
	public static function getMilliseconds() {
		return (int)floor(microtime(true) * 1000);
	}

	/**
	 * @return int
	 */
	public static function getMicroseconds() {
		return (int)floor(microtime(true) * 1000000);
	}

	/**
	 * @return DateTime
	 */
	public static function getNextMidnight() {
		$datetime = new DateTime();
		$datetime->modify('midnight next day');

		return $datetime;
	}

	/**
	 * @return DateTime
	 */
	public static function getNextHour() {
		$datetime = new DateTime();
		$datetime->setTime($datetime->format('G') + 1, 0);

		return $datetime;
	}

	/**
	 * @return DateTime
	 */
	public static function getNext30Minutes() {
		$datetime = new DateTime();
		$datetime->setTime($datetime->format('G'), $datetime->format('i') + 30);

		return $datetime;
	}

	/**
	 * Возвращает день недели для даны в привычной форме (0 - понедельник)
	 */
	public static function getRealDayOfWeek(\DateTime $date) {
		$dayOfWeek = $date->format('w') - 1;
		if ($dayOfWeek < 0) {
			$dayOfWeek = 6;
		}

		return $dayOfWeek;
	}

	/**
	 * @param string $date
	 *
	 * @return string
	 */
	public static function dateToTime($date) {
		$d = new \DateTime($date);
		return $d->format('H:i');
	}

	/**
	 * Возвращает имя месяца в родительном падеже
	 *
	 * @param $monthIndex - индекс месяца с 1 по 12
	 */
	public static function getMonthRod($monthIndex) {
		$months = array(
			'января',
			'февраля',
			'марта',
			'апреля',
			'мая',
			'июня',
			'июля',
			'августа',
			'сентября',
			'октября',
			'ноября',
			'декабря'
		);

		return $months[$monthIndex - 1];
	}

	/**
	 * Преобразует дату в читаемый удобный вид
	 *
	 * @param string $date
	 *
	 * @return string
	 */
	public static function dateToString($date) {
		$d = new \DateTime($date);
		$diff = $d->diff(new \DateTime());
		switch ($diff->format('%a')) {
			case 0:
				return 'сегодня';
			case 1:
				return 'вчера';
			case 2:
				return 'позавчера';
			default:
				return (integer)$d->format('d').' '.self::getMonthRod((integer)$d->format('m')).' '.$d->format('Y');
		}
	}

	/**
	 * Возвращает текущую дату в текстовом формате 'Y-m-d H:i:s'
	 */
	public static function now() {
		return date('Y-m-d H:i:s');
	}
}
