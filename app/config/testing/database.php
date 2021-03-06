<?php

return array(

	'default' => 'sqlite',

	'connections' => array(
	
			'mysql' => array(
					'driver'    => 'mysql',
					'host'      => 'localhost',
					'database'  => 'bvw',
					'username'  => $_ENV['dbusr'],
					'password'  => $_ENV['dbpass'],
					'charset'   => 'utf8',
					'collation' => 'utf8_unicode_ci',
					'prefix'    => '',
					'port'		=> '3307',
			),
			
			'sqlite' => array(
					'driver'=>'sqlite',
					'database'=>__DIR__.'/../../database/test.sqlite',
					'prefix'=>''
			)
	
	)
);