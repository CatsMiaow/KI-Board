<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

define('WIDGET', TRUE);

class Widget {
    function __construct() {
        $this->_assign_libraries();
    }

    public static function run($controller) {
        if (strpos($controller, '.') !== FALSE) {
            list($controller, $method) = explode('.', $controller);
        }

        require_once APPPATH.'controllers/'.$controller.EXT;

        // default method
        if (!isset($method)) $method = 'index';

        // class name
        $class = end(explode('/', $controller));

        if ($class = new $class()) {
            if (method_exists($class, $method)) {
                $args = func_get_args();
                return call_user_func_array(array($class, $method), array_slice($args, 1));
            }
        }
    }

    function _assign_libraries() {
        $CI =& get_instance();
        foreach (get_object_vars($CI) as $key => $object) {
            $this->$key =& $CI->$key;
        }
    }
}