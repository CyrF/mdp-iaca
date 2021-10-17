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
			// on recoit les infos encodées en base64
			$utilisateur =	base64_decode(htmlspecialchars($_GET['uid']));
			$mdp =			base64_decode(htmlspecialchars($_GET['set']));
			
			$result = "SET=". iaca_setmdp($utilisateur, $mdp);	// demande a iaca de changer le mdp
		//	$result .= " HIDE=". iaca_hidemdp($utilisateur);	// marche pas pour l'instant
		
			if ($result == "SET=OK HIDE=OK" or $result == "SET=OK") {
				echo "OK";
			} else {
				echo $result;
			}
		}
		if ( isset( $_GET['get'] )) {
			// on recoit les infos encodées en base64
			$utilisateur =	base64_decode( htmlspecialchars($_GET['get']));
			
			// attends quelques millisecondes en cas de nombreuses demandes simultanées
			time_nanosleep(0, rand(1000,1000000));
			
			// demande un mdp a iaca 
			echo iaca_getmdp( $utilisateur );
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
	global $_CONF;
	if( $_CONF['mode'] == 'fake' ) { return 'OK'; }
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
 * demande a iaca de masquer le mdp
 *
 *	@param string $utilisateur	nom d'utilisateur dans l'AD
 *
 *	@return string				"OK" | "Erreur"
 */
function iaca_hidemdp($utilisateur) {
	global $_CONF;
	$REPONSE="";		
	$fp=stream_socket_client("tcp://{$_CONF['AD_ServerIP']}:5016",$numerr,$strerr,1);
	if ($fp) {		
		fwrite($fp,"NU=$utilisateur|MDP=**************\r\n");
		stream_set_timeout($fp, 2);
		$REPONSE=stream_get_contents($fp);
	}
	fclose($fp);
	if (strchr($REPONSE, "MDP_OK")) {
		return "OK";
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
	global $_CONF;
	if( $_CONF['mode'] == 'fake' ) { return Creer_Pass( 8 ); }
	$REPONSE="";
	//$utilisateur = substr($utilisateur, 4);
	$fp=fsockopen($_CONF['AD_ServerIP'],5016,$numerr,$strerr,1);
	if ($fp) {
		fputs($fp,"NU=$utilisateur|GETMDP");
		$REPONSE=fgets($fp,64);
	}
	fclose($fp);
	// supprime l'entete recue pour retourner que le mdp
	return trim(substr($REPONSE, strlen($utilisateur) + 11));
}

/**
 * Cree un mot de passe aleatoire
 *
 *	@param int $n		longueur du mot de passe
 *
 *	@return string
 */
function Creer_Pass( $n=5 ) {
	return substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',$n)),0,$n);
}