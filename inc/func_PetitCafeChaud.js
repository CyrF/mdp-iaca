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
 *	@param string elem	Id du bouton qui a été cliqué
 *
 *	@return null
 */
function ajax_getMdp( elem ) {
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
		RequestVars = {
			get: btn_pwd.getAttribute('data-bs-uid')
		};
		var myRequest = new Request('ajax.php', {
			method: 'POST',
			cache: 'no-cache',
			body: JSON.stringify(RequestVars)
		});
		//var myRequest = new Request('ajax.php?get=' + btn_pwd.getAttribute('data-bs-uid'));
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
					document.getElementById( 'btnshow' ).innerHTML = 'Masquer tous';
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
		//var myRequest = new Request('ajax.php?set=' + btoa(modalBodyPw.value) + '&uid=' + btn_pwd.getAttribute('data-bs-uid'));
		RequestVars = {
			set: btoa(modalBodyPw.value),
			uid: btn_pwd.getAttribute('data-bs-uid')
		};
		var myRequest = new Request('ajax.php', {
			method: 'POST',
			cache: 'no-cache',
			body: JSON.stringify(RequestVars)
		});
		fetch(myRequest).then(function(response) {
			return response.text().then(function(text) {
				if (text == 'OK') {
					// affichage
					btn_pwd.disabled = false
					btn_pwd.innerHTML = modalBodyPw.value;
					btn_pwd.innerHTML += '&nbsp;<span type="button" class="btn btn-outline-secondary btn-sm d-print-none">Temporaire, Cliquez pour masquer</span>';
					btn_pwd.setAttribute('enclair', true);
					btn_pwd.setAttribute('name', 'motdepasse');
					document.getElementById( 'btnshow' ).innerHTML = 'Masquer tous';
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
 * Genere un mot de passe de 8 char, et rempli un champ avec.
 *
 *	@param string input		htmlinput a remplir
 *	@param int length		Longueur du mot de passe
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
 * Note la force d'un mot de passe
 *
 *	@param str pass		le mot de passe a tester
 *
 *  @source https://stackoverflow.com/a/11268104
 *	@return int
 */
function scorePassword(pass) {
    var score = 0;
    if (!pass)
        return score;

    // award every unique letter until 5 repetitions
    var letters = new Object();
    for (var i=0; i<pass.length; i++) {
        letters[pass[i]] = (letters[pass[i]] || 0) + 1;
        score += 5.0 / letters[pass[i]];
    }

    // bonus points for mixing it up
    var variations = {
        digits: /\d/.test(pass),
        lower: /[a-z]/.test(pass),
        upper: /[A-Z]/.test(pass),
        nonWords: /\W/.test(pass),
    }

    var variationCount = 0;
    for (var check in variations) {
        variationCount += (variations[check] == true) ? 1 : 0;
    }
    score += (variationCount - 1) * 10;

	if (pass.length < 5 ) { score = score / 5; }

    return parseInt(score);
}


/**
 * Note la force d'un mot de passe
 *
 *	@param str pass		le mot de passe a tester
 *
 *  @source https://codepen.io/oriadam/pen/ExmaoYy
 *	@return int
 */
function scorePassword2(pass) {
	let score = 0;

	// variation range
	score += new Set(pass.split("")).size * 1;

	// shuffle score - bonus for messing things up. 0 score for playing with upper/lowercase.
	const charCodes = pass.split('').map(x=>x.toLowerCase().charCodeAt(0));
	for (let i=1; i < charCodes.length;i++)
	{
		const dist = Math.abs(charCodes[i-1]-charCodes[i]);
		if (dist > 60)
			score += 15;
		else if (dist > 1)
			score += 5;
	}

	// bonus for length
	score += (pass.length - 6) * 3;

	return parseInt(score);
}


/**
 * Mets a jour la barre indiquant la force d'un mdp
 *
 *	@param str pass		le mot de passe a evaluer
 *
 */
function updatePasswordMeterBar(pass) {
	meter = document.getElementById( 'pwdscore' );
    var score = scorePassword(pass);
	meter.value = score;
	return;
}


/**
 * Bascule l'affichage d'un champ password entre clair/masqué
 *
 *	@param str passId	L'Id du champ mot de passe
 *
 */
function togglePasswordVisibility( passId ) {
	var p = document.getElementById( passId );
	if (p.type === "password") {
		p.type = "text";
	} else {
		p.type = "password";
	}
}


/**
 * click sur tous les boutons 'voir le mdp provisoire'
 *
 *	@param string btnid		Id du bouton qui a été cliqué
 *
 *	@return null
 */
function RevelerTous( btnid ) {
	passwords = document.getElementsByName('motdepasse');
	label = document.getElementById( btnid );

	if ( label.innerHTML == 'Masquer tous' ) {
		label.innerHTML = 'Afficher les MdP';
		hide = true;
	} else {
		label.innerHTML = 'Masquer tous';
		hide = false;
	}

	for (let passbtn of passwords) {
		if ( hide ) {
			// si c'est un bouton Hide et que le pass est en clair
			if ( passbtn.hasAttribute("enclair")) {
				passbtn.click();
			}
		} else {
			// si c'est un bouton Show et que le pass n'est pas affiché
			if (! passbtn.hasAttribute("enclair")) {
				passbtn.click();
			}
		}
	}
}