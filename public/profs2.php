<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

// début traitement des variables URL
$expl = @explode("/", $_SERVER["PATH_INFO"]);
if (@strlen($expl[1]) > 0)
{
	$e_id = (int) urldecode($expl[1]);
	
	$erreur_url = FALSE;

	// début vérification des variables
	$q_check_vars = "SELECT etablissements.nom, cursus, secondaire, dept, cp, villes.nom AS commune, villes.id as c_id FROM etablissements, villes WHERE etablissements.status = 'ok' AND etablissements.id = $e_id && villes.id = etablissements.ville_id".((Admin::MOD_SCHOOL)?" && etablissements.moderated = 'yes'":"");
	
	$result = DBPal::query($q_check_vars);
	$rnb = $result->num_rows;
	
	if ($rnb)
	{
		$row = $result->fetch_assoc();
		$cursus = $row["cursus"];
		$secondaire = $row["secondaire"];
		$dept = $row["dept"];
		$cp = $row["cp"];
		$e_nom = $row["nom"];
		$c_nom = $row["commune"];
		$c_id = $row["c_id"];
		
		$query =	"SELECT DISTINCT COUNT( notes.id ) AS notes, matieres.nom AS matiere, professeurs.nom, prenom, professeurs.id, UNIX_TIMESTAMP( MAX( notes.date ) ) AS lastdate , AVG( interet + pedagogie + connaissances ) / 3 AS moy, AVG( FIND_IN_SET( 'pop', extra ) || FIND_IN_SET( 'in', extra ) ) AS pop ".
				"FROM (professeurs, matieres) ".
				"LEFT JOIN notes ON notes.prof_id = professeurs.id && notes.status = 'ok' ".
				"WHERE professeurs.status = 'ok' && etblt_id = $e_id && matieres.id = professeurs.matiere_id".((Admin::MOD_PROF)?" && professeurs.moderated = 'yes'":"")." ".
				"GROUP BY professeurs.id ".
				"ORDER BY nom, prenom"; // LIMIT 0 , 30
		$result = DBPal::query($query);
		$rnb = $result->num_rows;
		
		$q_count_deleg = "SELECT COUNT(*) FROM delegues_etblts, delegues WHERE delegues.id = delegues_etblts.delegue_id && delegues_etblts.etblt_id = $e_id && status = 'ok' AND checked = 1 && locked = 'no' && TO_DAYS(NOW()) - TO_DAYS(last_conn) <= ".Admin::DEF_ADMIN_INACTIVE_AFTER;
		$nb_deleg = DBPal::getOne($q_count_deleg);
		
		//session_start();
		
		$erreur_var = FALSE;
		
		setcookie("last_etblt", $e_id, time() + 3600 * 24 * 365, "/", Settings::COOKIE_DOMAIN);
	}
	else
		$erreur_var = TRUE;
	// fin vérification des variables
}
else	$erreur_url = TRUE;
// fin traitement des variables URL

// Controller-View limit
DBPal::finish();

$title = "Liste des professeurs";
?>
<? require("tpl/haut.php"); ?>
<? if ($erreur_url) require("tpl/erreur_url.php"); else if ($erreur_var) require("tpl/erreur_var.php"); else { ?>
		<div class="navi">
			<?=Helper::navPath(array(
				$cursus,
				Geo::$DEPT[$dept]['ind'],
				$dept,
				array($c_id, $cp, $c_nom),
				array($e_id, $e_nom)
			), true)?>
		</div>
		<hr />
		<div>
			<div class="info-box">
<? if ($nb_deleg) { ?>
				Cet établissement est modéré par <?=$nb_deleg?> délégué<?=($nb_deleg == 1)?"":"s"?> actif<?=(($nb_deleg == 1)?"":"s")."\n"?>
<? } else { ?>
				Il n'y a aucun délégué actif pour cet établissement, <a href="devenir_delegue?etblt_id=<?=$e_id?>">propose-toi</a> !
<? } ?>
			</div>
			<h2><span class="etab"><?=htmlspecialchars($e_nom)?></span><? if ($cursus == E_2ND) { ?> (<? $secondaire = explode(",", $secondaire); foreach ($secondaire as $key => $val) { ?><?=Geo::$SECONDARY[$val].((isset($secondaire[$key + 1]))?", ":"")?><? } ?>)<? } ?><? if ($user->hasAccess($e_id)) { ?> <a href="admin/edit-school?id=<?=$e_id?>"><img src="img/edit.png" alt="Crayon" title="Éditer" height="16" width="16" /></a><? } ?></h2>
<? if ($message = Web::flash('msg')) { ?>
			<p class="msg"><?=$message?></p>
<? } ?>
			<p class="rapport"><a href="signaler?type=school&amp;id=<?=$e_id?>"><img src="img/attention.png" height="15" width="9" alt="" />Signaler une erreur sur cet établissement</a></p>
<? if (($nb_deleg < ceil($rnb / Admin::QUOTA_PROFS_PER_ADMIN) * Admin::QUOTA_MAX_REDONDANCY || $rnb == 0) and !$user->hasSchool($e_id)) { ?>
			<p class="action"><a href="devenir_delegue?etblt_id=<?=urlencode($e_id)?>">Devenir délégué</a></p>
<? } ?>
			<p style="clear: both; text-align: left;"><?=$rnb?> professeurs référencé(s) pour l'établissement : <span class="etab"><? if ($cursus == E_2ND) { ?><? foreach ($secondaire as $key => $val) { ?><?=Geo::$SECONDARY[$val].((isset($secondaire[$key + 1]))?", ":" ")?><? } ?><? } ?><?=htmlspecialchars($e_nom)?></span> (<?=htmlspecialchars($c_nom)?>) :</p>
			<table class="grille moyenne">
				<thead>
					<tr>
						<th></th>
						<th class="nom">Nom</th>
						<th class="nom"><span class="abbr" title="ou Civilité">Prénom</span></th>
						<th class="nom">Matière</th>
<? if ($user->hasAccess($e_id)) { ?>
						<th></th>
<? } ?>
						<th class="nbre"><span class="abbr" title="Moyenne">Moy.</span></th>
						<th class="nbre"><span class="abbr" title="Nombre de notes">Nb.</span></th>
						<th class="nom"><span class="abbr" title="Dernière note">Der&hellip;</span></th>
					</tr>
				</thead>
<? for ($i = 0; $row = $result->fetch_assoc(); $i = ($i + 1) % 2) { ?>
				<tbody<?=($i == 1)?" class=\"impair\"":""?>>
					<tr>
						<td><?=Helper::smiley($row["moy"], $row["pop"])?></td>
						<th class="nom up"><a title="Voir/Noter ce professeur" href="notes2/<?=urlencode($row["id"])?>/"><?=htmlspecialchars($row["nom"])?></a></th>
						<td class="nom"><a title="Voir/Noter ce professeur" href="notes2/<?=urlencode($row["id"])?>/"><?=htmlspecialchars($row["prenom"])?></td>
						<td class="nom small"><?=htmlspecialchars($row["matiere"])?></th>
<? if ($user->hasAccess($e_id)) { ?>
						<td><a href="admin/edit-prof?id=<?=$row["id"]?>"><img src="img/edit.png" alt="Crayon" title="Éditer" height="16" width="16" /></a></td>
<? } ?>
						<td class="nbre"><?=($row["moy"]) ? number_format($row["moy"], 1) : "<em>-</em>"?></td>
						<td class="nbre"><?=$row["notes"]?></td>
						<td class="nom small"><?=($row["lastdate"]) ? strftime("%d/%m/%Y", $row["lastdate"]) : "<em>-</em>"?></td>
					</tr>
				</tbody>
<? } ?>
			</table>
			<p>Toutes les notes sont établies sur une échelle allant de <strong>1</strong> à <strong>5</strong>.</p>
			<p class="action"><a href="ajout_prof?etblt_id=<?=urlencode($e_id)?>">Ajouter un professeur</a></p>
		</div>
<? } ?>
<? require("tpl/bas.php"); ?>
