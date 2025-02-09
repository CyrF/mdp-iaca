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

if (! extension_loaded ('ldap')) {
	die ('L\'extension LDAP n\'est pas installé/chargé.');
}

/**
 * Classe pour gerer un annuaire ldap
 *
 */
class AnnuaireLDAP {
	protected $ds;	//stocke le socket de connexion
	private $ldap;	// parametres de connexion
	private $ldap_user;
	private $ldap_pass;
	private $bound;

	function __construct($user, $pass, $Config) {
		$this->ldap			= $Config;
		$this->ldap_user	= $this->ldap->Domain . '\\' . $user;
		$this->ldap_pass	= $pass;
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
			$this->ds = ldap_connect($this->ldap->ServerIP);
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
					error_log("[MDPIACA] Error Binding($auth) to LDAP: $extended_error");
				}
				error_log("[MDPIACA] ldap_error: " . ldap_error($this->ds));
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
	function authentifier ($username, $password) {
		$this->connecter();
		$Autorized = false;

		if (! $this->ldap->install_ok ) {
			if ($username == 'admin' && $password == 'admin') {
			return GURU;
			}
			return false;
		}

		// tente une connection a l'ad...
			$bind = @ldap_bind ($this->ds,
				$this->ldap->Domain ."\\". ldap_escape ($username, '', LDAP_ESCAPE_DN),
				$password
			);
		if ($bind) {
			// recherche le chemin complet de l'user pour savoir a quelle UO il appartient.
			$res = ldap_search ($this->ds,
				$this->ldap->Chemin,
				"(sAMAccountName=". ldap_escape ($username, '', LDAP_ESCAPE_DN) . ")"
			);
			$first = ldap_first_entry ($this->ds, $res);
			$data = ldap_get_dn ($this->ds, $first);

			// compare avec la liste des UO approuvées
			if (strpos ($data, $this->ldap->OU_ELEVES) !== false) {$Autorized = ELEVE;}

			foreach ($this->ldap->OU_PROF as $auth) {
				if (strpos ($data, $auth) !== false) {$Autorized = PROF;}
			}

			foreach ($this->ldap->OU_GURU as $auth) {
				if (strpos ($data, $auth) !== false) {$Autorized = GURU;}
			}
			
			error_log("[MDPIACA] Authenticated as: $data");
			return $Autorized;
		} else {
			if (ldap_get_option($this->ds, LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error)) {
				error_log("[MDPIACA] Error authentify to LDAP: $extended_error");
			}
			error_log("[MDPIACA] ldap_error: " . ldap_error($this->ds));		
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
	function get_users_info ($uid='*'){
		$this->connecter();
		$res = ldap_search ($this->ds,
			$this->ldap->Chemin,
			"(sAMAccountName=". ldap_escape($uid, '', LDAP_ESCAPE_DN).")"
		);
		$first = ldap_first_entry ($this->ds, $res);
		return array (
			'cn' => ldap_get_values ($this->ds, $first, "displayname")[0],
			'Compte365' => ldap_get_values ($this->ds, $first, "userprincipalname")[0],
			'uid' => ldap_get_values ($this->ds, $first, "samaccountname")[0],
		);
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
		$justthese = array("cn", "displayname", "samaccountname", "userprincipalname", "logoncount", "pwdLastSet");
		$resultat = array();

		// liste les eleves dans une uo
		$lsclass = ldap_list($this->ds,
			"OU=" . ldap_escape($classe, '*', LDAP_ESCAPE_FILTER). ',' . $this->ldap->OU_ELEVES,
			"(&(objectCategory=person)(objectClass=user)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))",
			$justthese);

		if (!($lsclass)) {
			error_log("[MDPIACA] error listing users:" . ldap_error($this->ds));
			error_log("[MDPIACA] error listing users:" . ldap_err2str(ldap_errno($this->ds)));
			die;
		}

		$info = ldap_get_entries($this->ds, $lsclass);
		for ($i=0; $i < $info["count"]; $i++) {
			$logoncount = (isset ($info[$i]["logoncount"]))? $info[$i]["logoncount"][0] : 0;
			$resultat[] = array(
				'NomComplet' => $info[$i]["displayname"][0],
				'Identifiant' => $info[$i]["samaccountname"][0],
				'Compte365' => $info[$i]["userprincipalname"][0],
				'pwdLastSet' => $info[$i]["pwdlastset"][0],
				'logoncount' => $logoncount);
		}
		sort($resultat);
		return $resultat;
	}

	/**
	 * Recherche un ou des utilisateurs
	 *
	 *	@param string $cherche		utilisateur a rechercher
	 *	@param bool $non_utilise		comptes non activés
	 *
	 *	@return array
	 */
	function find_users ($cherche, $non_utilise = false) {
		$this->connecter (true);
		$resultat = array();
		$justthese = array (
			"displayname",			// le nom complet de l'utilisateur
			"samaccountname",		// l'identifiant de connexion
			"logoncount",				// nombre de connexion
			"distinguishedname", // utilisé pour extraire la classe
			"pwdLastSet",				// date du dernier changement
			"lastLogon",				// date de la dernière connexion
			"whenChanged",				// date du dernier changement
			"whenCreated",				// date de création
			);

		if ($non_utilise) {
			// filtre par comptes non activés
			$filtre = "(pwdLastSet=0)";
		} else {
			// filtre par nom d'eleve
			$filtre = "(displayname=*$cherche*)";
		}
		// liste les eleves dans une uo
		$lsclass = ldap_search ($this->ds,
			$this->ldap->OU_ELEVES, // cherche dans l'uo eleves
			$filtre,		// filtre ldap
			$justthese					// liste d'attributs
		);

		if (! ($lsclass)) {
			error_log("[MDPIACA] error searching users:" . ldap_error ($this->ds));
			error_log("[MDPIACA] error searching users:" . ldap_err2str (ldap_errno ($this->ds)));
			die;
		}

		$info = ldap_get_entries ($this->ds, $lsclass);

		for ($i=0; $i < $info["count"]; $i++) {
			// compteur nombre de connexion
			$logoncount = ($info[$i]["logoncount"][0]) ?? 0;

		// extrait la classe de l'utilisateur
			$classe = $this->get_user_class (
				$info[$i]["distinguishedname"][0],
				$info[$i]["samaccountname"][0]);

			// ne retourne pas la classe si elle doit etre masquee
			if (! in_array ($classe, $this->ldap->Grp_Cache)) {
			//	var_export ($info[$i]["lastlogon"]);
				$resultat[] = array (
					'NOMCOMPLET'	=> $info[$i]["displayname"][0],
					'CLASSE'			=> $classe,
					'IDENTIFIANT'	=> $info[$i]["samaccountname"][0],
					'PWDLASTSET'	=> $info[$i]["pwdlastset"][0],
					'LOGONCOUNT'	=> $logoncount,
					'LASTCHANGE'		=> (isset ($info[$i]["lastlogon"])) ? $this->dateldap_to_fr ($info[$i]["lastlogon"]) : 0,
					'WHENCREATED'		=> $this->dateldap_to_fr ($info[$i]["whencreated"]),
				);
			}
		}
		sort ($resultat);
		return $resultat;
	}

	/**
	 * Convertit une date au format win32_ldap
	 *
	 *	@param string $date_ldap		date soit UTC zulu, soit timestamp depuis 1601
	 *
	 *	@return string
	 */
	function dateldap_to_fr ($date_ldap) {
		if (is_array ($date_ldap)) {$date_ldap = $date_ldap[0];}
		if (empty ($date_ldap)) {return false;}

		if (substr ($date_ldap, -1) == 'Z') {
			$date = DateTime::createFromFormat ('YmdHis+', $date_ldap);
			if ($date) {return $date->format('d/m/Y');}
			//var_export( DateTime::getLastErrors() );
		} else {
			return date ('d/m/Y', $date_ldap / 10000000 - 11644473600);
		}
	}
	/**
	 * extrait la classe de l'utilisateur
	 *
	 *	@param string $distinguished		chemin ldap de l'utilisateur
	 *	@param string $account		identifiant de connexion
	 *
	 *	@return string
	 */
	function get_user_class ($distinguished, $account) {
		// extrait la classe de l'utilisateur
		$classe = str_replace (',' . $this->ldap->OU_ELEVES, '', $distinguished);
		$classe = substr ($classe, strlen ($account) + 7);
		// a tester
		//$array = ldap_explode_dn ($dn, 1);
		return $classe;
	}

	/**
	 * Retourne les classes non vides
	 *
	 *
	 *	@return array
	 */
	function get_classes() {
		$this->connecter (true);
		$justthese = array ("ou", "cn");
		$resultat = array();

		// liste les OU dans eleves
		$lsclass = ldap_search ($this->ds,
			$this->ldap->OU_ELEVES,
			"(objectClass=organizationalUnit)",
			$justthese
		);
		$info = ldap_get_entries ($this->ds, $lsclass);
		// parcours les OU, compte les membres actifs pour eliminer les OU vides
		for ($i=0; $i < $info["count"]; $i++) {
			$lsmemb = ldap_list ($this->ds,
				$info[$i]['dn'],
				"(&(objectCategory=person)(objectClass=user)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))",
				$justthese
			);
			if (ldap_count_entries ($this->ds, $lsmemb) > 0) {
				// ne retourne pas la classe si elle doit etre masquee
				if (! in_array ($info[$i]["ou"][0], $this->ldap->Grp_Cache)) {
					$resultat[] = $info[$i]["ou"][0];
				}
			}
		}
		sort ($resultat);
		return $resultat;
	}

	/**
	 * Force un utilisateur à modifier son mot de passe à la prochaine ouverture de session
	 *
	 *	@param string $uid	identifiant de l'utilisateur
	 *
	 *	@return null
	 */
	function set_UserMustChangePassword ($uid) {
		global $_CONF;
		$this->connecter (true);

		// cherche le chemin ldap correspondant a l'uid
		$res = ldap_search ($this->ds,
			$this->ldap->Chemin,
			"(sAMAccountName=". ldap_escape ($uid, '', LDAP_ESCAPE_DN) . ")"
		);
		$dn = ldap_first_entry ($this->ds, $res);

		// définis l’attribut pwdLastSet sur zéro
		$attribut = array();
		$attribut["pwdLastSet"][0] = 0;

		$result = ldap_modify ($this->ds, ldap_get_dn ($this->ds, $dn), $attribut);
		ldap_close ($this->ds);
	}
}
