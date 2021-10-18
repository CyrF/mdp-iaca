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
$list = $ldap->get_usergroups( $classe );

echo "<h3 class='d-print-none'>Attendez que tous les petits chats récupère les mots de passe avant de lancer l'impression...</h3>";

foreach ($list as $entry) {
	$b64_uid = b64($entry['Identifiant']);
	?>
	
<div class="vignet">
	<div class="vignet-classe"><?php echo $classe; ?></div>
	<div class="nom condensed"><?php echo $entry['NomComplet']; ?></div>
	
	<div class="">
		<span class="condensed desc short">Identifiant réseau : </span>
		<?php echo strtolower( $entry['Identifiant'] ); ?>
	</div>
	<div class="nom" style="letter-spacing: -0.065em;">
		<span class="condensed desc">Sur le portable #MonOrdiAuLycee et l'accès à office.com, utilisez<br></span>
		<?php echo $entry['Compte365']; ?>
	</div>
	
	<div class="">
		<span class="condensed desc short">Mot de passe réseau : </span>
		<span id="<?php echo 'pwd_' . $b64_uid; ?>" ></span>
		<img class="d-print-none" src="runcat2.gif" onload="getMdp(this.id);" id="<?php echo $b64_uid; ?>" />
	</div>
</div>

<?php
}; //foreach

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

<script>
/**
 * envoie une requete AJAX pour recuperer le mdp
 *
 *	@param string $catid	nom d'utilisateur dans l'AD
 *
 *	@return null
 */
function getMdp( catid ) {
	var cat = document.getElementById( catid );
	var pwd = document.getElementById( 'pwd_' + catid );
	// requete AJAX
	var myRequest = new Request('ajax.php?get=' + catid);
	fetch(myRequest).then(function(response) {
		return response.text().then(function(text) {
			if (text == '**************') {
				// masque dans iaca, affiche rien \ö/
				cat.style.display = "none"; 
			} else if (text == "Votre session a expirée.") {
				// renvoie a l'ecran de login
				window.location.replace("?sessionexpired");
			} else {
				// affichage du mdp
				cat.style.display = "none"; 
				pwd.innerHTML = text;
			}
		});
	});	
}
</script>

<style>
/* formattage des vignettes pour l'impression */	 

* {margin:0;padding:0;}
.vignet {
	background-image:url("inc/background-vignet.png");
	background-size:contain;
	height:			4.4cm;
	width:			9.0cm;
	padding:		0.1cm;
	display:		inline-block;
	margin:			0.05cm;
	margin-left:	0.1cm;	
	margin-bottom:	0.1cm;	
	font: 1.08rem "Times New Roman";
	text-overflow: "";
  white-space: nowrap;
  overflow: hidden;
  border:1px solid grey;
}
.vignet-classe {
	text-align:right;
	color:grey;
	font-style:italic;
	font-variant:petite-caps;
}
.condensed {
  font-stretch: 50%;    /*  ca marche pas/plus */
  letter-spacing: -0.045em;
}
.desc {
	font-style:italic;
	font-size:smaller;
}
.short {
	min-width:35%;
	display:		inline-block;}

.vignet .nom {
	padding-bottom:10px;
}
@media print{
	.d-print-none {
		display: none !important;
	}
}
</style>
