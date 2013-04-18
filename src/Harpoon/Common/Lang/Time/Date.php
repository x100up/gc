<?php
namespace Harpoon\Common\Lang\Time;

use DateTime;

class Date extends DateTime {

	const DATE_FORMAT = 'Y-m-d';

	/**
	 * @param string $time
	 */
	public function __construct($time = 'now') {
		parent::__construct($time);
		parent::__construct($this->format('Y-m-d'));
	}

	/**
	 * <p>Без параметров форматирует дату в виде <i>Y-m-d</i></p>
	 * <p>Если передать параметр, то выполнится <i>DateTime::format(параметр)</i></p>
	 *
	 * @param null $format
	 *
	 * @return string
	 */
	public function format($format = null) {
		if (is_null($format)) {
			return parent::format(self::DATE_FORMAT);
		} else {
			return parent::format($format);
		}
	}
}
