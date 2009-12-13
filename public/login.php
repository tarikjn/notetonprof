<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");


// initialisation des variables
$notice = NULL;

// début traitement formulaire
$sent = @$_POST["sent"];

if ($sent)
{
    $email = @$_POST["email"];
    $pass = @$_POST["pass"];
    $remember = !!@$_POST["remember"];
    
    if (empty($email))
    	$notice["email"] = "Remplis le champ adresse <cite>E-mail</cite>.";
    else
    {
    	$query = "SELECT id, md5_pass, checked, locked FROM delegues WHERE email = " . DBPal::quote($email) . " AND status = 'ok'";
    	$result = DBPal::getRow($query);
    	
    	if (!$result)
    		$notice["email"] = "Ce compte n'existe pas.";
    }
    
    if (empty($pass))
    	$notice["pass"] = "Remplis le champ <cite>Mot de passe</cite>.";
    else if (!$notice)
    {
    	if ($result->md5_pass != md5($pass)) {
    		$notice["pass"] = "Mot de passe incorrect.";
    	}
    	else if ($result->checked == 0) {
    		// check that the account has been validated
    		$notice["other"] = "L'adresse E-mail de ce compte n'a pas encore été validée.";
    	}
    	else if ($result->locked == 'yes') {
    		// check that the account is not locked
    		$notice["other"] = "Ce compte est vérouillé.";
    	}
    }
    
    if (!$notice)
    {
    	// log-in the user object
    	$user->checkLogin($email, $pass, $remember);
    	
    	// changement de page
    	if (@$_GET['return'])
    	{
    		Web::redirect($_SESSION['auth.previous_page'], true);
    	}
    	else
    	{
    		// redirect to moderator homepage
    		Web::redirect('/admin/');
    	}
    }
}
// fin traitement formulaire

// Controller-View limit
DBPal::finish();

$title = "Connexion Délégué";
?>
<? require("tpl/haut.php"); ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<h2>Connexion Délégué</h2>
			<p>
				<a href="ini_pass?email=<?=@urlencode($email)?>">Mot de passe oublié ?</a>
			</p>
<? if ($notice) { ?>
			<div class="head-notice">
				<p>Attention ! Vérifie les données du formulaire :</p>
				<ul><? foreach ($notice as $alert) { ?><li><?=$alert?></li><? } ?></ul>
			</div>
<? } ?>
			<form action="<?=$_SERVER["REQUEST_URI"]?>" method="post" class="form">
				<input type="hidden" name="sent" value="1" />
				<dl>
					<dt>Identification</dt>
					<dd>
						<fieldset>
							<legend>Identifiants</legend>
							<label for="email"<?=(@$notice["email"])?" class=\"notice\"":""?>>E-mail : <input type="text" name="email" id="email" value="<?=@htmlspecialchars($email)?>" maxlength="50" /></label>
							<label for="pass"<?=(@$notice["pass"])?" class=\"notice\"":""?>>Mot de passe : <input type="password" name="pass" id="pass" maxlength="50" /></label>
						</fieldset>
						<fieldset>
							<legend>Options</legend>
							<label for="remember"><input type="checkbox" name="remember" id="remember" value="1" <? if (@$remember) { ?>checked="checked" <? } ?>/> Rester connecté</label>
						</fieldset>
					</dd>
					<dt>Validation</dt>
					<dd><input type="submit" value="Se Connecter" /></dd>
				</dl>
			</form>
		</div>
<? require("tpl/bas.php"); ?>
