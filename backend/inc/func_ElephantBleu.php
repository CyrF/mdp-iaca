<?php

/*
 * =================		SESSION PHP		=================
 */


/*
 * Initialise une session php et definit sa valeur par defaut
 * (extrait de la doc php)
 *
 * @return null
 */
function my_session_start($idletime = 300) {
	session_start();

	if (!empty ($_SESSION['deleted_time']) &&
			$_SESSION['deleted_time'] < time() - ($idletime)) {
		//$ev->creer ('La session a expirée.', E_NOTICE);
		my_session_destroy();
		session_start();
		$_SESSION['page_par_defaut'] = (isset($_SESSION['est_connecter'])) ? 'timeout' : 'login';
		unset ($_SESSION['deleted_time']);
		unset ($_SESSION['acces']);
		unset ($_SESSION['nom_utilisateur']);
		unset ($_SESSION['est_connecter']);
	}

	$_SESSION['deleted_time'] = time();
	$_SESSION['acces'] = ($_SESSION['acces']) ?? 0;
	$_SESSION['page_par_defaut'] = ($_SESSION['page_par_defaut']) ?? false;
	$_SESSION['user_name'] = ($_SESSION['user_name']) ?? false;
	$_SESSION['user_id'] = ($_SESSION['user_id']) ?? false;
	$_SESSION['user_pass'] = ($_SESSION['user_pass']) ?? false;
	$_SESSION['acces'] = ($_SESSION['acces']) ?? 0;
}


/*
 * supprime aussi les cookies lors de la suppression de la session
 * (extrait de la doc php)
 *
 * @return null
 */
function my_session_destroy() {
	$params = session_get_cookie_params();
  setcookie (session_name(), '', time() - 42000,
    $params["path"], $params["domain"],
    $params["secure"], $params["httponly"]
  );
	session_destroy();
}

/*
 * =================		DIVERS		=================
 */

/*
 * retourne la page demandée dans l'url, ou celle definie par defaut
 *
 * @return string
 */
function get_page_courante() {
	$pg = ($_SESSION['page_par_defaut'] == 'timeout') ? array ('timeout') : array_keys ($_GET, '');
	$est_connecter = (!empty ($pg)) ? true : isset ( $_SESSION['est_connecter']);
	return (!empty ($pg) && $est_connecter) ? $pg[0] : $_SESSION['page_par_defaut'] ;
}


/**
 * Renvoie le type de compte correspondant au niveau d'acl
 *
 *	@param int $lvl			valeur stockée dans $_SESSION['acces']
 *
 *	@return string
 */
function get_userlevel($lvl)
{
    //convertit le niveau numerique en nom
    $consts = get_defined_constants(true)['user'];
    if (is_array($consts))
	{
        $consts_inv = array_flip($consts);
        return $consts_inv[$lvl] ?? $lvl;
    }
    return $lvl;
}


/**
 * Cree un mot de passe aleatoire
 *
 *	@param int $n			longueur du mot de passe
 *	@param bool $simple		ajoute ou pas des caractères spéciaux
 *
 *	@return string
 */
function Creer_Pass( $n=5, $simple=true ) {
	$listeChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	$listeChars .= '0123456789';
	if (!$simple) {
		$listeChars .= '~!@#$%&*_-+:;>,.';
		//$listeChars .= 'ÀàÂâÉéÈèÊêËëÎîÏïÔôÙùÛûÜüÇç';
	}
	return substr(str_shuffle(str_repeat($listeChars,$n)),0,$n);
}


/**
 * convertit en b64 sans les egals a la fin
 *
 *	@param string $txt	texte a convertir
 *
 *	@return string
 */
function b64( $txt ) {
	return str_replace('=', '', base64_encode($txt));
}


$Config = new Config();
//echo Config::get('Domaine', 'indéfini'); //marche pas (pas initialsé)
//echo $Config->get('Domaine', 'indéfini'); //marche pas (pas initialsé)
//var_export($Config->list_params_ad());

class Config {
	private $fichier_defaut = null;		// chemin vers le fichier listant les valeurs par default
	private $fichier_user = null;			// chemin vers les valeurs modifiées par l'admin
	public $ini = null;								// tableau de parametres par default
	public $config_user = null;				// tableau des valeurs personnalisées

	function __construct() {
		$this->fichier_defaut = __DIR__ . '/config.ini';
		$this->fichier_user = '/logs/user_config.php';

		$this->ini = parse_ini_file ($this->fichier_defaut, true, INI_SCANNER_TYPED);

		if (file_exists ($this->fichier_user)) {
			$donnee_brute = file_get_contents ($this->fichier_user);
			$this->config_user = unserialize (substr ($donnee_brute, 15));
		}
	}

	/*
	 * renvoie la 1ere valeur d'une clef trouvée suivant l'ordre user -> global -> defaut
	 *
	 * @param string $cle  		nom du parametre a chercher
	 * @param mixed $defaut  valeur a renvoyer si rien trouver
	 *
	 * @return mixed
	 */
	static function get ($cle, $defaut = null) {
			$config_user = &$GLOBALS['Config']->config_user;
			$ini = &$GLOBALS['Config']->ini;
			$k = explode ('_', $cle, 2);
			return ($config_user[$cle]) ?? (($ini[$k[0]][$k[1]]) ?? $defaut);
	}

	/*
	 * stocke la valeur d'une clef dans le tableau user
	 *
	 * @param string $cle
	 * @param mixed $valeur
	 *
	 * @return bool
	 */
	function set ($cle, $valeur) {
		if (is_array ($this->get ($cle))) {
			$valeur = explode ("\n", str_replace("\r\n","\n", $valeur));

			if (count (array_diff ($this->get ($cle), $valeur)) > 0) {
				$this->config_user[$cle] = $valeur;
				return true;
			}
		} else {
			if ($this->get ($cle) != $valeur) {
				$this->config_user[$cle] = $valeur;
				return true;
			}
		}
		return false;
	}

	/*
	 * enregistre le tableau user dans un fichier
	 *
	 * @return null
	 */
	function save_config() {
		// pour ne pas etre accessible depuis un navigateur
		$donnee_protegee = '<?php die(); //' . serialize ($this->config_user);
		file_put_contents ($this->fichier_user, $donnee_protegee);
	}

	/*
	 * verifie si la conf autorise l'action demandée
   *
	 *	@param string $FaireCa	action demandée
	 *
	 * @return bool
	 */
	function PuisJe ($FaireCa) {
			$acces = $this->get ('ACL_' . $FaireCa);
			return (defined ($acces) && $_SESSION['acces'] >= constant ($acces));
	}

	/*
	 * renvoie la liste des ACLs pour le template
	 *
	 * @return object
	 */
	function list_params_ad() {
		$ad = array();
		$ad['install_ok'] = (is_array ($this->config_user));
		foreach (array_keys ($this->ini['AD']) as $param_cle) {
			$ad[$param_cle] = $this->get ('AD_' . $param_cle);
		}
		return (object) $ad;
	}

	/*
	 * renvoie la liste des ACLs pour le template
	 *
	 * @return array
	 */
	function list_acl() {
		$acls = array();
		foreach (array_keys ($this->ini['ACL']) as $droit) {
			if ($this->PuisJe ($droit)) {
				$acls[$droit] = true;
			}
		}
		return $acls;
	}

	/*
	 * renvoie la liste des parametres pour le template
	 *
	 * @return array
	 */
	function list_params() {
		$params = array();
		$comm = array();
		$section = '';
		$old_k = null;
		$lines = file ($this->fichier_defaut, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		foreach ($lines as $line_num => $line) {
			if ($line[0] == ';') {
				// ligne de commentaire
				$comm[] = ltrim ($line, '; ');
			} elseif ($line[0] == '[') {
				// debut de section
				$section = trim ($line, '[]');
				$params[] = array (
					'DESCRIPTION'	=> implode ('<br />', $comm),
					'SECTION'			=> $section,
				);
				$comm = array();
			} else {
				// un paramètre
				$k = trim (substr ($line, 0, strpos ($line, '=')));
				$k = $section . '_' . rtrim ($k, '[]');
				$v = $this->get ($k);
				if ($k != $old_k) {
					$params[] = array (
						'DESCRIPTION'	=> implode ('<br />', $comm),
						'CLEF'				=> $k,
						'VALEUR'			=> (is_array ($v)) ? implode ("\n", $v) : $v,
						'MULTILIGNE'	=> is_array ($v),
					);
					$comm = array();
					$old_k = $k;
				}
			}
		}
		return $params;
	}
}
/*
 * =================		COMMUNICATION AVEC IACA / AD		=================
 */


/**
 * demande a iaca de changer le mdp
 *
 *	@param string $utilisateur	nom d'utilisateur dans l'AD
 *	@param string $mdp			nouveau mdp a changer
 *
 *	@return string				"OK" | "Erreur"
 */
function iaca_setmdp ($utilisateur, $mdp) {
	if (Config::get ('Global_mode') == 'fake') { return 'OK'; }
	$REPONSE = "";
	$fp = fsockopen (Config::get ('AD_ServerIP'), 5016, $numerr, $strerr, 1);
	if ($fp) {
		fputs ($fp,"NU=$utilisateur|MDP=$mdp");
		$REPONSE = fgets ($fp,1500);
	}
	fclose ($fp);
	if (strchr ($REPONSE, "MDP_OK")) {
		return "OK";
	} else {
		return "Une erreur est survenue. ($REPONSE)";
   // 2 : non trouvé ; 5 : non autorisé
   // 12 : accès invalide ; 87 : paramètre incorrect
   // 1325 : mot de passe trop simple. Refusé par AD
	}
}


/**
 * demande a iaca de masquer le mdp
 *
 *	@param string $utilisateur	nom d'utilisateur dans l'AD
 *
 *	@return string				"OK" | "Erreur"
 */
function iaca_hidemdp ($utilisateur) {
	if (Config::get ('Global_mode') == 'fake') { return 'OK'; }
	$REPONSE = "";
	$fp = stream_socket_client (
		'tcp://' . Config::get ('AD_ServerIP') . ':5016',
		$numerr, $strerr, 1
	);
	if ($fp) {
		fwrite ($fp, "NU=$utilisateur|MDP=**************");
		stream_set_timeout ($fp, 2);
		$REPONSE = stream_get_contents ($fp);
	}
	fclose ($fp);
	if (strchr ($REPONSE, "MDP_OK")) {
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
function iaca_getmdp ($utilisateur) {
	if (Config::get ('Global_mode') == 'fake') { return Creer_Pass (8); }
	$REPONSE = "";
	$fp = fsockopen (Config::get ('AD_ServerIP'), 5016, $numerr, $strerr, 1);
	if ($fp) {
		fputs ($fp, "NU=$utilisateur|GETMDP");
		$REPONSE = fgets($fp, 64);
	}
	fclose ($fp);
	// supprime l'entete recue pour retourner que le mdp
	return trim (substr ($REPONSE, strlen ($utilisateur) + 11));
}


/**
 * Force un utilisateur à modifier son mot de passe à la prochaine ouverture de session
 *
 *	@param string $utilisateur	nom d'utilisateur dans l'AD
 *
 *	@return null
 */
function ldap_mdptemporaire ($utilisateur) {
	if (Config::get ('Global_mode') == 'fake') { return; }
	$ldap = new AnnuaireLDAP (
		Config::get ('AD_Domain') . '\\' . Config::get ('AD_UserGest'),
		Config::get ('AD_PassGest')
	);
	$ldap->set_UserMustChangePassword ($utilisateur);
}
