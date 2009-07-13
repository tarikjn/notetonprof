<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

// début traitement des variables URL
if (isset($_GET["email"]) && isset($_GET["code"]))
{
	$email = $_GET["email"];
	$code = $_GET["code"];
	
	$erreur_url = FALSE;

	// début vérification des variables
	$query = "SELECT id FROM delegues WHERE email = " . DBPal::quote($email) . " && checked = 0 && check_id = " . DBPal::quote($code);
	$rnb = DBPal::getOne($query);
	
	if ($rnb)
	{
		DBPal::query(
		    "UPDATE delegues SET checked = 1"
		  . "  WHERE email = " . DBPal::quote($email) . " AND checked = 0 AND check_id = " . DBPal::quote($code)
		);
		
		App::log("Confirmed e-mail address", "user", $rnb, null);
		
		$erreur_var = FALSE;
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
			<h2>Compte activé</h2>
			<p>Félicitations ! Ton compte <strong><?=htmlspecialchars($email)?></strong> est à présent actif, tu peux maintenant être délégué des établissements de ton choix.</p>
			<p><a href=".">Retour au site</a></p>
		</div>
<? } ?>
<? require("tpl/bas.php"); ?>
