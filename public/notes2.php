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
	$query =	"SELECT COUNT( notes.id ) AS notes, etablissements.nom AS etblt, etablissements.id AS e_id, etablissements.cursus, secondaire, dept, cp, villes.nom AS commune, villes.id AS c_id, professeurs.*, matieres.nom AS matiere, AVG(" . Ratings::SQL_AVERAGE . ") AS moy, AVG( interest ) AS interest, AVG( clarity ) AS clarity, AVG( knowledgeable ) AS knowledgeable, AVG( fairness ) AS fairness, AVG( availability ) AS availability, AVG( regularity ) AS regularity, AVG( atmosphere ) AS atmosphere, AVG( difficulty ) AS difficulty, AVG( FIND_IN_SET( 'pop', extra ) || FIND_IN_SET( 'in', extra ) ) AS extra, AVG( FIND_IN_SET( 'pop', extra ) > 0 ) AS pop,  AVG( FIND_IN_SET( 'in', extra ) > 0 ) AS style ".
			"FROM (professeurs, matieres, etablissements, villes) ".
			"LEFT JOIN notes ON notes.prof_id = professeurs.id && notes.status = 'ok' ".
			"WHERE professeurs.status = 'ok' && professeurs.id = $p_id && etablissements.id = professeurs.etblt_id && villes.id = etablissements.ville_id AND etablissements.status = 'ok' && matieres.id = professeurs.matiere_id".((Admin::MOD_SCHOOL)?" && etablissements.moderated = 'yes'":"").((Admin::MOD_PROF)?" && professeurs.moderated = 'yes'":"")." ".
			"GROUP BY professeurs.id";
	$result = DBPal::query($query);
	$rnb = $result->num_rows;
	
	if ($rnb)
	{
		$record = $result->fetch_assoc();
		$cursus = $record["cursus"];
		$dept = $record["dept"];
		$cp = $record["cp"];
		$secondaire = $record["secondaire"];
		$e_nom = $record["etblt"];
		$e_id = $record["e_id"];
		$c_nom = $record["commune"];
		$c_id = $record["c_id"];
		$nom = $record["nom"];
		$prenom = $record["prenom"];
		$matiere = $record["matiere"];
		$sujet = $record["sujet"];
		$notes = $record["notes"];
		$moy = $record["moy"];
		$pop = $record["pop"];
		$style = $record["style"];
		$extra = $record["extra"];
		
		$query = "SELECT id, moderated, open_ticket, UNIX_TIMESTAMP( date ) AS date, clarity, interest, fairness, knowledgeable, availability, regularity, atmosphere, difficulty, " . Ratings::SQL_AVERAGE . " AS moy, ( FIND_IN_SET( 'pop', extra ) || FIND_IN_SET( 'in', extra ) ) AS pop, comment FROM notes WHERE prof_id = $p_id AND status = 'ok' ORDER by date desc";
		$result = DBPal::query($query);
		$rnb = $result->num_rows;
		
		//session_start();
		
		$message = FALSE;
		if (isset($_SESSION["msg"]))
		{
			$message = $_SESSION["msg"];
			unset($_SESSION["msg"]);
		}
		else if (isset($_COOKIE["votes"][$p_id]))
		{
			$found_n = FALSE;
			
			while (list ($n_id, ) = each($_COOKIE["votes"][$p_id]))
			{
				$n_id = (int) $n_id;
			
				if ($found_n)
					$where_n .= " || id = $n_id";
				else
					$where_n = "id = $n_id";
				$found_n = TRUE;
			}
			
			if ($found_n)
			{
				$query = "SELECT id FROM notes WHERE ($where_n) && status = 'ok';";
				$test_all_del = DBPal::query($query);
				$rnb = $test_all_del->num_rows;
				
				if (!$rnb)
				{
					$message = "Ton évaluation précédente pour ce professeur a été effacée, elle devait certainement ne pas respecter <a href=\"regles\">les règles</a>. Si tu es persuadé du contraire, <a href=\"contact\">contacte</a> la team de NoteTonProf.com. Tu peux à nouveau noter ce professeur.";
				}
			}
		}
		
		$erreur_var = FALSE;
	}
	else
		$erreur_var = TRUE;
	
	if (!isset(Geo::$DEPT[$dept]))
		$erreur_var = TRUE;
	
	// fin vérification des variables
}
else	$erreur_url = TRUE;
// fin traitement des variables URL

// Controller-View limit
DBPal::finish();

$title = $prenom . ' ' . strtoupper($nom);
?>
<? require("tpl/haut.php"); ?>
<? if ($erreur_url) require("tpl/erreur_url.php"); else if ($erreur_var) require("tpl/erreur_var.php"); else { ?>
		<div class="navi">
			<?=Helper::navPath(array(
				$cursus,
				Geo::$DEPT[$dept]['ind'],
				$dept,
				array($c_id, $cp, $c_nom),
				array($e_id, $e_nom),
				array($p_id, $prenom, $nom)
			), true)?>
		</div>
		<hr />
		<div>
			<table class="fiche">
				<tr class="p">
					<th>Notes :</th>
					<td class="r nb"><?=($notes)?></td>
				</tr>
				<tr class="new">
					<th>Moyenne :</th>
					<td class="r nb"><?=($moy) ? Helper::f_avg($moy) : "<em>-</em>"?></td>
				</tr>
				<tr class="new">
					<td><?=Ratings::$CRITERIAS['interest']['title']?> :</td>
					<td class="r nb"><?=($record['interest']) ? Helper::f_avg($record['interest']) : "<em>-</em>"?></td>
				</tr>
				<tr>
					<td><?=Ratings::$CRITERIAS['clarity']['title']?> :</td>
					<td class="r nb"><?=($record['clarity']) ? Helper::f_avg($record['clarity']) : "<em>-</em>"?></td>
				</tr>
				<tr>
					<td><?=Ratings::$CRITERIAS['knowledgeable']['title']?> :</td>
					<td class="r nb"><?=($record['knowledgeable']) ? Helper::f_avg($record['knowledgeable']) : "<em>-</em>"?></td>
				</tr>
				<tr>
					<td><?=Ratings::$CRITERIAS['fairness']['title']?> :</td>
					<td class="r nb"><?=($record['fairness']) ? Helper::f_avg($record['fairness']) : "<em>-</em>"?></td>
				</tr>
				<tr class="new">
					<td><?=Ratings::$CRITERIAS['regularity']['title']?> :</td>
					<td class="r nb"><?=($record['regularity']) ? Helper::f_avg($record['regularity']) : "<em>-</em>"?></td>
				</tr>
				<tr>
					<td><?=Ratings::$CRITERIAS['availability']['title']?> :</td>
					<td class="r nb"><?=($record['availability']) ? Helper::f_avg($record['availability']) : "<em>-</em>"?></td>
				</tr>
				<tr>
					<td><?=Ratings::$CRITERIAS['difficulty']['title']?> :</td>
					<td class="amb"><?=Helper::centeredAvg($record['difficulty'])?></td>
				</tr>
				<tr>
					<td><?=Ratings::$CRITERIAS['atmosphere']['title']?> :</td>
					<td class="amb"><?=Helper::centeredAvg($record['atmosphere'])?></td>
				</tr>
				<tr>
					<td>Popularité :</td>
					<td class="r"><?=($pop) ? number_format($pop * 100, 0)."%" : "<em>-</em>"?></td>
				</tr>
				<tr class="d">
					<td>Style :</td>
					<td class="r"><?=($style) ? number_format($style * 100, 0)."%" : "<em>-</em>"?></td>
				</tr>
			</table>
			<div class="left-box">
				<h2 class="over"><?=htmlspecialchars($prenom)?> <span class="up"><?=htmlspecialchars($nom)?></span><? if ($user->hasAccess($e_id)) { ?> <a href="admin/edit-prof?id=<?=$p_id?>"><img src="img/edit.png" alt="Crayon" title="Éditer" height="16" width="16" /></a><? } ?></h2>
				<div class="under">
					<a href="profs/<?=urlencode($e_id)?>/"><?=htmlspecialchars($e_nom)?><? if ($cursus == E_2ND) { ?> (<? $secondaire = explode(",", $secondaire); foreach ($secondaire as $key => $val) { ?><?=Geo::$SECONDARY[$val].((isset($secondaire[$key + 1]))?", ":"")?><? } ?>)<? } ?></a><br />
					<?=htmlspecialchars($c_nom)?> (<?=htmlspecialchars($dept)?>)
				</div>
			</div>
			<?=Helper::smiley($moy, $extra, 1)?>
<? if ($cursus == E_2ND) { ?>
			<div class="major">Matière : <em><?=htmlspecialchars( $matiere )?></em></div>
<? } else { ?>
			<div class="major">Matière : <em><?=htmlspecialchars( ($sujet) ? "$sujet ($matiere)" : $matiere )?></em></div>
<? } ?>
<? if ($message) { ?>
			<p class="msg"><?=$message?></p>
<? } ?>
			<p class="rapport"><a href="signaler?type=prof&amp;id=<?=urlencode($p_id)?>"><img src="img/attention.png" height="15" width="9" alt="" />Signaler une erreur sur ce professeur</a></p>
			<p class="action"><a href="ajout_note?prof_id=<?=urlencode($p_id)?>">Ajouter une note</a></p>
			<p>Toutes les notes sont établies sur une échelle allant de <strong>1</strong> à <strong>5</strong>.</p>
			<table class="grille large">
				<?
					for ($i = 0; $i == 0 or ($i < 2 and $notes >= 10); $i++)
					{
						$tag = ($i == 0)? 'thead':'tfoot';
				?>
				<<?=$tag?>>
					<tr>
						<th></th>
						<th class="nbre">Date</th>
						<th class="eval"><span class="abbr" title="<?=Ratings::$CRITERIAS['interest']['title']?>">Int.</span></th>
						<th class="eval"><span class="abbr" title="<?=Ratings::$CRITERIAS['clarity']['title']?>">Péd.</span></th>
						<th class="eval"><span class="abbr" title="<?=Ratings::$CRITERIAS['knowledgeable']['title']?>">Conn.</span></th>
						<th class="eval"><span class="abbr" title="<?=Ratings::$CRITERIAS['fairness']['title']?>">Just.</span></th>
						<th class="eval"><span class="abbr" title="<?=Ratings::$CRITERIAS['regularity']['title']?>">Rég.</span></th>
						<th class="eval"><span class="abbr" title="<?=Ratings::$CRITERIAS['availability']['title']?>">Disp.</span></th>
						<th class="eval"><span class="abbr" title="<?=Ratings::$CRITERIAS['difficulty']['title']?>">Niv.</span></th>
						<th class="eval"><span class="abbr" title="<?=Ratings::$CRITERIAS['atmosphere']['title']?>">Amb.</span></th>
						<th class="nom" style="width: 100%">Commentaire</th>
						<th></th>
					</tr>
				</<?=$tag?>>
				<?
					}
				?>
<? for($i = 0; $row = $result->fetch_assoc(); $i = ($i + 1) % 2) { ?>
				<tbody<?=(isset($_COOKIE["votes"][$p_id][$row["id"]])) ? " class=\"tanote\"" : (($i == 1) ? " class=\"impair\"" : "")?>>
					<tr>
						<td><?=Helper::smiley($row["moy"], $row["pop"])?></td>
						<td class="nbre small"><?=strftime("%d/%m/%Y", $row["date"])?></td>
						<td class="eval"><?=$row["interest"]?></td>
						<td class="eval"><?=$row["clarity"]?></td>
						<td class="eval"><?=$row["knowledgeable"]?></td>
						<td class="eval"><?=$row["fairness"]?></td>
						<td class="eval"><?=$row["regularity"]?></td>
						<td class="eval"><?=$row["availability"]?></td>
						<td><div class="smilep"><?=Helper::centeredRating($row["difficulty"], 'difficulty')?></div></td>
						<td><div class="smilep"><?=Helper::centeredRating($row["atmosphere"], 'atmosphere')?></div></td>
						<td class="comment" style="width: 100%">
<? if ((Admin::MOD_COMMENT && $row["moderated"] != 'yes') or $row["open_ticket"]) { ?>
<? if (strlen($row["comment"]) > 0) { ?>
							<em>Commentaire en attente de validation&hellip;</em>
<? } else { ?>
							<em></em>
<? } ?>
<? } else { ?>
							<div><?=h($row["comment"])?></div>
<? } ?>
						</td>
						<td>
<? if ((!Admin::MOD_COMMENT || $row["moderated"] == 'yes') && strlen($row["comment"]) > 0) { ?>
							<a href="signaler?type=comment&amp;id=<?=urlencode($row["id"])?>"><img src="img/attention.png" height="15" width="9" alt="" title="Signaler un commentaire ne respectant pas les règles" /></a>
<? } ?>
						</td>
					</tr>
				</tbody>
<? } ?>
			</table>
			<p class="action"><a href="ajout_note?prof_id=<?=urlencode($p_id)?>">Ajouter une note</a></p>
		</div>
<? } ?>
<? require("tpl/bas.php"); ?>
