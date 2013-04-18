<?php
/**
 * This sniff class detectes empty statement.
 *
 * Modified from squiz sniff to make empty while statement a warning, not error
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author	Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2011 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   Release: 1.3.4
 * @link	  http://pear.php.net/package/PHP_CodeSniffer
 */
class Harpoon_Sniffs_CodeAnalysis_EmptyStatementSniff extends Generic_Sniffs_CodeAnalysis_EmptyStatementSniff {

	/**
	 * List of block tokens that this sniff covers.
	 *
	 * The key of this hash identifies the required token while the boolean
	 * value says mark an error or mark a warning.
	 *
	 * @var array
	 */
	protected $checkedTokens = array(
		T_DO		=> true,
		T_ELSE		=> true,
		T_ELSEIF	=> true,
		T_FOR		=> true,
		T_FOREACH	=> true,
		T_IF		=> true,
		T_SWITCH	=> true,
		T_WHILE		=> true,
		T_TRY		=> true,
		T_CATCH		=> true,
	);

}
