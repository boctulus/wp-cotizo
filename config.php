<?php

# Cantidad mínima en [cm] para ancho o largo
$abs_min_dim = 3;

#
#	Largo y ancho (wxh) de los paneles están expresados en cm !!!
#
$formats = [
		[
			'wxh' => [125, 185],
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
			'wxh' => [124, 246],
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




