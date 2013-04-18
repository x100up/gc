<?php
namespace Harpoon\Common\Lang;

class SystemUtils {

	/**
	 * @param array  $data
	 * @param string $recordSeparator
	 *
	 * @return array
	 */
	public static function parseSimpleCsv($data, $recordSeparator = ',') {
		$explodedRows = explode("\n", $data);
		$header = current(array_splice($explodedRows, 0, 1));

		$parsedArray = array();
		$headersArray = array();
		foreach (explode($recordSeparator, $header) as $record) {
			$record = trim($record, '"');
			$headersArray[] = trim($record);
		}

		foreach ($explodedRows as $row) {
			$row = trim($row);
			if ($row !== '') {
				$info = array();
				foreach (explode($recordSeparator, $row) as $pos => $record) {
					$record = trim($record, '"');
					$info[$headersArray[$pos]] = trim($record);
				}
				$parsedArray[] = $info;
			}
		}

		return $parsedArray;
	}

	public static function globRecursive($pattern, $flags = 0) {
		$files = glob($pattern, $flags);
		foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
			$files = array_merge($files, self::globRecursive($dir.'/'.basename($pattern), $flags));
		}
		return $files;
	}


	/**
	 * @throws \Exception
	 * @return int
	 */
	public static function getPid() {
		$pid = getmypid();

		if ($pid === false) {
			throw new \Exception("Ошибка при получении PID'а процесса");
		}

		return $pid;
	}
}
