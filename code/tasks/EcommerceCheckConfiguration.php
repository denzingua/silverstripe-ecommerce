<?php

/**
 * This class reviews all of the static configurations in e-commerce for review
 * (a) which configs are set, but not required
 * (b) which configs are required, but not set
 * (c) review of set configs
 *
 * @TODO: compare to default
 *
 */

class EcommerceCheckConfiguration extends BuildTask{

	/**
	 * Default Location for Configuration File
	 * @var String
	 */
	protected $defaultLocation = "ecommerce/_config/ecommerce.yaml";

	/**
	 * Standard (required) SS variable for BuildTasks
	 * @var String
	 */
	protected $title = "Check Configuration";

	/**
	 * Standard (required) SS variable for BuildTasks
	 * @var String
	 */
	protected $description = "Runs through all static configuration for review.";

	/**
	 * Array of configs - set like this:
	 * ClassName
	 * 		VariableName: VariableValue
	 * @var Array
	 */
	protected $configs = array();

	/**
	 * Array of definitions - set like this:
	 * ClassName
	 * 		VariableName: VariableValue
	 * @var Array
	 */
	protected $definitions = array();

	/**
	 * Array of defaults - set like this:
	 * ClassName
	 * 		VariableName: VariableValue
	 * @var Array
	 */
	protected $defaults = array();

	/**
	 * Standard (required) SS method, runs buildtask
	 */
	function run($request){
		$definitionsObject = new EcommerceConfigDefinitions();
		$this->definitions = $definitionsObject->Definitions();
		$configsObject = new EcommerceConfig();
		$this->configs = $configsObject->getCompleteDataSet();
		$this->defaults = $this->getDefaultValues();
		if($this->definitions) {
			if($this->configs) {
				if($this->defaults) {
					$this->checkFiles();
					$this->classesThatDoNotExist();
					$this->definitionsNotSet();
					$this->configsNotSet();
					$this->addEcommerceDBConfigToConfigs();
					$this->addOtherValuesToConfigs();
					$this->addPages();
					$this->orderSteps();
					$this->definedConfigs();
				}
				else {
					DB::alteration_message("ERROR: could not find any defaults", "deleted");
				}
			}
			else {
				DB::alteration_message("ERROR: could not find any configs", "deleted");
			}
		}
		else {
			DB::alteration_message("ERROR: could not find any defitions", "deleted");
		}

	}

	/**
	 * Check what files is being used
	 */
	protected function checkFiles(){
		$configsObject = new EcommerceConfig();
		DB::alteration_message("<h2>Files Used</h2>");
		$files = implode(", ", $configsObject->fileLocations());
		global $project;
		$baseFolder = Director::baseFolder();
		$projectFolder = $project."/_config";
		$baseAndProjectFolder = $baseFolder."/".$projectFolder;
		$file = "ecommerce.yaml";
		$projectFolderAndFile = $projectFolder."/".$file;
		$fullFilePath = $baseFolder."/".$projectFolderAndFile;
		$defaultFileFullPath = Director::baseFolder()."/".$this->defaultLocation;
		DB::alteration_message("Current files used: <strong style=\"color: darkRed\">".$files."</strong>, unless stated otherwise, all settings can be edited in these files (or file).", "created");
		if(!file_exists($baseAndProjectFolder)) {
			mkdir($baseAndProjectFolder);
		}
		if(!file_exists($fullFilePath)) {
			copy($defaultFileFullPath, $fullFilePath);
			DB::alteration_message("We have created a new configuration file for you.", "created");
		}
		if($files == $this->defaultLocation) {
			if(file_exists($fullFilePath)) {
				DB::alteration_message("A customisable configuration file exists here: $projectFolderAndFile, you should add the following to your _config.php file:
				<pre>EcommerceConfig::set_folder_and_file_locations(array(\"$projectFolderAndFile\"));</pre>
				 ", "created");
			}
		}
	}

	/**
	 * Work out items set in the configuration but not set in the config file.
	 */
	protected function definitionsNotSet(){
		DB::alteration_message("<h2>Set in configs but not defined</h2>");
		$allOK = true;
		foreach($this->configs as $className => $setting) {
			if(!isset($this->definitions[$className])) {
				$allOK = false;
				DB::alteration_message("$className", "deleted");
			}
			else {
				$classConfigs = $this->configs[$className];
				foreach($classConfigs as $key => $classConfig) {
					if(!isset($this->definitions[$className][$key])) {
						$allOK = false;
						DB::alteration_message("$className.$key", "deleted");
					}
				}
			}
		}
		if($allOK) {
			DB::alteration_message("Perfect match, nothing to report", "created");
		}
		else {
			DB::alteration_message("Recommended course of action: remove from your config as these are superfluous!", "edited");
		}
	}


	/**
	 * Work out items set in the configuration but not set in the config file.
	 */
	protected function classesThatDoNotExist(){
		DB::alteration_message("<h2>Classes that do not exist</h2>");
		$allOK = true;
		foreach($this->configs as $className => $setting) {
			if(!class_exists($className)) {
				$allOK = false;
				DB::alteration_message("$className", "deleted");
			}
		}
		if($allOK) {
			DB::alteration_message("Perfect match, nothing to report", "created");
		}
		else {
			DB::alteration_message("Recommended course of action: remove from your config file and review if any other action needs to be taken.", "edited");
		}

	}


	/**
	 * Work out items set in the definitions but not set in the config file.
	 */
	protected function configsNotSet(){
		$allOK = true;
		DB::alteration_message("<h2>Defined but not set in configs</h2>");
		foreach($this->definitions as $className => $setting) {
			if(!isset($this->configs[$className])) {
				DB::alteration_message("$className", "deleted");
				$allOK = false;
			}
			else {
				$classConfigs = $this->definitions[$className];
				foreach($classConfigs as $key => $classConfig) {
					if(!isset($this->configs[$className][$key])) {
						DB::alteration_message("$className.$key", "deleted");
						$allOK = false;
					}
				}
			}
		}
		if($allOK) {
			DB::alteration_message("Perfect match, nothing to report", "created");
		}
		else {
			DB::alteration_message("Recommended course of action: add to your config file.", "edited");
		}
	}

	/**
	 * Work out items set in the definitions but not set in the config file.
	 */
	protected function definedConfigs(){
		$htmlHeader = "
		<style>
			th[scope='col'] {text-align: left; border-bottom: 3px solid blue;padding-top: 40px;}
			td {vertical-align: top; border-left: 1px solid blue; border-bottom: 1px solid blue;}
			td span {color: grey; font-size: 0.8em;}
			td span {color: grey; font-size: 0.8em; display: block}
			.sameConfig {color: #333;}
			.newConfig{color: green; font-weight: bold; font-size: 1.2em;}
			#TOC {
				-moz-column-count: 3;
				-moz-column-gap: 20px;
				-webkit-column-count: 3;
				-webkit-column-gap: 20px;
				column-count: 3;
				column-gap: 20px;
			}
			a.backToTop {display: block; font-size: 0.8em; }
		</style>
		<h2>Configuration Report</h2>";
		$htmlTable = "
		<table summary=\"list of configs\">
		";
		$oldClassName = "";
		$htmlTOC = "<ol id=\"TOC\">";
		$count = 0;
		foreach($this->configs as $className => $settings) {
			$count++;
			$htmlTOC .= "<li><a href=\"#$className\">$className</a></li>";
			if($className != $oldClassName) {
				$htmlTable .= "<tr id=\"$className\"><th colspan=\"2\" scope=\"col\">$count. $className <a class=\"backToTop\" href=\"#TOC\">top</a></th></tr>";
				$oldClassName = $className;
			}

			foreach($settings as $key => $classConfig) {
				if(!isset($this->defaults[$className][$key])) {
					echo "Could not retrieve default value for: $className $key <hr />";
				}
				else {
					$defaultValue = print_r($this->defaults[$className][$key], 1);
				}
				$actualValue = print_r($this->configs[$className][$key], 1);
				if($actualValue == $defaultValue) {
					$class = "sameConfig";
					$defaultValue = "";
				}
				else {
					$class = "newConfig";
				}
				if($actualValue === false || $actualValue === "") {
					$actualValue = "[FALSE] / [EMPTY STRING]";
				}
				if($actualValue === null) {
					$actualValue = "[NULL]";
				}
				if($actualValue === "1") {
					$actualValue = "[TRUE]";
				}
				if(!isset($this->definitions[$className][$key])) {
					$description = "<span style=\"color: red; font-weight: bold\">ERROR: no longer required in configs!</span>";
				}
				else {
					$description = $this->definitions[$className][$key];
				}
				$htmlTable .= "<tr>
			<td>
				$key
				<span>$description</span>
			</td>
			<td class=\"$class\">
				<pre>$actualValue</pre>
				<span><pre>$defaultValue</span></span>
			</td>
		</tr>";
			}
		}
		$htmlEnd = "
		</table>
		<h2>--- THE END ---</h2>
		";
		$htmlTOC .= "</ol>";
		echo $htmlHeader.$htmlTOC.$htmlTable.$htmlEnd;
	}

	protected function getDefaultValues(){
		require_once 'thirdparty/spyc/spyc.php';
		$fixtureFolderAndFile = Director::baseFolder()."/".$this->defaultLocation;
		$parser = new Spyc();
		return $parser->loadFile($fixtureFolderAndFile);
	}

	/**
	 * Adding EcommerceDBConfig values
	 */
	protected function addEcommerceDBConfigToConfigs(){
		$ecommerceConfig = EcommerceDBConfig::current_ecommerce_db_config();
		$fields = $ecommerceConfig->fieldLabels();
		foreach($fields as $field => $description) {
			if($field != "Title") {
				$defaultsDefaults = $ecommerceConfig->stat("defaults");
				$this->definitions["EcommerceDBConfig"][$field] = "$description. <br />THIS IS SET IN THE <a href=\"/admin/shop\">Ecommerce Configuration</a>";
				$this->configs["EcommerceDBConfig"][$field] = $ecommerceConfig->$field;
				$this->defaults["EcommerceDBConfig"][$field] = isset($defaultsDefaults[$field]) ? $defaultsDefaults[$field] : "no default set";
				$imageField = $field."ID";
				if(isset($ecommerceConfig->$imageField)) {
					if($image = $ecommerceConfig->$field()) {
						if($image->exists() && $image instanceOf Image) {
							$this->configs["EcommerceDBConfig"][$field] = "[Image]  --- <img src=\"".$image->Link()."\" />";
						}
					}
				}
			}
		}
	}


	protected function addOtherValuesToConfigs(){
		$this->definitions["Payment"]["site_currency"] = "Default currency for the site. <br />SET USING Payment::set_site_currency(\"NZD\") in the _config.php FILES";
		$this->configs["Payment"]["site_currency"] = Payment::site_currency()." ";
		$this->defaults["Payment"]["site_currency"] = "[no default set]";

		$this->definitions["Geoip"]["default_country_code"] = "Default currency for the site. <br />SET USING Geoip::\$default_country_code in the _config.php FILES";
		$this->configs["Geoip"]["default_country_code"] = Geoip::$default_country_code;
		$this->defaults["Geoip"]["default_country_code"] = "[no default set]";

		$this->definitions["Email"]["admin_email_address"] = "Default administrator email. SET USING Email::\$admin_email_address = \"bla@ta.com\" in the _config.php FILES";
		$this->configs["Email"]["admin_email_address"] = Email::$admin_email_address;
		$this->defaults["Email"]["admin_email_address"] = "[no default set]";

		$siteConfig = SiteConfig::current_site_config();
		$this->definitions["SiteConfig"]["website_title"] = "The name of the website. This is <a href=\"/admin/show/root\">set in the site configuration</a>";
		$this->configs["SiteConfig"]["website_title"] = $siteConfig->Title;
		$this->defaults["SiteConfig"]["website_title"] = "[no default set]";
	}

	protected function addPages(){

		$checkoutPage = DataObject::get_one("CheckoutPage");
		$this->getPageDefinitions($checkoutPage);
		$this->definitions["Pages"]["CheckoutPage"] = "Page where customers finalise (checkout) their order. This page is required.<br />".($checkoutPage ? "<a href=\"/admin/show/".$checkoutPage->ID."/\">edit</a>" : "Create one in the <a href=\"/admin/\">CMS</a>");
		$this->configs["Pages"]["CheckoutPage"] = $checkoutPage ? "view: <a href=\"".$checkoutPage->Link()."\">".$checkoutPage->Title."</a><br />".$checkoutPage->configArray : " NOT CREATED!";
		$this->defaults["Pages"]["CheckoutPage"] = $checkoutPage ? $checkoutPage->defaultsArray : "[add page first to see defaults]";

		$orderConfirmationPage = DataObject::get_one("OrderConfirmationPage");
		$this->getPageDefinitions($orderConfirmationPage);
		$this->definitions["Pages"]["OrderConfirmationPage"] = "Page where customers review their order after it has been placed. This page is required.<br />".($orderConfirmationPage ? "<a href=\"/admin/show/".$orderConfirmationPage->ID."/\">edit</a>" : "Create one in the <a href=\"/admin/\">CMS</a>");
		$this->configs["Pages"]["OrderConfirmationPage"] = $orderConfirmationPage ? "view: <a href=\"".$orderConfirmationPage->Link()."\">".$orderConfirmationPage->Title."</a><br />".$orderConfirmationPage->configArray: " NOT CREATED!";
		$this->defaults["Pages"]["OrderConfirmationPage"] = $orderConfirmationPage ? $orderConfirmationPage->defaultsArray : "[add page first to see defaults]";

		$accountPage = DataObject::get_one("AccountPage");
		$this->getPageDefinitions($accountPage);
		$this->definitions["Pages"]["AccountPage"] = "Page where customers can review their account. This page is required.<br />".($accountPage ? "<a href=\"/admin/show/".$accountPage->ID."/\">edit</a>" : "Create one in the <a href=\"/admin/\">CMS</a>");
		$this->configs["Pages"]["AccountPage"] = $accountPage ? "view: <a href=\"".$accountPage->Link()."\">".$accountPage->Title."</a><br />".$accountPage->configArray : " NOT CREATED!";
		$this->defaults["Pages"]["AccountPage"] = $accountPage ? $accountPage->defaultsArray : "[add page first to see defaults]";

		$cartPage = DataObject::get_one("CartPage", "ClassName = 'CartPage'");
		$this->getPageDefinitions($cartPage);
		$this->definitions["Pages"]["CartPage"] = "Page where customers review their cart while shopping. This page is optional.<br />".($cartPage ? "<a href=\"/admin/show/".$cartPage->ID."/\">edit</a>" : "Create one in the <a href=\"/admin/\">CMS</a>");
		$this->configs["Pages"]["CartPage"] = $cartPage ? "view: <a href=\"".$cartPage->Link()."\">".$cartPage->Title."</a>, <a href=\"/admin/show/".$cartPage->ID."/\">edit</a><br />".$cartPage->configArray : " NOT CREATED!";
		$this->defaults["Pages"]["CartPage"] = $cartPage ? $cartPage->defaultsArray : "[add page first to see defaults]";

	}

	private function getPageDefinitions($page){
		if($page) {
			$fields = $page->combined_static($page->ClassName, "db", "Page");
			$defaultsArray = $page->stat("defaults", true);
			$configArray = array();
			foreach($fields as $fieldKey => $fieldType) {
				$configArray[$fieldKey] = $page->$fieldKey;
				if(!isset($defaultsArray[$fieldKey])) {
					$defaultsArray[$fieldKey] = "[default not set]";
				}
			}
			$page->defaultsArray = $defaultsArray;
			$page->configArray = print_r($configArray, 1);
		}
	}


	function orderSteps(){
		$steps = DataObject::get("OrderStep");
		foreach($steps as $step) {
			$fields = $step->combined_static($step->ClassName, "db");
			$defaultsArray = $step->stat("defaults", true);
			$configArray = array();
			foreach($fields as $fieldKey => $fieldType) {
				$configArray[$fieldKey] = $step->$fieldKey;
				if(!isset($defaultsArray[$fieldKey])) {
					$defaultsArray[$fieldKey] = "[default not set]";
				}
			}
			$this->definitions["OrderStep"][$step->Code] = $step->Description;
			$this->configs["OrderStep"][$step->Code] = $configArray;
			$this->defaults["OrderStep"][$step->Code] = $defaultsArray;
		}
	}

}
