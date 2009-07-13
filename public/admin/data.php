<?
define("ROOT_DIR", "../");
require(ROOT_DIR."_ini.php");

$user->requireOrRedirect(Admin::ACC_DATA);

// number of objects per page
define('OBJECTSPP', 5);

$assignments = DBPal::query(
    "SELECT * FROM assignments"
  . "  WHERE assignee_id = {$user->uid} AND object_type IN ('school', 'prof')"
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
		if ($object->open_ticket) {
		    $reports = App::getReports($assignment->object_type, $object->id);
		}
	
		$objects[] = (object) array(
			"assign" => $assignment,
			"data" => $object,
			"reports" => ($object->open_ticket)? $reports : null
		);
	}
}

// show score column?, TODO: move to settings file
define('SHOW_SCORE', ($user->power >= Admin::ACC_SHOW_SCORE));

$title = "Données";
?>
<? require("tpl/haut.php"); ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <a href="/admin/index">Espace Délégué</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<div class="info-section">
				<h2><?=htmlspecialchars($title)?></h2>
			    <div class="info"><a href="/admin/help#data">Instructions</a></div>
			</div>
			<? if ($message = Web::flash('success')) { ?>
				<p class="msg"><?=$message?></p>
			<? } ?>
			<h3>Tâches</h3>
<? if (sizeof($objects) == 0) { ?>
			<div class="page-msg">Tu n'as aucunes données à modérer pour le moment</div>
<? } else { ?>
			<div class="page-msg"><?=sizeof($objects)?> tâches affichées sur un total de <?=$mp_count->data?> (classées par priorité)</div>
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
			    		<td class="title"><?=Helper::tagFor($row->assign->object_type, $row->data)?></td>
			    		<td class="edit"><a href="admin/edit-<?=$row->assign->object_type?>?id=<?=$row->data->id?>&amp;return=1" title="Editer"><img src="img/edit-action.png" alt="" /></a></td>
			    	</tr>
<?
	
	if ($row->reports) while ($report = array_shift($row->reports))
	{
?>
			    		<tr class="notice-tr">
			    			<td colspan="<?=SHOW_SCORE? 3:2?>" class="<?=($report->actor_id === 0)? 'system' : 'alert'?><?=($report->defered)?' defered':''?>">
			    				<div class="date"><?=strftime("%x", $report->time)?></div>
			    				<div class="notes"><?=htmlspecialchars($report->note)?></div>
			    			</td>
			    		</tr>
<?
	}
?>
			    	<tr class="spacer"><td colspan="<?=SHOW_SCORE? 3:2?>"></td></tr>
			    </tbody>
<? } ?>
			</table>
<? if ($mp_count->data > OBJECTSPP) { ?>
			<p class="page-end">Pour voir la suite, tu dois d'abord compléter ces tâches</p>
<? } ?>
			<h3>Rechercher</h3>
			<p>Pour modifier les données d'un professeur ou d'un établissement, rends toi directement sur la page en question à partir du portail en étant connecté.</p>
		</div>
<? require("tpl/bas.php"); ?>
