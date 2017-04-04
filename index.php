
<?php
//data access
require 'model.php';
$billets = getBillets();
//data display
require 'view.php';