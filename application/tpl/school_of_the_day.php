<?
if (($sotd = apc_fetch('school_of_the_day')) === false)
{
	// retrieve data from previous day, for the site time zone
	// time zone has been set by DBPal
	$query_sotd = "SELECT etablissements.id AS id, COUNT(notes.id) AS ct_notes, etablissements.nom AS etblt, cursus, secondaire, villes.nom AS commune, dept FROM (etablissements, professeurs, villes) LEFT JOIN notes ON professeurs.id = notes.prof_id && DATE( date ) = DATE( NOW() - INTERVAL 1 DAY ) WHERE villes.id = etablissements.ville_id && etablissements.id = professeurs.etblt_id GROUP by etablissements.id ORDER BY ct_notes desc LIMIT 1";
	
	$sotd_res = DBPal::getRow($query_sotd);
	
	// TODO: test no result -> DBPal.result var_dump?
	if ($sotd_res and @$sotd_res->ct_notes > 0)
	{
		$sotd = (object) array(
			'ratings' => $sotd_res->ct_notes,
			'id'      => $sotd_res->id,
			'caption' => Helper::schoolTitle($sotd_res->cursus, $sotd_res->secondaire, $sotd_res->etblt)
			           . " ({$sotd_res->commune}, {$sotd_res->dept})"
		);
	}
	else
	{
		$sotd = null;
	}
	
	// should expire at the end of the day
	// TODO: test mktime overflow (ex 31+1 day: next month?)
	// NOTE: mktime method should avoid issues during time changes to and from day light saving time (DST)
	$expires_at = mktime(0, 0, Settings::DB_MAX_TIME_DIFF, date("n"), date("j") + 1); // takes the time zone into account
	$now = time();
	if ($expires_at > $now) // avoids setting an unlimited cache (0)
		apc_store('school_of_the_day', $sotd, $expires_at - $now);
}
?>
<? if ($sotd) { ?>
		<hr />
		<div class="edj">
			<h2>Établissement du jour</h2>
			<div>
				<div>Établissement qui a recueilli hier le plus de notes (<?=Helper::f_int($sotd->ratings)?>) :</div>
				<div class="dernier-etblt"><a class="etab" href="profs/<?=$sotd->id?>/"><?=htmlspecialchars($sotd->caption)?></a></div>
			</div>
		</div>
<? } ?>
