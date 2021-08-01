<?php

# Cantidad mínima en [mm] para ancho o largo
define('MIN_DIM', 50);

$formats = [
	[
		'wxh' => [1250, 1850],
		[
			[
				'thickness' => 2,
				'color' => 'Transparente',
				'price' => [
					'net' 		=> 23000,
					'net_vat' 	=> 27370
				]
			],
			[
				'thickness' => 3,
				'color' => 'Transparente',
				'price' => [
					'net' 		=> 32000,
					'net_vat' 	=> 38080
				]
			],
			[
				'thickness' => 3,
				'color' => 'Negro Sólido',
				'price' => [
					'net' 		=> 36000,
					'net_vat' 	=> 42840
				]
			],
			[
				'thickness' => 3,
				'color' => 'Blanco Lechoso',
				'price' => [
					'net' 		=> 36000,
					'net_vat' 	=> 42840
				]
			],
			[
				'thickness' => 4,
				'color' => 'Transparente',
				'price' => [
					'net' 		=> 42000,
					'net_vat' 	=> 49980
				]
			],
			[
				'thickness' => 5,
				'color' => 'Transparente',
				'price' => [
					'net' 		=> 56000,
					'net_vat' 	=> 66640
				]
			]
		]
	],
	[ 
		'wxh' => [1240, 2460],
		[
			[
				'thickness' => 3,
				'color' => 'Transparente',
				'price' => [
					'net' 		=> 43000,
					'net_vat' 	=> 51170
				]
			],
			[
				'thickness' => 4,
				'color' => 'Transparente',
				'price' => [
					'net' 		=> 55000,
					'net_vat' 	=> 65450
				]
			],
			[
				'thickness' => 5,
				'color' => 'Transparente',
				'price' => [
					'net' 		=> 65000,
					'net_vat' 	=> 77350
				]
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
