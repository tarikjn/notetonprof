<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

$show_stats = 1;
$title = "Annoncer";
?>
<? require("tpl/haut.php"); ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<h2>Informations pour les annonceurs</h2>
			<p>Vous voulez être annonceur sur un site qui va bientôt faire un effet de tremblement de terre en France ?</p>
			<p>Vous recherchez un site populaire susceptible d'être fréquenté par tout les élèves et les étudiants connectés ? (et quelques professeurs perdus ;)</p>
			<p>Vous souhaitez être un partenaire ou sponsor privilégié ?</p>
			<p>Contactez : <a href="mailto:<?=Web::emailEncode("tarik@notetonprof.fr")?>">Tarik ANSARI</a></p>
		</div>
<? require("tpl/bas.php"); ?>
