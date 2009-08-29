<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

// load mail functions
require_once("conf/Mail.php");

// début traitement des variables URL
if (isset($_GET["email"]) && isset($_GET["code"]))
{
	$email = $_GET["email"];
	$code = $_GET["code"];
	
	$erreur_url = FALSE;

	// début vérification des variables
	$query = "SELECT id FROM delegues WHERE email = " . DBPal::quote($email) . " AND status = 'ok' && check_id = " . DBPal::quote($code) . " && checked = 1 && change_pwd = 1 && locked = 'no'";
	$rnb = DBPal::getOne($query);
	
	if ($rnb)
	{
		$pass = UserAuth::genPass();
		
		$query = "UPDATE delegues SET md5_pass = " . DBPal::quote(md5($pass)) . ", change_pwd = 0 WHERE email = " . DBPal::quote($email) . " && status = 'ok' AND check_id = " . DBPal::quote($code) . " && checked = 1 && change_pwd = 1 && locked = 'no'";
		$result = DBPal::query($query);
		
		// log
		App::log("Completed password reinitialization", "user", $rnb, null);
		
		// setup email template vars
		$email_vars = array(
		    'email' => $email,
		    'pass' => $pass,
		    'check_id' => $check_id
		  );
		
		// send new password email
		Mail::sendMail($email,
		               "Nouveau mot de passe",
		               'ini_pass2',
		               $email_vars);
		
		$erreur_var = false;
	}
	else
		$erreur_var = TRUE;
	// fin vérification des variables
}
else	$erreur_url = TRUE;
// fin traitement des variables URL

// Controller-View limit
DBPal::finish();

$title = "Activation de ton compte";
?>
<? require("tpl/haut.php"); ?>
<? if ($erreur_url) require("tpl/erreur_url.php"); else if ($erreur_var) require("tpl/erreur_var.php"); else { ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<h2>Mot de passe réinitialisé</h2>
			<p>Le mot de passe pour ton compte <strong><?=htmlspecialchars($email)?></strong> a été réinitialisaé avec succès. Un courriel avec ton nouveau mot de passe vient de t'être envoyé.</p>
			<p><a href=".">Retour au site</a></p>
		</div>
<? } ?>
<? require("tpl/bas.php"); ?>
