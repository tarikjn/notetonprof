<?
define("ROOT_DIR", "../");
require(ROOT_DIR."_ini.php");

$user->requireOrRedirect();

// Controller-View limit
DBPal::finish();

$title = "Assistance";
?>
<? require("tpl/haut.php"); ?>
		<div class="navi"><a href=".">Accueil</a> &gt; <a href="/admin/index">Espace Délégué</a> &gt; <?=htmlspecialchars($title)?></div>
		<hr />
		<div>
			<h2><?=htmlspecialchars($title)?></h2>
			<h3>Sommaire</h3>
			<ul>
				<li><a href="/admin/help#ranks">Pouvoirs des modérateurs</a></li>
				<li><a href="/admin/help#data">Données</a></li>
				<ul>
					<li><a href="/admin/help#data-moderate">Modération</a></li>
					<li><a href="/admin/help#data-solve">Résolution des signalement</a></li>
				</ul>
			</ul>
			<h3><a name="ranks">Pouvoirs des modérateurs</a></h3>
			<table class="rank-table">
				<thead>
					<tr>
						<th colspan="2">Niveau 1. <span class="rank-img rank-1"></span> <?=Admin::$RANKS[1]?></th>
					</tr>
				</thead>
				<tbody>
					<tr class="promotion">
						<th>Promotion</th>
						<td>
							<ol>
								<li>Acceptation des règles de modération générales</li>
								<li>Inscription</li>
								<li>Validation de l'addresse E-mail</li>
							</ol>
						</td>
					</tr>
					<tr class="powers">
						<th>Pouvoirs</th>
						<td>
						<ul>
							<li>Champ d'action : 2 établissements</li>
							<li>Peut pré-modérer les commentaires</li>
						</ul>
						</td>
					</tr>
				<? if ($user->power == 5) { ?>
					<tr class="controls">
						<th>Contrôles</th>
						<td>
							<ul>
								<li>[Auto] Bloquage après plus de 3 changements d'établissement en 1 semaine</li>
								<li>[Auto] Bloquage après plus de 60 actions en 1 minute</li>
								<li>[Auto] Bloquage si plus de 5% des commentaires modérés en 1 semaine sont contestés (50 commentaires minimum)</li>
							</ul>
						</td>
					</tr>
				<? } ?>
				</tbody>
				<thead>
					<tr>
						<th colspan="2">Niveau 2. <span class="rank-img rank-2"></span> <?=Admin::$RANKS[2]?></th>
					</tr>
				</thead>
				<tbody>
					<tr class="promotion">
						<th>Promotion</th>
						<td>
							<ol>
								<li>Être <?=Admin::$RANKS[1]?></li>
								<li>[Auto] Avoir 1 semaine ET accepter 25 commentaires avec moins de 2% de contestation</li>
							</ol>
						</td>
					</tr>
					<tr class="powers">
						<th>Pouvoirs</th>
						<td>
						<ul>
							<li>Champ d'action : 5 établissements</li>
							<li>Peut modérer les commentaires</li>
							<li>Peut modifier les informations des établissements et des professeurs</li>
						</ul>
						</td>
					</tr>
				<? if ($user->power == 5) { ?>
					<tr class="controls">
						<th>Contrôles</th>
						<td>
							<ul>
								<li>[Auto] Contrôles de Niveau 1</li>
								<li>Surveillance des modifications faites sur les informations des établissements et des professeurs par les modérateurs de niveau 4-5</li>
							</ul>
						</td>
					</tr>
				<? } ?>
				</tbody>
				<thead>
					<tr>
						<th colspan="2">Niveau 3. <span class="rank-img rank-3"></span> <?=Admin::$RANKS[3]?></th>
					</tr>
				</thead>
				<tbody>
					<tr class="promotion">
						<th>Promotion</th>
						<td>
							<ol>
								<li>[Auto] Avoir été <?=Admin::$RANKS[2]?> pendant 3 mois</li>
								<li>Un <?=Admin::$RANKS[5]?> doit approuver qu'il n'y a eu qu'un minimum d'incidents</li>
								<li>Doit fournir un numéro de téléphone de contact</li>
								<li>Doit accepter un nouveau contrat</li>
							</ol>
						</td>
					</tr>
					<tr class="powers">
						<th>Pouvoirs</th>
						<td>
						<ul>
							<li>Champ d'action : tout les établissements</li>
							<li>Pouvoirs de Niveau 2</li>
							<li>Peut modérer les commentaires pré-modérés</li>
						</ul>
						</td>
					</tr>
				<? if ($user->power == 5) { ?>
					<tr class="controls">
						<th>Contrôles</th>
						<td>
							<ul>
								<li>[Auto] Contrôles de Niveau 1</li>
								<li>Surveillance des modifications faites sur les informations des établissements et des professeurs</li>
							</ul>
						</td>
					</tr>
				<? } ?>
				</tbody>
				<thead>
					<tr>
						<th colspan="2">Niveau 4. <span class="rank-img rank-4"></span> <?=Admin::$RANKS[4]?></th>
					</tr>
				</thead>
				<tbody>
					<tr class="promotion">
						<th>Promotion</th>
						<td>
							<ol>
								<li>Être choisit part un <?=Admin::$RANKS[5]?></li>
								<li>Doit fournir des informations supplémentaires</li>
								<li>Doit accepter un nouveau contrat</li>
							</ol>
						</td>
					</tr>
					<tr class="powers">
						<th>Pouvoirs</th>
						<td>
						<ul>
							<li>Champ d'action : tout les établissements et modérateurs</li>
							<li>Pouvoirs de Niveau 3</li>
							<li>Peut modérer les commentaires et les informations signalés</li>
							<li>Peut vérouiller ou dévérouiller un modérateur de niveau inférieur</li>
						</ul>
						</td>
					</tr>
				<? if ($user->power == 5) { ?>
					<tr class="controls">
						<th>Contrôles</th>
						<td>
							<ul>
								<li>[Auto] Contrôles de Niveau 1</li>
								<li>Surveillance régulière par un <?=Admin::$RANKS[5]?></li>
							</ul>
						</td>
					</tr>
				<? } ?>
				</tbody>
				<thead>
					<tr>
						<th colspan="2">Niveau 5. <span class="rank-img rank-5"></span> <?=Admin::$RANKS[5]?></th>
					</tr>
				</thead>
				<tbody>
					<tr class="promotion">
						<th>Promotion</th>
						<td>
							[Personnel de Campus Citizens]
						</td>
					</tr>
					<tr class="powers">
						<th>Pouvoirs</th>
						<td>
						<ul>
							<li>Champ d'action : illimité</li>
							<li>Pleins pouvoirs</li>
						</ul>
						</td>
					</tr>
				<? if ($user->power == 5) { ?>
					<tr class="controls">
						<th>Contrôles</th>
						<td>
							[Internes]
						</td>
					</tr>
				<? } ?>
				</tbody>
			<table>
		</div>

<? require("tpl/bas.php"); ?>
