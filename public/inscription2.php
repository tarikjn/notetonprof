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
	$pass = @$_POST["pass"];
	$pass2 = @$_POST["pass2"];
	$prenom = @$_POST["prenom"];
	$nom = @$_POST["nom"];
	
	if (empty($email))
		$notice["email"] = "Indique ton adresse <cite>E-mail</cite> pour l'identifiant.";
	else if (!Mail::isValidEmail($email))
		$notice["email"] = "Ce n'est pas une adresse <cite>E-mail</cite> valide !";
	else
	{
		$query = "SELECT COUNT(*) FROM delegues WHERE email = " . DBPal::quote($email) . " AND status = 'ok'";
		$isUsed = DBPal::getOne($query);
		
		if ($isUsed)
			$notice["email"] = "L'adresse e-mail <cite>".htmlspecialchars($email)."</cite> est déjà utilisée !";
	}
	
	if (empty($pass))
		$pass = UserAuth::genPass();
	else
	{
		if (strlen($pass) < 5)
			$notice["pass"] = "Ton mot de passe doit faire 5 caractère minimum, pour des raisons de sécurité.";
		if ($pass != $pass2)
			$notice["pass2"] = "Les mots de passe ne correspondent pas !";
	}
	
	if (empty($prenom))
		$notice["prenom"] = "Remplis le champ <cite>Prenom</cite>.";
	if (empty($nom))
		$notice["nom"] = "Remplis le champ <cite>Nom</cite>.";
	
	// check the reCAPTCHA
	Web::checkReCaptcha($user, $notice);
	
	if (!$notice)
	{
		$check_id = UserAuth::genPass();
		
		// TODO: rule out check_id and md5_pass when logging creation
		$new_admin = array(
		    "check_id" => $check_id,
		    "level" => 1,
		    "email" => $email,
		    "nom" => $nom,
		    "prenom" => $prenom,
		    "md5_pass" => md5($pass)
		  );
		
		// insert admin and log, 3rd param means setup create_record
		App::createObjectAndLog('user', $new_admin, $user->uid, true);
		
		// setup email template vars
		$email_vars = array(
		    'prenom' => $prenom,
		    'email' => $email,
		    'pass' => $pass,
		    'check_id' => $check_id
		  );
		
		// send activation email
		Mail::sendMail($email,
		               "Ton inscription sur NoteTonProf.com",
		               'activation',
		               $email_vars);
		
		// changement de page
		$success = true;
	}
}
// fin traitement formulaire

// Controller-View limit
DBPal::finish();

$title = "Inscription délégué";
?>
<? require("tpl/haut.php"); ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
<? if ($success) { ?>
			<h2>Inscription délégué réussie</h2>
			<p>Ton inscription a bien été enregistrée, un courriel a été envoyé à l'adresse <strong><?=htmlspecialchars($email)?></strong> afin de te permettre d'activer ton compte.</p>
			<p><a href=".">Retour au site</a></p>
<? } else { ?>
			<h2>Formulaire d'inscription délégué</h2>
			<p>Après t'être inscrit via ce formulaire, tu pourras être délégué des établissements de ton choix.</p>
<? if ($notice) { ?>
			<div class="head-notice">
				<p>Attention ! Vérifie les données du formulaire :</p>
				<ul><? foreach ($notice as $alert) { ?><li><?=$alert?></li><? } ?></ul>
			</div>
<? } ?>
			<form action="<?=$_SERVER["SCRIPT_NAME"]?>" method="post" class="form">
				<input type="hidden" name="sent" value="1" />
				<dl>
					<dt>Formulaire</dt>
					<dd>
						<fieldset>
							<legend>Identifiant</legend>
							<label for="email"<?=(@$notice["email"])?" class=\"notice\"":""?>>E-mail : <input type="text" name="email" id="email" value="<?=@htmlspecialchars($email)?>" maxlength="255" /></label>
							<p class="indic">
								Cette adresse e-mail doit être valide,<br />
								Elle ne sera jamais affichée sur le site ou communiquée à un quelconque partenaire.
							</p>
						</fieldset>
						<fieldset>
							<legend>Mot de passe</legend>
							<p class="indic">Si tu n'entres pas de mot de passe, un mot de passe sécurisé te sera généré aléatoirement.</p>
							<label for="pass" class="facultatif<?=(@$notice["pass"])?" notice":""?>">Mot de passe<a href="http://<?=$_SERVER["HTTP_HOST"]?><?=$_SERVER["REQUEST_URI"]?>#facultatif">*</a> : <input type="password" name="pass" id="pass" value="" maxlength="50" /></label>
							<label for="pass2" class="facultatif<?=(@$notice["pass2"])?" notice":""?>">Confirmation<a href="http://<?=$_SERVER["HTTP_HOST"]?><?=$_SERVER["REQUEST_URI"]?>#facultatif">*</a> : <input type="password" name="pass2" id="pass2" value="" maxlength="50" /></label>
						</fieldset>
						<fieldset>
							<legend>Identité</legend>
							<label for="prenom"<?=(@$notice["prenom"])?" class=\"notice\"":""?>>Prénom : <input type="text" name="prenom" id="prenom" value="<?=@htmlspecialchars($prenom)?>" maxlength="50" /></label>
							<label for="nom"<?=(@$notice["nom"])?" class=\"notice\"":""?>>Nom : <input type="text" name="nom" id="nom" value="<?=@htmlspecialchars($nom)?>" maxlength="50" /></label>
							<p class="indic">Ton nom ne sert qu'à l'équipe de <cite>NoteTonProf.com</cite>, il ne sera en aucun cas affiché sur le site.</p>
						</fieldset>
						<p class="facultatif etoile" id="facultatif">*Champ facultatif, 5 caractères minimum (caractères quelconques)</p>
					</dd>
					<?=Web::getReCaptcha()?>
					<dt>Validation</dt>
					<dd><input type="submit" value="Terminer" /></dd>
				</dl>
			</form>
<? } ?>
		</div>
<? require("tpl/bas.php"); ?>
