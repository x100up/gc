<?php
namespace Harpoon\Common\Lang\Time;

use DateInterval;
use ErrorException;
use InvalidArgumentException;

class DatePeriod {

	/** @var Date */
	private $min;

	/** @var Date */
	private $max;

	/**
	 * Объект периода дат
	 *
	 * @param Date $min Начало периода
	 * @param Date $max Конец периода
	 *
	 */
	public function __construct(Date $min = null, Date $max = null) {
		$this->min = $min;
		$this->max = $max;
		$this->checkBounds();
	}

	/**
	 * Проверяет валидные ли заданы границы
	 *
	 * @throws InvalidArgumentException
	 */
	private function checkBounds() {
		if ($this->hasTwoBounds() && $this->min > $this->max) {
			throw new InvalidArgumentException('Дата начала должна быть больше даты окончания');
		} elseif (is_null($this->max) && is_null($this->min)) {
			throw new InvalidArgumentException('Должна быть задана хотя бы одна граница периода');
		}
	}

	/**
	 * Имеет ли период обе границы (нет null значений)
	 * @return bool
	 */
	public function hasTwoBounds() {
		return !(is_null($this->max) || is_null($this->min));
	}

	/**
	 * Проверяет имеет ли период обе границы (нет null значений)
	 *
	 * @throws ErrorException
	 */
	public function checkHasTwoBounds() {
		if (!$this->hasTwoBounds()) {
			throw new ErrorException('Должны быть заданы обе границы периода');
		}
	}

	/**
	 * Возвращает да, если дата начала совпадает с датой конца,
	 * т.е. период состоит из одного дня
	 *
	 * @return bool
	 */
	public function isOneDayPeriod() {
		return ($this->getMin() == $this->getMax());
	}

	/**
	 * Возвращает минимальную дату периода
	 *
	 * @return Date
	 */
	public function getMin() {
		return $this->min;
	}

	/**
	 * Возвращает максимальную дату периода
	 *
	 * @return Date
	 */
	public function getMax() {
		return $this->max;
	}

	/**
	 * Возвращает объект класса "интервал" для этого периода
	 * @return DateInterval
	 */
	public function getInterval() {
		return $this->min->diff($this->max);
	}

	/**
	 * Получает представление периода - список дат с указанной периодичностью
	 * @param DateInterval $interval
	 * @throws ErrorException
	 * @return DatePeriod
	 */
	public function getRepresentation(DateInterval $interval) {
		$this->checkHasTwoBounds();
		// Поскольку представление должно включать в себя дату конца периода,
		// чуть удлинняем временную переменную конца периода
		$max = clone $this->max;
		$max->add($interval);

		return new \DatePeriod($this->min, $interval, $max);
	}
}