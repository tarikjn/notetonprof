<?
define("ROOT_DIR", "../");
require(ROOT_DIR."_ini.php");

// only super-modos
$user->requireOrRedirect(5);

if ($_SERVER["REQUEST_METHOD"] == 'POST') // update
{
	if (!@$notice)
	{
		// params to be sent in the MQ
		$data = (object) array(
      	  'for_admins' => array_keys(@$_POST['mail']),
      	  'mail_locked' => (@$_POST['mail_locked'])? 'on' : 'off',
      	  'subject' => @$_POST['subject'],
      	  'hash' => UserAuth::genPass(8)
      	);
      	
      	// message to be written in the file
      	$message = @$_POST['message'];
      	
      	// TODO: log it
      	
      	// write message to file
      	$fp = fopen("../../jobs/emails/email.{$data->hash}.txt", 'w');
		fwrite($fp, $message);
		fclose($fp);
      	
      	// send job to MQ
      	App::queue('mail-admins', $data->hash);
      	// TODO: have a separate process send emails, log it
		
		//$_SESSION["success"] = "Ok! Message en cours d'envoi...";
		//Web::redirect("/admin/admins");
	}	
}

$title = "Contacter les Modérateurs";
?>
<? require("tpl/haut.php"); ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <a href="/admin/index">Espace Délégué</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<div class="info-section">
				<h2><?=htmlspecialchars($title)?></h2>
			    <!--<div class="info"><a href="admin/help#mail-admins">Instructions</a></div>-->
			</div>
<? if (@$notice) { ?>
			<div class="head-notice">
				<p>Attention ! Vérifie les données du formulaire :</p>
				<ul><? foreach ($notice as $alert) { ?><li><?=$alert?></li><? } ?></ul>
			</div>
<? } ?>
			<form action="<?=$_SERVER["REQUEST_URI"]?>" method="post" onsubmit="return confirm('Es-tu sûr ?');">
				<fieldset>
					<legend>Destinataires (TODO, non actif)</legend>
					<? foreach (Admin::$RANKS as $i => $rank) { ?>
					<label>
						<input type="checkbox" value="yes" name="mail[<?=$i?>]" checked="checked" />
						<span class="rank-img rank-<?=$i?>" title="<?=Admin::$RANKS[$i]?>"></span>
						<?=$rank?>
					</label>
					<? } ?>
					<label for="mail_locked">
						<input type="checkbox" name="mail_locked" id="mail_locked" value="on" />
						Inclure les modérateurs donc le compte est bloqué (non recommendé)
					</label>
				</fieldset>
				<fieldset>
					<legend>Message</legend>
					<label for="subject"<?=(@$notice["subject"])?" class=\"notice\"":""?>>Sujet : <input type="text" name="subject" id="subject" maxlength="255" /></label>
					<textarea name="message" rows="15" style="width: 100%;">
Amicalement,
L'équipe de NoteTonProf.com,

-- 
<?=Settings::WEB_ROOT?></textarea>
				<p class="indic">Le message doit être du texte brut</p>
				</fieldset>
				<div class="save"><input type="submit" value="Envoyer immédiatement" /></div>
			</form>
		</div>
<? require("tpl/bas.php"); ?>
