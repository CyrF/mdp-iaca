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
 
 
/**
 * Variables pour configurer l'application
 *
 *	@param int 'idletime'		Duree en secondes avant deconnexion automatique
 *	@param str 'AD_ServerIP'	Ip du serveur IACA et controleur du domaine
 *	@param str 'AD_Domain'		nom DNS du domaine (utilisé pour se connecter ex: domaine\prof)
 *	@param str 'AD_Chemin'		Chemin de la racine LDAP
 *	@param str 'AD_OU_ELEVES'	Chemin de l'UO contenant les eleves (sensible à la case)
 *	@param str 'AD_ou_Autorise'	UO ou utilisateur autorisé a acceder a l'outil (sensible à la case), 
 *								séparé par des "|" si plusieurs utilisateurs  
 *								ex:	OU=ENSEIGNANTS,DC=Contoso,DC=fr|OU=PROFS,DC=Contoso,DC=fr|CN=ThisGuy,OU=NiceGroup,DC=Contoso,DC=fr
 *
 */ 
 
$_CONF = array (			
	'idletime'			=> 5*60,
	'AD_ServerIP'		=> '10.100.00.65',
	'AD_Domain'			=> 'Contoso',
	'AD_Chemin'			=> 'dc=Contoso,dc=LOCAL',
	'AD_OU_ELEVES'		=> "OU=ELEVES,OU=Users,OU=Site par défaut,OU=IACA,DC=Contoso,DC=fr",
	'AD_ou_Autorise'	=> 'OU=TEACHERS,OU=Users,OU=Site par défaut,OU=IACA,DC=Contoso,DC=fr');	

/* le serveur iaca doit être configuré suivant la doc https://www.iacasoft.fr/outils/TCPComIACA/index.htm 
 * le module php_ldap doit être activé dans php.ini sur le serveur web hebergeant l'appli */