
/**
 * envoie une requete AJAX pour recuperer le mdp, ou le masque si deja affiché
 *
 *	@param string elem	Id du bouton qui a été cliqué
 *
 *	@return null
 */
function ajax_getMdp (elem) {
	var btn_pwd = document.getElementById (elem);
	if ((btn_pwd.hasAttribute ("data-bs-toggle")) || (btn_pwd.disabled)) {
		updatePasswordConstraint(0);
		// c'est un bouton reset, on fait rien.
		return;
	}
	if (btn_pwd.hasAttribute("enclair")) {
		// le mot de passe est affiché, on le masque
		btn_pwd.className = 'btn btn-outline-success btn-sm'
		btn_pwd.innerHTML = 'Voir le mot de passe';
		btn_pwd.removeAttribute ('enclair');
	} else {
		// affiche un spinner
		btn_pwd.disabled = true
		btn_pwd.className = ''
		btn_pwd.innerHTML = '<img src="./runcat2.gif" width="76" height="32">&nbsp;travail en cours...';
		// requete AJAX
		RequestVars = {
			get: btn_pwd.getAttribute ('data-bs-uid')
		};
		var myRequest = new Request ('ajax.php', {
			method: 'POST',
			cache: 'no-cache',
			body: JSON.stringify (RequestVars)
		});
		
		fetch (myRequest).then (function (response) {
			return response.text().then (function (text) {
				if (text == '**************') {
					// masque dans iaca, transforme le bouton en reset
					btn_pwd.disabled = false
					btn_pwd.className = 'btn btn-outline-success btn-sm'
					btn_pwd.innerHTML = 'Le mot de passe est masqué : le réinitialiser';
					btn_pwd.setAttribute ('data-bs-toggle', "modal");
				} else if (text == "Votre session a expirée.") {
					window.location.replace ("?sessionexpired");
				} else {
					// affichage
					btn_pwd.disabled = false
					btn_pwd.innerHTML = text;
					btn_pwd.setAttribute ('enclair', true);
					document.getElementById ('btnshow').innerHTML = 'Masquer tous';
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
function ajax_setMdp() {
    var modalBodyId = MotdePasseModal.querySelector ('.modal-body #floatingInput')
    var modalBodyPw = MotdePasseModal.querySelector ('.modal-body #floatingPass')
	var btn_pwd = document.getElementById ('pwd_' + b64 (modalBodyId.value));
	//check si le mdp n'est pas vide
	if ((modalBodyPw.value.length >= 5) && (!btn_pwd.disabled)) {
		// affiche un spinner
		btn_pwd.disabled = true
		btn_pwd.className = ''
		btn_pwd.removeAttribute ('data-bs-toggle');
		btn_pwd.innerHTML = '<img src="./runcat2.gif" width="76" height="32">&nbsp;Travail en cours...';
		// requete AJAX
		RequestVars = {
			set: btoa (modalBodyPw.value),
			otp: MotdePasseModal.querySelector ('.modal-body #pwdonetime').checked,
			uid: btn_pwd.getAttribute ('data-bs-uid')
		};
		var myRequest = new Request ('ajax.php', {
			method: 'POST',
			cache: 'no-cache',
			body: JSON.stringify (RequestVars)
		});
		fetch (myRequest).then (function (response) {
			return response.text().then (function (text) {
				if (text == 'OK') {
					// affichage
					btn_pwd.disabled = false
					btn_pwd.innerHTML = '';//modalBodyPw.value;
					btn_pwd.innerHTML += '&nbsp;<span type="button" class="btn btn-outline-secondary btn-sm d-print-none">Le mot de passe a été réinitialisé.</span>';
					btn_pwd.setAttribute ('enclair', true);
					btn_pwd.setAttribute ('name', 'motdepasse');
					btn_show = document.getElementById ('btnshow');
					if (btn_show) {
						btnshow.innerHTML = 'Masquer tous';
					}
				} else if (text == "Votre session a expirée.") {
					window.location.replace ("?sessionexpired");
				} else {
					btn_pwd.disabled = false
					btn_pwd.className = 'btn btn-outline-danger btn-sm'
					btn_pwd.innerHTML = 'Erreur. Réinitialiser avec un nouveau mot de passe. ';
					btn_pwd.setAttribute ('data-bs-toggle', "modal");
				}
			});
		});
	}
}