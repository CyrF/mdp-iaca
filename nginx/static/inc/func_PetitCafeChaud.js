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
 * Genere un mot de passe de xx char.
 *
 *	@param int length		Longueur du mot de passe
 *
 *	@return string
 */
//function generate_wrd () { return 'éviscérer3lapinsDiaboliques?';}
function generate_pwd (length) {
	wishlist = "23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz";
	return Array(length)
      .fill('') // fill an empty will reduce memory usage
      .map(() => wishlist[Math.floor(crypto.getRandomValues(new Uint32Array(1))[0] / (0xffffffff + 1) * wishlist.length)])
      .join('');
}

/**
 * Rempli un champ avec un mot de passe.
 *
 *	@param string input		htmlinput a remplir
 *	@param int length		  Longueur du mot de passe
 *	@param string method		Methode de creation
 *
 *	@return null
 */
function fill_pwd (inputId, length, method = 'alea') {
	input = document.getElementById (inputId);
	if (typeof(method) == 'object') {method = method.getAttribute('data-pw-method');}
	if (method == 'alea') {
		// remplit le champ avec un pw aleatoire
		input.value = generate_pwd (length);
	}
	if (method == 'word') {
		// remplit le champ avec une pseudo-phrase
		input.value = generate_wrd ();
	}
	// force l'affichage en clair
	togglePasswordVisibility (inputId, 'btnEyePass', true);
	// definit le bouton sur la derniere methode utilisée
	document.getElementById ('PassGenBtn').setAttribute ('data-pw-method', method);
	// permets d'activer le bouton enregistrer
	updatePasswordConstraint (input.value, length);
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
function scorePassword(pass, lg_mini) {
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

	if (pass.length < lg_mini ) { score = score / 5; }

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
function scorePassword2(pass, lg_mini) {
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
	score += (pass.length - lg_mini - 1) * 3;

	return parseInt(score);
}


/**
 * Mets a jour la barre indiquant la force d'un mdp
 *
 *	@param str pass		le mot de passe a evaluer
 *	@param int lg_mini	longueur minimale a respecter
 *
 */
function updatePasswordMeterBar (pass, lg_mini) {
	var meter = document.getElementById ( 'pwdscore' );
  var score = scorePassword (pass, lg_mini);
  var score2 = scorePassword2 (pass, lg_mini);
	meter.value = Math.min (score, score2);
	updatePasswordConstraint (pass, lg_mini);
	return;
}


/**
 * colorie en rouge le texte indiquant les contrainte de mdp
 * et desactive le bouton pour valider
 *
 *	@param str pass			le mot de passe a evaluer
 *	@param int lg_mini	longueur minimale a respecter
 *
 */
function updatePasswordConstraint (pass, lg_mini) {
	btn = document.getElementById ('modal-save');
	txt = document.getElementById ('PassConstraint');
	if (pass.length >= lg_mini) {
		//pass valide, on active le form, et mets en vert le texte.
		btn.disabled = false;
		txt.className = 'text-success';
	} else {
		//pass trop court.
		btn.disabled = true;
		txt.className = 'text-danger';
	}
}


/**
 * Bascule l'affichage d'un champ password entre clair/masqué
 *
 *	@param str passId	L'Id du champ mot de passe
 *
 */
function togglePasswordVisibility( passId, Eye = false, force = false ) {
	var p = document.getElementById( passId );
	if (p.type === "password") {
		p.type = "text";
		if (Eye) {document.getElementById(Eye).className = 'fa fa-eye-slash';}
	} else {
		if (!force) {
		p.type = "password";
		if (Eye) {document.getElementById(Eye).className = 'fa fa-eye';}
		}
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


/**
 * Genere une phrase de passe.
 *
 *	@param string modele 		forme de la phrase
 *
 *	@return string
 */
function generate_wrd (modele = 'NNsuJetsComplements') {

	var ChoisirDansTableau = function (arr) {
		return arr[Math.floor (crypto.getRandomValues (
			new Uint32Array (1))[0] / (0xffffffff + 1) * arr.length)];
	}

	var words = get_words ();
	var Nom = ChoisirDansTableau (words.n);
	var Chiffre = Math.ceil ((Math.random() * 89) + 10) ;
	var Verbe = ChoisirDansTableau (words.v);

	switch (modele) {
		case 'NNsuJetsComplements':
			if (Math.random () > 0.5) {
				// veRbe + NN + suJets
				return  words.RandomCase (Verbe) +
					Chiffre +
					words.RandomCase (Nom.substr (3));
			} else  {
				// NN + suJets + Complements
				var Adj = words.Accord (Nom, ChoisirDansTableau (words.c));
				if (Adj[0] == Adj[0].toUpperCase ()) {
					return 	Chiffre +
							words.RandomCase (Adj) +
							Nom[3].toUpperCase () + words.RandomCase (Nom.substr (4));
				} else {
					return 	Chiffre +
							words.RandomCase (Nom.substr (3)) +
							Adj[0].toUpperCase () + words.RandomCase (Adj.substr (1));
				}
			}

		case 'veRbeNNsuJetsComplements':
			// veRbe + NN + suJets + Complements
			var Adj = words.Accord (Nom, ChoisirDansTableau (words.c));
			if (Adj[0] == Adj[0].toUpperCase ()) {
				return 	words.RandomCase (Verbe) +
						Chiffre +
						words.RandomCase (Adj) +
						Nom[3].toUpperCase () + words.RandomCase (Nom.substr (4));
			} else {
				return 	words.RandomCase (Verbe) +
						Chiffre +
						words.RandomCase (Nom.substr (3)) +
						Adj[0].toUpperCase () + words.RandomCase (Adj.substr (1));
			}
	}
}

/**
 * Liste de mots pour le générateur de phrase.
 *
 *	@return object
 */
function get_words () {
	var words = {
		'c': [
			// format : "base_commune.suffixe_feminin.suffixe_masculin";
			// et 1ère lettre en majuscule si doit être placé devant.
			"Be.lles.aux", "Bon.nes.s", "Br.èves.efs", "Cher.es.s", "Chics", "Fameu.se.x", "Fi.ères.ers", "Fin.es.s", "Fo.lles.us", "Fort.es.s", "Grand.es.s", "Gre.ques.cs", "Gro.sses.s", "Haut.es.s", "Long.ues.s", "Lourd.es.s", "Masto.ques.cs", "Petit.es.s", "Pires", "Supers", "Vie.illes.ux", "Vil.es.s", "a genoux", "a l’ouest", "abject.es.s", "abrupt.es.s", "accru.es.s", "acerbes", "acides", "acqui.ses.s", "acti.ves.fs", "admi.es.s", "advenu.es.s", "affailbli.es.s", "affamé.es.s", "affiné.es.s", "agiles", "ahuris", "aidant.es.s", "aigres", "aigu.es.s", "ailé.es.s", "ambré.es.s", "amer.es.s", "amical.es.s", "amples", "apaisant.es.s", "aptes", "arboricoles", "ardu.es.s", "arides", "arpenté.es.s", "arrogé.es.s", "assagi.es.s", "assi.ses.s", "astral.es.s", "atomiques", "atones", "attirant.es.s", "au galops", "aux abois", "aux cassis", "avides", "avisé.es.s", "azuré.es.s", "baillant.es.s", "balisé.es.s", "banal.es.s", "bancal.es.s", "barbu.es.s", "basques", "basses", "beiges", "belges", "blanc.hes.s", "blets", "blond.es.s", "boisé.es.s", "borgnes", "bouffi.es.s", "bougeant.es.s", "braqué.es.s", "brunâtres", "brut.es.s", "caducs", "calmes", "capti.ves.fs", "carré.es.s", "caudal.es.s", "chaud.es.s", "chauves", "chloré.es.s", "choquant.es.s", "chromé.es.s", "cintré.es.s", "clair.es.s", "claveté.es.s", "clo.ses.s", "clouté.es.s", "confu.ses.s", "connu.es.s", "corsé.es.s", "cossu.es.s", "couvert.es.s", "cozys", "crain.tes.s", "creu.ses.x", "criard.es.s", "croisé.es.s", "croquant.es.s", "croulant.es.s", "cru.es.s", "cruel.les.s", "cuit.es.s", "cupides", "damné.es.s", "dansant.es.s", "de cross", "de cuba", "de ricins", "debout", "denses", "design", "dicté.es.s", "diffu.ses.s", "dignes", "dilué.es.s", "direct.es.s", "dirigeant.es.s", "disca.les.ux", "disco", "divin.es.s", "dociles", "dodu.es.s", "dominé.es.s", "dompté.es.s", "donné.es.s", "dopant.es.s", "dopé.es.s", "doré.es.s", "dou.ces.x", "doubles", "doué.es.s", "drainé.es.s", "dramatiques", "drapé.es.s", "dressé.es.s", "dru.es.s", "dupes", "dur.es.s", "durci.es.s", "effacé.es.s", "effarant.es.s", "effaré.es.s", "effillé.es.s", "effrayant.es.s", "empli.es.s", "en cobol", "encerclé.es.s", "enchanté.es.s", "encré.es.s", "endetté.es.s", "endeuillé.es.s", "endormi.es.s", "enfilé.es.s", "enflammé.es.s", "enflé.es.s", "enfoui.es.s", "enfumant.es.s", "enivrant.es.s", "enti.ères.ers", "envié.es.s", "envolé.es.s", "errant.es.s", "espérant.es.s", "exact.es.s", "exaltant.es.s", "exaucé.es.s", "excusé.es.s", "exigu.es.s", "exilé.es.s", "exitant.es.s", "exlu.es.s", "expiré.es.s", "expié.es.s", "extras", "fabuleu.ses.x", "facial.es.s", "fades", "faibles", "fané.es.s", "farci.es.s", "fatal.es.s", "fauché.es.s", "fendu.es.s", "fermé.es.s", "festi.ves.fs", "fichu.es.s", "ficti.ves.fs", "figé.es.s", "filant.es.s", "filtrant.es.s", "finaud.es.s", "fini.es.s", "fiscal.es.s", "fixé.es.s", "flambé.es.s", "flapi.es.s", "flatté.es.s", "fleuri.es.s", "flou.es.s", "fluet.es.s", "fondant.es.s", "fondu.es.s", "forgé.es.s", "formé.es.s", "foutu.es.s", "fra.îches.is", "franc.hes.s", "fri.ses.ts", "froid.es.s", "frontal.es.s", "frugal.es.s", "fugaces", "furieu.ses.x", "fuyant.es.s", "gaffeu.ses.rs", "gagnant.es.s", "gai.es.s", "galant.es.s", "galeu.ses.x", "ganté.es.s", "garant.es.s", "gardé.es.s", "garni.es.s", "garé.es.s", "gavant.es.s", "gazeu.ses.x", "geignant.es.s", "gelant.es.s", "gentil.les.s", "gercé.es.s", "gisant.es.s", "givrant.es.s", "glacant.es.s", "glacé.es.s", "glané.es.s", "gobé.es.s", "gommé.es.s", "gonflé.es.s", "goulu.es.s", "gra.sses.s", "grandi.es.s", "gratis", "graves", "gravi.es.s", "grenu.es.s", "gri.ses.s", "griffu.es.s", "griffé.es.s", "grimpant.es.s", "grippé.es.s", "grondé.es.s", "groupé.es.s", "habiles", "hardi.es.s", "hideu.ses.x", "hilares", "hostiles", "idiot.es.s", "idoines", "ignares", "imbu.es.s", "impoli.es.s", "impur.es.s", "inaptes", "indu.es.s", "ineptes", "inertes", "infinis", "infirmes", "ingrat.es.s", "iniques", "initié.es.s", "ivres", "jaunes", "jauni.es.s", "joli.es.s", "joyeu.ses.x", "juniors", "justes", "juteu.ses.x", "kaki.es.s", "laid.es.s", "larges", "lasci.ves.fs", "latent.es.s", "lent.es.s", "libres", "licites", "liquides", "local.es.s", "loti.es.s", "loya.les.ux", "lucides", "luisant.es.s", "magiques", "majors", "malin.es.s", "mates", "matures", "mauves", "migon.nes.s", "minces", "minci.es.s", "minimes", "minoré.es.s", "mixtes", "mo.lles.ux", "moches", "moisi.es.s", "moites", "mordu.es.s", "mortel.es.s", "moulu.es.s", "moyen.nes.s", "muet.tes.s", "mura.les.ux", "nati.ves.fs", "naval.es.s", "net.ttes.s", "neu.ves.fs", "neutres", "nobles", "noci.ves.fs", "noir.es.s", "nomades", "normal.es.s", "noueu.ses.x", "nourri.es.s", "nul.les.s", "obtu.es.s", "odieu.ses.x", "offert.es.s", "oisi.ves.fs", "omis.es.s", "opaques", "ouvert.es.s", "oval.es.s", "parfumé.es.s", "passi.ves.fs", "perses", "piteu.ses.x", "plat.es.s", "plein.es.s", "poilu.es.s", "poli.es.s", "pourri.es.s", "pourvu.es.s", "proches", "promi.ses.s", "promu.es.s", "propres", "prudes", "publi.ques.cs", "pur.es.s", "rances", "ranci.es.s", "rares", "recuit.es.s", "repu.es.s", "riches", "rigides", "romain.es.s", "rond.es.s", "rou.sses.x", "rouges", "rougeâtres", "roya.lles.ux", "rudes", "rura.lles.ux", "russes", "s.èches.ecs", "saoul.es.s", "saxon.nes.s", "seul.es.s", "seyant.es.s", "slaves", "snobs", "sobres", "social.es.s", "soumi.ses.s", "sourd.es.s", "stables", "suaves", "suisses", "sur.es.s", "sympas", "syrien.nes.s", "taquin.es.s", "texan.es.s", "timides", "transi.es.s", "trapu.es.s", "tribal.es.s", "tristes", "tur.ques.cs", "uniques", "usuel.les.s", "utiles", "vacant.es.s", "vaseu.ses.x", "vastes", "velu.es.s", "ventru.es.s", "verdâtres", "vi.ves.fs", "viables", "violet.tes.s", "vira.les.ux", "visuel.les.s", "vita.les.ux", "vivaces", "voca.les.ux", "zens"
		],
		'n': [
			// format :  préfixe "le" ou "la" pour masculin/féminin
			"la actrices", "la agences", "la agonies", "la agrafes", "la aides", "la ailes", "la aines", "la aires", "la aises", "la alarmes", "la alertes", "la algues", "la alliance", "la allures", "la amarres", "la ambres", "la amibes", "la amies", "la anches", "la ancres", "la angines", "la annexes", "la anodes", "la anses", "la antres", "la aortes", "la arases", "la arcades", "la arches", "la ares", "la argiles", "la armées", "la arrivées", "la astuces", "la aubes", "la auges", "la augures", "la autos", "la avances", "la avaries", "la avenues", "la averses", "la bagues", "la baies", "la balades", "la balises", "la balles", "la bananes", "la bandes", "la banques", "la barbe", "la barges", "la barres", "la bases", "la bauges", "la bennes", "la berces", "la berges", "la berlues", "la biches", "la biffes", "la biles", "la billes", "la bises", "la blagues", "la blouses", "la bobines", "la bombes", "la bondes", "la bottes", "la boues", "la bouffes", "la boules", "la bouses", "la boxes", "la braderies", "la braises", "la brebis", "la bricks", "la brides", "la briques", "la bruines", "la brumes", "la bulles", "la buses", "la buttes", "la cabines", "la cachettes", "la cages", "la cales", "la calottes", "la cames", "la canes", "la cannes", "la carafes", "la cardes", "la caries", "la carpes", "la cartes", "la castes", "la causes", "la cavales", "la caves", "la chaires", "la chances", "la chapes", "la chartes", "la chaux", "la chips", "la chiques", "la chopes", "la chorales", "la choses", "la chutes", "la cibles", "la cimes", "la citations", "la claies", "la claques", "la classes", "la clavettes", "la clefs", "la cliques", "la cloches", "la cohues", "la coiffes", "la colles", "la conques", "la convives", "la copies", "la coques", "la cordes", "la cornes", "la cosses", "la cotes", "la cottes", "la couches", "la couleurs", "la courbes", "la coures", "la courses", "la craies", "la crasses", "la crises", "la croix", "la crosses", "la crottes", "la crues", "la cuisses", "la cuves", "la dagues", "la dalles", "la dames", "la danses", "la datas", "la dattes", "la daubes", "la dents", "la dettes", "la diapos", "la digues", "la dindes", "la diode", "la dorures", "la dotes", "la douanes", "la douches", "la douves", "la dunes", "la dynamos", "la eaux", "la encres", "la enfilades", "la envies", "la ermites", "la escales", "la ethnies", "la fables", "la faces", "la faims", "la farces", "la farines", "la fautes", "la fentes", "la fermes", "la fibres", "la fiches", "la figues", "la figures", "la files", "la fioles", "la firmes", "la flammes", "la fleurs", "la foires", "la folies", "la fontes", "la forces", "la forges", "la formes", "la forêts", "la fosses", "la fouines", "la fraises", "la frayes", "la friches", "la frites", "la fugues", "la fuites", "la fumées", "la fureurs", "la furies", "la fusées", "la gaffes", "la gaines", "la gales", "la galettes", "la gambas", "la gammes", "la gangues", "la gardes", "la gares", "la gazes", "la gemmes", "la gerbes", "la gifles", "la gigues", "la glaces", "la gloires", "la gommes", "la gorges", "la gouges", "la gourdes", "la gousses", "la graines", "la grappes", "la greffes", "la griffes", "la grilles", "la grives", "la grottes", "la grues", "la grumes", "la guerres", "la gueuses", "la guêtres", "la haches", "la haies", "la haines", "la halles", "la haltes", "la hampes", "la hargnes", "la harpes", "la harpies", "la havres", "la herbes", "la hernies", "la herses", "la heures", "la hippies", "la hontes", "la hordes", "la horizons", "la houes", "la houles", "la huches", "la hulottes", "la humeurs", "la huppes", "la huttes", "la idoles", "la idées", "la images", "la iodes", "la ironies", "la issues", "la jades", "la jambes", "la jantes", "la jarres", "la jauges", "la joies", "la joutes", "la jouxtes", "la jupes", "la lagunes", "la laies", "la laines", "la laisses", "la lames", "la lampes", "la lances", "la landes", "la langues", "la laques", "la larmes", "la larves", "la lattes", "la laves", "la lettres", "la levures", "la lignes", "la ligues", "la limites", "la lionnes", "la listes", "la lobes", "la lois", "la longes", "la loques", "la loupes", "la loutres", "la louves", "la lubies", "la lueurs", "la luges", "la lunes", "la luttes", "la luxures", "la lyres", "la madones", "la mafias", "la magies", "la mailles", "la mains", "la mairies", "la malices", "la malles", "la manies", "la mannes", "la mantes", "la marches", "la mares", "la marges", "la marines", "la marques", "la masses", "la massues", "la masures", "la menaces", "la merlus", "la mers", "la meules", "la meutes", "la miches", "la mines", "la mires", "la misères", "la modes", "la moelles", "la momies", "la montres", "la morgues", "la morues", "la motos", "la mottes", "la mouches", "la moues", "la mousses", "la mures", "la muses", "la nacres", "la nageoires", "la nappes", "la nations", "la nattes", "la nefs", "la neiges", "la niches", "la noces", "la noix", "la normes", "la notes", "la nuits", "la nuques", "la oasis", "la obole", "la ocres", "la odes", "la odeurs", "la offres", "la ogives", "la oies", "la olives", "la ombres", "la ondes", "la opales", "la options", "la ordures", "la orges", "la orgues", "la orques", "la orties", "la otaries", "la ouates", "la oueds", "la ourses", "la pages", "la pagodes", "la paies", "la pailles", "la paires", "la paix", "la pales", "la palmes", "la pampas", "la pannes", "la paonnes", "la parades", "la parois", "la parts", "la parure", "la pattes", "la paumes", "la pauses", "la paye", "la peaux", "la peines", "la pelles", "la pelotes", "la pentes", "la pertes", "la pestes", "la peurs", "la phases", "la phobies", "la photos", "la pies", "la piges", "la piles", "la pilules", "la pinces", "la pintes", "la pistes", "la pizzas", "la places", "la plages", "la plaies", "la plaines", "la plantes", "la planète", "la pliures", "la pluies", "la plumes", "la poches", "la poires", "la poisses", "la polkas", "la pommes", "la pompes", "la pores", "la potions", "la poudres", "la poules", "la poupes", "la pousses", "la presses", "la prises", "la prisons", "la proies", "la proses", "la proues", "la prunes", "la pubs", "la puces", "la pulpes", "la purges", "la queues", "la quilles", "la races", "la racines", "la rades", "la radios", "la rafales", "la rafles", "la rages", "la raies", "la rasades", "la rations", "la razzias", "la recrues", "la reines", "la remises", "la rentes", "la revues", "la rimes", "la rivales", "la rives", "la rixes", "la robes", "la roches", "la ronces", "la rosaces", "la roses", "la rotules", "la roues", "la routes", "la ruches", "la ruines", "la ruses", "la sagas", "la salives", "la salles", "la salves", "la sambas", "la sauces", "la scies", "la scories", "la seiches", "la selles", "la seringues", "la serpes", "la siestes", "la soies", "la soifs", "la soles", "la solives", "la sondes", "la sonnettes", "la sonos", "la sorties", "la sottes", "la soupes", "la sources", "la souris", "la soutes", "la spires", "la squaws", "la stars", "la strates", "la stries", "la sueurs", "la suies", "la suites", "la sœurs", "la tables", "la taies", "la tailles", "la tantes", "la tares", "la tartes", "la tasses", "la taules", "la taupes", "la taxes", "la tempes", "la tentes", "la tenues", "la terres", "la tiges", "la tiques", "la tirades", "la tisanes", "la toges", "la toiles", "la toises", "la tonnes", "la tontes", "la toques", "la toupies", "la toux", "la toxines", "la traces", "la trames", "la trempes", "la tresses", "la triades", "la tribus", "la triches", "la tripes", "la trombes", "la truies", "la truites", "la tuiles", "la typos", "la unions", "la urgences", "la urines", "la urnes", "la usines", "la usures", "la vaches", "la vagues", "la valeurs", "la valses", "la vannes", "la vans", "la vapeurs", "la varechs", "la vases", "la veines", "la vengeances", "la verrues", "la vessies", "la vestes", "la viandes", "la vies", "la vigies", "la vignes", "la villas", "la villes", "la vis", "la visions", "la visites", "la vitres", "la vodkas", "la voies", "la voiries", "la voix", "la volutes", "la vrilles", "la vues", "la zones", "le abats", "le abords", "le abris", "le abrutis", "le absents", "le abus", "le acajous", "le accords", "le accrocs", "le accusés", "le aces", "le achats", "le acides", "le aciers", "le actes", "le acteurs", "le actifs", "le adages", "le adeptes", "le adieux", "le adultes", "le affixes", "le afflux", "le affres", "le agendas", "le agents", "le aigles", "le ails", "le aimants", "le airs", "le ajoncs", "le albums", "le alcools", "le alevins", "le alezans", "le alfas", "le alias", "le alibis", "le aloi", "le alpins", "le alter", "le altos", "le alus", "le amas", "le amis", "le amphis", "le amplis", "le anges", "le angles", "le angoras", "le animes", "le anis", "le anneaux", "le aphtes", "le appas", "le appeau", "le appels", "le apports", "le appuis", "le arbres", "le archets", "le arcs", "le argots", "le arts", "le asiles", "le aspects", "le aspics", "le astres", "le atlas", "le atolls", "le atomes", "le atours", "le atouts", "le aubier", "le audits", "le autels", "le auteurs", "le auvents", "le avals", "le avares", "le avatars", "le avenirs", "le aveux", "le avions", "le avis", "le axes", "le axiomes", "le azotes", "le azurs", "le azymes", "le babas", "le babils", "le bacons", "le bacs", "le badauds", "le badges", "le bagnes", "le bagous", "le bahuts", "le bains", "le balais", "le bals", "le balsas", "le bambins", "le bambous", "le bancos", "le bancs", "le banjos", "le bans", "le bantous", "le baquets", "le bardas", "le bardes", "le barils", "le barmans", "le barons", "le bars", "le bassins", "le bateaux", "le baudets", "le baumes", "le baux", "le bazars", "le becs", "le besoins", "le biais", "le biceps", "le bidets", "le bidons", "le bijoux", "le bilans", "le billets", "le billots", "le biseaux", "le bisons", "le bistros", "le bits", "le bitumes", "le blocs", "le boas", "le bocaux", "le bogues", "le bois", "le bolides", "le bols", "le bonbons", "le bonus", "le bords", "le boucs", "le boudins", "le bougres", "le boulons", "le bourgs", "le bouts", "le bovins", "le boyaux", "le bras", "le braves", "le breaks", "le brevets", "le bridges", "le bries", "le brins", "le bris", "le brocs", "le bronzes", "le bruits", "le budgets", "le buffles", "le buggys", "le buis", "le bulbes", "le burins", "le bus", "le busards", "le bustes", "le butins", "le buts", "le buvards", "le cabas", "le cacaos", "le cachets", "le cachous", "le cactus", "le caddies", "le cadets", "le cadres", "le cafards", "le cagibis", "le cahots", "le cajous", "le cakes", "le calages", "le calculs", "le calmars", "le camions", "le camps", "le canards", "le canaris", "le canaux", "le cancers", "le canifs", "le canons", "le canots", "le capots", "le caps", "le carats", "le carcans", "le cargos", "le carnets", "le carters", "le cartons", "le cas", "le casinos", "le catchs", "le caveaux", "le caviars", "le centres", "le ceps", "le cercles", "le cerfs", "le cernes", "le chahs", "le champs", "le chants", "le chaos", "le charmes", "le chars", "le chas", "le chatons", "le chats", "le chefs", "le chelems", "le chemins", "le chenets", "le chenils", "le chevets", "le chiens", "le chiots", "le chocs", "le choeurs", "le choix", "le choux", "le chromes", "le cidres", "le ciels", "le cils", "le cintres", "le cirages", "le citrons", "le civets", "le civils", "le clans", "le clapiers", "le climats", "le clins", "le clips", "le clones", "le clous", "le clowns", "le clubs", "le cobras", "le cochons", "le cocons", "le codages", "le codes", "le coeurs", "le coings", "le coins", "le colins", "le colis", "le colons", "le cols", "le colzas", "le comptes", "le contes", "le convois", "le coqs", "le coraux", "le cordages", "le cornets", "le corps", "le cors", "le corsets", "le cortex", "le cosmos", "le coteau", "le cotons", "le couacs", "le coucous", "le coups", "le cours", "le courts", "le cous", "le crabes", "le cracks", "le crans", "le crasses", "le crawls", "le crayons", "le crescendos", "le cribles", "le cricris", "le crics", "le crins", "le cris", "le crocs", "le cubes", "le cuirs", "le cuivres", "le culots", "le cumuls", "le curages", "le currys", "le cycles", "le cygnes", "le dadas", "le daims", "le dandys", "le dards", "le deltas", "le demis", "le deniers", "le derbys", "le dermes", "le destins", "le devins", "le devis", "le dictons", "le diesels", "le digits", "le dindons", "le dinghys", "le dingos", "le dires", "le divans", "le diviseurs", "le docks", "le dogmes", "le doigts", "le dollars", "le dolmens", "le donjons", "le dons", "le dos", "le dosages", "le doyens", "le dragons", "le draps", "le droits", "le ducs", "le duels", "le duos", "le duvets", "le dés", "le effets", "le efforts", "le egos", "le elfes", "le encas", "le enduis", "le enfers", "le engins", "le enjeux", "le ennemis", "le ennuis", "le envois", "le envols", "le enzymes", "le ergots", "le ersatz", "le espions", "le espoirs", "le essaims", "le essais", "le essors", "le esters", "le estocs", "le exercices", "le exocets", "le exodes", "le experts", "le fagots", "le fakirs", "le fanions", "le faons", "le fards", "le fauves", "le fax", "le feintes", "le fers", "le feux", "le fiacres", "le fiascos", "le fiefs", "le fiels", "le fifres", "le filets", "le filins", "le films", "le filons", "le filous", "le fils", "le fjords", "le flacons", "le flairs", "le flans", "le flashs", "le flegmes", "le flots", "le fluides", "le flux", "le focs", "le focus", "le foies", "le foins", "le folios", "le fonds", "le forages", "le forains", "le forums", "le fouets", "le fours", "le foyers", "le fracas", "le freins", "le frets", "le frigos", "le frocs", "le fronts", "le fruits", "le fuels", "le fumages", "le fumets", "le fumiers", "le fumoirs", "le furets", "le fusains", "le fuseaux", "le futurs", "le gags", "le gains", "le galas", "le galbes", "le galets", "le galons", "le gangs", "le gants", "le gars", "le gazages", "le gazes", "le gazons", "le geais", "le gels", "le gendres", "le genets", "le genres", "le gestes", "le gibbons", "le gibets", "le gigots", "le gilets", "le girons", "le givres", "le glands", "le globes", "le gnomes", "le gnons", "le gnous", "le godets", "le golfes", "le gonds", "le gongs", "le gorets", "le gosiers", "le goulots", "le gourous", "le grades", "le gradins", "le grains", "le gratins", "le griefs", "le grils", "le grogs", "le groins", "le grooms", "le guanos", "le guets", "le guis", "le gypses", "le habits", "le hachis", "le halls", "le hamacs", "le haras", "le hayons", "le heaumes", "le hertz", "le heurts", "le hiatus", "le hiboux", "le hippys", "le hivers", "le hobbys", "le hochets", "le hockeys", "le homards", "le houx", "le humours", "le humus", "le hydres", "le hymnes", "le ibis", "le ictus", "le idiomes", "le idylles", "le ifs", "le igloos", "le iguanes", "le impers", "le incas", "le index", "le induis", "le influx", "le injures", "le inox", "le invités", "le ions", "le iris", "le isolants", "le items", "le jabots", "le jaguars", "le jalons", "le jambons", "le jardins", "le jarrets", "le jars", "le jazz", "le jeans", "le jetons", "le jets", "le jeunes", "le jeux", "le jobs", "le jockeys", "le jokers", "le joncs", "le jonques", "le joues", "le jouets", "le joueurs", "le jougs", "le joules", "le jours", "le joyaux", "le judas", "le judokas", "le judos", "le juges", "le jumeaux", "le jupons", "le jurons", "le jurys", "le jus", "le kaolins", "le karmas", "le karts", "le kayaks", "le kilos", "le kimonos", "le kiwis", "le kolas", "le krafts", "le kystes", "le labels", "le lacets", "le lacs", "le lagons", "le laits", "le lamas", "le lapins", "le lardons", "le lards", "le larrons", "le lascars", "le lasers", "le lassos", "le latex", "le latins", "le lavoirs", "le leaders", "le levages", "le levains", "le liants", "le liens", "le lieux", "le limiers", "le limons", "le linges", "le linos", "le lins", "le lions", "le litres", "le lits", "le livres", "le lobbys", "le loges", "le logis", "le logos", "le loirs", "le lopins", "le lotos", "le lots", "le lotus", "le loups", "le loyers", "le lupins", "le lurons", "le lustres", "le luths", "le lutins", "le luxes", "le lynx", "le lys", "le mages", "le magmas", "le magots", "le maires", "le malts", "le malus", "le manches", "le mandats", "le mangas", "le maniocs", "le manuels", "le maquis", "le marbres", "le marins", "le martyrs", "le mastics", "le matins", "le mats", "le maux", "le mayas", "le mazouts", "le maïs", "le melons", "le mentons", "le mentors", "le menuets", "le menus", "le merises", "le merlans", "le merles", "le mets", "le meubles", "le miasmes", "le micas", "le micmacs", "le microns", "le micros", "le miels", "le mies", "le minets", "le minois", "le mioches", "le mirages", "le miroirs", "le mixages", "le modems", "le modèles", "le moires", "le molosses", "le moments", "le mondes", "le mors", "le morses", "le motels", "le motifs", "le mots", "le moulins", "le moyeux", "le mufles", "le mulots", "le murets", "le murs", "le muscles", "le muscs", "le museaux", "le myopes", "le mythes", "le mûrier", "le nababs", "le nabots", "le napalms", "le naseaux", "le navets", "le navires", "le nectars", "le nerfs", "le nez", "le nids", "le nigauds", "le ninjas", "le niveaux", "le nodules", "le noeuds", "le nuages", "le nylons", "le obiers", "le objets", "le obus", "le octanes", "le octaves", "le octets", "le octrois", "le oeuvres", "le offices", "le ogres", "le ohms", "le oignons", "le okapis", "le ongles", "le opus", "le orages", "le oraux", "le ordres", "le orteils", "le oscars", "le osiers", "le otages", "le oublis", "le ours", "le outils", "le outrages", "le ovnis", "le oxydes", "le ozones", "le pachas", "le packs", "le pactes", "le pagnes", "le pains", "le palaces", "le palans", "le palets", "le pandas", "le panels", "le pantins", "le paons", "le papiers", "le paquets", "le paraphes", "le parcs", "le parias", "le paris", "le partis", "le pascals", "le patines", "le patins", "le patios", "le pavot", "le pavé", "le pays", "le pelages", "le permis", "le perrons", "le pesages", "le pesetas", "le peuples", "le phares", "le phoques", "le piafs", "le pianos", "le pics", "le pieds", "le pieux", "le pignons", "le piliers", "le pilons", "le pins", "le piolets", "le pions", "le pipeaux", "le pistils", "le pitons", "le pitres", "le pivots", "le pixels", "le plagias", "le plaids", "le plans", "le plasmas", "le plats", "le pleurs", "le pliages", "le plis", "le plombs", "le plots", "le pneus", "le podiums", "le poids", "le poils", "le poings", "le points", "le pois", "le pokers", "le pollens", "le polos", "le pompons", "le poneys", "le ponts", "le popotes", "le porcs", "le portos", "le ports", "le potages", "le potins", "le pots", "le pouces", "le poufs", "le poulets", "le poumons", "le pouvoirs", "le poux", "le prix", "le profits", "le projets", "le pronoms", "le puits", "le pulls", "le pumas", "le punchs", "le purins", "le putois", "le puzzles", "le pyjamas", "le pyrex", "le quais", "le quarts", "le quartz", "le quintés", "le quotas", "le rabats", "le rabiots", "le rabots", "le radars", "le radeaux", "le radier", "le radis", "le radiums", "le radius", "le raffuts", "le ragots", "le raids", "le rails", "le ranchs", "le rangs", "le rapaces", "le rappels", "le rasages", "le rasoirs", "le ratages", "le ratios", "le ratons", "le rats", "le ravages", "le ravins", "le rayons", "le rebords", "le rebuts", "le recels", "le recoins", "le records", "le reculs", "le redoux", "le reflux", "le refuges", "le regards", "le regrets", "le remous", "le renards", "le rennes", "le renoms", "le renvois", "le repas", "le replis", "le repos", "le retards", "le rhumes", "le rhums", "le rings", "le rivages", "le rivaux", "le rivets", "le robots", "le rocks", "le rodages", "le rognons", "le rois", "le romans", "le rondins", "le ronds", "le rosbifs", "le rotins", "le rotors", "le rouages", "le roulis", "le ruades", "le rubans", "le rubis", "le rues", "le rugbys", "le rushs", "le rythmes", "le sables", "le sabots", "le sabres", "le sacres", "le sacs", "le sages", "le saloirs", "le salons", "le saloons", "le saluts", "le samedis", "le sangs", "le sapins", "le satins", "le satires", "le saules", "le saunas", "le sauts", "le savants", "le savons", "le saxos", "le sbires", "le scalps", "le sceaux", "le scoops", "le scores", "le scouts", "le seaux", "le selfs", "le sels", "le semis", "le semoirs", "le sens", "le serfs", "le serres", "le seuils", "le shorts", "le shows", "le sieurs", "le sigles", "le signaux", "le signes", "le signets", "le silex", "le sillons", "le silos", "le singes", "le sinus", "le siphons", "le sires", "le sirops", "le sisals", "le sites", "le skates", "le skis", "le slogans", "le slows", "le snacks", "le socles", "le socs", "le sodas", "le sodiums", "le sofas", "le soins", "le soirs", "le sojas", "le soldes", "le solos", "le sols", "le sommets", "le sonars", "le songes", "le sonnets", "le sons", "le sorbets", "le sorts", "le sosies", "le sots", "le soucis", "le souks", "le sous", "le spasmes", "le sphinx", "le sports", "le spots", "le squares", "le stades", "le staffs", "le stages", "le stands", "le statuts", "le steaks", "le stems", "le sticks", "le stocks", "le stops", "le stores", "le stucs", "le styles", "le stylets", "le stylos", "le sucres", "le sud", "le suifs", "le sujets", "le sultans", "le surfils", "le surfs", "le surjets", "le sursis", "le survols", "le swaps", "le swings", "le syndics", "le synodes", "le tabacs", "le tabous", "le tacts", "le talcs", "le talons", "le talus", "le tam-tams", "le tamis", "le tandems", "le tangos", "le tanins", "le tanks", "le taons", "le tapis", "le taquets", "le tarifs", "le tarots", "le taux", "le taxis", "le tecks", "le teints", "le tempos", "le temps", "le tendons", "le tenons", "le termes", "le tertres", "le tests", "le textes", "le thons", "le thuyas", "le thyms", "le tiares", "le tibias", "le tiercés", "le tigres", "le timbres", "le timons", "le tirants", "le tirets", "le tiroirs", "le tirs", "le tisons", "le tissus", "le titanes", "le titans", "le titres", "le toasts", "le tocs", "le toits", "le tomes", "le tonus", "le topazes", "le toreros", "le torons", "le torses", "le torts", "le totaux", "le totems", "le toupets", "le tours", "le tracs", "le tracts", "le trains", "le traits", "le trams", "le traumas", "le treuils", "le triages", "le tribuns", "le tricots", "le trios", "le tris", "le trocs", "le trolls", "le troncs", "le trots", "le trous", "le truands", "le trucs", "le truffes", "le trusts", "le tsars", "le tubas", "le tubes", "le tulles", "le turbans", "le turfs", "le tuyaux", "le tweeds", "le types", "le typhons", "le typons", "le tyrans", "le usagers", "le usages", "le usinages", "le valets", "le vallons", "le varans", "le veaux", "le venins", "le vents", "le verbes", "le vernis", "le verres", "le verrous", "le vers", "le veto", "le viagers", "le vices", "le vidages", "le vides", "le vigiles", "le vins", "le vinyles", "le violons", "le virus", "le visages", "le visons", "le vivier", "le voeux", "le voiles", "le volets", "le voltages", "le votes", "le voyous", "le vrac", "le wagons", "le watts", "le yachts", "le yacks", "le yaks", "le yards", "le yeux", "le yogas", "le zestes", "le zincs", "le zooms", "le zoos", "le îlots"
		],
		'v': [
			"abattre", "abolir", "abonder", "abonner", "aborder", "aboutir", "aboyer", "abriter", "abroger", "absenter", "abuser", "accoler", "accorder", "acculer", "accuser", "acheter", "achever", "acter", "adapter", "adjurer", "admettre", "admirer", "adonner", "adopter", "adorer", "aduler", "affluer", "affoler", "agacer", "agencer", "agir", "agiter", "agonir", "agrafer", "ahurir", "aider", "aigrir", "aimer", "airer", "ajouter", "ajuster", "alerter", "aliter", "aller", "allier", "allouer", "allumer", "alpaguer", "aluner", "amender", "amener", "ameuter", "amputer", "amuser", "ancrer", "animer", "aniser", "annexer", "annoter", "aplanir", "appeler", "araser", "arborer", "armer", "arrimer", "arriver", "arroser", "atomiser", "augurer", "avaler", "avancer", "avilir", "aviver", "avoir", "avouer", "axer", "badiner", "bafouer", "baguer", "baigner", "bailler", "baisser", "balayer", "bannir", "barrer", "barrir", "battre", "baver", "becter", "bercer", "berner", "biffer", "biser", "blaser", "blesser", "bleuir", "blinder", "bloquer", "bluffer", "boire", "boiter", "bondir", "border", "borner", "bosser", "bouder", "bouffer", "bouger", "bouter", "boxer", "brader", "braire", "bramer", "brasser", "braver", "brider", "briller", "brimer", "briser", "broder", "bronzer", "broyer", "brunir", "buller", "cabrer", "cacher", "cadrer", "cahoter", "caler", "calmer", "camper", "candir", "capter", "caquer", "caser", "casser", "causer", "caver", "centrer", "cercler", "cerner", "cesser", "changer", "chanter", "charmer", "chier", "chiper", "choir", "choisir", "choquer", "chuter", "cibler", "ciller", "cintrer", "cirer", "citer", "clamer", "cliquer", "cloner", "clore", "clouer", "cocher", "coder", "cogner", "coller", "colorer", "compter", "conclure", "confier", "convier", "copier", "corder", "corner", "coter", "couder", "coudre", "couler", "coupler", "courir", "couver", "couvrir", "craindre", "crever", "crier", "croire", "crouler", "cuire", "cuber", "cumuler", "curer", "damer", "damner", "danser", "dater", "deviner", "devoir", "dicter", "diluer", "dire", "diriger", "diviser", "dominer", "dompter", "doper", "dorer", "dormir", "doser", "doter", "doubler", "douer", "douter", "dresser", "duper", "durer", "effacer", "embuer", "emmener", "emparer", "encrer", "enduire", "enfiler", "enfler", "enfumer", "enliser", "ennuyer", "enquérir", "enrayer", "enrober", "entamer", "entrer", "envier", "envoler", "errer", "essayer", "essuyer", "estimer", "exhaler", "exiger", "exposer", "faire", "faner", "farcir", "faucher", "faxer", "fendre", "ferrer", "ficeler", "figer", "figurer", "filer", "filmer", "filtrer", "finir", "fixer", "fleurir", "fluer", "foncer", "fondre", "forer", "forger", "former", "fraiser", "frayer", "frimer", "friper", "frire", "fuguer", "fuir", "fumer", "gager", "gagner", "gainer", "galber", "garder", "garer", "gaver", "gazer", "geler", "gémir", "gerber", "germer", "gifler", "glaiser", "givrer", "glacer", "glaner", "gloser", "gober", "gommer", "gorger", "gracier", "gratter", "graver", "grener", "griffer", "griller", "grimper", "grogner", "gronder", "grouper", "gruger", "guider", "habiter", "hacher", "haler", "haleter", "hanter", "happer", "hausser", "hennir", "heurter", "hisser", "hocher", "honorer", "huer", "huiler", "humer", "hurler", "ignorer", "imbiber", "imiter", "imputer", "inciter", "indexer", "influer", "infuser", "inhumer", "innover", "inviter", "ioder", "irriter", "isoler", "jaser", "jauger", "jeter", "jongler", "jouer", "jubiler", "juger", "jumeler", "jurer", "lacer", "lamer", "laminer", "laper", "larder", "larguer", "laver", "lester", "lever", "lier", "ligoter", "limer", "lire", "lister", "livrer", "loger", "louer", "louper", "lover", "luire", "lustrer", "lutter", "luxer", "malaxer", "manger", "manquer", "marrer", "marteler", "masser", "mener", "merder", "migrer", "mijoter", "mimer", "miner", "miser", "miter", "mixer", "moisir", "montrer", "mordre", "motiver", "moudre", "mouler", "muer", "mugir", "munir", "murer", "muter", "mutiler", "nacrer", "nager", "navrer", "nicher", "nier", "niveler", "noter", "nuire", "obliger", "obturer", "occuper", "omettre", "onduler", "opter", "orner", "oser", "ouater", "ourler", "outrer", "ouvrir", "oxyder", "paginer", "palper", "paner", "panser", "parer", "parier", "parler", "partir", "passer", "paver", "peindre", "peiner", "peler", "pencher", "pendre", "penser", "percer", "perdre", "perler", "peser", "pester", "peupler", "piger", "piler", "piquer", "pisser", "pister", "pivoter", "plagier", "planer", "planter", "plaquer", "pleurer", "plier", "plumer", "pocher", "pointer", "polir", "pomper", "poncer", "pondre", "porter", "poser", "poster", "poudrer", "prendre", "primer", "priver", "publier", "puer", "puiser", "punir", "purger", "rabattre", "raboter", "racler", "radoter", "rafler", "rager", "raidir", "ramener", "ramer", "ramper", "ranger", "ranimer", "raquer", "raser", "rater", "ravager", "ravaler", "ravir", "raviser", "rayer", "rebuter", "recoudre", "redire", "refuser", "rejeter", "relaxer", "relever", "relier", "relire", "reluire", "remuer", "rendre", "renier", "rentrer", "réparer", "reposer", "rester", "revoir", "rider", "rimer", "rincer", "riper", "rire", "risquer", "river", "roder", "rogner", "rompre", "ronfler", "ronger", "rougir", "rouler", "ruer", "rugir", "ruiner", "ruminer", "ruser", "rutiler", "saboter", "sabrer", "sécher", "sacrer", "saillir", "saisir", "saler", "salir", "saluer", "saper", "saquer", "saturer", "sauter", "sauver", "savoir", "scander", "sceller", "scier", "scruter", "seller", "sembler", "semer", "sentir", "seoir", "serrer", "sertir", "servir", "sevrer", "siffler", "signer", "singer", "situer", "skier", "soigner", "solder", "sombrer", "sommer", "sonder", "songer", "sonner", "sortir", "soucier", "souder", "souper", "soyer", "stagner", "statuer", "stocker", "subir", "sucrer", "suer", "suffire", "suinter", "suivre", "surfer", "surgir", "survivre", "tabler", "tacher", "tailler", "taire", "taler", "tanguer", "taper", "tapoter", "tarder", "tarer", "targuer", "tarir", "tasser", "taxer", "tendre", "tenir", "tenter", "ternir", "tester", "tinter", "tirer", "tisser", "titrer", "toiser", "tomber", "tondre", "toper", "tordre", "tosser", "tracer", "tracter", "trahir", "traiter", "tramer", "traquer", "tremper", "tricher", "trier", "troquer", "trotter", "trouer", "trouver", "truffer", "tuber", "tuer", "tuner", "unifier", "unir", "urger", "user", "usiner", "usurper", "vaincre", "valoir", "valser", "vanner", "vanter", "vaquer", "varier", "vendre", "venger", "venir", "venter", "verdir", "vernir", "verser", "vexer", "vibrer", "vicier", "vider", "virer", "viser", "visser", "vitrer", "vivre", "voguer", "voir", "voler", "vomir", "voter", "vouloir", "zinguer", "zipper", "zoner", "épurer"
		]
	}

	/**
	 * renvoie une liste d'adjectifs construit a partir d'un verbe
	 *
	 *	@param string v 		un verbe
	 *
	 *	@return array
	 */
	var Verbe_to_Adjectif = function (v) {
		if (v.endsWith ('er')) { // créer des adjectifs avec les verbes en 'er'
			racine = v.substring (0, v.length - 2);
			return [racine + 'é.es.s',
							racine + 'eu.ses.rs',
							racine + 'ant.es.s'];
		}
		if (v.endsWith ('ir')) { // créer des adjectifs avec les verbes en 'ir'
			racine = v.substring (0, v.length - 2);
			return [racine + 'i.es.s',
							racine + 'issant.es.s'];
		}
		if (v.endsWith ('re')) { // créer des adjectifs avec les verbes en 're'
			racine = v.substring (0, v.length - 2);
			return [racine + 'u.es.s'];
		}
	};

	// créer des adjectifs avec les verbes, et les ajoute au tableau
	for (let v of words.v) {
		var nouveau_adj = Verbe_to_Adjectif (v);
		if (nouveau_adj) {
			words.c = words.c.concat (nouveau_adj);
		}
	}

	/**
	 * accorde un adjectif en fonction d'un nom
	 *
	 *	@param string n 		un nom au pluriel, au format 'la bananes'
	 *	@param string a 		un adjectif, au format 'pourri.es.s'
	 *
	 *	@return string
	 */
	words.Accord = function (n, a) {
		if (a.indexOf ('.') == -1) {return a;}	// pas de point: renvoie tel quel.
		var parts = a.split ('.');
		if (n.startsWith ('la ')) {
			return parts[0] + parts[1];		// base + suffixe_feminin
		} else {
			return parts[0] + parts[2];		// base + suffixe_masculin
		}
	};

	/**
	 * renvoie un mot avec une lettre au hazard mis en majuscule
	 *
	 *	@param string s 		un mot
	 *
	 *	@return string
	 */
	words.RandomCase = function (s) {
		var idx = Math.floor (Math.random() * s.length);
		return s.substr (0, idx) + s[idx].toUpperCase () + s.substr (idx + 1);
	};

	/**
	 * calcul le nombre de combinaisons possibles
	 *
	 *	@return rien, ca marche pas...
	 */
	words.strongitude = function () {
		nb_possibilites =	words.v.length *	// nombre de verbe
											words.n.length * 	// nombre de sujets
											words.c.length *	// nombre d'adjectifs
											89	*							// nombre de chiffres
											18 * 18;					// majuscules : moyenne de char au carré
		// marche pas: la fonction log de js ne calcule pas le base 62?
		equivalence_alea = getBaseLog (nb_possibilites, 62); // 62 = 26 lettres * 2 + 10 chiffres
		}

	return words;
}

function getBaseLog(x, y) {
	//src: https://developer.mozilla.org/fr/docs/Web/JavaScript/Reference/Global_Objects/Math/log
    return Math.log(y) / Math.log(x);
}
