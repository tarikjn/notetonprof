<?
define("ROOT_DIR", "../");
require(ROOT_DIR."_ini.php");

// requires level [[ 4 ]]
$user->requireOrRedirect(Admin::ACC_ADMINS);

$id = (int) @$_GET["id"];
$return = (@$_GET['return'])? true : false;
		
if (!$id)
    $err = "Identifiant incorrect";
else
{
	$current_data = DBPal::getRow("SELECT locked, level, open_ticket, session_id FROM delegues WHERE id = $id");
	
	if (!$current_data)
		$err = "Ce Modérateur est introuvable.";
	else if ($current_data->level >= $user->power and $user->power < Admin::ACC_CHANGE_POWERS)
		$err = "Droits insuffisants pour opérer sur ce modérateur.";
}

if (!@$err)
{
	if ($_SERVER["REQUEST_METHOD"] == 'POST') // update
	{	
		// form data
		$notes = (strlen(@$_POST['notes']))? $_POST['notes'] : null; // uses DBPal::str2null after
		$locked = ($_POST["locked"] == 'yes') ? 'yes' : 'no';
		$power = (int) $_POST["power"];
		
		// no input checks needed
		
		$log_msg = array();
		    
		// determine updated data
		$new_data = array(
		    "locked" => $locked
		  );
		if ($user->power >= Admin::ACC_CHANGE_POWERS)
		    $new_data["level"] = $power;
		
		$prev_data = (array) $current_data;
		$updated_data = array_diff_assoc($new_data, $prev_data);
		
		// TODO: test this
		if (sizeof($updated_data))
		{
		    if ($updated_data["locked"])
		    	$log_msg[] = ($locked == 'yes') ? "Locked" : "Unlocked";
		    
		    if ($updated_data["level"])
		    	$log_msg[] = ($power > $prev_data->level) ? "Promoted" : "Demoted";
		    
		    // TODO: add changePower and lock/unlock object model methods
		    
		    if ($prev_data->locked != 'yes' or $new_data->locked != 'yes')
		 	// means: does not apply if going from a locked to a locked state
		    {
		    	if ($locked == 'yes' or ($power < $prev_data->level and $prev_data->locked != 'yes'))
		    	{
		    		App::queue('refresh-assignments', array('of-admin', $id));
		    	}
		    	else if ($power < Admin::ACC_ALL_DATA)
		    	{
		    		App::queue('refresh-assignments', array('for-admin', $id));
		    	}
		    	else
		    	{
		    		App::queue('refresh-assignments', 'all');
		    	}
		}
		
		// process reports
		if (@$_POST['report'])
		    $new_open_ticket = App::processReports($_POST['report'], array('user', $id), $user, $current_data->open_ticket);
		
		// update
		DBPal::query(
		      "UPDATE delegues SET id = id"
		    		    		
		    . ((sizeof($updated_data))?
		      "  , " . DBPal::arr2set($updated_data) : "")
		    
		    . " WHERE id = $id"
		  );
		
		// TODO: what if no update at all?
		// log
		App::log($log_msg, "user", $id, $user->uid, $updated_data, $notes);
		
		// invalidate any active session for that user
		if ($updated_data["locked"] or $updated_data["level"])
			RealSession::replace($current_data->session_id, array('UserAuth', 'sessionSetRevalidate'));
		
		// if no more tickets and moderated -> clear assignments
		if ($current_data->open_ticket == null or @$new_open_ticket === false)
		{
		    DBPall::query("DELETE FROM assignments WHERE object_type = 'user' AND object_id = $id");
		}
		else
		{
		    // TODO: if no tickets or tickets are defered -> clear specific to admin
		    
		    // refresh assignments
			App::queue('refresh-assignments', array('for-object', 'user', $id));
		}
		
		$success = "Modifications enregistrées avec succès.";
		
		if ($return)
		{
		    $_SESSION["success"] = $success;
		    Web::redirect("/admin/admins");
		}
	}
	
	$admin = DBPal::getRow("SELECT *, UNIX_TIMESTAMP(logs.time) AS sub_date, UNIX_TIMESTAMP(last_conn) AS last_conn FROM delegues, logs WHERE delegues.id = $id AND logs.id = create_record");
	
	if ($admin->open_ticket) {
		 $reports = App::getReports('user', $id);
	}
	
	// log passif
	$logs = DBPal::query("SELECT *, UNIX_TIMESTAMP(time) AS time FROM logs LEFT JOIN delegues ON delegues.id = actor_id WHERE object_type = 'user' AND object_id = $id AND actor_id != $id ORDER BY time DESC");
	
	$active_logs_pre = DBPal::query(
	     "SELECT *, UNIX_TIMESTAMP(time) AS time FROM logs "
	    ."WHERE actor_id = $id ORDER BY time DESC"
	  );
	
	$active_logs = Array();
	while ($log = $active_logs_pre->fetch_object())
	{
		$active_logs[] = (object) Array (
		    'm' => $log,
		    'x' => DBPal::getRow("SELECT * FROM " . Settings::$objType2tabName[(string) $log->object_type] . " WHERE id = {$log->object_id}")
		  );
	}
}

// Controller-View limit
DBPal::finish();

$title = "Éditer un Modérateur";
?>
<? require("tpl/haut.php"); ?>
		<div class="navi">
			<a href=".">Accueil</a> &gt;
			<a href="admin/index">Espace Délégué</a> &gt;
			<a href="admin/admins">Modérateurs</a> &gt;
			<?=htmlspecialchars($title)?></div>
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
			<h3>Infos</h3>
			<div class="heading">Modérateur n° <?=$id?></div>
			<dl class="admin-id">
				<dt>Date d'inscription :</dt>
				<dd><?=strftime("%c", $admin->sub_date)?></dd>
				<dt>Dernière connexion :</dt>
				<dd><?=strftime("%c", $admin->last_conn)?></dd>
			</dl>
			<h3>Contact</h3>
			<div>TODO: contact, check power</div>
			<form action="<?=$_SERVER["REQUEST_URI"]?>" method="post">
				<h3>Accès</h3>
				<fieldset>
					<legend>Vérouillage</legend>
					<label>
						<input type="radio" value="no" name="locked"<?=($admin->locked == 'no')?" checked=\"checked\"":""?> />
						<?=($admin->locked == 'no')?'<strong>':''?>Accès Normal<?=($admin->locked == 'no')?'</strong>':''?>
					</label>
					<label>
						<input type="radio" value="yes" name="locked"<?=($admin->locked == 'yes')?" checked=\"checked\"":""?> />
						<?=($admin->locked == 'yes')?'<strong>':''?>Accès Vérouillé<?=($admin->locked == 'yes')?'</strong>':''?>
					</label>
				</fieldset>
				<? if ($user->power >= Admin::ACC_CHANGE_POWERS) { ?>
				<fieldset>
					<legend>Niveau</legend>
					<? foreach (Admin::$RANKS as $i => $rank) { ?>
					<label>
						<input type="radio" value="<?=$i?>" name="power"<?=($admin->level == $i)?" checked=\"checked\"":""?> />
						<span class="rank-img rank-<?=$i?>" title="<?=Admin::$RANKS[$i]?>"></span>
						<?=($admin->level == $i)?'<strong>':''?><?=$rank?><?=($admin->level == $i)?'</strong>':''?>
					</label>
					<? } ?>
				</fieldset>
				<? } ?>
<?
	if ($admin->open_ticket)
	{
?>
				<h3>Signalements ouverts</h3>
				<div class="info-section">
					<p>Cet administrateur a des signalements non résolus qui requièrent une action de ta part :</p>
					<div class="info"><a href="/admin/help#admin-solve">Aide</a></div>
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
					<input type="submit" name="action" value="Mettre à jour" />
				</div>
			</form>
			<hr class="space" />
			<h2>Statistiques</h2>
			TODO
			<hr class="space" />
			<h2>Log Passif</h2>
			<?=Helper::formatLog($logs)?>
			<hr class="space" />
			<h2>Log Actif</h2>
			<table class="logs">
				<thead>
					<tr>
						<th>Date / Heure</th>
						<th>Objet</th>
						<th>Action</th>
						<th>Détails</th>
					</tr>
				</thead>
				<tbody>
<? foreach ($active_logs as $i => $log) { ?>
					<tr class="<?=($i % 2 == 0)? 'even':'odd'?>">
						<td><?=strftime("%c", $log->m->time)?></td>
						<td>
						<?
							switch($log->m->object_type)
							{
								case 'user':
									if ($log->m->object_id == $id)
										echo 'self';
									else
										echo '<a href="admin/edit-admin?id='.$log->x->id.'">Modérateur n° '.$log->x->id.'</a> '
										    .'<span class="rank-img tiny rank-'.$log->x->level.'" title="'.Admin::$RANKS[$log->x->level].'"></span>';
									break;
								case 'school':
									echo '<a href="admin/edit-school?id='.$log->x->id.'">'.Helper::schoolTitle($log->x->cursus, $log->x->secondaire, $log->x->nom).'</a>';
									break;
								case 'prof':
									echo '<a href="admin/edit-prof?id='.$log->x->id.'">'.$log->x->nom.' '.$log->x->prenom.'</a>';
									break;
								case 'comment':
									echo 'Commentaire';
									break;
							}
						?>
						</td>
						<td><?=htmlspecialchars($log->m->log_msg)?></td>
						<td><?=htmlspecialchars($log->m->related_data)?></td>
					</tr>
<? } ?>
				</tbody>
			</table>
<? } ?>
		</div>
<? require("tpl/bas.php"); ?>
