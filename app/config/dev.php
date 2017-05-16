<?php

//include the prod configuration
require __DIR__.'/prod.php';

//enable the debug mode
$app['debug'] = true;

parameters:
	brochures_directory: '%kernel.root_dir%/../web/images';