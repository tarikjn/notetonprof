<?
define("ROOT_DIR", "../"); // TODO: replace by 'BP' for Base Path
require(ROOT_DIR."_ini.php");

$user->requireOrRedirect(Admin::ACC_PRE_MODERATE);

// number of comments per page
define('COMMENTSPP', 5);

if ($_SERVER["REQUEST_METHOD"] == 'POST')
{
	$errors = array();
	$count = 0;

	foreach ($_POST['comment'] AS $id => $post)
	{
		$id = (int) $id;
		$action = (in_array(@$post['action'], array('accept', 'raise', 'reject')))? $post['action'] : null;
		$note = (strlen($post['notes']))? $post['notes'] : null; // uses DBPal::str2null after
		
		// = check parameters =
		if (!$id or !$action) {
			$errors[] = "Paramètre incorrect";
			continue;
		}
		
		// = check that the comment requires action, get comment data =
		if (!($comment = DBPal::getRow("SELECT * FROM notes WHERE id = $id AND (moderated != 'yes' OR open_ticket IS NOT NULL) AND status = 'ok'"))) {
			$errors[] = "Aucune action nécessaire sur ce commentaire";
			// TODO: clear any assignments here
			continue;
		}
		
		// TODO: move $admin_schools to UserAuth for caching
		$admin_schools = DBPal::getList("SELECT etblt_id FROM delegues_etblts WHERE delegue_id = {$user->uid}");
		$comment_school = DBPal::getOne("SELECT etblt_id FROM professeurs WHERE id = {$comment->prof_id}");
		
		// = check rights =
		$req_level = Admin::ACC_PRE_MODERATE;
		
		if ($comment->moderated == 'pre')
			$req_level = max($req_level, Admin::ACC_REAL_MODERATE);
			
		if (!in_array($comment_school, $admin_schools))
			$req_level = max($req_level, Admin::ACC_ALL_DATA);
		
		if ($comment->open_ticket)
			$req_level = max($req_level, Admin::ACC_COMMENT_TICKET);
		
		if ($comment->moderated == 'raised')
			$req_level = max($req_level, Admin::ACC_RAISED_OBJECT);
		
		if ($user->power < $req_level) {
			$errors[] = "Droits insuffisants";
			continue;
		}
		
		// = take action =
		// clear assignments
		DBPal::query("DELETE FROM assignments WHERE object_type = 'comment' AND object_id = $id");
		$queue_params = array('for-object', 'comment', $id);
		
		switch ($action)
		{
			case 'raise':
				// set as 'raised'
				DBPal::query("UPDATE notes SET moderated = 'raised' WHERE id = $id");
				
				// log raise
				App::log("Raised comment", "comment", $id, $user->uid, null, $note, 'raise');
				
				// redo assignments
				App::queue('refresh-assignments', $queue_params);
				
				break;
			case 'accept':
			case 'reject':
				$moderate = ($user->power < Admin::ACC_REAL_MODERATE)? 'pre' : 'yes';
				
				// close all open tickets
				if ($comment->open_ticket)
				{
					DBPal::query("UPDATE reports SET status = 'closed' WHERE object_type = 'comment' AND object_id = $id");
				}
				
				// update comment, closes any open ticket
				// we assume a comment with an open ticket cannot be pre-moderated
				DBPal::query("UPDATE reports SET status = 'closed' WHERE object_type = 'comment' AND object_id = $id");
				DBPal::query(
				    "UPDATE notes"
				  . " SET moderated = '$moderate', status = '" . (($action == 'reject')? 'deleted' : 'ok') . "', open_ticket = NULL"
				  . " WHERE id = $id"
				);
				
				if ($moderate == 'pre') {
					// redo assignments
					App::queue('refresh-assignments', $queue_params);
					
					$log_msg = "Pre-Moderated";
				}
				else
				{
					// clear assignments
					DBPal::query("DELETE FROM assignments WHERE object_type = 'comment' AND object_id = $id");
					
					// we also assume a comment cannot have a ticket if it is not moderated
					if ($comment->open_ticket)
						$log_msg = "Closed report(s)";
					else
						$log_msg = "Moderated";
				}
				
				// log action
				$log_action = ($action == 'reject')? 'deleted' : 'accepted';
				App::log($log_msg . ": " . $log_action, "comment", $id, $user->uid, null, $note);
		}
		
		// = done for this comment =
		$count++;
	}
	
	$info = "$count commentaires modérés";
	
	// TODO: location redirect for refresh-friendly page
}

// not checking rights or if there is something to do, assuming assignments are up to date
$comments_q = DBPal::query(
    "SELECT * FROM notes AS o, assignments AS a "
  . "  WHERE o.status = 'ok' "
  . "    AND o.id = a.object_id AND a.object_type = 'comment' "
  . "    AND a.assignee_id = {$user->uid}"
  . "  ORDER BY a.score DESC"
  . "  LIMIT " . COMMENTSPP
);
$comments = array();
while ($comment = $comments_q->fetch_array())
{
	if ($comment['open_ticket'])
	{
		// assuming comment reports can't be defered
		$reports = DBPal::query(
		      "SELECT description, actor_id, UNIX_TIMESTAMP(time) AS time"
		    . " FROM reports, logs"
		    . " WHERE reports.object_type = 'comment' AND reports.object_id = " . $comment['id'] . " AND status = 'open'"
		    . "  AND logs.id = reports.create_record"
		    . " ORDER BY reports.id DESC"
		  );
	}
	
	$comments[] = (object) array_merge($comment,
		array(
			"reports" => ($comment['open_ticket'])? $reports : null
		)
	);
}

// TODO: pull comments: set session expiration date on comments, will avoid double-moderation

// Controller-View limit
DBPal::finish();

// show score column?, TODO: move to settings file
define('SHOW_SCORE', ($user->power >= 5));

$title = "Commentaires";
?>
<? require("tpl/haut.php"); ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <a href="/admin/index">Espace Délégué</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<div class="info-section">
				<h2><?=htmlspecialchars($title)?></h2>
			    <div class="info"><a href="admin/help#comments">Instructions</a></div>
			</div>
			<p class="warning">La modération des commentaires obéit à des règles strictes, tout abus ou entrave délibérée aux règles de modération établies par Campus Citizens peut engager ta responsabilité pénale. En modérant les commentaires, tu reconnais avoir lu et accepté les <a href="admin/help#moderator-agreement">Conditions de Modération</a>.</p>
			<?=Helper::showErrors(@$errors)?>
			<?=Helper::showInfo(@$info)?>
<? if (sizeof($comments) == 0) { ?>
			<div class="page-msg">Tu n'as aucun commentaires à modérer pour le moment</div>
<? } else { ?>
			<div class="page-msg"><?=sizeof($comments)?> commentaires affichées sur un total de <?=$mp_count->comments?> (classés par priorité)</div>
			<form action="<?=$_SERVER["REQUEST_URI"]?>" method="post" class="has-labels">
				<table class="mod-table">
					<thead>
						<tr>
<? if (SHOW_SCORE) { ?>
							<th class="score">P. Score</th>
<? } ?>			
					    	<th class="comment">Commentaire</th>
					    	<th class="action">Action</th>
					    </tr>
					</thead>
<? while ($row = array_shift($comments)) { ?>
					<tbody>
						<tr class="main-tr">
<? if (SHOW_SCORE) { ?>
							<td class="score"><?=$row->score?></td>
<? } ?>			
							<td class="comment"><div><?=htmlspecialchars($row->comment)?></div></td>
							<td class="action">
								<label title="Accepter" class="action-switch accept"><span class="contents"><input type="radio" name="comment[<?=$row->id?>][action]" value="accept" /> Accepter</span></label><label title="Refuser" class="action-switch reject"><span class="contents"><input type="radio" name="comment[<?=$row->id?>][action]" value="reject" /> Refuser</span></label><label title="Remonter" class="action-switch raise<?=($user->power >= Admin::ACC_RAISED_OBJECT)? ' disabled' : ''?>"><span class="contents"><input type="radio" name="comment[<?=$row->id?>][action]" value="raise"<?=($user->power >= Admin::ACC_RAISED_OBJECT)? ' disabled="disabled"' : ''?> /> Remonter</span></label>
							</td>
						</tr>
						<tr class="extra-tr">
							<td colspan="<?=SHOW_SCORE? 3:2?>"><label><span>Notes (facultatif)</span> <input type="text" name="comment[<?=$row->id?>][notes]" /></label></td>
						</tr>
<?
	if ($row->reports) while ($report = $row->reports->fetch_object())
	{
?>
			    		<tr class="notice-tr">
			    			<td colspan="<?=SHOW_SCORE? 3:2?>" class="<?=($report->actor_id === 0 or $report->actor_id === '0')? 'system' : 'alert'?>">
			    				<div class="date"><?=strftime("%x", $report->time)?></div>
			    				<div class="notes"><?=htmlspecialchars($report->description)?></div>
			    			</td>
			    		</tr>
<?
	}
?>
						<tr class="spacer"><td colspan="<?=SHOW_SCORE? 3:2?>"></td></tr>
					</tbody>
<? } ?>
				</table>
				<div class="confirm">
					<input type="submit" value="Confirmer" />
				</div>
			</form>
<? } ?>
<? if ($mp_count->comments > COMMENTSPP) { ?>
			<p class="page-end">Pour voir la suite, tu dois d'abord modérer ces commentaires</p>
<? } ?>
		</div>
<? require("tpl/bas.php"); ?>
