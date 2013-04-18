<?php
namespace Harpoon\Common\EmergencyInspector;

use Harpoon\Common\Lang\Enum;

class EmergencyType extends Enum {

	const PHP_ERROR = 1;
	const OS_SIGNAL = 2;

	/** Битовая сумма всех вышеобъявленных констант */
	const ANY_FAILURE = 3;
}