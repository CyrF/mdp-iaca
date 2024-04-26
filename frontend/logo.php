<?php
/*
 * Renvoie un logo au hazard. Inutile, donc indispensable ?
 *
 * @package mdp-iaca-web
 * @copyright (c) 2022 Cyril Fleury
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */
	
	
// cherche les fichiers logo disponibles
if ($liste = glob (__DIR__ . '/tpl/logo_*.png', GLOB_NOSORT)) {
	// en choisit un au hazard
	$logo = $liste[array_rand ($liste)];
	// l'envoie au navigateur
	header ('Content-type: image/png');
	$fp = fopen ($logo, 'rb');
	fpassthru ($fp);
	exit;
}
