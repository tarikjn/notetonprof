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
			<p>NoteTonProf.fr c'est : </p>
			<ul>
				<li>Un concept unique et novateur</li>
				<li>Un profil d'utilisateur type bien défini</li>
				<li>Une configuration idéale pour promouvoir des services liés à l'enseignement, secteur en plein développement</li>
				<li>Un nombre de possibilités presque illimitées en termes de ciblage de la publicité</li>
			</ul>
			<p>Vous souhaitez être un partenaire, sponsor ou tout simplement diffuser votre campagne publicitaire sur noteTonProf.fr ?</p>
			<p>Contactez : <a href="mailto:<?=Web::emailEncode("contact@campuscitizens.com")?>">contact@campuscitizens.com</a></p>
		</div>
<? require("tpl/bas.php"); ?>
