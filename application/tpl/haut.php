<?
$current = basename($_SERVER["SCRIPT_FILENAME"], ".php");
$map_haut = array(
	"regles"   => "règles",
	"objectif" => "objectif",
	"http://forums.notetonprof.com" => "forums",
	"faq"      => "faq",
	"legal"  => "infos légales",
	"annoncer" => "annoncez",
	"contact"  => "contact",
);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr" xml:lang="fr">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-language" content="fr" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<title>NoteTonProf.com<?=(isset($title))?" &gt; ".$title:""?></title>
	<base href="<?=Settings::WEB_ROOT?>/" />
	
	<meta name="revisit-after" content="7 days" />
	<meta name="robots" content="index,follow" />
	<meta name="DC.alternative" content="Note ton prof" />
	<meta name="DC.subject" content="vote, note, notes, mon, notestonprof, notetonprof, notemonprof" />
	<meta name="DC.language" content="fr" />
	<meta name="DC.coverage" content="France et territoires" />
	<meta name="DC.description" content="Évaluation anonymes des enseignants du secondaire et du supérieur." />
	<meta name="DC.creator" content="Campus Citizens LLC" />
	<meta name="DC.date" content="<?=date("Y-m-d")?>" />
	
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="shortcut icon" type="images/x-icon" href="favicon.ico" />
	
	<script type="text/javascript" src="js/jquery-1.3.2.min.js"></script>
	<script type="text/javascript" src="js/portal.js"></script>
	<script type="text/javascript">
		var RecaptchaOptions = {
		   theme: 'white',
		   lang: 'fr'
		};
	</script>
</head>
<body>
<? if ($user->uid) require('tpl/mod_panel.php') ?>
<? if (@$_SESSION['auth.message']) require('tpl/auth_msg.php') ?>
	<div class="haut">
	<div>
		<h1><a href="."><img src="img/titre.png" width="419" height="55" alt="NoteTonProf.com" title="Retour à la page d'accueil" /></a></h1>
		<ul>
<? $flag = 0; foreach($map_haut as $url => $cap) { ?>
			<li<? if (!$flag) { $flag = 1; ?> class="first"<? } ?>><? if ($url == $current) { ?><?=$cap?><? } else { ?><a href="<?=$url?>"><?=$cap?></a><? } ?></li>
<? } ?>
		</ul>
	</div>
	</div>
	<div class="global-link">
		<div class="box">
			<div class="cc-txt-logo"><span class="head">Campus</span> Citizens</div>
			<div class="cc-goto-link"><a href="http://www.campuscitizens.com">→ autres pays</a></div>
		</div>
	</div>
	<hr />
	<div class="gauche">
		<h2>Menu</h2>
		<div class="menu vnav">
			<h3>Notes</h3>
			<ul>
				<li><a href="indicatifs/<?=E_2ND?>/">Secondaire</a></li>
				<li><a href="indicatifs/<?=E_SUP?>/">Supérieur</a></li>
			</ul>
			<h3>Classements</h3>
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
			<h3>Réagissez</h3>
			<ul>
				<li><a href="http://forums.notetonprof.com">Forums</a></li>
				<li><a href="reactions">Réactions</a></li>
			</ul>
			<ul>
				<li><a href="news">Actualité</a></li>
			</ul>
			<?
				if (!$user->uid)
				{
			?>
			<h3>Délégués</h3>
			<ul>
				<li><a href="inscription">Inscription délégué</a></li>
				<li title="Gérer ton compte, valider les commentaires, faire des corrections"><a class="special" href="login">Accès délégué</a></li>
			</ul>
			<?
				}
			?>
		</div>
	</div>
<? if (@$show_stats) require("tpl/stats-droite.php"); ?>
	<div class="<?=(@$show_stats)?"centre":"page"?>">
