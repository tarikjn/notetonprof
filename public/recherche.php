<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");


// début traitement des variables URL
if (isset($_GET["ntp_q"]) && isset($_GET["sent"]))
{
	$erreur_url = FALSE;
	
	if (@strlen($_GET["ntp_q"]) > 0 && @strlen($_GET["ntp_q"]) < 255 && $_GET["sent"])
	{
		$q = DBPal::quote("%".str_replace(" ", "%", $_GET["ntp_q"])."%");
		
		$w_etblt = "FROM etablissements, villes WHERE etablissements.status = 'ok' AND CONCAT(secondaire, ' ', etablissements.nom) LIKE $q && villes.id = etablissements.ville_id".((Admin::MOD_SCHOOL)?" && moderated = 'yes'":"");
		$q_etblt = "SELECT etablissements.id AS id, etablissements.nom AS nom, cursus, secondaire, villes.nom AS commune, cp, ville_id $w_etblt LIMIT 20";
		$c_etblt = "SELECT COUNT(*) $w_etblt";
		
		// TODO: add %first last_name% and %last first_name%
		$w_prof = "FROM professeurs, etablissements, villes WHERE etablissements.status = 'ok' AND professeurs.status = 'ok' AND CONCAT(professeurs.nom, ' ', professeurs.prenom) LIKE $q && etablissements.id = professeurs.etblt_id && villes.id = etablissements.ville_id".((Admin::MOD_PROF)?" && professeurs.moderated = 'yes'":"").((Admin::MOD_SCHOOL)?" && etablissements.moderated = 'yes'":"");
		$q_prof = "SELECT professeurs.id AS id, professeurs.nom AS nom, professeurs.prenom AS prenom, etablissements.nom AS etblt, etablissements.id AS etblt_id, cursus, secondaire, villes.nom AS commune, villes.cp AS cp, ville_id $w_prof LIMIT 20";
		$c_prof = "SELECT COUNT(*) $w_prof";
		
		$r_etblt = DBPal::query($q_etblt);
		$ct_etblt = DBPal::getOne($c_etblt);
		$r_prof = DBPal::query($q_prof);
		$ct_prof = DBPal::getOne($c_prof);
		
		$nb_etblt = $r_etblt->num_rows;
		$nb_prof = $r_prof->num_rows;
	}
	else
	{
		$nb_etblt = 0;
		$nb_prof = 0;
	}
}
else	$erreur_url = TRUE;
// fin traitement des variables URL

// Controller-View limit
DBPal::finish();

$title = "Recherche rapide";
?>
<? require("tpl/haut.php"); ?>
<? if ($erreur_url) require("tpl/erreur_url.php"); else { ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <?=$title?></div>
		<hr />
		<h2><?=$title?></h2>
		<div title="établissement OU professeur">
			<form method="get" action="recherche">
				<input type="hidden" name="sent" value="1" />
				<input type="text" value="<?=htmlspecialchars($_GET["ntp_q"])?>" maxlength="255" accesskey="R" name="ntp_q" />
				<input type="submit" value="Rechercher" accesskey="R" />
			</form>
		</div>
		<hr />
		<h3>Établissements</h3>
<? if ($nb_etblt) { ?>
		<table class="large s">
			<thead>
				<tr>
					<th class="nom">Cursus</th>
					<th class="nom critere">Nom</th>
					<th class="nom">Commune</th>
				<tr>
			</thead>
<? while($row = $r_etblt->fetch_assoc()) { ?>
			<tbody>
				<tr>
					<td class="nom etab small"><a href="indicatifs/<?=urlencode($row["cursus"])?>/"><?=htmlspecialchars($row["cursus"])?></a></td>
					<td class="nom etab"><a href="profs/<?=urlencode($row["id"])?>/"><? if ($row["cursus"] == E_2ND) { ?><? $secondaire = explode(",", $row["secondaire"]); foreach ($secondaire as $key => $val) { ?><?=Geo::$SECONDARY[$val].((isset($secondaire[$key + 1]))?", ":"")?><? } ?> <? } ?><?=htmlspecialchars($row["nom"])?></a></td>
					<td class="nom small"><a href="etblts/<?=urlencode($row["cursus"])?>/<?=urlencode($row["ville_id"])?>/"><?=htmlspecialchars($row["cp"])?>, <?=htmlspecialchars($row["commune"])?></a></td>
				</tr>
			</tbody>
<? }?>
		</table>
<? if ($nb_etblt < $ct_etblt) { ?>
		<p><em>Il y a en tout <?=$ct_etblt?> résultats, utilise des critères plus fins.</em></p>
<? } ?>
<? } else { ?>
		<p><em>Aucun résultat.</em></p>
<? } ?>
		<hr />
		<h3>Profs</h3>
<? if ($nb_prof) { ?>
		<table class="large s">
			<thead>
				<tr>
					<th class="nom critere">Nom</th>
					<th class="nom critere"><span class="abbr" title="ou Civilité">Prénom</span></th>
					<th class="nom">Établissement</th>
					<th class="nom">Commune</th>
				<tr>
			</thead>
<? while($row = $r_prof->fetch_assoc()) { ?>
			<tbody>
				<tr>
					<td class="nom etab"><a href="notes/<?=urlencode($row["id"])?>/"><?=htmlspecialchars($row["nom"])?></a></td>
					<td class="nom"><a href="notes/<?=urlencode($row["id"])?>/"><?=htmlspecialchars($row["prenom"])?></a></td>
					<td class="nom etab small"><a href="profs/<?=urlencode($row["etblt_id"])?>/"><? if ($row["cursus"] == E_2ND) { ?><? $secondaire = explode(",", $row["secondaire"]); foreach ($secondaire as $key => $val) { ?><?=Geo::$SECONDARY[$val].((isset($secondaire[$key + 1]))?", ":"")?><? } ?> <? } ?><?=htmlspecialchars($row["etblt"])?></a></td>
					<td class="nom small"><a href="etblts/<?=urlencode($row["cursus"])?>/<?=urlencode($row["ville_id"])?>/"><?=htmlspecialchars($row["cp"])?>, <?=htmlspecialchars($row["commune"])?></a></td>
				</tr>
			</tbody>
<? }?>
		</table>
<? if ($nb_prof < $ct_prof) { ?>
		<p><em>Il y a en tout <?=$ct_prof?> résultats, utilise des critères plus fins.</em></p>
<? } ?>
<? } else { ?>
		<p><em>Aucun résultat.</em></p>
<? } ?>
<? } ?>
<? require("tpl/bas.php"); ?>
