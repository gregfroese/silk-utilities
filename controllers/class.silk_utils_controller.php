<?php

class SilkUtilsController extends SilkControllerBase {
	
	public $menuItems = array(	"Routes" => array(	"function" => "routes"),
						"Language Admin" => array(	"controller" => "language")
						);
						
	function routes() {
		self::build_menu();
		$this->set("routes", SilkRoute::get_routes());
	}
	
	function index() {
		self::build_menu();
		$this->set("menuItems", $this->menuItems);
	}
	
	private function mydump($myname, $var) {
		echo "Name: $myname<pre>";
		var_dump($var);
		echo "</pre>";
	}
	
	function build_menu() {
		$menu["Utils Home"] = SilkResponse::create_url(array(	"controller" => "silk_utils"));
		$menu["Routes"] = SilkResponse::create_url(array(	"controller" => "silk_utils",
															"action" => "routes"));
		$menu["Language Admin"] = SilkResponse::create_url(array(	"controller" => "language"));
		$menu["ACL"] = SilkResponse::create_url(array(	"controller" => "acl" ));
		$this->set("subMenu", MenuManagerController::auto_menu(array("menuItems" => $menu, "subMenu" => true)));
	}
	
	public function get_methods($params) {
		if( isset($params["class"]) ) {
			$class_name = $params["class"];
		} else {
			$class_name = get_class($this);
		}
		echo "class name: ($class_name)<br />";
		$methods = array();
		$class = new ReflectionClass($class_name);
		foreach($class->getMethods() as $method) { 
			if( $method->getDeclaringClass()->getName() == $class_name ) {
				if( $method->isPublic() ) {
					$methods["public"][] = $method->getName();
				} else {
					$methods["private"][] = $method->getName();
				}
			}
		}
//		echo "<pre>"; var_dump($methods); echo "</pre>";
	}
}
?>