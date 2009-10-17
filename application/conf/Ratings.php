<?php

class Ratings
{
	static $AMB = array(
	    1 => "Incontrôlée",
	    2 => "Décontractée",
	    3 => "Bonne",
	    4 => "Sérieuse",
	    5 => "Tendue"
	  );
	
	static $JUST = array(
	    1 => "Insouciante",
	    2 => "Légère",
	    3 => "Correcte",
	    4 => "Difficile",
	    5 => "Rude"
	  );
	
	static $CRITERIAS = array(
	    'interest' => array(
	        'title' => 'Intéressant / Motivant',
	        'desc' => "Les cours de ton prof sont-t-il intéressants ou ennuyants ?, Ton prof succite-t-il ton intérêt et ton envie de travailler ? C'est le critère de notation le plus important, il a coefficient 2 dans le calcul de la moyenne."
	      ),
	     'clarity' => array(
	        'title' => 'Clair / Pédagogue',
	        'desc' => "Avec quelle facilité comprends-tu les cours ? Ton prof explique-t-il bien ? Utilise-t-il une méthode efficace ?"
	      ),
	      'knowledgeable' => array(
	        'title' => 'Connaisseur / Compétent',
	        'desc' => "Lorsque tu pose des questions à ton prof, te répond-t-il de façon précise et développée ? Ton prof as-t-il des connaissances approfondies ou te semble-t-il passionné dans son sujet ?"
	      ),
	      'fairness' => array(
	        'title' => 'Juste / Équitable',
	        'desc' => "Ton prof note-t-il tes devoirs ou examens de façon juste par rapport aux autres élèves ? Récompense-t-il le travail de façon égale et constante ? Les règles dans son cours sont-t-elle claires ?"
	      ),
	      'regularity' => array(
	        'title' => 'Régulier',
	        'desc' => "Est-ce que ton prof est souvent absent ? Corrige-t-il rapidement tes devoirs ? As-tu terminé le programme avec ce prof ?"
	      ),
	      'availability' => array(
	        'title' => 'Disponible',
	        'desc' => "Ton prof prends-t-il le temps pour répondre à tes questions ou des points que tu n'as pas compris ? Est-t-il disponible à la fin du cours ou en dehors des cours ?"
	      ),
	      'difficulty' => array(
	        'title' => 'Difficultée / Niveau',
	        'desc' => "Comment est le niveau des cours et la difficulté des devoirs ?"
	      ),
	      'atmosphere' => array(
	        'title' => 'Ambiance',
	        'desc' => "La classe ressemble-t-elle à un zoo ou au contraire une école militaire ?"
	      ),
	  );
}
