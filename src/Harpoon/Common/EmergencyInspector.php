<?php
namespace Harpoon\Common;

use Closure;
use Harpoon\Common\EmergencyInspector\EmergencyType;

/**
 * Класс предназначен для выполнения необходимых действий при аварийном завершении работы скриптов.
 *
 * С помощью данного класса можно зарегистрировать callback-функции, выполнение которых произойдёт при
 * возникновении <b>E_ERROR</b>-ошибки или при перехвате определённого сигнала.<br>
 * <u>Важно помнить, что регистрация обработчика сигнала приводит к завершению работы скрипта в случае его
 * перехвата.</u> Также, при инициализации EmergencyInspector сигналы <i>SIGINT и SIGTERM</i> автоматически
 * регистрируются как аварийные.<br><br>
 * Также важно помнить, что при передаче деструктора в качестве callback-функции, необходимо самостоятельно
 * контролировать его "естественный" повторный вызов (к примеру, с помощью локальной статической переменной,
 * см. код деструктора в <i>Api\YandexMarket\Partner\RequestManager</i>)
 */
class EmergencyInspector {

	private static $anyFailureSignals = [SIGINT, SIGTERM];

	/** @var EmergencyInspector\CallbacksList */
	private $callbacks;

	/** @var array */
	private $registeredSignals = [];

	/** @var int */
	private $caughtSignal;

	/** @var array */
	private $phpErrorInfo;

	public function __construct() {
		$this->callbacks = new EmergencyInspector\CallbacksList();

		foreach (self::$anyFailureSignals as $signal) {
			$this->registerSignal($signal);
		}

		set_error_handler([$this, 'run']);
		register_shutdown_function([$this, 'run']);
	}

	/**
	 * @return int
	 */
	public function getCaughtSignal() {
		return $this->caughtSignal;
	}

	/**
	 * @return array
	 */
	public function getPhpErrorInfo() {
		return $this->phpErrorInfo;
	}

	/**
	 * @param int    $phpErrorLevel
	 * @param string $phpErrorMessage
	 * @param string $phpErrorFile
	 * @param int    $phpErrorLine
	 */
	public function run($phpErrorLevel = null, $phpErrorMessage = null, $phpErrorFile = null, $phpErrorLine = null) {
		static $inspected = false;

		if ($inspected) {
			exit(1);
		} else {
			$inspected = true;
		}

		$emergencyType  = $emergencyCondition = 0;
		$callbackCalled = false;

		$this->phpErrorInfo = error_get_last();
		if (is_null($this->phpErrorInfo) && !is_null($phpErrorLevel)) {
			$this->phpErrorInfo = [$phpErrorLevel, $phpErrorMessage, $phpErrorFile, $phpErrorLine];
		}

		if (!is_null($this->phpErrorInfo)) {
			$emergencyType      = EmergencyType::PHP_ERROR;
			$emergencyCondition = 1;
		} elseif (!is_null($this->caughtSignal)) {
			$emergencyType      = EmergencyType::OS_SIGNAL;
			$emergencyCondition = 1 << ($this->caughtSignal - 1);
		}

		/** Если тип аварии или условие неопределены, выходим (нормальное завершение скрипта) */
		if (($emergencyType * $emergencyCondition) == 0 ) {
			exit(0);
		}

		/** @var $callbackObject EmergencyInspector\Callback */
		foreach ($this->callbacks as $callbackObject) {
			/** Если тип аварии и условие аварии сходятся при бинарном сложении, осуществляем вызов */
			if (
				$callbackObject->getEmergencyType() & $emergencyType
				&& $callbackObject->getEmergencyCondition() & $emergencyCondition
			) {
				$callbackObject->execute();
				$callbackCalled = true;
			}
		}

		exit((int)$callbackCalled);
	}

	/**
	 * @param int $signal
	 */
	public function signalHandler($signal) {
		$this->caughtSignal = $signal;

		exit(1);
	}

	/**
	 * @param int $signal
	 */
	protected function registerSignal($signal) {
		pcntl_signal($signal, [$this, 'signalHandler']);
		pcntl_signal_dispatch();

		$this->registeredSignals[$signal] = true;
	}

	/**
	 * @param callable $callback
	 * @param bool     $append
	 */
	public function registerPhpErrorCallback($callback, $append = true) {
		$callbackObject = new EmergencyInspector\Callback([$callback], EmergencyType::PHP_ERROR);
		$this->registerCallbackObject($callbackObject, $append);
	}

	/**
	 * @param callable $callback
	 * @param array    $params
	 * @param bool     $append
	 */
	public function registerPhpErrorCallbackWithParams($callback, array $params, $append = true) {
		$callbackStructure = array_merge([$callback], $params);
		$callbackObject    = new EmergencyInspector\Callback($callbackStructure, EmergencyType::PHP_ERROR);
		$this->registerCallbackObject($callbackObject, $append);
	}

	/**
	 * @param int      $signal
	 * @param callable $callback
	 * @param bool     $append
	 */
	public function registerSignalCallback($signal, $callback, $append = true) {
		if (!isset($this->registeredSignals[$signal])) {
			$this->registerSignal($signal);
		}

		$callbackCondition = (1 << $signal - 1);
		$callbackObject    = new EmergencyInspector\Callback([$callback], EmergencyType::OS_SIGNAL, $callbackCondition);
		$this->registerCallbackObject($callbackObject, $append);
	}

	/**
	 * @param int      $signal
	 * @param array    $params
	 * @param callable $callback
	 * @param bool     $append
	 */
	public function registerSignalCallbackWithParams($signal, $callback, array $params, $append = true) {
		if (!isset($this->registeredSignals[$signal])) {
			$this->registerSignal($signal);
		}

		$callbackStructure = array_merge([$callback], $params);
		$callbackCondition = (1 << $signal - 1);
		$callbackObject    = new EmergencyInspector\Callback($callbackStructure, EmergencyType::OS_SIGNAL, $callbackCondition);
		$this->registerCallbackObject($callbackObject, $append);
	}

	/**
	 * @param callable $callback
	 * @param bool     $append
	 */
	public function registerEmergencyCallback($callback, $append = true) {
		$callbackObject = new EmergencyInspector\Callback([$callback], EmergencyType::ANY_FAILURE, (1 << 31) - 1);
		$this->registerCallbackObject($callbackObject, $append);
	}

	/**
	 * @param callable $callback
	 * @param array    $params
	 * @param bool     $append
	 */
	public function registerEmergencyCallbackWithParams($callback, array $params, $append = true) {
		$callbackStructure = array_merge([$callback], $params);
		$callbackCondition = (1 << 31) - 1;
		$callbackObject    = new EmergencyInspector\Callback($callbackStructure, EmergencyType::ANY_FAILURE, $callbackCondition);
		$this->registerCallbackObject($callbackObject, $append);
	}

	/**
	 * @param EmergencyInspector\Callback $callbackObject
	 * @param bool                        $append
	 */
	protected function registerCallbackObject(EmergencyInspector\Callback $callbackObject, $append) {
		$callback = $callbackObject->getCallback();
		if ($callback instanceof Closure) {
			$callbackObject->setCallback($callback->bindTo($this));
		}

		if ($append === false) {
			$this->callbacks = new EmergencyInspector\CallbacksList();
		}

		$this->callbacks->push($callbackObject);
	}
}