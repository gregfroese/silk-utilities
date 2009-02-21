<?php
class AclController extends SilkControllerBase {
	public function index($params) {
		$this->set("params", $params);
	}
	
	public function acl($params) {
//		echo "<pre>"; var_dump($params); echo "</pre>";
		if( isset( $params["params"] ) ) $params = $params["params"];
		if( isset( $params["input"] )) {
			$this->set_ACL_ARO( $params );
			$this->set("save", 1);
		}
		
		$this->set("acl_aro", $this->get_ACL_ARO($params));
		$methods = array();
		$components = SilkComponentManager::list_components();

		foreach( $components as $component => $controllers ) {
			foreach( $controllers as $controller ) {
				$controller = camelize(str_replace("class.", "", str_replace(".php", "", $controller)));

				if(isset($params["controllers"])) {
					if(in_array($controller, $params["controllers"])) {
						$methods = $this->get_methods( array("class" => $controller, "methods" => $methods));
					}
				} else {
					$methods = $this->get_methods( array("class" => $controller, "methods" => $methods));
				}
			}
		}
		
		$this->set("methods", $methods);
		$this->set("groups", SilkGroup::get_groups_for_dropdowm(false));
	}
	
	/**
	 * Check if the current user has access to the given 
	 *
	 * @param unknown_type $params
	 */
	public function allowed($params) {
		$params["controller"] = camelize($params["controller"]) . "Controller";
		$config = load_config();
		if(isset($config["acl"])) {
			if($config["acl"] == true) {
//				echo "Checking ACL<br />";
//				echo "<pre>"; var_dump($params); echo "</pre>";
				if(!isset($_SESSION["silk_user"]["id"])) {
					$config = load_config();
					$acl_ids = array();
					$acl = orm("acl")->find_by_acl_type_and_acl_type_id("group", $config["anonymous_group_id"]);
					if(!empty($acl)) {
						$acl_ids[] = $acl->id;
					}
				} else {
					$user = orm("user")->find_by_id($_SESSION["silk_user"]["id"]);
					
					$acl_ids = array();
					// get acl_ids for each group
					foreach($user->groups as $group) {
						$acl = orm("acl")->find_by_acl_type_and_acl_type_id("group", $group->id);
						if(!empty($acl)) {
							$acl_ids[] = $acl->id;
						}
					}
				}
//				echo "<pre>"; var_dump($acl_ids); echo "</pre>";
				
				$results = array();
				$results = 	self::check_acl($params, $acl_ids, $results);
				if(!count($results)) {
					return false;
				} elseif(in_array(false, $results)) {
					return false;
				} else {
					return true;
				}
			} else {
				return true;
			}
		} else {
			return true;
		}
	}
	
	private function check_acl($params, $acl_ids, $results) {
		$aro_controller = orm("aro")->find_by_aro_type_and_aro_name("controller", $params["controller"]);
		if(empty($aro_controller)) {
			// if no controller entry, there will be no function entry - default is to deny
			return $results;
		}
//		echo "found controller aro: $params[controller]<br />";
		$aro_function = orm("aro")->find_by_aro_type_and_aro_name_and_parent_id("function", $params["action"], $aro_controller->id);
		if(empty($aro_function)) {
			// nothing found, default is to deny
			return $results;
		}
//		echo "found function aro: $params[action]<pre>"; var_dump($aro_function); echo "</pre>";

		$results = self::check_acl2($acl_ids, $aro_function->id, $results);
//		echo "Results<pre>"; var_dump($results); echo "</pre>";
		return $results;
	}
	
	private function check_acl2($acl_ids, $aro_id, $results) {
		foreach($acl_ids as $acl_id) {
//			echo "looking for acl/aro: $acl_id/$aro_id<br />";
			$acl_aro = orm("AclAro")->find_by_aro_id_and_acl_id($aro_id, $acl_id);
			if(!empty($acl_aro)) {
				if($acl_aro->access_type == 1) {
					$results[] = true;
				} elseif($acl_aro->access_type == 2) {
					$results[] = false;
				}
			}
		}
		return $results;
	}
	
	private function set_ACL_ARO($params) {
		$this->delete_ACL_ARO();
		$params = $this->override_function_values( $params );
		
		// find the root node
		$aro_root = orm("aro")->find_by_parent_id(-1);
		if( empty( $aro_root ) ) {
			$aro_root = new ARO();
			$aro_root->aro_type = "root";
			$aro_root->aro_name = "root";
			$aro_root->lft = 1;
			$aro_root->rgt = 2;
			$aro_root->parent_id = -1;
			$aro_root->item_order = 1;
			$aro_root->save();
		}
		
		foreach( $params as $key => $value ) {
			if( $value != 0 ) {
				
				list($controller, $function, $group_id ) = explode( "___", $key );
//				echo "controller: $controller function: $function group_id: $group_id<br />";

				// create an ACL record if none exists for this group id
				$acl = orm("acl")->find_by_acl_type_and_acl_type_id( "group", $group_id );
				if( empty( $acl ) ) {
					$acl = new ACL();
					$acl->acl_type = "group";
					$acl->acl_type_id = $group_id;
					$acl->save();
				}
				
				// find/create the ARO record for the controller
				$aro_controller = orm("aro")->find_by_aro_type_and_aro_name("controller", $controller);
				if( empty( $aro_controller )) {
					
					$aro_controller = new ARO();
					$aro_controller->aro_type = "controller";
					$aro_controller->aro_name = $controller;
					$aro_controller->parent_id = $aro_root->id;
					$aro_controller->group_id = $group_id;
					$aro_controller->save();
				}

				// create ACL_ARO entry for all functions on a controller if CONTROLLER is set as the function
				if( $function == "CONTROLLER" ) {
					$acl_aro_controller = new AclAro();
					$acl_aro_controller->aro_id = $aro_controller->id;
					$acl_aro_controller->acl_id = $acl->id;
					$acl_aro_controller->access_type = $value;
					$acl_aro_controller->save();
				}

				// find/create the ARO record for the function as long as its not = CONTROLLER
				// CONTROLLER is used as a place holder for setting ACL for all the functions in a controller
				if( $function != "CONTROLLER" ) {
					$aro_function = orm("aro")->find_by_aro_type_and_aro_name_and_parent_id("function", $function, $aro_controller->id);
					if( empty( $aro_function )) {
						$aro_function = new ARO();
						$aro_function->aro_type = "function";
						$aro_function->aro_name = $function;
						$aro_function->parent_id = $aro_controller->id;
						$aro_function->group_id = $group_id;
						$aro_function->save();
					}
					
					$acl_aro_function = new AclAro();
					$acl_aro_function->aro_id = $aro_function->id;
					$acl_aro_function->acl_id = $acl->id;
					$acl_aro_function->access_type = $value;
					$acl_aro_function->save();
				}
			}
		}
	}
	
	private function override_function_values($params) {
//		echo "<pre>"; var_dump($params); echo "</pre>";
		$updated_params = $params;
		foreach( $params as $key => $value ) {
			if( substr_count( $key, "___") == 2 ) {
				list($controller, $function, $group_id ) = explode( "___", $key );
				if( $value != 0 && $function == "CONTROLLER" ) {
					foreach( $updated_params as $key2 => $value2 ) {
						if( substr_count( $key2, "___") == 2 ) {
							list($controller2, $function2, $group_id2 ) = explode( "___", $key2 );
							if( $controller == $controller2 && $group_id == $group_id2 ) {
								$updated_params[$controller2."___".$function2."___".$group_id2] = $value;
							}
						}
					}
				}
			}
		}
		return $updated_params;
	}
	private function delete_ACL_ARO() {
		$acl_aro = orm("acl_aro")->find_all();
		foreach( $acl_aro as $one_acl_aro ) {
			$one_acl_aro->delete();
		}
	}
	
	private function get_ACL_ARO() {
		$results = array();
		$acl_aro = orm("AclAro")->find_all();
		foreach( $acl_aro as $key => $value ) {
			$acl = orm("acl")->find_by_id( $value->acl_id );
			$aro = orm("aro")->find_by_id( $value->aro_id );
			
			if( !empty( $acl ) && !empty( $aro ) ) {
				if( $aro->aro_type == "controller" ) {
					$results[$aro->aro_name]["CONTROLLER"][$acl->acl_type_id] = $value->access_type;
				}
				if( $aro->aro_type == "function" ) {
					$aro_controller = orm("aro")->find_by_id($aro->parent_id);
					$results[$aro_controller->aro_name][$aro->aro_name][$acl->acl_type_id] = $value->access_type;
				}
			}
		}
		return $results;
	}
	
	private function get_methods($params) {
		if( isset($params["class"]) ) {
			$class_name = $params["class"];
		} else {
			$class_name = get_class($this);
		}
		if(is_array($params["methods"])) {
			$methods = $params["methods"];
		} else {
			$methods = array();
		}
		$class = new ReflectionClass($class_name);
		foreach($class->getMethods() as $method) { 
			if( $method->getDeclaringClass()->getName() == $class_name ) {
				if( $method->isPublic() ) {
					$methods["public"][$class_name][] = $method->getName();
				} else {
					$methods["private"][$class_name][] = $method->getName();
				}
			}
		}
		return $methods;
	}
}