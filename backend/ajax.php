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

require ("inc/journal.class.php");		// journalisation d'activité
include ("inc/func_ElephantBleu.php");	// session php, charge config, comm. avec iaca, divers.
include ('inc/' . Config::get ('Global_mode') . 'ldap.class.php');	// interroge active directory

define ('PROF', 2);	// niveau d'acces

// initialise le journal
$ev = new JournalEvent ('ActionUtilisateur');
$ev->set_donnee ('MachineSource', ""); //gethostbyaddr ($_SERVER['REMOTE_ADDR']));
$ev->set_donnee ('Utilisateur', (($_SESSION['user_name']) ?? 'nobody'));


// si un utilisateur est connecté
if (isset ($_SESSION['user_id']) && ! empty ($_SESSION['user_id'])) {
	//autologout: verifie que la session n'est pas expiree
	if (time() - $_SESSION['timestamp'] > Config::get ('Global_idletime')) {
		$ev->creer ('AJAX - La session a expirée.', E_NOTICE);
		session_destroy();
		session_unset();
		echo "Votre session a expirée.";
	} elseif ($_SESSION['acces'] >= PROF) {
		// les donnees sont en json, curieusement $_POST est vide ?
		$jsonData = file_get_contents ("php://input");
		$data = json_decode ($jsonData, true);
		$ev->set_donnee ('Utilisateur', $_SESSION['user_name']);

		if (isset ($data['set'])) {
			// on recoit les infos encodées en base64
			$utilisateur =	base64_decode (htmlspecialchars ($data['uid']));
			$mdp =			base64_decode (htmlspecialchars ($data['set']));
			$ev->creer ('Tentative de changement du mot de passe de ' . $utilisateur, E_NOTICE);
			$result = '';
			$result = "SET=". iaca_setmdp ($utilisateur, $mdp);	// demande a iaca de changer le mdp
		//	$result .= " HIDE=". iaca_hidemdp ($utilisateur);	// plus besoin avec la derniere version.

			// Apparemment, il faut appliquer 2 fois pour que ca soit pris en compte.
			if (isset ($data['otp']) && $data['otp']) {
				ldap_mdptemporaire ($utilisateur);
				ldap_mdptemporaire ($utilisateur);
			}

			if ($result == "SET=OK HIDE=OK" or $result == "SET=OK") {
				$ev->creer ('le mot de passe de ' . $utilisateur . ' a été changé.', E_PARSE);
				echo "OK";
			} else {
				$ev->creer ('Erreur ' . $result . ' en changeant le mot de passe de ' . $utilisateur, E_WARNING);
				echo $result;
			}
		}

		if (isset ($data['get'])) {
			// on recoit les infos encodées en base64
			$utilisateur =	base64_decode (htmlspecialchars ($data['get']));
			$ev->creer ('Tentative d\'affichage du mot de passe de ' . $utilisateur, E_NOTICE);

			// attends quelques millisecondes en cas de nombreuses demandes simultanées
			time_nanosleep (0, rand (1000,1000000));

			// demande un mdp a iaca
			echo iaca_getmdp ($utilisateur);
		}
	} else {
		$ev->creer ('AJAX - Droit d\'accès insuffisants.', E_ERROR);
		echo "Votre session a expirée.";
	}
} else {
	$ev->creer ('AJAX - Accès non authentifié.', E_ERROR);
	echo "Votre session a expirée.";
}
