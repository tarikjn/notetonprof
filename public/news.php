<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");


$query = "SELECT UNIX_TIMESTAMP( date ) AS date, txt FROM news ORDER BY date desc";
$result = DBPal::query($query);

// Controller-View limit
DBPal::finish();

$show_stats = 1;
$title = "Actualité";
?>
<? require("tpl/haut.php"); ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<h2>Actualité du site</h2>
			<dl class="news">
<? while($row = $result->fetch_assoc()) { ?>
				<dt><?=strftime("%d %B %Y", $row["date"])?></dt>
				<dd><?=$row["txt"]?></dd>
<? } ?>
			</dl>
		</div>
<? require("tpl/bas.php"); ?>
