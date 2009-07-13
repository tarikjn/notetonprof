<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

$show_stats = 1;
$title = "FAQ";
?>
<? require("tpl/haut.php"); ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<h2>Foire Aux Questions</h2>
			<dl class="faq">
				<dt>Comment calculez-vous la moyenne d'un prof ?</dt>
				<dd>
					Nous faisons la moyenne des trois critères principaux : Intérêt, Pédagogie et Connaissances. Nous pondérons ces 3 critères de la même façon.<br />
					Ces critères nous semblent essentiels pour exprimer les qualités d'un professeur qui jouent sur la motivation et la réussite des élèves.
				</dd>
				<dt>Comment signaler un commentaire qui ne devrait pas apparaître ?</dt>
				<dd>
					Parmi le grand nombre de commentaires qui sont validés chaque jour, il arrive malheureusement que certains commentaires passent à travers le filet.<br />
					Si tu trouves un commentaire qui ne respecte pas les <a href="regles#comment">règles</a>, tu peux nous le signaler en cliquant sur le point d'exclamation rouge à gauche du commentaire.
				</dd>
				<dt>Ce site n'est-t-il pas principalement utilisé par les élèves pour donner de mauvaises notes à leurs profs ?</dt>
				<dd>
					Absolument pas, nous avons remarqué que la plupart des élèves qui viennent sur ce site laissent de bonnes évaluations aux profs.<br />
					En effet, près de 65% des évaluations sont positives !
				</dd>
				<dt>À quoi sert-il d'être délégué ?</dt>
				<dd>
					La fonction principale du délégué est de valider les commentaires ajoutés.<br />
					Le délégué corrige également les erreurs qui peuvent être faites lors de l'ajout d'un établissement ou d'un professeur.<br />
					Il peut y avoir plusieurs délégués pour un même établissement, en fonction du nombre de professeurs, ce qui permet d'accélérer les validations.<br />
					C'est grâce au travail de chaque délégué que les informations peuvent être correctes et les commentaires filtrés.
				</dd>
				<dt>Est-ce normal qu'il y ait des professeurs référencés dans un établissement alors qu'ils l'ont quitté ?</dt>
				<dd>
					Oui. Les professeurs peuvent être référencés dans tous les établissements où ils ont enseignés. Ainsi ils ont des appréciations venant de différents élèves, anciens comme nouveaux. Cela permet d'enrichir les évaluations.
				</dd>
				<dt>Référencez-vous les lycées professionnels ?</dt>
				<dd>
					Nous ne référençons pas les lycées professionnels pour le moment. Tu t'en rendras compte si tu veux ajouter un prof : seules les matières générales sont disponibles.<br />
					Néanmoins nous avons prévu de bientôt permettre le référencement des lycées professionnels.
				</dd>
			</dl>
		</div>
<? require("tpl/bas.php"); ?>
