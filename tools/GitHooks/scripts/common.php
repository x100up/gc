<?php
/**
 * Common functions, used all php hooks scripts
 *
 * @author lex
 */

// when new branch is pushed to repository or deleted, it doesn't have revision,
// and this empty hash is counted as no revision.
const EMPTY_REVISION = '0000000000000000000000000000000000000000';

/**
 * Remove directory recursively
 *
 * @param string $dir
 * @param boolean $cleanUp - whether to remove parent directory or leave it
 */
function removeDir($dir, $cleanUp = false) {
	if (!is_dir($dir)) {
		return;
	}

	$contents = scandir($dir);
	foreach ($contents as $file) {
		if ($file == '.' || $file == '..') {
			continue;
		}
		$fullPath = $dir.'/'.$file;
		if (is_dir($fullPath)) {
			removeDir($fullPath);
		} else {
			unlink($fullPath);
		}
	}

	if (!$cleanUp) {
		rmdir($dir);
	}
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
