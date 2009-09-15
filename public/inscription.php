<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");


$title = "Inscription délégué";
?>
<? require("tpl/haut.php"); ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<h2>Inscription délégué</h2>
			<p>Avant de pouvoir être délégué, tu dois pleinement accepter les conditions de modération ci-dessous :</p>
			
			<form action="inscription2" class="form">
				<dl>
					<dt>Conditions de modération</dt>
					<dd>
						<div class="legal">
							<? require('tpl/legal/moderator_agreement.html'); ?>
							<p class="strong">En attendant que le bouton ci-dessous s’active, merci de <em>lire</em> les informations qui précèdent.</p>
						</div>
					</dd>
					<dt>Acceptation</dt>
					<dd style="text-align: center;"><input type="submit" id="timed-button" value="J'ai lu et j'accepte ce qui précède" /></dd>
				</dl>
			</form>
			<script type="text/javascript">
			    document.getElementById('timed-button').disabled = true;
			    setTimeout("document.getElementById('timed-button').disabled = false;", 25000);
			</script>
		</div>
<? require("tpl/bas.php"); ?>
