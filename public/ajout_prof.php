<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

// début traitement des variables URL
if (@isset($_GET["etblt_id"]))
{
	$e_id = (int) $_GET["etblt_id"];
	
	$erreur_url = FALSE;
	
	// début vérification des variables
	$query = "SELECT etablissements.nom, cursus, dept, cp, villes.nom AS commune, villes.id AS c_id FROM etablissements, villes WHERE villes.id = etablissements.ville_id AND etablissements.status = 'ok' AND etablissements.id = $e_id".((Admin::MOD_SCHOOL)?" && etablissements.moderated = 'yes'":"");
	$result = DBPal::query($query);
	$rnb = $result->num_rows;
	
	if ($rnb)
	{
		$row = $result->fetch_assoc();
		$cursus = $row["cursus"];
		$dept = $row["dept"];
		$cp = $row["cp"];
		$e_nom = $row["nom"];
		$c_nom = $row["commune"];
		$c_id = $row["c_id"];
		
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
		$nom = @$_POST["nom"];
		$prenom = @$_POST["prenom"];
		$matiere = (int) @$_POST["matiere"];
		if ($cursus == E_SUP)
			$sujet = @$_POST["sujet"];
		
		$query = "SELECT id FROM matieres WHERE id = $matiere && cursus = '$cursus'";
		$result = DBPal::query($query);
		$rnb = $result->num_rows;
		
		if (strlen($nom) < 1)
			$notice["nom"] = "Remplis le champ <cite>Nom</cite>.";
		if (strlen($prenom) < 1)
			$notice["prenom"] = "Remplis le champ <cite>Prénom</cite>.";
		// vérification fantôme
		if (!$rnb)
			$notice["matiere"] = "Identifiant de la matière incorrect.";
		if (!$notice)
		{
			// create associative array for prof data
			$new_prof = array(
			    "etblt_id" => $e_id,
			    "nom" => $nom,
			    "prenom" => $prenom,
			    "matiere_id" => $matiere
			  );
			if ($cursus == E_SUP)
				$new_prof["sujet"] = $sujet;
			
			// TODO: howto not log columns that won't change?
			// insert prof and log
			$i_id = App::createObjectAndLog('prof', $new_prof);
			
			// assign moderation of the prof
			if (Admin::MOD_PROF)
				App::queue('refresh-assignments', array('for-object', 'prof', $i_id));
				
			// changement de page
			$_SESSION["msg"] = (Admin::MOD_PROF)?"Ce professeur a bien été référencé, il apparaîtra dans la liste ci-dessous dès qu'il aura été validé par un délégué.":"Ce professeur a été référencé avec succès.";
			Web::redirect("/profs2/".rawurlencode($e_id)."/");
		}
		
	}
	else
		$notice = FALSE;
	
	// lecture de la liste des matières
	$result = DBPal::query("SELECT * FROM matieres WHERE cursus = '$cursus' ORDER by type, ".(($cursus == E_2ND) ? "id" : "nom"));
	while($row = $result->fetch_assoc())
		$_matieres[$row["type"]][$row["id"]] = $row["nom"];
}
// fin traitement formulaire

// Controller-View limit
DBPal::finish();

$title = "Ajout d'un professeur";
?>
<? require("tpl/haut.php"); ?>
<? if ($erreur_url) require("tpl/erreur_url.php"); else if ($erreur_var) require("tpl/erreur_var.php"); else { ?>
		<div class="navi">
			<?=Helper::navPath(array(
				$cursus,
				Geo::$DEPT[$dept]['ind'],
				$dept,
				array($c_id, $cp, $c_nom),
				array($e_id, $e_nom)
			))?>
			&gt; <?=htmlspecialchars($title)?>
		</div>
		<hr />
		<div>
			<h2>Formulaire d'ajout d'un professeur</h2>
			<p>Remplis le formulaire suivant pour ajouter un professeur non référencé dans l'établissement <cite><span class="etab"><?=htmlspecialchars($e_nom)?></span> (<?=htmlspecialchars($c_nom)?>)</cite> :</p>
<? if ($notice) { ?>
			<div class="head-notice">
				<p>Attention ! Vérifie les données du formulaire :</p>
				<ul><? foreach ($notice as $alert) { ?><li><?=$alert?></li><? } ?></ul>
			</div>
<? } ?>
			<form action="<?=$_SERVER["SCRIPT_NAME"]?>?etblt_id=<?=urlencode($e_id)?>" method="post" class="form">
				<input type="hidden" name="sent" value="1" />
				<dl>
					<dt>Formulaire</dt>
					<dd>
						<fieldset>
							<legend>Identité de ton prof</legend>
							<label for="nom"<?=(@$notice["nom"])?" class=\"notice\"":""?>>Nom : <input type="text" name="nom" id="nom" value="<?=@htmlspecialchars($nom)?>" maxlength="50" /></label>
							<label for="prenom"<?=(@$notice["prenom"])?" class=\"notice\"":""?>>Prénom / Civilité : <input type="text" name="prenom" id="prenom" value="<?=@htmlspecialchars($prenom)?>" maxlength="50" /></label>
							<p class="indic">Si tu ne connais pas le prénom de ton enseignant, tu peux indiquer sa civilité : <cite>Mlle</cite>, <cite>Mme</cite> ou <cite>M.</cite> (avec un point, ne pas utiliser Mr qui est le terme anglo-saxon).</p>
							<p class="indic">Les noms, les prénoms et les civilités prennent une Majuscule !</p>
						</fieldset>
						<fieldset>
							<legend>Profession</legend>
							<label for="matiere">
								Matière : 
								<select id="matiere" name="matiere">
<? foreach ($_matieres as $type => $mats) { ?>
<? if ($cursus == E_2ND) { ?>
									<optgroup label="<?=htmlspecialchars($type)?>">
<? foreach ($mats as $id => $mat) { ?>
										<option value="<?=$id?>"<?=@($matiere == $id)?" selected=\"selected\"":""?>><?=htmlspecialchars($mat)?></option>
<? } ?>
									</optgroup>
<? } else { ?>
<? foreach ($mats as $id => $mat) { ?>
									<option value="<?=$id?>"<?=@($matiere == $id)?" selected=\"selected\"":""?>><?=htmlspecialchars($mat)?></option>
<? } ?>
<? } ?>
<? } ?>
								</select>
							</label>
<? if ($cursus == E_SUP) { ?>
							<label for="sujet" class="facultatif<?=(@$notice["sujet"])?" notice":""?>">
								Sujet<a href="http://<?=$_SERVER["HTTP_HOST"]?><?=$_SERVER["REQUEST_URI"]?>#facultatif">*</a> : <input type="text" name="sujet" id="sujet" value="<?=@htmlspecialchars($sujet)?>" maxlength="50" />
							</label>
<? } ?>
						</fieldset>
<? if ($cursus == E_SUP) { ?>
						<p class="facultatif etoile" id="facultatif">*Champ facultatif</p>
<? } ?>
					</dd>
					<dt>Validation</dt>
					<dd><input type="submit" value="Terminer" /></dd>
				</dl>
			</form>
		</div>
<? } ?>
<? require("tpl/bas.php"); ?>
