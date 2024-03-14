<?php

if (!file_exists('../output')) {
    mkdir('../output');
}

if (!file_exists('../output/result.csv')) {
    unlink('../output/result.csv');
}


$mainDir = str_replace('\\','/', __DIR__);

$config = json_decode(file_get_contents('../config.json'), true);

if(isset($_SERVER['argv'][1]) && !empty($_SERVER['argv'][1])) {
	$projs = explode(',', $_SERVER['argv'][1]);
	$projectsNew = [];
	foreach($projs as $p) {
		echo $p .PHP_EOL;
		$projectsNew[$p] = $config['projects'][$p];
	}
	$config['projects'] = $projectsNew;
}

$config = (object)$config;

$command = '';
foreach ($config->projects as $project => $branch) {
	
    if (!file_exists('../output/' . $project)) {
        mkdir('../output/' . $project);
    }

	echo $project . PHP_EOL;

    $command .= 'echo "----------------------------------------" ' . PHP_EOL;
    $command .= 'echo "' . $project . '" ' . PHP_EOL;
	$command .= 'rm -rf ' . $mainDir . '/../output/' . $project . '/'. PHP_EOL;
	$command .= 'mkdir ' . $mainDir . '/../output/' . $project . '/'. PHP_EOL;
	
  
    $projectDir = rtrim($config->projectDocRoots[$project], '/') . '/';

    $command .= 'cd ' . $projectDir . PHP_EOL;
    $command .= 'git checkout ' . $branch . PHP_EOL;
	
	if($config->gitPull) {
		$command .= 'git pull' . PHP_EOL;
	}
    
    //git összerakés start
	
    foreach ($config->users as $niceName => $gitUsers) {
		$command .= 'touch ' . $mainDir . '/../output/' . $project . '/' . $project . '-'  . $niceName . '.txt'. PHP_EOL;

        $command .= 'git ls-files ';
		if(!in_array($niceName, $config->projectUsers[$project])) {
			continue;
		}

        if (isset($config->subDirs[$project])) {
			if(strpos($config->subDirs[$project], '|') === false) {
				$command .= ltrim($config->subDirs[$project], '^');
			} else {
				$locExpl = explode('|', $config->subDirs[$project]);
				foreach($locExpl as &$loc) {
					$loc = '^' . ltrim($loc, '^');
				}
				$locs = implode('|', $locExpl);

				$command .= ' | grep -E  "' . $locs . '" ';
			}

        }


		$fileExts = isset($config->fileExtensions) && is_array($config->fileExtensions) ? implode('$|\.', $config->fileExtensions) :null;

		if($fileExts) {
			$command .= ' | grep -E  "\.' . $fileExts . '$" ';
		}

        $command .= '| xargs -n1 git blame -M -C -w -l -f ' . (strpos($gitUsers, '@') ? '-e ' : ' ');

		if(isset($config->dateSearch) && $config->dateSearch) {
			$command .= '| grep " ' . trim($config->dateSearch) . '" ';
		}

		if(isset($config->ignorableFileLocOrCommits[$project])) {
			$regexp = strpos($config->ignorableFileLocOrCommits[$project], '|') === false ? '' : 'E';
			$command .= '| grep -' . $regexp . 'v "' . $config->ignorableFileLocOrCommits[$project] . '" ';
		}

		 $command .= '| grep -E "' . $gitUsers . '" '
            . ' > ' . $mainDir . '/../output/' . $project . '/' . $project . '-' . $niceName . '.txt || echo "NO ROWS FOUND (' .  $niceName . ')"' . PHP_EOL;
    }
}

file_put_contents('../output/git-stat.sh', $command);

