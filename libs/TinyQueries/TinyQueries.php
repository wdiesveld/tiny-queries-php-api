<?php
/**
 * TinyQueries - Framework for merging and nesting relational data
 *
 * @author      Wouter Diesveld <wouter@tinyqueries.com>
 * @copyright   2012 - 2016 Diesveld Query Technology
 * @link        http://www.tinyqueries.com
 * @version     3.0.7b
 * @package     TinyQueries
 *
 * License
 *
 * This software is licensed under Apache License 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace TinyQueries;




/**
 * HttpTools
 *
 * @author 	Wouter Diesveld <wouter@tinyqueries.com>
 * @package TinyQueries
 */
 class HttpTools
 {
	/**
	 * Gets a param from an array of params, trims it and checks if it matches the regexp provided
	 *
	 * Note 1: This is a helper-function and should not be used directly; use getURLparam, getPostVar etc instead
	 * Note 2: If the param itself is an array, all elements of the array are checked
	 *
	 * @param paramname
	 * @param requestArray
	 * @param regexp (optional)
	 * @param defaultvalue (optional) Value which is returned if there is no match with the regexp
	 */
	public static function getParam($paramname, $requestArray, $regexp=null, $defaultvalue=null)
	{
		$value = $defaultvalue;

		if (array_key_exists($paramname, $requestArray))
			$value = $requestArray[$paramname];
			
		self::sanitize( $value, $regexp, $defaultvalue );	

		return $value;
	}
	
    private static function is_assoc($arr) 
	{
        return (is_array($arr) && count(array_filter(array_keys($arr),'is_string')) == count($arr));
    }	

	/**
	 * Converts encoding to latin, trims the value, and checks it against the regular expression
	 *
	 * @param {mixed} $value Can be an array or a string - if it is an array, sanitize is called recursively for each array element
	 * @param {string} $regexp
	 * @param {string} $defaultvalue
	 */
	private static function sanitize(&$value, $regexp, $defaultvalue)
	{
		if (self::is_assoc($value))
		{
			foreach (array_keys($value) as $key)
			{
				self::sanitize( $value[$key], $regexp, $defaultvalue );
			}
		}
		elseif (is_array($value))
		{
			for ($i=0;$i<count($value);$i++)
			{
				self::sanitize( $value[$i], $regexp, $defaultvalue );
			}
		}
		elseif (is_string($value))
		{
			$value = trim( $value );
			
			if ($value == 'null')
			{
				// exception for javascript serialization of null-values
				$value = null;
			}
			elseif ($regexp && !preg_match($regexp, $value))
			{
				$value = $defaultvalue;
			}
		}
	}
	
	/**
	 * Gets the posted (or put) json blob
	 *
	 */
	public static function getJsonBody()
	{
		// Get content of body of HTTP message
		$body = file_get_contents('php://input');
		
		// Replace EOL's and tabs by a space character (these chars are forbidden to be used within json strings)
		$body = preg_replace("/[\n\r\t]/", " ", $body);		
		
		if ($json = @json_decode($body, true))
			return $json;
			
		return null;
	}

	public static function getURLparam($paramname, $regexp=null, $defaultvalue=null)
	{
		return self::getParam($paramname, $_GET, $regexp, $defaultvalue);
	}

	public static function getPostVar($paramname, $regexp=null, $defaultvalue=null)
	{
		return self::getParam($paramname, $_POST, $regexp, $defaultvalue);
	}

	public static function getRequestVar($paramname, $regexp=null, $defaultvalue=null)
	{
		return self::getParam($paramname, $_REQUEST, $regexp, $defaultvalue);
	}

	public static function getSessionVar($paramname, $regexp=null, $defaultvalue=null)
	{
		return self::getParam($paramname, $_SESSION, $regexp, $defaultvalue);
	}

	public static function getCookie($paramname, $regexp=null, $defaultvalue=null)
	{
		return self::getParam($paramname, $_COOKIE, $regexp, $defaultvalue);
	}
	
	public static function getServerVar($paramname, $regexp=null, $defaultvalue=null)
	{
		return self::getParam($paramname, $_SERVER, $regexp, $defaultvalue);
	}
	
	public static function getServerName()
	{
		$servername = ($_SERVER && array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME']) 
								? $_SERVER['SERVER_NAME']
								: "localhost";
								
		// add 'www.' if it is left out
		if (preg_match('/^\w+\.\w+$/', $servername))
			$servername = 'www.' . $servername;
			
		return $servername;
	}
	
	public static function urlEncode($parameters)
	{
		$paramValues = array();
		
		foreach ($parameters as $key=>$value)
		{
			if (is_string($value))
				$paramValues[] = urlencode($key) . '=' . urlencode($value);
		}
		
		return implode('&', $paramValues);
	}
	
	public static function addParamsToURL($url, $parameters)
	{
		$joinChar = (strpos($url, '?') === FALSE)
						? '?'
						: '&';
						
		return $url . $joinChar . self::urlEncode($parameters);
	}
	
	/**
	 * Sets the HTTP status code
	 *
	 * @param {int} $code
	 */
	public static function setHttpResponseCode($code = NULL) 
	{
		if (is_null($code))
			return (isset($GLOBALS['http_response_code'])) 
				? $GLOBALS['http_response_code'] 
				: 200;

		switch ($code) 
		{
			case 100: $text = 'Continue'; break;
			case 101: $text = 'Switching Protocols'; break;
			case 200: $text = 'OK'; break;
			case 201: $text = 'Created'; break;
			case 202: $text = 'Accepted'; break;
			case 203: $text = 'Non-Authoritative Information'; break;
			case 204: $text = 'No Content'; break;
			case 205: $text = 'Reset Content'; break;
			case 206: $text = 'Partial Content'; break;
			case 300: $text = 'Multiple Choices'; break;
			case 301: $text = 'Moved Permanently'; break;
			case 302: $text = 'Moved Temporarily'; break;
			case 303: $text = 'See Other'; break;
			case 304: $text = 'Not Modified'; break;
			case 305: $text = 'Use Proxy'; break;
			case 400: $text = 'Bad Request'; break;
			case 401: $text = 'Unauthorized'; break;
			case 402: $text = 'Payment Required'; break;
			case 403: $text = 'Forbidden'; break;
			case 404: $text = 'Not Found'; break;
			case 405: $text = 'Method Not Allowed'; break;
			case 406: $text = 'Not Acceptable'; break;
			case 407: $text = 'Proxy Authentication Required'; break;
			case 408: $text = 'Request Time-out'; break;
			case 409: $text = 'Conflict'; break;
			case 410: $text = 'Gone'; break;
			case 411: $text = 'Length Required'; break;
			case 412: $text = 'Precondition Failed'; break;
			case 413: $text = 'Request Entity Too Large'; break;
			case 414: $text = 'Request-URI Too Large'; break;
			case 415: $text = 'Unsupported Media Type'; break;
			case 500: $text = 'Internal Server Error'; break;
			case 501: $text = 'Not Implemented'; break;
			case 502: $text = 'Bad Gateway'; break;
			case 503: $text = 'Service Unavailable'; break;
			case 504: $text = 'Gateway Time-out'; break;
			case 505: $text = 'HTTP Version not supported'; break;
			default:
				exit('Unknown http status code "' . htmlentities($code) . '"');
			break;
		}

		$protocol = (isset($_SERVER['SERVER_PROTOCOL'])) 
			? $_SERVER['SERVER_PROTOCOL'] 
			: 'HTTP/1.0';

		header($protocol . ' ' . $code . ' ' . $text);

		$GLOBALS['http_response_code'] = $code;

		return $code;
	}
}
 



/**
 * UserFeedback
 *
 * @author 	Wouter Diesveld <wouter@tinyqueries.com>
 * @package TinyQueries
 */
class UserFeedback extends \Exception
{
	/**
	 * Constructor
	 *
	 * @param {string} $message
	 * @param {int} $httpCode
	 */
	public function __construct($message = null, $httpCode = 400)
	{
		parent::__construct($message, $httpCode);
	}
};




/**
 * Config
 *
 * @author 	Wouter Diesveld <wouter@tinyqueries.com>
 * @package TinyQueries
 */
class Config
{
	const DEFAULT_CONFIGFILE 	= '../config/config.xml';
	const DEFAULT_COMPILER 		= 'https://compiler1.tinyqueries.com';
	const VERSION_LIBS			= '3.0.7b';

	public $compiler;
	public $database;
	public $project;
	public $postprocessor;
	
	private $configFile;
	
	/**
	 * Constructor
	 *
	 * @param {string} $configFile Optionally you can provide a config file
	 */
	public function __construct($configFile = null)
	{
		$this->configFile = self::file($configFile);
		
		$this->load();
	}
	
	/**
	 * Checks if the given config file exists
	 * If no file is given, checks if the default config file exists
	 *
	 * @param {string} $configFile
	 */
	public static function exists($configFile = null)
	{
		return file_exists( self::file($configFile) );
	}
	
	/**
	 * Returns the config file
	 * If no file is given, return the default file
	 *
	 */
	private static function file($configFile = null)
	{
		return ($configFile)
			? $configFile
			: dirname(__FILE__) . "/" . self::DEFAULT_CONFIGFILE;
	}
	
	/**
	 * Returns the absolute path 
	 *
	 * @param {string} $path
	 */
	public static function pathAbs($path)
	{
		// Check if $path is a relative or absolute path
		$pathAbs = ($path && preg_match('/^\./', $path))
			? realpath( dirname(__FILE__) . "/" . $path )
			: realpath( $path );
			
		if (!$pathAbs)
			throw new \Exception("Cannot find path '" . $path . "'");
			
		return $pathAbs;
	}
	
	/**
	 * Loads the config file
	 *
	 */
	private function load()
	{
		// Check if file exists
		if (!self::exists( $this->configFile ))
			throw new \Exception('Config file \'' . $this->configFile . '\' does not exist');
	
		// Load XML file
		$config = @simplexml_load_file( $this->configFile );
		
		// Check required fields
		if (!$config)						throw new \Exception("Cannot read configfile " . $this->configFile);
		if (!$config->project)				throw new \Exception("Tag 'project' not found in " . $this->configFile);
		if (!$config->project['label'])		throw new \Exception("Field label not found in project tag of " . $this->configFile);
		if (!$config->database)				throw new \Exception("Tag 'database' not found in " . $this->configFile);
		if (!$config->database['name'])		throw new \Exception("Field 'name' not found in database tag of " . $this->configFile);
		if (!$config->database['user'])		throw new \Exception("Field 'user' not found in database tag of " . $this->configFile);
		if (!$config->database['password'])	throw new \Exception("Field 'password' not found in database tag of " . $this->configFile);
		if (!$config->compiler)				throw new \Exception("Tag 'compiler' not found in " . $this->configFile);
		if (!$config->compiler['output'])	throw new \Exception("Field 'output' not found in compiler tag of " . $this->configFile);
		
		// Import project fields
		$this->project = new \StdClass();
		$this->project->label		= (string) $config->project['label'];
		
		// Import database fields
		$this->database = new \StdClass();
		$this->database->driver		= ($config->database['driver']) ? (string) $config->database['driver'] : 'mysql';
		$this->database->host		= ($config->database['host']) ? (string) $config->database['host'] : 'localhost';
		$this->database->port		= ($config->database['port']) ? (string) $config->database['port'] : null;
		$this->database->name		= (string) $config->database['name'];
		$this->database->user		= (string) $config->database['user'];
		$this->database->password	= (string) $config->database['password'];
		$this->database->initQuery	= (string) $config->database['initquery'];
		
		// Import compiler fields
		$this->compiler = new \StdClass();
		$this->compiler->api_key	= (string) $config->compiler['api_key'];
		$this->compiler->input 		= ($config->compiler['input']) ? self::pathAbs( $config->compiler['input'] ) : null;
		$this->compiler->output		= self::pathAbs( (string) $config->compiler['output'] );
		$this->compiler->server		= ($config->compiler['server']) 	? (string) $config->compiler['server'] : self::DEFAULT_COMPILER;
		$this->compiler->version	= ($config->compiler['version']) 	? (string) $config->compiler['version'] : null;
		$this->compiler->logfile	= null;
		$this->compiler->enable 	= ($config->compiler['enable'] && strtolower( (string) $config->compiler['enable'] ) == 'true') ? true : false;
		$this->compiler->autocompile = ($config->compiler['autocompile'] && strtolower( (string) $config->compiler['autocompile'] ) == 'true') ? true : false;
		
		// Add "v" to version if missing
		if ($this->compiler->version && !preg_match("/^v/", $this->compiler->version))
			$this->compiler->version = "v" . $this->compiler->version;
		
		// Logfile needs special treatment 
		if ((string) $config->compiler['logfile']) 
		{
			$path = pathinfo( (string) $config->compiler['logfile'] );
			
			if (!$path || !array_key_exists('dirname', $path))
				throw new \Exception("Configfile " . $this->configFile . ": Path of logfile does not exist");
			
			$dir = realpath( $path['dirname'] );
			
			if (!$dir)
				throw new \Exception("Configfile " . $this->configFile . ": Path of logfile does not exist");
			
			$filename = (array_key_exists('filename', $path))
							? $path['filename'] . "." . $path['extension']
							: 'compiler.log';

			$this->compiler->logfile = $dir . "/" . $filename;
		}

		// Import postprocessor fields
		$this->postprocessor = new \StdClass();
		$this->postprocessor->nest_fields = 
			($config->postprocessor && 
			 $config->postprocessor['nest_fields'] && 
			 strtolower( $config->postprocessor['nest_fields'] ) == 'false')
			? false
			: true;
	}
};





/**
 * Query
 *
 * This class represents a TinyQuery
 *
 * @author 	Wouter Diesveld <wouter@tinyqueries.com>
 * @package TinyQueries
 */
class Query 
{
	const CREATE 	= 'create';
	const READ 		= 'read';
	const UPDATE 	= 'update';
	const DELETE	= 'delete';
	
	public $params;
	public $defaultParam;
	public $children;
	public $operation;
	
	protected $db;
	protected $keys;
	protected $output;
	protected $root;
	protected $orderBy;
	protected $orderType;
	protected $maxResults;
	protected $paramValues;

	/**
	 * Constructor
	 *
	 * @param {DB} $db Handle to database
	 */
	public function __construct($db)
	{
		$this->db 				= $db;
		$this->orderBy			= array();
		$this->children			= array();
		$this->paramValues 		= array();
		$this->keys				= new \StdClass();
		$this->params			= new \StdClass();
		$this->output			= new \StdClass();
		$this->output->key 		= null;
		$this->output->group	= false;
		$this->output->rows 	= "all";
		$this->output->columns 	= "all";
		$this->output->nested 	= null;
		$this->output->fields 	= new \StdClass();
	}

	/**
	 * Sets the query parameter values
	 *
	 * @param {mixed} $paramValues
	 *
	 * @return {Query}
	 */
	public function params( $paramValues )
	{
		// If paramValues is already an assoc, just copy the params
		if (Arrays::isAssoc($paramValues) || is_null($paramValues))
		{
			// Only copy the params which are defined for this query
			foreach ($this->params as $name => $def)
				if (array_key_exists($name, $paramValues))
					$this->paramValues[$name] = $paramValues[$name];
			
			return $this;
		}
		
		// Check if there is a default param
		if ($this->defaultParam)
		{
			$this->paramValues[ $this->defaultParam ] = $paramValues;
			return $this;
		}
		
		// First try to find a param which has no default value
		$n = 0;
		foreach ($this->params as $name => $def)
			if (!property_exists($def, 'default'))
			{
				$n++;
				$paramName = $name;
			}
		
		if ($n > 1)
			throw new \Exception("Cannot call query with one parameter value; query has $n parameters without default value");

		// If none was found, also take into account params having default values	
		if ($n == 0)
			foreach ($this->params as $name => $def)
			{
				$n++;
				$paramName = $name;
			}
			
		if ($n == 0)
			return $this;
		
		if ($n > 1)
			throw new \Exception("Cannot call query with one parameter value; query has $n parameters which have a default value");

		$this->paramValues[ $paramName ] = $paramValues;
		
		return $this;
	}
	
	/**
	 * Set the order 
	 *
	 * @param {string} $orderBy
	 * @param {string} $orderType
	 */
	public function order( $orderBy, $orderType = 'asc')
	{
		$this->orderBy 		= ($orderBy) ? array( $orderBy ) : array();
		$this->orderType	= $orderType;
		
		return $this;
	}
	
	/**
	 * Sets the fieldname which can be used as a key (for example for merging)
	 *
	 * @param {string} $keyField
	 *
	 * @return {Query}
	 */
	public function key( $keyField )
	{
		$this->output->key = $keyField;
		
		return $this;
	}
	
	/**
	 * Returns the field name in the select-part which corresponds to $key;
	 *
	 * @param {string} $key
	 */
	protected function keyField($key)
	{
		if (!property_exists($this->keys, $key))
			return $key;
			
		return is_array( $this->keys->$key ) 
			? "__" . $key 
			: $this->keys->$key;
	}
	
	/**
	 * Collects the values from $rows corresponding to the $key
	 *
	 * @param {string} $key
	 * @param {array} $rows
	 */
	protected function keyValues($key, &$rows)
	{
		if (!property_exists($this->keys, $key))
			throw new \Exception("Key $key is not present in " . $this->name());
		
		$values		= array();	
		$keyField 	= $this->keys->$key;
		
		if (count($rows)==0)
			return $values;
			
		// Simple case, just select the values from the key-column
		if (!is_array($keyField))
		{
			if (!array_key_exists($keyField, $rows[0]))
				throw new \Exception("Field $keyField is not present in rows");
				
			return array_reduce($rows, function ($result, $item) use ($keyField)
			{
				$result[] = $item[$keyField];
				return $result;
			}, array());
				
			return $values;
		}

		// Check existence of each key field
		foreach ($keyField as $field)
			if (!array_key_exists($field, $rows[0]))
				throw new \Exception("Field $field is not present in rows");
		
		// Create an array of arrays
		for ($i=0; $i<count($rows); $i++)
		{
			$value = array();
			foreach ($keyField as $field)
				$value[] = $rows[$i][ $field ];
				
			$values[] = $value;
		}
		
		return $values;
	}
	
	/**
	 * Sets the nested flag to indicate that the output of the query should be nested.
	 * This means that for example sql output fields named 'user.name' and 'user.email' will be converted to 
	 * a nested structure 'user' having fields 'name' and 'email' 
	 *
	 * @param {boolean} $nested
	 */
	public function nested( $nested = true )
	{
		$this->output->nested = $nested;
		
		foreach ($this->children as $child)
			$child->nested( $nested );

		return $this;
	}
	
	/**
	 * Sets whether the output should be grouped by the key
	 * so you get a structure like: { a: [..], b: [..] }
	 *
	 * @param {boolean} $value
	 */
	public function group($value = true)
	{
		$this->output->group = $value;
		
		return $this;
	}
	
	/**
	 * Set the maximum number of results which should be returned (only applies to merge queries)
	 *
	 * @param {int} $maxResults
	 */
	public function max( $maxResults )
	{
		$this->maxResults = $maxResults;
		
		return $this;
	}
	
	/**
	 * Returns the name of the query
	 *
	 */
	public function name()
	{
		if (count($this->children) == 0)
			return null;
			
		return $this->children[0]->name();
	}
	
	/**
	 * Returns the prefix of the query (first part before the dot)
	 *
	 */
	protected function prefix()
	{
		if ($this->root)
			return $this->root;
			
		$name = $this->name();
		
		if (!$name)
			return null;
			
		$parts = explode(".", $name);
		
		return $parts[0];
	}
	
	/**
	 * Adds a parameter binding to the query
	 *
	 * @param {string} $paramName
	 * @param {string} $fieldName 
	 */
	public function bind($paramName, $fieldName = null)
	{
		return $this;
	}
	
	/**
	 * Generic run function 
	 *
	 * @param {assoc} $paramValues
	 */
	public function run($paramValues = null)
	{
		if ($this->operation == self::READ)
			return $this->select($paramValues);
			
		$this->execute($paramValues);
		
		switch ($this->operation)
		{
			case self::CREATE: return "Created item";
			case self::UPDATE: return "Updated item";
			case self::DELETE: return "Deleted item";
		}
	}
	
	/**
	 * Executes the query and returns the result
	 *
	 * @param {assoc} $paramValues
	 */
	public function execute($paramValues = null)
	{
		if (!is_null($paramValues))
			$this->params( $paramValues );
			
		return null;	
	}
	
	/**
	 * Generic select function
	 *
	 * @param {mixed} $paramValues
	 * @param {string} $key (optional) Key field which can be used to group the output
	 * @param {boolean} $cleanUp Do clean up of columns in query output
	 */
	public function select($paramValues = null, $key = null, $cleanUp = true)
	{
		// If no key is supplied take default from JSON spec
		if (is_null($key))
			$key = $this->output->key;
		
		$data = $this->execute($paramValues);
		
		// Keys should not always be cleaned up
		if ($cleanUp)
			$this->cleanUp($data, 'keys', $key);
		
		// Child defs should always be cleaned up
		$this->cleanUp($data, 'childDefs', $key);
		
		// We are ready if output is not an array of assocs
		if ($this->output->columns != 'all' || $this->output->rows != 'all')
			return $data;
			
		// Apply rows2columns transformation
		if (property_exists($this->output, 'rows2columns'))
			$data = Arrays::rows2columns( 
				$data, 
				$this->output->rows2columns->key,
				$this->output->rows2columns->name,
				$this->output->rows2columns->value );
				
		// Apply grouping transformation
		if ($key && $this->output->group)
			return Arrays::groupBy($data, $key, true);
			
		// Apply key transformation
		if ($key)
			return Arrays::toAssoc($data, $key);
				
		return $data;
	}
	
	/**
	 * Generic select function; selects the first row
	 *
	 * @param {mixed} $paramValues
	 * @param {string} $key (optional) Key field which can be used to group the output
	 * @param {boolean} $cleanUp Do clean up of columns in query output
	 */
	public function select1($paramValues = null, $key = null, $cleanUp = true)
	{
		$output = $this->select($paramValues, $key, $cleanUp);
		
		return ($this->output->rows == 'first')
			? $output
			: ((is_array($output) && count($output)>0) 
				? $output[0] 
				: null
			);
	}
	
	/**
	 * Executes the query and attaches the fields to the given object
	 *
	 * @param {object} $object
	 * @param {assoc} $queryParams
	 */
	public function selectToObject(&$object, $queryParams = null)
	{
		if (!is_object($object))
			throw new \Exception('Query::selectToObject - given parameter is not an object');
			
		if ($this->output->columns == 'first')
			throw new \Exception('Query::selectToObject - query does not select all columns');

		if ($this->output->rows == 'all')
			throw new \Exception('Query::selectToObject - query does select all rows');
		
		$record = $this->select( $queryParams );
		
		if (!$record)
			return false;
			
		foreach ($record as $key => $value)
			$object->$key = $value;
			
		return true;
	}
	
	/**
	 * Imports (a part of) a query definition
	 *
	 * @param {object} $query
	 */
	public function import($query)
	{
		if (property_exists($query, 'root'))			$this->root 		= $query->root;
		if (property_exists($query, 'keys'))			$this->keys 		= $query->keys;
		if (property_exists($query, 'params'))			$this->params 		= $query->params;
		if (property_exists($query, 'defaultParam'))	$this->defaultParam	= $query->defaultParam;
		if (property_exists($query, 'operation')) 		$this->operation 	= $query->operation;
		if (property_exists($query, 'maxResults')) 		$this->maxResults 	= $query->maxResults;
		
		if (property_exists($query, 'output'))
		{
			if ($query->output)
			{
				$fields = array('key', 'group', 'rows', 'columns', 'nested', 'fields', 'rows2columns');
				foreach ($fields as $field)
					if (property_exists($query->output, $field) && !is_null($query->output->$field) && $query->output->$field !== '')
						$this->output->$field = $query->output->$field;
				
				// Take default setting from db if nested is not specified
				if (is_null($this->output->nested))
					$this->output->nested = $this->db->nested;
			}
			else
				// This means, the query is an insert, update or delete
				$this->output = false;
		}
			
		return $this;
	}
	
	/**
	 * Basic 'explain' function which shows the query-tree
	 *
	 */
	public function explain($depth = 0)
	{
		$expl = '';
	
		for ($i=0;$i<$depth;$i++)
			$expl .= "  ";
			
		$expl .= "- " . $this->name() . " ";
			
		// Remove main namespace from classname
		$class = explode( "\\", get_class( $this ) );
		array_shift($class);
		$class = implode( "\\", $class );
		
		$expl .= "[" . $class . "]\n";
		
		foreach ($this->children as $child)
			$expl .= $child->explain($depth+1);
			
		return $expl;
	}
	
	/**
	 * Cleans up columns which should not be in the query output
	 *
	 * @param {mixed} $data Query output
	 * @param {string} $type Type of cleaning 'keys' or 'childDefs'
	 * @param {string} $key Key field which should be excluded from clean up
	 */
	protected function cleanUp(&$rows, $type, $key = null)
	{
		if ($this->output->columns == 'first')
			return;
			
		if (!$rows || count($rows) == 0)
			return;			

		if ($this->output->rows == 'first')
			$rows = array( $rows );
		
		$columnsToRemove = array();
		
		switch ($type)
		{
			case 'keys':
				$registeredOutputFields = is_array($this->output->fields)
					? $this->output->fields
					: array_keys( get_object_vars($this->output->fields) );

				// Check which columns can be removed	
				foreach (array_keys($rows[0]) as $field)
					if ($field != $key && $field != $this->output->key && preg_match("/^\_\_/", $field) && !in_array( $field, $registeredOutputFields ))
						$columnsToRemove[] = $field;
				break;
				
			case 'childDefs':
				foreach ($rows[0] as $field => $value)
					if (is_null($value) && $this->output->fields && property_exists($this->output->fields, $field) && property_exists($this->output->fields->$field, 'child'))
						$columnsToRemove[] = $field;
						
				break;
		}
				
		// Do the clean up
		foreach (array_keys($rows[0]) as $field)
			if (in_array($field, $columnsToRemove))
				for ($i=0;$i<count($rows);$i++)
					unset( $rows[$i][$field] ); 
					
		if ($this->output->rows == 'first')
			$rows = $rows[0];
	}
	
	/**
	 * Updates meta info for this query 
	 * (Now only keys & parameters are updated; should be extended with other fields like output-fields etc)
	 */
	protected function update()
	{
		// Only applies for queries with children
		if (count($this->children) == 0)
			return;
		
		// Call update recursively
		foreach ($this->children as $child)
			$child->update();
			
		// Copy parameters from children
		foreach ($this->children as $child)
			foreach ($child->params as $name => $spec)
				$this->params->$name = $spec;
				
		// Copy operation from first child
		$this->operation = $this->children[0]->operation;
	}
	
	/**
	 * Links a list of terms to this query
	 *
	 * @param {array} $terms
	 * @param {boolean} $firstAsRoot
	 */
	protected function linkList($terms, $firstAsRoot)
	{
		$prefix = null;
		
		if ($firstAsRoot)
		{
			$term = array_shift($terms);
			
			// Link first query to get prefix
			$first = $this->link( $term );
			
			$first->update();
			
			$prefix = $first->prefix();
				
			if (!$prefix)
				throw new \Exception("prefix not known for " . $term);
		}
	
		foreach ($terms as $term)
		{
			if ($prefix)
				$term = $prefix . "." . $term;
				
			$this->link( $term );
		}
			
		$this->update();
	}
	
	/**
	 * Connects a query to this query
	 *
	 * @param {string} $term
	 *
	 * @return {Query}
	 */
	protected function link($term)
	{
		$child = $this->db->query($term);

		$this->children[] = $child;

		return $child;
	}

	/**
	 * Checks if the given queries match based on common keys.
	 * If one query is passed, then it is compared with this query
	 *
	 * @param {array|Query} $queries
	 */
	protected function match(&$queries)
	{
		$matching = null;
		
		$list = (is_array($queries))
			? $queries
			: array( $this, $queries );

		foreach ($list as $query)
		{
			$keys = array_keys( get_object_vars( $query->keys ) );
	
			$matching = ($matching)
				? array_intersect( $matching, $keys )
				: $keys;
		}
		
		// Convert back to normal array - array_intersect preserves the indices, which might result in an assoc array
		$matching = array_values($matching);

		if (count($matching) != 1)
			return null;
			
		return $matching[ 0 ];
	}
}





/**
 * Attach
 *
 * This class represents a sequence of attached queries
 *
 * @author 	Wouter Diesveld <wouter@tinyqueries.com>
 * @package TinyQueries
 */
class QueryAttach extends Query
{
	/**
	 * Constructor
	 *
	 * @param {DB} $db Handle to database
	 * @param {string} $terms (optional) 
	 */
	public function __construct($db, $terms = array())
	{
		parent::__construct($db);
		
		$this->linkList($terms, true);
		
		// Get the link key
		list($key) = array_keys( get_object_vars( $this->keys ) );

		// Add key-binding for all children except the first
		for ($i=1; $i<count($this->children); $i++)
			$this->children[$i]->bind($key);
	}

	/**
	 * 'Left joins' the child queries
	 *
	 * @param {assoc} $paramValues
	 */
	public function execute($paramValues = null)
	{
		parent::execute($paramValues);
		
		// Determine the attach key
		$key = ($this->output->key)
			? $this->output->key
			: $this->match( $this->children );
			
		if (!$key)
			throw new \Exception("Cannot attach queries - no common key found");
			
		$n = count($this->children);
		
		if ($n==0)
			return array();
		
		// Take first query as base
		$params		= $this->paramValues;
		$baseQuery	= $this->children[ 0 ];
		$rows 		= $baseQuery->select( $params, null, false );
		$fieldBase	= $baseQuery->keyField($key);
		
		// If there is no output or the number of rows is 0 we can stop
		if (!$rows || count($rows) == 0)
			return $rows;
			
		// Rewrite to array for single row queries
		if ($baseQuery->output->rows == 'first')
			$rows = array( $rows );

		// Collect the key field values and set them as parameter value for the other queries
		$params[ $key ] = $baseQuery->keyValues($key, $rows);

		// Attach all other queries
		for ($i=1; $i<$n; $i++)
		{
			$query 		= $this->children[ $i ];
			$keyField 	= $query->keyField($key);
			$rows1 		= $query->select($params, $keyField, false );
			
			if ($query->output->rows == 'first')
				$rows1 = array( $rows1[$keyField] => $rows1 );
			
			for ($j=0;$j<count($rows);$j++)
			{
				$keyValue = $rows[$j][$fieldBase];
					
				// Attach the fields of $rows1 to $rows
				if (array_key_exists($keyValue, $rows1))
					foreach ($rows1[ $keyValue ] as $name => $value)
						Arrays::mergeField( $rows[$j], $name, $value );
			}
		}
		
		return ($baseQuery->output->rows == 'first')
			? $rows[0]
			: $rows;
	}
	
	/**
	 * Adds a parameter binding to the query
	 *
	 * @param {string} $paramName
	 * @param {string} $fieldName 
	 */
	public function bind($paramName, $fieldName = null)
	{
		// Only bind to first child ('root')
		$this->children[ 0 ]->bind($paramName, $fieldName);
		
		return $this;
	}
	
	/**
	 * Updates meta info for this query 
	 *
	 */
	protected function update()
	{
		parent::update();
		
		// Copy all keys from all children
		$this->keys = new \StdClass();
		foreach ($this->children as $child)
			foreach ($child->keys as $key => $field)
				$this->keys->$key = $field;
		
		// Copy other fields from first child
		$this->root 		= $this->children[ 0 ]->root;
		$this->defaultParam	= $this->children[ 0 ]->defaultParam;
		$this->output		= clone $this->children[ 0 ]->output;
	}
}






/**
 * Filter
 *
 * This class represents a sequence of filter queries
 *
 * @author 	Wouter Diesveld <wouter@tinyqueries.com>
 * @package TinyQueries
 */
class QueryFilter extends Query
{
	// Max number of records which can come out of a filter query
	const MAX_SIZE_FILTER = 5000;
	
	/**
	 * Constructor
	 *
	 * @param {DB} $db Handle to database
	 * @param {string} $terms (optional) 
	 */
	public function __construct($db, $terms = array())
	{
		parent::__construct($db);

		$this->linkList( $terms, true );
		
		// Get the link key
		list($key) = array_keys( get_object_vars( $this->keys ) );

		// Add key-binding for all children except the last
		for ($i=0; $i<count($this->children)-1; $i++)
			$this->children[$i]->bind($key);
	}

	/**
	 * Sets the query parameter values
	 *
	 * @param {mixed} $paramValues
	 *
	 * @return {Query}
	 */
	public function params( $paramValues )
	{
		// If paramValues is already an assoc, just copy it
		if (Arrays::isAssoc($paramValues) || is_null($paramValues))
		{
			$this->paramValues = $paramValues;
			return $this;
		}
		
		$lastChild = $this->children[ count($this->children) - 1 ];
		
		// Pass the single param to the last child
		// This is needed to prevent that the param cannot be matched because there are more than 1 candidates
		// This is typical for a path like "a/1/b" which is translated to $db->get("b:a", 1)
		// Normally the query "a:b" has two non-default params, while "a.b" has one.
		$lastChild->params( $paramValues );
		
		// Copy the values from the last child back to this
		foreach ($lastChild->paramValues as $key => $value)
			$this->paramValues[ $key ] = $value;
	
		return $this;
	}
	
	/**
	 * Filters the output of the child queries
	 *
	 * @param {assoc} $paramValues
	 */
	public function execute($paramValues = null)
	{
		parent::execute($paramValues);
		
		// Determine the chain key
		$key = ($this->output->key)
			? $this->output->key
			: $this->match( $this->children );
			
		if (!$key)
			throw new \Exception("Cannot chain queries - no common key found");
			
		$n = count($this->children);
		
		if ($n==0)
			return array();
		
		// Take last query as base
		$params		= $this->paramValues;
		$baseQuery	= $this->children[ $n-1 ];
		$rows 		= $baseQuery->select( $params, null, false );
		$fieldBase	= $baseQuery->keyField($key);

		// Attach all other queries
		for ($i=$n-2; $i>=0; $i--)
		{
			// If the number of rows is 0 we can stop
			if (count($rows) == 0)
				return $rows;
			
			// Get all values for $rows[0..n][$key]
			$params[$key] 	= $baseQuery->keyValues($key, $rows);
			$query 			= $this->children[ $i ];
			
			// Check to prevent the SQL query to be blown up in size
			if (count($params[$key]) > self::MAX_SIZE_FILTER)
				throw new \Exception("Cannot apply filter query " . $this->name() . "; number of intermediate results is too large - if possible use 'split' option for the parameter");
				
			// Execute query
			$rows1 = $query->select($params, $query->keyField($key), false );
			
			$j = 0;
			$keyValues = array();
			
			// Do an intersection of $rows & $rows1
			while ($j<count($rows))
			{
				$keyValue = $rows[$j][$fieldBase];
					
				if (array_key_exists($keyValue, $rows1))
				{
					// Attach the fields of $rows1 to $rows
					foreach ($rows1[ $keyValue ] as $name => $value)
						$rows[$j][$name] = $value;
					$j++;
					
					// Remember value (needed for next loop)
					$keyValues[] = $keyValue;
				}
				else
				{
					// Remove elements which are not in the latest query result (rows1)
					array_splice( $rows, $j, 1 );
				}
			}
			
			// Add fields to $rows which were not in $rows yet
			// (in general this will not occur, but there are some exceptions, like aggregate queries)
			foreach ($rows1 as $keyValue => $record)
				if (!in_array($keyValue, $keyValues))
					$rows[] = $record;
		}
		
		return $rows;
	}

	/**
	 * Adds a parameter binding to the query
	 *
	 * @param {string} $paramName
	 * @param {string} $fieldName 
	 */
	public function bind($paramName, $fieldName = null)
	{
		// Only bind to first child ('root')
		$this->children[ 0 ]->bind($paramName, $fieldName);
		
		return $this;
	}
	
	/**
	 * Updates meta info for this query 
	 *
	 */
	protected function update()
	{
		parent::update();
		
		// Copy all keys from all children
		$this->keys = new \StdClass();
		foreach ($this->children as $child)
			foreach ($child->keys as $key => $field)
				$this->keys->$key = $field;
		
		// Copy root from first child
		$this->root = $this->children[ 0 ]->root;
		
		// Copy defaultParam from last child
		$lastChild = $this->children[ count($this->children) - 1 ];
		$this->defaultParam = $lastChild->defaultParam;
	}
}






/**
 * Merge
 *
 * This class represents a sequence of merge queries
 *
 * @author 	Wouter Diesveld <wouter@tinyqueries.com>
 * @package TinyQueries
 */
class QueryMerge extends Query
{
	/**
	 * Constructor
	 *
	 * @param {DB} $db Handle to database
	 * @param {string} $terms (optional) 
	 */
	public function __construct($db, $terms = array())
	{
		parent::__construct($db);
		
		$this->linkList($terms, false);
	}

	/**
	 * Merges the output of the child queries
	 *
	 * @param {assoc} $paramValues
	 */
	public function execute($paramValues = null)
	{
		parent::execute($paramValues);
		
		// Determine the merge key
		$mergeKey = ($this->output->key)
			? $this->output->key
			: $this->match( $this->children );

		$orderBy = (count($this->orderBy) == 1)
					? $this->orderBy[0]
					: null;
		
		$result = ($mergeKey)
			? $this->mergeByKey( $mergeKey, $orderBy )
			: $this->mergePlain( $orderBy );
			
		if ($this->maxResults)
		{
			if (Arrays::isAssoc($result))
				Arrays::spliceAssoc($result, (int) $this->maxResults);
			else
				array_splice($result, $this->maxResults);
		}
		
		// If there is orderBy, the result should be an index-based array
		// However, if there is also an key, the intermediate result should be an associative array, which is needed for correct merging
		// So in this case, the intermediate result should be converted to an index-based array
		if ($this->output->key && count($this->orderBy)==1 && Arrays::isAssoc($result))
			$result = Arrays::toIndexed($result); 
		
		return $result;
	}

	/**
	 * Adds a parameter binding to the query
	 *
	 * @param {string} $paramName
	 * @param {string} $fieldName 
	 */
	public function bind($paramName, $fieldName = null)
	{
		// Do recursive call on children
		foreach ($this->children as $child)
			$child->bind($paramName, $fieldName);
				
		return $this;
	}
	
	/**
	 * Merges the output of the child queries without a key
	 *
	 */
	private function mergePlain($orderBy)
	{
		$result = array();
		
		foreach ($this->children as $query)
		{
			$rows = $query->params( $this->paramValues )->select();
			Arrays::mergeArrays( $result, $rows, $orderBy, $this->orderType );
		}
		
		return $result;
	}
	
	/**
	 * Merges the output of the child queries by using a common key
	 *
	 * @param {string} $key
	 */
	private function mergeByKey($key, $orderBy)
	{
		$result = array();
		
		foreach ($this->children as $query)
		{
			$rows = $query->select( $this->paramValues, $query->keyField($key), false );
				
			Arrays::mergeAssocs( $result, $rows, $orderBy, $this->orderType );
		}

		// Convert 'back' to indexed array in case the main query has no output key
		if (!$this->output->key)
			return Arrays::toIndexed($result);
		
		return $result;
	}
	
	/**
	 * Updates meta info for this query 
	 *
	 */
	protected function update()
	{
		parent::update();
		
		// Copy all keys from all children
		$this->keys = new \StdClass();
		foreach ($this->children as $child)
			foreach ($child->keys as $key => $field)
				$this->keys->$key = $field;
				
		// Copy default param only if every child has the same default param
		$defaultParam = $this->children[0]->defaultParam;
		$diff = false;
		foreach ($this->children as $child)
			if ($child->defaultParam != $defaultParam)
				$diff = true;
		
		if (!$diff)
			$this->defaultParam = $defaultParam;
	}
}




/**
 * Arrays
 *
 * @author 	Wouter Diesveld <wouter@tinyqueries.com>
 * @package TinyQueries
 */
class Arrays
{
	/**
	 * Checks if an array is an assocative array
	 *
	 * @param {mixed} $array
	 */
	public static function isAssoc(&$array)
	{
		return (is_array($array) && array_keys($array) !== range(0, count($array) - 1)) 
					? true 
					: false;
	}
	
	/**
	 * Converts a nummerical array to an associative array, based on the given key
	 *
	 * @param {array} $rows
	 * @param {string} $key;
	 */
	public static function toAssoc($rows, $key)
	{
		$assocArray = array();

		// Check if key is present
		if (count($rows)>0 && !array_key_exists( $key, $rows[0] ))
			throw new \Exception("Arrays::toAssoc: key '$key' not present in rows'");
			
		// Build the new array
		foreach ($rows as $row)
			$assocArray[ $row[ $key ] ] = $row; 
			
		return $assocArray;
	}
	
	/**
	 * Groups the rows on the given key
	 *
	 * @param {array} $rows
	 * @param {string} $key
	 * @param {boolean} $deleteKey If true, removes the key-column from the result
	 */
	public static function groupBy($rows, $key, $deleteKey = false)
	{
		$assocArray = array();

		// Check if key is present
		if (count($rows)>0 && !array_key_exists( $key, $rows[0] ))
			throw new \Exception("Arrays::groupBy: key '$key' not present in rows'");
			
		// Build the new array
		foreach ($rows as $row)
		{
			$keyValue = $row[ $key ];
			
			if ($deleteKey)
				unset( $row[ $key ] );

			// Create empty array if keyValue is not yet present
			if (!array_key_exists($keyValue, $assocArray))
				$assocArray[ $keyValue ] = array();
			
			// Push row 
			$assocArray[ $keyValue ][] = $row; 
		}
			
		return $assocArray;
	}
	
	/**
	 * Converts a associative array to an numerical array
	 *
	 * @param {array} $rows
	 */
	public static function toIndexed($assoc)
	{
		$indexBased = array();
		foreach ($assoc as $_dummy => $value)
			$indexBased[] = $value;
	
		return $indexBased;
	}
	
	/**
	 * Merges two numerical arrays based on an the order of a common field (which is denoted by $orderBy)
	 *
	 * @param &$array {array} Array which will be modified by adding element of the next array:
	 * @param $arrayToAdd {array}
	 * @param {string} $orderBy (optional) Name of the field which should be used for ordering the merged result
	 * @param {string} $orderType (optional) 'asc' or 'desc'; default is 'asc'
	 */
	public static function mergeArrays(&$array, $arrayToAdd, $orderBy = null, $orderType = 'asc')
	{ 
		if (!$orderBy)
		{
			// Simply add the array
			$array = array_merge($array, $arrayToAdd);
			return;
		}
		
		/// TODO this algorithm can be made much faster if it can be assumed that each query returns sorted output
		foreach ($arrayToAdd as $item)
		{
			// find position in the objects-array where the item should be added
			
			$k = 0;
				
			while (	$k < count( $array ) && 
					(
						($orderType == 'asc' 	&& $array[$k][ $orderBy ] < $item[ $orderBy ]) ||
						($orderType == 'desc' 	&& $array[$k][ $orderBy ] > $item[ $orderBy ]) 
					)
				)
				{
					$k++;
				}
			
			if ($k < count( $array ))
				array_splice( $array, $k, 0, array( $item ) );
			else
				$array[] = $item;
		}
	}
	
	/**
	 *
	 */
	public static function spliceAssoc(&$input, $offset, $length = null, $replacement = array()) 
	{
		if (is_null( $length ))
			$length = count($input);
		
        $replacement = (array) $replacement;
        $key_indices = array_flip(array_keys($input));
        if (isset($input[$offset]) && is_string($offset)) {
                $offset = $key_indices[$offset];
        }
        if (isset($input[$length]) && is_string($length)) {
                $length = $key_indices[$length] - $offset;
        }

        $input = array_slice($input, 0, $offset, TRUE)
                + $replacement
                + array_slice($input, $offset + $length, NULL, TRUE);
	}

	/**
	 * Merges the $key - $value pair into $assoc
	 * Simple case is that $value is just a string or int
	 * But it can also be the case that $value is an assoc and that $assoc[$key] is also an assoc
	 *
	 * @param {assoc} $assoc
	 * @param {string} $key
	 * @param {string|int|assoc} $value
	 */
	public static function mergeField( &$assoc, $key, $value )
	{
		// Simple case; key is not present
		if (!array_key_exists($key, $assoc))
		{
			// Create entry
			$assoc[$key] = $value;
			return;
		}

		// Special cases
		if (is_null($value))
			return;

		if (is_null($assoc[$key]))
		{
			$assoc[$key] = $value;
			return;
		}
		
		$a1 = self::isAssoc( $assoc[$key] );
		$a2 = self::isAssoc( $value );
		
		if (!$a1 && !$a2)
		{
			// Overwrite entry
			$assoc[$key] = $value;
			return;
		}
		
		if ($a1 && $a2)
		{
			// Do recursive call
			foreach ($value as $subkey => $subvalue)
				self::mergeField( $assoc[$key], $subkey, $subvalue );
				
			return;
		}
		
		throw new \Exception("Cannot merge field '$key' - types are different");
	}
	
	/**
	 *
	 */
	public static function mergeAssocs(&$array, $arrayToAdd, $orderBy = null, $orderType = 'asc')
	{ 
		foreach ($arrayToAdd as $idToAdd => $itemToAdd)
		{
			// element already exists
			if (array_key_exists($idToAdd, $array))
			{
				// copy and/or add the fields of the array-element
				foreach (array_keys($itemToAdd) as $field)
					self::mergeField( $array[ $idToAdd ], $field, $itemToAdd[ $field ] );
			}
			// element does not exist and elements should be ordered
			elseif ($orderBy)
			{
				// find position in the array where the item should be added
				$k = 0;
				
				foreach ($array as $id => $item)
				{	
					if (
							($orderType == 'asc' 	&& $item[ $orderBy ] < $itemToAdd[ $orderBy ]) ||
							($orderType == 'desc' 	&& $item[ $orderBy ] > $itemToAdd[ $orderBy ])
						)
						$k++;
					else
						break;
				}
				
				if ($k < count($array))
					self::spliceAssoc( $array, $k, 0, array( $idToAdd => $itemToAdd ) );
				else
					$array[ $idToAdd  ] = $itemToAdd;
			}
			// element does not exist and elements are not ordered
			else
			{
				// just add the new field
				$array[ $idToAdd  ] = $itemToAdd;
			}
		}
	}
	
	public static function objectToArray($d) 
	{
		if (is_object($d)) {
			// Gets the properties of the given object
			// with get_object_vars function
			$d = get_object_vars($d);
		}
 
		if (is_array($d)) {
			/*
			* Return array converted to object
			* Using __FUNCTION__ (Magic constant)
			* for recursive call
			*/
			return array_map( 'self::objectToArray', $d);
		}
		else {
			// Return array
			return $d;
		}
	}
	
	/**
	 * Recursive function to remove structures like { a: null, b: null, c: { d: null, e: null } }
	 *
	 * @param {assoc} $fields
	 */
	public static function reduceNulls(&$fields)
	{
		if (!self::isAssoc($fields) && !is_object($fields))
			return;
			
		// First do recursive call
		foreach ($fields as $key => $value)
			(is_object($fields))
				? self::reduceNulls( $fields->$key )
				: self::reduceNulls( $fields[$key] );
				
		// Check if there are non null values
		foreach ($fields as $key => $value)
			if (!is_null($value))
				return;
			
		// If not, then reduce the $fields to null
		$fields = null;
	}
	
	/**
	 * Transforms rows to columns
	 *
	 * @param {array} $array Array of associative arrays
	 * @param {string} $key 
	 * @param {string} $name
	 * @param {string} $value
	 */
	public static function rows2columns(&$array, $key, $name, $value)
	{
		$trans = array();
		
		foreach ($array as $row)
		{
			$id = "id" . $row[ $key ];
			
			if (!array_key_exists($id, $trans))
				$trans[ $id ] = array
				(
					$key => $row[ $key ]
				);
				
			$trans[ $id ][ $row[ $name ] ] = $row[ $value ];
		}
		
		return self::toIndexed( $trans );
	}
	
	/**
	 * Makes an array of $any if it is not yet an array
	 *
	 * @param {mixed} $any
	 */
	public static function toArray( $any )
	{
		if (is_array($any))
			return $any;
			
		if (is_null($any))
			return array();
			
		return array( $any );
	}
} 
 





/**
 * SQL
 *
 * This class represents one SQL query
 *
 * @author 	Wouter Diesveld <wouter@tinyqueries.com>
 * @package TinyQueries
 */
class QuerySQL extends Query
{
	public $id;
	
	private $sql;
	protected $_interface;

	/**
	 * Constructor
	 *
	 * @param {DB} $db Handle to database
	 * @param {string} $id (optional) ID of the query
	 */
	public function __construct($db, $id = null)
	{
		parent::__construct($db);
		
		$this->id = $id;
		
		$this->load();
	}

	/**
	 * Loads the JSON and/or SQL files which correspond to this query
	 *
	 */
	public function load()
	{
		if (!$this->id)
			return $this;
	
		// Already loaded
		if ($this->_interface)
			return $this;
		
		$this->import( $this->getInterface() );
		
		return $this;
	}
	
	/**
	 * Returns the name of the query
	 *
	 */
	public function name()
	{
		return $this->id;
	}
	
	/**
	 * Executes the query
	 *
	 * @param {assoc} $queryParams
	 */
	public function execute($paramValues = null)
	{
		parent::execute($paramValues);
		
		$this->getInterface();

		// Check if there is a split defined for a parameter
		$paramSplit = null;
		foreach ($this->params as $paramID => $def)
			if (property_exists($this->params->$paramID, 'split') && is_array($this->paramValues[ $paramID]))
			{
				$buffersize = $this->params->$paramID->split;
				$paramSplit = $paramID;
			}
		
		if (!$paramSplit)
			return $this->executeHelper($this->paramValues);
		
		// Split parameter value
		$output 	= array();
		$nValues 	= count( $this->paramValues[ $paramSplit ] );
		
		for ($k=0; $k<$nValues; $k+=$buffersize)
		{
			// Create parameter value set for the current buffer
			$paramsBuffer = array();
			foreach ($this->paramValues as $key => $value)
				$paramsBuffer[$key] = ($key==$paramSplit)
					? array_slice($value, $k, $buffersize)
					: $value;
					
			$output = array_merge( $output, $this->executeHelper($paramsBuffer) );
		}	
		
		return $output;
	}
	
	/**
	 *
	 *
	 */
	private function executeHelper($paramValues)
	{
		try
		{
			// If the query has no output just execute it
			if (!$this->output)
			{
				list($sql, $pdoParams) = $this->getSql( $paramValues );
				return $this->db->execute( $sql, $pdoParams );
			}
			
			$rows = (string) $this->_interface->output->rows;
			$cols = (string) $this->_interface->output->columns;
			
			// Determine which select function should be used
			if ($rows == "first" && $cols == "first")	return $this->selectFirst( $paramValues );
			if ($rows == "first" && $cols == "all")		return $this->selectAssoc( $paramValues );
			if ($rows == "all" 	 && $cols == "first")	return $this->selectAllFirst( $paramValues );
			if ($rows == "all" 	 && $cols == "all")		return $this->selectAllAssoc( $paramValues );
			
			// Default:
			return $this->selectAllAssoc( $paramValues );
		}
		catch (\Exception $e)
		{
			throw new \Exception("SQL error for query " . $this->id . ": " . $e->getMessage());
		}
	}
	
	/**
	 *
	 *
	 * @param {assoc} $queryParams
	 */
	public function selectAllAssoc($queryParams = null)
	{
		list($sql, $pdoParams) = $this->getSql( $queryParams );
	
		$rows = $this->db->selectAllAssoc( $sql, $pdoParams );
		
		$this->postProcess( $rows );
			
		return $rows;
	}

	/**
	 *
	 *
	 * @param {assoc} $queryParams
	 */
	public function selectAssoc($queryParams = null)
	{
		list($sql, $pdoParams) = $this->getSql( $queryParams );
		
		$rows = array( $this->db->selectAssoc( $sql, $pdoParams ) );
		
		$this->postProcess( $rows );
		
		return $rows[0];
	}
	
	/**
	 *
	 *
	 * @param {assoc} $queryParams
	 */
	public function selectAllFirst($queryParams = null)
	{
		list($sql, $pdoParams ) = $this->getSql( $queryParams );
		
		$rows = $this->db->selectAllFirst( $sql, $pdoParams );
		
		$this->postProcess( $rows );
		
		return $rows;
	}
	
	/**
	 *
	 *
	 * @param {assoc} $queryParams
	 */
	public function selectFirst($queryParams = null)
	{
		list($sql, $pdoParams ) = $this->getSql( $queryParams );
		
		$rows = array( array( $this->db->selectFirst( $sql, $pdoParams ) ) );
		
		$this->postProcess( $rows );
		
		return $rows[0][0];
	}
	
	/**
	 * Does all post processing for the output of a query
	 *
	 * @param {array} $rows The rows as returned by the database
	 */
	private function postProcess(&$rows)
	{
		// If array is empty we are ready
		if (count($rows)==0)
			return;
			
		// If array consists of numerical arrays, we are ready (child queries only make sense for associative arrays)
		if (!Arrays::isAssoc($rows[0]))
			return;
			
		$this->db->profiler->begin('query::postprocess');	

		// Apply nesting
		$this->nestDottedFields($rows);
	
		// Apply typing
		$this->applyTyping($rows);
					
		// Do custom callback
		if ($callback = $this->db->queries->callback($this->id))
			$callback( $rows );
			
		$this->db->profiler->end();	
	}
	
	/**
	 * Sets the type of the output fields, according to the type specification in the json file
	 *
	 * @param {array} $rows
	 */
	private function applyTyping(&$rows)
	{
		foreach ($this->_interface->output->fields as $name => $type)
			if ($type != 'string')
				for ($i=0;$i<count($rows);$i++)
					$this->setType($rows[$i][$name], $type);
	}
	
	/**
	 * Do type casting for the given field
	 *
	 * @param {string} $field
	 * @param {string} $type
	 */
	private function setType(&$field, $type)
	{
		if (is_null($field))
			return;
			
		// Type can either be a string or an object
		$typeStr = (is_object($type) && property_exists($type, 'type'))
			? $type->type
			: $type;
		
		switch ($typeStr)
		{
			// Basic type casting
			case 'int': 	$field = (int) $field; break;
			case 'float': 	$field = (float) $field; break;
			case 'number': 	$field = (float) $field; break;
			case 'string':	$field = (string) $field; break;
			
			// Recursive call for object properties
			case 'object':
				foreach ($type->fields as $name => $subtype)
					$this->setType($field[$name], $subtype);
				break;
				
			// Child queries are handled by QueryTree
			case 'child':
				break;
			
			// JSON should only be decoded
			case 'json': 	
				$field = json_decode( $field ); 
				self::fixGroupConcatArray( $field );
				break;
				
			// Unknown type, do nothing
			default:
				break;
		}
	}
	
	/**
	 * Converts dot-notation fields to a nested structure.
	 * 
	 * @param {array} $rows
	 */
	private function nestDottedFields(&$rows)
	{
		// If nesting is not set, we are ready
		if (!$this->output->nested)
			return;
		
		// If there are no rows we are ready		
		if (count($rows) == 0)
			return;
			
		$keys 		= array_keys( $rows[0] ); 
		$mapping	= array();
		
		// Split dotted fieldnames
		foreach ($keys as $key)
		{
			$map = explode('.', $key);
			if (count($map) > 1)
				$mapping[ $key ] = $map;
		}
		
		// Apply nesting for each row
		foreach ($mapping as $key => $map)
			for ($i=0; $i<count($rows); $i++)
			{
				// These are some shortcuts for faster processing (nestField does the same but is slower)
				switch (count($map))
				{
					case 2: $rows[$i][$map[0]][$map[1]] = $rows[$i][$key]; break;
					case 3: $rows[$i][$map[0]][$map[1]][$map[2]] = $rows[$i][$key]; break;
					case 4: $rows[$i][$map[0]][$map[1]][$map[2]][$map[3]] = $rows[$i][$key]; break;
					case 5: $this->nestField( $rows[$i], $map, $rows[$i][$key] ); break;
				}
				
				unset( $rows[$i][$key] );
			}
			
		// Check for null objects, which are caused by 'empty' left joins
		$nestedFields = array();
		foreach ($rows[0] as $key => $value)
			if (Arrays::isAssoc($value) && count($value)>0)
				$nestedFields[] = $key;
		
		foreach ($nestedFields as $field)
			for ($i=0; $i<count($rows); $i++)
				Arrays::reduceNulls( $rows[$i][$field] );
	}
	
	/**
	 * Helper function for nestDottedFields
	 *
	 * @param {assoc} $row
	 * @param {array} $fieldComponents
	 * @param {string} $value
	 */
	private function nestField(&$row, $fieldComponents, $value)
	{
		$head = array_shift( $fieldComponents );
		
		// If last field component
		if (count($fieldComponents) == 0)
		{
			$row[ $head ] = $value;
			return;
		}
		
		// Recursive call
		$this->nestField($row[ $head ], $fieldComponents, $value);
	}
	
	/**
	 * Loads the interface if not yet loaded
	 *
	 */
	public function getInterface()
	{
		if (!$this->id)
			throw new \Exception('getInterface: Query ID not known');

		if ($this->_interface)
			return $this->_interface;

		$this->_interface = $this->db->queries->getInterface( $this->id );
		
		return $this->_interface;
	}
	
	/**
	 * Sets the interface for this query
	 *
	 */
	public function setInterface($params, $output)
	{
		if (!$this->_interface)
			$this->_interface = new \StdClass();
			
		$this->_interface->params = $params;
		$this->_interface->output = $output;
	}
	
	/**
	 * Sets the SQL code for this query
	 */
	public function setSql($sql)
	{
		$this->sql = $sql;
	}
	
	/**
	 * Reads query-file and fills in the IN-parameters - 
	 * other params will be converted to PDO params which can be passed to the select methods (which is faster)
	 *
	 * @param {array} $params query parameters
	 */
	public function getSql($params = array())
	{
	// TODO: parameter gedeelte in andere functie
	// sql($sql) get/set van maken
	
		$pdoParams = array();
		
		if (is_null($params))
			$params = array();
		
		if (!$this->id)
			throw new \Exception('sql: Query ID not known');

		// Read interface if it not yet known
		if (!$this->_interface)
			$this->getInterface();
		
		// Read compiled SQL if there is no SQL yet
		if (!$this->sql)
			$this->sql = $this->db->queries->sql( $this->id );
			
		$sqlParsed = $this->sql;	
		
		// Set defaults (only if not present in given param list)
		foreach ($this->_interface->params as $p => $props)
			if (!array_key_exists($p, $params))
				if (property_exists($props, 'default'))
					$params[ $p ] = $props->{'default'};
			
		// Add global parameters (only if not present in given param list)
		foreach ($this->db->globals as $p => $val) 
			if (!array_key_exists($p, $params) || is_null($params[$p]))
				$params[ $p ] = $val;
		
		// Special handling for paging parameters
		if (property_exists($this->_interface, 'paging'))
		{
			$page = (array_key_exists('page', $params)) ? $params['page'] : 0;
			unset($params['page']); // unset page param because it is not present in the SQL itself
			$params['__limitStart'] = $page * (int) $this->_interface->paging;
		}

		// Set the parameters
		foreach ($params as $name => $value)
			// Convert array to CSV which is suitable for IN
			if (is_array($value))
			{
				$this->setArrayParam($sqlParsed, $name, $value);
			}
			// Param is a registered parameter
			elseif (property_exists($this->_interface->params, $name))
			{
				switch ($this->_interface->params->$name->type)
				{
					case "int": $pdoType = \PDO::PARAM_INT; break;
					default:	$pdoType = \PDO::PARAM_STR; break;
				}
			
				$pdoParams[ $name ] = array
				(
					'value'	=> $value,
					'type'	=> $pdoType
				);
			}
			// Param is not registered (DEPRECATED - but still needed for global params)
			else
			{
				$valueSQL = $this->db->toSQL( $value, true );
				$this->setParam($sqlParsed, $name, $valueSQL);
			}

		return array($sqlParsed, $pdoParams);	
	}
	
	/**
	 * Helper function to convert parameters which are arrays into a format suitable to be used in the query
	 *
	 * @param {string} $sql
	 * @param {string} $name Parameter name
	 * @param {array} $value Parameter value
	 */
	private function setArrayParam(&$sql, $name, $value)
	{
		$values = array();
		
		// In case $value is an array of arrays, create tuples like (1,2,3)
		if (count($value)>0 && is_array($value[0]))
			return $this->setTupleParam($sql, $name, $value);
		
		foreach ($value as $v)
			$values[] = $this->db->encode( $v );
		
		$this->setParam($sql, $name, $values);
	}
	
	/**
	 * Helper function to convert parameters which are arrays of tuples into a format suitable to be used in the query
	 *
	 * @param {string} $sql
	 * @param {string} $name Parameter name
	 * @param {array} $value Parameter value
	 */
	private function setTupleParam(&$sql, $name, $value)
	{
		$tuples = array();
		$values = array();
		
		// Init array $values
		foreach ($value[0] as $i => $v)
			$values[$i] = array();
		
		
		// Create the tuples, but also collect the separate values
		foreach ($value as $v)
		{
			$tuple = array();
			
			foreach ($v as $i => $w)
			{
				$encval = $this->db->encode( $w );
				
				$values[$i][] 	= $encval;
				$tuple[] 		= $encval;
			}
				
			$tuples[] = "(" . implode(",", $tuple) . ")";
		}
		
		// Set parameters $name[0], $name[1] etc.
		for ($i=0; $i<count($values); $i++)
			$this->setParam($sql, $name . "\[" . $i . "\]", $values[$i]);
		
		$this->setParam($sql, $name, $tuples);
	}
	
	/**
	 * Replace the ":param" string with the value
	 *
	 * @param {string} $sql
	 * @param {string} $name Parameter name
	 * @param {mixed} $value SQL encoded parameter value or array of SQL encoded parameter values
	 */
	private function setParam(&$sql, $name, $value)
	{
		if (is_array($value))
			$value = (count($value)==0)
				? "NULL"
				: implode(",", $value);
			
		$sql = preg_replace("/\:" . $name . "(\W)/", $value . "$1", $sql . " ");
	}
	
	/**
	 * Workaround for groupconcat 'bug': when the groupconcat is based on a left join, the resulting array can 
	 * contain 1 (empty) element while you would expect is has 0 elements.
	 * This function checks for this special case, and ensures that $array is empty
	 */
	public static function fixGroupConcatArray(&$array)
	{
		if (!is_array($array))
			return;
			
		if (count($array) != 1)
			return;
			
		Arrays::reduceNulls( $array[0] );
		
		if (is_null($array[0]))
			$array = array();
	}
}





/**
 * Tree
 *
 * This class represents a tree of queries
 *
 * @author 	Wouter Diesveld <wouter@tinyqueries.com>
 * @package TinyQueries
 */
class QueryTree extends Query
{
	private $base; // Base query (actually the 'root' of the tree)
	
	/**
	 * Constructor
	 *
	 * @param {DB} $db Handle to database
	 * @param {string} $id ID of parent query - $id should refer to an atomic query
	 * @param {string} $terms Query terms corresponding to the child queries of the tree
	 */
	public function __construct($db, $id, $terms = array())
	{
		parent::__construct($db);
		
		// Create the root query
		$this->base = $this->db->query($id);
		
		// Ensure root query fields are copied to this query
		$this->update();
		
		// Check for child aliases
		$aliases = array();
		
		foreach ($this->output->fields as $field => $spec )
			if (property_exists($spec, 'child') && $spec->child != $field)
				$aliases[ $field ] = $spec->child;
		
		$terms = Term::convertAliases( $terms, $aliases );
		
		// Add root or id as filter to each child
		$linkID = $this->prefix();
		
		if (!$linkID)
			throw new \Exception("Query '$id' has no prefix");
			
		for ($i=0;$i<count($terms);$i++)
			$terms[$i] = "(" . $terms[$i] . "):" . $linkID; 
		
		// Create a child query for each term
		$this->linkList( $terms, false );
	}
	
	/**
	 * Returns the name of the query
	 *
	 */
	public function name()
	{
		return $this->base->name();
	}
	
	/**
	 * Adds a parameter binding to the query
	 *
	 * @param {string} $paramName
	 * @param {string} $fieldName 
	 */
	public function bind($paramName, $fieldName = null)
	{
		// Only bind to root
		$this->base->bind($paramName, $fieldName);
				
		return $this;
	}	
	
	/**
	 * Executes the query
	 *
	 * @param {assoc} $paramValues
	 */
	public function execute($paramValues = null)
	{
		parent::execute($paramValues);

		$data = $this->base->select( $this->paramValues );
		
		$this->bindChildren($data);
		
		return $data;
	}	
	
	/**
	 * Connects a query to this query
	 *
	 * @param {string} $term
	 *
	 * @return {Query}
	 */
	protected function link($term)
	{
		$child = parent::link($term);

		// If parent is compiled we are ready (child specs are set by compiler)
		if (get_class($this->base) == "TinyQueries\\QuerySQL")
			return $child;
		
		// Find the matching key between parent & child
		$queries = array( $this->base, $child );
		$matchKey = $this->match( $queries );

		if (!$matchKey)
			throw new \Exception("Tree::link - cannot link query; there is no unique matching key for '" . $this->base->name() . "' and '" . $child->name() . "'");

		$parentKey 		= $this->keys->$matchKey;
		$childKey		= $child->keys->$matchKey;
		$parentKeyAlias = "__parentKey-" . count($this->children);
		$childKeyAlias 	= $matchKey;

		// Add parentKey to select
		$this->base->addSelect($parentKey, $parentKeyAlias);
				
		// Create child definition which is compatible with the one used for compiled queries
		$childDef = new \StdClass();
		$childDef->type  		= 'child';
		$childDef->child 		= $child->name();
		$childDef->parentKey 	= $parentKeyAlias;
		$childDef->childKey		= $childKeyAlias;
		$childDef->params 		= new \StdClass();
		$childDef->params->$matchKey = $parentKeyAlias;
		
		$this->output->fields->{$child->name()} = $childDef;
		
		// Modify child such that it can be linked to the parent
		$child->bind( $matchKey, $childKey );
		
		return $child;
	}

	/**
	 * Updates meta info for this query 
	 * (Now only keys & parameters are updated; should be extended with other fields like output-fields etc)
	 */
	protected function update()
	{
		// Update base query
		$this->base->update();
		
		// Copy fields from parent (base)
		$fields = array('root', 'params', 'keys', 'defaultParam', 'operation');
		foreach ($fields as $field)
			$this->$field = is_object($this->base->$field)
				? clone $this->base->$field
				: $this->base->$field;
		
		// Output fields need special care - not all fields should be copied
		$outputToCopy = array('rows', 'columns', 'fields');
		foreach ($outputToCopy as $field)
			$this->output->$field = is_object($this->base->output->$field)
				? clone $this->base->output->$field
				: $this->base->output->$field;
	}
	
	/**
	 * Binds the child queries to the query output
	 *
	 * @param {array} $rows The rows/row as returned by QuerySQL
	 */
	private function bindChildren(&$rows)
	{
		// In this case child binding does not apply
		if ($this->output->columns == 'first')
			return;
		
		if (!$rows || count($rows) == 0)
			return;
	
		if ($this->output->rows == 'first')
			$rows = array( $rows );
			
		foreach ($this->children as $child)
			$this->bindChild($rows, $child);
		
		if ($this->output->rows == 'first')
			$rows = $rows[ 0 ];
	}

	/**
	 * Executes the child query and ties the result to the output of the parent query
	 *
	 * @param {array} $parentRows Query output of the parent query
	 * @param {object} $child
	 */
	private function bindChild(&$parentRows, &$child)
	{
		$generalErrorMessage = "Cannot nest queries " . $this->name() . " and " . $child->name() . " - ";
		
		// This error should never occur, since the parser constructs the two childs automatically
		if (!$child->children || count($child->children) != 2)
			throw new \Exception($generalErrorMessage . "child does not have 2 children");
	
		$paramID = $child->defaultParam;
		
		// Fall back in case there is no default param (should not occur anymore)
		if (!$paramID)
		{
			// Get parameters of second (last) child of $child. 
			// Suppose you have "a(b)". This corresponds to parent = "a" and child = "b:a"
			// "b:a" has two childs: "b" and "b.a"
			// We should get the param of the link-query "b.a" which is the second child of $child
			$paramIDs = array_keys( get_object_vars( $child->children[1]->params ) );
			
			// There should be exactly 1 parameter
			if (count($paramIDs) != 1)
				throw new \Exception($generalErrorMessage . "link-query " . $child->children[1]->name() . " should have exactly one parameter");

			$paramID = $paramIDs[0];	
		}
		
		// Get the parent key which should be matched with the childs parameter
		$keyIDs = array_keys( get_object_vars( $this->keys ) );
		
		if (count($keyIDs) != 1)
			throw new \Exception($generalErrorMessage . "parent should have exactly one key");
		
		$parentKey 	= $this->keys->{$keyIDs[0]};	
		
		if (!is_array($parentRows))
			throw new \Exception($generalErrorMessage . "parentRows should be an array of associative arrays");

		if (count($parentRows)>0 && !is_array($parentRows[0]))
			throw new \Exception($generalErrorMessage . "parentRows should be an array of associative arrays");
		
		if (count($parentRows)>0 && !array_key_exists($parentKey, $parentRows[0]))
			throw new \Exception($generalErrorMessage . "parentRows should consist of associative arrays containing the field '".$parentKey."'");
			
		// Take root parameters as default params for child
		$params	= $this->paramValues; 
		
		// Select the child param values from the parent query output
		$values = array();
			
		foreach ($parentRows as $row)
			if (!in_array( $row[ $parentKey ], $values))
				$values[] = $row[ $parentKey ];
				
		$params[ $paramID ] = $values;

		// Execute child query and group results; cleanUp can also be done at this point
		$childRows = $child->group()->select( $params, $paramID, true );

		$childFieldName = $child->prefix();
		
		// Combine child rows with parent rows
		for ($i=0;$i<count($parentRows);$i++)
		{
			$keyValue = $parentRows[$i][$parentKey];
					 
			$parentRows[$i][ $childFieldName ] = (array_key_exists($keyValue, $childRows)) 
				? $childRows[ $keyValue ]
				: array();
		}
		
	}
}









/**
 * Term
 *
 * This class represents a query term
 *
 * @author 	Wouter Diesveld <wouter@tinyqueries.com>
 * @package TinyQueries
 */
class Term
{
	// A term string should match the following reg exp
	const CHARS = '/^[\w\.\:\#\-\,\(\)\|\+\;\s]+$/';
	
	/**
	 * Parses a query term and returns an object of type Query (or extended class)
	 *
	 * Technical notes:
	 * First tries to parse a structure like "( ... )" or "prefix.( ... )"
	 * ( the second form is needed for parsing terms like a:(b|c) - a is passed as prefix to (b|c), like a.(b|c) )
	 *
	 * @param {DB} $db
	 * @param {string} $term
	 */
	public static function parse($db, $term)
	{
		if (!$term)
			return;
		
		// Replace return chars by space
		$term = str_replace("\t", " ", $term);
		$term = str_replace("\r", " ", $term);
		$term = str_replace("\n", " ", $term);
			
		list( $id, $children ) = self::parseID( $term );
		
		// Extract the prefix from the id (if present)
		$prefix = ($id && substr($id, -1) == '.')
			? substr($id, 0, -1)
			: null;
		
		// Determine the term to be passed to the merge parser
		$termMerge = ($children && (!$id || $prefix))
			? $children
			: $term;
			
		return self::parseMerge( $db, $termMerge, $prefix );
	}
	
	/**
	 * Checks if the 'root' element of each terms should be replaced by an alias
	 * For example "a(b,c)" and alias "a" => "d" will result in "d(b,c)"
	 *
	 * @param {array} $terms
	 * @param {assoc} $aliases
	 */
	public static function convertAliases($terms, $aliases)
	{
		if (count($aliases) == 0)
			return $terms;
	
		$terms1 = array();
		
		foreach ($terms as $term)
		{
			list( $id, $children ) = self::parseID( $term );
			
			$terms1[] = ($id && array_key_exists($id, $aliases))
				? $aliases[ $id ] . (($children) ? "(" . $children . ")" : "")
				: $term;
		}
		
		return $terms1;
	}
	
	/**
	 * Parses a merge term, like a|b|c
	 *
	 * @param {DB} $db
	 * @param {string} $term 
	 */
	private static function parseMerge($db, $term = null, $prefix = null)
	{
		$list = self::split($term, '|');

		if ($prefix)
			foreach ($list as $i => $v)
				$list[$i] = $prefix . "." . $list[$i];
		
		// If there is only one element, parse it as an attach
		if (count($list) == 1)
			return self::parseAttach( $db, $list[0] );

		return new QueryMerge($db, $list);
	}
	
	/**
	 * Parses a attach term, like a+b+c
	 *
	 * @param {DB} $db
	 * @param {string} $term
	 */
	private static function parseAttach($db, $term)
	{
		$list = self::split($term, '+', ';');
		
		// If there is only 1 element, parse it as a chain
		if (count($list) == 1)
			return self::parseChain( $db, $list[0] );

		return new QueryAttach($db, $list);
	}
	
	/**
	 * Parses a filter term, like a:b:c
	 *
	 * @param {DB} $db
	 * @param {string} $term
	 */
	private static function parseChain($db, $term)
	{
		$list = self::split($term, ':', '#');
		
		// If there is only 1 element, parse it as an tree
		if (count($list) == 1)
			return self::parseTree( $db, $list[0] );

		return new QueryFilter($db, $list);
	}
	
	/**
	 * Parses a ID tree structure and sets the ID of this query and creates child queries
	 *
	 * @param {DB} $db
	 * @param {string} $term
	 */
	private static function parseTree($db, $term)
	{
		list( $id, $children ) = self::parseID( $term );

		if (!$id && !$children)
			throw new \Exception("Term::parseTree - Cannot parse term " . $term);
			
		if (!$id)
			throw new \Exception("Term::parseTree - No id found " . $term);
		
		// If there are no children, we are at the 'leaves', e.g. the atomic queries (either JSON or SQL)
		if (!$children)
			return self::atomic($db, $id);
			
		$list = self::split($children, ',');
		
		return new QueryTree( $db, $id, $list );
	}
	
	/**
	 * Gets the ID part and children-part out of a tree structure, so "a(b(c),d)" will return "a" & "b(c),d"
	 *
	 * @param {string} $idTree
	 */
	private static function parseID($idTree)
	{
		$idTree = trim($idTree);
		
		// If tree has only one node this is just the ID of the query
		if (preg_match('/^[\w\-\.]+$/', $idTree))
			return array($idTree, null);
			
		$match = null;
		
		if (!preg_match('/^([\w\-\.]*)\s*\((.*)\)$/', $idTree, $match))
			return array( null, null );
			
		$id = ($match[1])
			? $match[1]
			: null;
			
		return array( $id, trim( $match[2] ) );
	}
	
	/**
	 * Checks which type of query corresponds with $id and returns a new instance of the corresponding query object
	 *
	 * @param {DB} $db
	 * @param {string} $id
	 */
	private static function atomic($db, $id)
	{
		$interface = $db->queries->getInterface( $id );

		// Check if query is an alias
		if (property_exists($interface, 'term'))
			return self::parse($db, $interface->term);
					
		return new QuerySQL($db, $id);
	}
	
	/**
	 * Splits the string by the separator, respecting possible nested parenthesis structures
	 *
	 * @param {string} $string
	 * @param {string} $separator1 Must be a single char!
	 * @param {string} $separator2 (optional) Must be a single char!
	 */
	private static function split($string, $separator1, $separator2=null)
	{
		$string = trim( $string );
		$stack 	= 0;
		$part 	= '';
		$list 	= array();
		
		if ($string == '')
			return $list;
		
		for ($i=0; $i<strlen($string); $i++)
		{
			$char = substr( $string, $i, 1 );
			
			switch ($char)
			{
				case '(': $stack++; break;
				case ')': $stack--; break;
				case $separator1: 
				case $separator2: 
					if ($stack == 0)
					{
						$list[] = $part;
						$part 	= '';
						$char 	= '';
					}
					break;
			}

			$part .= $char;
		}
		
		// Add last element
		$list[] = $part;
		
		return $list;
	}
}



/**
 * QuerySet
 *
 * Maintains a set of queries
 *
 * @author 	Wouter Diesveld <wouter@tinyqueries.com>
 * @package TinyQueries
 */
class QuerySet
{
	const PATH_INTERFACE 	= '/interface';
	const PATH_SOURCE		= '/tiny';
	const PATH_SQL 			= '/sql';
	
	private $project;
	private $pathQueries;
	private $labelQuerySet;
	private $callbacks;
	
	/**
	 * Constructor
	 *
	 * @param {string} $pathQueries
	 */
	public function __construct($pathQueries)
	{
		$this->path($pathQueries);
		
		$this->callbacks 	= array();
		$this->project		= null;
	}

	/**
	 * Gets/sets the label for the query set
	 *
	 * @param {string} $label
	 */
	public function label($label = -1)
	{
		if ($label != -1)
		{
			if ($label && !preg_match("/^[\w\-]+$/", $label))
				throw new \Exception("setLabel: No valid label value");
			
			$this->labelQuerySet = $label;
		}
		
		return $this->labelQuerySet;
	}	
	
	/**
	 * Gets/sets a callback function for a query
	 *
	 * @param {string} $queryID
	 * @param {function} $callback
	 */
	public function callback($queryID, $callback = null)
	{
		if (is_null($callback))
			return (array_key_exists($queryID, $this->callbacks))
				? $this->callbacks[ $queryID ]
				: null;
				
		$this->callbacks[ $queryID ] = $callback;
	}
	
	/**
	 * Gets/sets the path to the queries
	 *
	 * @param {string} $path
	 */
	public function path($path = null)
	{
		if ($path)
			$this->pathQueries = $path;	
	
		return 
			$this->pathQueries .
			(($this->labelQuerySet) ? "-" . $this->labelQuerySet : "");
	}
	
	/**
	 * Loads the content of a file
	 *
	 * @param {string} $filename
	 */
	public static function load($filename, $parseAsJSON = false)
	{
		if (!file_exists($filename)) 	
			throw new \Exception('Cannot find ' . $filename); 
		
		$content = @file_get_contents( $filename );
		
		if (!$content)
			throw new \Exception('File ' . $filename . ' is empty');
			
		if (!$parseAsJSON)
			return $content;
			
		// Replace EOL's and tabs by a space character (these chars are forbidden to be used within json strings)
		$content = preg_replace("/[\n\r\t]/", " ", $content);
			
		$json = @json_decode( $content );
		
		if (!$json)
			throw new \Exception("Error parsing JSON of " . $filename);
		
		return $json;
	}
	
	/**
	 * Gets all meta data related to the given query
	 *
	 * @param {string} $queryID
	 */
	public function getInterface($queryID)
	{
		$filename = $this->path() . self::PATH_INTERFACE . "/" . $queryID . ".json";

		try
		{
			return $this->load( $filename, true );
		}
		catch (\Exception $e)
		{
			// Throw more human readable message
			throw new \Exception("Cannot load query '" . $queryID . "' - maybe the name of the query is misspelled, the project might not be compiled yet or the file permissions of the queries folder are not set correctly");
		}
	}
	
	/**
	 * Gets the JSON file for the given query
	 *
	 * @param {string} $queryID
	 */
	public function json($queryID)
	{
		$filename = $this->path() . "/" . $queryID . ".json";
		
		return $this->load( $filename, true );
	}
	
	/**
	 * Returns the SQL-code which is associated with the given queryID
	 *
	 * @param {string} $queryID ID of the query
	 */
	public function sql($queryID)
	{
		$filename = $this->path() . self::PATH_SQL . "/" . $queryID . ".sql";

		return $this->load( $filename, false );
	}
	
	/**
	 * Returns the project info contained in _project.json
	 */
	public function project()
	{
		if ($this->project)
			return $this->project;
			
		$filename = $this->path() . self::PATH_INTERFACE . "/" . "_project.json";
		
		$this->project = $this->load( $filename, true );
		
		return $this->project;
	}
};




/**
 * Profiler
 *
 * @author 	Wouter Diesveld <wouter@tinyqueries.com>
 * @package TinyQueries
 */
class Profiler
{
	private $start;
	private $nodes;
	private $current;
	private $running;
	private $counter;
	
	/**
	 * Constructor
	 *
	 * @param {boolean} $run Do profiling or not
	 */
	public function __construct($run = true)
	{
		$this->running = $run;
		
		if ($run)
			$this->run();
	}
	
	/**
	 * Initializes the Profiler and sets start time
	 *
	 */
	public function run()
	{
		$this->running 	= true;
		$this->start 	= microtime(true);
		$this->nodes	= array();
		$this->counter	= 0;
		$this->current	= &$this->nodes;
	}
	
	/**
	 * Create a new node
	 *
	 * @param {string} $node
	 */
	public function begin($node)
	{
		if (!$this->running)
			return;
		
		$this->counter++;
			
		$label = $this->counter . ":" . $node;	
			
		$this->current[ $label ] = array
		(
			"_start" 	=> microtime(true),
			"_parent"	=> &$this->current
		);
		
		$this->current = &$this->current[ $label ];
	}

	/**
	 * End the current node
	 */
	public function end()
	{
		if (!$this->running)
			return;
			
		if (!$this->current)
			return;
			
		$parent = &$this->current['_parent'];
			
		$time = microtime(true) - $this->current[ "_start" ];
		
		// If the current node does not have children, just set the node to the total time
		if (count( array_keys($this->current) ) <= 2)
			$this->current = $time;
		else	
		{
			// Otherwise add a field _total
			$this->current[ "_total" ] = $time;
			unset( $this->current[ "_start" ] );
			unset( $this->current[ "_parent" ] );
		}
			
		$this->current = &$parent;
	}
	
	/**
	 * Get the profiling results
	 */
	public function results()
	{
		if (!$this->running)
			return null;
			
		$results = $this->nodes;
			
		$results['_total'] = microtime(true) - $this->start;
			
		return $results;
	}
}








/**
 * Compiler
 *
 * Interface for the online TinyQueries compiler
 * CURL needs to be enabled
 *
 * @author 	Wouter Diesveld <wouter@tinyqueries.com>
 * @package TinyQueries
 *
 */
class Compiler
{
	const SQL_FILES			= 'sql';
	const INTERFACE_FILES 	= 'interface';
	const SOURCE_FILES 		= 'source';
	
	public $apiKey;
	public $querySet;
	public $server;
	
	private $enabled;
	private $folderInput;
	private $folderOutput;
	private $version;
	private $logfile;
	private $verbose;
	private $curlOutput;
	private $filesWritten;
	private $projectLabel;
	
	/**
	 * Constructor
	 *
	 * @param {string} $configFile Optionally you can provide a config file
	 */
	public function __construct($configFile = null)
	{
		$config = new Config( $configFile );
		
		// Import settings
		$this->projectLabel	= $config->project->label;
		$this->enabled		= $config->compiler->enable;
		$this->apiKey		= $config->compiler->api_key;
		$this->folderInput 	= $config->compiler->input;
		$this->folderOutput	= $config->compiler->output;
		$this->server		= $config->compiler->server;
		$this->version		= $config->compiler->version;
		$this->logfile		= $config->compiler->logfile;
		$this->querySet 	= new QuerySet( $this->folderOutput );
		$this->verbose		= true;
		$this->filesWritten	= array();
	}
	
	/**
	 * Checks if the TinyQuery code has changed; if so calls the online compiler
	 *
	 * @param {boolean} $force (optional) Set this to true to call the compiler anyway
	 * @param {boolean} $doCleanUp (optional) If set to true, it will delete local sql files which are not in the compiler output
	 */
	public function compile($force = false, $doCleanUp = false)
	{
		if (!$force && !$this->compileNeeded())
			return;
		
		try
		{
			$this->callCompiler($doCleanUp, 'POST');
		}
		catch (\Exception $e)
		{
			$this->log( $e->getMessage() );
			if ($this->curlOutput)
				$this->log( $this->curlOutput );
				
			throw $e;
		}
	}
	
	/**
	 * Checks the online compiler if sql-code can be downloaded (only applies if client granted permission to save code on the server)
	 *
	 */
	public function download()
	{
		try
		{
			$this->callCompiler(true, 'GET');
		}
		catch (\Exception $e)
		{
			$this->log( $e->getMessage() );
			if ($this->curlOutput)
				$this->log( $this->curlOutput );
				
			throw $e;
		}
	}
	
	/**
	 * Returns the timestamp of newest SQL file in the queries folder
	 *
	 */
	public function getTimestampSQL()
	{
		list($sqlPath, $sqlFiles)  = $this->getFolder( self::SQL_FILES );
		
		$sqlChanged = null;
		
		// Get max time of all sql files
		foreach ($sqlFiles as $file)
		{
			$mtime = filemtime($file);
			if ($mtime > $sqlChanged)
				$sqlChanged = $mtime;
		}
		
		return $sqlChanged;
	}
	
	/**
	 * Checks whether there are changes made in either the model or the queries file
	 *
	 */
	public function compileNeeded()
	{
		// If there is no input folder specified we cannot know
		if (!$this->folderInput)
			return null;
		
		$project	= null;
		$qplChanged = 0;
		$sqlChanged = 0;
		
		try
		{
			$project = $this->querySet->project();
		}
		catch (\Exception $e)
		{
			// If there is no compiled project file, a compile is needed
			return true;
		}
		
		// If versions differ a compile is needed
		if ($project->compiledWith && $this->version && $project->compiledWith != $this->version)
			return true;
		
		list($dummy, $sourceFiles, $sourceIDs) = $this->getFolder( self::SOURCE_FILES );
		list($sqlPath, $dummy)  = $this->getFolder( self::SQL_FILES );
		
		// Get max time of all source files
		foreach ($sourceFiles as $file)
		{
			$mtime = filemtime($file);
			if ($mtime > $qplChanged)
				$qplChanged = $mtime;
		}
		
		$sqlChanged = $this->getTimestampSQL();

		if ($qplChanged > $sqlChanged)
			return true;
			
		// Check for source files which are deleted
		foreach ($project->queries as $queryID => $dummy)
			if (!in_array($queryID, $sourceIDs))
			{
				$sqlFile = $sqlPath . "/" . $queryID . ".sql";
				if (file_exists($sqlFile))
				{
					$mtime = filemtime($sqlFile);
					if ($mtime < $sqlChanged)
						return true; 
				}
			}
			
		return false;	
	}
	
	/**
	 * Returns the path + files + fileID's
	 *
	 */
	private function getFolder( $fileType )
	{
		$extension = null;
		
		switch ($fileType)
		{
			case self::SQL_FILES:
				$path = $this->querySet->path() . QuerySet::PATH_SQL;
				$extenstion = "sql"; 
				break;
				
			case self::INTERFACE_FILES:
				$path = $this->querySet->path() . QuerySet::PATH_INTERFACE;
				$extenstion = "json"; 
				break;
				
			case self::SOURCE_FILES:
				$path = $this->folderInput;
				$extenstion = "json"; 
				break;
				
			default: 
				throw new \Exception("getFolder: Unknown filetype");
		}
		
		$files 	= array();
		$ids	= array();
		$match	= null;
		
		foreach (scandir($path) as $file)
			if (preg_match('/^(.+)\.'.$extenstion.'$/', $file, $match))
			{
				$files[] = $path . "/" . $file;
				$ids[] 	 = $match[1];
			}
				
		return array($path, $files, $ids);
	}
	
	/**
	 * Calls the online TinyQueries compiler and updates the local SQL-cache
	 *
	 */
	private function callCompiler($doCleanUp, $method = 'POST')
	{
		if (!$this->enabled)
			throw new \Exception('Compiling is not enabled on this instance - set field compiler > enable = "true" in config.xml to enable compiling');
	
		// Reset array
		$this->filesWritten = array();
		
		// Update log-file
		$this->log('Compiler being called..');

		// Init CURL
		if (!function_exists('curl_init'))
			throw new \Exception('Cannot compile queries - curl extension for PHP is not installed');

		$this->curlOutput = null;
		$ch = curl_init();

		if (!$ch) 
			throw new \Exception( 'Cannot initialize curl' );
		
		// Set post message 
		$postBody = 
			"api_key=" 	. urlencode( $this->apiKey ) 		. "&" .
			"project="	. urlencode( $this->projectLabel ) 	. "&" .
			"version=" 	. urlencode( $this->version )		. "&" ;

		// Read project files and add them to the postBody
		list($dummy, $sourceFiles, $sourceIDs) = $this->getFolder( self::SOURCE_FILES );

		// Only add source files for POST calls
		if ($method == 'POST')
			for ($i=0; $i<count($sourceFiles); $i++)
			{
				$content = @file_get_contents( $sourceFiles[ $i ] );
			
				if (!$content) 	
					throw new \Exception('Cannot read ' . $file);
					
				$sourceID = $sourceIDs[ $i ];
					
				$postBody .= "code[$sourceID]=" . urlencode( $content ) . "&";
			}
			
		// Catch curl output
		$curlOutputFile = "qpl-call.txt";
		
		$handle = @fopen($curlOutputFile, "w+");

		if ($handle)
			curl_setopt($ch, CURLOPT_STDERR, $handle);	

		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_HEADER, true); 		// Return the headers
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	// Return the actual reponse as string
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // nodig omdat er anders een ssl-error is; waarschijnlijk moet er een intermediate certificaat aan curl worden gevoed.
		curl_setopt($ch, CURLOPT_HTTPHEADER,array("Expect:")); // To disable status 100 response 
		
		$compilerURL =  $this->server . '/api/compile/';
		
		if ($method == 'GET')
			$compilerURL .= '?' . $postBody;
		else	
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postBody);
	
		curl_setopt($ch, CURLOPT_URL, $compilerURL);
		
		
		// Execute the API call
		$raw_data = curl_exec($ch); 

		curl_close($ch);
		
		// Read temp file for curl output
		if ($handle)
		{
			fclose($handle);
			$this->curlOutput = file_get_contents($curlOutputFile);
			@unlink($curlOutputFile);
		}
		
		$status = null;
		
		if ($raw_data === false) 
			throw new \Exception('Did not receive a response from the online TinyQueryCompiler; no internet? - SQL-files are NOT updated');
		
		// Split the headers from the actual response
		$response = explode("\r\n\r\n", $raw_data, 2);
			
		// Find the HTTP status code
		$matches = array();
		if (preg_match('/^HTTP.* ([0-9]+) /', $response[0], $matches)) 
			$status = intval($matches[1]);

		if ($status != 200)
		{
			$error = @simplexml_load_string( $response[1] ); 
			$errorMessage = ($error)
								? $error->message
								: 'Received status '.$status." - ". $response[1];
								
			throw new \Exception( $errorMessage );
		}
		
		// Unfortunately, the xml-code needs to be parsed twice in order to handle the CDATA-blocks
		$ids 	= @simplexml_load_string( $response[1] ); 
		$code	= @simplexml_load_string( $response[1] , 'SimpleXMLElement', LIBXML_NOCDATA ); 
		
		if ($ids===false || $code===false) 
		{
			$errorMsg = 'Error parsing xml coming from the TinyQueryCompiler - please visit www.tinyqueries.com for support.';
			
			if ($this->verbose) 
				$errorMsg .= '\n\nResponse:\n\n' . $response[1];
			
			throw new \Exception( $errorMsg );
		}

		// Update sql & interface-files
		for ($i=0;$i<count($ids->query);$i++)
		{
			$queryID = $ids->query[$i]->attributes()->id;
			
			$this->writeInterface( $queryID, $code->query[$i]->{'interface'} );
			
			if (property_exists($code->query[$i], 'sql'))
				$this->writeSQLfile( $queryID, $code->query[$i]->sql );
		}
		
		// Write _project file
		if ($code->{'interface'})
			$this->writeInterface( '_project', (string) $code->{'interface'} );
			
		$cleanUpTypes = array(self::SQL_FILES, self::INTERFACE_FILES);

		// Write source code if present
		if ($code->source)
		{
			for ($i=0;$i<count($ids->source);$i++)
			{
				$sourceID = $ids->source[$i]->attributes()->id;
				$this->writeSource($sourceID, $code->source[$i]->code);
			}
			
			$cleanUpTypes[] = self::SOURCE_FILES;
		}
			
		// Clean up files which were not in the compiler output
		if ($doCleanUp)
			foreach ($cleanUpTypes as $filetype)
			{
				list($path, $files) = $this->getFolder( $filetype );
				foreach ($files as $file)
					if (!in_array($file, $this->filesWritten))
					{
						$r = @unlink($file);
						if ($r)
							$this->log( 'Deleted ' . $file );
					}
			}
		
		// Update log-file
		$this->log('SQL-files updated successfully');
	}
	
	/**
	 * Writes a message to the logfile (if present)
	 *
	 * @param {string} $message
	 */
	private function log($message)
	{
		if (!$this->logfile) return;
		
		$message = "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
		
		@file_put_contents( $this->logfile, $message, FILE_APPEND);
	}

	/**
	 * Writes the source file
	 *
	 * @param {string} $fileID 
	 * @param {string} $code
	 */
	private function writeSource($fileID, $code)
	{
		$filename = $this->folderInput . "/" . $fileID . ".json";
			
		$this->writeFile( $filename, $code );
	}
	
	/**
	 * Writes the interface file
	 *
	 * @param {string} $fileID 
	 * @param {string} $interface
	 */
	private function writeInterface($fileID, $interface)
	{
		$filename = $this->querySet->path() . QuerySet::PATH_INTERFACE . "/" . $fileID . ".json";

		$this->writeFile( $filename, $interface );
	}
	
	/**
	 * Creates a .sql file containing the query. The name of the file will be [$queryID].sql
	 *
	 * @param {string} $fileID 
	 * @param {string} $sqlCode
	 */
	private function writeSQLfile($fileID, $sqlCode)
	{
		$filename = $this->querySet->path() . QuerySet::PATH_SQL . "/" . $fileID . ".sql";
			
		$this->writeFile( $filename, $sqlCode );
	}

	/**
	 * Writes $content to $filename
	 *
	 * @param {string} $filename
	 * @param {string} $content
	 */
	private function writeFile($filename, $content)
	{
		$r = @file_put_contents($filename, (string) $content);
			
		if (!$r) 
			throw new \Exception('Error writing ' . $filename . ' -  are the permissions set correctly?' );
			
		$this->filesWritten[] = $filename;
	}
}









/**
 * DB
 *
 * PDO based DB layer which can be used to call predefined SQL queries
 *
 * @author 	Wouter Diesveld <wouter@tinyqueries.com>
 * @package TinyQueries
 */
class DB
{
	public $dbh;	// PDO database handle
	public $nested; // Default setting whether or not query output should be nested - more info see Query::nested(.)
	public $queries;
	public $profiler;
	public $globals;
	public $driver;
	public $host;
	public $port;
	public $dbname;
	public $user;
	
	private $pw;
	private $initQuery;
	private $globalQueryParams;
	private $primaryKey;
	
	/**
	 * Constructor
	 *
	 * If no parameters are specified, database-parameters like username/passwd are read from the default configfile config.xml
	 * The connection should be explicitly set up by calling the connect-method after the DB-object is constructed.
	 * If you specify a $pdoHandle, this method should not be called.
	 *
	 * @param {PDO} $pdoHandle (optional) Use this if you already have a PDO database connection.
	 * @param {string} $configFile (optional) Use this to specify your custom XML-configfile
	 * @param {Profiler|boolean} $profiler (optional) If 'true' then a Profiler object is created and run is called; if 'false' the object is also created but not initialized
	 * @param {boolean} $neverAutoCompile (optional) This can be used to overrule the setting in the config file 
	 */
	public function __construct( $pdoHandle = null, $configFile = null, $profiler = null, $neverAutoCompile = false )
	{
		// Initialize profiler object
		if (is_object($profiler))
			$this->profiler = &$profiler;
		else
			$this->profiler = new Profiler( ($profiler) ? true : false );
	
		$config = new Config( $configFile );
		
		// Import settings
		$this->driver		= $config->database->driver; 
		$this->host			= ($config->database->host) ? $config->database->host : 'localhost';
		$this->port			= $config->database->port;
		$this->dbname		= $config->database->name;
		$this->user			= $config->database->user;
		$this->pw 			= $config->database->password;
		$this->initQuery	= $config->database->initQuery;
		$this->nested		= $config->postprocessor->nest_fields;

		// Call the compiler if autocompile is set
		if (!$neverAutoCompile && $config->compiler->autocompile)
		{
			$compiler = new Compiler( $configFile );
			$compiler->compile( false, true );
		}
		
		$this->queries 		= new QuerySet( $config->compiler->output );
		$this->globals 		= array();
		$this->primaryKey 	= 'id'; 
		
		if ($pdoHandle)
			$this->dbh = $pdoHandle;
	}
	
	/**
	 * Get/set method for global query parameters. If value is not specified, the value of the global is returned
	 *
	 * @param {string} $name
	 * @param {mixed} $value
	 */
	public function param($name, $value = -99999999)
	{
		if ($value == -99999999)
		{
			if (!array_key_exists($name, $this->globals))
				throw new \Exception("DB::param - global parameter '".$name."' does not exist");
				
			return $this->globals[ $name ];
		}
		
		$this->globals[ $name ] = $value;
	}
	
	/**
	 * Sets up the database connection
	 *
	 */
	public function connect()
	{
		$this->disconnect();
		
		if (!$this->driver)
			throw new \Exception("No database driver specified in config");
			
		if (!$this->dbname)
			throw new \Exception("No database name specified in config");
		
		if (!$this->user)
			throw new \Exception("No database user specified in config");
		
		// construct PDO object
		$dsn = $this->driver . ":dbname=" . $this->dbname . ";host=" . $this->host;
		
		if ($this->port)
			$dsn .= ';port=' . $this->port;
		
		$this->dbh = new \PDO($dsn, $this->user, $this->pw);
		
		// throw exception for each error
		$this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		
		// execute the initial query
		if ($this->initQuery)
			$this->execute($this->initQuery);
	}

	/**
	 * Checks if there is a connection
	 *
	 */
	public function connected()
	{
		return (($this->dbh) ? true : false);
	}
	
	/**
	 * Disconnects from the DB
	 *
	 */
	public function disconnect()
	{
		// destroy db-object
		$this->dbh = null;
	}
	
	/**
	 * @return {PDO} The PDO DB handle
	 */
	public function pdo()
	{
		return $this->dbh;
	}
	
	/**
	 * Creates and returns a new Query object 
	 *
	 * @param {string} $term A query term like "a" or "a:b+c(d|e)"
	 */
	public function query($term)
	{
		return Term::parse($this, $term);
	}
	
	/**
	 * Creates a query based on $term, executes it and returns the query output
	 *
	 * @param {string} $term
	 * @param {mixed} $paramValues
	 */
	public function get($term, $paramValues = null)
	{
		return $this->query($term)->select($paramValues);
	}
	
	/**
	 * Creates a query based on $term, executes it and returns the first row of the query output
	 *
	 * @param {string} $term
	 * @param {mixed} $paramValues
	 */
	public function get1($term, $paramValues = null)
	{
		return $this->query($term)->select1($paramValues);
	}
	
	/**
	 * Creates a basic select query for the given table and IDfields
	 *
	 * @param {string} $table
	 * @param {int|array} $IDfields If an integer is supplied, it is assumed to be the primary key. 
	 *                            If it is an array, it is assumed to be an assoc array of fields which should all be matched
	 */
	private function createSelect($table, $IDfields)
	{
		// Convert to primary key selection
		if (!is_array($IDfields))
			$IDfields = array( $this->primaryKey => $IDfields );
			
		return "select * from `" . $this->toSQL($table) . "` where " . $this->fieldList( $IDfields, " and ", true );
	}
	
	/**
	 * Selects a single record from the given table
	 *
	 * @param {string} $table
	 * @param {int|array} $IDfields If an integer is supplied, it is assumed to be the primary key. 
	 *                            If it is an array, it is assumed to be an assoc array of fields which should all be matched
	 */
	public function getRecord($table, $IDfields)
	{
		return $this->selectAssoc( $this->createSelect($table, $IDfields) );
	}
	
	/**
	 * Selects records from the given table
	 *
	 * @param {string} $table
	 * @param {int|array} $IDfields If an integer is supplied, it is assumed to be the primary key. 
	 *                            If it is an array, it is assumed to be an assoc array of fields which should all be matched
	 */
	public function getRecordSet($table, $IDfields)
	{
		return $this->selectAllAssoc( $this->createSelect($table, $IDfields) );
	}
	
	/**
	 * Selects a single record from the given table
	 *
	 * @param {string} $field Fieldname which is used for selection
	 * @param {string} $table
	 * @param {int|string} $value Fieldvalue
	 */
	public function getRecordBy($field, $table, $value)
	{
		return $this->getRecord($table, array( $field => $value ));
	}

	/**
	 * Selects records from the given table
	 *
	 * @param {string} $field Fieldname which is used for selection
	 * @param {string} $table
	 * @param {int|string} $value Fieldvalue
	 */
	public function getRecordSetBy($field, $table, $value)
	{
		return $this->getRecordSet($table, array( $field => $value ));
	}
	
	/**
	 * Inserts a record in the given table
	 *
	 * @param {string} $table
	 * @param {assoc} $record
	 * @param {boolean} $updateOnDuplicateKey If the insert fails due to a duplicate key error, then try to do an update (MySQL only)
	 */
	public function insert($table, $record, $updateOnDuplicateKey = false)
	{
		if (!is_array($record) || count($record)==0)
			throw new \Exception("DB::insert - record is empty");
	
		$keys 	= array_keys($record);
		$values	= array_values($record);
		
		for ($i=0;$i<count($keys);$i++)
		{
			$keys[$i] 	= "`" . $this->toSQL($keys[$i]) . "`";
			$values[$i] = $this->toSQL($values[$i], true);
		}
		
		$keysSQL 	= implode(",", $keys);
		$valuesSQL 	= implode(",", $values);
		
		$query = "insert into `" . $this->toSQL($table) . "` ($keysSQL) values ($valuesSQL)";
		
		if ($updateOnDuplicateKey)
			$query .= " on duplicate key update " . $this->fieldList( $record, "," );
		
		$this->execute($query);
		
		$id = $this->dbh->lastInsertId();

		// If an update is done and the update is not changing the record, lastInsertId will return "0"
		if ($id == 0 && $updateOnDuplicateKey)
		{
			// Try to find the record based on $record
			$recordForID = array();
			foreach ($record as $key => $value)
				// skip large fields like text & blobs
				if (strlen($value) <= 255) 
					$recordForID[ $key ] = $value;
			
			$recordInDB = $this->getRecord($table, $recordForID);
			
			$id = ($recordInDB && array_key_exists( $this->primaryKey, $recordInDB ))
					? $recordInDB[ $this->primaryKey ]
					: null;
		}
		
		return $id;
	}
	
	/**
	 * Saves (either inserts or updates) a record in the given table (MySQL only)
	 * NOTE: for this function to work correctly, the field(s) which correspond to a unique DB-key should be present in $record
	 *
	 * @param {string} $table
	 * @param {assoc} $record
	 */
	public function save($table, $record)
	{
		return $this->insert($table, $record, true);
	}
	
	/**
	 * Updates a record in the given table
	 *
	 * @param {string} $table
	 * @param {int|array} $IDfields If an integer is supplied, it is assumed to be the primary key. 
	 *                            If it is an array, it is assumed to be an assoc array of fields which should all be matched
	 * @param {assoc} $record
	 */
	public function update($table, $IDfields, $record)
	{
		if (!is_array($record) || count($record)==0)
			throw new \Exception("DB::update - record is empty");
	
		// Convert to primary key selection
		if (!is_array($IDfields))
			$IDfields = array( $this->primaryKey => $IDfields );

		$query = 	"update `" . $this->toSQL($table) . "`" .
					" set " . $this->fieldList( $record, "," ) . 
					" where " . $this->fieldList( $IDfields, " and ", true );
		
		$this->execute($query);
	}
	
	/**
	 * Deletes a record from the given table
	 *
	 * @param {string} $table
	 * @param {int|array} $IDfields If an integer is supplied, it is assumed to be the primary key. 
	 *                            If it is an array, it is assumed to be an assoc array of fields which should all be matched
	 */
	public function delete($table, $IDfields)
	{
		// Convert to primary key selection
		if (!is_array($IDfields))
			$IDfields = array( $this->primaryKey => $IDfields );
			
		$query = "delete from `" . $this->toSQL($table) . "` where " . $this->fieldList( $IDfields, " and ", true );
		
		$this->execute($query);
	}
	
	/**
	 * Executes the given query
	 *
	 * @param {string} $query SQL query
	 * @param {assoc} $params Query parameters
	 */
	public function execute($query, $params = array())
	{
		if (!$this->dbh) 
			throw new \Exception("DB::execute called but there is no connection to the DB - call connect first");
	
		$this->profiler->begin('db::execute');
		
		$sth = $this->dbh->prepare($query);

		// Bind the parameters
		foreach ($params as $name => $props)
		{
			// Do casting (otherwise the types might still not be set correctly)
			if (is_null($props['value']))
				$props['type'] = \PDO::PARAM_NULL;
			else
				switch ($props['type'])
				{
					case \PDO::PARAM_INT: $props['value'] = (int) $props['value']; break;
					case \PDO::PARAM_STR: $props['value'] = (string) $props['value']; break;
				}
			
			$sth->bindValue( ":" . $name, $props['value'], $props['type'] );
		}
		
		$r = $sth->execute();

		$this->profiler->end();
		
		if (!$r) 
		{
			$error = $sth->errorInfo();
			if ($error && is_array($error) && count($error)>=3)
				throw new \Exception($error[1] . " - " . $error[2]);
			throw new \Exception('unknown error during execution of query');
		}
		
		return $sth;
	}

	/**
	 * Escapes a string such that it can be used in a query
	 *
	 * @param {string} $string
	 * @param {boolean} $addquotes (optional)
	 * @param {boolean} $useNULLforEmptyValue (optional)
	 */
	public function toSQL($string, $addquotes = false, $useNULLforEmptyValue = false)
	{
		if (!$this->dbh) 
			throw new \Exception("toSQL called before creation of dbh-object");
			
		if (is_array($string))
			throw new \Exception("toSQL: Array passed while expecting a string or a number");
			
		if (is_object($string))
			throw new \Exception("toSQL: Object passed while expecting a string or a number");
			
		if (is_null($string))
			return "NULL";
		
		if ($string === '' && $useNULLforEmptyValue)
			return "NULL";

		$sql = '';

		if (!isset($string)) 
			$string = "";
			
		$sql = $this->dbh->quote( $string );

		// remove quotes added by quote(.)
		if (!$addquotes)
			$sql = substr($sql, 1, strlen($sql)-2);

		return $sql;
	}
	
	/**
	 * Same as toSQL, except that integers & tuples like (1,2,3) are not quoted
	 *
	 * @param {string} $string
	 */
	public function encode($string)
	{
		if (is_string($string) && (preg_match("/^\d+$/", $string) || preg_match("/^\([\d\,]+\)$/",$string)))
			return $string;
			
		return $this->toSQL($string, true);
	}
	
	/**
	 * Executes query and returns numeric array of numeric arrays
	 *
	 * @param {string} $query SQL-query
	 * @param {assoc} $params Query parameters
	 */
	public function selectAll($query, $params = array())
	{
		return $this->execute( $query, $params )->fetchAll( \PDO::FETCH_NUM );
	}

	/**
	 * Executes query and returns numeric array of associative arrays
	 *
	 * @param {string} $query SQL-query
	 * @param {assoc} $params Query parameters
	 */
	public function selectAllAssoc($query, $params = array())
	{
		return $this->execute( $query, $params )->fetchAll( \PDO::FETCH_ASSOC );
	}

	/**
	 * Executes query and returns first record as numeric array
	 *
	 * @param {string} $query SQL-query
	 * @param {assoc} $params Query parameters
	 */
	public function selectRow($query, $params = array())
	{
		return $this->execute( $query, $params )->fetch( \PDO::FETCH_NUM );
	}
	
	/**
	 * Executes query and returns first record as associative array
	 *
	 * @param {string} $query SQL-query
	 * @param {assoc} $params Query parameters
	 */
	public function selectAssoc($query, $params = array())
	{
		return $this->execute( $query, $params )->fetch(\PDO::FETCH_ASSOC);
	}
	
	/**
	 * Executes query and returns first field of first record
	 *
	 * @param {string} $query SQL-query
	 * @param {assoc} $params Query parameters
	 */
	public function selectFirst($query, $params = array()) 
	{
		$sth = $this->execute( $query, $params );
		$row = $sth->fetch(\PDO::FETCH_NUM);
		return $row[0];
	}
	
	/**
	 * Executes query and returns numeric array containing first field of each row
	 *
	 * @param {string} $query SQL-query
	 * @param {assoc} $params Query parameters
	 */
	public function selectAllFirst($query, $params = array()) 
	{
		$sth = $this->execute( $query, $params );
		$rows = $sth->fetchAll(\PDO::FETCH_NUM);
		$firsts = array();
		foreach ($rows as $row)
			$firsts[] = $row[0];
		return $firsts;
	}
	
	/**
	 * Create a concatenation of `fieldname` = "value" strings
	 *
	 * @param {assoc} $fields
	 * @param {string} $glue
	 * @param {boolean} $isOnNull If true, it uses 'is' for NULL values
	 */
	private function fieldList($fields, $glue, $isOnNull = false)
	{
		$list = array();
		
		foreach ($fields as $name => $value)
		{
			$equalsSign = ($isOnNull && is_null($value)) 
							? " is " 
							: " = ";
							
			$list[] = "`" . $this->toSQL($name) . "`" . $equalsSign . $this->toSQL($value, true);
		}
	
		return implode( $glue, $list );
	}
} 






 

/**
 * Api
 *
 * This is a simple JSON API which can be used on top of DB
 *
 * @author 	Wouter Diesveld <wouter@tinyqueries.com>
 * @package TinyQueries
 */
class Api extends HttpTools
{
	protected $server;
	protected $query;
	protected $debugMode;
	protected $configFile;
	protected $addProfilingInfo;
	protected $doTransaction;
	protected $request;
	protected $outputFormat;
	protected $reservedParams;
	protected $params = array();
	
	public $db;
	public $profiler;
	
	/**
	 * Constructor
	 *
	 * @param {string} $configFile (optional) Path to DB settings file
	 * @param {boolean} $debugMode (optional) Sets debug mode
	 * @param {boolean} $addProfilingInfo (optional) Adds profiling info to api response
	 */
	public function __construct($configFile = null, $debugMode = false, $addProfilingInfo = false)
	{
		$this->server 	 		= self::getServerVar('SERVER_NAME');
		$this->debugMode 		= $debugMode;
		$this->configFile 		= $configFile;
		$this->addProfilingInfo = $addProfilingInfo;
		$this->doTransaction	= true;
		$this->contentType		= null;
		$this->reservedParams 	= array('query', 'param'); // + all params starting with _ are also ignored as query parameter
		
		// request contains the details of the request
		$this->request = array(
			'method' => self::getServerVar('REQUEST_METHOD', '/^\w+$/', 'GET')
		);

		// Overrule profiling setting if param _profiling is send
		if (array_key_exists('_profiling', $_REQUEST))
			$this->addProfilingInfo	= self::getRequestVar('_profiling', '/^\d+$/'); 

		// Create Profiler object
		$this->profiler	= new Profiler( $this->addProfilingInfo );
	}
	
	/**
	 * Initializes the api (connects to db)
	 *
	 */
	public function init()
	{
		if ($this->db)
			return;
		
		$this->db = new DB( null, $this->configFile, $this->profiler );
		
		$this->db->connect();
	}
	
	/**
	 * Processes the request and sends the response to the stdout
	 *
	 * @param {string} $contentType (optional)
	 */
	public function sendResponse($contentType = 'application/json')
	{
		// Get the output format
		$this->contentType = self::getRequestVar('_contentType', '/^[\w\/]+$/', $contentType);
		
		// Set content type
		header( 'Content-type: ' . $this->contentType . '; charset=utf-8' );

		$response = array();
		
		try
		{
 			// Catch all output which is send to stdout
			ob_start();
			
			$this->init();
			
			$dbConnectedModus = ($this->db && $this->db->connected());
			
			if ($dbConnectedModus && $this->doTransaction)
			{
				// Disable nested fields for CSV since CSV can only handle 'flat' data
				if (preg_match('/\/csv$/', $this->contentType))
					$this->db->nested = false;
		
				$this->db->pdo()->beginTransaction();
			}
			
			$response = $this->processRequest();

			// Ensure the output is an associative array, so that meta data like exectime can be added
			if ($this->addProfilingInfo && (!Arrays::isAssoc($response) || (Arrays::isAssoc($response) && count($response) == 0)))
				$response = array
				(
					'result' => $response
				);

			$textOutput = ob_get_clean();
			
			if ($textOutput)
				throw new \Exception($textOutput);
			
			if ($dbConnectedModus)
			{
				if ($this->doTransaction)
					$this->db->pdo()->commit();
					
				$this->db->disconnect();
			}
			
			if ($this->addProfilingInfo)
				$response['profiling'] = $this->profiler->results();
			
		}
		catch (\Exception $e)
		{
			// reset output buffer
			ob_clean();
			
			if ($this->doTransaction)
				$this->rollback();

			$errorMessage = $e->getMessage();
		
			$showToUser = (get_class( $e ) == "TinyQueries\\UserFeedback" || $this->debugMode == true) 
								? true 
								: false;
								
			$httpCode	= (get_class( $e ) == "TinyQueries\\UserFeedback")
								? $e->getCode()
								: 400;

			$response = $this->createErrorResponse( $errorMessage, $showToUser, $httpCode );
		}
		
		// add general info to response
		if ($this->addProfilingInfo)
		{
			$response['timestamp'] 	= time();
			$response['server'] 	= $this->server;
		}
		
		// optional parameters for redirect (non ajax only)
		$urlSuccess	= self::getRequestVar('url-success');
		$urlError 	= self::getRequestVar('url-error');
		
		// Do redirect
		if ($urlSuccess && !array_key_exists('error', $response))
		{
			header('Location: ' . self::addParamsToURL($urlSuccess, $response));
			exit;
		}
		
		// Do redirect
		if ($urlError && array_key_exists('error', $response))
		{
			header('Location: ' . self::addParamsToURL($urlError, $response));
			exit;
		}

		$this->sendResponseBody($response);
	}
	
	/**
	 * Do contentType specific encoding and output to stdout
	 *
	 */
	protected function sendResponseBody(&$response)
	{
		switch ($this->contentType)
		{
			case 'text/csv': 
				header('Content-Disposition: attachment; filename="' . $this->createFilename( $this->request['query'] ) . '.csv"');
				$this->csvEncode( $response );
				break;
				
			case 'application/json':
				echo $this->jsonEncode( $response );
				break;
				
			default:
				throw new \Exception('No handler for contentType: ' . $this->contentType);
				// Do nothing - for custom content-types you should override this method
				break;
		}
	}
	
	/**
	 * Overload this function if you have more things to clean up when an error occors during the request-processing
	 */
	protected function rollback()
	{
		if (!$this->db)
			return;
			
		if (!$this->db->pdo())
			return;
			
		if (!$this->db->pdo()->inTransaction())
			return;
			
		$this->db->pdo()->rollBack();
	}
	
	/**
	 * Overload this function if you want some post processing of the response before it is sent to the client
	 *
	 * @param {mixed} $response
	 */
	protected function postProcessResponse( &$response )
	{
	}
	
	/**
	 * Returns a filename based on a queryID (term)
	 */
	protected function createFilename( $queryTerm )
	{
		if (!$queryTerm)
			return 'query_output';
			
		// Replace all query term special chars with underscore
		return preg_replace('/[\+\(\)\,\s\:\|]/', '_', $queryTerm);
	}
	
	/**
	 * Checks if the given endpoint matches the endpoint as coming from the webserver.
	 * Furthermore it sets the keys of the $params property corresponding to the variables in the path prefixed with :
	 *
	 * @example
	 *    if ($this->endpoint('GET /users/:userID'))
	 *			return $this->getUser();
	 *
	 * This will set $this->params['userID'] to the value in the URL if there is a match
	 *
	 * @param {string} $endpoint
	 */
	public function endpoint($endpoint)
	{
		// Split into two
		list($method, $pathVars) = explode(' ', $endpoint);
		
		// Check if request method matches
		if ($method != $this->request['method'])
			return false;

		// Get path which is coming from the request
		$path = self::getRequestVar('_path');
		
		// Add pre- and post slashes if not present
		if (substr($path,0,1)  != '/') $path = '/' . $path;
		if (substr($path,-1,1) != '/') $path = $path . '/';
		if (substr($pathVars,0,1)  != '/') $pathVars = '/' . $pathVars;
		if (substr($pathVars,-1,1) != '/') $pathVars = $pathVars . '/';
			
		// Get variable names from $pathVars
		preg_match_all( "/\:(\w+)\//", $pathVars, $vars );
			
		// Create a regexp based on pathVars
		$pathRexExp = str_replace('/', '\/', $pathVars);
		$pathRexExp = str_replace('.', '\.', $pathRexExp);
		
		// Replace the :vars with \w+ 
		if ($vars)
			foreach ($vars[1] as $var)
				$pathRexExp = str_replace(':'.$var, "([\\w\\-]+)", $pathRexExp);

		// Check if there is a match
		if (!preg_match_all('/^' . $pathRexExp . '$/', $path, $values))
			return false;
			
		// Set the parameters
		foreach ($vars[1] as $i => $var)
			$this->params[ $var ] = $values[$i+1][0];
			
		return true;
	}
	
	/**
	 * Converts a URI-path to a term + paramvalue + [output should be single row] true/false
	 *
	 * @param {$string} $path The resource path
	 * @param {string} $method The HTTP method
	 */
	private function pathToTerm($path, $method)
	{
		$match = null;
		
		// Remove first slash if there is nothing before it
		if (preg_match("/^\/(.*)$/", $path, $match))
			$path = $match[1];
			
		// Remove last slash if there is nothing after it
		if (preg_match("/^(.*)\/$/", $path, $match))
			$path = $match[1];
			
		if (!$path)
			return array(null, null, false);
		
		$words 	= explode('/', $path);
		$n 		= count($words);
		
		// Check even words for term-chars
		foreach ($words as $i => $word)
			if ($i%2==0 && !preg_match( Term::CHARS, $word ))
				throw new \Exception("Path contains invalid characters");
				
		// Determine queryID postfix		
		switch ($method)
		{
			case 'PUT':
			case 'PATCH': 	$postfix = ".update"; break;
			case 'POST': 	$postfix = ".create"; break;
			case 'DELETE': 	$postfix = ".delete"; break;
			default:		$postfix = ""; break;
		}		
		
		// path = "/a"  --> term = "a"
		if ($n==1)
			return array( $path . $postfix, null, false );
			
		// path = "../../a/:param"  --> term = "a" using parameter :param
		if ($n%2==0)
			return array($words[ $n-2 ] . $postfix, $words[ $n-1 ], true);
			
		// path = "../a/:param/b" --> term = "(b):a" using parameter :param
		return array( "(" . $words[ $n-1 ] . $postfix . "):" . $words[ $n-3 ], $words[ $n-2 ], false );	
	}
	
	/**
	 * Returns the request parameter values
	 *
	 */
	private function getQueryParams()
	{
		$params = array();
		
		// read the query-parameters
		foreach (array_keys($_REQUEST) as $paramname)
			if (!in_array($paramname, $this->reservedParams) && substr($paramname, 0, 1) != '_')
			{
				// Try/catch is needed to prevent global parameters to be overwritten by api users
				try
				{
					// If param is NOT present, an error is thrown
					$this->db->param($paramname);
				}
				catch (\Exception $e) 
				{
					$params[ $paramname ] = self::getRequestVar($paramname);
				}
			}
		
		if (count($params) > 0)
			return array( $params );
			
		// If no params are found check if the body is a json blob
		if ($json = self::getJsonBody())
		{
			// Ensure the output is an array of assoc arrays
			if (Arrays::isAssoc($json))
				return array( $json );
				
			if (is_array($json))
				return $json;
		}
				
		return array(array());
	}
	
	/**
	 * Returns the requested query term + its parameter values
	 *
	 */
	protected function requestedQuery()
	{
		$term 	= self::getRequestVar('query', Term::CHARS ); 
		$path 	= self::getRequestVar('_path');
		$param	= self::getRequestVar('param');
		
		$singleRow = false;
		
		if (!$term && !$path) 
			throw new \Exception('query-param is empty'); 
			
		if (!$term && $path)
			list($term, $param, $singleRow) = $this->pathToTerm($path, $this->request['method'] );
			
		// Convert space to + (since in URL's + is converted to space, while + is the attach operator and should be preserved)
		$term = str_replace(" ", "+", $term);
		
		$params = $this->getQueryParams();

		$this->request['query']	= $term;	
		
		return array( $term, $params, $param, $singleRow );
	}
	
	/**
	 * Processes the api request, e.g. executes the query/queries and returns the output
	 */
	protected function processRequest()
	{
		if (!$this->db)
			throw new \Exception('Database is not initialized');

		if (!$this->db->connected())
			throw new \Exception('There is no database connection');
			
		list($term, $paramsSet, $param, $singleRow) = self::requestedQuery();
		
		$multipleCalls 	= (count($paramsSet) > 1) ? true : false;
		$response 		= ($multipleCalls) ? array() : null; 
		
		$this->query = $this->db->query($term);
		
		$this->request['queryID'] = property_exists($this->query, 'id') 
			? $this->query->id 
			: null;
	
		if (!$this->checkRequestPermissions())
			throw new UserFeedback( 'You have no permission to do this request' );
			
		$this->profiler->begin('query');
		
		if ($param && !$this->query->defaultParam)
			throw new \Exception("Single parameter value passed, but query does not have a default parameter");

		foreach ($paramsSet as $params)
		{		
			// Only overwrite if $param is not null
			if ($this->query->defaultParam)
				if (!is_null($param) || !array_key_exists($this->query->defaultParam, $params))
					$params[ $this->query->defaultParam ] = $param;
					
			if ($multipleCalls)
				$response[] = $this->query->run( $params ); 
			else
				$response   = $this->query->run( $params ); 
		}

		$this->postProcessResponse( $response );
		
		$this->profiler->end();
		
		// Wrap response in array if profiling is added
		if ($this->addProfilingInfo)
			$response = array
			(
				"query"			=> $term,
				"params"		=> $params,
				"result"		=> $response
			);
		
		return $response;
	}
	
	/**
	 * Creates an error response in JSON
	 *
	 */
	protected function createErrorResponse($errorMessage, $showToUser, $httpCode = 400, $altoMessage = "Cannot process request" )
	{
		$this->setHttpResponseCode($httpCode);
		
		return array(
			"error"	=> ($showToUser) 
				? $errorMessage 
				: $altoMessage
		);
	}

	/**
	 * CSV encoder function; outputs to stdout
	 *
	 * @param {assoc|array} $response
	 */
	public function csvEncode($response)
	{
		$stdout = fopen("php://output", 'w');

		if (is_object($response))
			$response = Arrays::objectToArray($response);

		// Ignore meta info; only output query output
		if (Arrays::isAssoc($response) && count($response)>0 && array_key_exists('result', $response))
			$response = $response['result'];
		
		if (is_null($response))
			$response = array( "" );
		
		if (is_string($response))
			$response = array( $response );
			
		if (Arrays::isAssoc($response))
			$response = array(
				array_keys( $response ),
				array_values( $response )
			);
			
		// Should not occur at this point..
		if (!is_array($response))
			throw new \Exception("Cannot convert reponse to CSV");

		// Encode as UTF8
		array_walk_recursive($response, array($this, 'toUTF8'));			
			
		// If output is array of assocs
		if (count($response)>0 && Arrays::isAssoc($response[0]))
		{
			// Put array keys as first CSV line
		    fputcsv($stdout, array_keys($response[0]), ';');
			
			foreach($response as $row) 
				fputcsv($stdout, array_values($row), ';');
		}
		// Output is an array of arrays
		elseif (count($response)>0 && is_array($response[0]))
			foreach($response as $row) 
				fputcsv($stdout, $row, ';');
		// Output is 1 dim array
		else
			foreach($response as $item) 
				fputcsv($stdout, array( $item ), ';');
		
        fclose($stdout);
	}
	
	/**
	 * JSON encoder function (Also does the UTF8 encoding)
	 *
	 * @param {object} $object
	 */
	public static function jsonEncode($object)
	{
		if (is_object($object))
			$object = Arrays::objectToArray($object);
		
		if (is_array($object))
			array_walk_recursive($object, array('TinyQueries\Api', 'toUTF8'));
		else
			$object = self::toUTF8($object);
		
		return json_encode( $object );
	}
	
	/**
	 * Converts a string to UTF8, if it is not yet in UTF8
	 *
	 * @param {mixed} $item If item is not a string, it is untouched
	 */
	public static function toUTF8(&$item) 
	{ 
		if (is_string($item) && mb_detect_encoding($item, 'UTF-8', true))
			return $item;	
	
		if (is_string($item)) 
			$item = utf8_encode( $item );
			
		return $item;
	}

	/**
	 * This method can be overloaded to add your own permission checks
	 * The overloaded method can use $this->request to check the specs of the request
	 *
	 */
	protected function checkRequestPermissions()
	{
		return true;
	}
}



// Include libs  



/**
 * API for the admin tool
 *
 * @author 	Wouter Diesveld <wouter@tinyqueries.com>
 * @package TinyQueries
 */
class AdminApi extends Api
{
	const REG_EXP_SOURCE_ID = "/^[\w\.\-]+$/";
	
	protected $compiler;
	protected $dbError;
	protected $jsonPcallback;
	
	/**
	 * Constructor 
	 *
	 */
	public function __construct($configFile = null)
	{
		// Get the function-name for the jsonp callback
		$this->jsonPcallback = self::getJsonpCallback();
		
		// Set debug mode = true
		parent::__construct($configFile, true);
	}
	
	/**
	 * Overrides parent::init
	 *
	 */
	public function init()
	{
		if (!Config::exists( $this->configFile ))
			return;
	
		try
		{
			if ($this->db)
				return;
			
			// Do neverAutoCompile for admin
			$this->db = new DB( null, $this->configFile, $this->profiler, true );
			
			$this->db->connect();
		}
		catch (\Exception $e)
		{
			// If initializing fails, there is no DB connection
			// A DB connection is not required for the admin tool (except that some functions are not available)
			// So no exception must be thrown, only the message must be saved
			$this->dbError = $e->getMessage();
		}
		
		// Initialize compiler
		$this->compiler = new Compiler( $this->configFile );
	}
	
	/**
	 * Overrides parent::processRequest
	 *
	 */
	protected function processRequest()
	{
		// Get request params
		$apiKey		= self::getRequestVar('_api_key', '/^\w+$/');
		$method		= self::getRequestVar('_method', '/^[\w\.]+$/');
		$globals	= self::getRequestVar('_globals');
		$server 	= self::getRequestVar('_compiler'); // the compiler which is calling this api

		$configExists = Config::exists( $this->configFile );
		
		// Check api-key
		if (!$apiKey)
			throw new UserFeedback("You need to provide an api-key to use this API");
			
		if ($configExists && !$this->compiler->apiKey)
			throw new UserFeedback("You need to set the api-key in your TinyQueries config-file");
			
		if ($configExists && $apiKey != $this->compiler->apiKey)
			throw new UserFeedback("api-key does not match");
			
		// Ensure that there is only one compiler which is speaking with this api, otherwise queries might get mixed up
		if ($configExists && $server && strpos($this->compiler->server, $server) === false)
			throw new UserFeedback('Compiler which is calling this api does not match with compiler in config');
			
		// Set global query params
		if ($this->db && $globals)
		{
			$globals = json_decode( $globals );
			foreach ($globals as $name => $value)
				$this->db->param($name, $value);
		}
		
		// If no method is send, just do the default request handler for queries
		if (!$method)
			return parent::processRequest();
			
		// Method mapper
		switch ($method)
		{
			case 'compile': 		return $this->compile();
			case 'createView':		return $this->createView();
			case 'deleteQuery':		return $this->deleteQuery();
			case 'downloadQueries':	return $this->downloadQueries();
			case 'getDbScheme':		return $this->getDbScheme();
			case 'getInterface':	return $this->getInterface();
			case 'getProject':		return $this->getProject();
			case 'getSource':		return $this->getSource();
			case 'getSQL':			return $this->getSQL();
			case 'getStatus':		return $this->getStatus();
			case 'getTermParams': 	return $this->getTermParams();
			case 'renameQuery':		return $this->renameQuery();
			case 'saveSource':		return $this->saveSource();
			case 'setup':			return $this->setup();
			case 'testApi':			return array( "message" => "Api is working" );
		}
		
		throw new \Exception('Unknown API method');
	}
	
	/**
	 * Overrides parent::sendResponse
	 *
	 */
	public function sendResponse($contentType = 'application/json')
	{
		if (!$this->jsonPcallback)
			return parent::sendResponse($contentType);
			
		return parent::sendResponse('application/javascript');
	}
	
	/**
	 * Overrides parent::sendResponseBody
	 *
	 */
	protected function sendResponseBody(&$response)
	{
		if (!$this->jsonPcallback)
			return parent::sendResponseBody($response);
			
		echo $this->jsonPcallback . '(' . $this->jsonEncode( $response ) . ');';
	}
	
	/**
	 * Overrides parent::createErrorResponse
	 *
	 */
	public static function setHttpResponseCode($code = NULL) 
	{
		// Don't set the response code for JSONP; the response code must always be 200 
		if (self::getJsonpCallback())
			return;
			
		parent::setHttpResponseCode($code);
	}
	
	/**
	 * Sets up the TinyQueries environment
	 *
	 */
	public function setup()
	{
		$setupFile = dirname(__FILE__) . '/../config/setup.php';
		
		// Don't throw error, but just return informative message
		if (!file_exists($setupFile))
			return array(
				'message' => 'Nothing to do - No setup script found'
			);
			
		include( $setupFile );
		
		if (!function_exists('TinyQueries\\setup'))
			throw new \Exception('There is no function \'TinyQueries\\setup\' defined in setup.php');
		
		$result = setup();
		
		if (Arrays::isAssoc($result))
			return $result;
			
		return array(
			'message' => 'TinyQueries setup complete'
		);
	}
	
	/**
	 * Compiles the tinyqueries source code 
	 *
	 */
	public function compile()
	{
		// Call compiler with settings Force compile & do clean up
		$this->compiler->compile( true, true );
		
		return array(
			'message' => 'Compiled at ' . date('Y-m-d H:i:s')
		);
	}
	
	/**
	 * Downloads the queries from the tinyqueries server (only available if code is stored on server)
	 *
	 */
	public function downloadQueries()
	{
		$this->compiler->download();
		
		return array(
			'message' => 'Queries downloaded at ' . date('Y-m-d H:i:s')
		);
	}
	
	/**
	 * Returns some info about the status of this api
	 *
	 */
	public function getStatus()
	{
		$timestamp = ($this->compiler)
			? $this->compiler->getTimestampSQL()
			: null;
		
		return array(
			'version_libs'	=> Config::VERSION_LIBS,
			'timestampSQL'	=> ($timestamp) ? date ("Y-m-d H:i:s", $timestamp) : null,
			'configExists'	=> Config::exists( $this->configFile ),
			'dbError' 		=> $this->dbError,
			'dbStatus'		=> ($this->db && $this->db->connected()) 
				? 'Connected with ' . $this->db->dbname . ' at ' . $this->db->host
				: 'Not connected'
		);
	}
	
	/**
	 * Gets the name of the callback function for the JSONP response
	 *
	 */
	private static function getJsonpCallback()
	{
		return self::getRequestVar('_jsonpcallback', '/^[\w\.]+$/');
	}
	
	/**
	 * Checks if file exists, if so deletes it
	 *
	 */
	private function deleteFile($path)
	{
		if (!file_exists($path))
			return;
			
		$r = @unlink( $path );
		
		if (!$r)
			throw new \Exception("Could not delete $file");
	}

	/**
	 * Creates or replaces an SQL-view which corresponds to the query
	 *
	 */
	public function createView()
	{
		$queryID = self::getRequestVar('query', self::REG_EXP_SOURCE_ID);
		
		if (!$queryID)
			throw new \Exception("No queryID");
			
		$sql = $this->compiler->querySet->sql($queryID);
		
		if (!$sql)
			throw new \Exception("Could not read SQL file");
			
		$this->db->execute( 'create or replace view `' . $queryID . '` as ' . $sql );
		
		return array(
			'message' => 'Created / updated view "' . $queryID . '"'
		);
	}
	
	/**
	 * Deletes the source, sql and interface file of a query
	 *
	 */
	public function deleteQuery()
	{
		$queryID = self::getRequestVar('query', self::REG_EXP_SOURCE_ID);
		
		if (!$queryID)
			throw new \Exception("No queryID");
		
		$this->deleteFile( $this->getSourceFilename('query') );
		
		return array(
			'message' => 'Query is removed'
		);
	}
	
	/**
	 * Checks the given ID and gives feedback if it is not ok
	 *
	 */
	private static function checkUserDefinedSourceID($requestVar)
	{
		$sourceID = self::getRequestVar($requestVar);
		
		if (is_null($sourceID) || ($sourceID === ''))
			throw new \Exception("You have to give a name to the query");

		if (!preg_match(self::REG_EXP_SOURCE_ID, $sourceID))
			throw new \Exception("Name of the query can only contain the characters: a-z A-Z _ 0-9 . -");
			
		return $sourceID;
	}
	
	/**
	 * Renames the source file and deletes the sql and interface file of a query
	 *
	 */
	public function renameQuery()
	{
		// First save the source
		$this->saveSource('query_old');
		
		$queryIDold = self::getRequestVar('query_old', self::REG_EXP_SOURCE_ID);
		$queryIDnew = self::checkUserDefinedSourceID('query_new');
		
		if (!$queryIDold)
			throw new \Exception("param query_old is missing");
		
		$filenameSourceOld = $this->getSourceFilename('query_old');
		$filenameSourceNew = $this->getSourceFilename('query_new');
		
		// Don't throw error in this case, because the query which is being renamed might not be saved yet
		if (!file_exists($filenameSourceOld))
			return array
			(
				'message' => 'Query is not present on file system'
			);
		
		if (file_exists($filenameSourceNew))
			throw new \Exception("Cannot rename: queryID already exists");
			
		$r = @rename($filenameSourceOld, $filenameSourceNew);
		
		if (!$r)
			throw new \Exception("Error during renaming");
		
		return array(
			'message' => 'Query is renamed'
		);
	}
	
	/**
	 * Converts a DB type to a TQ type
	 *
	 */
	private function convertDbType($dbType)
	{
		$match = null;
		$details = null;
		
		if (preg_match("/^(\w+)$/", $dbType, $match))
			$baseType = $match[1];
		elseif (preg_match("/^(\w+)\((.+)\)/", $dbType, $match))
		{
			$baseType = $match[1];
			$details = $match[2];
		}
		else
			return 'string';
			
		switch ($baseType)
		{
			case 'enum':
				$values = array();
				foreach (explode(',', $details) as $element)
					$values[] = substr($element, 1, strlen($element)-2);
				return array(
					'enum' => $values
				);

			case 'smallint':
			case 'mediumint':
			case 'tinyint':
			case 'bigint':
			case 'int':
				return 'int';
				
			case 'date':
				return 'date';
				
			case 'datetime':
			case 'timestamp':
				return 'datetime';
				
			case 'decimal':
			case 'float':
			case 'real':
			case 'double':
				return 'float';
				
			case 'boolean':
				return 'boolean';
				
			default:
				return 'string';
		}
	}

	/**
	 * Returns the database scheme
	 *
	 */
	private function getDbScheme()
	{
		$scheme = array();
		
		// Currently only available for MySQL
		if ($this->db->driver != 'mysql')
			return $scheme;
			
		$tables = $this->db->selectAllFirst('show tables');
		
		foreach ($tables as $table)
		{
			$columns = $this->db->selectAllAssoc('show columns from `' . $table . '`');
			
			foreach ($columns as $column)
				$scheme[$table]['fields'][ $column['Field'] ] = array(
					'type' 	=> $this->convertDbType( $column['Type'] ),
					'key'	=> ($column['Key']) ? $column['Key'] : null,
					'null'	=> ($column['Null'] == 'YES') ? true : false
				);
		}
		
		return $scheme;
	}
	
	/**
	 * Returns the project info
	 *
	 */
	public function getProject()
	{
		$config 	= new Config();
		$project 	= null;

		try
		{
			$project = $this->compiler->querySet->project();
		}
		catch (\Exception $e)
		{
			$project = new \StdClass();
			$project->loadError = $e->getMessage();
		}
		
		// Add compiler info to project
		$project->compiler = $config->compiler;
		
		$project->compiler->compileNeeded 	= $this->compiler->compileNeeded();
		$project->version_libs 				= Config::VERSION_LIBS;
		$project->dbError 					= $this->dbError;
		$project->dbStatus					= ($this->db && $this->db->connected()) ? 'Connected' : 'Not connected';
		$project->mode						= ($config->compiler->api_key) ? 'edit' : 'view';

		if (!property_exists($project, 'queries'))
			$project->queries = new \StdClass();
		
		// Set runnable = true for all compiled queries & add id
		foreach ($project->queries as $queryID => $def)
		{
			$project->queries->$queryID->id			= $queryID;
			$project->queries->$queryID->runnable 	= true;
		}
		
		// We are ready in case there is nothing to edit
		if ($project->mode != 'edit' || !$project->compiler->input)
			return $project;
		
		// Load query list from the input folder in order to get all other files which have no equivalent in the sql folder
		// (these are _model, hidden queries, not compiled queries)
		$match = null;
		$sourceIDs = array();
			
		// Scan input folder for source files
		foreach (scandir($project->compiler->input) as $file)
			if (preg_match("/^(.*)\.json$/", $file, $match))
				$sourceIDs[] = $match[1];

		// Compiled items which are not in the source file list should be removed
		// (usually these are deleted or renamed source files)
		foreach (array_keys(get_object_vars($project->queries)) as $queryID)
			if (!in_array($queryID, $sourceIDs))
				unset($project->queries->$queryID);
		
		// Source files which are not in the compiled list should be added
		foreach ($sourceIDs as $sourceID)		
			if (!property_exists( $project->queries, $sourceID ))
			{
				$queryDef = new \StdClass();
				$queryDef->id			= $sourceID;
				$queryDef->expose 		= 'hide';
				$queryDef->type			= null;
				$queryDef->defaultParam = null;
				$queryDef->operation	= null;
				$queryDef->runnable		= false;
						
				$project->queries->$sourceID = $queryDef;
			}
		
		return $project;
	}
	
	/**
	 * Returns the name of the source file which is posted
	 *
	 */
	private function getSourceFilename( $requestVar )
	{
		$sourceID = self::getRequestVar($requestVar, self::REG_EXP_SOURCE_ID);
		
		if (!$sourceID)
			throw new \Exception("sourceID not known");
			
		$config	= new Config();
			
		if (!$config->compiler->input)
			throw new \Exception("No input folder specified");
		
		return $config->compiler->input . "/" . $sourceID . ".json";
	}
	
	/**
	 * Returns the source of a query (if available)
	 *
	 */
	public function getSource()
	{
		$filename = $this->getSourceFilename('sourceID');
		
		// NOTE: regular api output is overruled - just the file itself is sent
		header( 'Content-type:  text/plain' );
		echo QuerySet::load( $filename );
		exit;
	}

	/**
	 * Saves the source of a query
	 *
	 */
	public function saveSource( $sourceIDvar = 'sourceID' )
	{
		self::checkUserDefinedSourceID($sourceIDvar);
		
		$filename 	= $this->getSourceFilename($sourceIDvar);
		$source 	= self::getRequestVar('source');
		
		$r = @file_put_contents($filename, $source);
			
		if (!$r) 
			throw new \Exception('Error writing ' . $filename . ' -  are the permissions set correctly?' );			
			
		return array(
			'message' => 'Source is saved'
		);
	}
	
	/**
	 * Returns the interface for a query
	 *
	 */
	public function getInterface()
	{
		list($queryID, $dummy) = self::requestedQuery();
		
		$interface = $this->compiler->querySet->getInterface($queryID);
		
		// Add parameters for aliases
		if (property_exists($interface, 'term'))
		{
			$response = $this->getTermParams();
		
			$interface->params = $response['params'];
		}
		
		return $interface;
	}
	
	public function getSQL()
	{
		list($queryID, $dummy) = self::requestedQuery();
		
		$sql = $this->compiler->querySet->sql($queryID);
		
		if (!$sql)
			throw new \Exception("Could not read SQL file");
	
		// NOTE: regular api output is overruled - just the file itself is sent
		header( 'Content-type:  text/plain' );
		echo $sql;
		exit;
	}
	
	/**
	 * Returns the parameters of the query-term passed by URL param 'query'
	 *
	 */
	private function getTermParams()
	{
		list($term, $dummy) = self::requestedQuery();
		
		$query = $this->db->query($term);
		
		return array(
			'params' => $query->params
		);
	}
};
