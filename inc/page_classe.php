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

$classe = base64_decode($_GET['id']);

include("inc/form_motdepasseeleve.php");
?>

<div class="container navbar">
  <div class="container-fluid d-print-none">
	<h3>Elèves dans la classe <?php echo $classe; ?></h3>
	<span>
	<span class="btn btn-outline-success
		<?php if (! PuisJe( 'AfficherBoutonVoirTous' )) { echo 'd-none'; }?>"
		onclick='RevelerTous(this.id);' id="btnshow">Afficher les MdP</span>
	<a class="btn btn-outline-success
		<?php if (! PuisJe( 'AfficherBoutonImprimer' )) { echo 'd-none'; }?>"
		href="?nobootstrap&pg=etiquettes&id=<?php echo $_GET['id']; ?>"> 🖨️ </a>
	</span>
  </div>

<table class="table table-striped table-hover">
<thead class="table-dark d-print-none">
<tr class="vignet-tr">
	<th></th>
	<th>Identifiant IACA</th>
	<th></th>
	<th>Mot de passe</th>
</tr>
</thead>

<?php
$list = $ldap->get_usergroups( $classe );
sort($list);
foreach ($list as $entry) {
	$b64_uid = b64($entry['Identifiant']);
	$b64_name = b64(rawurlencode($entry['NomComplet']));
	echo '<tr class="vignet-tr">
	<td class="vignet-td">' . $entry['NomComplet'] .'<span class="d-none d-print-inline"> (' . $classe . ')</span></td>
	<td class="vignet-td"><span class="d-none d-print-inline">Identifiant réseau : </span>' . $entry['Identifiant'] .'</td>
	<td class="vignet-td"><span class="d-none d-print-inline">Id. Office365 : ' . $entry['Compte365'] .'</span></td>
	<td class="vignet-td"><span class="d-none d-print-inline">Mot de passe : </span>';
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
