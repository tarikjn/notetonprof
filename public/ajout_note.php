<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

// début traitement des variables URL
if (@isset($_GET["prof_id"]))
{
	$p_id = (int) $_GET["prof_id"];
	
	$erreur_url = FALSE;
	
	// début vérification des variables
	$query = "SELECT etablissements.nom AS etblt, etablissements.id AS e_id, etablissements.cursus, secondaire, dept, cp, villes.nom AS commune, villes.id AS c_id, professeurs.*, matieres.nom AS matiere FROM professeurs, etablissements, villes, matieres WHERE professeurs.status = 'ok' AND etablissements.status = 'ok' AND professeurs.id = $p_id && etablissements.id = professeurs.etblt_id && villes.id = etablissements.ville_id && matieres.id = professeurs.matiere_id".((Admin::MOD_SCHOOL)?" && etablissements.moderated = 'yes'":"").((Admin::MOD_PROF)?" && professeurs.moderated = 'yes'":"");
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
		$nom = strtoupper($row["nom"]);
		$prenom = $row["prenom"];
		$sujet = $row["sujet"];
		$matiere = $row["matiere"];
		
		$erreur_var = FALSE;
		
		// cookies
		//session_start();
		
		if (isset($_SESSION["test_started"]))
		{
			if (@$_SESSION["test_nbre"] == @$_COOKIE["test_nbre"])
			{
				$erreur_cookies = FALSE;
				
				// vérification si déjà voté
				$voted = FALSE;
				if (isset($_COOKIE["votes"][$p_id]))
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
						$result = DBPal::query("SELECT id FROM notes WHERE ($where_n) && deleted = 0;");
						$rnb = $result->num_rows;
						
						if ($rnb)
						{
							$voted = TRUE;
						}
					}
				}
				// fin vérification si déjà voté
			}
			else
			{
				$erreur_cookies = TRUE;
			}
		}
		else
		{
			// on lance la procédure de test pour voir si le client accepte les cookies
			$test_nbre = mt_rand(0x10, 0xFFFFFFF); // PHP > 4.2.0
			
			$_SESSION["test_started"] = TRUE;
			$_SESSION["test_nbre"] = $test_nbre;
			// on enregistre le cookie, le décalage horaire est géré par HTTP
			// attention, le temps du cookie doit être supérieur au temps de la session
			setcookie("test_nbre", $test_nbre, time() + ini_get("session.gc_maxlifetime") + 24 * 3600, "/", Settings::COOKIE_DOMAIN);
			
			Web::redirect($_SERVER["SCRIPT_NAME"]."?prof_id=".rawurlencode($p_id));
		}
		// fin cookies
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
if (!$erreur_url && !$erreur_var && !$erreur_cookies && !$voted)
{
	$sent = @stripslashes($_POST["sent"]);
	
	if ($sent)
	{
		$interet = (int) @$_POST["interet"];
		$pedagogie = (int) @$_POST["pedagogie"];
		$connaissances = (int) @$_POST["connaissances"];
		$regularite = (int) @$_POST["regularite"];
		$ambiance = (int) @$_POST["ambiance"];
		$justesse = (int) @$_POST["justesse"];
		$comment = @$_POST["comment"];
		$statut = @$_POST["statut"];
		$extra = @$_POST["extra"];
		
		$echelle = array(1, 2, 3, 4, 5);
		
		if (!in_array($interet, $echelle))
			$notice["interet"] = "Aller, commence par évaluer l'<cite>Intérêt</cite> général des cours avec ton prof !";
		if (!in_array($pedagogie, $echelle))
			$notice["pedagogie"] = "Comment est la <cite>Pédagogie</cite> de ton professeur ?";
		if (!in_array($connaissances, $echelle))
			$notice["connaissances"] = "Dis-en sur les <cite>Connaissances</cite> de ton prof en sa matière !";
		if (!in_array($regularite, $echelle))
			$notice["regularite"] = "Tu n'as noté la <cite>Régularité</cite> de ton professeur !";
		if (!in_array($ambiance, $echelle))
			$notice["ambiance"] = "Quelle est l'<cite>Ambiance</cite> dans ses cours ?";
		if (!in_array($justesse, $echelle))
			$notice["justesse"] = "N'ai pas peur, dis-nous en sur la <cite>Justesse</cite> de sa notation ;)";
		if (strlen($comment) > Settings::COMMENT_MAX_LEN)
			$notice["comment"] = "Ton commentaire est trop long, il ne doit pas dépasser ".Settings::COMMENT_MAX_LEN." caractères (et oui ! c'est comme les <cite>SMS</cite>, c'est pas illimité !).";
		// vérification fantôme
		if (!in_array($statut, array("on", "off")))
			$notice["statut"] = "Statut incorrect.";
		if (!@$notice)
		{
			// enregistrement
			$query = "INSERT INTO notes (prof_id, date, pedagogie, interet, connaissances, regularite, ambiance, justesse, statut, extra, comment) VALUES ($p_id, NOW(), $pedagogie, $interet, $connaissances, $regularite, $ambiance, $justesse, '$statut', '".(($extra["pop"])?"pop,":"").(($extra["in"])?"in,":"")."', " . DBPal::quote($comment) . ")";
			$i_id = DBPal::insert($query);
			
			// log it
			App::log('Submitted Review', 'comment', $i_id, $user->uid);
			
			// assign moderation of the comment
			if (strlen($comment))
				App::queue('refresh-assignments', array('for-object', 'comment', $i_id));
			
			// enregistrement du cookie
			setcookie("votes[$p_id][$i_id]", 1, time() + 3600 * 24 * 30 * 4, "/", Settings::COOKIE_DOMAIN);
			
			// changement de page
			$_SESSION["msg"] = (strlen($comment))?"Ton évaluation a bien été prise en compte, ton commentaire apparaîtra dès qu'il aura été validé par un délégué.":"Ton évaluation a bien été prise en compte.";
			Web::redirect("/notes2/".rawurlencode($p_id)."/");
		}
		
	}
	else
		$notice = FALSE;
	
	// définition des paramètres par défaut
	if (!isset($statut)) $statut = "on";
}
// fin traitement formulaire

// Controller-View limit
DBPal::finish();

$title = "Note Ton Prof";
?>
<? require("tpl/haut.php"); ?>
<? if ($erreur_url) require("tpl/erreur_url.php"); else if ($erreur_var) require("tpl/erreur_var.php"); else if ($erreur_cookies) require("tpl/erreur_cookies.php"); else { ?>
		<div class="navi">
			<?=Helper::navPath(array(
				$cursus,
				Geo::$DEPT[$dept]['ind'],
				$dept,
				array($c_id, $cp, $c_nom),
				array($e_id, $e_nom),
				array($p_id, $prenom, $nom)
			))?>
			&gt; <?=htmlspecialchars($title)?>
		</div>
		<hr />
		<div>
			<h2 class="over"><?=htmlspecialchars($prenom)?> <span class="up"><?=htmlspecialchars($nom)?></span></h2>
			<div class="under"><?=htmlspecialchars($e_nom)?></div>
<? if ($cursus == E_2ND) { ?>
			<div class="major">Matière : <em><?=htmlspecialchars( $matiere )?></em></div>
<? } else { ?>
			<div class="major">Matière : <em><?=htmlspecialchars( ($sujet) ? "$sujet ($matiere)" : $matiere )?></em></div>
<? } ?>
<? if ($voted) { ?>
			<p>Tu as déjà noté ce professeur, tu pourras le noter de nouveau dans 4 mois à partir du moment où tu l'as noté.</p>
			<p><a href="profs/<?=urlencode($e_id)?>/">Retour à la page de ton établissement</a></p>
<? } else { ?>
			<p>Remplis ce formulaire la tête reposée et attentivement car tu n'aura pas la posibilité de modifier ton évaluation.</p>
			<p>Pense à consulter les <a href="regles">régles et critères</a> de notation.</p>
<? if ($notice) { ?>
			<div class="head-notice">
				<p>Attention ! Vérifie les données du formulaire :</p>
				<ul><? foreach ($notice as $alert) { ?><li><?=$alert?></li><? } ?></ul>
			</div>
<? } ?>
			<script type="text/javascript">
			<!--
			function limite(zone, max)
			{
				if(zone.value.length > max)
					zone.value = zone.value.substring(0, max);
			}
			-->
			</script>
			<form action="<?=$_SERVER["SCRIPT_NAME"]?>?prof_id=<?=urlencode($p_id)?><?=(SID) ? "&amp;".SID : ""?>" method="post" class="form">
				<input type="hidden" name="sent" value="1" />
				<dl>
					<dt>Critères d'évaluation</dt>
					<dd>
						<table class="note">
							<thead>
								<tr>
									<th></th>
									<th></th>
<? for ($i = 1; $i <= 5; $i++) { ?>
									<th><?=$i?></th>
<? } ?>
									<th></th>
								</tr>
							</thead>
							<tbody>
								<tr<?=(@$notice["interet"])?" class=\"notice\"":""?>>
									<th>Intérêt</th>
									<td class="etat etat-g">Nul <img src="img/smileys/evaluations/mediocre.png" class="smiley" alt="" /></td>
<? for ($i = 1; $i <= 5; $i++) { ?>
									<td class="note"><input type="radio" value="<?=$i?>" name="interet"<?=@($interet == $i)?" checked=\"checked\"":""?> /></td>
<? } ?>
									<td class="etat"><img src="img/smileys/evaluations/bon.png" class="smiley" alt="" /> Immense</td>
								</tr>
							</tbody>
							<tbody>
								<tr<?=(@$notice["pedagogie"])?" class=\"notice\"":""?>>
									<th>Pédagogie</th>
									<td class="etat etat-g">Mauvaise <img src="img/smileys/evaluations/mediocre.png" class="smiley" alt="" /></td>
<? for ($i = 1; $i <= 5; $i++) { ?>
									<td class="note"><input type="radio" value="<?=$i?>" name="pedagogie"<?=@($pedagogie == $i)?" checked=\"checked\"":""?> /></td>
<? } ?>
									<td class="etat"><img src="img/smileys/evaluations/bon.png" class="smiley" alt="" /> Excellente</td>
								</tr>
							</tbody>
							<tbody>
								<tr<?=(@$notice["connaissances"])?" class=\"notice\"":""?>>
									<th>Connaissances</th>
									<td class="etat etat-g">Imposteur <img src="img/smileys/evaluations/mediocre.png" class="smiley" alt="" /></td>
<? for ($i = 1; $i <= 5; $i++) { ?>
									<td class="note"><input type="radio" value="<?=$i?>" name="connaissances"<?=@($connaissances == $i)?" checked=\"checked\"":""?> /></td>
<? } ?>
									<td class="etat"><img src="img/smileys/evaluations/bon.png" class="smiley" alt="" /> Érudit</td>
								</tr>
							</tbody>
						</table>
						<p class="indic">Pour chacun de ces critères, <strong>1</strong> est la note la plus faible et <strong>5</strong> est la meilleure note.</p>
					</dd>
					<dt>Critères additionnels</dt>
					<dd>
						<table class="note">
							<tbody>
								<tr<?=(@$notice["regularite"])?" class=\"notice\"":""?>>
									<th><span class="abbr" title="Est-ce que ton prof est souvent absent ? Corrige-t-il rapidement tes devoirs ? As-tu terminé le programme avec ce prof ?">Régularité</span></th>
									<td class="etat etat-g">Pas terrible <img src="img/smileys/evaluations/mediocre.png" class="smiley" alt="" /></td>
<? for ($i = 1; $i <= 5; $i++) { ?>
									<td class="note"><input type="radio" value="<?=$i?>" name="regularite"<?=@($regularite == $i)?" checked=\"checked\"":""?> /></td>
<? } ?>
									<td class="etat"><img src="img/smileys/evaluations/bon.png" class="smiley" alt="" /> Pointue</td>
								</tr>
							</tbody>
							<tbody>
								<tr<?=(@$notice["ambiance"])?" class=\"notice\"":""?>>
									<th><span class="abbr" title="Ce critère n'est pas forcement en rapport avec l'autorité du professeur.">Ambiance</span></th>
									<td class="etat etat-g">Incontrôlée <img src="img/smileys/evaluations/cool.png" class="smiley" alt="" /></td>
<? for ($i = 1; $i <= 5; $i++) { ?>
									<td class="note"><input type="radio" value="<?=$i?>" name="ambiance"<?=@($ambiance == $i)?" checked=\"checked\"":""?> title="<?=Ratings::$AMB[$i]?>" /></td>
<? } ?>
									<td class="etat"><img src="img/smileys/evaluations/serieux.png" class="smiley" alt="" /> Tendue</td>
								</tr>
							</tbody>
														<tbody>
								<tr<?=(@$notice["justesse"])?" class=\"notice\"":""?>>
									<th><span class="abbr" title="Ce critère est relatif à la façon dont ton enseignant note tes devoirs.">Justesse</span></th>
									<td class="etat etat-g">Insouciante <img src="img/smileys/evaluations/cool.png" class="smiley" alt="" /></td>
<? for ($i = 1; $i <= 5; $i++) { ?>
									<td class="note"><input type="radio" value="<?=$i?>" name="justesse"<?=@($justesse == $i)?" checked=\"checked\"":""?> title="<?=Ratings::$JUST[$i]?>" /></td>
<? } ?>
									<td class="etat"><img src="img/smileys/evaluations/serieux.png" class="smiley" alt="" /> Rude</td>
								</tr>
							</tbody>
						</table>
						<p class="facultatif etoile">Ne rentre pas en compte dans la note globale de ton professeur.</p>
						<p class="indic">Pour les deux derniers critères, la meilleure évaluation se trouve au centre.</p>
					</dd>
					<dt>Commentaire</dt>
					<dd<?=(@$notice["comment"])?" class=\"notice\"":""?>>
						<textarea style="background-color: #DFECFF;" rows="3" cols="66" name="comment" onkeydown="limite(this, <?=Settings::COMMENT_MAX_LEN?>);" onkeyup="limite(this, <?=Settings::COMMENT_MAX_LEN?>);"><?=@htmlspecialchars($comment)?></textarea>
						<p class="facultatif etoile">Champ facultatif, <?=Settings::COMMENT_MAX_LEN?> caractères maximum.</p>
						<div class="indic longue">Aie des propos constructifs et en relation avec l'efficacité de ton prof en classe. Justifie l'évaluation que tu lui as laissée. Il est interdit de signer d'une quelconque façon ce commentaire. Tout commentaire ne respectant pas les <a href="regles#comment">règles</a> sera effacé.</div>
					</dd>
					<dt>Informations</dt>
					<dd class="bg">
						<fieldset<?=(@$notice["statut"])?" class=\"notice\"":""?>>
							<legend>Statut</legend>
							<label for="on">
								<input type="radio" value="on" name="statut" id="on"<?=@($statut == "on")?" checked=\"checked\"":""?> />
								Enseigne actuellement
							</label>
							<label for="off">
								<input type="radio" value="off" name="statut" id="off"<?=@($statut == "off")?" checked=\"checked\"":""?> />
								Parti(e) / En retraite
							</label>
						</fieldset>
						<fieldset>
							<legend>Extras</legend>
							<label for="extra[pop]">
								<input type="checkbox" value="1" name="extra[pop]" id="extra[pop]"<?=@($extra["pop"])?" checked=\"checked\"":""?> />
								Populaire <img src="img/smileys/evaluations/pop.png" class="smiley" alt="" />
							</label>
							<label for="extra[in]">
								<input type="checkbox" value="1" name="extra[in]" id="extra[in]"<?=@($extra["in"])?" checked=\"checked\"":""?> />
								Stylé(e)
							</label>
						</fieldset>
					</dd>
					<dt>Validation</dt>
					<dd><input type="submit" value="Terminer" /></dd>
				</dl>
			</form>
<? } ?>
		</div>
<? } ?>
<? require("tpl/bas.php"); ?>
