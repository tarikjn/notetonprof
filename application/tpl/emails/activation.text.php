Bonjour <?=$prenom?>,

Nous te remercions pour ton inscription sur noteTonProf.fr, voici le récapitulatif des informations dont tu as besoin pour te connecter à ton compte :
- Identifiant :   <?=$email."\n"?>
- Mot de passe :  <?=$pass."\n"?>

Tu pourra commencer à utiliser ton compte dès que tu aura séléctionné un ou plusieurs établissements à modérer.
Pour celà, rends-toi sur la page d'accueil de noteTonProf.fr, puis sur la page de l'établissement que tu souhaite modérer et clique sur « Devenir délégué ».

Attention ! Ton compte n'est pas encore actif, afin que ton compte soit opérationnel, tu dois ouvrir la page suivante :
<?=$gotoURL?>/confirm?email=<?=urlencode($email)?>&code=<?=urlencode($check_id)."\n"?>

Cette procédure nous permet de vérifier les adresses e-mail des personnes qui s'inscrivent.

Amicalement,
L'équipe de noteTonProf.fr,

-- 
<?=$gotoURL."\n"?>
