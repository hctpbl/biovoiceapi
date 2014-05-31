<?php

return array(

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
					'unix_socket' => $_ENV['socket'],
					'port'		=> '3307',
			),
	
	)
);