<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

$loader = new Twig_Loader_Filesystem('template/');
$twig = new Twig_Environment($loader, array(
    // 'cache' => 'cache/',
));

echo $twig->render('index.html.twig', array(
	'search_saved' => $search_saved
	, 'search_saved_anime' => $search_saved_anime
));