<<?="?"?>xml version="1.0" encoding="iso-8859-1"<?="?"?>>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr" xml:lang="fr">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-language" content="fr" />
	<style type="text/css">
	body {
		background-color: #DFECFF;
		color: black;
		font-family: "Verdana", "Helvetica", sans-serif;
		font-size: 95%;
	}
	h1 {
		border-bottom: 2px solid black;
	}
	img {
		border: 0;
	}
	p {
		text-align: justify;
	}
	
	a:link {
		color: #3333CC;
		background-color: transparent;
	}
	a:visited, a:active {
		color: #3366CC;
		background-color: transparent;
	}
	cite {
		font-weight: bold;
		font-style: normal;
	}
	.guill {
		padding: 0 .5em;
	}
	</style>
</head>
<body>
	<h1><a href="<?=$gotoURL?>"><img src="cid:titre" width="385" height="55" alt="notetonprof.fr" title="Aller à la page d'accueil" /></a></h1>
	<p>Bonjour <em><?=htmlspecialchars($prenom)?></em>,</p>
	<p>
		Nous te remercions pour ton inscription sur noteTonProf.fr, voici le récapitulatif des informations dont tu as besoin pour te connecter à ton compte :
		<ul>
			<li>Identifiant : <cite><?=htmlspecialchars($email)?></cite></li>
			<li>Mot de passe : <cite><?=htmlspecialchars($pass)?></cite></li>
		</ul>
	</p>
	<p>
		Tu pourra commencer à utiliser ton compte dès que tu aura séléctionné un ou plusieurs établissements à modérer.
		Pour celà, rends-toi sur la page d'accueil de <a href="<?=$gotoURL?>">noteTonProf.fr</a>, puis sur la page de l'établissement que tu souhaite modérer et clique sur «<span class="guill">Devenir délégué</span>».
	</p>
	<p>Attention ! Ton compte n'est pas encore actif, afin que ton compte soit opérationnel, tu dois ouvrir ce <a href="<?=$gotoURL?>/confirm?email=<?=urlencode($email)?>&amp;code=<?=urlencode($check_id)?>">lien d'activation</a>.</p>
	<p>Cette procédure nous permet de vérifier les adresses e-mail des personnes qui s'inscrivent.</p>
	<p>
		Amicalement,<br />
		L'équipe de <a href="<?=$gotoURL?>">noteTonProf.fr</a>
	</p>
</body>
</html>
