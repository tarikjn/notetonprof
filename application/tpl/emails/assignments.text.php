<?
$f_caption = array(
    'daily' => 'quotidiennement',
    'weekly' => 'chaque semaine'
  );
?>
Bonjour <?=$var->prenom?>,


Tu as actuellement <?=array_sum($var->count)?> élément(s) à modérer, dont :
<? if ($var->count['admin']) { ?>

- <?=$var->count['admin']?> administrateur(s)
  <?=$var->base_url?>/admin/admins
<? } ?>
<? if ($var->count['data']) { ?>

- <?=$var->count['data']?> établissement(s) et/ou professeur(s)
  <?=$var->base_url?>/admin/data
<? } ?>
<? if ($var->count['comment']) { ?>

- <?=$var->count['comment']?> commentaire(s)
  <?=$var->base_url?>/admin/comments
<? } ?>


Ce courriel t'es envoyé <?=$f_caption[$var->f]?>, lorsque tu as des éléments à modérer.
Tu peux changer tes préférences de notification lorsque tu es connecté dans la section « Mon Compte » : 
<?=$var->base_url?>/admin/account


Amicalement,
L'équipe de NoteTonProf.com,

-- 
<?=$var->base_url."\n"?>
