<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

$rnb = 0;
$notice = FALSE;

// début traitement des variables URL
if (isset($_GET["type"]) && isset($_GET["id"]))
{
	$type = $_GET["type"];
	$id = (int) $_GET["id"];
	
	$erreur_url = FALSE;
	$erreur_var = true;

	// vérification des variables
	if ( in_array($type, array('school', 'prof', 'comment')) )
	{
		$obj = DBPal::getRow("SELECT * FROM " . Settings::$objType2tabName[$type] . " WHERE id = $id AND status = 'ok'");
		
		if ($obj)
		{
			$erreur_var = false;
			
			switch ($type)
			{
				case 'school':
					$concerne = "Établissement : " . Helper::schoolTitle($obj->cursus, $obj->secondaire, $obj->nom, $id);
					$return_url = "/profs2/$id/";
					break;
					
				case "prof":
					$concerne = "Professeur : " . Helper::profTitle($obj->prenom, $obj->nom, $id);
					$return_url = "/notes2/$id/";
					break;
					
				case "comment":
					$concerne = "Commentaire : "
					          . '<div class="comment-view">' . htmlspecialchars($obj->comment) .'</div>';
					$return_url = "/notes2/{$obj->prof_id}/";
					break;
			}
			
			// has the form been posted?
			if ($_SERVER["REQUEST_METHOD"] == 'POST')
			{
				$probleme = @$_POST["probleme"];
				
				// check the reCAPTCHA
				Web::checkReCaptcha($user, $notice);
				
				if (!$notice)
				{
					// report data
					$report = (object) array(
						'object_type' => $type,
						'object_id' => $id,
						'description' => $probleme
					);
				
					// add report (will also log and ticket object)
					App::addReport($report, $obj->open_ticket);
					
					// refresh assignments
					App::queue('refresh-assignments', array('for-object', $type, $id));
					// TODO: should now take care of emailing assignments
					
					// changement de page
					$_SESSION["msg"] = "Ton signalement a bien été pris en compte, nous ferons le nécessaire dans les plus bref délais.";
					Web::redirect($return_url);
				}
			}
		}
	}
}
else	$erreur_url = TRUE;
// fin traitement des variables URL

// Controller-View limit
DBPal::finish();

$title = "Signaler un problème";
?>
<? require("tpl/haut.php"); ?>
<? if ($erreur_url) require("tpl/erreur_url.php"); else if ($erreur_var) require("tpl/erreur_var.php"); else { ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<h2>Signaler un problème</h2>
			<p>
<? if ( in_array($type, array('school', 'prof')) ) { ?>
				Ce formulaire te permet de nous signaler une erreur ou un problème.
<? } else if ($type == "comment") { ?>
				Ce formulaire te permet de nous signaler un commentaire qui ne respecte pas les <a href="regles">règles</a>.
<? } ?>
			</p>
<? if ($notice) { ?>
			<div class="head-notice">
				<p>Attention ! Le signalement n'a pas été envoyé :</p>
				<ul><? foreach ($notice as $alert) { ?><li><?=$alert?></li><? } ?></ul>
			</div>
<? } ?>
			<form action="<?=$_SERVER["REQUEST_URI"]?>" method="post" class="form">
				<dl>
					<dt>Concerne :</dt>
					<dd><?=$concerne?></dd>
					<dt>Détails :</dt>
					<dd>
						<label for="probleme" class="facultatif">Problème* : <input type="text" name="probleme" id="probleme" value="<?=@htmlspecialchars($probleme)?>" size="65" maxlength="255" /></label>
						<p class="facultatif etoile">*Champ facultatif</p>
						<p class="indic">
<? if ( in_array($type, array('school', 'prof')) ) { ?>
							S'il s'agit d'une correction à effectuer, merci de donner les bonnes informations au complet. Ce n'est pas forcément quelqu'un de votre établissement qui sera chargé d'effectuer la correction.
<? } else if ($type == "comment") { ?>
							La seule action que nous prenons vis à vis des commentaires est la suppression des évaluations comportant un commentaire qui ne respecte pas les règles. Nous ne pouvons supprimer une évaluation ou la modifier pour une quelque autre raison.
<? } ?>
						</p>
					</dd>
					<?=Web::getReCaptcha($user)?>
					<dt>Validation</dt>
					<dd><input type="submit" value="Terminer" /></dd>
				</dl>
			</form>
		</div>
<? } ?>
<? require("tpl/bas.php"); ?>
