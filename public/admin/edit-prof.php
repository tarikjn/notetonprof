<?
define("ROOT_DIR", "../");
require(ROOT_DIR."_ini.php");

$user->requireOrRedirect();

// initialisation des variables
unset($err, $notice);

$id = (int) @$_GET["id"];
$return = (@$_GET['return'])? true : false;
		
if (!$id)
	$err = "Format d'identifiant incorrect.";
else
{
	$test_row = DBPal::getRow("SELECT moderated, open_ticket FROM professeurs WHERE id = $id and status = 'ok'");
	
	if (!$test_row)
		$err = "Le professeur portant cet identifiant est introuvable.";
	
	else if ($user->power < Admin::ACC_ALL_DATA && !DBPal::getOne("SELECT COUNT(*) FROM professeurs, etablissements, delegues_etblts WHERE professeurs.id = $id && professeurs.etblt_id = etablissements.id && etablissements.id = delegues_etblts.etblt_id && delegues_etblts.delegue_id = {$user->uid}"))
    	$err = "Tu n'es pas délégué pour l'établissement de ce professeur.";
    else if ($test_row->open_ticket and $user->power < Admin::ACC_DATA_TICKET)
		$err = "Pouvoirs insuffisants pour opérer sur un professeur avec siglement(s).";
	else if ($test_row->moderated = 'raised' and $user->power < Admin::ACC_RAISED_OBJECT)
		$err = "Professeur en attente de vérification par un Administrateur.";
}

if (!@$err)
{
	$result = DBPal::query("SELECT etablissements.nom AS etblt, etablissements.id AS etblt_id, cursus, secondaire, villes.nom AS commune, villes.id AS c_id, dept, cp FROM professeurs, etablissements, villes WHERE professeurs.id = $id && professeurs.status = 'ok' && etablissements.id = professeurs.etblt_id && villes.id = etablissements.ville_id;");
	$school = $result->fetch_assoc();
	$cursus = $school["cursus"];
	
	if ($_SERVER["REQUEST_METHOD"] == 'POST')
	{
		// common HTTP POST data
		$notes = (strlen(@$_POST['notes']))? $_POST['notes'] : null; // uses DBPal::str2null after
		
		if (@$_POST["action"] == "Supprimer") // TODO: must be independant from template for i18n
		{
			// delete prof and orphan dependant data in cascade, log and remove associated assignments
		    App::deleteProf($id, $notes);
		    		    
		    $_SESSION[($return)? "success" : "msg"] = "Le professeur #$id a été supprimé.";
		    
		    // redirect to appropriate page
		    Web::redirect(($return)? "/admin/data" : "/profs/" . $school['etblt_id']);
		}
		else // normal update
		{
			/* begin - processing HTTP POST data */
			$moderate = (in_array(@$_POST['moderate'], array('accept', 'raise', 'delete')))? $_POST['moderate'] : null;
			
		    $nom = @$_POST["nom"];
			$prenom = @$_POST["prenom"];
			$matiere = (int) @$_POST["matiere"];
			if ($cursus == E_SUP)
				$sujet = @$_POST["sujet"];
			/* end - processing HTTP POST data */
			
			$check_major = DBPal::getOne("SELECT COUNT(*) FROM matieres WHERE id = $matiere && cursus = '$cursus'");
			
			if (strlen($nom) < 1)
				$notice["nom"] = "Remplis le champ <cite>Nom</cite>.";
			if (strlen($prenom) < 1)
				$notice["prenom"] = "Remplis le champ <cite>Prénom</cite>.";
			if (!$check_major)
				$notice["matiere"] = "Identifiant de la matière incorrect.";
			
			if ($moderate == 'delete')
		    	$notice['moderate'] = "Pour rejeter cet objet, utilise le bouton <cite>Supprimer</cite>.";
			
			if (!@$notice)
		    {
		    	$log_msg = array();
		    
		    	// determine updated data
		    	$new_data = array(
		    	    "nom" => $nom,
		    	    "prenom" => $prenom,
		    	    "matiere_id" => $matiere
		    	  );
		    	if ($cursus == E_SUP)
		    		$new_data["sujet"] = $sujet;
		    	
		    	$prev_data = (array) DBPal::getRow("SELECT nom, prenom, matiere_id".(($cursus == E_2ND)?", sujet":"")." FROM professeurs WHERE id = $id");
		    	
		    	$updated_data = array_diff_assoc($new_data, $prev_data);
		    	if (sizeof($updated_data))
		    	{
		    		$log_msg[] = 'Changed details';
		    	}
		    	
		    	// process moderate
		    	if ($moderate)
		    	{
		    		$new_moderate = ($moderate == 'accept')? 'yes' : 'raise';
		    		$log_msg[] = 'Moderated: ' . (($moderate == 'accept')? 'accepted' : 'raised');
		    	}
		    	
		    	// process reports
		    	if (@$_POST['report'])
					$new_open_ticket = App::processReports($_POST['report'], array('prof', $id), $user, $test_row->open_ticket);
		    		
		    	// update
		    	DBPal::query(
		    		  "UPDATE professeurs SET id = id"
		    		  
		    		. (($moderate)?
		    		  "  , moderated = '$new_moderate'" : "")
		    				    		
		    		. ((sizeof($updated_data))?
		    		  "  , " . DBPal::arr2set($updated_data) : "")
		    		
		    		. " WHERE id = $id"
		    	  );
		    	
		    	// log
		    	App::log($log_msg, "prof", $id, $user->uid, $updated_data, $notes);
		    	
		    	// if no more tickets and moderated -> clear assignments
		    	if (($test_row->moderated == 'yes' or @$new_moderate == 'yes')
		    		and ($test_row->open_ticket == null or @$new_open_ticket == false))
		    	{
		    		DBPal::query("DELETE FROM assignments WHERE object_type = 'prof' AND object_id = $id");
		    	}
		    	else
		    	{
		    		// TODO: if raised or no moderation and no tickets or tickets are defered -> clear specific to admin
		    		
		    		// refresh assignments
		    		App::queue('refresh-assignments', array('for-object', 'prof', $id));
		    	}
		    	
		    	$success = "Modifications enregistrées avec succès.";
		    	
		    	if ($return)
		    	{
		    		$_SESSION["success"] = $success;
		    		Web::redirect("/admin/data");
		    	}
		    }
		}
	}
	
	$prof = DBPal::getRow("SELECT * FROM professeurs WHERE id = $id");
	
	$nom = $prof->nom;
	$prenom = $prof->prenom;
	$matiere = $prof->matiere_id;
	$sujet = $prof->sujet;
	
	// lecture de la liste des matières
	$query = "SELECT * FROM matieres WHERE cursus='$cursus' ORDER by type, ".(($cursus == E_2ND) ? "id" : "nom");
	$result = DBPal::query($query);
	while($row = $result->fetch_assoc())
		$_matieres[$row["type"]][$row["id"]] = $row["nom"];
	
	if ($prof->open_ticket) {
		 $reports = App::getReports('prof', $id);
	}
	
	$logs = DBPal::query("SELECT *, UNIX_TIMESTAMP(time) AS time FROM logs LEFT JOIN delegues ON delegues.id = actor_id WHERE object_type = 'prof' AND object_id = $id ORDER BY time DESC");
}

// Controller-View limit
DBPal::finish();

$title = "Éditer un professeur";
?>
<? require("tpl/haut.php"); ?>
<? if (@$err) { ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <a href="/admin/index">Espace Délégué</a> &gt; <?=htmlspecialchars($title)?></div>
<? } else { ?>
		<div class="navi">
			<?=Helper::navPath(array(
				$cursus,
				Geo::$DEPT[$school['dept']]["ind"],
				$school['dept'],
				array($school['c_id'], $school['cp'], $school['commune']),
				array($school["etblt_id"], $school["etblt"]),
				array($id, $prenom, $nom)
			))?>
			&gt; Éditer
		</div>
<? } ?>
		<hr />
		<div>
			<h2><?=htmlspecialchars($title)?></h2>
<? if (@$err) { ?>
			<div class="head-notice"><?=$err?></div>
<? } else { ?>
<? if (@$notice) { ?>
			<div class="head-notice">
				<p>Attention ! Vérifie les données du formulaire :</p>
				<ul><? foreach ($notice as $alert) { ?><li><?=$alert?></li><? } ?></ul>
			</div>
<? } else if (@$success) { ?>
			<div class="msg"><?=$success?></div>
<? } ?>
			<h3>Établissement</h3>
			<p><strong>Cursus :</strong> <?=htmlspecialchars(Geo::$COURSE[$cursus])?></p>
			<a href="profs/<?=$school["etblt_id"]?>/" class="upper"><?=Helper::schoolTitle($school["cursus"], $school["secondaire"], $school["etblt"])?> (<?=htmlspecialchars($school["commune"])?>, <?=htmlspecialchars($school["dept"])?>)</a>
			<p class="indic">Tu ne peux pas changer l'établissement dans la fiche d'un professeur. Si ce prof n'a jamais enseigné dans cet établissement, il faut effacer la fiche.</p>
			<form action="<?=$_SERVER["REQUEST_URI"]?>" method="post">
				<h3>Données</h3>
				<fieldset>
					<legend>Identité</legend>
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
<? foreach ($mats as $mid => $mat) { ?>
								<option value="<?=$mid?>"<?=@($matiere == $mid)?" selected=\"selected\"":""?>><?=htmlspecialchars($mat)?></option>
<? } ?>
							</optgroup>
<? } else { ?>
<? foreach ($mats as $mid => $mat) { ?>
							<option value="<?=$mid?>"<?=@($matiere == $mid)?" selected=\"selected\"":""?>><?=htmlspecialchars($mat)?></option>
<? } ?>
<? } ?>
<? } ?>
						</select>
					</label>
<? if ($cursus == E_SUP) { ?>
					<label for="sujet" class="facultatif<?=(@$notice["sujet"])?" notice":""?>">
						Sujet* : <input type="text" name="sujet" id="sujet" value="<?=@htmlspecialchars($sujet)?>" maxlength="50" />
					</label>
<? } ?>
				</fieldset>
<? if ($cursus == E_SUP) { ?>
				<p class="facultatif etoile" id="facultatif">*Champ facultatif</p>
<? } ?>
<?
	if ($prof->moderated != 'yes')
	{
?>
				<h3>Modération</h3>
				<div class="info-section">
					<p>Ce professeur nécessite une action de modération de ta part :</p>
					<div class="info"><a href="/admin/help#data-moderate">Aide</a></div>
				</div>
				<div class="highlight-select">
					<label class="selected">
					    <input type="radio" name="moderate" value="" checked="checked" />
					    <span>Ne pas modérer</span>
					</label>
					<label class="accept">
					    <input type="radio" name="moderate" value="accept" />
					    <span>Accepter ce professeur</span>
					</label>
					<label class="reject">
					    <input type="radio" name="moderate" value="delete" />
					    <span>Rejeter / Supprimer</span>
					</label>
					<label class="raise<?=($user->power >= Admin::ACC_RAISED_OBJECT)? ' disabled' : ''?>">
					    <input type="radio" name="moderate" value="raise"<?=($user->power >= Admin::ACC_RAISED_OBJECT)? ' disabled="disabled"' : ''?> />
					    <span>Remonter</span>
					</label>
					<p class="indic">
						Si après avoir lu l'aide, tu ne sais pas quelle action prendre, "Remonter" te permet d'assigner la modération à un administrateur.
					</p>
				</div>
<?
	}
?>
<?
	if ($prof->open_ticket)
	{
?>
				<h3>Signalements ouverts</h3>
				<div class="info-section">
					<p>Ce professeur a des signalements non résolus qui requièrent une action de ta part :</p>
					<div class="info"><a href="/admin/help#data-solve">Aide</a></div>
				</div>
				<div class="report-list">
<?
		while ($report = array_shift($reports))
		{
?>
			    	<div class="report <?=($report->actor_id === 0)? 'system' : 'alert'?><?=($report->defered)?' defered':''?>">
			    	    <div class="date"><?=strftime("%x", $report->time)?></div>
			    	    <div class="notes"><?=htmlspecialchars($report->description)?></div>
			    	    <div class="action">
			    	    	Action : 
							<select name="report[<?=$report->id?>]">
								<option value=""></option>
								<option value="close">Clôre</option>
								<option value="defer"<?=($report->defered)? ' disabled="disabled"' : ''?>>Reporter</option>
							</select>
			    	    </div>
			    	</div>
<?
		}
?>
			    </div>
<?
	}
?>
				<h3>Action</h3>
				<label class="facultatif">
					Notes sur l'action prise* :<br />
					<input type="text" name="notes" size="50" maxlength="150" />
				</label>
				<p class="facultatif etoile">*Champ facultatif</p>
				<div class="save">
					<input type="submit" name="action" value="Mettre à jour" class="update-btn" />
					<input type="submit" name="action" value="Supprimer" class="delete-btn" onclick="return confirm('Es-tu sûr de vouloir supprimer ce professeur ?');" />
				</div>
				<p class="indic">
					Attention ! Si tu effaces la fiche d'un professeur, les évaluations ne seront plus disponibles.<br />
					La fiche d'un professeur ne doit pas être effacée si le professeur quitte l'établissement.
				</p>
			</form>
			<hr class="space" />
			<h2>Log</h2>
			<?=Helper::formatLog($logs)?>
<? } ?>
		</div>
<? require("tpl/bas.php"); ?>
