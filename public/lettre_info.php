<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

$show_stats = 1;
$title = "Lettre d'information";
?>
<? require("tpl/haut.php"); ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<h2>Le mot du webmestre</h2>
			<img style="float: left; margin: 0 1em 1em 0;" src="img/tarik.jpg" alt="Photo" height="225" width="150" />
			<h3>L'évaluation démocratique des profs, une première en France !</h3>
			<p>L'ouverture du site noteTonProf.fr est une grande nouveauté dans le système éducatif français tant pour la liberté d'expression des élèves et des étudiants français que pour leur information.</p>
			<p>Le site permet dès aujourd'hui à tous les étudiants et élèves à partir du collège de noter gratuitement et sans inscription leurs professeurs sur un portail central.</p>
			<p>J'espère de tout mon c&oelig;ur que ce système d'évaluation contribuera à l'évolution positive du système éducatif et des rapports entre professeurs et élèves.</p>
			<p>Par ailleurs, à l'heure où l'Europe s'élargit à 25, je rêve de voir ce mode d'évaluation se développer dans les différents pays de l'Union afin de construire une Europe où l'éducation change et nous assure un avenir de sagesse, de prospérité et de respect.</p>
			<p style="text-align: right;">
				<strong>Tarik ANSARI</strong><br />
				Projet noteTonProf.fr
			</p>
		</div>
<? require("tpl/bas.php"); ?>
