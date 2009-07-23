<?
$current = basename($_SERVER["SCRIPT_FILENAME"]);
$map_haut = array(
	"objectif.php" => "objectif",
	"regles.php"   => "règles",
	"faq.php"      => "faq",
	"donnees.php"  => "confidentialité",
	"annoncer.php" => "annoncez",
	"contact.php"  => "nous contacter",
);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr" xml:lang="fr">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-language" content="fr" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<title>NoteTonProf.fr<?=(isset($title))?" &gt; ".$title:""?></title>
	<base href="<?=Settings::WEB_ROOT?>" />
	
	<meta name="revisit-after" content="7 days" />
	<meta name="robots" content="index,follow" />
	<meta name="DC.alternative" content="Note ton prof" />
	<meta name="DC.subject" content="vote, notes, mon, notestonprof, notetonprof, notemonprof" />
	<meta name="DC.language" content="fr" />
	<meta name="DC.coverage" content="France et territoires" />
	<meta name="DC.description" content="Évaluation démocratique des enseignants du secondaire et du supérieur." />
	<meta name="DC.creator" content="Campus Citizens, LLC" />
	<meta name="DC.date" content="<?=date("Y-m-d")?>" />
	
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="shortcut icon" type="images/x-icon" href="favicon.ico" />
	
	<script type="text/javascript" src="js/jquery-1.3.2.min.js"></script>
	<script type="text/javascript" src="js/portal.js"></script>
</head>
<body>
<? if ($user->uid) require('tpl/mod_panel.php') ?>
<? if (@$_SESSION['auth.message']) require('tpl/auth_msg.php') ?>
	<div class="haut">
	<div>
		<h1><a href="."><img src="img/titre.png" width="385" height="55" alt="notetonprof.fr" title="Retour à la page d'accueil" /></a></h1>
		<ul>
<? $flag = 0; foreach($map_haut as $url => $cap) { ?>
			<li<? if (!$flag) { $flag = 1; ?> class="first"<? } ?>><? if ($url == $current or true) { ?><?=$cap?><? } else { ?><a href="<?=basename($url, ".php")?>"><?=$cap?></a><? } ?></li>
<? } ?>
		</ul>
	</div>
	</div>
	<hr />
	<div class="gauche">
		<h2>Menu</h2>
		<div class="menu vnav">
			<h3 id="evaluations"><span>Évaluations</span></h3>
			<ul>
				<li><a href="indicatifs/<?=E_2ND?>/">Secondaire</a></li>
				<li><a href="indicatifs/<?=E_SUP?>/">Supérieur</a></li>
			</ul>
			<h3 id="classements" title="Classements Établissements"><span>Classements établissements</span></h3>
			<ul>
				<li><a href="frequentation/">Fréquentation</a></li>
				<li>
<? if (isset(Geo::$COURSE[@($cursus)])) { ?>
					<a href="appreciation/<?=$cursus?>/">Appréciation</a>
<? } else { ?>
					<a class="inactif" title="Sélectionne un cursus pour accéder à ce classement">Appréciation</a>
<? } ?>			
				</li>
			</ul>
			<h3 id="site"><span>NoteTonProf.fr</span></h3>
			<ul>
				<li><a href="news">Actualité</a></li>
				<li><a href="lettre_info">Lettre d'information</a></li>
				<li><a href="reactions">Réactions</a></li>
			</ul>
			<h3 id="delegues"><span>Délégués</span></h3>
			<ul>
				<li><a href="inscription">Inscription délégué</a></li>
				<li title="Gérer ton compte, valider les commentaires, faire des corrections"><a class="special" href="login">Accès délégué</a></li>
			</ul>
		</div>
	</div>
<? if (@$show_stats) require("tpl/stats-droite.php"); ?>
	<div class="<?=(@$show_stats)?"centre":"page"?>">