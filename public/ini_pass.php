<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

// initialisation des variables
$notice = NULL;
$success = FALSE;

// début traitement formulaire
$sent = @$_POST["sent"];

if ($sent)
{
	$email = @$_POST["email"];
	
	if (empty($email))
		$notice["email"] = "Remplis le champ adresse <cite>E-mail</cite>.";
	else
	{
		$query = "SELECT id FROM delegues WHERE email = " . DBPal::quote($email) . " AND status = 'ok' AND locked = 'no' AND checked = 1";
		$rnb = DBPal::getOne($query);
		
		if (!$rnb)
			$notice["email"] = "Ce compte n'existe pas ou n'est pas actif.";
	}
	
	if (!$notice)
	{
		$check_id = UserAuth::genPass();
		
		// enregistrement
		DBPal::query("UPDATE delegues SET check_id = " . DBPal::quote($check_id) . ", change_pwd = 1 WHERE email = " . DBPal::quote($email) . " AND status = 'ok' AND locked = 'no' AND checked = 1");
		
		// log
		App::log("Requested password reinitialization", "user", $rnb, null, null, null, true);
		
		// setup email template vars
		$email_vars = array(
		    'email' => $email,
		    'check_id' => $check_id
		  );
		
		// send password reinitialization email
		Mail::sendMail($email,
		               "Réinitialisation de ton mot de passe",
		               'ini_pass',
		               $email_vars);
			
		// changement de page
		$success = true;
	}
}
else
	$email = @$_GET["email"];
// fin traitement formulaire

// Controller-View limit
DBPal::finish();

$title = "Mot de passe oublié";
?>
<? require("tpl/haut.php"); ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
<? if ($success) { ?>
			<h2>Demande de réinitialisation de mot de passe envoyée</h2>
			<p>Ta demande a bien été prise en compte, un courriel a été envoyé à l'adresse <strong><?=htmlspecialchars($email)?></strong> afin de te permettre de redéfinir un nouveau mot de passe.</p>
			<p><a href=".">Retour au site</a></p>
<? } else { ?>
			<h2>Réinitialisation de ton mot de passe</h2>
			<p>Si tu ne te souviens plus de ton mot de passe et que tu as changé d'adresse e-mail, tu dois <a href="inscription">créer un nouveau compte</a>.</p>
<? if ($notice) { ?>
			<div class="head-notice">
				<p>Attention ! Vérifie les données du formulaire :</p>
				<ul><? foreach ($notice as $alert) { ?><li><?=$alert?></li><? } ?></ul>
			</div>
<? } ?>
			<form action="<?=$_SERVER["SCRIPT_NAME"]?>" method="post" class="form">
				<input type="hidden" name="sent" value="1" />
				<dl>
					<dt>Identification</dt>
					<dd>
						<fieldset>
							<legend>Identifiants</legend>
							<label for="email"<?=(@$notice["email"])?" class=\"notice\"":""?>>E-mail : <input type="text" name="email" id="email" value="<?=@htmlspecialchars($email)?>" maxlength="50" /></label>
						</fieldset>
					</dd>
					<dt>Validation</dt>
					<dd><input type="submit" value="Demande de réinitialisation" /></dd>
				</dl>
			</form>
<? } ?>
		</div>
<? require("tpl/bas.php"); ?>
