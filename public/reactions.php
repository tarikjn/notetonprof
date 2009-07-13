<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");


$show_stats = 1;
$title = "Réactions";
?>
<? require("tpl/haut.php"); ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<h2>Réactions</h2>
			<p>Nous comptons bientôt afficher sur cette page vos réactions et les réactions de la presse.</p>
		</div>
<? require("tpl/bas.php"); ?>
