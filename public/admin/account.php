<?
define("ROOT_DIR", "../");
require(ROOT_DIR."_ini.php");

$user->requireOrRedirect();

// initialisation des variables
$change_pass_q = "";
unset($success, $notice, $noticeD);

/* début - traitement des données HTTP POST */
$save = @$_POST["save"];
$del = @$_POST["del"];
/* fin - traitement des données HTTP POST */

// get user data
$query = "SELECT * FROM delegues WHERE id = {$user->uid}";
$row = DBPal::getRow($query);

if ($save)
{
    $email = @$_POST["email"];
    $cur_pass = @$_POST["cur_pass"];
    $pass = @$_POST["pass"];
    $pass2 = @$_POST["pass2"];
    $prenom = @$_POST["prenom"];
    $nom = @$_POST["nom"];
    
    if (empty($email))
    	$notice["email"] = "Indique ton adresse <cite>E-mail</cite> pour l'identifiant.";
    else if (!Web::isEmail($email))
    	$notice["email"] = "Ce n'est pas une adresse <cite>E-mail</cite> valide !";
    else
    {
    	$query = "SELECT COUNT(*) FROM delegues WHERE email=".DBPal::quote($email)." AND id != {$user->uid} AND status = 'ok'";
    	$isUsed = DBPal::getOne($query);
    	
    	if ($isUsed)
    		$notice["email"] = "L'adresse e-mail <cite>".htmlspecialchars($email)."</cite> est déjà utilisée !";
    }
    
    if (!empty($cur_pass))
    {
    	$query = "SELECT COUNT(*) FROM delegues WHERE id = {$user->uid} AND md5_pass=".DBPal::quote(md5($cur_pass));
    	$passOk = DBPal::getOne($query);
    	
    	if ($passOk)
    	{
    		if (strlen($pass) < 5)
    			$notice["pass"] = "Ton mot de passe doit faire 5 caractère minimum, pour des raisons de sécurité.";
    		if ($pass != $pass2)
    			$notice["pass2"] = "Les mots de passe ne correspondent pas !";
    	}
    	else
    		$notice["cur_pass"] = "Mot de passe actuel incorrect.";
    }
    
    if (empty($prenom))
    	$notice["prenom"] = "Remplis le champ <cite>Prenom</cite>.";
    if (empty($nom))
    	$notice["nom"] = "Remplis le champ <cite>Nom</cite>.";
    
    if (!@$notice)
    {
    	$log_msg = array();
    	
    	// determine updated data
		$new_data = array(
			"email" => $email,
		    "nom" => $nom,
		    "prenom" => $prenom
		  );
		  
		$prev_data = (array) $row;
		$updated_data = array_diff_assoc($new_data, $prev_data);
		
    	if (!empty($cur_pass))
    	{
    		$change_pass_q = ", md5_pass='".md5($pass)."'";
    		$log_msg[] = 'Changed account password';
    	}
    	
    	if (sizeof($updated_data))
    		$log_msg[] = 'Changed account details';
    	
    	if (sizeof($log_msg))
    	{
    		// update
			DBPal::query(
			      "UPDATE delegues SET id = id"
			    		    		
			    . ((sizeof($updated_data))?
			      "  , " . DBPal::arr2set($updated_data) : "")
			    
			    . $change_pass_q
			    
			    . " WHERE id = {$user->uid}"
			  );
    		
    		// log
    		App::log($log_msg, "user", $user->uid, $user->uid, $updated_data);
    		
    		// send refresh directly to UserAuth
    		// TODO: how about password change? -> disconnect/avoid foreign sessions
    		$user->invalidateSessionData();
    		
    		$success = "Modifications enregistrées avec succès.";
    	}
    	else
    		$success = "Aucune modifications.";
    }
}
else if ($del)
{
    $pass = @$_POST["pass"];
    
    if (empty($pass))
    	$noticeD["pass"] = "Indique ton <cite>Mot de passe</cite>.";
    else
    {
    	$query = "SELECT COUNT(*) FROM delegues WHERE id = {$user->uid} && md5_pass=".DBPal::quote(md5($pass));
    	
    	if (!DBPal::getOne($query))
    		$noticeD["pass"] = "Mot de passe incorrect.";
    }
    
    if (!@$noticeD)
    {
    	// delete user
    	DBPal::query("UPDATE delegues SET status = 'deleted' WHERE id = {$user->uid}");
    	
    	// log
    	App::log("Deleted account", "user", $user->uid, $user->uid);
    	
    	// refresh assignments of dependant objects
    	App::queue('refresh-assignments', array('of-admin', $user->uid));
    	
    	// logout
    	$user->logout();
    	
		// redirect
		$_SESSION['auth.message'] = "Ton compte a été supprimé avec succès";	
		Web::redirect($_SERVER["REQUEST_URI"]);
    }
}

if (!$save)
{
    $nom = $row->nom;
    $prenom = $row->prenom;
    $email = $row->email;
}

// Controller-View limit
DBPal::finish();

$title = "Mon Compte";
?>
<? require("tpl/haut.php"); ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <a href="/admin/index">Espace Délégué</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<h2>Modification</h2>
<? if (@$success) { ?>
			<p class="msg"><?=$success?></p>
<? } else if (@$notice) { ?>
			<div class="head-notice">
				<p>Attention ! Vérifie les données du formulaire :</p>
				<ul><? foreach ($notice as $alert) { ?><li><?=$alert?></li><? } ?></ul>
			</div>
<? } ?>
			<form action="<?=$_SERVER["REQUEST_URI"]?>" method="post">
				<input type="hidden" name="save" value="1" />
				<fieldset>
					<legend>Identifiant</legend>
					<label for="email"<?=(@$notice["email"])?" class=\"notice\"":""?>>E-mail : <input type="text" name="email" id="email" value="<?=@htmlspecialchars($email)?>" maxlength="255" /></label>
					<p class="indic">
						Cette adresse e-mail doit être valide,<br />
						Elle ne sera jamais affichée sur le site ou communiquée à un quelconque partenaire.
					</p>
				</fieldset>
				<fieldset>
					<legend>Changement de mot de passe</legend>
					<p class="indic">Si tu n'entre pas de mot de passe, ton mot de passe actuel restera inchangé.</p>
					<label for="cur_pass" class="facultatif<?=(@$notice["cur_pass"])?" notice":""?>">Mot de passe actuel* : <input type="password" name="cur_pass" id="cur_pass" value="" maxlength="50" /></label>
					<label for="pass" class="facultatif<?=(@$notice["pass"])?" notice":""?>">Nouveau mot de passe* : <input type="password" name="pass" id="pass" value="" maxlength="50" /></label>
					<label for="pass2" class="facultatif<?=(@$notice["pass2"])?" notice":""?>">Confirmation* : <input type="password" name="pass2" id="pass2" value="" maxlength="50" /></label>
				</fieldset>
				<fieldset>
					<legend>Identité</legend>
					<label for="prenom"<?=(@$notice["prenom"])?" class=\"notice\"":""?>>Prénom : <input type="text" name="prenom" id="prenom" value="<?=@htmlspecialchars($prenom)?>" maxlength="50" /></label>
					<label for="nom"<?=(@$notice["nom"])?" class=\"notice\"":""?>>Nom : <input type="text" name="nom" id="nom" value="<?=@htmlspecialchars($nom)?>" maxlength="50" /></label>
					<p class="indic">Ton nom ne sert qu'à l'équipe de <cite>noteTonProf.fr</cite>, il ne sera en aucun cas affiché sur le site.</p>
				</fieldset>
				<p class="facultatif etoile">*Champ facultatif, 5 caractères minimum (caractères quelconques)</p>
				<div class="save"><input type="submit" value="Enregistrer" /></div>
			</form>
			<hr />
			<h2>Suppression</h2>
<? if (@$noticeD) { ?>
			<div class="head-notice">
				<p>Attention ! Vérifie les données du formulaire :</p>
				<ul><? foreach ($noticeD as $alert) { ?><li><?=$alert?></li><? } ?></ul>
			</div>
<? } ?>
			<form action="<?=$_SERVER["SCRIPT_NAME"]?>" method="post" onsubmit="return confirm('Es-tu sûr de vouloir te désinscrire ?');">
				<input type="hidden" name="del" value="1" />
				<fieldset>
					<legend>Vérification</legend>
					<label for="pass"<?=(@$noticeD["pass"])?" class=\"notice\"":""?>>Mot de passe : <input type="password" name="pass" id="pass" value="" maxlength="50" /></label>
				</fieldset>
				<div class="save"><input type="submit" value="Désinscription" /></div>
			</form>
		</div>
<? require("tpl/bas.php"); ?>
