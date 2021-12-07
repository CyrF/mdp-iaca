<?php
/**
 * Copyright (C) 2021.
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

if (! PuisJe( 'AfficherMenuAide' )) { die('Non autorisé.'); }
?>

<div class="container-sm w-75 overflow-hidden">

<h3>Actions disponibles sur un compte élève</h3>

<div class="row align-items-center gx-4">
	<span class="col-4 text-center">
		<span type='button' class='btn btn-outline-success btn-sm'>
			Le compte n'a pas été utilisé : voir le mdp
		</span>
	</span>
	<span class="col form-text">
		Le compteur indique que personne ne s'est connecté avec ce compte.
		<br />Le bouton propose d'afficher le mot de passe en clair,
		à condition qu'il ne soit pas déjà chiffré sur le serveur.
	</span>
</div><br />

<div class="row align-items-center gx-4">
	<span class="col-4 text-center">
		<span type='button' class='btn btn-outline-success btn-sm'>
			Réinitialiser avec un nouveau mot de passe
		</span>
	</span>
	<span class="col form-text">
		Ce compte est actif. <br />
		Le mot de passe actuel n'est pas consultable,
		la seule action disponible est donc de le changer.
	</span>
</div><br />

<div class="row align-items-center gx-4">
	<span class="col-4 text-center">
		<span type='button' class='btn btn-outline-success btn-sm'>
			Voir le mot de passe
		</span>
	</span>
	<span class="col form-text">
		Le bouton propose d'afficher le mot de passe en clair,
		à condition qu'il ne soit pas déjà chiffré sur le serveur.
	</span>
</div><br />

<div class="row align-items-center gx-4">
	<span class="col-4 text-center">
		<span type='button' class='btn btn-outline-secondary btn-sm'>
			Temporaire, Cliquer pour masquer
		</span>
	</span>
	<span class="col form-text">
		Le mot de passe vient d'être réinitialisé,
		et Windows demandera a le changer à la prochaine ouverture de session.
		<br />Le bouton propose de masquer le mot de passe en clair.
	</span>
</div><br />

<div class="row align-items-center gx-4">
	<span class="col-4 text-center">
		<span type='button' class='btn btn-outline-secondary btn-sm disabled'>
			Temporaire
		</span>
	</span>
	<span class="col form-text">
		Indique que Windows demandera a changer le mot de passe
		à la prochaine ouverture de session.
	</span>
</div><br />

<div class="row align-items-center gx-4">
	<span class="col-4 text-center">
		<img src="./runcat2.gif" width="76" height="32">
	</span>
	<span class="col form-text">
		L'action demandée est en cours de traitement par le serveur.
	</span>
</div><br />
<h3>Actions disponibles sur une classe</h3>

<?php if ( PuisJe( 'AfficherBoutonVoirTous' )) { ?>
<div class="row align-items-center gx-4">
	<span class="col-4 text-center">
		<span type='button' class='btn btn-outline-primary btn-sm'>
			Afficher les MdP
		</span>
	</span>
	<span class="col form-text">
		Le bouton propose d'afficher en clair tous les mots de passe non actif
		de la page en cours.
	</span>
</div><br />

<div class="row align-items-center gx-4">
	<span class="col-4 text-center">
		<span type='button' class='btn btn-outline-primary btn-sm'>
			Masquer tous
		</span>
	</span>
	<span class="col form-text">
		Le bouton propose de masquer tous les mots de passe en clair
		sur la page en cours.
	</span>
</div><br />
<?php } ?>

<?php if ( PuisJe( 'AfficherBoutonImprimer' )) { ?>
<div class="row align-items-center gx-4">
	<span class="col-4 text-center">
		<span type='button' class='btn btn-outline-primary btn-sm'>
			 🖨️
		</span>
	</span>
	<span class="col form-text">
		Le bouton permets d'accéder à une page d'étiquettes imprimables.
	</span>
</div><br />
<?php } ?>

