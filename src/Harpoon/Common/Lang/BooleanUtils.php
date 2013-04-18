<?php
namespace Harpoon\Common\Lang;

class BooleanUtils {

	/**
	 * Преобразует строку в булевское значение
	 *
	 * @param string $string
	 *
	 * @return boolean
	 */
	public static function str2boolean($string) {
		return mb_strtolower($string) == 'true';
	}

	/**
	 * Преобразует различные варианты переменной в булевское значение
	 * @param $any
	 * @return bool
	 */
	public static function any2boolean($any) {
		return ($any == 1 || in_array(strtolower($any), array('t', 'true', 'yes', 'on')));
	}

	/**
	 * Преобразует булевское значение в строку
	 * @param boolean $boolean
	 * @return string
	 */
	public static function boolean2str($boolean) {
		return $boolean ? 'true' : 'false';
	}
}
