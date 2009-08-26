<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

// début traitement des variables URL
if (@isset($_GET["cursus"]) && @isset($_GET["dept"]))
{
	$cursus = $_GET["cursus"];
	$dept = $_GET["dept"];
	
	$erreur_url = FALSE;
	
	// début vérification des variables
	if (isset(Geo::$COURSE[$cursus]) && isset(Geo::$DEPT[$dept]))
	{
		$erreur_var = FALSE;
		
		$cp_length = 5 - strlen($dept);
	}
	else
		$erreur_var = TRUE;
	// fin vérification des variables
}
else	$erreur_url = TRUE;
// fin traitement des variables URL

// début traitement formulaire
if (!$erreur_url && !$erreur_var)
{
	$sent = @$_GET["sent"];
	
	if ($sent)
	{
		$s_cp = @$_GET["s_cp"];
		
		if (!ereg("^[0-9]{".$cp_length."}$", $s_cp))
			$notice["s_cp"] = "Suffixe du code postal incorrect, celui-ci doit être composé de $cp_length chiffres.";
		else
		{
			// vérification, attribution éventuelle du code postal
			
			$query = "SELECT cp FROM villes"
			       . " WHERE dept = " . DBPal::quote($dept) . " && cp <= " . DBPal::quote($dept.$s_cp)
			       . " ORDER by cp desc LIMIT 1;";
			$row = DBPal::getRow($query);
			
			if (!$row)
			{
				// on ne trouve aucun code postal similaire
				
				$notice["s_cp"] = "Aucun code postal semblable n'a été trouvé.";
			}
			else
			{
				// passage à l'étape suivante
				
				$cp = $row->cp;
				
				if ($cp != $dept.$s_cp)
					$_SESSION["msg"] = "Le code postal le plus proche de celui que tu as indiqué a été pris en compte.";
				
				Web::redirect("/ajout_etblt2?cursus=".rawurlencode($cursus)."&cp=".rawurlencode($cp));
			}
		}
	}
	else
		$notice = FALSE;
}
// fin traitement formulaire

// Controller-View limit
DBPal::finish();

$title = "Ajout d'un établissement";
?>
<? require("tpl/haut.php"); ?>
<? if ($erreur_url) require("tpl/erreur_url.php"); else if ($erreur_var) require("tpl/erreur_var.php"); else { ?>
		<div class="navi">
			<?=Helper::navPath(array(
				$cursus,
				Geo::$DEPT[$dept]['ind'],
				$dept
			))?>
			&gt; <?=htmlspecialchars($title)?>
		</div>
		<hr />
		<div>
			<h2>Formulaire d'ajout d'établissement</h2>
			<p>Étape 1/2, entrer le code postal :</p>
<? if ($notice) { ?>
			<div class="head-notice">
				<p>Attention ! Vérifie les données du formulaire :</p>
				<ul><? foreach ($notice as $alert) { ?><li><?=$alert?></li><? } ?></ul>
			</div>
<? } ?>
			<form action="<?=$_SERVER["SCRIPT_NAME"]?>" method="get" class="form">
				<input type="hidden" name="cursus" value="<?=htmlspecialchars($cursus)?>" />
				<input type="hidden" name="dept" value="<?=htmlspecialchars($dept)?>" />
				<input type="hidden" name="sent" value="1" />
				<dl>
					<dt>Étape 1/2</dt>
					<dd><label for="s_cp"<?=(@$notice["s_cp"])?" class=\"notice\"":""?>>Code Postal : <?=htmlspecialchars($dept)?><input type="text" name="s_cp" id="s_cp" value="<?=@htmlspecialchars($s_cp)?>" size="<?=$cp_length?>" maxlength="<?=$cp_length?>" /></label></dd>
					<dt>Validation</dt>
					<dd><input type="submit" value="Continuer" /></dd>
				</dl>
			</form>
		</div>
<? } ?>
<? require("tpl/bas.php"); ?>
