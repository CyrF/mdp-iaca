<?php
/**
* Moteur de template HTML minimaliste
*
* @copyright mix portion de ma class v1 avec ci-dessous (c) 2022 Cyril Fleury
* @copyright compiling template class (c) 2005 phpBB Group
* @copyright conditional implementation (c) 2001 ispi of Lincoln Inc
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
* Derivé d'une ancienne version du code de phpBB, lui-même inspiré par le
* moteur de template PHPLib et utilise des portions de code de Smarty.
*/
class Modele_HTML {
	public $show_debug;
	public $tpl;			// string, stocke la page finie
	public $vars;			// tableau de toutes les valeurs a mettre dans le template
	var $var_root;		// tableau des valeurs qui ne sont pas dans une boucle

	/*
	 * constructeur de classe
	 *
	 * @param bool $debug  ajoute des commentaires dans la sortie HTML pour debugger le code
	 *
	 * @return null
	 */
	function __construct ($debug = false) {
		$this->var_root = &$this->vars['.'];
		$this->show_debug = $debug;
		$this->tpl = array();
	}

	/*
	 * chemin vers le template
	 *
	 * @param string $tpl  nom du template a charger sans le chemin ni l'extension
	 *
	 * @return string
	 */
	function get_path ($tpl) {
		return 'tpl/' . $tpl . '.html';
	}

	/*
	 * lit le fichier et execute les instructions et remplacement
	 *
	 * @param string $tpl  nom du template a charger sans le chemin ni l'extension
	 * @param bool $page  retourne la page finie.
	 *
	 * @return array		page html finale
	 */
	function parser ($tpl = false, $page = false) {
		$tpl_file = ($tpl) ? $this->get_path ($tpl) : $this->path;

		$handle = @fopen ($tpl_file, "r");
		if ($handle) {
			$source = fread ($handle, filesize ($tpl_file));
			fclose ($handle);
			if ($page) {
				$this->tpl = $this->traiter_lignes ($source);
				return $this->tpl;
			} else {
				$this->tpl = $this->traiter_lignes ($source);
			}
		}
	}

	/*
	 * affiche la page finie.
	 *
	 * @return null
	 */
	function render() {
		if (is_array($this->tpl)) {
			echo implode("\n", $this->tpl);
		} else {
			echo $this->tpl;
		}
		if ($this->show_debug) {
			echo "\n\n" . '<!-- DEBUG: variable dump' . "\n";
			var_dump ($this->vars);
			echo '-->' . "\n";
		}
	}

	/*
	 * ajoute des valeurs pour les variables qui seront remplacées
	 *
	 * @param string|array $bloc  nom du bloc dans le template | tableau associatif
	 * @param array $newvars      tableau associatif
	 *
	 * @return null
	 */
	function add_vars ($bloc, $newvars = false) {
		if (! is_array($newvars)) {
			$newvars = $bloc;   // copie du tableau recu dans $bloc
			$bloc = '.';	// ces valeurs seront générales
		}
		if (isset ($this->vars[$bloc])) {
			$this->vars[$bloc] = array_merge ($this->vars[$bloc], $newvars);
		} else {
			$this->vars[$bloc] = $newvars;
		}
	}

	/*
	 * analyse le code du template pour generer la page finale
	 *
	 * @param string $code  HTML a analyser
	 * @param array $vars   tableau associatif contenant les variables a inserer
	 * @param string $espace_nom   de la boucle courante
	 *
	 * @return array		page html finale
	 */
	function traiter_lignes ($code, $vars = false, $espace_nom = false) {
		if (!$vars) {$vars = &$this->vars;}
		$page = array();							// stocke la page finale
		$est_dans_boucle = false;			// stocke le nom de la boucle
		$ne_pas_masquer = array (true);		// Booleen pour la structure conditionelle IF/ELSE/ENDIF
		$contenu_boucle = array();		// stocke l'intérieur d'une boucle

		// stocke les commentaires html
		$blocks = array();
		preg_match_all ('#<!-- ([^<].*?) (.*?)? ?-->#', $code, $blocks, PREG_SET_ORDER);
		// format sortie regex: array (['<!-- comm complet -->', 'mot-clef', 'params'], ...)

		// stocke le contenu entre 2 commentaires
		$text_blocks = preg_split ('#<!-- [^<].*? (?:.*?)? ?-->#', $code);
		// format sortie regex: array ('mix_html', ...)

		for ($curr_tb = 0, $tb_size = sizeof ($text_blocks); $curr_tb < $tb_size; $curr_tb++) {
			if ($curr_tb > 0 && isset ($blocks[$curr_tb -1])) {
				$block_val = &$blocks[$curr_tb -1];
				$instruction = $block_val[1];
				$params = $block_val[2];
			} else {
				$instruction = false;
				$params = '';
			}
			if ($est_dans_boucle) {
				if ($instruction == 'STOP' && $params == $est_dans_boucle) {
					// fin de la boucle et traitement des donnees
					if (isset ($vars[$est_dans_boucle])) {
						foreach ($vars[$est_dans_boucle] as $boucle_data) {
							$b = implode ('', $contenu_boucle[$est_dans_boucle]);
							$this->debug ($page, 'debut de la boucle '. $est_dans_boucle);
							$b = $this->traiter_lignes ($b, $boucle_data, $est_dans_boucle . '.');
							$this->debug ($page, 'fin de la boucle '. $est_dans_boucle);
							$page[] = implode ("\n", $b);
						}
					}
					$est_dans_boucle = false;
					if (! in_array (false, $ne_pas_masquer)) {
						$this->remplace_vars ($text_blocks[$curr_tb], $espace_nom, $vars);
						$this->debug ($page, 'contenu restant après le stop');
						$page[] = $text_blocks[$curr_tb];
					}
					continue;
				} else {
					// copie le contenu de la boucle pour traitement ulterieur
					$this->debug ($contenu_boucle[$est_dans_boucle], 'copie le contenu de la boucle '. $est_dans_boucle);
					$contenu_boucle[$est_dans_boucle][] = $block_val[0] . $text_blocks[$curr_tb];
				}
			} else {
				switch ($instruction) {
					case 'BEGIN':
						// copie le contenu de la boucle pour traitement ulterieur
						$est_dans_boucle = $params;
						$this->debug ($contenu_boucle[$est_dans_boucle], 'copie le debut de la boucle '. $est_dans_boucle);
						$contenu_boucle[$est_dans_boucle][] = $text_blocks[$curr_tb];
						break;
					case 'IF_DEFINED':
						// evalue la condition
						$variable = explode ('.', $params);
						if ($variable[0] . '.' == $espace_nom) {
							$this->debug ($page, 'verifie l\'existence de la var. '. $variable[1] .' dans ' . $variable[0]);
							$ne_pas_masquer[] = ! empty ($vars[$variable[1]]);
						}else{
							$this->debug ($page, 'verifie l\'existence de la var. globale '. $params);
							$ne_pas_masquer[] = ! empty ($this->var_root[$params]);
						}
						if (! in_array (false, $ne_pas_masquer)) {
							$this->remplace_vars ($text_blocks[$curr_tb], $espace_nom, $vars);
							$this->debug ($page, 'condition vrai la var. '. $params .' existe');
							$page[] = $text_blocks[$curr_tb];
						}
						break;
					case 'ELSE':
						// Inverse le status de la condition actuelle
						$ne_pas_masquer[array_key_last ($ne_pas_masquer)] = ! end ($ne_pas_masquer);
						if (! in_array (false, $ne_pas_masquer)) {
							$this->remplace_vars ($text_blocks[$curr_tb], $espace_nom, $vars);
							$this->debug ($page, 'inverse la condition actuelle');
							$page[] = $text_blocks[$curr_tb];
						}
						break;
					case 'ENDIF':
						// retire la condition
						array_pop ($ne_pas_masquer);
						if (! in_array (false, $ne_pas_masquer)) {
							$this->remplace_vars ($text_blocks[$curr_tb], $espace_nom, $vars);
							$this->debug ($page, 'fin de la condition');
							$page[] = $text_blocks[$curr_tb];
						}
						break;
					case 'INCLUDE':
						// inclus du contenu d'un autre template
						if (! in_array (false, $ne_pas_masquer)) {
							$this->debug ($page, 'insere le template ' . $params );
							$buffer = $this->parser ($params, true);
							$page[] = implode ("\n", $buffer);
							$this->remplace_vars ($text_blocks[$curr_tb], $espace_nom, $vars);
							$this->debug ($page, 'contenu restant après le include');
							$page[] = $text_blocks[$curr_tb];
						}
						break;
					default:
						// copie le contenu
						if (! in_array (false, $ne_pas_masquer)) {
							$this->remplace_vars ($text_blocks[$curr_tb], $espace_nom, $vars);
							if ($curr_tb > 0) {
								$this->debug ($page, 'copie le contenu après un tag');
								$page[] = $block_val[0] . $text_blocks[$curr_tb];
							} else {
								$this->debug ($page, 'copie le contenu');
								$page[] = $text_blocks[$curr_tb];
							}
						}
				}
			}
		}
		$this->debug ($page, 'fin de l\'analyse du namespace ' . $espace_nom);
		return $page;
	}

	/*
	 * insere dans le rendu final des messages sous forme de commentaire HTML
	 *
	 * @param by_ref array $pg		le tableau $this->pl?
	 * @param string $msg					un commentaire a insérer
	 *
	 * @return null
	 */
	function debug (&$pg, $msg) {
		if ($this->show_debug && isset ($pg)) {
			$pg[] = '<!--DEBUG_' . sizeof ($pg) . ' : ' . $msg  . '-->';
		}
	}
	/*
	 * remplace les variables dans une portion du template
	 *
	 * @param by_ref string $text_blocks	HTML a analyser
	 * @param string $curr_block					nom de la boucle courante
	 * @param by_ref array $boucle_data		variables a inserer
	 *
	 * @return null
	 */
	function remplace_vars (&$text_blocks, $curr_block = false, &$boucle_data = false) {
		$varrefs = array(); // stocke les variables avec un espace de nom, ex: {espace_nom.VARIABLE_1}
		$root_refs = array(); // stocke les variables sans espace de nom, ex: {Une_Variable_1}

		preg_match_all ('#\{((?:[a-z0-9\-_]+\.)+)(\$)?([A-Z0-9\-_]+)\}#', $text_blocks, $varrefs, PREG_SET_ORDER);
		// format sortie regex: array (['{var complete}', 'espace_de_nom.', '$', 'variable'], ...)

		foreach ($varrefs as $var_val) {
			$b64 = (substr ($var_val[1], 0, 4) == 'b64_');			// prefixe pour convertir la var en base64
			$nl2br = (substr ($var_val[1], 0, 6) == 'nl2br_');	// prefixe pour remplacer les ; par des <br>
			// contient un point a la fin !
			$namespace = ($b64) ? substr ($var_val[1], 4, -1) : rtrim ($var_val[1], '.');
			$namespace = ($nl2br) ? substr ($namespace, 6) : $namespace;
			$varname = $var_val[3];
			if ($curr_block && $curr_block == $namespace . '.') {
				if (isset ($boucle_data[$varname])) {
					$valeur = ($b64) ? $this->to_b64_js ($boucle_data[$varname]) : $boucle_data[$varname];
					$valeur = ($nl2br) ? str_replace ('; ', "<br>\n", $valeur) : $valeur;
					$text_blocks = str_replace ($var_val[0], $valeur, $text_blocks);
				}
			}
			if (isset ($this->vars[$namespace][$varname]) && !is_array ($this->vars[$namespace][$varname])) {
				$valeur = ($b64) ? $this->to_b64_js ($this->vars[$namespace][$varname]) : $this->vars[$namespace][$varname];
				$valeur = ($nl2br) ? str_replace ('; ', "<br>\n", $valeur) : $valeur;
				$text_blocks = str_replace ($var_val[0], $valeur, $text_blocks);
			}
		}

		preg_match_all ('#\{([a-z0-9\-_]*)\}#is', $text_blocks, $root_refs, PREG_SET_ORDER);
		// format sortie regex: array (['{var complete}', 'variable'], ...)

		for ($i = 0, $j = sizeof ($root_refs); $i < $j; $i++) {
			if (isset ($this->var_root[$root_refs[$i][1]])) {
				$text_blocks = str_replace ($root_refs[$i][0], $this->var_root[$root_refs[$i][1]], $text_blocks);
			}
		}
	}

	/*
	 * encode une chaine utf8 en base64 compatible avec javascript
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	function to_b64_js ($string) {
		return rtrim (base64_encode (utf8_decode ($string)), '=');
	}
}

// polyfill pour PHP <= 7.3.0 (src: commentaire dans documentation php)
if( !function_exists('array_key_last') ) {
    function array_key_last(array $array) {
        if( !empty($array) ) return key(array_slice($array, -1, 1, true));
    }
}
