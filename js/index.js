$(function() {
	var formSearch = $('#form-search');
	var urlProce = 'proce.php';
	var tmplSearch = $('#tmpl-search');
	var listSearch = $('#list-search');

	var tmplSerie = $('#tmpl-serie');
	var listEpisodes = $('#list-episodes');

	var listSearchSaved = $('#search-saved');

	var myModal = $('#myModal');

	var Serie = {};
	var Banners = {};

	formSearch.on('submit', function(e) {
		e.preventDefault();
		$.notify.alert('Buscando serie. Por favor espere...', { occupySpace: true });
		$.getJSON(urlProce, {
			accion: 'search'
			, q: $(this).find('input').val()
		}, function(data) {
			/* ---- En caso de que no se encuntren resultados ---------- */
			if( !(typeof data.Series == "undefined") )
			{
				$.notify.success('Series encontradas...', { occupySpace: true, close: true, autoClose: 3000 });
				/* ---- En caso de que solo venga un resultado ------------- */
				if( ! (typeof data.Series.id == "undefined") )
				{
					var tmp = data.Series;
					data.Series = [];
					data.Series.push( tmp );
				}
				var tmpl = Handlebars.compile( tmplSearch.html() );
				var html = tmpl( data );
				listSearch.html( html );
			}
			else
			{
				$.notify.error('Serie no encontrada', { occupySpace: true, close: true, autoClose: 3000 });
			}
		});
	});

	listSearch.on('click', '.serie', function(e) {
		e.preventDefault();
		$.notify.alert('Rescatando información de la serie. Por favor espere...', { occupySpace: true });
		$.getJSON(urlProce, {
			accion: 'serie'
			, seriesid: $(this).data('seriesid')
			, language: $(this).data('language')
		}, function(data) {
			$.notify.success('Información recuperada.', { occupySpace: true, close: true, autoClose: 3000 });
			var seasons = _.uniq( _.pluck( data.serie.Episode, 'SeasonNumber' ) );
			data.serie.seasons = seasons;
			var tmpl = Handlebars.compile( tmplSerie.html() );
			var html = tmpl( data.serie );
			listSearch.html( html );
			Serie = data.serie;
			Banners = data.banners;
		});
	});

	// listSearch.on('click', '.episodemakexml', function(e) {
	// 	e.preventDefault();
	// 	$.notify.alert('Creado XML and Metathumb', { occupySpace: true });
	// 	$.getJSON(urlProce, {
	// 		accion: 'episodemakexml'
	// 		, seriesid: $(this).data('seriesid')
	// 		, language: $(this).data('language')
	// 		, id: $(this).data('id')
	// 	}, function(data) {
	// 		$.notify.success('XML and Metathumb creados.', { occupySpace: true, close: true, autoClose: 3000 });
	// 		console.log( Banners );
	// 	}).complete(function() {
	// 		$.notify.success('XML and Metathumb creados.', { occupySpace: true, close: true, autoClose: 3000 });
	// 		console.log( Banners );
	// 	});
	// });

	listSearchSaved.on('click', 'a', function(e) {
		e.preventDefault();
		$.notify.alert('Buscando serie. Por favor espere...', { occupySpace: true });
		$.getJSON(urlProce, {
			accion: 'search'
			, q: $(this).data('q')
		}, function(data) {
			/* ---- En caso de que no se encuntren resultados ---------- */
			if( !(typeof data.Series == "undefined") )
			{
				$.notify.success('Series encontradas...', { occupySpace: true, close: true, autoClose: 3000 });
				/* ---- En caso de que solo venga un resultado ------------- */
				if( ! (typeof data.Series.id == "undefined") )
				{
					var tmp = data.Series;
					data.Series = [];
					data.Series.push( tmp );
				}
				var tmpl = Handlebars.compile( tmplSearch.html() );
				var html = tmpl( data );
				listSearch.html( html );
			}
			else
			{
				$.notify.error('Serie no encontrada', { occupySpace: true, close: true, autoClose: 3000 });
			}
		});
	});

	// listSearch.on('click', '.seasonmakexml', function(e) {
	// 	e.preventDefault();
	// 	var $this = $(this);
	// 	var seriesid = $this.data('seriesid');
	// 	var language = $this.data('language');
	// 	var season = $this.data('season');

	// 	$.notify.alert('Descargado Meta-Información y Thumbnails. Espera por favor...', { occupySpace: true });
	// 	$.getJSON(urlProce, {
	// 		accion: 'episodemakexml'
	// 		, seriesid: $(this).data('seriesid')
	// 		, language: $(this).data('language')
	// 		, season: $(this).data('season')
	// 	}, function(data) {
	// 		$.notify.success('Descarga completada, revisa la carpeta downloaded.', { occupySpace: true, autoClose: 3000, close: true });
	// 	}).complete(function(){ $.notify.success('Descarga completada, revisa la carpeta downloaded.', { occupySpace: true, autoClose: 3000, close: true }); });
	// });
});