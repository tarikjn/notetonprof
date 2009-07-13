<?php

class RealSession
{
	const PS_DELIMITER = '|';
	const PS_UNDEF_MARKER = '!';
	
	static function replace($sid, $handler)
	{
		$fname = Settings::SESSION_PATH . "/sess_" . $sid;
		if ($fcontent = @file_get_contents($fname))
		{
			$sess = self::decode($fcontent);
			
			$sess = call_user_func($handler, $sess);
			
			file_put_contents($fname, self::encode($sess));
		}
	}
	
	static function encode($data)
	{
		$result = '';
		foreach ($data as $key => $value) {
		    $result .= ((strlen($result))? '|':'') . $key . '|' . serialize($value);
		}
		return $result;
	}
	
	// source: http://us3.php.net/manual/en/function.session-decode.php (bmorel at ssi dot fr)
	static function decode($str)
	{
	    $str = (string)$str;
	
	    $endptr = strlen($str);
	    $p = 0;
	
	    $serialized = '';
	    $items = 0;
	    $level = 0;
	
	    while ($p < $endptr) {
	        $q = $p;
	        while ($str[$q] != self::PS_DELIMITER)
	            if (++$q >= $endptr) break 2;
	
	        if ($str[$p] == self::PS_UNDEF_MARKER) {
	            $p++;
	            $has_value = false;
	        } else {
	            $has_value = true;
	        }
	       
	        $name = substr($str, $p, $q - $p);
	        $q++;
	
	        $serialized .= 's:' . strlen($name) . ':"' . $name . '";';
	       
	        if ($has_value) {
	            for (;;) {
	                $p = $q;
	                switch ($str[$q]) {
	                    case 'N': /* null */
	                    case 'b': /* boolean */
	                    case 'i': /* integer */
	                    case 'd': /* decimal */
	                        do $q++;
	                        while ( ($q < $endptr) && ($str[$q] != ';') );
	                        $q++;
	                        $serialized .= substr($str, $p, $q - $p);
	                        if ($level == 0) break 2;
	                        break;
	                    case 'R': /* reference  */
	                        $q+= 2;
	                        for ($id = ''; ($q < $endptr) && ($str[$q] != ';'); $q++) $id .= $str[$q];
	                        $q++;
	                        $serialized .= 'R:' . ($id + 1) . ';'; /* increment pointer because of outer array */
	                        if ($level == 0) break 2;
	                        break;
	                    case 's': /* string */
	                        $q+=2;
	                        for ($length=''; ($q < $endptr) && ($str[$q] != ':'); $q++) $length .= $str[$q];
	                        $q+=2;
	                        $q+= (int)$length + 2;
	                        $serialized .= substr($str, $p, $q - $p);
	                        if ($level == 0) break 2;
	                        break;
	                    case 'a': /* array */
	                    case 'O': /* object */
	                        do $q++;
	                        while ( ($q < $endptr) && ($str[$q] != '{') );
	                        $q++;
	                        $level++;
	                        $serialized .= substr($str, $p, $q - $p);
	                        break;
	                    case '}': /* end of array|object */
	                        $q++;
	                        $serialized .= substr($str, $p, $q - $p);
	                        if (--$level == 0) break 2;
	                        break;
	                    default:
	                        return false;
	                }
	            }
	        } else {
	            $serialized .= 'N;';
	            $q+= 2;
	        }
	        $items++;
	        $p = $q;
	    }
	    return @unserialize( 'a:' . $items . ':{' . $serialized . '}' );
	}
}
