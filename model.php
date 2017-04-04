<?php

//return all billets
function getBillets() {
	$bdd = new PDO('mysql:host=localhost;dbname=alaska;charset=utf8', 'root', '');
	$billets = $bdd->query('select * from t_billet order by billet_id desc');
	return $billets;
}