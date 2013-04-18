<?php
namespace Harpoon\Common\Validator\Xml;

use DOMDocument;

class Xsd {

	const OUTPUT_SEPARATOR = "\n\t";

	/**
	 * Функция валидации
	 *
	 * @param string $xmlSourceContent   Код XML файла, подлежащего валидации
	 * @param string $xsdSchemeFileName  Полный путь и имя к XSD схеме
	 *
	 * @throws Exception
	 */
	public static function validate($xmlSourceContent, $xsdSchemeFileName) {
		libxml_use_internal_errors(true);

		$xml = new DOMDocument();
		$xml->loadXML($xmlSourceContent);

		if (!$xml->schemaValidate($xsdSchemeFileName)) {
			$outErrors = array();

			foreach (libxml_get_errors() as $error) {
				$outErrors[] = trim($error->message);
			}

			libxml_clear_errors();

			throw new Exception(self::OUTPUT_SEPARATOR.implode(self::OUTPUT_SEPARATOR, $outErrors));
		}
	}
}
