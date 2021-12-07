<?php

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


/**
 * verifie si la conf autorise l'action demandée
 *
 *	@param string $FaireCa	action demandée
 *
 *	@return bool
 */
function PuisJe( $FaireCa ) {
	global $_CONF;
	if( isset( $_CONF['action_autorisee'] )) {
		if( in_array( $FaireCa, $_CONF['action_autorisee'] )) {
			return true;
		}
	}
	return false;
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
		fwrite($fp,"NU=$utilisateur|MDP=**************");
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
 * Force un utilisateur à modifier son mot de passe à la prochaine ouverture de session
 *
 *	@param string $utilisateur	nom d'utilisateur dans l'AD
 *
 *	@return null
 */
function ldap_mdptemporaire($utilisateur) {
	global $_CONF;
	if( $_CONF['mode'] == 'fake' ) { return; }
	$ldap = new AnnuaireLDAP(
		$_CONF['AD_ServerIP'],
		"{$_CONF['AD_Domain']}\\{$_CONF['AD_UserGest']}",
		$_CONF['AD_PassGest'],
		$_CONF['AD_OU_ELEVES'] );
	$ldap->set_UserMustChangePassword($utilisateur);
}
