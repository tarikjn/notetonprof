<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

$show_stats = 1;
$title = "Informations légales";
?>
<? require("tpl/haut.php"); ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div class="text-page">
			<h3>Sommaire</h3>
			<ul>
				<li><a href="legal#terms">Conditions d'Utilisation</a></li>
				<li><a href="legal#legal">Procédures Légales</a></li>
				<li><a href="legal#privacy">Politique de Confidentialité</a></li>
			</ul>
			<h3><a name="terms">Conditions d'Utilisation</a></h3>
			<div class="legal">
				<? require('tpl/legal/terms.html'); ?>
			</div>
			<h3><a name="legal">Procédures Légales</a></h3>
			<div class="legal">
				<? require('tpl/legal/legal_use.html'); ?>
			</div>
			<h3><a name="privacy">Politique de Confidentialité</a></h3>
			<div class="legal">
				<? require('tpl/legal/privacy_policy.html'); ?>
			</div>
		</div>
<? require("tpl/bas.php"); ?>
