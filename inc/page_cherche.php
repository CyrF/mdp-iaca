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

$cherche = $_GET['q'];

include("inc/form_motdepasseeleve.php");
?>

<div class="container navbar">
  <div class="container-fluid d-print-none">
	<h3>Recherche : <?php echo $cherche; ?></h3>
	<span>
		<span class="btn btn-outline-primary
			<?php if (! PuisJe( 'AfficherBoutonVoirTous' )) { echo 'd-none'; }?>"
			onclick='RevelerTous(this.id);' id="btnshow">Afficher les MdP</span>
	</span>
  </div>

<table class="table table-striped table-hover">
<thead class="table-dark">
<tr>
	<th></th>
	<th style="text-align:center;">Classe</th>
	<th>Identifiant IACA</th>
	<th>Mot de passe</th>
</tr>
</thead>

<?php
$list = $ldap->find_users( $cherche );
sort($list);
foreach ($list as $entry) {
	$b64_uid = b64($entry['Identifiant']);
	$b64_name = b64(rawurlencode($entry['NomComplet']));
	echo '<tr>
	<td>' . $entry['NomComplet'] .'</td>
	<td style="text-align:center;">
	  <a href="?pg=classe&id='. b64($entry['Classe']) .'"
	    class="btn btn-outline-success"
	    style=width:100%;>' . $entry['Classe'] .'</a></td>
	<td>' . $entry['Identifiant'] .'</td>
	<td>';
	if ($entry['logoncount'] == 0) {
		// pas encore logué, donc le mdp est provisoire
		echo "<span type='button' class='btn btn-outline-success btn-sm d-print-none'
				id='pwd_{$b64_uid}' onclick='ajax_getMdp(this.id);'
				data-bs-target='#MotdePasseModal' name='motdepasse'
				data-bs-uid='{$b64_uid}' data-bs-name='{$b64_name}'>
					Le compte n'a pas été utilisé : voir le mdp
			</span>";
	} else {
		// le compte a servi, donc le mdp a été personnalisé
		echo "<span type='button' class='btn btn-outline-success btn-sm d-print-none'
				id='pwd_{$b64_uid}' onclick='ajax_getMdp(this.id);'
				data-bs-toggle='modal' data-bs-target='#MotdePasseModal'
				data-bs-uid='{$b64_uid}' data-bs-name='{$b64_name}'>
					Réinitialiser avec un nouveau mot de passe
			</span>";
	}
	if ($entry['pwdLastSet'] == 0) {
		echo "&nbsp;<span type='button' class='btn btn-outline-secondary btn-sm d-print-none disabled'>
					Temporaire
			</span>";
	}

	echo '</td>
</tr>';
};

?>
</table><img src="./runcat2.gif" style="visibility:hidden;">
</div>
