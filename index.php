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

$pageid = 'dashboard';
$expired = false;

// creation session variables vides
if ( ! isset( $_SESSION['user_pass'] ) ) {
	$_SESSION['user_id'] = '';
	$_SESSION['user_pass'] = '';
}

//autologout
if (isset($_SESSION['timestamp'])) {
	if ( time() - $_SESSION['timestamp'] > $_CONF['idletime'] ){
		session_destroy();
		session_unset();
		$expired = true;
		header("Location: index.php?sessionexpired");
	} else {
		$_SESSION['timestamp']=time();
	}
}


if ( ! empty( $_GET ) ) {
	if ( isset( $_GET['logout'] )) {	// deconnecte l'utilisateur
		session_unset();
		session_destroy();
		header("Location: index.php");
	}
	if ( isset( $_GET['pg'] )) {	// extrait la page demandée
		$pageid = htmlspecialchars($_GET['pg']);
	}
	if ( isset( $_GET['sessionexpired'] )) {
		$expired = true;
	}
}

if ( ! empty( $_POST ) ) {
    if ( isset( $_POST['username'] ) && isset( $_POST['password'] ) ) {
		// quelqu'un essaie de se connecter...
		$ldap = new AnnuaireLDAP( $_CONF['AD_ServerIP'], "", "", $_CONF['AD_OU_ELEVES'] );
		$is_auth = $ldap->authentifier($_POST['username'], $_POST['password']);
		if ($is_auth != false) {
			$userinfo = $ldap->get_users_info($_POST['username']);
			$_SESSION['user_id'] =$_POST['username'];
			$_SESSION['user_pass'] =$_POST['password'];
			$_SESSION['user_name'] = $userinfo['cn'];
			$_SESSION['Compte365'] = $userinfo['Compte365'];
			$_SESSION['timestamp'] = time();
    	} else {
			if (isset( $_SESSION['failed_count'] )) {
				$_SESSION['failed_count'] += 1;
			} else {
				$_SESSION['failed_count'] = 1;
			}
		}
    }
}

$ldap = new AnnuaireLDAP( $_CONF['AD_ServerIP'], "{$_CONF['AD_Domain']}\\{$_SESSION['user_id']}", $_SESSION['user_pass'], $_CONF['AD_OU_ELEVES'] );

?>
<!DOCTYPE html>
<html dir="ltr" lang="fr">

<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php if (!isset( $_GET['nobootstrap'] )) { ?>
    <!-- Bootstrap core CSS -->
<link href="dist/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

	<link href="./inc/style-base.css" rel="stylesheet" type="text/css">
	<script src="inc/func_PetitCafeChaud.js"></script>

	<?php } // si nobootstrap ?>
	<title>Mot de passe IACA</title>
</head>
<?php
// si un utilisateur est connecté
if ( isset( $_SESSION['user_id'] ) && ! empty( $_SESSION['user_id'] ) ) {
    // affiche la page.
?>
<body>

<?php if ( $pageid != 'etiquettes' ) { // pas de navbar sur la page etiquettes ?>
<nav class="navbar navbar-expand-md navbar-dark bg-dark mb-4 d-print-none">
  <div class="container-fluid">
    <span>
		<a class=" navbar-brand" href=".">
			<img src="pass-blk.jpg" width="33" height="24">
			&nbsp;Comptes Iaca
		</a>
		<a class="btn btn-outline-success" href=".">Liste des Classes</a>

	</span>
	<form>
		<input type="text" class="form-control" id="floatingsearch" placeholder="🔎 Rechercher un élève..." name="q">
		<input type="hidden" id="floatingsearch" value="cherche" name="pg">
	</form>
	<div class="dropdown">
	  <a class="btn btn-secondary dropdown-toggle" href="#"
		role="button" id="dropdownMenuLink" data-bs-toggle="dropdown">
		<?php echo $_SESSION['user_name']; ?>
	  </a>
	  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuLink">
		<?php if (PuisJe( 'AfficherMenuProfilProf' )) { ?>
		<li><a class="dropdown-item" href="?pg=profil">Changer votre mot de passe</a></li>
		<?php } ?>
		<?php if (PuisJe( 'AfficherMenuAide' )) { ?>
		<li><a class="dropdown-item" href="?pg=aide">Aide</a></li>
		<?php } ?>
		<li><a class="dropdown-item" href="?logout">Déconnexion</a></li>
	  </ul>
	</div>
</nav>

<?php } // si pageid not etiquettes ?>

		<div id="contenu">
<?php
if ( file_exists("inc/page_". $pageid .".php") ) {
	include("inc/page_". $pageid .".php");
} else {
	include("inc/page_dashboard.php");
}
?>
		</div>

</body>
	<script src="dist/bootstrap.min.js"></script>
</html>
<?php
} else { // is connecter
    // affiche la page de connexion
?>
  <body class="text-center">

<main class="form-signin">
  <form action="." method="POST">
    <img class="mt-5 mb-3" src="./pass.jpg" alt="" width="72" height="57">
    <h3 class="h4 mb-3 fw-normal">Gestion des mots de passe</h3>
    <h1 class="h3 mb-3 fw-normal">Identification de l'enseignant</h1>
<?php
if ($expired) {
	echo '<div class="alert alert-warning">info: votre session a expirée.</div>';
}
if (isset($is_auth)) {
	echo '<div class="alert alert-warning">Erreur: authentification invalide.</div>';
}
if ( isset( $_SESSION['failed_count'] ) && $_SESSION['failed_count'] >= 3 ) {
	echo '<div class="alert alert-danger">Erreur: Nombre de tentatives dépassées.</div>';
} else {
		?>
    <div class="form-floating mb-3">
      <input type="text" class="form-control" id="floatingInput" placeholder="Identifiant" name="username" required>
      <label for="floatingInput"> Utilisateur (Identifiant de session IACA)</label>
    </div>
    <div class="form-floating mb-3">
      <input type="password" class="form-control" id="floatingPassword" placeholder="Mot de passe" name="password" required>
      <label for="floatingPassword">Mot de passe</label>
    </div>
    <button class="w-100 btn btn-lg btn-success" type="submit">Connexion</button>
    <p class="mt-5 mb-3 text-muted">Informatique Mandela &copy; 2021–2185, version 0.1</p>
	<?php
} // else fail count
?>
  </form>
</main>
</body>
</html>
<?php
} // else is connecter
