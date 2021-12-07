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
include("inc/{$_CONF['mode']}ldap.class.php");
include("inc/func_ElephantBleu.php");


// si un utilisateur est connecté
if ( isset( $_SESSION['user_id'] ) && ! empty( $_SESSION['user_id'] ) ) {
	//autologout: verifie que la session n'est pas expiree
	if ( time() - $_SESSION['timestamp'] > $_CONF['idletime'] ){
		session_destroy();
		session_unset();
		echo "Votre session a expirée.";
	} else {
		// les donnees sont en json, curieusement $_POST est vide ?
		$jsonData = file_get_contents("php://input");
		$data = json_decode($jsonData, true);

		if ( isset( $data['set'] )) {
			// on recoit les infos encodées en base64
			$utilisateur =	base64_decode(htmlspecialchars($data['uid']));
			$mdp =			base64_decode(htmlspecialchars($data['set']));
			$result = '';
			$result = "SET=". iaca_setmdp($utilisateur, $mdp);	// demande a iaca de changer le mdp
		//	$result .= " HIDE=". iaca_hidemdp($utilisateur);	// plus besoin avec la derniere version.

			// Apparemment, il faut appliquer 2 fois pour que ca soit pris en compte.
			ldap_mdptemporaire($utilisateur);
			ldap_mdptemporaire($utilisateur);

			if ($result == "SET=OK HIDE=OK" or $result == "SET=OK") {
				echo "OK";
			} else {
				echo $result;
			}
		}

		if ( isset( $data['get'] )) {
			// on recoit les infos encodées en base64
			$utilisateur =	base64_decode( htmlspecialchars($data['get']));

			// attends quelques millisecondes en cas de nombreuses demandes simultanées
			time_nanosleep(0, rand(1000,1000000));

			// demande un mdp a iaca
			echo iaca_getmdp( $utilisateur );
		}
	}
} else {
	echo "Votre session a expirée.";
}
