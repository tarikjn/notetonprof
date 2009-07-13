<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

$top_nb = 100;
$place = 0;

$query =
	"SELECT etablissements.id, etablissements.nom, cursus, secondaire, villes.nom AS commune, COUNT( DISTINCT notes.id ) AS notes ".
	"FROM etablissements, villes, professeurs ".
	"LEFT JOIN notes ON notes.prof_id = professeurs.id AND notes.status = 'ok' ".
	"WHERE villes.id = etablissements.ville_id && professeurs.etblt_id = etablissements.id && notes.prof_id = professeurs.id AND professeurs. status = 'ok' AND etablissements.status = 'ok' ".((Admin::MOD_SCHOOL)?" && etablissements.moderated = 'yes'":"")." ".
	"GROUP BY etablissements.id ".
	"ORDER BY notes desc, etablissements.nom ".
	"LIMIT ".$top_nb;
$result = DBPal::query($query);

// Controller-View limit
DBPal::finish();

$title = "Classement fréquentation";
?>
<? require("tpl/haut.php"); ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<h2>Classement des établissements par fréquentation</h2>
			<p>Top des <?=$top_nb?> établissements qui ont obtenu le plus de notes :</p>
			<table class="grille moyenne">
				<thead>
					<tr>
						<th class="nbre">Rang</th>
						<th class="nom" style="padding-left: 2em;">Établissement</th>
						<th class="nbre" style="padding-right: .5em;">Évaluations</th>
					</tr>
				</thead>
<? for($i = 0; $row = $result->fetch_assoc(); $i = ($i + 1) % 2) { ?>
				<tbody<?=($i == 1)?" class=\"impair\"":""?>>
					<tr>
						<td class="nbre"><?=Helper::f_int(++$place)?></td>
						<td class="nom etab" style="padding-left: 2em;">
							<a href="profs/<?=$row["id"]?>/">
<? if ($row["cursus"] == E_2ND) { ?>
								<? $secondaire = explode(",", $row["secondaire"]); foreach ($secondaire as $key => $val) { ?><?=Geo::$SECONDARY[$val].((isset($secondaire[$key + 1]))?", ":"")?><? } ?>
<? } ?>
								<?=htmlspecialchars($row["nom"])?>
								(<?=htmlspecialchars($row["commune"])?>)
							</a>
						</td>
						<th class="nbre" style="padding-right: .5em;"><?=Helper::f_int($row["notes"])?></th>
					</tr>
				</tbody>
<? } ?>
			</table>
		</div>
<? require("tpl/bas.php"); ?>

