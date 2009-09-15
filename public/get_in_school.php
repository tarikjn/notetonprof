<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

// début traitement des variables URL
$expl = @explode("/", $_SERVER["PATH_INFO"]);
if (@strlen($expl[1]) > 0)
{
	$obj_id = (int) urldecode($expl[1]);
	$action = Web::action();
	
	$erreur_url = FALSE;
	
	// début vérification des variables
	switch($action)
	{
		case 'profs':
			$s_id = $obj_id;
			$query = "SELECT s.nom s_name, cursus, secondaire, dept, cp, c.nom c_name, c.id c_id"
			       . " FROM etablissements s, villes c"
			       . " WHERE s.status = 'ok' AND s.id = $s_id AND c.id = s.ville_id"
			       . ((Admin::MOD_SCHOOL)?
			         "  AND s.moderated = 'yes'":"");
			$goto = "/profs2/$s_id/";
			$head = "Pour accéder à la page de cet établissement, "
			      . "tu dois faire partie ou avoir fait partie de cet établissement.";
			break;
		
		case 'notes':
			$p_id = $obj_id;
			$query = "SELECT s.nom s_name, s.id s_id, cursus, secondaire, dept, cp, c.nom c_name, c.id c_id"
			       . " FROM professeurs p, etablissements s, villes c"
			       . "  WHERE p.status = 'ok' AND p.id = $p_id AND s.id = p.etblt_id AND s.status = 'ok'"
			       . "   AND c.id = s.ville_id"
			       . ((Admin::MOD_SCHOOL)?
			         "   AND s.moderated = 'yes'":"")
			       .((Admin::MOD_PROF)?
			         "   AND p.moderated = 'yes'":"");
			$goto = "/notes2/$p_id/";
			$head = "Pour accéder à la page de ce professeur, "
			      . "tu dois faire partie ou avoir fait partie de son établissement.";
			break;
	}
	         
	$result = DBPal::query($query);
	
	if ($result->num_rows)
	{
		// fetch results
		$r = $result->fetch_object();
		// for action = 'notes'
		if (!$s_id)
			$s_id = $r->s_id;
		
		// process POST
		$action = @$_POST["action"];
		
		if ($user->power >= Admin::ACC_ALL_DATA or Web::isRobot($_SERVER['HTTP_USER_AGENT']) or true)
		{
			Web::redirect($goto);
		}
		// redirection COOKIE + POST
		else if (isset($_COOKIE["acces"][$s_id]) or $action == "Oui")
		{
			// 1 year validity (refreshed)
			setcookie("acces[$s_id]", 1, time() + 3600 * 24 * 365, "/", Settings::COOKIE_DOMAIN);
			Web::redirect($goto);
		}
		// fin redirection COOKIE
		else if ($action == "Non")
		{
			Web::redirect("/etblts/{$r->cursus}/{$r->c_id}/");
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
				$r->cursus,
				Geo::$DEPT[$r->dept]['ind'],
				$r->dept,
				array($r->c_id, $r->cp, $r->c_name)
			))?>
			&gt; <?=h($title)?>
		</div>
		<hr />
		<div>
			<h2>Accès établissement</h2>
			<p>
				<?=h($head)?>
				La page de cet établissement et les pages qui en font partie ne sont pas publics !
			</p>
			<form action="<?=$_SERVER["REQUEST_URI"]?>" method="post">
				<p><strong>Certifies-tu sur l'honneur faire ou avoir fait parti de l'établissement : <span class="etab"><?=Helper::schoolTitle($r->cursus, $r->secondaire, $r->s_name)?></span> (<?=h($r->c_name)?>), ceci en temps qu'élève ou personnel de l'établissement ?</strong></p>
				<div class="submit">
					<input type="submit" name="action" value="Oui" />
					<input type="submit" name="action" value="Non" />
				</div>
			</form>
			<p class="info-add">Si les cookies sont activés sur ton navigateur et ta réponse est positive, cette page ne s'affichera plus lorsque tu voudras accéder à cet établissement.</p>
		</div>
<? } ?>
<? require("tpl/bas.php"); ?>
