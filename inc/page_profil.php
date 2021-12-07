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

if (! PuisJe( 'AfficherMenuProfilProf' )) { die('Non autorisé.'); }
?>

<div class="container-sm w-50">
<?php
if( isset( $_POST['NouveauMDP'] ) && ! empty( $_POST['NouveauMDP'] )) {
	if( iaca_setmdp($_SESSION['user_name'], $_POST['NouveauMDP']) == 'OK' ) {
		echo "<div class='alert alert-warning'>Votre mot de passe a été changé.</div>";
	} else {
		echo "<div class='alert alert-danger'>
			Une erreur est survenue. ". strlen($_POST['NouveauMDP']) ."
			 marmottes sont parties enquêter.
			Attendez qu'elles reviennent, ou réessayez plus tard.</div>";
	}
}

?>
<h3>Compte de <?php echo $_SESSION['user_name']; ?> </h3><br />
<form method="POST">
  <div class="">
    <label for="uid" class="form-label">Votre identifiant IACA</label>
	<input type="text" class="form-control"
		id="uid" name="Identifiant"
		value="<?php echo $_SESSION['user_id']; ?>" $entry['Compte365']
		disabled readonly>
    <div id="uidHelp" class="form-text">
		Le Conseil Régional a souscrit un contrat de licence vous permettant
		d'utiliser Microsoft Office 365. <br />
		Pour cela, utilisez votre identifiant IACA sous sa forme complète
		<code><?php echo $_SESSION['Compte365']; ?></code>
		directement sur le site office.com
	</div>
  </div>
  <br />
  <div class="">
    <label for="set" class="form-label">Entrez votre nouveau mot de passe</label>
    <input type="password" class="form-control"
		id="set" name="NouveauMDP"
		pattern=".{5,128}" autocomplete="new-password"
		oninput="updatePasswordMeterBar(this.value);">
	<meter id="pwdscore" min=0 max=100 value=1 style="width:100%"></meter><br />
	<div class="form-check form-switch form-text">
		<input type="checkbox" class="form-check-input"
			id="showpwd" onclick="togglePasswordVisibility('set')">
		<label class="form-check-label" for="showpwd">Afficher le mot de passe en clair</label>
	</div>
    <div id="setHelp" class="form-text">
		Le mot de passe doit comporter un minimum de 5 caractères. <br/>
		Il peut combiner des lettres majuscules ou minuscules,
		ainsi que des caractères spéciaux et des chiffres. Par exemple :
		<code><?php echo Creer_Pass( 8, false ); ?></code>
	</div>
  </div>
  <br />
  <button type="submit" class="btn btn-primary ">Enregistrer</button>
</form>
</div>
