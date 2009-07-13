<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

// load mail functions
require_once("conf/Mail.php");

$rnb = 0;
$notice = FALSE;

// début traitement des variables URL
if (isset($_GET["type"]) && isset($_GET["id"]))
{
	$type = $_GET["type"];
	$id = (int) $_GET["id"];
	
	$erreur_url = FALSE;

	// début vérification des variables
	if ( in_array($type, array("prof", "comment")) )
	{
		switch ($type)
		{
			// TODO: add school
			case "prof":
				$concerne = "La fiche d'un professeur";
				$query = "SELECT professeurs.id AS id, etablissements.id AS e_id FROM professeurs, etablissements WHERE professeurs.status = 'ok' AND professeurs.id = $id AND etablissements.id = professeurs.etblt_id";
				break;
			case "comment":
				$concerne = "Le commentaire d'un visiteur";
				$query = "SELECT prof_id AS id, etablissements.id AS e_id FROM notes, professeurs, etablissements WHERE notes.id = $id && notes.status = 'ok' && professeurs.id = notes.prof_id && etablissements.id = professeurs.etblt_id";
				break;
		}
		
		$result = DBPal::query($query);
		$rnb = $result->num_rows;
	}
	
	if ($rnb > 0)
	{
		$row = $result->fetch_assoc();
		$r_id = $row["id"];
		$e_id = $row["e_id"];
		
		$sent = @$_POST["sent"];
	
		if ($sent)
		{
			$probleme = @$_POST["probleme"];
			
			// log
			App::log("Reported issue", $type, $id, null, array("issue" => $probleme));
			
			// envoi du signalement par mail
			ob_start();
			require("tpl/emails/signalement.html.php");
			$mail_html = ob_get_clean();
			ob_start();
			require("tpl/emails/signalement.text.php");
			$mail_text = ob_get_clean();
			
			$mail = new PHPMailer();
			$mail->IsSMTP();
			$mail->Host = $smtp;
			if ($smtpA)
			{
				$mail->SMTPAuth = true;
				$mail->Username = $smtpUser;
				$mail->Password = $smtpPass;
			}
			
			$mail->From = $botMail;
			$mail->FromName = $botName;
			
			// sélection des adresses emails à alerter
			$q_emails_deleg = "SELECT delegues.email FROM delegues_etblts, delegues WHERE delegues.id = delegues_etblts.delegue_id && delegues_etblts.etblt_id = $e_id AND status = 'ok' && checked = 1 && locked = 'no'";
			$r_emails_deleg = DBPal::query($q_emails_deleg);
			
			while ($row = $r_emails_deleg->fetch_assoc())
				$mail->AddAddress($row["email"]);
			
			$q_emails_nbactive = "SELECT COUNT(*) FROM delegues_etblts, delegues WHERE delegues.id = delegues_etblts.delegue_id && delegues_etblts.etblt_id = $e_id AND status = 'ok' && checked = 1 && locked = 'no' && TO_DAYS(NOW()) - TO_DAYS(last_conn) < 31";
			$nbactive = DBPal::getOne($q_emails_nbactive);
			
			if ($type == "comment" || !$nbactive)
				$mail->AddAddress("modos@notetonprof.fr");
			// fin
			
			$mail->Subject = "Signalement concernant : $concerne";
			$mail->AddEmbeddedImage("img/titre-lite.png", "titre");
			$mail->Body = $mail_html;
			$mail->AltBody = $mail_text;
			
			if (!$mail->Send())
				$notice["mail"] = "Un problème technique est survenu lors de l'envoi, veuillez réessayer, et dans le cas échéant contacter le <a href=\"webmestre@notetonprof.fr\">Webmestre</a>.";
			
			if (!$notice)
			{
				// changement de page
				$_SESSION["msg"] = ($type == "comment" || !$nbactive)?"Ton signalement a bien été pris en compte, nous feront le nécessaire dans les plus bref délais.":"Ton signalement a été envoyé aux délégués concernés.";
				Web::redirect("/notes2/".rawurlencode($r_id)."/");
			}
		}
		
		$erreur_var = FALSE;
	}
	else
		$erreur_var = TRUE;
	// fin vérification des variables
}
else	$erreur_url = TRUE;
// fin traitement des variables URL

// Controller-View limit
DBPal::finish();

$title = "Signaler un problème";
?>
<? require("tpl/haut.php"); ?>
<? if ($erreur_url) require("tpl/erreur_url.php"); else if ($erreur_var) require("tpl/erreur_var.php"); else { ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<h2>Signaler un problème</h2>
			<p>
<? if ($type == "prof") { ?>
				Ce formulaire te permet de nous faire remonter rapidement une erreur sur la fiche d'un professeur :
<? } else if ($type == "comment") { ?>
				Ce formulaire te permet de nous faire remonter rapidement un commentaire qui ne respecterait pas les <a href="regles">règles</a>.
<? } ?>
			</p>
<? if ($notice) { ?>
			<div class="head-notice">
				<p>Attention ! Le signalement n'a pas été envoyé :</p>
				<ul><? foreach ($notice as $alert) { ?><li><?=$alert?></li><? } ?></ul>
			</div>
<? } ?>
			<form action="<?=$_SERVER["SCRIPT_NAME"]?>?type=<?=urlencode($type)?>&amp;id=<?=urlencode($id)?>" method="post" class="form">
				<input type="hidden" name="sent" value="1" />
				<dl>
					<dt>Concerne :</dt>
					<dd><?=htmlspecialchars($concerne)?> (#<?=$id?>)</dd>
					<dt>Détails :</dt>
					<dd>
						<label for="probleme" class="facultatif">Problème* : <input type="text" name="probleme" id="probleme" value="<?=@htmlspecialchars($probleme)?>" size="65" maxlength="255" /></label>
						<p class="facultatif etoile">*Champ facultatif</p>
						<p class="indic">
<? if ($type == "prof") { ?>
							S'il s'agit d'une correction à effectuer, merci de donner les bonnes informations au complet. Ce n'est pas forcément quelqu'un de votre établissement qui sera chargé d'effectuer la correction.
<? } else if ($type == "comment") { ?>
							La seule action que nous prenons vis à vis des commentaires est la suppression des évaluations comportant un commentaire qui ne respecte pas les règles. Nous ne pouvons supprimer une évaluation ou la modifier pour une quelque autre raison.
<? } ?>
						</p>
					</dd>
					<dt>Validation</dt>
					<dd><input type="submit" value="Terminer" /></dd>
				</dl>
			</form>
		</div>
<? } ?>
<? require("tpl/bas.php"); ?>
