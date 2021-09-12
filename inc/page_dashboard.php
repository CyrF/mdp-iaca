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
<table>
<tr>
	<th>Selectionner la classe :</th>
</tr>

<?php
$list = $ldap->get_classes();

sort($list);
foreach ($list as $entry) {
	echo '	<tr><td><a href="?pg=classe&id=' . base64_encode($entry) . '" />'. $entry .'</td></tr>';
}
?>
</table>
</div>