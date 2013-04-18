<?php
/**
 * Script, that checks differences between git commits and runs
 * PHP Code Sniffer on changed files.
 *
 * Should be triggered by hook
 *
 * @author lex
 */

// including common function
include_once dirname(__FILE__).'/common.php';

// Defining path to tmp folder, where files will be copied
// Since script is intended to work on bare-git repository, there are no actual
// files contained in it, so all files should be requested via git command and
// saved to the filesystem
const CS_TMP_DIR = '/tmp/git-hook-run-phpcs';

// flag, that can be in the last commit before push indicating,
// that style check should be skipped
const SKIP_CHECK_FLAG = 'SKIP-CS';

// master head ref, always with the same name
const MASTER_REF = 'refs/heads/master';

/**
 * Main function of the script.
 *
 * @param string $baseRevision
 * @param string $commitRevision
 * @return integer - return code of sniffer-run
 */
function runPhpCs($baseRevision, $commitRevision) {
	// in case of empty base, we will check all files, that changed, since
	// the branch was created from master branch... hopefully this will work
	// in most of the cases
	if ($baseRevision == EMPTY_REVISION) {
		$baseRevision = getMergeBase(MASTER_REF, $commitRevision);
	}

	// getting log message for last commit to see if coding style
	// check skip was selected
	$logMessage = getLogMessage($commitRevision);
	if (strpos($logMessage, SKIP_CHECK_FLAG) !== false) {
		echo 'Skipping coding style check... If you are abusing this, than we will come and punish you'."\n\n";
		return 0;
	}

	// getting list of modified files
	$modifiedFiles = getModifiedFiles($baseRevision, $commitRevision);
	// on empty filelist there is nothing to check
	if (empty($modifiedFiles)) {
		return 0;
	}

	echo 'Running coding style check on modified files...'."\n";

	// saving modified files to filesystem
	extractFilesToDir($modifiedFiles, $commitRevision, CS_TMP_DIR);
	// running code sniffer in tmp directory, where
	// all modified files are stored
	$result = runCodeSniffer(CS_TMP_DIR);
	return $result;
}

/**
 * Get merge base for two commits
 *
 * @param string $firstRevision
 * @param string $secondRevision
 * @return string
 */
function getMergeBase($firstRevision, $secondRevision) {
	$args = array(
		'merge-base',
		$firstRevision,
		$secondRevision
	);
	return trim(git($args));
}

/**
 * Get commit message, added by user.
 *
 * @param $revision
 * @return string
 */
function getLogMessage($revision) {
	$args = array(
		'log', // run log command
		'-n 1', // show only one commit
		$revision, // show log, starting from revision
		'--format=format:%B' // show only body of the commit
	);
	return trim(git($args));
}

/**
 * Get list of modified files between revisions
 *
 * @param string $base
 * @param string $commit
 * @return array|string
 */
function getModifiedFiles($base, $commit) {
	$args = array(
		'diff', // run diff command
		'--diff-filter=ACMT', // show files, that was A(dded), C(copied),
		// M(odified) and have their T(ype) changed
		'--no-renames', // treat renamed files as new ones
		'--name-only', // show only names of the changed files
		$base.' '.$commit,
	);
	$output = trim(git($args));

	if (empty($output)) {
		return $output;
	} else {
		return explode("\n", $output);
	}
}

/**
 * Extract version of the files in provided revision
 * from git and save them to directory
 *
 * @param array $files
 * @param string $revision
 * @param string $directory
 */
function extractFilesToDir($files, $revision, $directory) {
	// cleaning up tmp directory
	removeDir(CS_TMP_DIR, true);

	foreach ($files as $file) {
		$targetFile = $directory.'/'.$file;
		$targetDir = dirname($targetFile);
		if (!is_dir($targetDir)) {
			mkdir($targetDir, 0777, true);
		}

		$fileContent = getRevisionOfTheFile($revision, $file);
		file_put_contents($targetFile, $fileContent);
	}
}

/**
 * Get revision of the file, stored in git
 *
 * @param $revision
 * @param string $file
 * @return string
 */
function getRevisionOfTheFile($revision, $file) {
	$args = array(
		'show', // run show command
		$revision.':"'.$file.'"', // show file contents from revision
	);

	return git($args);
}

/**
 * Run php code sniffer on directory
 *
 * @param string $dir
 * @return int result of the sniffing
 */
function runCodeSniffer($dir) {
	$returnValue = null;
	$output = null;

	$cmd = 'phpcs --extensions=php';
	$cmd .= ' --standard='.__DIR__.'/Harpoon ';
	$cmd .= $dir;
	$cmd .= ' --tab-width=4';
	$cmd .= ' --encoding=utf-8';

	exec($cmd, $output, $returnValue);
	$outputContents = implode("\n", $output);

	// hack for warning in the output
	// if there are only warnings, we don't want to fail the push
	if ($returnValue
		&& strpos($outputContents, ' | WARNING ') !== false
		&& strpos($outputContents, ' | ERROR ') === false
	) {
		$returnValue = 0;
	}

	// we can always output results of style check
	echo $outputContents."\n";

	return $returnValue;
}
