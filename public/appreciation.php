<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

$min_notes = 100;
$place = 0;

// début traitement des variables URL
$expl = @explode("/", $_SERVER["PATH_INFO"]);
if (@strlen($expl[1]) > 0)
{
	$cursus = urldecode($expl[1]);
	
	$erreur_url = FALSE;
	
	// début vérification des variables
	if (isset(Geo::$COURSE[$cursus]))
	{
		$erreur_var = FALSE;
		
		$query =
			"SELECT etablissements.id, etablissements.nom, secondaire, villes.nom AS commune, AVG( interest + clarity + knowledgeable ) / 3 AS moy, COUNT( DISTINCT notes.id ) AS notes ".
			"FROM etablissements, villes, professeurs ".
			"LEFT JOIN notes ON notes.prof_id = professeurs.id && notes.status = 'ok' ".
			"WHERE villes.id = etablissements.ville_id && cursus = '$cursus' && professeurs.etblt_id = etablissements.id && notes.prof_id = professeurs.id && professeurs.status = 'ok' AND etablissements.status = 'ok'".((Admin::MOD_SCHOOL)?" && etablissements.moderated = 'yes'":"")." ".
			"GROUP BY etablissements.id ".
			"HAVING COUNT( DISTINCT notes.id ) >= ".$min_notes." ".
			"ORDER BY moy desc, notes desc";
		$result = DBPal::query($query);
	}
	else
		$erreur_var = TRUE;
	// fin vérification des variables
}
else	$erreur_url = TRUE;
// fin traitement des variables URL

// Controller-View limit
DBPal::finish();

$title = "Classement appréciation";
?>
<? require("tpl/haut.php"); ?>
<? if ($erreur_url) require("tpl/erreur_url.php"); else if ($erreur_var) require("tpl/erreur_var.php"); else { ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <a href="indicatifs/<?=urlencode($cursus)?>">Enseignement <?=Geo::$COURSE[$cursus]?></a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<h2>Classement des établissements par appréciation</h2>
			<p>Il faut bien comprendre que ce classement est purement indicatif, étant donné que la façon dont les élèves notent leurs profs est souvent relative à leur environnement (l'établissement en grande partie).</p>
			<p>La moyenne est calculée à partir des critères principaux des évaluations dont l'échelle est établie de 1 à 5.</p>
			<p>Seuls les établissements ayant recueillit au moins <?=$min_notes?> notes apparaissent dans ce classement.</p>
			<table class="grille moyenne">
				<thead>
					<tr>
						<th class="nbre">Rang</th>
						<th class="nom" style="padding-left: 2em;">Établissement</th>
						<th class="nbre">Moyenne</th>
						<th class="nbre" style="padding-right: .5em;">Notes</th>
					</tr>
				</thead>
<? for($i = 0; $row = $result->fetch_assoc(); $i = ($i + 1) % 2) { ?>
				<tbody<?=($i == 1)?" class=\"impair\"":""?>>
					<tr>
						<td class="nbre"><?=Helper::f_int(++$place)?></td>
						<td class="nom etab" style="padding-left: 2em;">
							<a href="profs/<?=$row["id"]?>/">
<? if ($cursus == E_2ND) { ?>
								<? $secondaire = explode(",", $row["secondaire"]); foreach ($secondaire as $key => $val) { ?><?=Geo::$SECONDARY[$val].((isset($secondaire[$key + 1]))?", ":"")?><? } ?>
<? } ?>
								<?=htmlspecialchars($row["nom"])?>
								(<?=htmlspecialchars($row["commune"])?>)
							</a>
						</td>
						<th class="nbre"><?=number_format($row["moy"], 2)?></th>
						<td class="nbre" style="padding-right: .5em;"><?=Helper::f_int($row["notes"])?></td>
					</tr>
				</tbody>
<? } ?>
			</table>
		</div>
<? } ?>
<? require("tpl/bas.php"); ?>
