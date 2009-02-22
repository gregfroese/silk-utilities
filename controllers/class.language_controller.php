<?php
class LanguageController extends SilkControllerBase {
	
	public function changeLanguage($params) {
		$this->show_layout = false;
		SilkLang::set_lang($params["lang"]);
		redirect($params["redirect"]);
	}
	
	public function index($params) {
		$config = load_config();			
		self::build_navigation();
	}
	
	/**
	 * Scan all .tpl files and update each language file with all names of lang calls for translation
	 *
	 * @param unknown_type $params
	 */
	public function update_files($params) {

		self::build_navigation();
		
		$langEntries = self::get_all_lang_entries();
		$lang = $langEntries;  // duplicate the results so we don't screw up the loop
		$config = load_config();
			
		foreach($config["available_languages"] as $langIndicator) {
			$specificLang = SilkLang::load_language_file( $langIndicator );
			
			foreach($langEntries as $section=>$keys) {
				foreach( $keys as $key=>$text ) {
					if( !isset( $specificLang[$section][$key] ) ) {
						if( $config["default_lang"] != $langIndicator ) {
							$text .= " (" . $langIndicator . ")";
						}
						$specificLang[$section][$key] = $text;
					}
				}
			}
			SilkLang::write_language_file(array("lang" => $specificLang, "langIndicator" => $langIndicator));
		}
		$this->set("updateComplete", 1);
	}
	
	public function get_all_lang_entries() {
		
		$langPattern = "(\{lang(/?[^\>]+)\})";
		$langEntries = array();
		$components = SilkComponentManager::list_components();
		
		foreach($components as $key=>$component) {
			
			foreach($component as $controller) {
				
				$controller_dir = join_path(ROOT_DIR, "components", $key, "views", str_replace("class.", "", str_replace("_controller.php", "", $controller)));
				if(file_exists($controller_dir)) {
					$templates = scandir($controller_dir);
					
					foreach($templates as $template) {
						if($template != "." && $template != ".." && strpos($template, ".tpl")) {
							$file_contents = file(join_path($controller_dir, $template));
							
							$count = 0;
							foreach($file_contents as $line) {
								$count++;
								if( $line != "\n" && $line != "" ) {
									preg_match_all($langPattern, $line, $matches);
									if(!empty($matches[0])) {
										$matches = array_diff($matches[0], array(""));
										
										foreach($matches as $match) {
											$section = self::get_section_name($match);
											$name = self::get_name($match);
											$text = self::get_text($match);
											if(!empty($name) && !empty($text) && !empty($section)) {
												$langEntries[$section][$name] = $text;
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		return $langEntries;
	}
	
	public function get_name($line) {
		$namePositionStart = strpos($line, 'name="')+6;
		$namePositionEnd = strpos($line, '"', $namePositionStart +1);
		return substr($line, $namePositionStart, $namePositionEnd - $namePositionStart);
	}
	
	public function get_text($line) {
		$textPositionStart = strpos($line, 'text="')+6;
		$textPositionEnd = strpos($line, '"', $textPositionStart +1);
		$text = substr($line, $textPositionStart, $textPositionEnd - $textPositionStart);
		if($textPositionStart == 6) {
			$text = self::get_name($line);
		}
		return $text;
	}
	
	public function get_section_name($line) {
		$sectionPositionStart = strpos($line, 'section="')+9;
		$sectionPositionEnd = strpos($line, '"', $sectionPositionStart +1);
		$text = substr($line, $sectionPositionStart, $sectionPositionEnd - $sectionPositionStart);
		if($sectionPositionStart == 9) {
			$text = "General";
		}
		return camelize($text); //necessary for ajax functions to work
	}
	
	public function translate($params) {
		self::build_navigation();
		
		if(isset($params["formAction"])) {
			SilkLang::update_value($params);
			$resp = new SilkAjax();
			$div = "#".$params["section"].$params["key"]."result";
			$resp->replace_html($div, "<font color='Red'>Updated</font>");
			return $resp->get_result();
		}
		
		$langText = array_diff(SilkSyck::loadFile(SilkLang::build_language_filename(SilkLang::get_lang())), array("--"));
		$this->set("langText", $langText);
		$this->set("langIndicator", SilkLang::get_lang());
	}
	
	public function build_navigation() {
		self::translate_links();
		self::update_link();
		self::home_link();
		SilkUtilsController::build_menu();
	}
	
	public function translate_links() {
		$config = load_config();
		$translateLanguageLinks = array();
		foreach($config["available_languages"] as $lang) {
			$translateLanguageLinks[$lang] = SilkResponse::create_url(array("controller" => "language",
	  													"action" => "translate",
	  													"lang" => "$lang"
	  													));
		}
		$this->set("translateLanguageLinks", $translateLanguageLinks);
		return $translateLanguageLinks;
	}
	
	public function update_link() {
		$updateLink = SilkResponse::create_url(array(	"controller" => "language",
	  													"action" => "update_files"
	  													));
		$this->set("updateLink", $updateLink);
	}
	
	public function home_link() {
		$homeLink = SilkResponse::create_url(array(	"controller" => "language"));
		$this->set("homeLink", $homeLink);
	}
}

?>