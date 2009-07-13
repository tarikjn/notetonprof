<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");


$title = "Inscription délégué";
?>
<? require("tpl/haut.php"); ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<h2>Inscription délégué</h2>
			<p>Avant de pouvoir être délégué, tu dois pleinement accepter les conditions de modération ci-dessous :</p>
			
			<form action="inscription2" class="form">
				<dl>
					<dt class="warn">Avertissement !</dt>
					<dd class="warn">
						<p>
							L'activité des délégués est régulièrement contrôlée, ceux qui valident délibérément des commentaires insultants ou diffamatoires auront leur compte supprimé et peuvent être poursuivis.
						</p>	
						<p>
							Il est inutile également de s'inscrire pour donner une fausse identité, ce n'est pas le genre de comportement attendu lorsque l'on est censé occuper une position de responsabilité.<br />
							Nous pouvons retrouver la véritable identité d'un délégué ainsi que sa position géographique grâce à l'adresse IP enregistrée sur notre serveur.
						</p>
						<p>
							Note : ton anonymat sur le site est conservé, même lorsque tu es délégué (ton identité ne sera jamais affichée sur le site).
						</p>
					</dd>
					<dt>Conditions de modération</dt>
					<dd>
						<h3>Préambule</h3>
						<p>
							La modération des commentaires doit être faite en respectant scrupuleusement les <a href="regles#comment">règles</a>, sans ne faire aucune exception.
						</p>
						<h3>Paragraphe 1</h3>
						<p>
							L'âge minimum requis pour être délégué, et par cette occasion modérer les commentaires laissés par les autres étudiants, est de 15 ans.
							Âge à partir duquel nous considérons que tu es suffisamment mûr pour être responsable des commentaires acceptés.
							Tu ne peux pas t'inscrire si tu as moins de 15 ans.
						</p>
						<h3>Paragraphe 2</h3>
						<p>
							Tout commentaire que tu acceptes engage ta responsabilité partielle de publication, dans la mesure où tu es en pouvoir de le publier ou non.<br />
							Dans le cas où les commentaires sont modérés après publication, tu engage ta responsabilité partielle de publication sur les commentaires que tu valides.
						</p>
						<h3>Paragraphe 3</h3>
						<p>
							En cas de doute sur un commentaire, tu peux demander à l'équipe de noteTonProf.fr de le modérer pour toi,
							ta responsabilité dans sa publication n'est alors nullement engagée.
						</p>
						<h3>Paragraphe 4</h3>
						<p>
							La validation de cette page par le bouton « J'accepte » présuppose acceptation totale des conditions précédentes.
						</p>
					</dd>
					<dt>Acceptation</dt>
					<dd style="text-align: center;"><input type="submit" value="J'accepte" /></dd>
				</dl>
			</form>
		</div>
<? require("tpl/bas.php"); ?>
