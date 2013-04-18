<?php
namespace Harpoon\Common\Validator\Xml;

use DOMDocument;
use DOMImplementation;

class Dtd {

	const OUTPUT_SEPARATOR = "\n\t";

	/**
	 * Функция валидации
	 *
	 * @param string $xmlSourceContent  Код XML файла, подлежащего валидации
	 * @param string $dtdFileName       Полный путь до файла с DTD-определением
	 * @param string $rootNodeName      Имя корневого узла в документе
	 *
	 * @throws Exception
	 * @return void
	 */
	public static function validate($xmlSourceContent, $dtdFileName, $rootNodeName) {
		libxml_use_internal_errors(true);

		$oldXml = new DOMDocument();
		$oldXml->loadXML($xmlSourceContent);

		$oldNode = $oldXml->getElementsByTagName($rootNodeName)->item(0);
		if (empty($oldNode)) {
			throw new Exception('Имя корневого узла указано неверно');
		}

		$dtdContent = file_get_contents($dtdFileName);
		$systemId   = 'data://text/plain;base64,'.base64_encode($dtdContent);

		$creator = new DOMImplementation;
		$docType = $creator->createDocumentType($rootNodeName, null, $systemId);

		$newXml = $creator->createDocument(null, null, $docType);
		$newXml->encoding = 'UTF-8';

		$newNode = $newXml->importNode($oldNode, true);
		$newXml->appendChild($newNode);

		if (!$newXml->validate()) {
			$outErrors = array();

			foreach (libxml_get_errors() as $error) {
				$outErrors[] = trim($error->message);
			}

			$errorMessage = self::OUTPUT_SEPARATOR.implode(self::OUTPUT_SEPARATOR, $outErrors);
			libxml_clear_errors();

			throw new Exception($errorMessage);
		}
	}
}
