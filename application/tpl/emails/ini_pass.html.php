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
	<h1><a href="<?=$gotoURL?>"><img src="cid:titre" width="385" height="55" alt="notetonprof.fr" title="Aller à la page d'accueil" /></a></h1>
	<p>Bonjour,</p>
	<p>Tu as demandé la réinitialisation de ton mot de passe, pour continuer, ouvre <a href="<?=$gotoURL?>/ini_pass2?email=<?=urlencode($email)?>&code=<?=urlencode($check_id)?>">cette page</a>.</p>
	<p>
		Amicalement,<br />
		L'équipe de <a href="<?=$gotoURL?>">noteTonProf.fr</a>
	</p>
</body>
</html>
