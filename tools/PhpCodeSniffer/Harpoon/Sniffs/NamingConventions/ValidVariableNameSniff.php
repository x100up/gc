<?php
/**
 * Запрещает именовать не как camelCase
 * Кроме ключей массивов
 */
class Harpoon_Sniffs_NamingConventions_ValidVariableNameSniff extends Squiz_Sniffs_NamingConventions_ValidVariableNameSniff
{
	/**
	 * Processes class member variables.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param int				  $stackPtr  The position of the current token in the
	 *										stack passed in $tokens.
	 *
	 * @return void
	 */
	protected function processMemberVar(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
	{
		$tokens = $phpcsFile->getTokens();

		$varName	 = ltrim($tokens[$stackPtr]['content'], '$');
		$memberProps = $phpcsFile->getMemberProperties($stackPtr);
		if (substr($varName, 0, 1) === '_') {
			$scope = ucfirst($memberProps['scope']);
			$error = "$scope member variable \"$varName\" must not contain a leading underscore";
			$phpcsFile->addError($error, $stackPtr);
			return;
		}

		if (PHP_CodeSniffer::isCamelCaps($varName, false, true, false) === false) {
			$error = "Variable \"$varName\" is not in valid camel caps format";
			$phpcsFile->addError($error, $stackPtr);
		}

	}//end processMemberVar()

}