<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

// début traitement des variables URL
if (@isset($_GET["cursus"]) && @isset($_GET["cp"]))
{
	$cursus = $_GET["cursus"];
	$cp = $_GET["cp"];
	
	$erreur_url = FALSE;
	
	// début vérification des variables
	$query = "SELECT dept FROM villes WHERE cp = " . DBPal::quote($cp) . " LIMIT 1;";
	$result = DBPal::query($query);
	$rnb = $result->num_rows;
	
	if (isset(Geo::$COURSE[$cursus]) && $rnb)
	{
		$row = $result->fetch_assoc();
		$dept = $row["dept"];
				
		if (isset($_SESSION["msg"]))
		{
			$message = $_SESSION["msg"];
			unset($_SESSION["msg"]);
		}
		else
			$message = FALSE;
		
		$erreur_var = FALSE;
	}
	else
	{
		$erreur_var = TRUE;
	}
	// fin vérification des variables
}
else	$erreur_url = TRUE;
// fin traitement des variables URL

// début traitement formulaire
if (!$erreur_url && !$erreur_var)
{
	$sent = @$_POST["sent"];
	
	if ($sent)
	{
		$c_id = (int) @$_POST["c_id"];
		$nom = @$_POST["nom"];
		if ($cursus == E_2ND)
			$secondaire = @$_POST["secondaire"];
		
		$query = "SELECT id FROM villes WHERE id = $c_id";
		$result = DBPal::query($query);
		$rnb = $result->num_rows;
		
		if (strlen($nom) < 1)
			$notice["nom"] = "Remplis le champ <cite>Nom</cite>.";
		if ($cursus == E_2ND && (!isset($secondaire["college"]) && !isset($secondaire["lycee"])))
			$notice["secondaire"] = "Sélectionne au moins un type d'enseignement secondaire.";
		// vérification fantôme
		if (!$rnb)
			$notice["c_id"] = "Identifiant de la commune incorrect.";
		
		// check the reCAPTCHA
		Web::checkReCaptcha($user, $notice);
		
		// check if the network is blacklisted
		Web::checkBlacklisted($notice);
		
		if (!$notice)
		{
			// create associative array for school data
			$new_school = array(
			    "cursus" => $cursus,
			    "ville_id" => $c_id,
			    "nom" => $nom
			  );
			App::appendSecondary($new_school, $cursus, @$secondaire);
			
			// insert school and log
			$i_id = App::createObjectAndLog('school', $new_school, $user->uid);
			
			// assign moderation of the school
			if (Admin::MOD_SCHOOL)
				App::queue('refresh-assignments', array('for-object', 'school', $i_id));
				
			// changement de page
			$_SESSION["msg"] = (Admin::MOD_SCHOOL)?"Ton établissement a bien été soumis, il apparaîtra dans la liste ci-dessous dès qu'il aura été validé par un administrateur.":"Ton établissement a été ajouté avec succès.";
			Web::redirect("/etblts/".rawurlencode($cursus)."/".rawurlencode($c_id)."/");
		}
		
	}
	else
		$notice = NULL;
	
	// lecture de la liste des communes pour le code postal donné
	$query = "SELECT id, nom FROM villes WHERE cp = " . DBPal::quote($cp) . " ORDER by nom;";
	$result = DBPal::query($query);
}
// fin traitement formulaire

// Controller-View limit
DBPal::finish();

$title = "Ajout d'un établissement";
?>
<? require("tpl/haut.php"); ?>
<? if ($erreur_url) require("tpl/erreur_url.php"); else if ($erreur_var) require("tpl/erreur_var.php"); else { ?>
		<div class="navi">
			<?=Helper::navPath(array(
				$cursus,
				Geo::$DEPT[$dept]['ind'],
				$dept
			))?>
			&gt; <?=htmlspecialchars($title)?>
		</div>
		<hr />
		<div>
			<h2>Formulaire d'ajout d'établissement</h2>
<? if ($message) { ?>
			<p class="msg"><?=$message?></p>
<? } ?>
			<p>Étape 2/2, entrer les informations sur l'établissement :</p>
<? if ($notice) { ?>
			<div class="head-notice">
				<p>Attention ! Vérifie les données du formulaire :</p>
				<ul><? foreach ($notice as $alert) { ?><li><?=$alert?></li><? } ?></ul>
			</div>
<? } ?>
			<form action="<?=$_SERVER["SCRIPT_NAME"]?>?cursus=<?=urlencode($cursus)?>&amp;cp=<?=urlencode($cp)?>" method="post" class="form">
				<input type="hidden" name="sent" value="1" />
				<dl>
					<dt>Étape 1/2</dt>
					<dd>Code Postal : <a href="ajout_etblt?cursus=<?=urlencode($cursus)?>&amp;dept=<?=urlencode($dept)?>" title="Choisir un autre code postal"><?=htmlspecialchars($cp)?></a></dd>
					<dt>Étape 2/2</dt>
					<dd>
						<label for="c_id">
							Commune : 
							<select id="c_id" name="c_id">
<? while($row = $result->fetch_assoc()) { ?>
								<option value="<?=$row["id"]?>"<?=@($c_id == $row["id"])?" selected=\"selected\"":""?>><?=$row["nom"]?></option>
<? } ?>
							</select>
						</label>
						<label for="nom"<?=(@$notice["nom"])?" class=\"notice\"":""?>>Nom de l'établissement : <input type="text" name="nom" id="nom" value="<?=@htmlspecialchars($nom)?>" maxlength="50" /></label>
<? if ($cursus == E_2ND) { ?>
						<p class="indic">Ne pas inclure <cite>Collège</cite> ou <cite>Lycée</cite>.</p>
						<fieldset<?=(@$notice["secondaire"])?" class=\"notice\"":""?>>
							<legend>Enseignement assuré</legend>
							<label for="secondaire[college]">
								<input type="checkbox" value="1" name="secondaire[college]" id="secondaire[college]"<?=@($secondaire["college"])?" checked=\"checked\"":""?> />
								Collège
							</label>
							<label for="secondaire[lycee]">
								<input type="checkbox" value="1" name="secondaire[lycee]" id="secondaire[lycee]"<?=@($secondaire["lycee"])?" checked=\"checked\"":""?> />
								Lycée
							</label>
						</fieldset>
<? } ?>
					</dd>
					<?=Web::getReCaptcha($user)?>
					<dt>Validation</dt>
					<dd><input type="submit" value="Terminer" /></dd>
				</dl>
			</form>
		</div>
<? } ?>
<? require("tpl/bas.php"); ?>
