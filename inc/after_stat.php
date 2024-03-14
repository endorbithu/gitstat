<?php

$userCounts = ['ALL' => []];
foreach (scandir('../output/') as $project) {
    if (strpos($project, '.') !== false) continue;
    $projectDir = '../output/' . $project . '/';
    if (!isset($userCounts[$project])) {
        $userCounts[$project] = [];
    }

    foreach (scandir($projectDir) as $user) {
        if (strpos($user, '.') === 0) continue;
        if (strpos($user, '_') !== false) continue;
        $userName = explode('.', $user)[0];
        $commits = [];
        $files = [];

        $file = $projectDir . $user;
        $handle = fopen($file, "r");
        if ($handle) {
            $rows = 0;
            while (($line = fgets($handle)) !== false) {
                if (empty($line)) continue;
                $cols = explode(' ', $line);
				$commUrl = 'https://github.com/delocalzrt/' . $project . '/commit/' . $cols[0];
                $commits[$commUrl] = ($commits[$commUrl] ?? 0) +1;
                $files[$cols[1]] = (($files[$cols[1]] ?? 0) + 1);
                $rows++;
            }

            $userCounts['ALL'][$userName] = $userCounts['ALL'][$userName] ?? 0;
            $userCounts['ALL'][$userName] += $rows;
            $userCounts[$project][$userName] = $rows;
            fclose($handle);
        }

        $fileCommits = str_replace('.txt', '_commits.txt', $file);
		arsort($commits, SORT_NUMERIC);
        file_put_contents($fileCommits, print_r($commits, true));

        $fileFiles = str_replace('.txt', '_files.txt', $file);
		arsort($files, SORT_NUMERIC);
        file_put_contents($fileFiles, print_r($files, true));
    }
}

$rows = '';
foreach ($userCounts as $project => $userCount) {
    if (empty($rows)) {
        $rowHeader = 'PROJECT';
        foreach ($userCount as $user => $item) {
			$user = explode('-', $user)[1];
            $rowHeader .= ';' . $user;
        }
        $rows .= $rowHeader . PHP_EOL;
    }

    $row = $project;
    foreach ($userCount as $user => $item) {
        $row .= ';' . $item;
    }
    $row .= PHP_EOL;
    $rows .= $row;
}
/*
foreach ($userCounts as $project => $userCount) {
    $row = $project;
    foreach ($userCount as $user => $item) {
        $row .= ';' . $user . ':' . $item;
    }
    $row .= PHP_EOL;
    $rows .= $row;
}
*/
file_put_contents('../output/result.csv', $rows);
