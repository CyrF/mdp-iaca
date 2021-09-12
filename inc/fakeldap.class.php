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

/**
 * Classe pour gerer un annuaire ldap
 *
 */
class AnnuaireLDAP {
	private $ldap_server;
	private $ldap_base_dn;
	private $bound;

	function __construct($dc, $admin, $pass, $racine) {
		$this->ldap_server	= 'localhost';
		$this->ldap_base_dn	= $dc;
		$this->bound		= false;
	}
	
	/**
	 * Tente de s'authentifier avec un compte LDAP
	 *
	 *	@param string $username	nom d'ouverture de session.
	 *	@param string $password mot de passe.
	 *
	 *	@return bool true si connexion ok
	 */
	function authentifier($username, $password) {
		if ($username == "prof" && $password == "prof") {
			return true;
		} else {
			return false;
		}
	}
		
	/**
	 * Retourne les utilisateurs
	 *
	 *	@return array
	 */
	function get_users_info($uid='*'){
		if ($uid == "prof") {
			return array(
				'cn' => "Hilaire Prof",
				'uid' => "prof");
		}
	}

	/**
	 * Retourne les classes non vides
	 *
	 *
	 *	@return array
	 */
	function get_classes(){
		return array('Justice League', 'Gotham City', 'Metropolis', 'Themyscira', 'Sector 666', 'Atlantis');
	}
	
	/**
	 * Retourne les groupes associés a un utilisateur
	 *
	 *	@param string $grp	nom du groupe classe
	 *
	 *	@return array
	 */
	function get_usergroups($grp='*', $description=false, $cn='*'){
		switch($grp) {
			case 'Justice League':
				return array(
					array('NomComplet' => 'Arthur Curry', 'Identifiant' => 'aqua', 'logoncount' => rand(0,10)), 
					array('NomComplet' => 'Bruce Wayne', 'Identifiant' => 'batman', 'logoncount' => 0), 
					array('NomComplet' => 'Dinah Drake', 'Identifiant' => 'canary', 'logoncount' => rand(0,10)),
					array('NomComplet' => 'Victor Stone', 'Identifiant' => 'cyborg', 'logoncount' => rand(0,10)), 
					array('NomComplet' => 'Hal Jordan', 'Identifiant' => 'green', 'logoncount' => rand(0,10)), 
					array('NomComplet' => 'John Stewart', 'Identifiant' => 'green2', 'logoncount' => rand(0,10)), 
					array('NomComplet' => 'Barry Allen', 'Identifiant' => 'flash', 'logoncount' => rand(0,10)), 
					array('NomComplet' => 'John Jonzz', 'Identifiant' => 'martian', 'logoncount' => rand(0,10)), 
					array('NomComplet' => 'Kal-El', 'Identifiant' => 'superman', 'logoncount' => rand(0,10)),  
					array('NomComplet' => 'Lois Lane', 'Identifiant' => 'lois', 'logoncount' => rand(0,10)),  
					array('NomComplet' => 'Kara-Zor-El', 'Identifiant' => 'supergirl', 'logoncount' => rand(0,10)),  
					array('NomComplet' => 'Diana Prince', 'Identifiant' => 'wonder', 'logoncount' => rand(0,10)),
					array('NomComplet' => 'Alfred Pennyworth', 'Identifiant' => 'alfred', 'logoncount' => rand(0,10)), 
					array('NomComplet' => 'Barbara Gordon', 'Identifiant' => 'oracle', 'logoncount' => rand(0,10)), 
					array('NomComplet' => 'Roy Harper', 'Identifiant' => 'arsenal', 'logoncount' => rand(0,10)), 
					array('NomComplet' => 'Oliver Queen', 'Identifiant' => 'greenarrow', 'logoncount' => rand(0,10)), 
					array('NomComplet' => 'Ray Palmer', 'Identifiant' => 'atom', 'logoncount' => rand(0,10)), 
					array('NomComplet' => 'Billy Batson', 'Identifiant' => 'shazam', 'logoncount' => rand(0,10)));
				break;
			case 'Gotham City':
				return array(
					array('NomComplet' => 'Dinah Drake', 'Identifiant' => 'canary', 'logoncount' => rand(0,10)), 
					array('NomComplet' => 'Bruce Wayne', 'Identifiant' => 'batman', 'logoncount' => rand(0,10)), 
					array('NomComplet' => 'Alfred Pennyworth', 'Identifiant' => 'alfred', 'logoncount' => rand(0,10)), 
					array('NomComplet' => 'Barbara Gordon', 'Identifiant' => 'oracle', 'logoncount' => rand(0,10)), 
					array('NomComplet' => 'Roman Sionis', 'Identifiant' => 'blask', 'logoncount' => rand(0,10)));
				break;
			case 'Metropolis':
				return array(
					array('NomComplet' => 'Kal-El', 'Identifiant' => 'superman', 'logoncount' => rand(0,10)));
				break;
			case 'Themyscira':
				return array( 
					array('NomComplet' => 'Diana Prince', 'Identifiant' => 'wonder', 'logoncount' => rand(0,10)));
				break;
			case 'Sector 666':
				return array(
					array('NomComplet' => 'Hal Jordan', 'Identifiant' => 'green', 'logoncount' => rand(0,10)), 
					array('NomComplet' => 'Atrocitus', 'Identifiant' => 'red', 'logoncount' => rand(0,10)));
				break;
			case 'Atlantis':
				return array( 
					array('NomComplet' => 'Arthur Curry', 'Identifiant' => 'aqua', 'logoncount' => rand(0,10)));
				break;
			default:
				return array('Justice League', 'Gotham City', 'Metropolis', 'Themyscira', 'Sector 666', 'Atlantis');
		}
		/*/////
		$this->connecter();
		if ($uid == '*') {
			$filtre = "(&(objectClass=posixGroup)(cn=" . ldap_escape($cn, '*', LDAP_ESCAPE_FILTER) . "))";
		} else {
			$filtre = "(&(objectClass=posixGroup)(memberUid=" . ldap_escape($uid, '*', LDAP_ESCAPE_FILTER) . "))";
		}
		$cherche = ldap_search($this->ds, $this->ldap_uo['group'], $filtre);
		$info = ldap_get_entries($this->ds, $cherche);
		$resultat = array();

		for ($i=0; $i<$info["count"]; $i++) {
			if ($info[$i]["description"][0] != "private") {
				if ($description) {
					$resultat[] = array(
						'description' => $info[$i]["description"][0],
						'cn' => $info[$i]["cn"][0],
						'gidnumber' => $info[$i]["gidnumber"][0],
						'memberuid' => $info[$i]["memberuid"]);
				} else {
					$resultat[] = $info[$i]["cn"][0];
				}
			}
		}
*/
	}
}