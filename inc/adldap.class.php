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
	protected $ds;
	private $ldap_server;
	private $ldap_user;
	private $ldap_pass;
	private $ldap_ou_elv;
	private $bound;

	function __construct($server, $user, $pass, $racine) {
		$this->ldap_server	= $server;
		$this->ldap_user	= $user;
		$this->ldap_pass	= $pass;
		$this->ldap_ou_elv	= $racine;
		$this->ds			= false;
		$this->bound		= false;
	}
	
	/**
	 * Se connecte au serveur LDAP
	 *
	 *	@param bool $auth	force une connexion authentifiée pour modifier l'annuaire.
	 *
	 *	@return bool true si connexion ok.
	 */
	protected function connecter($auth=false) {
		if (!$this->ds) { // initialise le socket si c'est pas deja fait
			$this->ds = ldap_connect($this->ldap_server);
			ldap_set_option($this->ds, LDAP_OPT_PROTOCOL_VERSION, 3); // php reste en version 2 par defaut.
			ldap_set_option($this->ds, LDAP_OPT_REFERRALS, 0);

		}
		if (!$this->bound or $auth) { // se connecte au serveur si c'est pas deja fait, ou force la reconnexion si non anonyme
			try {
				if ($auth) {
					$r = ldap_bind($this->ds, $this->ldap_user, $this->ldap_pass);
				} else { // en anonyme si pas besoin d'ecrire des données
					$r = ldap_bind($this->ds);
				}
				$this->bound = $r;
			} catch (RuntimeException $e){
				if (ldap_get_option($this->ds, LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error)) {
					echo "Error Binding to LDAP: $extended_error<br>";
				}
				echo "ldap_error: " . ldap_error($this->ds) . '<br>';
				$this->bound = false;
			}
		}
		return $this->bound;
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
		global $_CONF;
		$this->connecter();
		$Autorized = false;
		
		// tente une connection a l'ad...
			$bind = @ldap_bind($this->ds, "{$_CONF['AD_Domain']}\\". ldap_escape($username, '', LDAP_ESCAPE_DN), $password);
		if ($bind) {
			// recherche le chemin complet de l'user pour savoir a quelle UO il appartient.
			$res = ldap_search($this->ds, $_CONF['AD_Chemin'], "(sAMAccountName=". ldap_escape($username, '', LDAP_ESCAPE_DN).")");
			$first = ldap_first_entry($this->ds, $res);
			$data = ldap_get_dn($this->ds, $first);
			
			// compare avec la liste des UO approuvées
			$listAutorise = explode("|", $_CONF['AD_ou_Autorise']);
			foreach ($listAutorise as $auth) {
				if (strpos($data, $auth) !== false ){
					$Autorized = true;
				}
			}
			return $Autorized;
		} else {
			return false;
		}
	}
		
	/**
	 * Retourne les utilisateurs
	 *
	 *	@param string $uid	identifiant de l'utilisateur
	 *
	 *	@return array
	 */
	function get_users_info($uid='*'){
		global $_CONF;
		$this->connecter();
		$res = ldap_search($this->ds, $_CONF['AD_Chemin'], "(sAMAccountName=". ldap_escape($uid, '', LDAP_ESCAPE_DN).")");
		$first = ldap_first_entry($this->ds, $res);
		return array(
			'cn' => ldap_get_values($this->ds, $first, "displayname")[0],
			'uid' => ldap_get_values($this->ds, $first, "samaccountname")[0]);
	}

	/**
	 * Retourne les groupes associés a un utilisateur
	 *
	 *	@param string $classe	nom du groupe classe
	 *
	 *	@return array
	 */
	function get_usergroups($classe) {
		$this->connecter(true);
		$justthese = array("cn", "displayname", "samaccountname", "userprincipalname", "logoncount");
		$resultat = array();
		
		// liste les eleves dans une uo
		$lsclass = ldap_list($this->ds, 
			"OU=" . ldap_escape($classe, '*', LDAP_ESCAPE_FILTER). ',' . $this->ldap_ou_elv, 
			"(&(objectCategory=person)(objectClass=user)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))", 
			$justthese);
			
		if (!($lsclass)) { 
			echo "<p>Error:" . ldap_error($this->ds) . "</p>"; 
			echo "<p>Error:" . ldap_err2str(ldap_errno($this->ds)) . "</p>"; 
			die;
		}
		
		$info = ldap_get_entries($this->ds, $lsclass);
		for ($i=0; $i < $info["count"]; $i++) {
			$logoncount = (isset ($info[$i]["logoncount"]))? $info[$i]["logoncount"][0] : 0;
			$resultat[] = array(
				'NomComplet' => $info[$i]["displayname"][0],
				'Identifiant' => $info[$i]["samaccountname"][0],
				'Compte365' => $info[$i]["userprincipalname"][0],
				'logoncount' => $logoncount);
		}
		sort($resultat);
		return $resultat;
	}
	

	/**
	 * Recherche un ou des utilisateurs
	 *
	 *	@param string $cherche	utilisateur a rechercher
	 *
	 *	@return array
	 */
	function find_users($cherche) {
		$this->connecter(true);
		$justthese = array("cn", "displayname", "samaccountname", "userprincipalname", "logoncount", "distinguishedname");
		$resultat = array();
		
		if ($cherche == '') {
			// champ recherche vide ?
			return $resultat;
		}
		
		// liste les eleves dans une uo
		$lsclass = ldap_search($this->ds, 
			$this->ldap_ou_elv, 
			"(displayname=*$cherche*)", 
			$justthese);
			
		if (!($lsclass)) { 
    echo "<p>Error:" . ldap_error($this->ds) . "</p>"; 
    echo "<p>Error:" . ldap_err2str(ldap_errno($this->ds)) . "</p>"; 
    die;
} 	
		$info = ldap_get_entries($this->ds, $lsclass);
		for ($i=0; $i < $info["count"]; $i++) {
			$logoncount = (isset ($info[$i]["logoncount"]))? $info[$i]["logoncount"][0] : 0;
			$classe = str_replace( ',' . $this->ldap_ou_elv, '', $info[$i]["distinguishedname"][0]);
			
			$classe = substr($classe, strlen($info[$i]["samaccountname"][0]) + 7);
			$resultat[] = array(
				'NomComplet' => $info[$i]["displayname"][0],
				'Classe' => $classe,
				'Identifiant' => $info[$i]["samaccountname"][0],
				'logoncount' => $logoncount);
		}
		sort($resultat);
		return $resultat;
	}
	
	/**
	 * Retourne les classes non vides
	 *
	 *
	 *	@return array
	 */
	function get_classes(){
		$this->connecter(true);
		$justthese = array("ou", "cn");
		$resultat = array();
		
		// liste les OU dans eleves
		$lsclass = ldap_search($this->ds, $this->ldap_ou_elv, "(objectClass=organizationalUnit)", $justthese);
		$info = ldap_get_entries($this->ds, $lsclass);
		// parcours les OU, compte les membres actifs pour eliminer les OU vides
		for ($i=0; $i < $info["count"]; $i++) {
			$lsmemb = ldap_list($this->ds, 
				$info[$i]['dn'], 
				"(&(objectCategory=person)(objectClass=user)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))", 
				$justthese);
			if (ldap_count_entries($this->ds, $lsmemb) > 0) {
				$resultat[] = $info[$i]["ou"][0];
			}
		}
		sort($resultat);
		return $resultat;
	}
}