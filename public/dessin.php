<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

$show_stats = 1;
$title = "À propos du dessin";
?>
<? require("tpl/haut.php"); ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<h2>À propos du dessin</h2>
			<p>Le dessin que tu vois en haut à droite de ce site a été réalisé par Gérard MATHIEU et se trouve sur <a href="http://dessinsavendre.fr/">DESSINS A VENDRE</a>.</p>
			<p>Le filigrane de l'arbre sur la page d'accueil est également un dessin de Gérard MATHIEU.</p>
		</div>
<? require("tpl/bas.php"); ?>
