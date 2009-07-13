<?
define("ROOT_DIR", "../");
require(ROOT_DIR."_ini.php");

$user->requireOrRedirect(Admin::ACC_MONITOR);

// Controller-View limit
DBPal::finish();

$title = "Surveillance";
?>
<? require("tpl/haut.php"); ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <a href="/admin/index">Espace Délégué</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<h2><?=htmlspecialchars($title)?></h2>
			<p>TODO</p>
		</div>
<? require("tpl/bas.php"); ?>
