<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

// début traitement des variables URL
$expl = @explode("/", $_SERVER["PATH_INFO"]);
if (@strlen($expl[1]) > 0 && @strlen($expl[2]) > 0)
{
	$cursus = urldecode($expl[1]);
	$dept = urldecode($expl[2]);
	
	$erreur_url = FALSE;

	// début vérification des variables
	if (isset(Geo::$COURSE[$cursus]) && isset(Geo::$DEPT[$dept]))
		$erreur_var = FALSE;
	else
		$erreur_var = TRUE;
	// fin vérification des variables
}
else	$erreur_url = TRUE;
// fin traitement des variables URL

if (!$erreur_url && !$erreur_var)
{
	$query = "SELECT DISTINCT COUNT(*) AS etblts, cp, villes.nom, villes.id FROM villes, etablissements WHERE dept = '$dept' AND etablissements.status = 'ok' && etablissements.ville_id = villes.id && cursus = '$cursus'".((Admin::MOD_SCHOOL)?" && moderated = 'yes'":"")." GROUP by villes.id ORDER by nom, cp;";
	$result = DBPal::query($query);
	
	$rnb = $result->num_rows;
}

// Controller-View limit
DBPal::finish();

$title = "Sélection d'une ville";
?>
<? require("tpl/haut.php"); ?>
<? if ($erreur_url) require("tpl/erreur_url.php"); else if ($erreur_var) require("tpl/erreur_var.php"); else { ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <a href="indicatifs/<?=urlencode($cursus)?>">Enseignement <?=Geo::$COURSE[$cursus]?></a> &gt; <a href="depts/<?=urlencode($cursus)?>/<?=urlencode(Geo::$DEPT[$dept]["ind"])?>/">Indicatif <?=htmlspecialchars(Geo::$DEPT[$dept]["ind"])?></a> &gt; <?=htmlspecialchars($dept)?> - <?=htmlspecialchars(Geo::$DEPT[$dept]["nom"])?></div>
		<hr />
		<div>
			<h2>Séléctionne la commune de ton établissement</h2>
			<p>Seules les communes des établissements référencés sur noteTonProf.fr apparaissent dans la liste ci-dessous.</p>
			<p><?=$rnb?> commune(s) et lieux dits pour le département <?=htmlspecialchars($dept)?> (<?=Geo::$DEPT[$dept]["nom"]?>) :</p>
			<table class="liste petite">
				<thead>
					<tr>
						<th class="code">Code postal</th>
						<th class="nom">Commune / Lieu dit</th>
						<th class="nbre">Établissements</th>
					</tr>
				</thead>
<? while($row = $result->fetch_assoc()) { ?>
				<tbody>
					<tr>
						<td class="code"><a href="etblts/<?=urlencode($cursus)?>/<?=urlencode($row["id"])?>/"><?=htmlspecialchars($row["cp"])?></a></td>
						<td class="nom"><a href="etblts/<?=urlencode($cursus)?>/<?=urlencode($row["id"])?>/"><?=htmlspecialchars($row["nom"])?></a></td>
						<td class="nbre"><a href="etblts/<?=urlencode($cursus)?>/<?=urlencode($row["id"])?>/"><?=$row["etblts"]?></a></td>
					</tr>
				</tbody>
<? } ?>
			</table>
			<p>
				La commune de ton établissement n'apparaît pas dans la liste ci-dessus ?<br />
				<strong><span style="font-size: 1.45em;">&rArr;</span> <a href="ajout_etblt?cursus=<?=urlencode($cursus)?>&amp;dept=<?=urlencode($dept)?>">Référence ton établissement</a> !</strong>
			</p>
		</div>
<? } ?>
<? require("tpl/bas.php"); ?>
