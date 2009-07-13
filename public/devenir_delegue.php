<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

// initialisation des variables
$notice = FALSE;

// début traitement des variables URL
if (@isset($_GET["etblt_id"]))
{
	$e_id = (int) $_GET["etblt_id"];
	
	$erreur_url = FALSE;
	
	// début vérification des variables
	$query = "SELECT etablissements.nom, cursus, dept, cp, villes.nom AS commune, villes.id AS c_id FROM etablissements, villes WHERE villes.id = etablissements.ville_id && etablissements.id = $e_id AND etablissements.status = 'ok'".((Admin::MOD_SCHOOL)?" && etablissements.moderated = 'yes'":"");
	$result = DBPal::query($query);
	$rnb = $result->num_rows;
	
	if ($rnb)
	{
		$row = $result->fetch_assoc();
		$cursus = $row["cursus"];
		$dept = $row["dept"];
		$cp = $row["cp"];
		$e_nom = $row["nom"];
		$c_nom = $row["commune"];
		$c_id = $row["c_id"];
		
		$erreur_var = FALSE;
	}
	else
	{
		$erreur_var = TRUE;
	}
	// fin vérification des variables
}
else	$erreur_url = TRUE;
// fin traitement des variables URL

// début traitement formulaire
if (!$erreur_url && !$erreur_var)
{
	if ($user->uid)
	{
	    $query = "SELECT COUNT(*) FROM delegues_etblts WHERE delegue_id = {$user->uid} && etblt_id = {$e_id}";
	    $rnb = DBPal::getOne($query);
	    
	    if ($rnb)
	    	$_SESSION["msg"] = "Tu es déjà délégué de cet établissement !";
	    else
	    {
	    	// enregistrement
	    	$query = "INSERT INTO delegues_etblts (etblt_id, delegue_id) VALUES ($e_id, {$user->uid})";
	    	DBPal::query($query);
	    	
	    	// log
	    	App::log("Enlisted as a moderator on school", "user", $d_id, $d_id, array("school_id" => $e_id));
	    	
	    	$_SESSION["msg"] = "Tu es maintenant délégué de cet établisssement.";
	    }
	    	
	    // redirect
	    Web::redirect("/profs2/$e_id");
	}
}
// fin traitement formulaire

// Controller-View limit
DBPal::finish();

$title = "Devenir délégué";
?>
<? require("tpl/haut.php"); ?>
<? if ($erreur_url) require("tpl/erreur_url.php"); else if ($erreur_var) require("tpl/erreur_var.php"); else { ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <a href="indicatifs/<?=urlencode($cursus)?>">Enseignement <?=Geo::$COURSE[$cursus]?></a> &gt; <a href="depts/<?=urlencode($cursus)?>/<?=urlencode(Geo::$DEPT[$dept]["ind"])?>/">Indicatif <?=htmlspecialchars(Geo::$DEPT[$dept]["ind"])?></a> &gt; <a href="villes/<?=urlencode($cursus)?>/<?=urlencode($dept)?>/"><?=htmlspecialchars($dept)?> - <?=htmlspecialchars(Geo::$DEPT[$dept]["nom"])?></a> &gt; <a href="etblts/<?=urlencode($cursus)?>/<?=urlencode($c_id)?>/"><?=htmlspecialchars($cp)?> - <?=htmlspecialchars($c_nom)?></a> &gt; <a href="profs2/<?=urlencode($e_id)?>/"><span class="etab"><?=htmlspecialchars($e_nom)?></span></a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<h2>Tu n'es pas encore inscrit</h2>
			<p>C'est le moment de le faire ! : <a href="inscription">Inscription</a></p>
			<hr />
			<h2>Tu es déjà inscrit</h2>
			<p>
				Entre tes identifiants et le tour est joué !<br />
				<a href="ini_pass">Mot de passe oublié ?</a>
			</p>
			<form action="login?return=1" method="post" class="form">
				<input type="hidden" name="sent" value="1" />
				<dl>
					<dt>Identification</dt>
					<dd>
						<fieldset>
							<legend>Identifiants</legend>
							<label for="email">E-mail : <input type="text" name="email" id="email" maxlength="50" /></label>
							<label for="pass">Mot de passe : <input type="password" name="pass" id="pass" maxlength="50" /></label>
						</fieldset>
						<fieldset>
							<legend>Options</legend>
							<label for="remember"><input type="checkbox" name="remember" id="remember" value="1" /> Rester connecté</label>
						</fieldset>
					</dd>
					<dt>Validation</dt>
					<dd><input type="submit" value="Se Connecter" /></dd>
					</dd>
				</dl>
			</form>
		</div>
<? } ?>
<? require("tpl/bas.php"); ?>
