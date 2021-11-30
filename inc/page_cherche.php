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
	   <br />
	   <div class="form-floating">
        <input  onfocus="generate_pwd(this, 6);" 
			type="text" class="form-control" 
			id="floatingPass" name="password"  placeholder=""
			pattern=".{5,128}"
			title="5 caractères minimum. 
Minuscules, majuscules et chiffres autorisés.
Plus les caractères spéciaux ~!@#$%&*_-+|\(){}[]:;<>,.?/">
		<label for="floatingPass">Nouveau mot de passe</label>
		<div class="form-check form-switch">
		  <input type="checkbox" class="form-check-input" id="pwdonetime" checked disabled>
		  <label class="form-check-label" for="pwdonetime">Devra être changé à la prochaine ouverture de session.</label>
		</div>
		<div class="form-check form-switch">
		  <input type="checkbox" class="form-check-input" id="pwdhidden" checked disabled>
		  <label class="form-check-label" for="pwdhidden">N'est pas stocké en clair sur le serveur</label>
		</div>
	   </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="setMdp();" id="modal-save">Enregistrer</button>
      </div>
    </div>
  </div>
</div>


<div class="container">
<h3>Recherche : <?php echo $cherche; ?></h3>

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
				id='pwd_{$b64_uid}' onclick='getMdp(this.id);'
				data-bs-target='#MotdePasseModal' name='motdepasse'
				data-bs-uid='{$b64_uid}' data-bs-name='{$b64_name}'>
					Le compte n'a pas été utilisé : voir le mdp
			</span>";
	} else {
		// le compte a servi, donc le mdp a été personnalisé
		echo "<span type='button' class='btn btn-outline-success btn-sm d-print-none' 
				id='pwd_{$b64_uid}' onclick='getMdp(this.id);'
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
?>
</table><img src="./runcat2.gif" style="visibility:hidden;">
</div>

<script>
/**
 * convertit en b64 sans les egals a la fin
 *
 *	@param string $txt	texte a convertir
 *
 *	@return string
 */
function b64( txt ) {
	return btoa(txt).replaceAll('=', '');
}

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
	var btn_pwd = document.getElementById('pwd_' + b64(modalBodyId.value));
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
					btn_pwd.innerHTML += '&nbsp;<span type="button" class="btn btn-outline-secondary btn-sm d-print-none">Temporaire, Cliquez pour masquer</span>';
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

/**
 * prérempli la fenetre modal avec les bonnes valeurs
 *
 *	@return null
 */
var MotdePasseModal = document.getElementById('MotdePasseModal')
MotdePasseModal.addEventListener('show.bs.modal', function (event) {
  // Button that triggered the modal
  var button = event.relatedTarget
  // Extract info from data-bs-* attributes
  var NomComplet = button.getAttribute('data-bs-name')
  var Identifiant = button.getAttribute('data-bs-uid')
  // Update the modal's content.
  var modalTitle = MotdePasseModal.querySelector('.modal-title')
  var modalBodyId = MotdePasseModal.querySelector('.modal-body #floatingInput')
  var modalBodyPw = MotdePasseModal.querySelector('.modal-body #floatingPass')

  modalTitle.textContent = 'Réinitialise ' + decodeURIComponent(window.atob( NomComplet ))
  modalBodyId.value = atob(Identifiant)
  modalBodyPw.value = ''
})

/**
 * valide la fenetre quand on apppuie sur entree
 *
 *	@return null
 */
var input = document.getElementById("floatingPass");
input.addEventListener("keyup", function(event) {
  // Number 13 is the "Enter" key on the keyboard
  if (event.keyCode === 13) {
    event.preventDefault();
    // Trigger the button element with a click
    document.getElementById("modal-save").click();
  }
}); 
</script>