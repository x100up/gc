<?php
/**
 * Script, that installs git hooks in current directory.
 *
 * Should be run from the .git/hook directory.
 * For usage, see printHelp() function.
 *
 * @author lex, arturgspb
 */

$hookName = getHookName($argv);
if (empty($hookName)) {
	exit(1);
}

define('GIT_HOOKS', 'tools/GitHooks');
define('SCRIPTS', GIT_HOOKS.'/scripts');
define('CODE_STANDART_FILES', 'tools/PhpCodeSniffer/Harpoon');

define('OUT_CODE_STANDART_PATH', './scripts/Harpoon');

$files = array(
	// needed hook
	GIT_HOOKS.'/'.$hookName => './'.$hookName,

	// hook additional scripts
	SCRIPTS.'/common.php' => './scripts/common.php',
	SCRIPTS.'/run-phpcs.php' => './scripts/run-phpcs.php',

	// install script
	GIT_HOOKS.'/install.php' => './install.php',
);

$codeStandartFiles = array(
	'/ruleset.xml',
	'/Sniffs/CodeAnalysis/EmptyStatementSniff.php',
	'/Sniffs/Classes/ClassDeclarationSniff.php',
	'/Sniffs/NamingConventions/ValidFunctionNameSniff.php',
	'/Sniffs/NamingConventions/ValidVariableNameSniff.php',
);

foreach ($codeStandartFiles as $file) {
	$files[CODE_STANDART_FILES.$file] = OUT_CODE_STANDART_PATH.$file;
}

extractFilesFromHead($files);

makeFileExecutable($hookName);

exit(0);

/**
 * Print usage
 *
 */
function printHelp() {
	echo 'Help will be here'."\n";
}

/**
 * Get hook name from command line arguments
 *
 * @param string $argv
 * @return string
 */
function getHookName($argv) {
	if (count($argv) != 2) {
		printHelp();
		return;
	}

	$hookName = $argv[1];
	if (!in_array($hookName, array('pre-receive', 'pre-commit'))) {
		printHelp();
		return;
	}

	return $hookName;
}

/**
 * Extract latest version of the files from git to local dir
 *
 * @param array $files - key-value array, where key is filename in repository
 *                       and value is name for a file to be saved as
 */
function extractFilesFromHead($files) {
	foreach ($files as $gitFilename => $saveFilename) {
		if (file_exists($saveFilename)) {
			unlink($saveFilename);
		}

		$saveDir = dirname($saveFilename);
		if (!is_dir($saveDir)) {
			mkdir($saveDir, 0777, true);
		}

		$handle = fopen($saveFilename, 'w');
		fwrite($handle, getFileContents($gitFilename));
		fclose($handle);
	}
}

/**
 * Get file contents from git
 *
 * @param string $file
 */
function getFileContents($file) {
	$args = array(
		'show', // run show command
		'HEAD'.':'.$file, // show file contents from revision
	);

	return git($args);
}

/**
 * Run git command with provided arguments
 *
 * @param array $args
 * @return string - output of the command
 */
function git($args) {
	$params = implode(' ', $args);
	$output = `git $params`;
	return $output;
}

/**
 * Make file executable
 *
 * @param string $filename
 */
function makeFileExecutable($filename) {
	passthru('chmod +x '.$filename);
}
