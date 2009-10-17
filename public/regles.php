<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

$show_stats = 1;
$title = "Règles de notation";
?>
<? require("tpl/haut.php"); ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<h2>Règles de notation</h2>
			<p><strong>En utilisant ce site, tu acceptes de noter et de commenter <em>uniquement</em> tes professeurs, administrateurs, conseillers ou autres professionnels qui affectent ton éducation. Tu ne peux évaluer un professeur (ou professionnel) qu'une seule fois.</strong></p>
			<p>Une fois que tu as laissé une évaluation, il t'es impossible de la modifier ou de la supprimer.</p>
			<h3><a name="comment">Commentaire</a></h3>
			<p>
				Ton commentaire doit être utile, constructif, il doit avant tout être en rapport avec l'enseignement et le déroulement des cours avec ton professeur.<br />
				Attention ! Tout comme pour l'évaluation, il t'est impossible de modifier ou de supprimer ton commentaire. Réfléchis donc bien et relis-toi avant de le poster.
			</p>
			<p><strong>Tout commentaire&hellip;</strong></p>
			<ul class="soft-list">
				<li>contenant des mots ou un langage vulgaire</li>
				<li>de nature sexuelle (sexy, bonne, etc.)</li>
				<li>en relation avec l'apparence physique (grande, gros, mignon, odeur, vêtements, etc.)</li>
				<li>en relation avec un handicap physique (bégaie, boîte, etc.)</li>
				<li>d'une nature insultante (naze, idiot, etc.)</li>
				<li>faisant référence à l'état mental, à l'alcool ou à l'utilisation de drogues ou de médicaments</li>
				<li>faisant référence à des problèmes avec la loi</li>
				<li>faisant référence à la nationalité, religion, ethnicité, orientation sexuelle, âge</li>
				<li>comprenant le nom ou les initiales de l'auteur ou d'un autre élève, une adresse ou un numéro de téléphone</li>
				<li>faisant référence à la vie personnelle ou familiale du professeur (viens de se marier, fille charmante, plein aux as, etc.)</li>
				<li>comprenant une diffamation évidente</li>
				<li>incompréhensible, en langage SMS, ou truffé de fautes manifestement volontaires</li>
				<li>contenant une publicité, adresse de site ou écrit tout en MAJUSCULES</li>
			</ul>
			<p>&hellip; sera effacé.</p>
			<p><strong>Tout commentaire&hellip;</strong></p>
			<ul class="soft-list">
				<li>menaçant un prof, un élève, l'administration ou l'établissement</li>
				<li>faisant état d'une intention de lui faire du mal</li>
			</ul>
			<p>&hellip; sera transmis aux autorités compétentes avec ton adresse IP.</p>
			<h3>Critères de notation</h3>
			<h4>Critères principaux</h4>
			<p>Ce sont les critères qui rentrent en compte dans la moyenne de ton professeur. Pour chaque critère, nous te demandons de donner une note de 1 à 5, 1 étant le pire, et 5 le meilleur, correspondant à ton avis personnel.</p>
			<h5><?=Ratings::$CRITERIAS['interest']['title']?></h5>
			<p><?=Ratings::$CRITERIAS['interest']['desc']?></p>
			
			<h5><?=Ratings::$CRITERIAS['clarity']['title']?></h5>
			<p><?=Ratings::$CRITERIAS['clarity']['desc']?></p>
			
			<h5><?=Ratings::$CRITERIAS['knowledgeable']['title']?></h5>
			<p><?=Ratings::$CRITERIAS['knowledgeable']['desc']?></p>
			
			<h5><?=Ratings::$CRITERIAS['fairness']['title']?></h5>
			<p><?=Ratings::$CRITERIAS['fairness']['desc']?></p>
			<h4>Critères additionnels</h4>
			<h5><?=Ratings::$CRITERIAS['regularity']['title']?></h5>
			<p><?=Ratings::$CRITERIAS['regularity']['desc']?></p>
			
			<h5><?=Ratings::$CRITERIAS['availability']['title']?></h5>
			<p><?=Ratings::$CRITERIAS['availability']['desc']?></p>
			
			<h5><?=Ratings::$CRITERIAS['difficulty']['title']?></h5>
			<p><?=Ratings::$CRITERIAS['difficulty']['desc']?></p>
			
			<h5><?=Ratings::$CRITERIAS['atmosphere']['title']?></h5>
			<p><?=Ratings::$CRITERIAS['atmosphere']['desc']?></p>
			<p style="margin-top: 2.5em;"><strong>En notant tes professeurs sur ce site, tu acceptes les <a href="legal#terms">Conditions d'Utilisation du Site.</a></strong></p>
		</div>
<? require("tpl/bas.php"); ?>
