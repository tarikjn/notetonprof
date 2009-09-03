<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

$show_stats = 1;
$title = "L'objectif du site";
?>
<? require("tpl/haut.php"); ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<h2>L'objectif de ce site</h2>
			<p>L'objectif de ce site est avant tout de permettre aux élèves et aux étudiants de s'exprimer en échangeant leurs appréciations sur leurs professeurs. Et ainsi de faire participer les élèves à l'évolution de l'enseignement en permettant à leurs professeurs de mieux comprendre leurs besoins.</p>
			<p>Chacun a eut, ou a des professeurs passionnés, passionnants, doués pour transmettre leur savoir et faire aimer leur matière aux élèves.</p>
			<p>Pour les faire connaître, et &mdash; peut-être &mdash; donner envie aux autres de leur ressembler, il est temps d'inverser les rôles et de laisser les élèves noter les professeurs.</p>
			<p>Pour la première fois, c'est vous qui allez noter vos profs et consulter leurs bulletins de notes.</p>
			<p>Nous espérons que cet espace de liberté sera pour les professeurs l'occasion espéré de savoir enfin ce que pensent les élèves (et non seulement l'administration) de leur enseignement.</p>
			<p>Il faut bien comprendre cependant que ce site est un moyen d'expression pour les élèves, et que les notes sont des appréciations personnelles, elles ne viennent donc d'aucune manière remettre en question les évaluations faites par des entités agréés professionnellement.</p>
			<p>Nota Bene : Ce site n'est en aucun cas destiné à monter les élèves contre leurs professeurs, encore moins un prétexte pour leur manquer de respect. Nous demandons aux élèves de noter jusqu'à quel point ils aiment la façon d'enseigner de leur professeur, de 1/5 (je n'aime pas du tout) à 5/5 (j'adore), et de éventuellement de justifier leur évaluation avec un commentaire devant respecter des règles précises.</p>
		</div>
<? require("tpl/bas.php"); ?>
