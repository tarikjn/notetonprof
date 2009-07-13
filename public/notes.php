<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

// début traitement des variables URL
$expl = @explode("/", $_SERVER["PATH_INFO"]);
if (@strlen($expl[1]) > 0)
{
	$p_id = (int) urldecode($expl[1]);
	
	$erreur_url = FALSE;

	// début vérification des variables
	$query =	"SELECT COUNT( notes.id ) AS notes, etablissements.nom AS etblt, etablissements.id AS e_id, etablissements.cursus, secondaire, dept, cp, villes.nom AS commune, villes.id AS c_id, professeurs.*, matieres.nom AS matiere, AVG( interet + pedagogie + connaissances ) / 3 AS moy, AVG( interet ) AS interet, AVG( pedagogie ) AS pedagogie, AVG( connaissances ) AS connaissances, AVG( regularite ) AS regularite, AVG( ambiance ) AS ambiance, AVG( justesse ) AS justesse, AVG( FIND_IN_SET( 'pop', extra ) || FIND_IN_SET( 'in', extra ) ) AS extra, AVG( FIND_IN_SET( 'pop', extra ) > 0 ) AS pop,  AVG( FIND_IN_SET( 'in', extra ) > 0 ) AS style ".
			"FROM professeurs, matieres, etablissements, villes ".
			"LEFT JOIN notes ON notes.prof_id = professeurs.id && notes.status = 'ok' ".
			"WHERE professeurs.status = 'ok' && professeurs.id = $p_id && etablissements.id = professeurs.etblt_id AND etablissements.status = 'ok' && villes.id = etablissements.ville_id && matieres.id = professeurs.matiere_id".((Admin::MOD_SCHOOL)?" && etablissements.moderated = 'yes'":"").((Admin::MOD_PROF)?" && professeurs.moderated = 'yes'":"")." ".
			"GROUP BY professeurs.id";
	$result = DBPal::query($query);
	$rnb = $result->num_rows;
	
	if ($rnb)
	{
		$row = $result->fetch_assoc();
		$cursus = $row["cursus"];
		$dept = $row["dept"];
		$cp = $row["cp"];
		$secondaire = $row["secondaire"];
		$e_nom = $row["etblt"];
		$e_id = $row["e_id"];
		$c_nom = $row["commune"];
		$c_id = $row["c_id"];
		$nom = $row["nom"];
		$prenom = $row["prenom"];
		$matiere = $row["matiere"];
		$sujet = $row["sujet"];
		
		// redirection COOKIE
		if (isset($_COOKIE["acces"][$e_id]))
		{
			setcookie("acces[$e_id]", 1, time() + 3600 * 24 * 365, "/", Settings::COOKIE_DOMAIN);
			Web::redirect("/notes2/$p_id/");
		}
		
		// fin redirection COOKIE
		
		// redirection POST
		$action = @$_POST["action"];
		
		if ($action == "Oui")
		{
			setcookie("acces[$e_id]", 1, time() + 3600 * 24 * 365, "/", Settings::COOKIE_DOMAIN);
			Web::redirect("/notes2/$p_id/");
		}
		else if ($action == "Non")
		{
			Web::redirect("/etblts/$cursus/$c_id/");
		}
		// fin redirection POST
		
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

$title = "Accès établissement";
?>
<? require("tpl/haut.php"); ?>
<? if ($erreur_url) require("tpl/erreur_url.php"); else if ($erreur_var) require("tpl/erreur_var.php"); else { ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <a href="indicatifs/<?=urlencode($cursus)?>">Enseignement <?=Geo::$COURSE[$cursus]?></a> &gt; <a href="depts/<?=urlencode($cursus)?>/<?=urlencode(Geo::$DEPT[$dept]["ind"])?>/">Indicatif <?=htmlspecialchars(Geo::$DEPT[$dept]["ind"])?></a> &gt; <a href="villes/<?=urlencode($cursus)?>/<?=urlencode($dept)?>/"><?=htmlspecialchars($dept)?> - <?=htmlspecialchars(Geo::$DEPT[$dept]["nom"])?></a> &gt; <a href="etblts/<?=urlencode($cursus)?>/<?=urlencode($c_id)?>/"><?=htmlspecialchars($cp)?> - <?=htmlspecialchars($c_nom)?></a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<h2>Accès établissement</h2>
			<p>Pour accéder à la page de ce professeur, tu dois faire partie ou avoir fait partie de son établissement. La page de l'établissement et les pages qui en font partie ne sont pas publics !</p>
			<form action="<?=$_SERVER["SCRIPT_NAME"]?>/<?=urlencode($p_id)?>/" method="post">
				<p><strong>Certifie-tu sur l'honneur faire ou avoir fait partie de l'établissement : <span class="etab"><? if ($cursus == E_2ND) { ?><? $secondaire = explode(",", $secondaire); foreach ($secondaire as $key => $val) { ?><?=Geo::$SECONDARY[$val].((isset($secondaire[$key + 1]))?", ":" ")?><? } ?><? } ?><?=htmlspecialchars($e_nom)?></span> (<?=htmlspecialchars($c_nom)?>), ceci en temps qu'élève ou personnel de l'établissement ?</strong></p>
				<div class="submit"><input type="submit" name="action" value="Oui" /> <input type="submit" name="action" value="Non" /></div>
			</form>
			<p class="info-add">Si les cookies sont activés sur ton navigateur et ta réponse est positive, cette page ne s'affichera plus lorsque tu voudras accéder à cet établissement.</p>
		</div>
<? } ?>
<? require("tpl/bas.php"); ?>
