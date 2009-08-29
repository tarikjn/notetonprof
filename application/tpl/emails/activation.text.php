Bonjour <?=$var->prenom?>,


Nous te remercions pour ton inscription sur noteTonProf.fr, voici le récapitulatif des informations dont tu as besoin pour te connecter à ton compte :
- Identifiant : <?=$var->email."\n"?>
- Mot de passe : <?=$var->pass."\n"?>

Tu pourra commencer à utiliser ton compte dès que tu aura séléctionné un ou plusieurs établissements à modérer.
Pour celà, rends-toi sur la page d'accueil de noteTonProf.fr, puis sur la page de l'établissement que tu souhaite modérer et clique sur « Devenir délégué ».

Attention ! Ton compte n'est pas encore actif, afin que ton compte soit opérationnel, tu dois ouvrir la page suivante :
<?=$var->base_url?>/confirm?email=<?=urlencode($var->email)?>&code=<?=urlencode($var->check_id)."\n"?>

Cela nous permet de vérifier ton adresse e-mail.


Amicalement,
L'équipe de noteTonProf.fr,

-- 
<?=$var->base_url."\n"?>
