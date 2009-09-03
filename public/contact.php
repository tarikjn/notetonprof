<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");


$show_stats = 1;
$title = "Contact";
// http://php.uni-kassel.de/hrz/db4/extern/sstefani/codetext1.php
?>
<? require("tpl/haut.php"); ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<h2>Forum</h2>
			<p>Les <a href="http://forums.notetonprof.fr">forums</a> sont à votre disposition pour échanger vos avis sur le site, vos idées ou débatre sur tout ce qui s'y rapporte.</p>
			<h2>Nous contacter</h2>
			<p>Pour nous signaler une erreur sur un professeur ou un établissement, ou signaler un commentaire qui ne respecte pas les <a href="regles">règles du site</a>, merci d'utiliser la fonction « Signaler » (représentée par un <img src="img/attention.png" />) à coté de l'établissement, du professeur ou du commentaire concerné.</p>
			<p>Merci d'envoyer votre message à l'adresse appropriée.</p>
			<ul class="contact">
				<li>
					Pour signaler une erreur sur le site qui ne peut être signalée via la fonction « Signaler », ou pour toute problème de modération rencontré :<br />
					<a href="mailto:<?=Web::emailEncode("ops@notetonprof.fr")?>">ops@notetonprof.fr</a>
				</li>
				<li>
					Pour nous faire part de votre avis sur le site :<br />
					<a href="mailto:<?=Web::emailEncode("feedback@notetonprof.fr")?>">feedback@notetonprof.fr</a>
				</li>
				<li>
					Pour signaler un problème technique ou de sécurité sur le site :<br />
					<a href="mailto:<?=Web::emailEncode("tech@notetonprof.fr")?>">tech@notetonprof.fr</a>
				</li>
				<li>
					Pour tout autre raison :<br />
					<a href="mailto:<?=Web::emailEncode("info@notetonprof.fr")?>">info@notetonprof.fr</a>
				</li>
			</ul>
		</div>
<? require("tpl/bas.php"); ?>
