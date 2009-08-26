<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

// début traitement des variables URL
$expl = @explode("/", $_SERVER["PATH_INFO"]);
if (@strlen($expl[1]) > 0)
{
	$e_id = (int) urldecode($expl[1]);
	
	$erreur_url = FALSE;

	// début vérification des variables
	$q_check_vars = "SELECT etablissements.nom, cursus, secondaire, dept, cp, villes.nom AS commune, villes.id as c_id FROM etablissements, villes WHERE etablissements.status = 'ok' AND etablissements.id = $e_id && villes.id = etablissements.ville_id".((Admin::MOD_SCHOOL)?" && etablissements.moderated = 'yes'":"");
	
	$result = DBPal::query($q_check_vars);
	$rnb = $result->num_rows;
	
	if ($rnb)
	{
		// redirection COOKIE
		if (isset($_COOKIE["acces"][$e_id]))
		{
			setcookie("acces[$e_id]", 1, time() + 3600 * 24 * 365, "/", Settings::COOKIE_DOMAIN);
			Web::redirect("/profs2/$e_id/");
		}
		// fin redirection COOKIE
		
		$row = $result->fetch_assoc();
		$cursus = $row["cursus"];
		$secondaire = $row["secondaire"];
		$dept = $row["dept"];
		$cp = $row["cp"];
		$e_nom = $row["nom"];
		$c_nom = $row["commune"];
		$c_id = $row["c_id"];
		
		// redirection POST
		$action = @$_POST["action"];
		
		if ($action == "Oui")
		{
			setcookie("acces[$e_id]", 1, time() + 3600 * 24 * 365, "/", Settings::COOKIE_DOMAIN);
			Web::redirect("/profs2/$e_id/");
		}
		else if ($action == "Non")
		{
			Web::redirect("/etblts/$cursus/$c_id/");
		}
		// fin redirection POST
		
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

$title = "Accès établissement";
?>
<? require("tpl/haut.php"); ?>
<? if ($erreur_url) require("tpl/erreur_url.php"); else if ($erreur_var) require("tpl/erreur_var.php"); else { ?>
		<div class="navi">
			<?=Helper::navPath(array(
				$cursus,
				Geo::$DEPT[$dept]['ind'],
				$dept,
				array($c_id, $cp, $c_nom)
			))?>
			&gt; <?=htmlspecialchars($title)?>
		</div>
		<hr />
		<div>
			<h2>Accès établissement</h2>
			<p>Pour accéder à la page de cet établissement, tu dois faire partie ou avoir fait partie de cet établissement. La page de cet établissement et les pages qui en font partie ne sont pas publics !</p>
			<form action="<?=$_SERVER["SCRIPT_NAME"]?>/<?=urlencode($e_id)?>/" method="post">
				<p><strong>Certifies-tu sur l'honneur faire ou avoir fait parti de l'établissement : <span class="etab"><? if ($cursus == E_2ND) { ?><? $secondaire = explode(",", $secondaire); foreach ($secondaire as $key => $val) { ?><?=Geo::$SECONDARY[$val].((isset($secondaire[$key + 1]))?", ":" ")?><? } ?><? } ?><?=htmlspecialchars($e_nom)?></span> (<?=htmlspecialchars($c_nom)?>), ceci en temps qu'élève ou personnel de l'établissement ?</strong></p>
				<div class="submit"><input type="submit" name="action" value="Oui" /> <input type="submit" name="action" value="Non" /></div>
			</form>
			<p class="info-add">Si les cookies sont activés sur ton navigateur et ta réponse est positive, cette page ne s'affichera plus lorsque tu voudras accéder à cet établissement.</p>
		</div>
<? } ?>
<? require("tpl/bas.php"); ?>
