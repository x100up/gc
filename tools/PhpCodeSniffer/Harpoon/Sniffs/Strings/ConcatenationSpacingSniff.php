<?php
class Harpoon_Sniffs_Strings_ConcatenationSpacingSniff extends Squiz_Sniffs_Strings_ConcatenationSpacingSniff {

	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
		$tokens = $phpcsFile->getTokens();
		$error = function() use ($phpcsFile, $stackPtr) {
			$message = 'Concat operator must not be surrounded by spaces';
			$phpcsFile->addError($message, $stackPtr, 'Missing');
		};

		$before = $tokens[($stackPtr - 1)];
		$after = $tokens[($stackPtr + 1)];
		if ($before['code'] === T_WHITESPACE) {
			$error();
		} else if ($after['code'] === T_WHITESPACE && $after['content'] != "\n") {
			$error();
		}
	}
}
