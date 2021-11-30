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
?>
<div class="container">
	<h3>Sélectionner la classe :</h3>
<table style="margin:auto;">


<?php
$list = $ldap->get_classes();

$secd = array();
$prem = array();
$term = array();
$autr = array();

foreach ($list as $entry) {
	// certaines classes sont nommées _1FOOBAR
	$section = ($entry[0] == '_')? $entry[1] : $entry[0];
	
	// rempli les array correspondants
	switch ($section) {
		case "1": 
			$prem[] = '<a class="btn btn-outline-success" style="width:100%;margin:2px auto;" href="?pg=classe&id=' . base64_encode($entry) . '" />'. $entry .'</a>';
			break;
		case "2": 
			$secd[] = '<a class="btn btn-outline-success" style="width:100%;margin:2px auto;" href="?pg=classe&id=' . base64_encode($entry) . '" />'. $entry .'</a>';
			break;
		case "T": 
			$term[] = '<a class="btn btn-outline-success" style="width:100%;margin:2px auto;" href="?pg=classe&id=' . base64_encode($entry) . '" />'. $entry .'</a>';
			break;
		default:  	
			$autr[] = '<a class="btn btn-outline-success" style="width:100%;margin:2px auto;" href="?pg=classe&id=' . base64_encode($entry) . '" />'. $entry .'</a>';
			break;
	} 
}
echo '<tr>';
echo '	<td style="vertical-align: top;padding: 0 20px;">'. implode('<br />', $secd) .'</td>';
echo '	<td style="vertical-align: top;padding: 0 20px;">'. implode('<br />', $prem) .'</td>';
echo '	<td style="vertical-align: top;padding: 0 20px;">'. implode('<br />', $term) .'</td>';
echo '	<td style="vertical-align: top;padding: 0 20px;">'. implode('<br />', $autr) .'</td>';
echo '</tr>';
?>
</table>
</div>