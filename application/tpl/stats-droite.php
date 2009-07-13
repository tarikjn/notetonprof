<?
if (!$stat_data = apc_fetch('day_stats'))
{
	// retrieve data from previous day, for the site time zone
	// time zone has been set by DBPal
	$stat_data = (object) array(
		"ratings"           => DBPal::getOne("SELECT COUNT(*) FROM notes"),
		"profs"             => DBPal::getOne("SELECT COUNT(*) FROM professeurs"),
		"schools"           => DBPal::getOne("SELECT COUNT(*) FROM etablissements"),
		"yesterday_ratings" => DBPal::getOne("SELECT COUNT(*) FROM notes WHERE DATE( date ) = DATE( NOW() - INTERVAL 1 DAY )")
	);
	
	// should expire every 30 min
	apc_store('day_stats', $stat_data, 30 * 60);
}
?>
	<div class="droite">
		<h2>Statistiques</h2>
		<dl class="menu stats">
			<dt><span>Nombre total de notes</span></dt>
			<dd><?=Helper::f_int($stat_data->ratings)?></dd>
			<dt><span>Professeurs référencés</span></dt>
			<dd><?=Helper::f_int($stat_data->profs)?></dd>
			<dt><span>Établissements référencés</span></dt>
			<dd><?=Helper::f_int($stat_data->schools)?></dd>
			<dt><span>Notes ajoutées hier</span></dt>
			<dd><?=Helper::f_int($stat_data->yesterday_ratings)?></dd>
		</dl>
	</div>
