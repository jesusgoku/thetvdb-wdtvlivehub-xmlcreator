<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

$loader = new Twig_Loader_Filesystem('template/');
$twig = new Twig_Environment($loader, array(
    // 'cache' => 'cache/',
));

function addzero( $num )
{
	return $num < 10 ? '0' . $num : $num;
}

function parseName( $name )
{
	$search = array(' ', 'á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ');
	$replace = array('.', 'a', 'e', 'i', 'o', 'u', 'n', 'A', 'E', 'I', 'O', 'U', 'N');
	return trim(preg_replace('/[^\w\.]/','',str_replace($search,$replace,$name)), '.');
}

function parseFolderName( $name )
{
	$search = array('á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ');
	$replace = array('a', 'e', 'i', 'o', 'u', 'n', 'A', 'E', 'I', 'O', 'U', 'N');
	$searchtwo = array('\\','/',':','*','?','<','>', '.', '|');
	return trim(str_replace($searchtwo,'',str_replace($search,$replace,$name)), '.');
}

function tvdb_search( $q )
{
	$api_url_search = $GLOBALS['api_url_search'];
	$q = urlencode( $q );
	$cache_file = __DIR__ . '/cache/thetvdb/' . $q . '.json';
	if( file_exists( $cache_file ) )
	{
		$xml = json_decode( file_get_contents( $cache_file ) );
	}
	else
	{
		$xml = simplexml_load_file( $api_url_search . $q );
		if (!file_exists(dirname($cache_file))) {
			mkdir(dirname($cache_file), 0777, true);
		}
		file_put_contents($cache_file, json_encode($xml));
	}

	return $xml;
}

function tvdb_serieall( $seriesid, $language = 'es' )
{
	$api_url_all = $GLOBALS['api_url_all'];
	$cache_file_all = __DIR__ . '/cache/thetvdb/' . $seriesid . '-all-' . $language . '.json';
	if( file_exists( $cache_file_all ) )
	{
		$xmlall = json_decode( file_get_contents( $cache_file_all ) );
	}
	else
	{
		$xmlall = simplexml_load_file( sprintf($api_url_all, $seriesid, $language) );
        if (!file_exists(dirname($cache_file_all))) {
            mkdir(dirname($cache_file_all, 0777, true));
        }
		file_put_contents( $cache_file_all, json_encode( $xmlall ) );
	}

	return $xmlall;
}

function tvdb_banners( $seriesid )
{
	$api_url_banners = $GLOBALS['api_url_banners'];
	$cache_file_banners = __DIR__ . '/cache/thetvdb/' . $seriesid . '-banners.json';
	if( file_exists( $cache_file_banners ) )
	{
		$xmlbanners = json_decode( file_get_contents( $cache_file_banners ) );
	}
	else
	{
		$xmlbanners = simplexml_load_file( sprintf($api_url_banners, $seriesid) );
        if (!file_exists(dirname($cache_file_banners))) {
            mkdir(dirname($cache_file_banners, 0777, true));
        }
		file_put_contents( $cache_file_banners, json_encode( $xmlbanners ) );
	}

	return $xmlbanners;
}

$twig->addFilter(new Twig_SimpleFilter('addzero','addzero'));

$accion = $_REQUEST['accion'];

switch( $accion )
{
	case 'search':
		$q = $_REQUEST['q'];

		$xml = tvdb_search( $q );

		header('Content-type: application/json');
		echo json_encode( $xml );
		break;
	case 'serie':
		$seriesid = $_REQUEST['seriesid'];
		$language = $_REQUEST['language'];

		$xmlall = tvdb_serieall( $seriesid, $language );
		$xmlbanners = tvdb_banners( $seriesid );
		
		header('Content-type: application/json');
		echo json_encode( array(
			'serie' => $xmlall
			, 'banners' => $xmlbanners
		) );
		break;
	case 'episodemakexml':
		$seriesid = $_REQUEST['seriesid'];
		$language = $_REQUEST['language'];
		$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : false;
		$season = isset($_REQUEST['season']) ? $_REQUEST['season'] : false;
		$only = isset($_REQUEST['only']) ? $_REQUEST['only'] : false;

		$xml = tvdb_serieall( $seriesid, $language );
		$xmlbanners = tvdb_banners( $seriesid );

		/* ---- La recodificamos como array ------------------------ */
		$xml = json_decode( json_encode( $xml ), true );
		$xmlbanners = json_decode( json_encode( $xmlbanners ), true );

		/* ---- CREAMOS EL ZIP ------------------------------------ */
		if( !$only )
		{
			$zip = new ZipArchive();
			$zip->open(__DIR__ . '/cache/temp.zip', ZIPARCHIVE::OVERWRITE);
		}

		foreach( $xml['Episode'] as $item )
		{
			$item = array_merge($xml['Series'], $item);
			if( $item['id'] === $id || $item['SeasonNumber'] === $season )
			{
				// var_dump( $item );
				$item['Genre'] = implode('/', explode('|', trim($item['Genre'], '|')));
				$item['Actors'] = implode('/', explode('|', trim($item['Actors'], '|')));
				// --
				foreach( $xmlbanners['Banner'] as $itemb )
				{
					if( $itemb['BannerType'] == 'season' && $itemb['Season'] == $item['SeasonNumber'] )
					{
						$banner = $itemb;
						break;
					}
				}
				
				$base_season = parseFolderName($item['SeriesName']) . ' - Season ' . addzero($item['SeasonNumber']);
				$folder_serie = __DIR__ . '/downloaded/' . parseFolderName($item['SeriesName']) . '/';
				$folder_season = $folder_serie . 'Season ' . addzero($item['SeasonNumber']) . '/';
				$base_folder = $folder_season;
				$base_file = parseName($item['SeriesName']) . '.S' . addzero($item['SeasonNumber']) . 'E' . addzero($item['EpisodeNumber']) . '.' . parseName($item['EpisodeName']);
				$base_path = $base_folder . $base_file;
				$xml_file = $base_path . '.xml';
				$thumb_file = $base_path . '.metathumb';
				$cache_banner = __DIR__ . '/cache/images/' . basename( $banner['BannerPath'] );
				/* ---- VERIFICO Y/O CREO FOLDER ----------------- */
				if( ! file_exists( $folder_serie ) ) mkdir($folder_serie);
				if( ! file_exists( $folder_season ) ) mkdir($folder_season);
				/* ---- DESCARGO XML ----------------------------- */
				if( $only && $only == 'xml')
				{
					header('Content-type: text/xml');
					header('Content-disposition: attachment; filename=' . $base_file . '.xml');
					echo $twig->render('episodewd.xml.twig', $item);
					exit;
				}
				else
				{
					file_put_contents( $xml_file, $twig->render('episodewd.xml.twig', $item) );
					if( isset($zip) ) $zip->addFile($xml_file, $base_file . '.xml');
				}
				/* ---- DESCARGO BANNER ------------------------- */
				if( !empty( $banner ) && !file_exists( $cache_banner ) )
				{
                    if (!file_exists(dirname($cache_banner))) {
                        mkdir(dirname($cache_banner), 0777, true);
                    }
					file_put_contents( $cache_banner, file_get_contents( $api_url_banners_base . $banner['BannerPath'] ) );
				}

				if( !empty( $banner ) )
				{
					if( $only && $only == 'metathumb' )
					{
						header('Content-type: image/jpeg');
						header('Content-disposition: attachment; filename=' . $base_file . '.metathumb');
						readfile( $cache_banner );
						exit;
					}
					else
					{
						copy( $cache_banner, $thumb_file );
						$zip->addFile($thumb_file, $base_file . '.metathumb');
					}
				}
			}
		}

		if( !$only )
		{
			// $zip = new ZipArchive();
			// $zip->open('temp.zip', ZIPARCHIVE::OVERWRITE);
			// $zip->addFile($xml_file, $base_file . '.xml');
			// $zip->addFile($thumb_file, $base_file . '.metathumb');
			$zip->close();

			header('Content-type: application/zip');
			header('Content-disposition: attachment; filename=' . ($season ? $base_season : $base_file) . '.zip');
			readfile(__DIR__ . '/cache/temp.zip');
			exit;
		}
		break;
}
