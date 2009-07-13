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
			<h2>Nous contacter</h2>
			<p>Merci d'envoyer ton message à l'adresse appropriée.</p>
			<ul class="contact">
				<li>
					Pour contacter les modérateurs :<br />
					<a href="mailto:<?=Web::emailEncode("modos@notetonprof.fr")?>">&#109;&#111;&#100;&#111;&#115;&#64;&#110;&#111;&#116;&#101;&#116;&#111;&#110;&#112;&#114;&#111;&#102;&#46;&#102;&#114;</a>
				</li>
				<li>
					Pour nous faire part de ton avis sur le site :<br />
					<a href="mailto:<?=Web::emailEncode("comment@notetonprof.fr")?>">&#99;&#111;&#109;&#109;&#101;&#110;&#116;&#64;&#110;&#111;&#116;&#101;&#116;&#111;&#110;&#112;&#114;&#111;&#102;&#46;&#102;&#114;</a>
				</li>
				<li>
					Pour nous transmettre ou nous demander une information :<br />
					<a href="mailto:<?=Web::emailEncode("infos@notetonprof.fr")?>">&#105;&#110;&#102;&#111;&#115;&#64;&#110;&#111;&#116;&#101;&#116;&#111;&#110;&#112;&#114;&#111;&#102;&#46;&#102;&#114;</a>
				</li>
				<li>
					Pour signaler un problème technique sur le site :<br />
					<a href="mailto:<?=Web::emailEncode("webmestre@notetonprof.fr")?>">&#119;&#101;&#98;&#109;&#101;&#115;&#116;&#114;&#101;&#64;&#110;&#111;&#116;&#101;&#116;&#111;&#110;&#112;&#114;&#111;&#102;&#46;&#102;&#114;</a>
				</li>
			</ul>
		</div>
<? require("tpl/bas.php"); ?>
