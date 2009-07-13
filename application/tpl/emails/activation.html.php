<<?="?"?>xml version="1.0" encoding="iso-8859-1"<?="?"?>>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr" xml:lang="fr">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=iso-8859-1" />
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
	<h1><a href="<?=$gotoURL?>"><img src="cid:titre" width="385" height="55" alt="notetonprof.fr" title="Aller � la page d'accueil" /></a></h1>
	<p>Bonjour <em><?=htmlspecialchars($prenom)?></em>,</p>
	<p>
		Nous te remercions pour ton inscription sur noteTonProf.fr, voici le r�capitulatif des informations dont tu as besoin pour te connecter � ton compte :
		<ul>
			<li>Identifiant : <cite><?=htmlspecialchars($email)?></cite></li>
			<li>Mot de passe : <cite><?=htmlspecialchars($pass)?></cite></li>
		</ul>
	</p>
	<p>
		Tu pourra commencer � utiliser ton compte d�s que tu aura s�l�ctionn� un ou plusieurs �tablissements � mod�rer.
		Pour cel�, rends-toi sur la page d'accueil de <a href="<?=$gotoURL?>">noteTonProf.fr</a>, puis sur la page de l'�tablissement que tu souhaite mod�rer et clique sur �<span class="guill">Devenir d�l�gu�</span>�.
	</p>
	<p>Attention ! Ton compte n'est pas encore actif, afin que ton compte soit op�rationnel, tu dois ouvrir ce <a href="<?=$gotoURL?>/confirm?email=<?=urlencode($email)?>&amp;code=<?=urlencode($check_id)?>">lien d'activation</a>.</p>
	<p>Cette proc�dure nous permet de v�rifier les adresses e-mail des personnes qui s'inscrivent.</p>
	<p>
		Amicalement,<br />
		L'�quipe de <a href="<?=$gotoURL?>">noteTonProf.fr</a>
	</p>
</body>
</html>
