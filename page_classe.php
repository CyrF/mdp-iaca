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
?>

<!-- Modal -->
<div class="modal fade" id="MotdePasseModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
	<div class="modal-header">
        <h5 class="modal-title">Réinitialise un compte. Mais lequel?</h5>
		</div>
      <div class="modal-body">
	   <div class="form-floating mb-3">
        <input type="text" class="form-control" 
			id="floatingInput" name="identifiant" 
			value="Ha, ha, ha..." disabled readonly>
		<label for="floatingInput">Identifiant</label>
	   </div>
	   <div class="form-floating">
        <input  onfocus="generate_pwd(this, 6);" 
			type="text" class="form-control" 
			id="floatingPass" name="password"  placeholder=""
			pattern=".{5,128}"
			title="5 caractères minimum. 
Minuscules, majuscules et chiffres autorisés.
Plus les caractères spéciaux ~!@#$%&*_-+|\(){}[]:;<>,.?/">
		<label for="floatingPass">Nouveau mot de passe</label>
	   </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="setMdp();">Enregistrer</button>
      </div>
    </div>
  </div>
</div>


<div class="container">
<h3>Elèves dans la classe <?php echo $classe; ?></h3>

<table class="table table-striped table-hover">
<thead class="table-dark">
<tr>
	<th></th>
	<th>Identifiant IACA</th>
	<th>Mot de passe</th>
</tr>
</thead>

<?php
$list = $ldap->get_usergroups( $classe );
sort($list);
foreach ($list as $entry) {
	echo '<tr>
	<td>' . $entry['NomComplet'] .'</td>
	<td>' . $entry['Identifiant'] .'</td>
	<td>';
	if ($entry['logoncount'] == 0) {
		// pas encore logué, donc le mdp est provisoire
		echo "<span type='button' class='btn btn-outline-success btn-sm' 
				id='pwd_{$entry['Identifiant']}' onclick='getMdp(this.id);'
				data-bs-target='#MotdePasseModal' 
				data-bs-uid='" . str_replace('=', '', base64_encode($entry['Identifiant'])) . "' data-bs-name='{$entry['NomComplet']}'>
					Le compte n'a pas été utilisé : voir le mdp provisoire
			</span>";
	} else {
		// le compte a servi, donc le mdp a été personnalisé
		echo "<span type='button' class='btn btn-outline-success btn-sm' 
				id='pwd_{$entry['Identifiant']}' onclick='getMdp(this.id);'
				data-bs-toggle='modal' data-bs-target='#MotdePasseModal' 
				data-bs-uid='" . str_replace('=', '', base64_encode($entry['Identifiant'])) . "' data-bs-name='{$entry['NomComplet']}'>
					Réinitialiser avec un nouveau mot de passe
			</span>";
	}
	
	echo '</td>
</tr>';
};
?>
</table><img src="./runcat2.gif" style="visibility:hidden;">
</div>

<script>
/**
 * envoie une requete AJAX pour recuperer le mdp, ou le masque si deja affiché
 *
 *	@return null
 */
function getMdp( elem ) {
	var btn_pwd = document.getElementById( elem );
	if ((btn_pwd.hasAttribute("data-bs-toggle")) || (btn_pwd.disabled)) {
		// c'est un bouton reset, on fait rien.
		return;
	}
	if (btn_pwd.hasAttribute("enclair")) {
		// le mot de passe est affiché, on le masque
		btn_pwd.className = 'btn btn-outline-success btn-sm'
		btn_pwd.innerHTML = 'Voir le mot de passe';		
		btn_pwd.removeAttribute('enclair');
	} else {
		// affiche un spinner
		btn_pwd.disabled = true
		btn_pwd.className = ''
		btn_pwd.innerHTML = '<img src="./runcat2.gif" width="76" height="32">&nbsp;travail en cours...';
		// requete AJAX
		var myRequest = new Request('ajax.php?get=' + btn_pwd.getAttribute('data-bs-uid'));
		fetch(myRequest).then(function(response) {
			return response.text().then(function(text) {
				if (text == '**************') {
					// masque dans iaca, transforme le bouton en reset
					btn_pwd.disabled = false
					btn_pwd.className = 'btn btn-outline-success btn-sm'
					btn_pwd.innerHTML = 'Le mot de passe est masqué : le réinitialiser';
					btn_pwd.setAttribute('data-bs-toggle', "modal");
				} else if (text == "Votre session a expirée.") {
					window.location.replace("?sessionexpired");
				} else {
					// affichage
					btn_pwd.disabled = false
					btn_pwd.innerHTML = text;
					btn_pwd.setAttribute('enclair', true);
				}
			});
		});				
	}
}

/**
 * envoie une requete AJAX pour recuperer le mdp, ou le masque si deja affiché
 *
 *	@return null
 */
function setMdp() {
    var modalBodyId = MotdePasseModal.querySelector('.modal-body #floatingInput')
    var modalBodyPw = MotdePasseModal.querySelector('.modal-body #floatingPass')
	var btn_pwd = document.getElementById('pwd_' + modalBodyId.value);
	//check si le mdp n'est pas vide
	if ((modalBodyPw.value.length >= 5) && (!btn_pwd.disabled)) {
		// affiche un spinner
		btn_pwd.disabled = true
		btn_pwd.className = ''
		btn_pwd.removeAttribute('data-bs-toggle');
		btn_pwd.innerHTML = '<img src="./runcat2.gif" width="76" height="32">&nbsp;Travail en cours...';
		// requete AJAX
		var myRequest = new Request('ajax.php?set=' + btoa(modalBodyPw.value) + '&uid=' + btn_pwd.getAttribute('data-bs-uid'));
		fetch(myRequest).then(function(response) {
			return response.text().then(function(text) {
				if (text == 'OK') {
					// affichage
					btn_pwd.disabled = false
					btn_pwd.innerHTML = modalBodyPw.value;	
					btn_pwd.setAttribute('enclair', true);
				} else if (text == "Votre session a expirée.") {
					window.location.replace("?sessionexpired");
				} else {
					btn_pwd.disabled = false
					btn_pwd.className = 'btn btn-outline-danger btn-sm'
					btn_pwd.innerHTML = 'Erreur. Réinitialiser avec un nouveau mot de passe. ';
					btn_pwd.setAttribute('data-bs-toggle', "modal");					
				}
			});
		});				
	}
}

/**
 * Genere un mot de passe de 8 char.
 *
 *	@return null
 */
function generate_pwd(input, length) {
	wishlist = "23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz";
	if (input.value == '') {
	input.value = Array(length)
      .fill('') // fill an empty will reduce memory usage
      .map(() => wishlist[Math.floor(crypto.getRandomValues(new Uint32Array(1))[0] / (0xffffffff + 1) * wishlist.length)])
      .join('');
	}
	input.select();
}

var MotdePasseModal = document.getElementById('MotdePasseModal')
MotdePasseModal.addEventListener('show.bs.modal', function (event) {
  // Button that triggered the modal
  var button = event.relatedTarget
  // Extract info from data-bs-* attributes
  var NomComplet = button.getAttribute('data-bs-name')
  var Identifiant = button.getAttribute('data-bs-uid')
  // If necessary, you could initiate an AJAX request here
  // and then do the updating in a callback.
  //
  // Update the modal's content.
  var modalTitle = MotdePasseModal.querySelector('.modal-title')
  var modalBodyId = MotdePasseModal.querySelector('.modal-body #floatingInput')
  var modalBodyPw = MotdePasseModal.querySelector('.modal-body #floatingPass')

  modalTitle.textContent = 'Réinitialise ' + NomComplet
  modalBodyId.value = atob(Identifiant)
  modalBodyPw.value = ''
})

</script>