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
 
$_CONF = array (			
	'idletime'			=> 2*60, 			/* Duree en secondes avant deconnexion automatique */
	'AD_ServerIP'		=> '10.100.00.65',	/* Ip du serveur domaine */
	'AD_Domain'			=> 'domaine',	/* nom DNS du domaine */
	'AD_Chemin'			=> 'dc=domaine,dc=LOCAL',	/* Chemin de la racine LDAP */
	'AD_OU_ELEVES'		=> "OU=ELEVES,OU=Users,OU=Site par défaut,OU=IACA,DC=domaine,DC=local",	/* Chemin de l'UO contenant les eleves */
	/* UO ou utilisateur autorisé a acceder a l'outil, séparé par | */
	'AD_ou_Autorise'	=> 'OU=PROFS,OU=Users,OU=Site par défaut,OU=IACA,DC=bourdonnieres,DC=local');	

/* le serveur iaca doit être configuré suivant la doc https://www.iacasoft.fr/outils/TCPComIACA/index.htm */