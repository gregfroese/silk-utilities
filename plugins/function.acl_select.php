<?php
function smarty_function_acl_select($params, &$smarty)
{
//	echo "<pre>"; var_dump($params); echo "</pre>"; 
	$name = $params["controller"]."___".$params["function"]."___".$params["group_id"];
	$select = SilkForm::create_input_select(array("name" => $name));
	$items = array();
	$items[0] = "";
	$items[1] = "Allow";
	$items[2] = "Deny";
	if( isset( $params["acl_aro"][$params["controller"]][$params["function"]][$params["group_id"]] ) ) {
		$selected_index = $params["acl_aro"][$params["controller"]][$params["function"]][$params["group_id"]];
	} else {
		$selected_index = 0;
	}
//	echo "setting index to $selected_index<br />";
	$select .= SilkForm::create_input_options(array("items" => $items, "selected_index" => $selected_index));
	$select .= SilkForm::create_end_tag("select");
	return $select;
}

# vim:ts=4 sw=4 noet
?>