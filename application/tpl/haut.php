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
	<meta http-equiv="Content-language" content="fr-fr" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<title>NoteTonProf.com<?=(isset($title))?" &gt; $title":' - Note Ton Prof'?></title>
	<base href="<?=Settings::WEB_ROOT?>/" />
	
	<meta name="DC.alternative" content="Note ton prof" />
	<meta name="DC.subject" content="vote, note, notes, mon, notestonprof, notetonprof, notemonprof" />
	<meta name="DC.language" content="fr" />
	<meta name="DC.coverage" content="France et territoires" />
	<meta name="DC.description" content="Évaluation anonymes des enseignants du secondaire et du supérieur." />
	<meta name="DC.creator" content="Campus Citizens LLC" />
	<meta name="DC.date" content="<?=date("Y-m-d")?>" />
	
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="shortcut icon" type="images/x-icon" href="favicon.ico" />
	
	<script type="text/javascript">
		var RecaptchaOptions = {
		   theme: 'white',
		   lang: 'fr'
		};
	</script>
<? if (@$yui_mode) { ?>
	<!-- YUI for Slider -->
	<script src="js/yui/yahoo-dom-event.js"></script> 
	<script src="js/yui/dragdrop-min.js"></script>
	<script src="js/yui/slider-min.js"></script>
	
	<!-- new reCaptcha mode -->
	<script type="text/javascript">
	RecaptchaOptions.theme = 'white';
	</script>
<? } ?>
	<script type="text/javascript" src="js/jquery-1.3.2.min.js"></script>
	<script type="text/javascript" src="js/jquery.autoexpand.js"></script>
	<script type="text/javascript" src="js/portal.js"></script>
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
	
	<div class="ad-top">
		<script type="text/javascript"><!--
		google_ad_client = "pub-5461332511807874";
		/* 180x150, created 1/28/10 */
		google_ad_slot = "4933686609";
		google_ad_width = 180;
		google_ad_height = 150;
		//-->
		</script>
		<script type="text/javascript"
		src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
		</script>
	</div>
	
	<div class="ad-top-right">
		<script type="text/javascript"><!--
		google_ad_client = "pub-5461332511807874";
		/* 180x150, top right corner */
		google_ad_slot = "0013285911";
		google_ad_width = 180;
		google_ad_height = 150;
		//-->
		</script>
		<script type="text/javascript"
		src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
		</script>
	</div>
	</div>
	<!--
	<div class="global-link">
		<div class="box">
			<div class="cc-txt-logo"><span class="head">Campus</span> Citizens</div>
			<div class="cc-goto-link"><a href="http://www.campuscitizens.com">→ autres pays</a></div>
		</div>
	</div>
	-->
	
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
		
		<div class="ad-panel">
			<script type="text/javascript"><!--
			google_ad_client = "pub-5461332511807874";
			/* 200x200, created 1/28/10 */
			google_ad_slot = "0990054607";
			google_ad_width = 200;
			google_ad_height = 200;
			//-->
			</script>
			<script type="text/javascript"
			src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
			</script>
		</div>
		
		<div class="sponsor-box menu">
			<div class="inside">
				<div class="caption">Aidez Campus Citizens à se développer :</div>
				<div>
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
					<input type="hidden" name="cmd" value="_s-xclick">
					<input type="hidden" name="hosted_button_id" value="9TYR276SWL4QS">
					<input type="image" src="https://www.paypal.com/fr_FR/FR/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - la solution de paiement en ligne la plus simple et la plus sécurisée !">
					<img alt="" border="0" src="https://www.paypal.com/fr_XC/i/scr/pixel.gif" width="1" height="1">
					</form>
				</div>
			</div>
		</div>
		
	</div>
<? if (@$show_stats) require("tpl/stats-droite.php"); ?>
	<div class="<?=(@$show_stats)?"centre":"page"?>">
