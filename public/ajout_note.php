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
						$result = DBPal::query("SELECT id FROM notes WHERE ($where_n) && status = 'ok'");
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
	if ($_SERVER["REQUEST_METHOD"] == 'POST')
	{
		$comment = @$_POST["comment"];
		$statut = @$_POST["statut"];
		
		$extra = array();
		if (@$_POST['extra']['pop'])
			$extra[] = 'pop';
		if (@$_POST['extra']['in'])
			$extra[] = 'in';
		$extra_q = implode(",", $extra);
		
		$criterias = array_keys(Ratings::$CRITERIAS);
		$scale = array(1, 2, 3, 4, 5);
		
		$grades = array();
		foreach ($criterias as $criteria)
		{
			if (!in_array(@$_POST[$criteria], $scale))
				$error[$criteria] = "Sélectionne une note pour ce critère !";
			else
				$grades[$criteria] = @$_POST[$criteria];
		}
		if (strlen($comment) > Settings::COMMENT_MAX_LEN)
			$error["comment"] = "Commentaire trop long, ".Settings::COMMENT_MAX_LEN." caractères max !";
		// vérification fantôme
		if (!in_array($statut, array("on", "off")))
			$error["statut"] = "Statut incorrect !";
		
		// check notification E-mail
		if (@$_POST['get_update_notification'] == 'yes')
		{
			if (Mail::isValidEmail(@$_POST['update_notification_email']))
			{
				$subscribe_to_updates = true;
				$update_email = $_POST['update_notification_email'];
			}
			else
				$error['update_notification_email'] = "Format d'adresse E-mail incorrect !";
		}
		
		// check the reCAPTCHA
		Web::checkReCaptcha($user, $error);
		
		if (!@$error)
		{
			$insert = array(
			    'prof_id' => $p_id,
			  );
			// enregistrement
			$query = "INSERT INTO notes"
			       . " (prof_id, date, " . implode(",", array_keys($grades)) . ", statut, extra, comment)"
			       . " VALUES"
			       . " ($p_id, NOW(), " . implode(",", array_values($grades)) . ", '$statut', '$extra_q', " . DBPal::quote($comment) . ")";
			$i_id = DBPal::insert($query);
			
			// log it
			App::log('Submitted Review', 'comment', $i_id, $user->uid);
			
			// assign moderation of the comment
			if (strlen($comment))
				App::queue('refresh-assignments', array('for-object', 'comment', $i_id));
			
			// saving cookie: won't be able to rate teacher again for 1 year
			setcookie("votes[$p_id][$i_id]", 1, time() + 3600 * 24 * 365, "/", Settings::COOKIE_DOMAIN);
			
			// TODO: log
			// insert update notification
			if (@$subscribe_to_updates)
			{
				$update_set = array(
				    'rating_id' => $i_id,
				    'email' => $update_email,
				    'user_id' => $user->uid
				  );
				
				DBPal::insert(
				    "INSERT INTO update_notifications " . DBPal::arr2values($update_set)
				  );
			}
			
			// update notification cookie
			setcookie("update_notification[get]", @$_POST['get_update_notification'], time() + 3600 * 24 * 365, "/", Settings::COOKIE_DOMAIN);
			setcookie("update_notification[email]", @$_POST['update_notification_email'], time() + 3600 * 24 * 365, "/", Settings::COOKIE_DOMAIN);
			
			// changement de page
			$_SESSION["msg"] = (strlen($comment))?"Ton évaluation a bien été prise en compte, ton commentaire apparaîtra dès qu'il aura été validé par un délégué.":"Ton évaluation a bien été prise en compte.";
			Web::redirect("/notes2/".rawurlencode($p_id)."/");
		}
		else
		{
			// posted data replaces defaults
			$defaults = $_POST;
		}
	}
	else
	{
		$error = FALSE;
		
		// set default values
		$statut = "on";
		$defaults = array();
		$defaults['get_update_notification'] = (@$_COOKIE["update_notification"]['get'])? 'yes' : 'no';
		$defaults['update_notification_email'] = @$_COOKIE["update_notification"]['email'] or '';
	}
}
// fin traitement formulaire

// Controller-View limit
DBPal::finish();

$title = "Noter";
$yui_mode = true;
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
			<p>Tu as déjà noté ce professeur, tu ne peux noter un professeur qu'une fois.</p>
			<p><a href="profs/<?=urlencode($e_id)?>/">Retour à la page de ton établissement</a></p>
<? } else { ?>
			<p class="information">
				Remplis ce formulaire la tête reposée et attentivement car tu n'aura pas la possibilité de modifier ton évaluation.<br />
				Pense à consulter les <a href="regles">régles</a> de notation.
			</p>
			<?=Helper::getErrorHeader($error)?>
			<form action="<?=$_SERVER["REQUEST_URI"]?>" method="post" class="form rating-form">
				<dl>
					<dt>Commentaire</dt>
					<dd>
						<?=Helper::getFormError('comment', $error)?>
						<div class="cc-padding-compensater">
							<div class="field-tip"><span class="maxlen-counter"><?=Settings::COMMENT_MAX_LEN?></span> caractères restants</div>
							<div class="bubble-tip">
								<div class="bt-body">Ton commentaire doit justifier l'évaluation que tu laisses à ton prof et doit uniquement être en relation avec ses cours. Écris et ortographie ton commentaire correctement, c'est à dire PAS DE LANGAGE SMS. Il est INTERDIT DE SIGNER d'une quelconque façon ce commentaire. Tout commentaire ne respectant pas les <a href="regles#comment">règles</a> sera effacé.</div>
								<div class="bt-foot"></div>
							</div>
							<textarea class="comment-field maxlen-field autoexpand" rows="4" name="comment"><?=h(@$comment)?></textarea>
						</div>
					</dd>
					<dt>Note</dt>
					<dd class="widgets">
						<fieldset>
							<legend>Critères principaux</legend>
							<?=Helper::getFormError('interest', $error)?>
							<div class="cc-rating-widget">
								<div class="cc-control">
									<div class="cc-slider-left">Pas du tout <img src="img/smileys/evaluations/mediocre.png" class="smiley" alt="" /></div>
									<div class="cc-slider-control leveled-slider"><?=Helper::radioSlider('interest', 1, 5, 1, @$grades)?></div>
									<div class="cc-slider-right"><img src="img/smileys/evaluations/bon.png" class="smiley" alt="" /> Très motivant</div>
								</div>
								<div class="cc-label cc-tooltip" title="<?=Ratings::$CRITERIAS['interest']['desc']?>"><?=Ratings::$CRITERIAS['interest']['title']?></div>
							</div>
							<?=Helper::getFormError('clarity', $error)?>
							<div class="cc-rating-widget even">
								<div class="cc-control">
									<div class="cc-slider-left">Pas du tout <img src="img/smileys/evaluations/mediocre.png" class="smiley" alt="" /></div>
									<div class="cc-slider-control leveled-slider"><?=Helper::radioSlider('clarity', 1, 5, 1, @$grades)?></div>
									<div class="cc-slider-right"><img src="img/smileys/evaluations/bon.png" class="smiley" alt="" /> Excellente pédagogie</div>
								</div>
								<div class="cc-label cc-tooltip" title="<?=Ratings::$CRITERIAS['clarity']['desc']?>"><?=Ratings::$CRITERIAS['clarity']['title']?></div>
							</div>
							<?=Helper::getFormError('knowledgeable', $error)?>
							<div class="cc-rating-widget">
								<div class="cc-control">
									<div class="cc-slider-left">Pas du tout <img src="img/smileys/evaluations/mediocre.png" class="smiley" alt="" /></div>
									<div class="cc-slider-control leveled-slider"><?=Helper::radioSlider('knowledgeable', 1, 5, 1, @$grades)?></div>
									<div class="cc-slider-right"><img src="img/smileys/evaluations/bon.png" class="smiley" alt="" /> Très connaisseur</div>
								</div>
								<div class="cc-label cc-tooltip" title="<?=Ratings::$CRITERIAS['knowledgeable']['desc']?>"><?=Ratings::$CRITERIAS['knowledgeable']['title']?></div>
							</div>
							<?=Helper::getFormError('fairness', $error)?>
							<div class="cc-rating-widget even">
								<div class="cc-control">
									<div class="cc-slider-left">Pas du tout <img src="img/smileys/evaluations/mediocre.png" class="smiley" alt="" /></div>
									<div class="cc-slider-control leveled-slider"><?=Helper::radioSlider('fairness', 1, 5, 1, @$grades)?></div>
									<div class="cc-slider-right"><img src="img/smileys/evaluations/bon.png" class="smiley" alt="" /> Très juste</div>
								</div>
								<div class="cc-label cc-tooltip" title="<?=Ratings::$CRITERIAS['fairness']['desc']?>"><?=Ratings::$CRITERIAS['fairness']['title']?></div>
							</div>
						</fieldset>
						<fieldset>
							<legend title="Ces critères ne rentrent pas en compte dans la moyenne de ton prof" class="cc-tooltip">Critères additionnels</legend>
							<?=Helper::getFormError('regularity', $error)?>
							<div class="cc-rating-widget">
								<div class="cc-control">
									<div class="cc-slider-left">Pas du tout <img src="img/smileys/evaluations/mediocre.png" class="smiley" alt="" /></div>
									<div class="cc-slider-control leveled-slider"><?=Helper::radioSlider('regularity', 1, 5, 1, @$grades)?></div>
									<div class="cc-slider-right"><img src="img/smileys/evaluations/bon.png" class="smiley" alt="" /> Très régulier</div>
								</div>
								<div class="cc-label cc-tooltip" title="<?=Ratings::$CRITERIAS['regularity']['desc']?>"><?=Ratings::$CRITERIAS['regularity']['title']?></div>
							</div>
							<?=Helper::getFormError('availability', $error)?>
							<div class="cc-rating-widget even">
								<div class="cc-control">
									<div class="cc-slider-left">Pas du tout <img src="img/smileys/evaluations/mediocre.png" class="smiley" alt="" /></div>
									<div class="cc-slider-control leveled-slider"><?=Helper::radioSlider('availability', 1, 5, 1, @$grades)?></div>
									<div class="cc-slider-right"><img src="img/smileys/evaluations/bon.png" class="smiley" alt="" /> Très disponible</div>
								</div>
								<div class="cc-label cc-tooltip" title="<?=Ratings::$CRITERIAS['availability']['desc']?>"><?=Ratings::$CRITERIAS['availability']['title']?></div>
							</div>
							<div class="bubble-tip for-widget">
								<div class="bt-body">La meilleure note se trouve au CENTRE.</div>
								<div class="bt-foot"></div>
							</div>
							<?=Helper::getFormError('difficulty', $error)?>
							<div class="cc-rating-widget">
								<div class="cc-control">
									<div class="cc-slider-left">Trop facile <img src="img/smileys/evaluations/cool.png" class="smiley" alt="" /></div>
									<div class="cc-slider-control centered-slider"><?=Helper::radioSlider('difficulty', 1, 5, 1, $defaults, true)?></div>
									<div class="cc-slider-right"><img src="img/smileys/evaluations/serieux.png" class="smiley" alt="" /> Trop difficile</div>
								</div>
								<div class="cc-label cc-tooltip" title="<?=Ratings::$CRITERIAS['difficulty']['desc']?>"><?=Ratings::$CRITERIAS['difficulty']['title']?></div>
							</div>
							<div class="bubble-tip for-widget">
								<div class="bt-body">La meilleure note se trouve au CENTRE.</div>
								<div class="bt-foot"></div>
							</div>
							<?=Helper::getFormError('atmosphere', $error)?>
							<div class="cc-rating-widget even">
								<div class="cc-control">
									<div class="cc-slider-left">Incontrôlée <img src="img/smileys/evaluations/cool.png" class="smiley" alt="" /></div>
									<div class="cc-slider-control centered-slider"><?=Helper::radioSlider('atmosphere', 1, 5, 1, $defaults, true)?></div>
									<div class="cc-slider-right"><img src="img/smileys/evaluations/serieux.png" class="smiley" alt="" /> Tendue</div>
								</div>
								<div class="cc-label cc-tooltip" title="<?=Ratings::$CRITERIAS['atmosphere']['desc']?>"><?=Ratings::$CRITERIAS['atmosphere']['title']?></div>
							</div>
						</fieldset>
					</dd>
					<dt>Infos complémentaires</dt>
					<dd class="bg">
						<?=Helper::getFormError('statut', $error)?>
						<fieldset class="cc-compact left50">
							<legend>Statut</legend>
							<label>
								<input type="radio" value="on" name="statut"<?=@($statut == "on")?" checked=\"checked\"":""?> />
								Enseigne actuellement
							</label>
							<label>
								<input type="radio" value="off" name="statut"<?=@($statut == "off")?" checked=\"checked\"":""?> />
								Parti(e) / En retraite
							</label>
						</fieldset>
						<fieldset class="cc-compact right50">
							<legend>Extras</legend>
							<label>
								<input type="checkbox" value="1" name="extra[pop]"<?=(@$extra["pop"])?" checked=\"checked\"":""?> />
								Populaire <img src="img/smileys/evaluations/pop.png" class="smiley" alt="" />
							</label>
							<label>
								<input type="checkbox" value="1" name="extra[in]"<?=(@$extra["in"])?" checked=\"checked\"":""?> />
								Stylé(e)
							</label>
						</fieldset>
					</dd>
					<dt>Validation</dt>
					<dd>
						<fieldset class="cc-smaller">
							<legend>Mise à jour</legend>
							<label>
								<?=Helper::formFieldCheck('get_update_notification', 'yes', $defaults, true)?>
								<span class="cc-tooltip" title="Ton choix est automatiquement mémorisé, tu peux le changer à tout moment.">M'informer lorsque ce prof reçoit une nouvelle note</span>
							</label>
							<div id="update_notification_email">
								<?=Helper::getFormError('update_notification_email', $error)?>
								<div class="bubble-tip">
									<div class="bt-body">L'adresse E-mail fournie sert UNIQUEMENT pour l'envoi des notifications, l'équipe de NoteTonProf.com n'y a pas accès, et en aucun cas elle ne pourra être utilisée pour t'identifier. Tu peux te désinscire facilement à partir d'un lien qui se trouve dans l'email envoyé.</div>
									<div class="bt-foot"></div>
								</div>
								<label>E-mail : <?=Helper::formFieldInput('update_notification_email', $defaults)?></label>
							</div>
						</fieldset>
						<?=Web::getReCaptcha($user, 'plain', $error)?>
						<div class="cc-submit">
							<input type="submit" value="Confirmer" />
						</div>
					</dd>
				</dl>
			</form>
<? } ?>
		</div>
<? } ?>
<? require("tpl/bas.php"); ?>
