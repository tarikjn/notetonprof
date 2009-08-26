<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");


// début traitement des variables URL
$expl = @explode("/", $_SERVER["PATH_INFO"]);
if (@strlen($expl[1]) > 0 && @strlen($expl[2]) > 0)
{
	$cursus = urldecode($expl[1]);
	$ind = urldecode($expl[2]);
	
	$erreur_url = FALSE;
	
	// début vérification des variables
	if (isset(Geo::$COURSE[$cursus]) && isset(Geo::$IND[$ind]))
		$erreur_var = FALSE;
	else
		$erreur_var = TRUE;
	// fin vérification des variables
}
else	$erreur_url = TRUE;
// fin traitement des variables URL

$title = "Sélection d'un département";
?>
<? require("tpl/haut.php"); ?>
<? if ($erreur_url) require("tpl/erreur_url.php"); else if ($erreur_var) require("tpl/erreur_var.php"); else { ?>
		<div class="navi"><?=Helper::navPath(array($cursus, $ind), true)?></div>
		<hr />
		<div>
			<h2>Séléctionne ton département</h2>
			<p>Départements pour l'indicatif <?=htmlspecialchars($ind)?> (<?=Geo::$IND[$ind]?>) :</p>
			<ul>
<? foreach(Geo::$DEPT as $num => $dept) if ($dept["ind"] == $ind) { ?>
				<li><a href="villes/<?=urlencode($cursus)?>/<?=$num?>/"><?=$num?> - <?=$dept["nom"]?></a></li>
<? } ?>
			</ul>
		</div>
<? } ?>
<? require("tpl/bas.php"); ?>
