<?php
namespace Dagger;

use Exception;

/**
* Simple and restful php router class
*/
class Router
{
	private $_methods = array();
	private $_uris = array();
	private $_callbacks = array();
	private $_patterns = array(
		':any' => '[^/]+',
		':num' => '[0-9]+',
		':all' => '.*'
	);

	public function __call($method, $args)     
	{
		if (count($args) < 2) {
			throw new Exception("Must take exactly 2 arguments");
		}

		$this->_methods[] = strtolower($method);
		$this->_uris[] = '/' . ltrim($args[0], '/');
		$this->_callbacks[] = $args[1];
	}

	public function dispatch()
	{
		$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    	$method = strtolower($_SERVER['REQUEST_METHOD']);

    	$found_route = false;

    	// check uri is defined without regex
    	if (in_array($uri, $this->_uris, true)) {
    		$routePos = array_keys($this->_uris, $uri);
    		foreach ($routePos as $route) {
    			// if used method match...
    			if ($this->_methods[$route] === $method) {
    				$found_route = true;

    				// callback = object or string?
    				if (is_string($this->_callbacks[$route])) {
    					$segments = explode('@', $this->_callbacks[$route]);
    					if (empty($segments)) {
            				throw new Exception('Missing data for URL segment');
        				}

        				// instanitate 
        				$controller = new $segments[0]();

        				// call method
        				$controller->{$segments[1]}();
    				} else {
    					// Call closure
    					call_user_func($this->_callbacks[$route]);
    				}

    				break;
    			}
    		}
    	} else {
    		// Check if defined with regex

    		$searches = array_keys($this->_patterns);
    		$replaces = array_values($this->_patterns);

    		foreach ($this->_uris as $routePos => $value) {
    			// when met symbol ':', then replace it with assoc regex
    			if (strpos($value, ':') !== false) {
          			$route = str_replace($searches, $replaces, $value);
        		}
    			
    			// The usage of delimiter "#" as follows. At this time the "/" will not be escaped!
    			if (preg_match("#^$route$#", $uri, $matches)) {
    				$useMethod = $this->_methods[$routePos];
    				if ($useMethod === $method) {
    					$found_route = true;

    					// Remove $matched[0] as [1] is the first parameter.
    					array_shift($matches);

    					$useCallback = $this->_callbacks[$routePos];
    					if (is_string($useCallback)) {
    						$segments = explode('@', $useCallback);
	    					if (empty($segments)) {
	            				throw new Exception('Missing data for URL segment');
	        				}

	        				$controller = new $segments[0]();

	        				if (!method_exists($controller, $segments[1])) {
	        					throw new Exception("controller and action not found");
	        				} else {
	        					call_user_func_array(array($controller, $segments[1]), $matches);
	        				}

    					} else {
    						call_user_func_array($useCallback, $matches);
    					}

    					break;
    				}
    			}
    		}
    	}

    	if (!$found_route) {
    		header($_SERVER['SERVER_PROTOCOL']." 404 Not Found");
    		echo '404';
    	}
	}
}