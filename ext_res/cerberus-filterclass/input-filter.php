<?php
/**#@+
* Ways of cleaning input. Should be mostly self-explanatory.
*/
define('TYPE_NOCLEAN',           0); // no change
define('TYPE_BOOL',              1); // force boolean
define('TYPE_INT',               2); // force integer
define('TYPE_UINT',              3); // force unsigned integer
define('TYPE_NUM',               4); // force number
define('TYPE_UNUM',              5); // force unsigned number
define('TYPE_UNIXTIME',          6); // force unix datestamp (unsigned integer)
define('TYPE_STR',               7); // force trimmed string
define('TYPE_NOTRIM',            8); // force string - no trim
define('TYPE_NOHTML',            9); // force trimmed string with HTML made safe
define('TYPE_ARRAY',            10); // force array
define('TYPE_FILE',             11); // force file
define('TYPE_BINARY',           12); // force binary string
define('TYPE_NOHTMLCOND',       13); // force trimmed string with HTML made safe if determined to be unsafe

define('TYPE_ARRAY_BOOL',      101);
define('TYPE_ARRAY_INT',       102);
define('TYPE_ARRAY_UINT',      103);
define('TYPE_ARRAY_NUM',       104);
define('TYPE_ARRAY_UNUM',      105);
define('TYPE_ARRAY_UNIXTIME',  106);
define('TYPE_ARRAY_STR',       107);
define('TYPE_ARRAY_NOTRIM',    108);
define('TYPE_ARRAY_NOHTML',    109);
define('TYPE_ARRAY_ARRAY',     110);
define('TYPE_ARRAY_FILE',      11);  // An array of "Files" behaves differently than other <input> arrays. TYPE_FILE handles both types.
define('TYPE_ARRAY_BINARY',    112);
define('TYPE_ARRAY_NOHTMLCOND',113);

define('TYPE_ARRAY_KEYS_INT',  202);
define('TYPE_ARRAY_KEYS_STR',  207);

define('TYPE_CONVERT_SINGLE',  100); // value to subtract from array types to convert to single types
define('TYPE_CONVERT_KEYS',    200); // value to subtract from array => keys types to convert to single types
/**#@-*/

// temporary
define('INT',        TYPE_INT);
define('STR',        TYPE_STR);
define('STR_NOHTML', TYPE_NOHTML);
define('FILE',       TYPE_FILE);

/**
* Class to handle and sanitize variables from GET, POST and COOKIE etc
*
*/
class Input_Cleaner
{
	/**
	* Translation table for short superglobal name to long superglobal name
	*
	* @var     array
	*/
	var $superglobal_lookup = array('g' => '_GET',
		                            'p' => '_POST',
		                            'r' => '_REQUEST',
		                            'c' => '_COOKIE',
		                            's' => '_SERVER',
		                            'e' => '_ENV',
		                            'f' => '_FILES'
	                               );

	/**
	* System state. The complete URL of the current page, without sessionhash
	*
	* @var	string
	*/
	var $scriptpath = '';

	/**
	* Reload URL. Complete URL of the current page including sessionhash
	*
	* @var	string
	*/
	var $reloadurl = '';

	/**
	* System state. The complete URL of the referring page
	*
	* @var	string
	*/
	var $url = '';

	/**
	* System state. The IP address of the current visitor
	*
	* @var	string
	*/
	var $ipaddress = '';

	/**
	* System state. An attempt to find a second IP for the current visitor (proxy etc)
	*
	* @var	string
	*/
	var $alt_ip = '';

	/**
	* Keep track of variables that have already been cleaned
	*
	* @var	array
	*/
	var $cleaned_vars = array();

	/**
	* Constructor
	*
	* First, reverses the effects of magic quotes on GPC
	* Second, translates short variable names to long (p --> $_POST)
	* Third, deals with $_COOKIE[name] conflicts
	*
	* @param	Registry	The instance of the Registry object
	*/
	function Input_Cleaner()
	{
		if (!is_array($GLOBALS))
		{
			die('<strong>Fatal Error:</strong> Invalid URL.');
		}

		// overwrite GET[x] and REQUEST[x] with POST[x] if it exists (overrides server's GPC order preference)
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			foreach (array_keys($_POST) AS $key)
			{
				if (isset($_GET["$key"]))
				{
					$_GET["$key"] = $_REQUEST["$key"] = $_POST["$key"];
				}
			}
		}

		// reverse the effects of magic quotes if necessary
		if (function_exists('get_magic_quotes_gpc') AND get_magic_quotes_gpc())
		{
			$this->stripslashes_deep($_REQUEST); // needed for some reason (at least on php5 - not tested on php4)
			$this->stripslashes_deep($_GET);
			$this->stripslashes_deep($_POST);
			$this->stripslashes_deep($_COOKIE);

			if (is_array($_FILES))
			{
				foreach ($_FILES AS $key => $val)
				{
					$_FILES["$key"]['tmp_name'] = str_replace('\\', '\\\\', $val['tmp_name']);
				}
				$this->stripslashes_deep($_FILES);
			}
		}
		//set_magic_quotes_runtime(0);
		@ini_set('magic_quotes_sybase', 0);

		foreach (array('_GET', '_POST') AS $arrayname)
		{
			if (isset($GLOBALS["$arrayname"]['do']))
			{
				$GLOBALS["$arrayname"]['do'] = trim($GLOBALS["$arrayname"]['do']);
			}
		}

		// set the AJAX flag if we have got an AJAX submission
		if ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) )
		{
		    if ($_SERVER['REQUEST_METHOD'] == 'POST' AND $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
		    {
		    	$_POST['ajax'] = $_REQUEST['ajax'] = 1;
		    }
		}
		else
		{
		    $_POST['ajax'] = $_REQUEST['ajax'] = 0;
		}

		// reverse the effects of register_globals if necessary
		if (@ini_get('register_globals') OR !@ini_get('gpc_order'))
		{
			foreach ($this->superglobal_lookup AS $arrayname)
			{
				$registry->superglobal_size["$arrayname"] = sizeof($GLOBALS["$arrayname"]);

				foreach (array_keys($GLOBALS["$arrayname"]) AS $varname)
				{
					// make sure we dont unset any global arrays like _SERVER
					if (!in_array($varname, $this->superglobal_lookup))
					{
						unset($GLOBALS["$varname"]);
					}
				}
			}
		}
		else
		{
			foreach ($this->superglobal_lookup AS $arrayname)
			{
				$this->superglobal_size["$arrayname"] = sizeof($GLOBALS["$arrayname"]);
			}
		}

		// deal with cookies that may conflict with _GET and _POST data, and create our own _REQUEST with no _COOKIE input
		foreach (array_keys($_COOKIE) AS $varname)
		{
			unset($_REQUEST["$varname"]);
			if (isset($_POST["$varname"]))
			{
				$_REQUEST["$varname"] =& $_POST["$varname"];
			}
			else if (isset($_GET["$varname"]))
			{
				$_REQUEST["$varname"] =& $_GET["$varname"];
			}
		}

		// fetch client IP address
		$this->ipaddress = $this->fetch_ip();
		define('IPADDRESS', $this->ipaddress);

		// attempt to fetch IP address from behind proxies - useful, but don't rely on it...
		$this->alt_ip = $this->fetch_alt_ip();
		define('ALT_IP', $this->alt_ip);

		// defines if the current page was visited via SSL or not
		if ( isset($_SERVER['HTTPS']) )
		{
		    define('REQ_PROTOCOL', ( ($_SERVER['HTTPS'] == 'on' OR $_SERVER['HTTPS'] == '1') ? 'https' : 'http') );
		}
		else
		{
		    define('REQ_PROTOCOL', 'http');
		}

		// fetch complete url of current page
		$this->scriptpath = $this->fetch_scriptpath();
		define('SCRIPTPATH', $this->scriptpath);

		$this->url = $this->fetch_url();
		define('URL', $this->url);

		// fetch url of current page without the variable string
		$quest_pos = strpos($this->scriptpath, '?');
		if ($quest_pos !== false)
		{
			$this->script = substr($this->scriptpath, 0, $quest_pos);
		}
		else
		{
			$this->script = $this->scriptpath;
		}
		define('SCRIPT', $this->script);

		// define session constants
		define('SESSION_HOST',   substr($this->ipaddress, 0, 15));

		// define some useful contants related to environment
		define('USER_AGENT', $_SERVER['HTTP_USER_AGENT']);

		if ( isset($_SERVER['HTTP_REFERER']) )
		{
		    define('REFERRER', $_SERVER['HTTP_REFERER']);
		}
		else
		{
		    define('REFERRER', '');
		}
	}

	/**
	* Makes data in an array safe to use
	*
	* @param	array	The source array containing the data to be cleaned
	* @param	array	Array of variable names and types we want to extract from the source array
	*
	* @return	array
	*/
	function &clean_array(&$source, $variables)
	{
		$return = array();

		foreach ($variables AS $varname => $vartype)
		{
			$return["$varname"] =& $this->clean($source["$varname"], $vartype, isset($source["$varname"]));
		}

		return $return;
	}

	/**
	* Makes GPC variables safe to use
	*
	* @param	string	Either, g, p, c, r or f (corresponding to get, post, cookie, request and files)
	* @param	array	Array of variable names and types we want to extract from the source array
	*
	* @return	array
	*/
	function clean_array_gpc($source, $variables)
	{
		$sg =& $GLOBALS[$this->superglobal_lookup["$source"]];

		foreach ($variables AS $varname => $vartype)
		{
			// clean a variable only once unless its a different type
			if (!isset($this->cleaned_vars["$varname"]) OR $this->cleaned_vars["$varname"] != $vartype)
			{
				$this->GPC_exists["$varname"] = isset($sg["$varname"]);
				$this->GPC["$varname"] =& $this->clean($sg["$varname"],
					                                   $vartype,
					                                   isset($sg["$varname"])
				                                      );
				$this->cleaned_vars["$varname"] = $vartype;
			}
		}
	}

	/**
	* Makes a single GPC variable safe to use and returns it
	*
	* @param	array	The source array containing the data to be cleaned
	* @param	string	The name of the variable in which we are interested
	* @param	integer	The type of the variable in which we are interested
	*
	* @return	mixed
	*/
	function &clean_gpc($source, $varname, $vartype = TYPE_NOCLEAN)
	{
		// clean a variable only once unless its a different type
		if (!isset($this->cleaned_vars["$varname"]) OR $this->cleaned_vars["$varname"] != $vartype)
		{
			$sg =& $GLOBALS[$this->superglobal_lookup["$source"]];

			$this->GPC_exists["$varname"] = isset($sg["$varname"]);
			$this->GPC["$varname"] =& $this->clean($sg["$varname"],
				                                   $vartype,
				                                   isset($sg["$varname"])
			                                      );
			$this->cleaned_vars["$varname"] = $vartype;
		}

		return $this->GPC["$varname"];
	}

	/**
	* Makes a single variable safe to use and returns it
	*
	* @param	mixed	The variable to be cleaned
	* @param	integer	The type of the variable in which we are interested
	* @param	boolean	Whether or not the variable to be cleaned actually is set
	*
	* @return	mixed	The cleaned value
	*/
	function &clean(&$var, $vartype = TYPE_NOCLEAN, $exists = true)
	{
		if ($exists)
		{
			if ($vartype < TYPE_CONVERT_SINGLE)
			{
				$this->do_clean($var, $vartype);
			}
			else if (is_array($var))
			{
				if ($vartype >= TYPE_CONVERT_KEYS)
				{
					$var = array_keys($var);
					$vartype -=  TYPE_CONVERT_KEYS;
				}
				else
				{
					$vartype -= TYPE_CONVERT_SINGLE;
				}

				foreach (array_keys($var) AS $key)
				{
					$this->do_clean($var["$key"], $vartype);
				}
			}
			else
			{
				$var = array();
			}
			return $var;
		}
		else
		{
			if ($vartype < TYPE_CONVERT_SINGLE)
			{
				switch ($vartype)
				{
					case TYPE_INT:
					case TYPE_UINT:
					case TYPE_NUM:
					case TYPE_UNUM:
					case TYPE_UNIXTIME:
					{
						$var = 0;
						break;
					}
					case TYPE_STR:
					case TYPE_NOHTML:
					case TYPE_NOTRIM:
					case TYPE_NOHTMLCOND:
					{
						$var = '';
						break;
					}
					case TYPE_BOOL:
					{
						$var = 0;
						break;
					}
					case TYPE_ARRAY:
					case TYPE_FILE:
					{
						$var = array();
						break;
					}
					case TYPE_NOCLEAN:
					{
						$var = null;
						break;
					}
					default:
					{
						$var = null;
					}
				}
			}
			else
			{
				$var = array();
			}

			return $var;
		}
	}

	/**
	* Does the actual work to make a variable safe
	*
	* @param	mixed	The data we want to make safe
	* @param	integer	The type of the data
	*
	* @return	mixed
	*/
	function &do_clean(&$data, $type)
	{
		static $booltypes = array('1', 'yes', 'y', 'true');

		switch ($type)
		{
			case TYPE_INT:    $data = intval($data);                                    break;
			case TYPE_UINT:   $data = ($data = intval($data)) < 0 ? 0 : $data;          break;
			case TYPE_NUM:    $data = strval($data) + 0;                                break;
			case TYPE_UNUM:   $data = strval($data) + 0;
							  $data = ($data < 0) ? 0 : $data;                          break;
			case TYPE_BINARY: $data = strval($data);                                    break;
			case TYPE_STR:    $data = trim(strval($data));                              break;
			case TYPE_NOTRIM: $data = strval($data);                                    break;
			case TYPE_NOHTML: $data = $this->htmlspecialchars_uni(trim(strval($data))); break;
			case TYPE_BOOL:   $data = in_array(strtolower($data), $booltypes) ? 1 : 0;  break;
			case TYPE_ARRAY:  $data = (is_array($data)) ? $data : array();              break;
			case TYPE_NOHTMLCOND:
			{
				$data = trim(strval($data));
				if (strcspn($data, '<>"') < strlen($data) OR (strpos($data, '&') !== false AND !preg_match('/&(#[0-9]+|amp|lt|gt|quot);/si', $data)))
				{
					// data is not htmlspecialchars because it still has characters or entities it shouldn't
					$data = $this->htmlspecialchars_uni($data);
				}
				break;
			}
			case TYPE_FILE:
			{
				// perhaps redundant :p
				if (is_array($data))
				{
					if (is_array($data['name']))
					{
						$files = count($data['name']);
						for ($index = 0; $index < $files; $index++)
						{
							$data['name']["$index"]     = trim(strval($data['name']["$index"]));
							$data['type']["$index"]     = trim(strval($data['type']["$index"]));
							$data['tmp_name']["$index"] = trim(strval($data['tmp_name']["$index"]));
							$data['error']["$index"]    = intval($data['error']["$index"]);
							$data['size']["$index"]     = intval($data['size']["$index"]);
						}
					}
					else
					{
						$data['name']     = trim(strval($data['name']));
						$data['type']     = trim(strval($data['type']));
						$data['tmp_name'] = trim(strval($data['tmp_name']));
						$data['error']    = intval($data['error']);
						$data['size']     = intval($data['size']);
					}
				}
				else
				{
					$data = array(
						'name'     => '',
						'type'     => '',
						'tmp_name' => '',
						'error'    => 0,
						'size'     => 4, // UPLOAD_ERR_NO_FILE
					);
				}
				break;
			}
			case TYPE_UNIXTIME:
			{
				if (is_array($data))
				{
					$data = $this->clean($data, TYPE_ARRAY_UINT);
					if ($data['month'] AND $data['day'] AND $data['year'])
					{
						$data = $this->mymktime($data['hour'], $data['minute'], $data['second'], $data['month'], $data['day'], $data['year']);
					}
					else
					{
						$data = 0;
					}
				}
				else
				{
					$data = ($data = intval($data)) < 0 ? 0 : $data;
				}
				break;
			}
			// null actions should be deifned here so we can still catch typos below
			case TYPE_NOCLEAN:
			{
				break;
			}

			default:
			{
				trigger_error('Input_Cleaner::do_clean() Invalid data type specified', E_USER_WARNING);
			}
		}

		// strip out characters that really have no business being in non-binary data
		switch ($type)
		{
			case TYPE_STR:
			case TYPE_NOTRIM:
			case TYPE_NOHTML:
			case TYPE_NOHTMLCOND:
				$data = str_replace(chr(0), '', $data);
		}

		return $data;
	}

	/**
	* Removes HTML characters and potentially unsafe scripting words from a string
	*
	* @param	string	The variable we want to make safe
	*
	* @return	string
	*/
	function xss_clean($var)
	{
		static $preg_find    = array('#^javascript#i', '#^vbscript#i', '#^script#i'),
			   $preg_replace = array('',               '',             '');

		return preg_replace($preg_find, $preg_replace, htmlspecialchars(trim($var)));
	}

	/**
	* Reverses the effects of magic_quotes on an entire array of variables
	*
	* @param	array	The array on which we want to work
	*/
	function stripslashes_deep(&$value, $depth = 0)
	{
		if (is_array($value))
		{
		    foreach ($value AS $key => $val)
		    {
		        if (is_string($val))
		        {
		            $value["$key"] = stripslashes($val);
		        }
		        else if (is_array($val) AND $depth < 10)
		        {
		            $this->stripslashes_deep($value["$key"], $depth + 1);
		        }
		    }
		}
	}

	/**
	* Strips out the s=gobbledygook& rubbish from URLs
	*
	* @param	string	The URL string from which to remove the session stuff
	*
	* @return	string
	*/
	function strip_sessionhash($string)
	{
		$string = preg_replace('/(s|sessionhash)=[a-z0-9]{32}?&?/', '', $string);
		return $string;
	}

	/**
	* Fetches the 'scriptpath' variable - ie: the URI of the current page
	*
	* @return	string
	*/
	function fetch_scriptpath()
	{
		if ($this->scriptpath != '')
		{
			return $this->scriptpath;
		}
		else
		{
			if ($_SERVER['REQUEST_URI'] OR $_ENV['REQUEST_URI'])
			{
				$scriptpath = $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : $_ENV['REQUEST_URI'];
			}
			else
			{
				if ($_SERVER['PATH_INFO'] OR $_ENV['PATH_INFO'])
				{
					$scriptpath = $_SERVER['PATH_INFO'] ? $_SERVER['PATH_INFO'] : $_ENV['PATH_INFO'];
				}
				else if ($_SERVER['REDIRECT_URL'] OR $_ENV['REDIRECT_URL'])
				{
					$scriptpath = $_SERVER['REDIRECT_URL'] ? $_SERVER['REDIRECT_URL'] : $_ENV['REDIRECT_URL'];
				}
				else
				{
					$scriptpath = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_ENV['PHP_SELF'];
				}

				if ($_SERVER['QUERY_STRING'] OR $_ENV['QUERY_STRING'])
				{
					$scriptpath .= '?' . ($_SERVER['QUERY_STRING'] ? $_SERVER['QUERY_STRING'] : $_ENV['QUERY_STRING']);
				}
			}

			// in the future we should set $registry->script here too
			$quest_pos = strpos($scriptpath, '?');
			if ($quest_pos !== false)
			{
				$script = urldecode(substr($scriptpath, 0, $quest_pos));
				$scriptpath = $script . substr($scriptpath, $quest_pos);
			}
			else
			{
				$scriptpath = urldecode($scriptpath);
			}

			// store a version that includes the sessionhash
			$this->reloadurl = $this->xss_clean($scriptpath);

			$scriptpath = $this->strip_sessionhash($scriptpath);
			$scriptpath = $this->xss_clean($scriptpath);
			$this->scriptpath = $scriptpath;

			return $scriptpath;
		}
	}

    /**
	* Fetches the 'url' variable - usually the URL of the previous page in the history
	*
	* @return	string
	*/
	function fetch_url()
	{
	    $temp_url = '';
	    $url      = '';

		if ( isset($_REQUEST['url']) )
		{
		    $temp_url = $_REQUEST['url'];
		}

		$scriptpath = $this->fetch_scriptpath();

		if ( empty($temp_url) && isset($_SERVER['HTTP_REFERER']) )
		{
			$url = $_SERVER['HTTP_REFERER'];
		}
		else
		{
		    if ( isset($_SERVER['HTTP_REFERER']) )
		    {
		        if ( ($temp_url == $_SERVER['HTTP_REFERER']) )
			    {
			    	$url = 'index.php';
			    }
			    else
			    {
			    	$url = $temp_url;
			    }

		    }
		}

		if ($url == $scriptpath OR empty($url))
		{
			$url = 'index.php';
		}

		$url = $this->xss_clean($url);

		return $url;
	}

	/**
	* Fetches the IP address of the current visitor
	*
	* @return	string
	*/
	function fetch_ip()
	{
		return $_SERVER['REMOTE_ADDR'];
	}

	/**
	* Fetches an alternate IP address of the current visitor, attempting to detect proxies etc.
	*
	* @return	string
	*/
	function fetch_alt_ip()
	{
		$alt_ip = $_SERVER['REMOTE_ADDR'];

		if (isset($_SERVER['HTTP_CLIENT_IP']))
		{
			$alt_ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches))
		{
			// try to avoid using an internal IP address, its probably a proxy
			$ranges = array('10.0.0.0/8'     => array(ip2long('10.0.0.0'),    ip2long('10.255.255.255')),
				            '127.0.0.0/8'    => array(ip2long('127.0.0.0'),   ip2long('127.255.255.255')),
				            '169.254.0.0/16' => array(ip2long('169.254.0.0'), ip2long('169.254.255.255')),
				            '172.16.0.0/12'  => array(ip2long('172.16.0.0'),  ip2long('172.31.255.255')),
				            '192.168.0.0/16' => array(ip2long('192.168.0.0'), ip2long('192.168.255.255')),
			               );
			foreach ($matches[0] AS $ip)
			{
				$ip_long = ip2long($ip);
				if ($ip_long === false OR $ip_long == -1)
				{
					continue;
				}

				$private_ip = false;
				foreach ($ranges AS $range)
				{
					if ($ip_long >= $range[0] AND $ip_long <= $range[1])
					{
						$private_ip = true;
						break;
					}
				}

				if (!$private_ip)
				{
					$alt_ip = $ip;
					break;
				}
			}
		}
		else if (isset($_SERVER['HTTP_FROM']))
		{
			$alt_ip = $_SERVER['HTTP_FROM'];
		}

		return $alt_ip;
	}

	// #############################################################################
    /**
    * Unicode-safe version of htmlspecialchars()
    *
    * @param	string	Text to be made html-safe
    *
    * @return	string
    */
    function htmlspecialchars_uni($text, $entities = true)
    {
    	return str_replace(// replace special html characters
    		               array('<', '>', '"'),
    		               array('&lt;', '&gt;', '&quot;'),
    		               // translates all non-unicode entities
    		               preg_replace('/&(?!' . ($entities ? '#[0-9]+|shy' : '(#[0-9]+|[a-z]+)') . ';)/si',
    			                        '&amp;',
    			                        $text
    		                           )
    	                  );
    }

    function mymktime($hours = 0, $minutes = 0, $seconds = 0, $month = 0, $day = 0, $year = 0)
    {
    	return mktime(intval($hours), intval($minutes), intval($seconds), intval($month), intval($day), intval($year));
    }
}
?>