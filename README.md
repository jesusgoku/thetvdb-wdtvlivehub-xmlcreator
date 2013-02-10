# The TV DB - WD TV Live HUB - XML Creator

¡Tremendo nombre!... pero es la descripción un poco de lo que hace esta app
que se conecta con la API de http://thetvdb.com para rescatar la
información de tus series favoritas y conseguir las sinopsis de cada
capitulo y tambien una miniatura adecuada a fin de mantener siempre
agradable tu WD TV Live HUB.

## Antes de usar

1. Editar el archivo config.example.php
2. Editar la variable $api_key con tu API KEY de TVDB
3. Guardar el archivo como config.php
4. Crear carpeta **downloaded**
5. Asignar permisos 777 a las carpetas **downloaded**, **cache** y todos sus subcarpetas
6. Ejecutar composer

## Composer

Para ejecutar **composer** primero deben descargarlo:

	curl -s https://getcomposer.org/installer | php

Luego ejecutar para instalar las dependencias:

	php composer.phar install

## Optimizaciones pendientes

1. Descargar las sinopsis de una Season en ZIP (OK)
2. Descargar individualmente la sinopsis y un cover para un episodio (OK)
3. Descargar una vez un season poster y luego copiar y renombrar para el resto (OK)
4. Guardar los datos devueltos de una serie en el cliente (OK)
5. Dar a elegir el cover que se usara para la season (NOT)
6. Mostrar Fant-Art, Posters, Banners y otros (NOT)
7. Perfeccionar console.php ofreciendo modelos de busqueda y/o ingresar su propia expresion