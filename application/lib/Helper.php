<?php

// only used form Helper(-like) purposes
require_once('conf/Ratings.php');

// used statically
class Helper
{
	/* *****************
	 * formatting
	 */
	
	static function f_int($i)
	{
		$locale = localeconv();
		return number_format($i, 0, $locale['decimal_point'], $locale['thousands_sep']);
	}

	static function schoolTitle($course, $secondary, $name)
	{
		$extra = "";
		
		if ($course == E_2ND)
		{
			$levels = explode(",", $secondary);
			foreach ($levels as $key => $val) {
				$extra .= Geo::$SECONDARY[$val].((isset($levels[$key + 1]))?", ":" ");
			}
		}
		
		return $extra . $name;
	}
	
	static function profTitle($firstName, $lastName)
	{
		return htmlspecialchars($firstName) . ' <span class="up">' . htmlspecialchars($lastName) . '</span>';
	}
	
	static function formatPath($title, $args)
	{
		
	}
	
	static function showErrors($errors)
	{
		if (!$errors)
			return;
		
		?>
		<div class="head-notice">
		  	<p>Certaines actions n'ont pu être procédées :</p>
			<ul><? foreach ($errors as $alert) { ?><li><?=$alert?></li><? } ?></ul>
		</div>
		<?
	}
	
	static function showInfo($info)
	{
		if (!$info)
			return;
		
		?>
		<p class="msg"><?=$info?></p>
		<?
	}
	
	static function titleFor($type, $data)
	{
		if ($type == 'school')
			return self::schoolTitle($data->cursus, $data->secondaire, $data->nom);
		else if ($type == 'prof')
			return self::profTitle($data->prenom, $data->nom);
	}
	
	static function urlFor($type, $data)
	{
		if ($type == 'school')
			return "profs2/{$data->id}/";
		else if ($type == 'prof')
			return "notes2/{$data->id}/";
	}
	
	static function tagFor($type, $data)
	{
		$tag = "(" . (($type == 'school')? 'Établissement' : 'Professeur') . ") ";
		
		$tag .= '<a href="' . self::urlFor($type, $data) . '">' . self::titleFor($type, $data) . '<a/>';
		
		return $tag;
	}
	
	static function smiley($moy, $pop = 0, $big = 0)
	{
		if (!$moy)
			$tag = NULL;
		else
		{
			if ($moy < 2.5)
			{
				$img = "mediocre";
				$tit = "Médiocre";
			}
			else if ($moy > 3.5)
			{
				$img = "bon";
				$tit = "Bon";
			}
			else
			{
				$img = "moyen";
				$tit = "Moyen";
			}
			
			if ($pop > .5)
			{
				$img .= "-pop";
				$tit .= ", Populaire/Stylé(e)";
			}
			
			$tag = "<img src=\"img/smileys/evaluations/$img.png\" class=\"".(($big) ? "big" : "smiley")."\" alt=\"\" title=\"$tit\" />";
		}
		
		return $tag;
	}
	
	static function ambiance($moy, $amb = 1)
	{	
		if (!$moy)
			$tag = NULL;
		else
		{
			$add = "";
			
			if ($moy < 2)
			{
				$img = "cool";
				$tit = 1;
				$add = " +";
			}
			if ($moy < 2.75)
			{
				$img = "cool";
				$tit = 2;
				$add = " -";
			}
			else if ($moy > 4)
			{
				$img = "serieux";
				$tit = 5;
				$add = " +";
			}
			else if ($moy > 3.25)
			{
				$img = "serieux";
				$tit = 4;
				$add = " -";
			}
			else
			{
				$img = "normal";
				$tit = 3;
			}
			$tit = ($amb) ? Ratings::$AMB[$tit] : Ratings::$JUST[$tit];
			
			$tag = "<strong><img src=\"img/smileys/evaluations/$img.png\" class=\"smiley\" alt=\"\" title=\"$tit\" />$add</strong>";
		}
		
		return $tag;
	}
	
	static function formatLog($result)
	{
		?>
		<table class="logs">
			<thead>
			    <tr>
			    	<th>Date / Heure</th>
			    	<th>Par</th>
			    	<th>Action</th>
			    	<th>Détails</th>
			    </tr>
			</thead>
			<tbody>
			    <? for ($i = 0; $row = $result->fetch_object(); $i++) { ?>
			    <tr class="<?=($i % 2 == 0)? 'even':'odd'?>">
			    	<td><?=strftime("%c", $row->time)?></td>
			    	<td>
			    		<?
			    			if ($row->actor_id)
			    			{
			    		?>
			    		<a href="admin/edit-admin?id=<?=$row->actor_id?>">Modérateur n° <?=$row->actor_id?></a> <span class="rank-img tiny rank-<?=$row->level?>" title="<?=Admin::$RANKS[$row->level]?>"></span>
			    		<?
			    			}
			    			else
			    			{
			    		?>
			    		Système
			    		<?
			    			}
			    		?>
			    	</td>
			    	<td><?=htmlspecialchars($row->log_msg)?></td>
			    	<td><?=htmlspecialchars($row->related_data)?></td>
			    </tr>
			    <? } ?>
			</tbody>
		<table>
		<?
	}
}
