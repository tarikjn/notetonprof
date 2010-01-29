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

if (!$yesterday_ratings = apc_fetch('yesterday_ratings_count'))
{
	// retrieve data from previous day, for the site time zone
	// time zone has been set by DBPal
	$yesterday_ratings = DBPal::getOne("SELECT COUNT(*) FROM notes WHERE DATE( date ) = DATE( NOW() - INTERVAL 1 DAY )");
	
	// should expire at the end of the day
	// view notes in school_of_the_dat, TODO: merge gears
	$expires_at = mktime(0, 0, Settings::DB_MAX_TIME_DIFF, date("n"), date("j") + 1);
	$now = time();
	if ($expires_at > $now)
		apc_store('yesterday_ratings_count', $yesterday_ratings, $expires_at - $now);
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
			<dd><?=Helper::f_int($yesterday_ratings)?></dd>
		</dl>
		
		<!-- Google Ads -->
		<script type="text/javascript"><!--
		google_ad_client = "pub-5461332511807874";
		/* 160x600, created 1/28/10 */
		google_ad_slot = "1104923012";
		google_ad_width = 160;
		google_ad_height = 600;
		//-->
		</script>
		<script type="text/javascript"
		src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
		</script>
		
	</div>
