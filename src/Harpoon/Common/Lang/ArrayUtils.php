<?php
namespace Harpoon\Common\Lang;

class ArrayUtils {

	/**
	 * @param mixed $needle
	 * @param array $haystack
	 * @param array $excludeKey
	 *
	 * @return array
	 */
	public static function multiSearch($needle, array &$haystack, $excludeKey = array()) {
		$result = [];

		foreach ($haystack as $k => $v) {
			if ($v === $needle) {
				if (!in_array($k, $excludeKey)) {
					$result[] = $k;
				}
			}
		}

		return $result;
	}

	/**
	 * Удаляет пустые элементы массива
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public static function deleteEmptyElements(array $array) {
		$result = [];

		foreach ($array as $k => $v) {
			if (mb_strlen($v) != 0) {
				$result[$k] = $v;
			}
		}

		return $result;
	}

	/**
	 * Берет элементы по кругу
	 *
	 * @param array $array
	 * @param int   $offset
	 * @param int   $length
	 *
	 * @return array
	 */
	public static function sliceCircle(array $array, $offset, $length) {
		$result = ['after' => [], 'before' => []];
		$min = $offset;
		$max = $offset + $length;

		if ($max < $min) {
			list($min, $max) = [$max, $min];
		}

		$size = sizeof($array);
		for ($pos = -$size; $pos <= $size; $pos++) {
			if ($min < $pos && $pos <= $max) {
				$result[$pos < 0 ? 'before' : 'after'][] = $array[$pos >= 0 ? $pos : $size + $pos];
			}
		}

		$result = array_merge($result['after'], $result['before']);

		return $result;
	}

	/**
	 * @param mixed $needle
	 * @param array $haystack
	 * @param bool  $subParamName
	 *
	 * @return bool|int|string
	 */
	public static function findElementPosition(&$needle, array &$haystack, $subParamName = false) {
		foreach ($haystack as $pos => $item) {
			if ($subParamName !== false && $item[$subParamName] == $needle) {
				return $pos;
			} elseif ($subParamName === false && $item == $needle) {
				return $pos;
			}
		}

		return false;
	}

	/**
	 * Возвращает сгруппированный по ключам массив значений элементов многомерного массива
	 *
	 * @param array $array
	 * @param array $keys
	 *
	 * @return array
	 */
	public static function filterByKeys(array &$array, array &$keys) {
		$result = [];

		foreach ($array as $sourceKey => $item) {
			foreach ($keys as $key) {
				$result[$key][$sourceKey] = is_array($item) ? $item[$key] : $item->$key;
			}
		}

		return $result;
	}

	/**
	 * @param mixed $object
	 *
	 * @return array|mixed
	 */
	public static function objectToArray($object) {
		if (!is_array($object) && !is_object($object)) {
			return $object;
		}

		$result = [];
		foreach ($object as $key => $value) {
			$result[$key] = self::objectToArray($value);
		}

		return $result;
	}

	/**
	 * Возвращает массив, созданный на основе исходного, но с использованием в качестве ключей значений указанного
	 * свойства объектов исходного массива.
	 *
	 * Метод определяет имя get-метода и пытается вызвать его для каждого элемента исходного массива. Полученное
	 * значение используется как ключ в результирующем массиве, а сам элемент - как значение.
	 *
	 * @static
	 *
	 * @param array  $objectSet
	 * @param string $keyProperty
	 *
	 * @return array
	 */
	public static function rekeyObjectSet(array &$objectSet, $keyProperty) {
		$result = [];

		$getter = 'get'.ucfirst($keyProperty);

		foreach ($objectSet as $object) {
			$key = $object->$getter();
			$result[$key] = $object;
		}

		return $result;
	}

	/**
	 * Удаляет первый элемент указанного массива, возвращая массив из двух элементов - ключа и значения данного элемента
	 *
	 * @static
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public static function shiftWithKey(array &$array) {
		$value = reset($array);
		$key = key($array);

		unset($array[$key]);

		return [$key, $value];
	}

	/**
	 * @param array $first
	 * @param array $second
	 *
	 * @return array
	 */
	public static function sumRecursive(array $first, array $second) {
		$sumResult = $first;

		foreach ($second as $key => $item) {
			if (isset($sumResult[$key])) {
				if (is_array($item)) {
					$sumResult[$key] = self::sumRecursive($sumResult[$key], $item);
				} else {
					$sumResult[$key] += $item;
				}
			} else {
				$sumResult[$key] = $item;
			}
		}

		return $sumResult;
	}

	/**
	 * @static
	 *
	 * @param array $array1
	 * @param array $array2
	 *
	 * @return int
	 *
	 * @link http://www.php.net/manual/ru/function.array-diff-assoc.php#73972
	 */
	public static function diffAssocRecursive($array1, $array2) {
		foreach ($array1 as $key => $value) {
			if (is_array($value)) {
				if (!isset($array2[$key])) {
					$difference[$key] = $value;
				} elseif (!is_array($array2[$key])) {
					$difference[$key] = $value;
				} else {
					$newDiff = self::diffAssocRecursive($value, $array2[$key]);
					if ($newDiff != false) {
						$difference[$key] = $newDiff;
					}
				}
			} elseif (!isset($array2[$key]) || $array2[$key] != $value) {
				$difference[$key] = $value;
			}
		}

		return !isset($difference) ? [] : $difference;
	}

	/**
	 * Функция для преобразования многомерного массива в одномерный путём "склеивания" измерений
	 * @static
	 *
	 * @param array  $multiDimensionArray
	 * @param string $glue
	 *
	 * @return array
	 */
	public static function buildSingleDimensional(array $multiDimensionArray, $glue) {
		$singleDimensional = [];

		foreach ($multiDimensionArray as $key => $item) {
			if (is_array($item)) {
				foreach (self::buildSingleDimensional($item, $glue) as $subKey => $subItem) {
					$singleDimensional[$key.$glue.$subKey] = $subItem;
				}
			} else {
				$singleDimensional[$key] = $item;
			}
		}

		return $singleDimensional;
	}

	/**
	 * Функция для преобразования одномерного массива в многомерный путём "разделения" строк массива на уровни
	 * @static
	 *
	 * @param array  $singleDimensional
	 * @param string $delimiter
	 *
	 * @return array
	 */
	public static function buildMultiDimensional(array $singleDimensional, $delimiter) {
		$multiDimensional = [];

		foreach ($singleDimensional as $key => $value) {
			$p =& $multiDimensional;

			foreach (explode($delimiter, $key) as $subKey) {
				if (!isset($p[$subKey])) {
					$p[$subKey] = [];
				}

				$p =& $p[$subKey];
			}

			$p = $value;
			unset($p);
		}

		return $multiDimensional;
	}

	/**
	 * Функция переиндексации массива с помощью значений указанного ключа
	 *
	 * @param array $array
	 * @param mixed $keyForRekey
	 *
	 * @return array
	 */
	public static function rekey(array &$array, $keyForRekey) {
		$newArray = [];

		foreach ($array as $item) {
			$newArray[$item[$keyForRekey]] = $item;
		}

		$array = $newArray;
	}

	/**
	 * Функция для получения значений из массива по указанному ключу
	 *
	 * @param array  $array
	 * @param string $columnKey
	 *
	 * @return array
	 */
	public static function column(array &$array, $columnKey) {
		$columnValues = [];

		foreach ($array as $item) {
			$columnValues[] = $item[$columnKey];
		}

		return $columnValues;
	}
}