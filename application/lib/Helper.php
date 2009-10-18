<?php

// only used form Helper(-like) purposes
require_once('conf/Ratings.php');

// used statically
class Helper
{
	// reCAPTCHA layouts
	static $RC_LAYOUTS = array(
	    'dl'    => '<dt>Étape Anti-spam et robots</dt><dd>%s</dd>',
		'plain' => '%s'
	  );

	/* *****************
	 * formatting
	 */
	
	/* $params: array of parameters
	 * - course: course identifier (required)
	 * - area: area identifier
	 * - dept: department identifier
	 * - city: [city id, zip code, city name]
	 * - school: [school id, school name]
	 * - prof: [prof id, first name, last name]
	 */
	static function navPath($params, $end = false)
	{
		$s = '<a href=".">Accueil</a>';
		$f = '<a href="%s">%s</a>';
		$e = '%2$s';
	
		for ($i = 0; list(, $p) = each($params); $i++)
		{
			// output '>'
			$s .= ' &gt; ';
			
			$b = ($end and $i + 1 == sizeof($params)) ? $e : $f;
			
			switch ($i)
			{
				case 0:
					$s .= sprintf($b,
					        'indicatifs/' . urlencode($p) .'/',
					        'Enseignement ' . Geo::$COURSE[$p] );
					break;
				
				case 1:
					$s .= sprintf($b,
					        'depts/' . urlencode($params[0]) . '/' . urlencode($p) . '/',
					        'Indicatif ' . htmlspecialchars($p) );
					break;
				
				case 2:
					$s .= sprintf($b,
					        'villes/' . urlencode($params[0]) . '/' . urlencode($p) . '/',
					        htmlspecialchars($p) . ' - ' . htmlspecialchars(Geo::$DEPT[$p]["nom"]) );
					break;
				
				case 3:
					$s .= sprintf($b,
					        'etblts/' . urlencode($params[0]) . '/' . urlencode($p[0]) . '/',
					        htmlspecialchars($p[1]) . ' - ' . htmlspecialchars($p[2]) );
					break;
				
				case 4:
					$s .= sprintf($b,
					        'profs2/' . $p[0] . '/',
					        '<span class="etab">' . htmlspecialchars($p[1]) . '</span>' );
					break;
				
				case 5:
					$s .= sprintf($b,
					        'notes2/' . $p[0] . '/',
					        htmlspecialchars($p[1]) . ' <span class="up">' . htmlspecialchars($p[2]) . '</span>' );
					break;	
			}
		}
		
		return $s;	
	}
	
	static function linkAndCurrent($path, $class = "")
	{
		if (Web::getPath() == Settings::WEB_PATH . "/$path")
		{
			$class = ((strlen($class))? "$class " : '') . 'current';
		}
		
		return '<a href="' . $path . '"' . ((strlen($class))?' class="'. $class .'"':'') . '>';
	}
	
	static function flatten($v)
	{
		if (is_array($v))
		{
			$f = '';
			
			foreach ($v as $s)
				$f .= " $s";
			
			$v = $f;
		}
		
		return $v;
	}
	
	static function f_int($i)
	{
		$locale = localeconv();
		$sep = ($locale['thousands_sep'])? $locale['thousands_sep'] : $locale["mon_thousands_sep"];
		return number_format($i, 0, $locale['decimal_point'], $sep);
	}

	static function schoolTitle($course, $secondary, $name, $id = null)
	{
		$extra = "";
		
		if ($course == E_2ND)
		{
			$levels = explode(",", $secondary);
			foreach ($levels as $key => $val) {
				$extra .= Geo::$SECONDARY[$val].((isset($levels[$key + 1]))?", ":" ");
			}
		}
		
		$retval = $extra . $name;
		
		if ($id)
			$retval = '<a href="profs2/' . $id . '/">' . $retval . '</a>';
		
		return $retval;
	}
	
	static function profTitle($firstName, $lastName, $id = null)
	{
		$retval = htmlspecialchars($firstName) . ' <span class="up">' . htmlspecialchars($lastName) . '</span>';
		
		if ($id)
			$retval = '<a href="notes2/' . $id . '/">' . $retval . '</a>';
		
		return $retval;
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
	
	static function selectHelper($values, $match)
	{
		$s = '';
		  
		for ($i = 0; list($value, $title) = each($values); $i++)
		{
			if ($match == $value)
				$selected = ' selected="selected"';
			else
				$selected = null;
			  	
			$s .= "<option value=\"" . h($value) . "\"$selected>" . h($title) . "</option>";
		}
		
		return $s;
	}
	
	static function radioSlider($name, $start, $end, $step, $defaults = null, $is_centered = false)
	{
		$s = '<div class="cc-radioslider">';
		
		for ($i = $start; $i <= $end; $i += $step)
		{
			$default = (@$defaults[$name])? $defaults[$name] :
			  (($is_centered)? 3 : null);
			
			$sel = ($i == $default)? ' checked="checked"' : '';
			$lpos = 9 + ($i - $start) * (200 / (($end - $start) * $step));
			$s .= "<div class=\"cc-radioslider-pos\" style=\"left: {$lpos}px;\">"
			    . "<label for=\"radio-$name-$i\">$i</label>"
			    . "<div><input type=\"radio\" id=\"radio-$name-$i\" value=\"$i\" name=\"$name\"$sel /></div>"
			    . "</div>";
		}
		
		return $s . '</div>';
	}
	
	static function formFieldCheck($name, $on_value = 'on', $defaults = array(), $put_id = false)
	{
		$s = '<input type="checkbox" name="' . $name . '" value="' . $on_value . '"';
				
		if ($put_id)
			$s .= ' id="' . $name . '"';
		
		if ($defaults[$name] and !in_array($defaults[$name], array('no', 'off')))
			$s .= ' checked="checked"';
		
		return $s . ' />';
	}
	
	static function formFieldInput($name, $defaults = array())
	{
		return '<input type="text" name="' . $name . '" value="' . $defaults[$name] . '" />';
	}
	
	static function getErrorHeader($error)
	{
		if ($error)
			$retval = '<p class="error">'
			        . '  Attention ! Des <a href="'.$_SERVER["REQUEST_URI"].'#first-form-error">erreurs</a> sont présentes, revérifie le formulaire.'
			        . '</p>';
		else
			$retval = '';
		
		return $retval;
	}
	
	static function getFormError($name, $error)
	{
		if (!@$error[$name])
			return;
		
		// TODO: check array key position?
		$anchor = (1) ? '<a name="first-form-error">%s</a>' : '';
		
		$s = '<div class="cc-form-error"><div class="text"><div class="bg"></div><div class="inner">'
		   . sprintf($anchor, $error[$name])
		   . '</div></div><div class="tip"></div></div>';
		
		return $s;
	}
}
