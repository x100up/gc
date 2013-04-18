<?php
namespace Harpoon\Common\Lang;

class StringUtils {


	const EN_KEYBOARD = "qwertyuiop[]asdfghjkl;'zxcvbnm,.";

	const RU_KEYBOARD = "йцукенгшщзхъфывапролджэячсмитьбю";

	public static $arrayWordColor = array(
		'black', 'silver', 'grey', 'white', 'red', 'maroon', 'purple', 'fuchsia',
		'green', 'lime', 'pink', 'gold',
		'olive', 'yellow', 'orange', 'blue', 'navy', 'белый', 'белая', 'черный',
		'черная', 'нержавейка', 'стальной',
		'серебристый', 'серебристая', 'зеленый', 'зеленая', 'красный', 'красная',
		'пурпурный', 'пурпурная', 'бордовый',
		'беж', 'бежевый', 'бежевая', 'фуксия', 'желтый', 'желтая', 'голубой', 'голубая',
		'оранжевый', 'оранжевая', 'синий', 'obsidian',
		'синяя', 'фиолетовый', 'фиолетовая', 'cream', 'кремовый', 'кремовая', 'черн.',
		'экон.', 'см.', 'нерж', 'чёрный'
	);

	/**
	 * Корректный json-кодировщик, правильно сохраняющий UTF-8
	 * @param $value
	 * @return string
	 */
	public static function jsonEncode($value) {
		$content = json_encode($value, JSON_HEX_AMP);
		$content = preg_replace("/\\\u([0-9a-z]{4})/", '&#x$1;', $content);

		return html_entity_decode($content, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * Декодировщик json
	 * @param $string
	 * @param $assoc - вернуть результат в виде ассоциативного массива
	 * @return mixed
	 */
	public static function jsonDecode($string, $assoc = true) {
		return json_decode($string, $assoc);
	}

	/**
	 * Берет уникальные слова из строки
	 * @param $str
	 * @return array
	 */
	public static function getUniqueWords($str) {
		$str = trim($str);
		if ($str === '') {
			return array();
		}
		return array_unique(explode(' ', $str));
	}

	/**
	 * Получает общее количество слов в строке, разделенных одним пробелом.
	 * Двойные пробелы не склеиваются, т.е. строка "a  b" будет посчитана как 3 слова,
	 * т.к. "a" и "b" разделены двумя пробелами
	 *
	 * @param string $str
	 * @return int
	 */
	public static function getWordsCount($str) {
		return sizeof(explode(' ', trim($str)));
	}

	/**
	 * Ищет в строке символы, и удаляет символ и все что после него до конца строки
	 *
	 * @param string $str
	 * @param string|string[] $ignoreChars
	 * @return string $str
	 */
	public static function removeCharacterAndEverythingAfter($str, $ignoreChars) {
		$ignoreChars = (array)$ignoreChars;
		foreach ($ignoreChars as $ignoreChar) {
			$pos = mb_strpos($str, $ignoreChar);
			if ($pos !== false) {
				$str = mb_substr($str, 0, $pos);
				return $str;
			}
		}
		return $str;
	}

	/**
	 * Приводит к нижнему регистру все слова кроме первого и аббревиатур
	 *
	 * @param string $str
	 * @return string
	 */
	public static function lowercaseExceptAbbreviation($str) {
		$words = explode(' ', $str);
		$ret = array();
		$ret[] = array_shift($words);
		foreach ($words as $str) {
			// Если первые две буквы не верхнего регистра - это не аббривеатура
			if (preg_match("/[a-zа-яеё]/su", mb_substr($str, 0, 2))) {
				$str = mb_strtolower($str);
			}
			$ret[] = $str;
		}
		return implode(' ', $ret);
	}

	/**
	 * Удаляет двойные пробелы. Использует trimTwoSymbols
	 *
	 * @param string $str
	 *
	 * @return string
	 */
	public static function trimTwoSpaces($str) {
		return self::trimTwoSymbols($str, ' ');
	}

	/**
	 * Удаляет повторяющийся более одного раза подряд символ, указанный в параметре
	 *
	 * @param string $str
	 * @param string $symbol
	 * @return string
	 */
	public static function trimTwoSymbols($str, $symbol) {
		return preg_replace("/{$symbol}{$symbol}+/", $symbol, trim($str));
	}

	/**
	 * Переводит строку в неправильную раскладку
	 *
	 * @param string $str
	 * @param string $layoutFrom.
	 * @return string
	 */
	public static function wrongKeyboardLayout($str, $layoutFrom = "auto") {
		$str = mb_strtolower($str);
		if ($layoutFrom == 'auto') {
			$ru = preg_match("/([а-яё])/isu", $str);
			$en = preg_match("/([a-z])/isu", $str);

			if ((!$ru && !$en) || ($ru && $en)) {
				$layoutFrom = '';
			} elseif ($ru && !$en) {
				$layoutFrom = 'ru';
			} elseif (!$ru && $en) {
				$layoutFrom = 'en';
			}
		}
		switch (mb_strtolower($layoutFrom)) {
			case 'en':
				$str = str_replace(
					self::mbStrSplit(self::EN_KEYBOARD),
					self::mbStrSplit(self::RU_KEYBOARD),
					$str
				);
				break;

			case 'ru':
				$str = str_replace(
					self::mbStrSplit(self::RU_KEYBOARD),
					self::mbStrSplit(self::EN_KEYBOARD),
					$str
				);
				break;

			default:
				$str = '';
		}
		return $str;
	}

	/**
	 * Разбивает строку посимвольно
	 *
	 * @param string $string
	 * @return string
	 */
	public static function mbStrSplit($string) {
		return preg_split('/(?<!^)(?!$)/us', $string);
	}

	/**
	 * Первую букву каждого слова в верхний регистр, остальные приводит в нижний регистр
	 *
	 * @param string $str
	 * @return string
	 */
	public static function mbUcWord($str) {
		$ret = array();
		foreach (explode(' ', $str) as $word) {
			$ret[] = self::mbUcFirst($word);
		}
		return implode(' ', $ret);
	}

	/**
	 * Первую букву строки в верхний регистр, остальные в нижний
	 *
	 * @param string $str
	 * @param bool   $lowerStrEnd
	 * @return string
	 */
	public static function mbUcFirst($str, $lowerStrEnd = false) {
		$firstLetter = mb_strtoupper(mb_substr($str, 0, 1));
		if ($lowerStrEnd) {
			$strEnd = mb_strtolower(mb_substr($str, 1, mb_strlen($str)));
		} else {
			$strEnd = mb_substr($str, 1, mb_strlen($str));
		}
		$str = $firstLetter.$strEnd;
		return $str;
	}

	/**
	 * Приводит строку к первому подходящему варианту по длине строки,
	 * вырезая слова (по разделителю), начиная с конца.
	 *
	 * @param string $str
	 * @param string[] $sepArray - массив разделителей
	 * @param int $maxLength
	 * @return string
	 */
	public static function cutBackWord($str, array $sepArray, $maxLength) {
		$str = trim($str);

		if (mb_strlen($str) <= $maxLength) {
			return $str;
		}

		$maxSepLength = 0;
		foreach ($sepArray as $k => $sep) {
			$sepArray[$k] = mb_strtolower($sep);
			$l = mb_strlen($sep);
			$maxSepLength = $maxSepLength > $l ? $maxSepLength : $l;
		}

		$cutStr = mb_substr($str, 0, $maxLength + $maxSepLength + 1);
		$lowerCutStr = mb_strtolower($cutStr);

		$maxPos = false;
		while (true) {
			$find = false;
			foreach ($sepArray as $sep) {
				$pos = mb_strpos($lowerCutStr, $sep, $maxPos + 1);
				if ($pos !== false && $pos < $maxLength) {
					$find = true;
					$maxPos = $pos;
				}
			}
			if (!$find) {
				break;
			}
		}

		if ($maxPos !== false) {
			return mb_substr($cutStr, 0, $maxPos);
		}
		return $str;
	}

	/**
	 * Заменяет известные нам символы не русского и не английского алфавита на приемлимые аналоги
	 *
	 * @param string $word
	 * @return string
	 */
	public static function fixWrongSymbols($word) {
		$replace = array(
			'ä' => 'a',
			'é' => 'e',
		);
		$word = str_replace(array_keys($replace), $replace, $word);
		return $word;
	}

	public static function floatFormat($value) {
		$res = $value;
		$res = str_replace(',', '.', $res);
		$res = preg_replace('/[^\d\.\-]/isU', '', $res);
		$res = bcmul($res, 100);
		$res = bcdiv($res, 100, 2);
		$res = number_format($res, 2, '.', '');
		return $res;
	}

	/**
	 * Модифицирует каждое слово в строке по алгоритму: если слово длиннее 4 символов и,
	 * если там нет цифр и букв в нижнем регистре, то первая буква становиться заглавной,
	 * остальные в нижний регистр
	 *
	 * @param string $str
	 * @return string
	 */
	public static function fixCapsLock($str) {
		$words = explode(' ', $str);
		for ($i = 0; $i < count($words); $i++) {
			if (mb_strlen($words[$i]) > 4 && !preg_match("/[0-9a-zа-яеё]/su", $words[$i])) {
				$words[$i] = self::mbUcFirst(mb_strtolower($words[$i]));
			}
		}
		return implode(' ', $words);
	}

	/**
	 * Делает массив строк путем подстановки по очереди к исходной строке строк из массива
	 *
	 * @param string $sourceStr
	 * @param string[] $strArray
	 * @return array
	 */
	public static function combineStringAndStringsFromArray($sourceStr, array $strArray) {
		$combineStrArray = array();
		foreach ($strArray as $str) {
			$combineStrArray[] = $sourceStr.' '.$str;
		}
		return $combineStrArray;
	}

	/**
	 * Конкатинирует
	 *
	 * @param array $sourceStr
	 * @param array $strArray
	 * @param string $concatSymbols
	 * @return string[]
	 */
	public static function combineStringsArrays(array $sourceStr, array $strArray, $concatSymbols = ' ') {
		$combineStrArray = array();
		foreach ($sourceStr as $str) {
			foreach ($strArray as $str2) {
				$combineStrArray[] = $str.$concatSymbols.$str2;
			}
		}
		return $combineStrArray;
	}

	/**
	 * Составление фраз путем перебора вариантов, начиная со второго слова
	 * @param string $str
	 * @return array
	 */
	public static function iterateOverOptionsWithSecondWord($str) {
		$newCombineStringsArray = array();
		$explodedStr = explode(' ', $str);
		$firstWord = array_shift($explodedStr);

		$explodedStrSize = sizeof($explodedStr);

		for ($pos = 0; $pos < $explodedStrSize; $pos++) {
			for ($offset = $pos + 1; $offset < $explodedStrSize; $offset++) {
				for ($secondOffset = 0; $secondOffset < $offset + 1; $secondOffset++) {
					// Если не залезаем за границу слова, пускаем
					if ($pos + $secondOffset <= $offset) {
						$wordsBefore = implode(' ', array_slice($explodedStr, $pos, $secondOffset));
						$key = $wordsBefore === '' ? $explodedStr[$offset] : $wordsBefore.' '.$explodedStr[$offset];
						$newCombineStringsArray[$key] = true;
					}
					$a = Arrays::sliceCircle($explodedStr, $pos, -$secondOffset);
					$key = implode(' ', $a);
					if (trim($key) !== '') {
						$newCombineStringsArray[$key] = true;
					}
				}
			}
		}
		$newCombineStringsArray = array_keys($newCombineStringsArray);
		$newCombineStringsArray = self::combineStringAndStringsFromArray($firstWord, $newCombineStringsArray);
		return $newCombineStringsArray;
	}

	/**
	 * Очищает строку после первой скобки из набора "()[]"
	 * @param string $str
	 * @return mixed
	 */
	public static function cleanUpAfterBrackets($str) {
		return preg_replace("([\(|\[|\)|\]].*)", '', $str);
	}

	/**
	 * Заменяет искомые постфиксы слов в строке на сроку-заменитель. Поиск регистронезависимый
	 * replacePostfixInWords('art money 128GB 512гб', array('gb', 'гб'), 'Gb') = "art 128 Gb 512 Gb"
	 *
	 * @param $sourceStr
	 * @param array $haystackArray
	 * @param string $replaceStr
	 * @internal param string $str
	 * @return string
	 */
	public static function replacePostfixInWords($sourceStr, array $haystackArray, $replaceStr) {
		$haystack = implode('|', $haystackArray);
		$explodedStr = explode(' ', $sourceStr);
		foreach ($explodedStr as $k => $word) {
			if (mb_strlen($word) > 2) {
				$tmpWord = mb_substr($word, -2, 2);
				$replacedTmpWord = preg_replace('/('.$haystack.')/isu', $replaceStr, $tmpWord);
				// Если было заменено, то расцениваем, как 2 новых слова и разделяем из пробелом
				if ($replacedTmpWord !== $tmpWord) {
					$explodedStr[$k] = mb_substr($word, 0, -2).' '.$replacedTmpWord;
				}
			} else {
				$explodedStr[$k] = preg_replace('/('.$haystack.')/isu', $replaceStr, $word);
			}
		}
		return implode(' ', $explodedStr);
	}

	/**
	 * Ищет искомое слово в строке и удаляет его и все слова до него
	 * ('art money 345 book ok 123', 'book', 2) = 'art ok 123'
	 *
	 * @param string $sourceStr
	 * @param string $findWord
	 * @param int $countWordsBeforeNeedleForRemove - количество слов для удаления, расположенных до искомого слова
	 * @return string
	 */
	public static function findWordAndRemoveHimAndWordsBeforeHimIfNeed(
		$sourceStr, $findWord, $countWordsBeforeNeedleForRemove
	) {
		$explodedStr = explode(' ', $sourceStr);
		foreach ($explodedStr as $k => $word) {
			if ($word == $findWord) {
				unset($explodedStr[$k]);
				for ($i = 0; $i <= $countWordsBeforeNeedleForRemove; $i++) {
					if (isset($explodedStr[$k - $i])) {
						unset($explodedStr[$k - $i]);
					}
				}
				break;
			}
		}
		return implode(' ', $explodedStr);
	}

	public static function findWordAndRemoveHimAndWordWithoutLetters($sourceStr, $findWord) {
		$explodedStr = explode(' ', $sourceStr);
		foreach ($explodedStr as $k => $word) {
			if ($word == $findWord) {
				if (isset($explodedStr[$k - 1]) && !preg_match('#[a-zа-яё]#isu', $explodedStr[$k - 1])) {
					echo '#';
					unset($explodedStr[$k]);
					unset($explodedStr[$k - 1]);
				}
			}
		}
		return trim(implode(' ', $explodedStr));
	}

	public static function removeWordsAfterSearchWordWithMinSearchWordPos($str, $searchWord, $minPosSearchWord = 2) {
		$searchWord = mb_strtolower($searchWord);
		if ($minPosSearchWord > 1) {
			$explodedStr = explode(' ', $str);
			$slice = array_slice($explodedStr, $minPosSearchWord - 1, null, true);
			foreach ($slice as $k => $word) {
				if (mb_strtolower($word) == $searchWord) {
					return implode(' ', array_slice($explodedStr, 0, $k));
				}
			}
		}
		return $str;
	}

	/**
	 * Удаляет все в строке, со слова, содержащего русские символы включительно
	 *
	 * @param $str
	 * @param $offset - начиная с какого слова по порядку будет производиться поиск
	 * @return string
	 */
	public static function removeEverythingFromTheWordWithRussianChar($str, $offset = 0) {
		$explodedStr = explode(' ', $str);
		$delete = false;
		foreach ($explodedStr as $k => $word) {
			if ($k < $offset) {
				if (preg_match('/([а-яё])/isu', $word)) {
					return $str;
				}
				continue;
			}
			if (!$delete) {
				if (preg_match('/([а-яё])/isu', $word)) {
					$delete = true;
				}
			}
			if ($delete) {
				unset($explodedStr[$k]);
			}
		}
		return implode(' ', $explodedStr);
	}

	/**
	 * Заменяет не-цифры и не буквы русского и английского алфавита на пробелы
	 *
	 * @param string $str
	 * @return string
	 */
	public static function replaceBadCharWithSpaces($str) {
		return preg_replace('/([^a-zа-яё0-9])/isu', ' ', $str);
	}

	/**
	 * Заменяет все не цифры и символы не английского алфавита на пробелы
	 *
	 * @param string $str
	 * @return string
	 */
	public static function replaceNotEnglishAndFiguresCharWithSpaces($str) {
		return preg_replace('/([^a-z0-9])/isu', ' ', $str);
	}

	/**
	 * Вырезает из строки подстроку, не обращая внимание на символы-разделители слов,
	 * т.е. ими могут быть не только пробелы, но и символы неанглийского алфавита и не цифры
	 *
	 * @param string $sourceStr
	 * @param string $cutWord
	 * @return string
	 */
	public static function cutWordProvidedAndOfBadCharAsSpace($sourceStr, $cutWord) {
		$replacedCutWord = self::replaceNotEnglishAndFiguresCharWithSpaces($cutWord);
		$cutWordWithReplacedBadCharOnSpace = preg_replace('/('.$replacedCutWord.')/isu', '', $sourceStr);
		if ($cutWordWithReplacedBadCharOnSpace === $sourceStr) {
			return preg_replace('/('.str_replace(' ', '', $cutWord).')/isu', '', $sourceStr);
		} else {
			return $cutWordWithReplacedBadCharOnSpace;
		}
	}

	/**
	 * Получает из строки определенное количество слов, начиная с первого
	 *
	 * @param string $sourceStr
	 * @param int $countWords
	 * @return string
	 */
	public static function cutWordsByCount($sourceStr, $countWords) {
		if ($countWords > 0) {
			$explodedStr = explode(' ', $sourceStr);
			$cutedStr = array_slice($explodedStr, 0, $countWords);
			return implode(' ', $cutedStr);
		}
		return $sourceStr;
	}

	/**
	 * Удаляет индекс модели товара из строки, если строка похожа на модель товара
	 *
	 * @param string $str
	 * @return string
	 */
	public static function cutOfferModelIndex($str) {
		$explodedStr = explode(' ', $str);
		if (sizeof($explodedStr) > 2) {
			$delete = false;
			foreach ($explodedStr as $k => $word) {
				if ($delete) {
					unset($explodedStr[$k]);
					continue;
				}
				if (mb_strlen($word) > 3 && (preg_match("/[0-9]/su", $word) && preg_match("/[a-z]/isu", $word))) {
					$delete = true;
				}
			}
			return implode(' ', $explodedStr);
		}
		return $str;
	}

	/**
	 * Удаляет слова, длиннее 4 символов, состоящие только из цифр
	 *
	 * @param string $str
	 * @param int $limit
	 * @return string
	 */
	public static function removeLongFiguresWord($str, $limit = 4) {
		$explodedStr = explode(' ', $str);
		if (sizeof($explodedStr) > 1) {
			foreach ($explodedStr as $k => $word) {
				if (mb_strlen($word) > $limit && !preg_match("/[^0-9]/isu", $word)) {
					unset($explodedStr[$k]);
				}
			}
			return implode(' ', $explodedStr);
		}
		return $str;
	}

	/**
	 * Удаляет из строки слова из массива
	 *
	 * @param string $str
	 * @param string[] $wordsForRemove
	 * @return string
	 */
	public static function removeWordsFromArray($str, array $wordsForRemove) {
		$explodedStr = explode(' ', $str);
		foreach ($explodedStr as $k => $word) {
			if (in_array(mb_strtolower($word), $wordsForRemove)) {
				unset($explodedStr[$k]);
			}
		}
		return implode(' ', $explodedStr);
	}

	/**
	 * Разбивает строку по множеству разделителей сразу
	 *
	 * @param array $delimiters
	 * @param string $string
	 * @return string[]
	 */
	public static function multiExplode(array $delimiters, $string) {
		$data = array($string);
		$delimitersCount = 0;
		while (isset($delimiters[$delimitersCount])) {
			$newData = array();
			foreach ($data as $item) {
				$parts = explode($delimiters[$delimitersCount], $item);
				foreach ($parts as $part) {
					$newData[] = $part;
				}
			}
			$data = $newData;
			$delimitersCount++;
		}
		return $data;
	}

	/**
	 * Прямой пербор всех вариаций слов слева направо
	 * "art money book" = ["art money book", "art money", "money book", "art", "money", "book"]
	 *
	 * @param string $str
	 * @return string[]
	 */
	public static function getAllWordsDirectVariants($str) {
		$words = array();
		$arr = explode(' ', $str);
		$swordsCount = sizeof($arr);
		for ($length = $swordsCount; $length > 0; $length--) {
			for ($offset = 0; $offset <= ($swordsCount - $length); $offset++) {
				$word = array_slice($arr, $offset, $length);
				$words[] = implode(' ', $word);
			}
		}
		return $words;
	}

	/**
	 * Удаляет только цифровые слова, если они идут группой подряд
	 * cutOnlyNumbersByLimit('ШВУ 0 4 1 3', 3) = 'ШВУ 0 4 1'
	 *
	 * @param string $str
	 * @param int $limit
	 * @return string
	 */
	public static function cutOnlyNumbersWordsByLimit($str, $limit) {
		$limit = (int)$limit;
		if ($limit <= 0) {
			return $str;
		}

		$e = explode(' ', $str);
		$find = 0;
		foreach ($e as $k => $i) {
			if (preg_match('@[^0-9]@su', $i)) {
				$find = 0;
			} else {
				$find++;
			}

			if ($find > $limit) {
				unset($e[$k]);
			}
		}
		return implode(' ', $e);
	}

	/**
	 * Подсчитывает цифровые слова и количество символов в них и удаляет всё, после удовлетворения заданных лимитов
	 *
	 * Dell 210 32068 005 = Dell 210 32068
	 * ШВУ 0 4 1 3 = ШВУ 0 4 1 3
	 * ШВУ 0 4 1 3 d 6 = ШВУ 0 4 1 3
	 * ШВУ 0 4 1 3 7 d = ШВУ 0 4 1 3
	 * ШВУ 0 4 d 7 7 7 d = ШВУ 0 4 d 7 7 7 d
	 * ШВУ 0 4 1 d 777 8 764 7 = ШВУ 0 4 1 d 777 8
	 * ШВУ 04133 333 d 77 5 7 8 764 7 = ШВУ 04133 333
	 *
	 * @param string $str
	 * @param int $maxNumberCount - максимальное количество символов, после которого наступает удаление
	 * @param int $minWordsCount - минимальное количество слов, для установки флага на удаление
	 * @return string
	 */
	public static function removeNumberWordsAndAfterByNumbersAndWordsLimit(
		$str, $maxNumberCount = 4, $minWordsCount = 2
	) {
		$numberCharCount = 0;
		$numberWordsCount = 0;
		$remove = false;
		$e = explode(' ', $str);
		foreach ($e as $k => $word) {
			if ($numberWordsCount >= $minWordsCount && $numberCharCount >= $maxNumberCount) {
				$remove = true;
			}

			if ($remove) {
				unset($e[$k]);
			}

			if (!preg_match('@[^0-9]@su', $word)) {
				$numberCharCount += mb_strlen($word);
				$numberWordsCount++;
			} else {
				if (!$remove) {
					$numberWordsCount = 0;
					$numberCharCount = 0;
				}
			}
		}
		return implode(' ', $e);
	}

	/**
	 * Обрезает слова, состоящие только из русских символов и все после них после общего лимита
	 * cutRussianWordsAfterLimit('sdfsd А П Р О Л Д Ж asdfasd', 3) = 'sdfsd А П'
	 *
	 * @param string $str
	 * @param int $limit - общее количество слов, после которого начнется проверка на русские символы
	 * @return string
	 */
	public static function cutRussianWordsAfterGeneralLimit($str, $limit) {
		$limit = (int)$limit;
		if ($limit <= 0) {
			return $str;
		}
		$e = explode(' ', $str);
		$find = 0;
		$break = false;
		foreach ($e as $k => $i) {
			$find++;
			if ($find >= $limit) {
				if (preg_match('@[^а-яё]@isu', $i) == false) {
					$break = true;
				}

				if ($break) {
					unset($e[$k]);
				}
			}
		}
		return implode(' ', $e);
	}

	/**
	 * Получает содержимое строки, находящееся в кавычках " или ', если таковые есть
	 * или от первой кавычки до конца строки, если закрывающей кавычки такогоже вида нет
	 * Также отчищает от кавычек и обрезает пробелы с обоих сторон
	 *
	 * @param string $str
	 * @return string
	 */
	public static function getQuotesContent($str) {
		$pos1 = mb_strpos($str, '"');
		$pos2 = mb_strpos($str, '\'');

		if ($pos1 !== false && $pos2 === false) {
			$pos = $pos1;
		} else if ($pos1 === false && $pos2 !== false) {
			$pos = $pos2;
		} else if ($pos1 !== false && $pos2 !== false) {
			$pos = $pos1 < $pos2 ? $pos1 : $pos2;
		} else {
			$pos = null;
		}

		if ($pos !== null) {
			$last = mb_strrpos($str, $pos == $pos ? '"' : '\'');
			if ($last === $pos) {
				$last = false;
			}
			$ret = mb_substr($str, $pos, $last - $pos);
			return trim(str_replace(array('"', '\''), '', $ret));
		}
		return '';
	}

	/**
	 * Удаляет слова, содержажие искомую подстроку
	 *
	 * @param string $str
	 * @param string $searchStr
	 * @return string
	 */
	public static function removeWordsContainsSubstr($str, $searchStr) {
		$ret = array();
		foreach (explode(' ', $str) as $word) {
			if (mb_strpos($word, $searchStr) === false) {
				$ret[] = $word;
			}
		}
		return implode(' ', $ret);
	}

	/**
	 * Разбирает строку сначала по симпольным разделителям, а потом по слова-разделителям
	 * @param string $str
	 * @param array $symbolsSeparators
	 * @param array $wordSeparators
	 * @return string[]
	 */
	public static function explodeBySepatarors($str, array $symbolsSeparators, array $wordSeparators = array()) {
		$ret = array();
		foreach (self::multiExplode($symbolsSeparators, $str) as $substr) {
			$substr = trim($substr);
			$explodedStr = self::explodeByWordSepatarors($substr, $wordSeparators);
			$ret = array_merge($ret, $explodedStr);
		}
		return $ret;
	}

	/**
	 * Разделяет строку по словам по разделяющему слову
	 * explodeByWordSepataror('Кофеварки и кофемашины', 'и') = ['Кофеварки', 'кофемашины']
	 * Алгоритм уже оптимизирован.
	 *
	 * @param string $str
	 * @param array $wordSeparators
	 * @internal param string $wordSeparator
	 * @return array
	 */
	public static function explodeByWordSepatarors($str, array $wordSeparators) {
		$ret = array();
		$s = '';
		foreach (explode(' ', $str) as $word) {
			if ($word === '') {
				continue;
			}
			if (in_array($word, $wordSeparators)) {
				$s = trim($s);
				if ($s !== '') {
					$ret[] = $s;
					$s = '';
				}
				continue;
			}
			$s .= $word.' ';
		}
		$s = trim($s);
		if ($s !== '') {
			$ret[] = $s;
		}
		return $ret;
	}

	/**
	 * Удаляем элемент массива, если он превосходит лимит максимального кол-ва слов
	 *
	 * @param array $source
	 * @param int $maxWordsCount
	 * @return string[]
	 */
	public static function removeItemWithWordCount(array $source, $maxWordsCount) {
		foreach ($source as $k => $str) {
			if (self::getWordsCount($str) > $maxWordsCount) {
				unset($source[$k]);
			}
		}
		return $source;
	}

	/**
	 * Удаляем одинарные и двойные кавычки, и символ ' если они находятся внутри слова
	 *
	 * @param array $source
	 * @return string
	 */
	public static function trimSpecialChars($source) {
		$source = self::trimTwoSpaces($source);
		$sourcePieces = explode(" ", $source);
		$source = '';
		foreach ($sourcePieces as $sourcePiece) {
			foreach (array('"', '\'', '`') as $symbol) {
				$sourcePiece = self::trimTwoSymbols($sourcePiece, $symbol);
			}
			$source .= trim($sourcePiece, "\x22\x27")." ";
		}
		return trim($source);
	}

	/**
	 * Получает строку, которую можно использовать для уникальности строк
	 * Убирает "плохие" символы, убирает пробелы и приводит в нижний регистр
	 *
	 * @param string $str
	 * @return string
	 */
	public static function makeUniqueKey($str) {
		$str = self::replaceBadCharWithSpaces($str);
		$str = str_replace(' ', '', $str);
		$str = mb_strtolower($str);
		return $str;
	}

	/**
	 * Проверяет похожи ли строки без учета регистров и многочисленных пробелов
	 *
	 * @param string $str1
	 * @param string $str2
	 * @return boolean
	 */
	public static function isEqualStrings($str1, $str2) {
		$str1 = self::makeUniqueKey($str1);
		$str2 = self::makeUniqueKey($str2);
		return $str1 == $str2;
	}

	/**
	 * Проверяет похожа ли строка на одну из строк из массива
	 *
	 * @param string $str1
	 * @param string[] $strArray
	 * @return bool
	 */
	public static function isEqualStringAndStringsArray($str1, array $strArray) {
		foreach ($strArray as $str) {
			if (self::isEqualStrings($str1, $str)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Удаляет единицы измерения (сантиметры и литры) и все после них из строки
	 *
	 * @param string $str
	 * @return string
	 */
	public static function removeUnitsOfMeasure($str) {
		$findStr = implode('|', array('см', 'л', 'мл', 'м', 'cм'));
		$intStr = '([\d]+.[\d]+|[\d]+,[\d]+|[\d]+)';
		$space = '(\s+|\s?)';
		$pattern = '';
		$pattern .= '# ('.$intStr.''.$space.'((x|х)'.$space;
		$pattern .= $intStr.')?'.$space.'('.$findStr.'))([\s].*)?$#isu';

		$str = preg_replace(
			$pattern,
			'',
			$str
		);
		return $str;
	}

	public static function isAdTextContaintsDifferentAlphabetsWord($str) {
		$str = preg_replace('/([^a-zа-яё])/isu', ' ', $str);
		$str = self::trimTwoSpaces($str);
		$words = self::getUniqueWords($str);
		foreach ($words as $word) {
			if (self::isDifferentAlphabetsWord($word)) {
				return true;
			}
		}
		return false;
	}

	public static function isDifferentAlphabetsWord($str) {
		return (
			preg_match('#([a-z])([а-яё])#isu', $str) ||
				preg_match('#([а-яё])([a-z])#isu', $str)
		);
	}

	public static function isRussianWord($str) {
		return (
		preg_match('#([а-яё])#isu', $str)
		);
	}

	/**
	 * Превода текста с кириллицы в траскрипт
	 * @param $string
	 *
	 * @return string
	 */
	public static function getInTranslit($string) {
		$replace = array(
			"'" => "",
			"`" => "",
			"а" => "a", "А" => "a",
			"б" => "b", "Б" => "b",
			"в" => "v", "В" => "v",
			"г" => "g", "Г" => "g",
			"д" => "d", "Д" => "d",
			"е" => "e", "Е" => "e",
			"ж" => "zh", "Ж" => "zh",
			"з" => "z", "З" => "z",
			"и" => "i", "И" => "i",
			"й" => "y", "Й" => "y",
			"к" => "k", "К" => "k",
			"л" => "l", "Л" => "l",
			"м" => "m", "М" => "m",
			"н" => "n", "Н" => "n",
			"о" => "o", "О" => "o",
			"п" => "p", "П" => "p",
			"р" => "r", "Р" => "r",
			"с" => "s", "С" => "s",
			"т" => "t", "Т" => "t",
			"у" => "u", "У" => "u",
			"ф" => "f", "Ф" => "f",
			"х" => "h", "Х" => "h",
			"ц" => "c", "Ц" => "c",
			"ч" => "ch", "Ч" => "ch",
			"ш" => "sh", "Ш" => "sh",
			"щ" => "sch", "Щ" => "sch",
			"ъ" => "", "Ъ" => "",
			"ы" => "y", "Ы" => "y",
			"ь" => "", "Ь" => "",
			"э" => "e", "Э" => "e",
			"ю" => "yu", "Ю" => "yu",
			"я" => "ya", "Я" => "ya",
			"і" => "i", "І" => "i",
			"ї" => "yi", "Ї" => "yi",
			"є" => "e", "Є" => "e"
		);
		return $str = iconv("UTF-8", "UTF-8//IGNORE", strtr($string, $replace));
	}

	/**
	 * Удаляет название цветов из строки
	 *
	 * @param $str
	 * @return string
	 */
	public static function removeColorWords($str) {
		return trim(self::removeWordsFromArray($str, self::$arrayWordColor));
	}

	/**
	 * Получить кол-во символов без учета пробелов
	 *
	 * @param $str
	 * @return int
	 */
	public static function getCountSymbolsWithoutSpaces($str) {
		return mb_strlen(trim(str_replace(' ', '', $str)));
	}

	/**
	 * Состоит только из цифр
	 *
	 * @param $str
	 * @return bool
	 */
	public static function isOnlyNumeric($str) {
		return !preg_match("/[^0-9]/isu", $str);
	}

	/**
	 * Состоит только из букв
	 *
	 * @param $str
	 * @return bool
	 */
	public static function isOnlyLetters($str) {
		return !preg_match("/[^a-zа-яё]/isu", $str);
	}

	/**
	 * Строка содержит стово
	 *
	 * @param $str
	 * @param $word
	 * @return bool
	 */
	public static function isContainsWord($str, $word) {
		return in_array(trim($word), self::getUniqueWords($str));
	}

	public static function getModelPieces($modelRaw) {
		$modelForVariants = self::replaceBadCharWithSpaces($modelRaw);
		$modelForVariants = self::trimTwoSpaces($modelForVariants);

		$rawModelVariants = array();
		$rawModelVariantsForVendor = array();

		foreach (self::getAllWordsDirectVariants($modelForVariants) as $rawModelVariant) {
			$rawModelVariant = trim($rawModelVariant);

			$rawModelVariantWithoutSpaces = str_replace(' ', '', $rawModelVariant);
			if (
				self::isOnlyNumeric($rawModelVariantWithoutSpaces) ||
				self::isOnlyLetters($rawModelVariantWithoutSpaces)
			) {
				continue;
			}

			$countIfSymbols = self::getCountSymbolsWithoutSpaces($rawModelVariant);
			if ($countIfSymbols >= 3) {
				$rawModelVariantsForVendor[] = $rawModelVariant;
			}

			if ($countIfSymbols >= 5) {
				$rawModelVariants[] = $rawModelVariant;
			}
		}

		return array(
			'forVendor' => $rawModelVariantsForVendor,
			'basic' => $rawModelVariants
		);
	}

	/**
	 * Подставляет окончание слова (1 кампания, 2 кампании, 5 кампаний)
	 *
	 * @param $number
	 * @param array $titles
	 * @return mixed
	 */
	public static function numberEnd($number, array $titles) {
		$cases = array(2, 0, 1, 1, 1, 2);
		return $titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
	}

	/**
	 * Удаляет неразрещенные символы из строк
	 *
	 * http://head.lan:8090/browse/GARPUN-1551
	 *
	 * @param $str
	 * @return string
	 */
	public static function replaceBadText($str) {
		$str = preg_replace('/([^a-zа-яё0-9-+,._#&*\\/"!\'\?%;:$\(\)\[\]\\№=\\\\])/isu', ' ', $str);
		$str = self::trimTwoSpaces($str);
		return $str;
	}


	public static function generateRandom(
		$length,
		$discardDigits = false,
		$discardCharacters = false
	) {
		if (!is_numeric($length)) {
			throw new \InvalidArgumentException('Длина строки д.б. числом');
		} elseif ($discardDigits && $discardCharacters) {
			throw new \InvalidArgumentException('Одновременно убрать буквы и цифры нельзя');
		}

		$length = intval($length);

		$charClasses = array(
			range(97, 122), // a to z
			range(65, 90), // A to Z
			range(48, 57), // 0 to 9
		);
		if ($discardDigits) {
			unset($charClasses[2]);
		} elseif ($discardCharacters) {
			$charClasses = array_slice($charClasses, 2, 1);
		}

		$returnString = '';
		for ($count = 0; $count < $length; $count++) {
			$charClass = $charClasses[mt_rand(0, count($charClasses) - 1)];
			$char = chr($charClass[array_rand($charClass)]);
			$returnString .= $char;
		}
		// Convert ASCII chars to HTML valid characters
		return htmlentities($returnString);
	}

	public static function removeBOM($str = "") {
		if (substr($str, 0, 3) == pack('CCC', 0xef, 0xbb, 0xbf)) {
			$str = substr($str, 3);
		}
		return $str;
	}
}
