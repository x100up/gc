<?php
namespace Harpoon;

class ComposerInstaller {

	const LOCAL_FILE_NAME = 'composer.phar';

	public function run() {
		$this->downloadComposerIfNeed();
		$this->runInstallAndUpdate();
	}

	private function downloadComposerIfNeed() {
		$dir = __DIR__;
		if (!is_file($dir.'/'.self::LOCAL_FILE_NAME)) {
			$returnVar = 0;
			passthru("cd $dir; curl -s http://getcomposer.org/installer | php", $returnVar);
			if ($returnVar) {
				exit(1);
			}
		}
	}

	private function runInstallAndUpdate() {
		$dir = __DIR__;
		$returnVar = 0;
		passthru("cd $dir; php composer.phar install; php composer.phar update", $returnVar);
		if ($returnVar) {
			exit(1);
		}
	}
}

$composerInstaller = new ComposerInstaller();
$composerInstaller->run();
exit(0);
