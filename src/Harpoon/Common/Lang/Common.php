<?php
namespace Harpoon\Common\Lang;

/**
 * Тут быть ничего не должно. Все, что тут находится должно либо находится в том единственном месте, где используется,
 * либо в составе будущей библиотеки
 */
class Common {

	/**
	 * Сравнивает две переменных
	 * @param $a
	 * @param $b
	 * @return int
	 */
	public static function sortAsc($a, $b) {
		if (mb_strlen($a[0]) > mb_strlen($b[0])) {
			return -1;
		}

		if (mb_strlen($a[0]) < mb_strlen($b[0])) {
			return 1;
		} else {
			return 0;
		}
	}

	/**
	 * Возвращает Url, дописывая http://, если он отсутствует
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public static function getUrlWithProtocol($url) {
		$parsedUrl = parse_url($url);
		if (!isset($parsedUrl['scheme'])) {
			return 'http://'.$url;
		} else {
			return $url;
		}
	}

	/**
	 * Строит url подставляя новые и заменяя сташые параметры из url
	 *
	 * @param string $url
	 * @param array $mergeParams
	 * @throws \Exception
	 * @return string|string
	 */
	public static function mergeUrlParams($url, array $mergeParams) {
		$url = trim($url);

		if (sizeof($mergeParams) > 0 && $url !== '') {
			// Парсим url
			$parsedUrl = parse_url($url);
			if (!isset($parsedUrl['scheme'])) {
				throw new \Exception('Некорректный URL -  "'.$url.'"');
			}

			// Разбираем параметры на массив
			$params = array();
			if (isset($parsedUrl['query'])) {
				$pairs = explode('&', $parsedUrl['query']);
				foreach ($pairs as $pair) {
					$pair = explode('=', $pair, 2);
					$key = $pair[0];
					$value = isset($pair[1]) ? rawurldecode($pair[1]) : '';

					$params[$key] = $value;
				}
			}

			// Добавляем в ранее разобранный массив новые параметры,
			// а старые заменяем соответсчтвенно
			foreach ($mergeParams as $k => $v) {
				$params[$k] = $v;
			}

			// Собираем параметры обратно
			if (sizeof($params) > 0) {
				$pairs = array();
				foreach ($params as $pKey => $pValue) {
					$pairs[] = $pKey.'='.rawurlencode($pValue);
				}
				$parsedUrl['query'] = implode('&', $pairs);
			}

			// Клеим urk заново
			$path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
			$query = isset($parsedUrl['query']) ? '?'.$parsedUrl['query'] : '';
			$fragment = isset($parsedUrl['fragment']) ? '#'.$parsedUrl['fragment'] : '';

			$url = $parsedUrl['scheme'].'://'.$parsedUrl['host'].$path.$query.$fragment;
		}

		return $url;
	}


	/**
	 * @param mixed $value
	 *
	 * @return null|mixed
	 */
	public static function nullIfEmpty($value) {
		return empty($value) ? null : $value;
	}

	/**
	 * @param array $classNameElements
	 * @param bool  $nsZendStyle
	 *
	 * @return string
	 */
	public static function buildClassName(array $classNameElements, $nsZendStyle = false) {
		if ($nsZendStyle) {
			$className = implode('_', $classNameElements);
		} else {
			$className = '\\'.implode('\\', $classNameElements);
		}

		if (!class_exists($className) && !$nsZendStyle) {
			$className = self::buildClassName($classNameElements, true);
		}

		return $className;
	}


}
