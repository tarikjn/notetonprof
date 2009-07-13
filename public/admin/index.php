<?
define("ROOT_DIR", "../");
require(ROOT_DIR."_ini.php");

$user->requireOrRedirect();

$title = "Espace Délégué";
?>
<? require("tpl/haut.php"); ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<h2><?=htmlspecialchars($title)?></h2>
			<div class="page-msg">Bienvenue, <strong><?=htmlspecialchars($user->username)?></strong> !</div>
			<div class="left60">
				<h3>Messages</h3>
				<p>TODO</p>
				<h3>Tâches</h3>
				<p>TODO - from mod_panel/counts</p>
			</div>
			<div class="right40">
				<div class="info-section">
					<h3>Statut</h3>
					<div class="info"><a href="/admin/help#ranks">Plus d'infos</a></div>
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
				<h3>Statistiques</h3>
				<p>TODO - from modo/stats</p>
			</div>
		</div>
<? require("tpl/bas.php"); ?>
