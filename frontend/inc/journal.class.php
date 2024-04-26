<?php
/**
	* Moteur de journalisation d'activité utilisateur minimaliste
	*
	* @copyright (c) 2022 Cyril Fleury
	* @license http://opensource.org/licenses/gpl-license.php GNU Public License
	*
	*/

if (! extension_loaded ('sqlite3')) {
	die ('L\'extension SQLITE3 n\'est pas installé/chargé.');
}

class JournalEvent {
	public $db = null;		// object sqlite
	public $table = null;		// nom de la table
	public $cols = null;		// liste des colonnes

	/*
	 * constructeur de classe
	 *
	 * @param string $nom_base 		fichier contenant la bdd
	 * @param string $nom_table  	la table sql
	 * @param string $cols		 		tableau listant les colonnes a utiliser
	 *
	 * @return null
	 */
	function __construct ($nom_base, $nom_table = 'EventLog', $cols = false) {
		$fichier_base = __DIR__ . "/$nom_base.sqlite";

		$this->table = $nom_table;
		$this->cols = $cols;

		try {		// ouvre la base sql pour voir si elle existe
			$this->db = new SQLite3 ($fichier_base, SQLITE3_OPEN_READWRITE);
		} catch (Exception $e) {
			$this->db = new SQLite3 ($fichier_base, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
			$this->initialize_tables();
		}
	}

	/*
	 * cree la structure de la table
	 *
	 * @return null
	 */
	function initialize_tables() {
		// definit un schema de base par defaut, si il n'a pas été fourni.
		if (! $this->cols) {
			$this->cols = array (
				'TimeStamp'			=> 'INTEGER',
				'MachineSource'	=> 'TEXT',
				'Utilisateur'		=> 'TEXT',
				'Niveau'				=> 'INTEGER', // E_ERROR=1, E_WARNING=2, E_NOTICE=8
				'Action'				=> 'TEXT',
			);
		}

		// construit la requete
		$sql = 'CREATE TABLE IF NOT EXISTS '. $this->table .' (';
		foreach ($this->cols as $nom_col => $type_col) {
			$sql .= $nom_col .' '. $type_col .', ';
		}
		$sql = rtrim($sql, ', ') . ');';

		// creer la table
		return $this->db->exec ($sql);
	}

	/*
	 * met a jour une valeur dans le tableau colonne
	 *
	 * @param string $col 		colonne a modifier
	 * @param mixed $donnee
	 *
	 * @return null
	 */
	function set_donnee ($col, $donnee) {
		$this->cols[$col] = htmlspecialchars ($donnee);
	}

	/*
	 * enregistre un event dans la base
	 *
	 * @param string $action 		description de l'event
	 * @param string $niveau  	niveau d'alerte
	 *
	 * @return string
	 */
	function creer ($action, $niveau = 8) {
		$this->set_donnee ('Niveau', $niveau);
		$this->set_donnee ('Action', htmlspecialchars ($action));
		$this->set_donnee ('TimeStamp', time());

		$sql = 'INSERT INTO ' . $this->table . ' (' .
			implode(', ', array_keys($this->cols)) . ') VALUES (:' .
			implode(', :', array_keys($this->cols)) . ');';
		$stmt = $this->db->prepare ($sql);

		foreach ($this->cols as $nom_col => $donnee) {
			$stmt->bindValue (':'. $nom_col, $donnee);
		}
		$stmt->execute();
		return $this->db->lastErrorMsg(); // stmt->getSQL (true) n'est pas compatible < 7.4
	}

	/*
	 * renvoie la liste des events
	 *
	 * @param string $niveau  	niveau d'alerte
	 *
	 * @return array
	 */
	function get_events ($niveau = 8) {
		$evt = array();
		// mappe les niveaux avec les couleurs bootstrap
		$n = array(1=>'danger', 2=>'warning', 4=>'info', 8=>'transparent');

		// interroge la base
		$sql = 'SELECT
				*,
				STRFTIME (\'%Y-%m-%d %H:%M\', TimeStamp, \'unixepoch\', \'localtime\') AS Date
				FROM ' . $this->table .
			' WHERE Niveau <= '. $niveau .
			' ORDER BY rowid DESC' .
			' LIMIT 1000;';

		$results = $this->db->query ($sql);

		while ($row = $results->fetchArray (SQLITE3_ASSOC)) {
			$row['Niveau'] = $n[$row['Niveau']];
			$evt[] = array_change_key_case ($row, CASE_UPPER);
		}
		return $evt;
	}
}
