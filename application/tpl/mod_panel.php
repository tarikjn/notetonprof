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
				<a href="/admin/account"<? if (Web::getPath() == '/admin/account') {?> class="current"<? } ?>>Mon Compte</a>
				<a href="/admin/myschools"<? if (Web::getPath() == '/admin/myschools') {?> class="current"<? } ?>>Mes Établissements</a>
				<a href="/admin/help"<? if (Web::getPath() == '/admin/help') {?> class="current"<? } ?>>Assistance</a>
				<a class="special" href="/logout">Déconnexion</a>
			</div>
		</div>
		<div id="mod-tabs">
			<a href="/admin/index" class="icon home <? if (Web::getPath() == '/admin/index') {?> current<? } ?>">Accueil</a>
			<? if ($user->power >= Admin::ACC_MONITOR) { ?>
				<a href="/admin/monitor" class="icon monitoring<? if (Web::getPath() == '/admin/monitor') {?> current<? } ?>">Surveillance</a>
			<? } ?>
			<? if ($user->power >= Admin::ACC_ADMINS) { ?>
				<a href="/admin/admins"<? if (Web::getPath() == '/admin/admins') {?> class="current"<? } ?>>Modérateurs <em<? if ($mp_count->users == 0) { ?> class="empty"<? } ?>><?=$mp_count->users?></em></a>
			<? } ?>
			<? if ($user->power >= Admin::ACC_DATA) { ?>
				<a href="/admin/data"<? if (Web::getPath() == '/admin/data') {?> class="current"<? } ?>>Données <em<? if ($mp_count->data == 0) { ?> class="empty"<? } ?>><?=$mp_count->data?></em></a>
			<? } ?>
			<a href="/admin/comments"<? if (Web::getPath() == '/admin/comments') {?> class="current"<? } ?>>Commentaires <em<? if ($mp_count->comments == 0) { ?> class="empty"<? } ?>><?=$mp_count->comments?></em></a>
		</div>
	</div>
	</div>
