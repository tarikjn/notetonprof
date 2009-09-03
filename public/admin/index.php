<?
define("ROOT_DIR", "../");
require(ROOT_DIR."_ini.php");

$user->requireOrRedirect();

$title = "Espace Délégué";
?>
<? require("tpl/haut.php"); ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <?=h($title)?></div>
		<hr />
		<div>
			<h2><?=h($title)?></h2>
			<div class="page-msg">Bienvenue, <strong><?=h($user->username)?></strong> !</div>
			<div class="left60">
				<!--
				<h3>Messages</h3>
				<p>TODO</p>
				-->
				<h3>Tâches</h3>
				<?
					if ($mp_count->users or $mp_count->data or $mp_count->comments)
					{
				?>
				<p>Éléments restants à modérer :</p>
				<ul>
					<?
					    if ($mp_count->users)
					    	printf("<li><a href=\"admin/admins\"><strong>%d</strong>"
					    	     . " administrateur(s)<a/></li>", $mp_count->users);
					    
					    if ($mp_count->data)
					    	printf("<li><a href=\"admin/data\"><strong>%d</strong>"
					    	     . " établissement(s) et/ou professeur(s)</a></li>", $mp_count->data);
					    
					    if ($mp_count->comments)
					    	printf("<li><a href=\"admin/comments\"><strong>%s</strong>"
					    	     . " commentaire(s)</a></li>", $mp_count->comments);
					?>
				</ul>
				<?
					}
					else
					{
				?>
				<p>Tu n'as aucun élément à modérer pour le moment.</p>
				<?
					}
				?>
			</div>
			<div class="right40">
				<div class="info-section">
					<h3>Statut</h3>
					<div class="info"><a href="admin/help#ranks">Plus d'infos</a></div>
				</div>
				<ul class="ranks">
				<?
					foreach (Admin::$RANKS as $i => $rank)
					{
				?>
					<li class="rank-<?=$i?><?=($user->power == $i)?' current':''?>"><?=$rank?></li>
				<?
					}
				?>
				</ul>
				<!--
				<h3>Statistiques</h3>
				<p>TODO - from modo/stats</p>
				-->
			</div>
		</div>
<? require("tpl/bas.php"); ?>
