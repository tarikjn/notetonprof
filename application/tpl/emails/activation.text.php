Bonjour <?=$prenom?>,

Nous te remercions pour ton inscription sur noteTonProf.fr, voici le r�capitulatif des informations dont tu as besoin pour te connecter � ton compte :
- Identifiant :   <?=$email."\n"?>
- Mot de passe :  <?=$pass."\n"?>

Tu pourra commencer � utiliser ton compte d�s que tu aura s�l�ctionn� un ou plusieurs �tablissements � mod�rer.
Pour cel�, rends-toi sur la page d'accueil de noteTonProf.fr, puis sur la page de l'�tablissement que tu souhaite mod�rer et clique sur � Devenir d�l�gu� �.

Attention ! Ton compte n'est pas encore actif, afin que ton compte soit op�rationnel, tu dois ouvrir la page suivante :
<?=$gotoURL?>/confirm?email=<?=urlencode($email)?>&code=<?=urlencode($check_id)."\n"?>

Cette proc�dure nous permet de v�rifier les adresses e-mail des personnes qui s'inscrivent.

Amicalement,
L'�quipe de noteTonProf.fr,

-- 
<?=$gotoURL."\n"?>
