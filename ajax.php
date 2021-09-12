<?php
/**
 * Copyright (C) 2020.
 * This file is a part of mdpIacaWeb
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */
 
session_start();
require("inc/config.php");


// si un utilisateur est connecté
if ( isset( $_SESSION['user_id'] ) && ! empty( $_SESSION['user_id'] ) ) {
	//autologout: verifie que la session n'est pas expiree
	if ( time() - $_SESSION['timestamp'] > $_CONF['idletime'] ){
		session_destroy();
		session_unset();
		echo "Votre session a expirée.";
	} else {		
		if ( isset( $_GET['set'] )) {
			// demande a iaca de changer le mdp
			$utilisateur = htmlspecialchars($_GET['uid']);
			$mdp = htmlspecialchars($_GET['set']);
		//	echo iaca_setmdp($utilisateur, $mdp);
			sleep(2);
			echo "OK";
		}
		if ( isset( $_GET['get'] )) {
			// demande un mdp a iaca 
			$utilisateur = htmlspecialchars($_GET['get']);
		//	echo iaca_getmdp($utilisateur);
			sleep(1);
			echo "s7jAV5XuLioe4e6H9JhVwRpv";
		}
	}
} else {
	echo "Votre session a expirée.";
}


/**
 * demande a iaca de changer le mdp
 *
 *	@param string $utilisateur	nom d'utilisateur dans l'AD
 *	@param string $mdp			nouveau mdp a changer
 *
 *	@return string				"OK" | "Erreur"
 */
function iaca_setmdp($utilisateur, $mdp) {
	$REPONSE="";
	$fp=fsockopen($_CONF['AD_ServerIP'],5016,$numerr,$strerr,1);
	if ($fp) {
		fputs($fp,"NU=$utilisateur|MDP=$mdp");
		$REPONSE=fgets($fp,1500);
	}
	fclose($fp);
	if (strchr($REPONSE, "MDP_OK")) {
		return "OK";
	} else {
		return "Une erreur est survenue. ($REPONSE)";
	}
}


/**
 * demande un mdp a iaca 
 *
 *	@param string $utilisateur	nom d'utilisateur dans l'AD
 *
 *	@return string				mot de passe courant | ****
 */
function iaca_getmdp($utilisateur) {
	$REPONSE="";
	$fp=fsockopen($_CONF['AD_ServerIP'],5016,$numerr,$strerr,1);
	if ($fp) {
		fputs($fp,"NU=$utilisateur|GETMDP");
		$REPONSE=fgets($fp,64);
	}
	fclose($fp);
	
	return substr($REPONSE, strlen($utilisateur) + 11);
}
