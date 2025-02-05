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

require ('inc/tpl2.class.php');		// Moteur de template HTML minimaliste
require ("inc/journal.class.php");		// journalisation d'activité
include ("inc/func_ElephantBleu.php");	// session php, charge config, comm. avec iaca, divers.
include ('inc/' . getenv('DEPLOYMENT_MODE') . 'ldap.class.php');	// interroge active directory

define ('ANONYME',	0);	// niveau d'acces
define ('ELEVE',	1);
define ('PROF',		2);
define ('GURU',		4);

my_session_start(getenv('DECONNEXION_SESSION_INACTIVE'));	// definit aussi la page_par_defaut et l'autologout

$h = new Modele_HTML();	// initialise le template

// initialise le journal
$ev = new JournalEvent ('ActionUtilisateur');
$ev->set_donnee ('MachineSource', ""); //gethostbyaddr ($_SERVER['REMOTE_ADDR']));
$ev->set_donnee ('Utilisateur', (($_SESSION['user_name']) ?? 'nobody'));

// l'utilisateur s'en va
if (isset ($_GET['logout'])) {
	$ev->creer ('L\'utilisateur s\'est déconnecté.', E_NOTICE);
	my_session_destroy();
	header ("Location: " . $_SERVER['PHP_SELF']);
	exit();
}


// quelqu'un essaie de se connecter...
if (! empty ($_POST)) {
	if  ((isset ($_POST['username']) && ! empty ($_POST['username']))
		&& (isset ($_POST['password']) && ! empty ($_POST['password']))) {
	$ldap = new AnnuaireLDAP ('', '', list_params_ad());
	$ev->creer ('Tentative d\'authentification de '. $_POST['username'] . '...', E_NOTICE);
	$is_auth = $ldap->authentifier ($_POST['username'], $_POST['password']);
	if ($is_auth !== false) {
		$userinfo = $ldap->get_users_info ($_POST['username']);
		$_SESSION['user_id']			= $_POST['username'];
		$_SESSION['user_pass']			= $_POST['password'];
		$_SESSION['user_name']			= $userinfo['cn'];
		$_SESSION['Compte365']			= $userinfo['Compte365'];
		$_SESSION['timestamp']			= time();
		$_SESSION['est_connecter']		= true;
		$_SESSION['page_par_defaut']	= ($is_auth >= PROF) ? 'liste_classes' : 'profil';
		$_SESSION['acces']				= $is_auth;

		$ev->set_donnee ('Utilisateur', $_SESSION['user_name']);
		$ev->creer ($_SESSION['user_name'] . ' est connecté ('. get_userlevel($_SESSION['acces']) .').', E_NOTICE);
		} else {
			$msg_erreur_login = 'Identifiant et/ou mot de passe incorrect !';
			if (isset ($_SESSION['failed_count'])) {
				$_SESSION['failed_count'] += 1;
			} else {
				$_SESSION['failed_count'] = 1;
			}
			$ev->creer ('Erreur d\'authentification ('. $_SESSION['failed_count'] .')', E_WARNING);
		}
	}
}

// se connecte a l'ad avec les identifiants stockés
$ldap = new AnnuaireLDAP (
	$_SESSION['user_id'],
	$_SESSION['user_pass'],
	list_params_ad() // ne pas mettre de virgule, pas compatible < 7.1
	);

// donnees communes dans le template
$navigation = array (
	'TITRE' 			=> 'MdP Iaca',								// titre de la page a afficher
	'TIMEOUT'	=> getenv('DECONNEXION_SESSION_INACTIVE') + 10,
	'BASE_URL'		=> (! empty ($_SERVER['REQUEST_SCHEME'])) ? $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] : dirname ($_SERVER['PHP_SELF']),
	);
$h->add_vars (array (
	'Nom_Utilisateur'		=> $_SESSION['user_name'],	// nom affiché dans la barre de menus
	'MotdePasse_Longueur'	=> 10,	// longueur mini imposée pour valider la fenetre
	'Domaine_Office'		=> (isset ($_SESSION['Compte365'])) ? strpbrk ($_SESSION['Compte365'], '@') : false,
	'InclureJavascript'		=> $_SESSION['acces'],	// generer pwd, modifier affichage
	'InclureAjax'			=> ($_SESSION['acces'] >= PROF),	// change le mdp d'un eleve.
	'AfficherMenus'			=> ($_SESSION['acces'] >= PROF),	// les eleves ne verront pas le barre de menus.
	'URLAideCreation'		=> getenv('URLAideCreation'), // lien 'howto choose a pwd?'
	));

// liste des pages autorisées qui seront affichés dans le menu.
$h->add_vars (list_acl());

// quelle page doit etre affichee ?
$_SESSION['page_courante'] = ($_SESSION['acces']) ? (($_GET['pg']) ?? $_SESSION['page_par_defaut']) : 'login';

switch ($_SESSION['page_courante']) {
	case 'liste_classes' :
		/*
		 *	=================		TABLEAU LISTANT TOUTES LES CLASSES		=================
		 */

		if ($_SESSION['acces'] >= PROF) {
			$liste_classes = array();

			foreach ($ldap->get_classes() as $entry) {
				// certaines classes sont nommées _1FOOBAR : supprime le '_'
				$section = ($entry[0] == '_') ? $entry[1] : $entry[0];
				// fourre tout le reste dans la 4eme colonne
				$section = (strpos ('21T', $section) !== false) ? $section : 'A';

				$liste_classes[$section][] = array ('CLASSE' => $entry);
			}

			// prepare les donnees a inserer dans le template
			$h->add_vars ('classes_2nde', $liste_classes['2']);
			$h->add_vars ('classes_1ere', $liste_classes['1']);
			$h->add_vars ('classes_term', $liste_classes['T']);
			$h->add_vars ('classes_autr', $liste_classes['A']);
			$navigation['TITRE'] .= ' - Liste des classes';
		} else {
			$ev->creer ('Acces non autorisé a ' . $_SESSION['page_courante'], E_ERROR);
			exit();
		}
		break;

	case 'aide' :
		/*
		 *	=================		MENU AIDE		=================
		 */
		// aucune donnée a ajouter.
		$navigation['TITRE'] .= ' - ' . $_SESSION['page_courante'];
		break;

	case 'journal' :
		/*
		 *	=================		JOURNAL D'ACTIVITES		=================
		 */
		if (PuisJe ('AfficherMenuJournal')) {
			$ev->creer ('Acces à une page d\'administration (' . $_SESSION['page_courante'] . ')', E_PARSE);
			// affiche le journal
			$h->add_vars ('journal', $ev->get_events());
			$navigation['TITRE'] .= ' - ' . $_SESSION['page_courante'];
		} else {
			$ev->creer ('Acces non autorisé a ' . $_SESSION['page_courante'], E_ERROR);
			exit();
		}
		break;


	case 'profil' :
		/*
		 *	=================		CHANGEMENT DE MOT DE PASSE PERSO		=================
		 */
		if ($_SESSION['acces'] >= ELEVE) {
			// reception d'un mdp: mets a jour le compte
			if (isset ($_POST['NouveauMDP']) && ! empty ($_POST['NouveauMDP'])) {
				$msg_changement = iaca_setmdp ($_SESSION['user_id'], $_POST['NouveauMDP']);
				if ($msg_changement == 'OK') {
					$ev->creer ('L\'utilisateur a changé son mot de passe.', E_PARSE);
					$h->add_vars (array ('msg_changement_ok' => true));
				} else {
					$ev->creer ('Erreur "$msg_changement" en changeant son mot de passe.', E_WARNING);
					$h->add_vars (array ('msg_changement_err' => $msg_changement));
				}
			}

			// prepare les donnees a inserer dans le template
			$h->add_vars (array (
				'user_id'		=> $_SESSION['user_id'],
				'Compte365'	=> $_SESSION['Compte365'],
			));
			$navigation['TITRE'] .= ' - ' . $_SESSION['page_courante'];

		} else {
			$ev->creer ('Acces non autorisé a ' . $_SESSION['page_courante'], E_ERROR);
			exit();
		}
		break;

	case 'classe' :
		/*
		 *	=================		LISTE TOUS LES ELEVES D'UNE CLASSE	=================
		 */
		if ($_SESSION['acces'] >= PROF) {
			$classe = base64_decode ($_GET['id']);
			$list = $ldap->get_usergroups ($classe);
			// les cles doivent etre en majuscule,
			foreach ($list as $k => $v) {
				// cette function de merde ne traite les sous-array
				$list[$k] = array_change_key_case ($v, CASE_UPPER);
			}
			// prepare les donnees a inserer dans le template
			$h->add_vars (array (
				'classe_courante'	=> htmlspecialchars ($classe),
				'mdp_temporaire'	=> (getenv('CocherMDPtmp')) ? 'checked' : 'unchecked',
				));
			$h->add_vars ('eleves', $list);
			$navigation['TITRE'] .= ' - eleves de ' . $classe;
		} else {
			$ev->creer ('Acces non autorisé a ' . $_SESSION['page_courante'], E_ERROR);
			exit();
		}
		break;

	case 'cherche' :
		/*
		 *	=================		RESULTAT DE RECHERCHE PAR NOM	=================
		 */
		if ($_SESSION['acces'] >= PROF) {
			$cherche = $_GET['q'];
			$list = $ldap->find_users ($cherche);

			// prepare les donnees a inserer dans le template
			$h->add_vars (array (
				'cherche'		=> htmlspecialchars ($cherche),
				'nombre'		=> count($list),
				'mdp_temporaire'	=> (getenv('CocherMDPtmp')) ? 'checked' : 'unchecked',
				));
			$h->add_vars ('eleves', $list);
			$navigation['TITRE'] .= ' - ' . $_SESSION['page_courante'];
		} else {
			$ev->creer ('Acces non autorisé a ' . $_SESSION['page_courante'], E_ERROR);
			exit();
		}
		break;

	case 'temporaire' :
		/*
		 *	=================		LISTE DES COMPTES NON ACTIFS	=================
		 */
		if (PuisJe ('AfficherMenuListeTemporaire')) {
			$list = $ldap->find_users (null, true);

			// prepare les donnees a inserer dans le template
			$h->add_vars ('eleves', $list);
			$h->add_vars (array (
				'nombre'		=> count($list),
				));
			$navigation['TITRE'] .= ' - ' . $_SESSION['page_courante'];
		} else {
			$ev->creer ('Acces non autorisé a ' . $_SESSION['page_courante'], E_ERROR);
			exit();
		}
		break;
        
	case 'exam' :
		/*
		 *	===============		LISTE DES COMPTES EXAMENS	===============
		 */
		if (PuisJe ('AfficherMenuCompteExams')) {
			$list = $ldap->find_users (null, true);

			// prepare les donnees a inserer dans le template
			$h->add_vars ('exams', $list);
			$h->add_vars (array (
				'nombre'		=> count($list),
				));
			$navigation['TITRE'] .= ' - ' . $_SESSION['page_courante'];
		} else {
			$ev->creer ('Acces non autorisé a ' . $_SESSION['page_courante'], E_ERROR);
			exit();
		}
		break;

	default:
		/*
		 *	==============		PAGE DE LOGIN POUR IDENTIFIER L'UTILISATEUR ANONYME	=============
		 */
		if (! isset ($_SESSION['est_connecter'])) {
			if (isset ($msg_erreur_login)) {
				$h->add_vars (array (
					'msg_erreur_login' => $msg_erreur_login,
					'erreur_login' => 'erreur_connexion', // class css pour secouer la fenetre de login
					));
			}

			if (isset ($_SESSION['failed_count']) && $_SESSION['failed_count'] >= 3) {
				$h->add_vars (array (
					'msg_erreur_count' => 'Nombre de tentatives dépassées.',
					));
			}

			if ($_SESSION['page_courante'] == 'timeout') {
				$h->add_vars (array (
					'msg_erreur_login' => 'La session a expirée',
					));
			}

			$h->add_vars ('navigation', $navigation);
			$h->parser ('login');
			$h->render();
		} else {
			$ev->creer ('Acces non autorisé a ' . $_SESSION['page_courante'], E_ERROR);
			exit();
		}

		exit();
}

// affiche la page
$h->add_vars ('navigation', $navigation);
$h->parser ($_SESSION['page_courante']);
$h->render ();
