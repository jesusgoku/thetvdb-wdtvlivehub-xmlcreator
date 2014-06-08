<?php
ini_set('display_errors', 1);

function expand_user( $ruta )
{
	return preg_replace('/^~\//',$_ENV['HOME'] . '/', $ruta);
}

function question_yn( $question )
{
	$answer = false;
	do
	{
		echo $question . ' [y/n]: ';
		$answer = trim(fgets(STDIN));
		if( $answer != 'y' && $answer != 'n' )
			echo 'Respuesta incorrecta!' . "\n";
	}while( $answer != 'y' && $answer != 'n' );
	return $answer;
}

$ruta = expand_user( $_SERVER['argv'][1] );
echo 'Open route: ' . $ruta . "\n";

$files = scandir( $ruta );

if( $dp = opendir( $ruta ) )
{
	while( $file = readdir( $dp ) )
	{
		$matches = array();/* 0 => Todo, 1 => SeriesName, 2 => Season&Episode, 3 => EpisodeName */
		preg_match('/^(([\w\.]+)\.(S[\d]{2}E[\d]{2})\.([\w\.]+))\.xml$/', $file, $matches);
		if( $matches )
		{
			echo "\n\n----------------------------------------------------------------------\n\n"; 
			echo $file . "\n";
			$file_founds_pattern = '/^[\w\. -]+'. preg_replace('/^S[\d]{2}E/','0', $matches[3]) . '[\w\. -\[\]]*\.(mp4|avi|mkv|srt)$/';
			// $file_founds_pattern = '/^[\w][\w\. -]+'. str_replace(array('S0','E'),array('', ''), $matches[3]) . '[\w\. -\[\]]*\.(mp4|avi|mkv|srt)$/';
			// $file_founds_pattern = '/^[\w][\w\. -]+'. $matches[3] . '[\w\. -]*\.(mp4|avi|mkv|srt)$/';
			// $file_founds_pattern = '/^[\w\. ]*'. $matches[3] . '[\w\. ]+\.(srt)$/';
			$file_founds = preg_grep($file_founds_pattern, $files );
			if( $file_founds )
			{
				foreach( $file_founds as $item )
				{
					preg_match($file_founds_pattern, $item, $m);
					$file_destine = $matches[1] . '.' . $m[1];
					if( $item ==  $file_destine)
						continue;
					echo $item . " -> Encontrado\n";
					$answer = question_yn( $file_destine . ' -> Renombrar?' );
					if( $answer == 'y' )
					{
						if( rename($ruta . '/' . $item, $ruta . '/' . $file_destine) )
						{
							echo 'Renombado a: ' . $file_destine . "\n";
						}
						else
						{
							echo 'Error: no se logro renombrar.' . "\n";
						}
					}
				}
			}
		}
	}
	closedir( $dp );
}

echo "\n\n";
