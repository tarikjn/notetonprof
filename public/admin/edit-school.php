<?
define("ROOT_DIR", "../");
require(ROOT_DIR."_ini.php");

$user->requireOrRedirect(Admin::ACC_DATA);

// initialisation des variables
unset($err, $notice);

$id = (int) @$_GET["id"];
$return = (@$_GET['return'])? true : false;
		
if (!$id)
    $err = "Format d'identifiant incorrect.";
else
{
	$test_row = DBPal::getRow("SELECT moderated, open_ticket FROM etablissements WHERE id = $id and status = 'ok'");
	
	if (!$test_row)
		$err = "L'établissement portant cet identifiant est introuvable.";
	else if ($user->power < Admin::ACC_ALL_DATA && !DBPal::getOne("SELECT COUNT(*) FROM delegues_etblts WHERE etblt_id = $id AND delegue_id = {$user->uid}"))
    	$err = "Tu n'es pas délégué pour cet établissement.";
    else if ($test_row->open_ticket and $user->power < Admin::ACC_DATA_TICKET)
		$err = "Pouvoirs insuffisants pour opérer sur un établissement avec siglement(s).";
	else if ($test_row->moderated = 'raised' and $user->power < Admin::ACC_RAISED_OBJECT)
		$err = "Établissement en attente de vérification par un Administrateur.";
}

if (!@$err)
{
	// TODO: move to bottom to be able to update this data
	$result = DBPal::query("SELECT cursus, villes.nom AS commune, villes.id AS c_id, dept, cp FROM etablissements, villes WHERE etablissements.id = $id && villes.id = etablissements.ville_id");
	$etblt = $result->fetch_assoc();
	$cursus = $etblt["cursus"];
	
	if ($_SERVER["REQUEST_METHOD"] == 'POST')
	{
		// common HTTP POST data
		$notes = (strlen(@$_POST['notes']))? $_POST['notes'] : null; // uses DBPal::str2null after
		
		if (@$_POST["action"] == "Supprimer") // TODO: must be independant from template for i18n
		{
			// delete school and orphan dependant data in cascade, log and remove associated assignements
		    App::deleteSchool($id, $notes);
		    		    
		    $_SESSION[($return)? "success" : "msg"] = "L'établissement #$id a été supprimé.";
		    
		    // redirect to appropriate page
		    Web::redirect(($return)? "/admin/data" : "/etblts/$cursus/" . $etblt["c_id"]);
		}
		else // normal update
		{
			/* begin - processing HTTP POST data */
			$moderate = (in_array(@$_POST['moderate'], array('accept', 'raise', 'delete')))? $_POST['moderate'] : null;
			
		    $nom = @$_POST["nom"];
		    
		    if ($cursus == E_2ND)
		    	$secondaire = @$_POST["secondaire"];
			/* end - processing HTTP POST data */
		    
		    if (strlen($nom) < 1)
		    	$notice["nom"] = "Remplis le champ <cite>Nom</cite>.";
		    if ($cursus == E_2ND && (!isset($secondaire["college"]) && !isset($secondaire["lycee"])))
		    	$notice["secondaire"] = "Sélectionne au moins un type d'enseignement secondaire.";
		    if ($moderate == 'delete')
		    	$notice['moderate'] = "Pour rejeter cet objet, utilise le bouton <cite>Supprimer</cite>.";
		    
		    if (!@$notice)
		    {
		    	$log_msg = array();
		    
		    	// determine updated data
		    	$new_data = array(
		    	    "nom" => $nom
		    	  );
		    	App::appendSecondary($new_data, $cursus, $secondaire);
		    	
		    	$prev_data = (array) DBPal::getRow("SELECT nom".(($cursus == E_2ND)?", secondaire":"")." FROM etablissements WHERE id = $id");
		    	
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
					App::processReports($_POST['report'], array('school', $id), $user, $test_row->open_ticket);
		    	
		    	// update
		    	DBPal::query(
		    		  "UPDATE etablissements SET id = id"
		    		  
		    		. (($moderate)?
		    		  "  , moderated = '$new_moderate'" : "")
		    				    		
		    		. ((sizeof($updated_data))?
		    		  "  , " . DBPal::arr2set($updated_data) : "")
		    		
		    		. " WHERE id = $id"
		    	  );
		    	
		    	// log
		    	App::log($log_msg, "school", $id, $user->uid, $updated_data, $notes);
		    	
		    	// refresh assignements
		    	App::queue('refresh-assignments', array('school', $id));
		    	
		    	$success = "Modifications enregistrées avec succès.";
		    	
		    	if ($return)
		    	{
		    		$_SESSION["success"] = $success;
		    		Web::redirect("/admin/data");
		    	}
		    }
		}
	}	
	
	$result = DBPal::query("SELECT *, FIND_IN_SET('college', secondaire) > 0 AS college, FIND_IN_SET('lycee', secondaire) > 0 AS lycee FROM etablissements WHERE id = $id");
	$school = $result->fetch_object();
	
	$nom = $school->nom;
	$secondaire["college"] = $school->college;
	$secondaire["lycee"] = $school->lycee;
	
	if ($school->open_ticket) {
		 $reports = App::getReports('school', $id);
	}
	
	$logs = DBPal::query("SELECT *, UNIX_TIMESTAMP(time) AS time FROM logs LEFT JOIN delegues ON delegues.id = actor_id WHERE object_type = 'school' AND object_id = $id ORDER BY time DESC");
}

// Controller-View limit
DBPal::finish();

$title = "Éditer un établissement";
?>
<? require("tpl/haut.php"); ?>
<? if (@$err) { ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <a href="admin/index">Espace Délégué</a> &gt; <?=htmlspecialchars($title)?></div>
<? } else { ?>
		<div class="navi">
			<a href=".">Accueil</a> &gt;
			<a href="indicatifs/<?=urlencode($cursus)?>">Enseignement <?=Geo::$COURSE[$cursus]?></a> &gt;
			<a href="depts/<?=urlencode($cursus)?>/<?=urlencode(Geo::$DEPT[$etblt['dept']]["ind"])?>/">Indicatif <?=htmlspecialchars(Geo::$DEPT[$etblt['dept']]["ind"])?></a> &gt;
			<a href="villes/<?=urlencode($cursus)?>/<?=urlencode($etblt['dept'])?>/"><?=htmlspecialchars($etblt['dept'])?> - <?=htmlspecialchars(Geo::$DEPT[$etblt['dept']]["nom"])?></a> &gt;
			<a href="etblts/<?=urlencode($cursus)?>/<?=urlencode($etblt['c_id'])?>/"><?=htmlspecialchars($etblt['cp'])?> - <?=htmlspecialchars($etblt['commune'])?></a> &gt;
			<a href="profs2/<?=urlencode($id)?>/"><span class="etab"><?=htmlspecialchars($nom)?></span></a> &gt;
			Éditer
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
			<h3>Commune</h3>
			<p class="upper"><?=htmlspecialchars($etblt["commune"])?> (<?=htmlspecialchars($etblt["cp"])?>)</p>
			<p><strong>Cursus :</strong> <?=htmlspecialchars(Geo::$COURSE[$cursus])?></p>
			<p class="indic">TODO: change here directly -- Pour changer la commune ou le cursus, contacte les <a href="mailto:modos@notetonprof.fr">modérateurs</a>.</p>
			<form action="<?=$_SERVER["REQUEST_URI"]?>" method="post">
				<h3>Données</h3>
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
<?
	if ($school->moderated != 'yes')
	{
?>
				<h3>Modération</h3>
				<div class="info-section">
					<p>Cet établissement nécessite une action de modération de ta part :</p>
					<div class="info"><a href="/admin/help#data-moderate">Aide</a></div>
				</div>
				<div class="highlight-select">
					<label class="selected">
					    <input type="radio" name="moderate" value="" checked="checked" />
					    <span>Ne pas modérer</span>
					</label>
					<label class="accept">
					    <input type="radio" name="moderate" value="accept" />
					    <span>Accepter cet établissement</span>
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
	if ($school->open_ticket)
	{
?>
				<h3>Signalements ouverts</h3>
				<div class="info-section">
					<p>Cet établissement a des signalements non résolus qui requièrent une action de ta part :</p>
					<div class="info"><a href="/admin/help#data-solve">Aide</a></div>
				</div>
				<div class="report-list">
<?
		while ($report = array_shift($reports))
		{
?>
			    	<div class="report <?=($report->actor_id === 0)? 'system' : 'alert'?><?=($report->defered)?' defered':''?>">
			    	    <div class="date"><?=strftime("%x", $report->time)?></div>
			    	    <div class="notes"><?=htmlspecialchars($report->note)?></div>
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
					<input type="submit" name="action" value="Supprimer" class="delete-btn" onclick="return confirm('Es-tu sûr de vouloir supprimer cet établissement ?');" />
				</div>
				<p class="indic">Attention ! Si tu supprimes un établissement, les professeurs et les évaluations ne seront plus disponibles.</p>
			</form>
			<hr class="space" />
			<h2>Log</h2>
			<?=Helper::formatLog($logs)?>
<? } ?>
		</div>
<? require("tpl/bas.php"); ?>
