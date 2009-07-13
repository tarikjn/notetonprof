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
			<h3>Évaluation</h3>
			<p>Pour pouvoir noter un professeur, il faut qu'il ait ou ait eu un rôle dans ton éducation.</p>
			<p>Tu peux laisser une évaluation tous les 4 mois pour le même professeur, afin d'exprimer à nouveau ton avis à différentes périodes de l'année.</p>
			<p>Une fois que tu as laissé une évaluation, il t'est impossible de la modifier ou de la supprimer.</p>
			<h3>Échelle</h3>
			<p>Nous te demandons de noter jusqu'à quel point <strong>tu</strong> aimes la façon d'enseigner de ton professeur.</p>
			<p>Indications :</p>
			<ul class="soft-list">
				<li>1/5 : « Je n'aime pas du tout »</li>
				<li>2/5 : « Je n'aime pas trop »</li>
				<li>3/5 : « Je suis mitigé »</li>
				<li>4/5 : « J'aime bien »</li>
				<li>5/5 : « J'adore ! »</li>
			</ul>
			<h3>Critères principaux</h3>
			<h4>Intérêt</h4>
			<p>
				Pour résumer, ce critère répond à la question suivante : « Lorsque tu sors des cours de ton prof, as-tu l'impression d'avoir appris des choses utiles ? »<br />
			</p>
			<h4>Pédagogie</h4>
			<p>
				Ce critère exprime la facilité avec laquelle ton professeur parvient à te faire comprendre le cours et à te motiver pour le travailler.
			</p>
			<h4>Connaissances</h4>
			<p>
				« Lorsque tu pose des question à ton prof, te répond-t-il facilement ? », « As-tu l'impression que ton professeur sait beaucoup de choses ? ».<br />
				Ce dernier point est bien entendu en rapport avec la matière de ton enseignant.
			</p>
			<h2><a name="comment">Commentaires</a></h2>
			<p>
				Ton commentaire doit être utile, constructif, il doit avant tout être en rapport avec l'enseignement et le déroulement des cours avec ton professeur.<br />
				Attention ! Tout comme pour l'évaluation, il t'est impossible de modifier ou de supprimer ton commentaire. Réfléchis donc bien et relis-toi avant de le poster.
			</p>
			<p><strong>Tout commentaire contenant&hellip;</strong></p>
			<ul class="soft-list">
				<li>une signature explicite ou implicite, une adresse de contact, le nom ou les initiales d'un autre élève</li>
				<li>injures, vulgarités, violences</li>
				<li>des informations sur l'apparence physique du professeur, sa vie personnelle, religieuse ou familiale</li>
				<li>diffamations de toute sorte</li>
				<li>des propos désobligeants ou rudes</li>
				<li>publicité ou adresse de site ou écrit tout en MAJUSCULES</li>
			</ul>
			<p>&hellip; sera effacé.</p>
			<p><strong>Tout commentaire&hellip;</strong></p>
			<ul class="soft-list">
				<li>menaçant un prof, un élève, l'administration ou l'établissement</li>
				<li>faisant état d'une intention de lui faire du mal</li>
			</ul>
			<p>&hellip; sera transmis aux autorités compétentes avec ton adresse IP.</p>
		</div>
<? require("tpl/bas.php"); ?>
