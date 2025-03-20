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



/*
 * ================= CHARGE LA CONF DEPUIS L'ENVIRONMENT DOCKER	=================
 */


/*
* renvoie la liste des parametres AD, formatté pour la classe ldap
*
* @return object
*/
function list_params_ad() {
	$ad = array();
	$ad['install_ok'] = TRUE;
	$ad['ServerIP'] = getenv("AD_ServerIP");
	$ad['Domain'] = getenv("AD_Domain");
	$ad['Chemin'] = getenv("AD_Chemin");
	$ad['OU_ELEVES'] = getenv("AD_OU_ELEVES");
	$ad['OU_PROF'] = explode("|", getenv("AD_OU_PROF"));
	$ad['OU_EXAMEN'] = getenv("AD_OU_EXAMEN");
	$ad['OU_GURU'] = array(getenv("AD_OU_GURU"));
	$ad['Grp_Cache'] = explode("|", getenv("AD_Grp_Cache"));

	return (object) $ad;
}


/*
* verifie si la conf autorise l'action demandée
*
*	@param string $FaireCa	action demandée
*
* @return bool
*/
function PuisJe ($FaireCa) {
	$acces = getenv('ACL_' . $FaireCa);
	return (defined ($acces) && $_SESSION['acces'] >= constant ($acces));
}


/*
* renvoie la liste des ACLs pour le template
*
* @return array
*/
function list_acl() {
	$acls = array();
	foreach (array_keys (getenv()) as $droit) {	
		if (substr($droit, 0, 4) == "ACL_" && PuisJe (substr($droit, 4))) {
			$acls[substr($droit, 4)] = true;
		}
	}
	return $acls;
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
	if (getenv('DEPLOYMENT_MODE') == 'fake') { return 'OK'; }
	$REPONSE = "";
	$fp = fsockopen (getenv ('AD_ServerIP'), 5016, $numerr, $strerr, 1);
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
	if (getenv('DEPLOYMENT_MODE') == 'fake') { return 'OK'; }
	$REPONSE = "";
	$fp = stream_socket_client (
		'tcp://' . getenv('AD_ServerIP') . ':5016',
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
	if (getenv('DEPLOYMENT_MODE') == 'fake') { return Creer_Pass (8); }
	$REPONSE = "";
	$fp = fsockopen (getenv('AD_ServerIP'), 5016, $numerr, $strerr, 1);
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
	if (getenv('DEPLOYMENT_MODE') == 'fake') { return; }
	$ldap = new AnnuaireLDAP (
		getenv('AD_Domain') . '\\' . getenv('AD_UserGest'),
		rtrim(file_get_contents( getenv('AD_PassGest_FILE'))),
		list_params_ad() // bugfix ArgumentCountError Too few arguments
	);
	$ldap->set_UserMustChangePassword ($utilisateur);
}
