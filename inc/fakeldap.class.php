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
 * Classe pour gerer un annuaire ldap
 *
 */
class AnnuaireLDAP {
	private $ldap_server;
	private $ldap_base_dn;
	private $bound;

	function __construct($dc, $admin, $pass, $racine) {
		$this->ldap_server	= 'localhost';
		$this->ldap_base_dn	= $dc;
		$this->bound		= false;
	}

	/**
	 * Tente de s'authentifier avec un compte LDAP
	 *
	 *	@param string $username	nom d'ouverture de session.
	 *	@param string $password mot de passe.
	 *
	 *	@return bool true si connexion ok
	 */
	function authentifier($username, $password) {
		if ($username == "prof" && $password == "prof") {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Retourne les utilisateurs
	 *
	 *	@param string $uid		identifiant de l'utilisateur
	 *
	 *	@return array
	 */
	function get_users_info($uid='*'){
		if ($uid == "prof") {
			return array(
				'cn' => "Hilaire PROF",
				'Compte365' => 'prof@pdtor.dS3iose.a645caan5i4ule0ley',
				'uid' => "prof");
		}
	}

	/**
	 * Retourne les classes non vides
	 *
	 *
	 *	@return array
	 */
	function get_classes(){
		return array(
			'Justice League', '_1Gotham City', '2Metropolis', 'Themyscira',
			'TSector 666', '1Atlantis', '_2Asgard', 'TManhattan', '_TAvengers',
			'1Wakanda', "2Hell's Kitchen", 'Guardians of the Galaxy'
		);
	}

	/**
	 * Cree un login credible a partir du nom
	 *
	 *	@param string $nom		nom complet de l'utilisateur, au format "DOE John Jane"
	 *	@param bool $long		format "john.doe42" si vrai, sinon "doej2"
	 *
	 *	@return string
	 */
	private function Creer_Login( $nom, $long = false) {
		$n = explode(' ', $nom, 3);
		if ($long) {
			//format elyco
			return strtolower( $n[1] . '.' . $n[0] ) . rand(0,9999);
		} else {
			//format iaca
			$nom = str_replace('_', '-', substr($n[0], 0, 11));
			return strtolower( $nom . substr($n[1], 0, 1) ) . rand(0,10);
		}
	}

	/**
	 * Cree un mot de passe aleatoire
	 *
	 *	@param int $n		longueur du mot de passe
	 *
	 *	@return string
	 */
	private function Creer_Pass( $n=5 ) {
		return substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',$n)),0,$n);
	}

	/**
	 * Cree un utilisateur aleatoire, au format "DOE John Jane"
	 *
	 *	@return string
	 */
	private function Creer_NomAleatoire() {
		$prenom = array(
			array(
				'Arthur', 'Bruce', 'Victor', 'John', 'Yondu', 'Alfred',
				'Ray', 'Oliver', 'Billy', 'Stephen', 'Kal', "T'Challa",
				'Tony', 'Henry',  'Steve',  'Thor', 'Peter', 'Hal',
			), array(
				'Dinah', 'Ivy', 'Lois', 'Diana', 'Barbara', 'Maria',
				'Kara', 'Harleen', 'Cassandra', 'Janet', 'Wanda',
				'Carol', 'Natasha', 'Gamora', 'Mötley'
			)
		);
		$famille = array(
			'Curry', 'Wayne', 'Drake', 'Stone', 'Jordan', 'Stewart', 'Lane',
			'Allen', "Zor'El", 'Prince', 'Pennyworth', 'Quinzel', 'Odinson',
			'Gordon', 'Queen', 'Palmer', 'Batson', 'Strange', 'Cain', 'El',
			'Stark', 'Jonzz', 'Van_Dyne', 'Banner', 'Rogers', 'Danvers',
			'Maximoff', 'Romanoff', 'Parker', 'de_Guadalupe_Santiago', 'Pym',
			'Blue Öyster', 'Mötorhead', 'Crüe', 'Queensrÿche', 'Zerø'
		);

		$nom  = strtoupper( $famille[rand( 0 , count($famille) -1 )] ) . ' ';
		$genre = rand(0,1);
		$nom .= $prenom[$genre][rand( 0 , count($prenom[$genre]) -1 )] . ' ';
		$nom .= $prenom[$genre][rand( 0 , count($prenom[$genre]) -1 )] . ' ';
		$nom .= $prenom[$genre][rand( 0 , count($prenom[$genre]) -1 )];


		return $nom;
	}

	/**
	 * Retourne les utilisateurs dans a une classe
	 *
	 *	@param string $grp	nom du groupe classe
	 *
	 *	@return array
	 */
	function get_usergroups( $grp ) {
		$list = array();
		for($i=0; $i<rand(1,35); $i++){
			$nom = $this->Creer_NomAleatoire();
			$id = $this->Creer_Login( $nom );
			array_push($list, array(
				'NomComplet' => str_replace('_', ' ', $nom),
				'Identifiant' => $id,
				'Compte365' => $id . '@pdtor.dS3iose.a645caan5i4ule0ley',
				'logoncount' => rand(0,10),
				'pwdLastSet' => rand(0,10),
				'Classe' => $grp,
				'Id_ent' => $this->Creer_Login( $nom , true),
				'Pw_ent' => $this->Creer_Pass(),
				));
		}
		sort($list);
		return $list;
	}


	/**
	 * Recherche un ou des utilisateurs
	 *
	 *	@param string $cherche	utilisateur a rechercher
	 *
	 *	@return array
	 */
	function find_users($cherche) {
		$users = $this->get_usergroups( "foobar" );
		$list = array();
		foreach( $users as $u ) {
			if( stristr( $u['NomComplet'], $cherche ) != false ) {
				array_push( $list, $u );
			}
		}
		return $list;
	}

}