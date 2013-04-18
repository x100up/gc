<?php
namespace Harpoon\Common\EmergencyInspector;

class Callback {

	/** @var callable */
	private $callback;

	/** @var array */
	private $callbackParams;

	/** @var int */
	private $emergencyType;

	/** @var int */
	private $emergencyCondition;

	/**
	 * @param array $callbackStructure
	 * @param int   $emergencyType
	 * @param int   $emergencyCondition
	 *
	 * @throws Callback\Exception
	 */
	public function __construct(array $callbackStructure, $emergencyType, $emergencyCondition = 1) {
		if (empty($callbackStructure)) {
			throw new Callback\Exception('Указана пустая структура callback-функции');
		}

		$callback = array_shift($callbackStructure);
		if (!is_callable($callback)) {
			throw new Callback\Exception('Указана неверная callback-функция');
		}

		$this->callback = $callback;

		if (!empty($callbackStructure)) {
			$this->callbackParams = $callbackStructure;
		}

		$this->emergencyType      = $emergencyType;
		$this->emergencyCondition = $emergencyCondition;
	}

	/**
	 * @param callable $callback
	 */
	public function setCallback($callback) {
		$this->callback = $callback;
	}

	/**
	 * @return callable
	 */
	public function getCallback() {
		return $this->callback;
	}

	/**
	 * @return int
	 */
	public function getEmergencyType() {
		return $this->emergencyType;
	}

	/**
	 * @return int
	 */
	public function getEmergencyCondition() {
		return $this->emergencyCondition;
	}

	public function execute() {
		if (empty($this->callbackParams)) {
			call_user_func($this->callback);
		} else {
			call_user_func_array($this->callback, $this->callbackParams);
		}
	}
}