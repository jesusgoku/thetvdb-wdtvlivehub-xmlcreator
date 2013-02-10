<?php
class TVDB
{
	protected $apiKey;
	protected $series = array();
	protected $urlBase = 'http://thetvdb.com/';
	protected $urlBaseBanners;
	protected $urlBaseApi;
	protected $config = array();

	public function __construct( $apiKey )
	{
		$this->apiKey = $apiKey;
		$this->urlBaseBanners = $this->urlBase . 'banners/';
		$this->urlBaseApi = $this->urlBase . 'api/' . $this->apiKey . '/';
		$this->config = array(
			'search' => $this->urlBase . 'api/GetSeries.php?seriesname=%s&language=%s'
			, 'series' => $this->urlBaseApi . 'series/%s/%s.xml'
			, 'series-all' => $this->urlBaseApi . 'series/%s/all/%s.xml'
			, 'banners' => $this->urlBaseApi . 'series/%s/banners.xml'
		);
	}

	public function search( $q, $lang = 'es' )
	{
		return json_decode( json_encode( simplexml_load_file( sprintf($this->config['search'],urlencode($q),$lang) )), TRUE );
	}

	public function getSerie( $seriesid, $lang = 'es' )
	{
		$serie = json_decode( json_encode( simplexml_load_file( sprintf( $this->config['series-all'], $seriesid, $lang ) ) ), TRUE );
		$banners = json_decode( json_encode( simplexml_load_file( sprintf( $this->config['banners'], $seriesid ) ) ), TRUE );

		return array(
			'serie' => $serie
			, 'banners' => $banners
		);
	}
}