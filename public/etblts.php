<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

// début traitement des variables URL
$expl = @explode("/", $_SERVER["PATH_INFO"]);
if (@strlen($expl[1]) > 0 && @strlen($expl[2]) > 0)
{
	$cursus = urldecode($expl[1]);
	$c_id = (int) urldecode($expl[2]);
	
	$erreur_url = FALSE;

	// début vérification des variables
	$result = DBPal::query("SELECT dept, cp, nom FROM villes WHERE id = $c_id");
	$rnb = $result->num_rows;
	
	if (isset(Geo::$COURSE[$cursus]) && $rnb)
	{
		$row = $result->fetch_assoc();
		$dept = $row["dept"];
		$cp = $row["cp"];
		$c_nom = $row["nom"];
		
		$query = "SELECT DISTINCT COUNT(professeurs.id) AS profs, etablissements.nom, etablissements.id".(($cursus == E_2ND)?", secondaire":"")." FROM etablissements LEFT JOIN professeurs ON professeurs.status = 'ok' && professeurs.etblt_id = etablissements.id".((Admin::MOD_PROF)?" && professeurs.moderated = 'yes'":"")." WHERE ville_id = $c_id".((Admin::MOD_SCHOOL)?" && etablissements.moderated = 'yes'":"")." && cursus = '$cursus' AND etablissements.status = 'ok' GROUP by etablissements.id ORDER by nom;";
		$result = DBPal::query($query);
		$rnb = $result->num_rows;
		
		//session_start();
		
		$erreur_var = FALSE;
	}
	else
		$erreur_var = TRUE;
	// fin vérification des variables
}
else	$erreur_url = TRUE;
// fin traitement des variables URL

// Controller-View limit
DBPal::finish();

$title = "Sélection d'un établissement";
?>
<? require("tpl/haut.php"); ?>
<? if ($erreur_url) require("tpl/erreur_url.php"); else if ($erreur_var) require("tpl/erreur_var.php"); else { ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <a href="indicatifs/<?=urlencode($cursus)?>">Enseignement <?=Geo::$COURSE[$cursus]?></a> &gt; <a href="depts/<?=urlencode($cursus)?>/<?=urlencode(Geo::$DEPT[$dept]["ind"])?>/">Indicatif <?=htmlspecialchars(Geo::$DEPT[$dept]["ind"])?></a> &gt; <a href="villes/<?=urlencode($cursus)?>/<?=urlencode($dept)?>/"><?=htmlspecialchars($dept)?> - <?=htmlspecialchars(Geo::$DEPT[$dept]["nom"])?></a> &gt; <?=htmlspecialchars($cp)?> - <?=htmlspecialchars($c_nom)?></div>
		<hr />
		<div>
			<h2>Séléctionne ton établissement</h2>
<? if ($message = Web::flash('msg')) { ?>
			<p class="msg"><?=$message?></p>
<? } ?>
			<p><?=$rnb?> établissement dans la commune : <?=htmlspecialchars($c_nom)?> (<?=htmlspecialchars($cp)?>) :</p>
			<p class="action"><a href="ajout_etblt2?cursus=<?=urlencode($cursus)?>&amp;cp=<?=urlencode($cp)?>">Ajoute ton établissement</a></p>
			<table class="liste petite">
				<thead>
					<tr>
						<th class="nom">Nom</th>
<? if ($cursus == E_2ND) { ?>
						<th class="nom">Enseignement</th>
<? } ?>
						<th class="nbre">Professeurs</th>
					</tr>
				</thead>
<? while($row = $result->fetch_assoc()) { ?>
				<tbody>
					<tr>
						<td class="nom etab"><a href="profs/<?=urlencode($row["id"])?>/"><?=htmlspecialchars($row["nom"])?></a></td>
<? if ($cursus == E_2ND) { ?>
						<td class="nom"><a href="profs/<?=urlencode($row["id"])?>/"><? $secondaire = explode(",", $row["secondaire"]); foreach ($secondaire as $key => $val) { ?><?=Geo::$SECONDARY[$val].((isset($secondaire[$key + 1]))?", ":"")?><? } ?></a></td>
<? } ?>
						<td class="nbre"><a href="profs/<?=urlencode($row["id"])?>/"><?=htmlspecialchars($row["profs"])?></a></td>
					</tr>
				</tbody>
<? } ?>
			</table>
			<p>Ton établissement n'apparaît pas dans la liste ci-dessus ? <a href="ajout_etblt2?cursus=<?=urlencode($cursus)?>&amp;cp=<?=urlencode($cp)?>">Ajoute-le</a> !</p>
		</div>
<? } ?>
<? require("tpl/bas.php"); ?>
