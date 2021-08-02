<?php

$formats = [
	[
		'wxh' => [1250, 1850],
		[
			[
				'thickness' => 2,
				'color' => 'Transparente',
				'price' => 27370
			],
			[
				'thickness' => 3,
				'color' => 'Transparente',
				'price' => 38080
			],
			[
				'thickness' => 3,
				'color' => 'Negro Sólido',
				'price' => 42840
			],
			[
				'thickness' => 3,
				'color' => 'Blanco Lechoso',
				'price' => 42840
			],
			[
				'thickness' => 4,
				'color' => 'Transparente',
				'price' => 49980
			],
			[
				'thickness' => 5,
				'color' => 'Transparente',
				'price' => 66640
			]
		]
	],
	[ 
		'wxh' => [1240, 2460],
		[
			[
				'thickness' => 3,
				'color' => 'Transparente',
				'price' => 51170
			],
			[
				'thickness' => 4,
				'color' => 'Transparente',
				'price' => 65450
			],
			[
				'thickness' => 5,
				'color' => 'Transparente',
				'price' => 77350
			]
		]
	]
];

$colors  = [
	0 => [
		'name' => 'Transparente',
		'rgba' => 'rgba(0, 0, 0, 0)'
	],
	1 => [
		'name' => 'Negro Sólido',
		'rgba' => 'rgba(0, 0, 0, 1)'
	],
	3 => [
		'name' => 'Blanco Lechoso',
		'rgba' => 'rgba(223,223,223,1)'
	],
];


# Cantidad mínima en [mm] para ancho o largo
define('MIN_DIM', 50);

