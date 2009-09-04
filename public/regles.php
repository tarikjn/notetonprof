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
			<p><strong>En utilisant ce site, tu acceptes de noter et de commenter <em>uniquement</em> tes professeurs, administrateurs, conseillers ou autres profesionnels qui affectent ton éducation. Tu ne peux évaluer un professeur (ou autre) qu'une seule fois.</strong></p>
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
				<li>une diffamation évidente</li>
				<li>incompréhensible</li>
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
			<p>Seuls les critères suivants rentrent en compte dans la note de ton professeur. Pour chaque critère, nous te demandons de donner une note de 1 à 5, 1 étant le pire, et 5 le meilleur, correspondant à ton avis personnel.</p>
			<h4>Intérêt</h4>
			<p>
				Ce critère répond à la question suivante : « Lorsque tu sors des cours de ton prof, as-tu l'impression d'avoir appris des choses utiles et d'avoir progressé dans la matière ? ». C'est le critère principal de notation.<br />
			</p>
			<h4>Pédagogie</h4>
			<p>
				Ce critère exprime la facilité avec laquelle ton professeur parvient à te faire comprendre le cours et à te motiver pour le travailler.
			</p>
			<h4>Connaissances</h4>
			<p>
				« Lorsque tu pose des questions à ton prof, te répond-t-il de façon utile et développée ? », « Ton professeur développe-t-il les cours au-delà du programme, ou as-t-il une connaissance appronfondie en sa matière ? ».<br />
			</p>
			<p style="margin-top: 2.5em;"><strong>En notant tes professeurs sur ce site, tu acceptes les <a href="legal#terms">Conditions d'Utilisation du Site.</a></strong></p>
		</div>
<? require("tpl/bas.php"); ?>
