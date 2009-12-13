<?
define("ROOT_DIR", "../");
require(ROOT_DIR."_ini.php");

$user->requireOrRedirect();

// initialisation des variables
$success = FALSE;
$notice = NULL;
$q_add = "";

if ($_SERVER["REQUEST_METHOD"] == 'POST')
{
    $etblt = @$_POST["etblt"];
    
    while (list($e_id, $val) = @each($etblt))
    {
    	$e_id = (int) $e_id;
    	$q_add .= " || etblt_id=" . DBPal::quote($e_id);
    	
    	// log
    	App::log("Unenlisted as a moderator from school", "user", $user->uid, $user->uid, array("school_id" => $e_id));
    	
    	// refresh assignments for that school
    	App::queue('refresh-assignments', array('for-school', $e_id));
    }		
    
    $query = "DELETE FROM delegues_etblts WHERE delegue_id = {$user->uid} && (0$q_add)";
    $result = DBPal::query($query);
    
    $success = "Modifications enregistrées avec succès.";
}

$query = "SELECT etablissements.id AS id, etablissements.nom AS nom, cursus, secondaire, villes.nom AS commune, cp, ville_id FROM etablissements, villes, delegues_etblts WHERE delegues_etblts.delegue_id = {$user->uid} && etablissements.id = delegues_etblts.etblt_id && villes.id = etablissements.ville_id";
$result = DBPal::query($query);
$count = $result->num_rows;

// Controller-View limit
DBPal::finish();

$title = "Mes Établissements";
?>
<? require("tpl/haut.php"); ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <a href="/admin/index">Espace Délégué</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<h2>Établissements modérés</h2>
			<p class="tip">Pour modérer un nouvel établissement, rends-toi sur la page de l'établissement que tu veux modérer à partir de la <a href="/">page d'accueil de NoteTonProf.com</a> et clique sur « Devenir délégué ».</p>
			<p class="warning">Ton compte sera automatiquement bloqué si tu change trop souvent les établissements que tu modères.</p>
<? if ($success) { ?>
			<p class="msg"><?=$success?></p>
<? } ?>
<? if ($count > 0) { ?>
			<form action="<?=$_SERVER["SCRIPT_NAME"]?>" method="post">
				<table class="list">
					<thead>
						<tr>
							<th class="first check">Séléct.</th>
							<th>Cursus</th>
							<th>Nom</th>
							<th>Commune</th>
							<th class="check">Édit.</th>
						</tr>
					</thead>
<? while ($row = $result->fetch_assoc()) { ?>
					<tbody>
						<tr>
							<td class="first check"><input type="checkbox" value="1" name="etblt[<?=htmlspecialchars($row["id"])?>]" /></td>
							<td><a href="indicatifs/<?=urlencode($row["cursus"])?>/"><?=htmlspecialchars(Geo::$COURSE[$row["cursus"]])?></a></td>
							<th><a href="profs/<?=urlencode($row["id"])?>/"><? if ($row["cursus"] == E_2ND) { ?><? $secondaire = explode(",", $row["secondaire"]); foreach ($secondaire as $key => $val) { ?><?=Geo::$SECONDARY[$val].((isset($secondaire[$key + 1]))?", ":"")?><? } ?> <? } ?><?=htmlspecialchars($row["nom"])?></a></th>
							<td><a href="etblts/<?=urlencode($row["cursus"])?>/<?=urlencode($row["ville_id"])?>/"><?=htmlspecialchars($row["cp"])?>, <?=htmlspecialchars($row["commune"])?></a></td>
							<td class="check"><a href="admin/edit-school?id=<?=$row["id"]?>"><img src="img/edit.png" alt="Crayon" title="Éditer" height="16" width="16" /></a></td>
						</tr>
					</tbody>
<? } ?>
				</table>
				<div class="save"><input type="submit" value="Ne plus modérer" /></div>
			</form>
<? } else { ?>
			<p class="info">Cette liste est vide.</p>
<? } ?>
		</div>
<? require("tpl/bas.php"); ?>
