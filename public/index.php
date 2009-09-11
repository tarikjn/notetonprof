<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

// last visited school
$last_etblt = FALSE;
if (isset($_COOKIE["last_etblt"]))
{
	$query = "SELECT etablissements.nom AS etblt, etablissements.id AS e_id, dept, villes.nom AS commune, cursus, secondaire FROM etablissements, villes WHERE etablissements.id = " . DBPal::quote($_COOKIE['last_etblt']) ." && villes.id = etablissements.ville_id".((Admin::MOD_SCHOOL)?" && etablissements.moderated = 'yes'":"");
	$result = DBPal::query($query);
	$rnb = $result->num_rows;
	
	if ($rnb)
	{
		$row = $result->fetch_assoc();
		$last_etblt = TRUE;
	}
}

// Controller-View limit
DBPal::finish();

$show_stats = 1;
$title = NULL;
?>
<? require("tpl/haut.php"); ?>
		<div class="cursus">
			<p>
				<strong>NoteTonProf.com te permet de noter tes professeurs de façon totalement anonyme.</strong>
			</p>
<? if ($last_etblt) { ?>
			<div>
				Dernier établissement que tu as consulté :
				<div class="dernier-etblt">
					<a class="etab" href="profs2/<?=urlencode($row["e_id"])?>/"><? if ($row["cursus"] == E_2ND) { ?><? $secondaire = explode(",", $row["secondaire"]); foreach ($secondaire as $key => $val) { ?><?=Geo::$SECONDARY[$val].((isset($secondaire[$key + 1]))?", ":"")?><? } ?> <? } ?><?=htmlspecialchars($row["etblt"])?> (<?=htmlspecialchars($row["commune"])?>, <?=htmlspecialchars($row["dept"])?>)</a>
				</div>
			</div>
<? } ?>
			<form method="get" action="recherche">
				<div class="recherche" title="établissement OU professeur">
					<input type="hidden" name="sent" value="1" />
					<input class="box" type="text" value="recherche rapide" onfocus="this.value = '';" maxlength="255" accesskey="R" name="ntp_q" /><input class="but" type="submit" value="Go" accesskey="G" />
				</div>
			</form>
			<div>
				<h2>Sélectionne ton cursus :</h2>
				<dl>
					<dt><a href="indicatifs/<?=E_2ND?>/">Secondaire</a></dt>
					<dd>Collèges &amp; Lycées</dd>
					<dt><a href="indicatifs/<?=E_SUP?>/">Supérieur</a></dt>
					<dd>Facs, Écoles &amp; Prépas</dd>
				</dl>
			</div>
		</div>
<? require("tpl/school_of_the_day.php"); ?>
		<hr />
		<div class="message">
			<p>
				<strong>Un outil essentiel à la disposition des élèves.</strong>
				Pour permettre aux enseignants d'améliorer et de perfectionner leurs méthodes d'apprentissage.<br />
				Et récompenser le travail formidable effectué par les professeurs brillants et pédagogues dont on ne parle pas assez.
			</p>
			<!-- Dernière info -->
		</div>

<? require("tpl/bas.php"); ?>
