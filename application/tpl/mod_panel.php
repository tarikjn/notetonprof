<?
	// TODO: Cache & invalidate when changing user data/adding/completing task
	$countTemplate = "(SELECT COUNT(*) FROM assignments WHERE assignee_id = {$user->uid} AND object_type %s)";
	$query = "SELECT"
	       . sprintf($countTemplate, "IN ('prof', 'school')") . " AS data,"
	       . sprintf($countTemplate, "= 'comment'") . " AS comments,"
	       . sprintf($countTemplate, "= 'user'") . " AS users";
    $mp_count = DBPal::getRow($query);
?>
	<div id="mod-container">
	<div id="mod-panel">
		<div id="mod-header">
			<div id="mod-status"><span id="mod-msg">Connecté en tant que : <strong><?=$user->username?></strong></span><span class="rank-img rank-<?=$user->power?>" title="<?=Admin::$RANKS[$user->power]?>"></span></div>
			<div id="mod-account-actions">
				<?=Helper::linkAndCurrent('admin/account')?>Mon Compte</a>
				<?=Helper::linkAndCurrent('admin/myschools')?>Mes Établissements</a>
				<?=Helper::linkAndCurrent('admin/help')?>Assistance</a>
				<a class="special" href="logout">Déconnexion</a>
			</div>
		</div>
		<div id="mod-tabs">
			<?=Helper::linkAndCurrent('admin/index', 'icon home')?>Accueil</a>
			<? if ($user->power >= Admin::ACC_MONITOR) { ?>
				<?=Helper::linkAndCurrent('admin/monitor', 'icon monitoring')?>Surveillance</a>
			<? } ?>
			<? if ($user->power >= Admin::ACC_ADMINS) { ?>
				<?=Helper::linkAndCurrent('admin/admins')?>Modérateurs <em<? if ($mp_count->users == 0) { ?> class="empty"<? } ?>><?=$mp_count->users?></em></a>
			<? } ?>
			<? if ($user->power >= Admin::ACC_DATA) { ?>
				<?=Helper::linkAndCurrent('admin/data')?>Données <em<? if ($mp_count->data == 0) { ?> class="empty"<? } ?>><?=$mp_count->data?></em></a>
			<? } ?>
			<?=Helper::linkAndCurrent('admin/comments')?>Commentaires <em<? if ($mp_count->comments == 0) { ?> class="empty"<? } ?>><?=$mp_count->comments?></em></a>
		</div>
	</div>
	</div>
