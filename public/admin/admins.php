<?
define("ROOT_DIR", "../");
require(ROOT_DIR."_ini.php");

// only super-modos
$user->requireOrRedirect(4);

// number of objects per page
define('OBJECTSPP', 5);

$assignments = DBPal::query(
    "SELECT * FROM assignments"
  . "  WHERE assignee_id = {$user->uid} AND object_type = 'user'"
  . "  ORDER BY score DESC"
  . "  LIMIT " . OBJECTSPP
);
$objects = array();
while ($assignment = $assignments->fetch_object())
{
	// not checking rights or if there is something to do, assuming assignements are up to date
	$object = DBPal::getRow(
	    "SELECT * FROM " . Settings::$objType2tabName[$assignment->object_type]
	  . " WHERE id = {$assignment->object_id} AND status = 'ok'"
	);
	if ($object)
	{
		$objects[] = (object) array(
			"assign" => $assignment,
			"data" => $object
		);
	}
}

// show score column?, TODO: move to settings file
define('SHOW_SCORE', ($user->power >= Admin::ACC_SHOW_SCORE));

$title = "Modérateurs";
?>
<? require("tpl/haut.php"); ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <a href="/admin/index">Espace Délégué</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<div class="info-section">
				<h2><?=htmlspecialchars($title)?></h2>
			    <div class="info"><a href="/admin/help#admins">Instructions</a></div>
			</div>
			<? if ($message = Web::flash('success')) { ?>
				<p class="msg"><?=$message?></p>
			<? } ?>
			<h3>Tâches</h3>
<? if (sizeof($objects) == 0) { ?>
			<div class="page-msg">Tu n'as aucun modérateur à vérifier pour le moment</div>
<? } else { ?>
			<div class="page-msg"><?=sizeof($objects)?> modérateurs affichées sur un total de <?=$mp_count->users?> (classés par priorité)</div>
<? } ?>
			<table class="mod-table">
			    <thead>
			    	<tr>
<? if (SHOW_SCORE) { ?>
			    		<th class="score">P. Score</th>
<? } ?>
			        	<th class="title">Description</th>
			        	<th class="edit">Action</th>
			        </tr>
			    </thead>
<? while ($row = array_shift($objects)) { ?>
			    <tbody>
			    	<tr class="main-tr">
<? if (SHOW_SCORE) { ?>
			    		<td class="score"><?=$row->assign->score?></td>
<? } ?>
			    		<td class="title"><a href="#">Modérateur n° <?=$row->data->id?><a/> (<?=Admin::$RANKS[$row->data->level]?>)</td>
			    		<td class="edit"><a href="admin/edit-<?=$row->assign->object_type?>?id=<?=$row->data->id?>&amp;return=1" title="Editer"><img src="img/edit-action.png" alt="" /></a></td>
			    	</tr>
<?
	if ($row->data->open_ticket)
	{
		$reports = DBPal::query(
		             "SELECT note, actor_id, UNIX_TIMESTAMP(time) AS time FROM logs"
		           . " WHERE object_type = '{$row->assign->object_type}' AND object_id = {$row->data->id} AND special_action = 'report' AND id >= {$row->data->open_ticket}"
		           . " ORDER BY id DESC"
		         );
		while ($report = $reports->fetch_object())
		{
?>
			    		<tr class="notice-tr">
			    			<td colspan="<?=SHOW_SCORE? 3:2?>" class="<?=($report->actor_id === 0)? 'system' : 'alert'?>">
			    				<div class="date"><?=strftime("%x", $report->time)?></div>
			    				<div class="notes"><?=htmlspecialchars($report->note)?></div>
			    			</td>
			    		</tr>
<?
		}
	}
?>
			    	<tr class="spacer"><td colspan="<?=SHOW_SCORE? 3:2?>"></td></tr>
			    </tbody>
<? } ?>
			</table>
<? if ($mp_count->users > OBJECTSPP) { ?>
			<p class="page-end">Pour voir la suite, tu dois d'abord compléter ces tâches</p>
<? } ?>
			<h3>Rechercher</h3>
			<p>TODO: how to keep modos anonymity?, redo modos flow, when logged, see from school page, connected status, tab by power...</p>
		</div>
<? require("tpl/bas.php"); ?>
