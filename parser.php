<?php
require("vendor/autoload.php");


if(empty($argv[1]))
{
	echo "Usage php parser.php {packagename}  eg:php parser.php propertyguru/core";
	echo "\n";
}
else{
	
	$rootPath = realpath('.');
	$dirObj = dir($rootPath);
	//$dirObj = dir(__DIR__.'/../');
	echo "\n----------------------------------\n";
	echo "Packagename: ".$argv[1]."\n";
	echo "Path: " . $dirObj->path;
	echo "\n----------------------------------\n";

	$folders = [];

	while (false !== ($dir = $dirObj->read())) {
	  $folders[] = $dir;
	}
	$dirObj->close();

	$hashes = [];

	foreach ($folders as $app) {
		$lockFile = $rootPath.'/.'.$app.'/composer.lock';

		if (!is_file($lockFile)) {
			continue;
		}

		$composerInfo = new ComposerLockParser\ComposerInfo($lockFile, $argv[1]);

		$composerInfo->getHash();

		$packages = $composerInfo->getPackages();

		foreach ($packages as $package) {
			if($package->getName()==$argv[1]) {
				$hash = trim($package->getSource());

				if (empty($hashes[$hash])) {
					$hashes[$hash] = [
						'hash' => $hash,
						'date' => trim($package->getTime()->format('Y-m-d H:i:s')),
						'timestamp' => trim($package->getTime()->getTimestamp()),
						'version' => trim($package->getVersion()),
						'repos' => [],
					];
				}

				$hashes[$hash]['repos'][] = $app;
			}
		}	
	}

	usort($hashes, function($a, $b){
		if ($a['timestamp'] == $b['timestamp']) {
			return 0;
		} else {
			return $a['timestamp'] > $b['timestamp'] ? -1 : 1;
		}
	});

	foreach($hashes as $hash) {
	    echo $hash['version'] . " @ " . $hash['date'] . ' - ' . $hash['hash'];
	    echo "\n - ". implode("\n - ", $hash['repos']);	
	    echo "\n";
	    echo "\n";
	}
}

?>
