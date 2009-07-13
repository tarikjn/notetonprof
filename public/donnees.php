<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

$show_stats = 1;
$title = "Données personnelles";
?>
<? require("tpl/haut.php"); ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<h2>Données Personnelles</h2>
			<p>Numéro d'enregistrement du site auprès de la <a href="http://www.cnil.fr/"><acronym title="Commission Nationale de l'Informatique et des Libertés">CNIL</acronym></a></a> : <em>1020320</em></p>
			<h3>Données nominatives</h3>
			<p>Si tu t'inscris en tant que délégué, des informations nominatives sont enregistrées dans la base de donnée du site.</p>
			<p>Ces données restent privées dans tous les cas et n'apparaissent nul part sur le site. Ainsi, tu conserves ton anonymat même si tu es délégué.</p>
			<p>Tu peux exercer un droit d'accès et de modification sur ces données de façon autonome en t'identifiant via la rubrique <a href="delegues/">Accès délégué</a> et en te rendant sur la page <cite>Compte</cite>.</p>
			<p>Tu peux exercer un droit d'accès, de modification et de suppression de tes données personnelles en nous contactant via la rubrique <a href="contact">Nous contacter</a>.</p>
			<h3>Cookies</h3>
			<p>Chaque fois que tu laisses une évaluation, un cookie est enregistré afin de mémoriser la date où tu as laissé l'évaluation. Ceci t'évite d'avoir à t'inscrire pour utiliser le site. Le système d'évaluation étant basé sur ce principe, il ne t'est pas possible de noter un professeur si les cookies ne sont pas activés sur ton navigateur.</p>
			<p>Ce cookie nous permet aussi de surligner tes évaluations lorsque tu reviens sur la page d'un professeur.</p>
			<p>D'autre part, trois cookies peuvent être enregistrés sur ta session afin de rendre le site plus interactif, ils te servent à :</p>
			<ul>
				<li>Accéder directement aux établissements dont tu as déclaré faire ou avoir fait partie, en mémorisant ta déclaration.</li>
				<li>Accéder directement à partir de la page d'accueil au dernier établissement que tu as consulté.</li>
				<li>Directement corriger la fiche d'un établissement, d'un professeur, ou supprimer un commentaire. Ce cookie n'étant actif que dans le cas où tu es un délégué actif.</li>
			</ul>
			<h3>Sécurité</h3>
			<p>La sécurité est pour nous essentielle, ainsi, si tu es délégué, ton mot de passe est chiffré avant d'être enregistré dans notre base de donnée. Ce qui empêche à quiconque (y compris l'équipe de noteTonProf.fr) de connaître ton mot de passe.</p>
			<p>Aussi ce site a été développé consciencieusement, par conséquent il n'est pas vulnérable aux injections SQL ou autres attaques de base. Pour le reste, nous utilisons diverses précautions afin que les scripts soient sécurisés. Si malgré tout &mdash; errare humanum est &mdash;, tu trouves une vulnérabilité, <a href="contact">contacte-nous</a> ;).</p>
		</div>
<? require("tpl/bas.php"); ?>
