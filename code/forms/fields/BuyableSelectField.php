<?php
/**
 * Text input field that allows the user to select a Buyable
 * using the auto-complete technique from jQuery UI.
 *
 * @authors: Nicolaas [at] Sunny Side Up .co.nz
 * @package: ecommerce
 * @sub-package: forms
 * @inspiration: https://github.com/sheadawson/silverstripe-zenautocompletefield
 **/

class BuyableSelectField extends FormField {


	/**
	 * number of suggestions
	 * @var Int
	 */
	protected $countOfSuggestions;

	/**
	 * @var FormField
	 */
	protected $fieldFindBuyable = null;

	/**
	 * @var FormField
	 */
	protected $fieldSelectedBuyable = null;

	/**
	 * @var DataObject
	 */
	protected $buyable = null;

	/**
	 * @param String $name
	 * @param String $title
	 * @param Object $buyable - currently selected buyable
	 * @param Int $countOfSuggestions - number of suggestions shown (max)
	 * @param Form $form
	 */
	function __construct($name, $title = null, $buyable = null, $countOfSuggestions = 7, $form = null) {
		$this->countOfSuggestions = $countOfSuggestions;
		$this->fieldFindBuyable = new TextField("{$name}[FindBuyable]", _t('BuyableSelectField.FIELDLABELFINDBUYABLE', 'Find Product'));
		$this->fieldSelectedBuyable = new ReadonlyField("{$name}[SelectedBuyable]", _t('BuyableSelectField.FIELDLABELSELECTEDBUYABLE', ''), _t('BuyableSelectField.NONE', 'None'));
		$this->buyable = $buyable;
		if($this->buyable) {
			$value = $this->buyable->FullName ? $this->buyable->FullName : $this->buyable->getTitle();
		}
		else {
			$value = "";
		}
		parent::__construct($name, $title, $value, $form);
	}

	function hasData() { return false; }

	/**
	 * @return string
	 */
	function Field() {
		if(!$this->form->dataFieldByName("Version")) {
			user_error("You must have a Version field in your form");
		}
		if(!$this->form->dataFieldByName("BuyableClassName")) {
			user_error("You must have a BuyableClassName field in your form");
		}
		if(!$this->form->dataFieldByName("BuyableID")) {
			user_error("You must have a BuyableID field in your form");
		}
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/jquery.ui.core.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/jquery.ui.widget.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/jquery.ui.position.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/jquery.ui.autocomplete.js');
		Requirements::javascript('ecommerce/javascript/EcomBuyableSelectField.js');
		Requirements::customScript($this->getJavascript(), "BuyableSelectField".$this->id());
		Requirements::themedCSS("BuyableSelectField");
		return
		"<div class=\"fieldgroup\">" .
			"<div class=\"selectedBuyable fieldGroupInner\">" . $this->fieldSelectedBuyable->SmallFieldHolder()."</div>".
			"<div class=\"findBuyable fieldGroupInner\">" . $this->fieldFindBuyable->SmallFieldHolder()."</div>" .
		"</div>";
	}

	protected function getJavascript(){
		return '
		EcomBuyableSelectField.set_fieldName("'.Convert::raw2js($this->name()).'");
		EcomBuyableSelectField.set_countOfSuggestions('.$this->countOfSuggestions.');
		EcomBuyableSelectField.set_selectedFieldName("'.Convert::raw2js($this->fieldSelectedBuyable->name()).'");
		EcomBuyableSelectField.set_selectedFieldID("'.Convert::raw2js($this->fieldSelectedBuyable->name()).'");
		';
	}

	function setValue($data) {
		if($this->buyable) {
			$value = $this->buyable->FullName ? $this->buyable->FullName : $this->buyable->getTitle();
			$this->fieldSelectedBuyable->setValue($value);
		}
	}

	/**
	 * Returns a readonly version of this field.
	 */
	function performReadonlyTransformation() {
		$clone = clone $this;
		$clone->setReadonly(true);
		return $clone;
	}

	/**
	 */
	function setReadonly($bool) {
		parent::setReadonly($bool);
		if($bool) {
			$this->fieldFindBuyable = $this->fieldFindBuyable->performReadonlyTransformation();
			$this->fieldSelectedBuyable = $this->fieldSelectedBuyable->performReadonlyTransformation();
		}
	}

}

class BuyableSelectField_DataList extends Controller {

	protected $fieldsToSearch = array(
		"InternalItemID",
		"Title",
		"FullName",
		"MetaKeywords"
	);

	function json($request){
		$countOfSuggestions = $request->requestVar("countOfSuggestions");
		$term = Convert::raw2sql($request->requestVar("term"));
		$arrayOfBuyables = EcommerceConfig::get("EcommerceDBConfig", "array_of_buyables");
		$arrayOfAddedItemIDsByClassName = array();
		$lengthOfFieldsToSearch = count($this->fieldsToSearch);
		$lenghtOfBuyables = count($arrayOfBuyables);
		$array = array();
		//search by InternalID ....
		$absoluteCount = 0;
		$buyables = array();
		foreach($arrayOfBuyables as $key => $buyableClassName) {
			$buyables[$key] = array();
			$singleton = singleton($buyableClassName);
			$buyables[$key]["Singleton"] = $singleton;
			$buyables[$key]["ClassName"] = $buyableClassName;
			$buyables[$key]["TableName"] = $buyableClassName;
			if($singleton instanceOf SiteTree) {
				if(Versioned::current_stage() == "Live") {
					$buyables[$key]["TableName"] .= "_Live";
				}
			}
		}
		unset($arrayOfBuyables);
		while((count($array) <= $countOfSuggestions) && ($absoluteCount < 300)) {
			$absoluteCount++;
			for($i = 0; $i < $lengthOfFieldsToSearch; $i++) {
				$fieldName = $this->fieldsToSearch[$i];
				for($j = 0; $j < $lenghtOfBuyables; $j++) {
					$buyableArray = $buyables[$j];
					$singleton = $buyableArray["Singleton"];
					$className = $buyableArray["ClassName"];
					$tableName = $buyableArray["TableName"];
					if(!isset($arrayOfAddedItemIDsByClassName[$className])) {
						$arrayOfAddedItemIDsByClassName[$className] = array(-1 => -1);
					}
					if($singleton->hasDatabaseField($fieldName)) {
						$where = "\"$fieldName\" LIKE '%$term%'
								AND \"".$tableName."\".\"ID\" NOT IN (".implode(",", $arrayOfAddedItemIDsByClassName[$className]).")
								AND \"AllowPurchase\" = 1";
						$obj = DataObject::get_one($className,$where);
						if($obj) {
							//we found an object, we dont need to find it again.
							$arrayOfAddedItemIDsByClassName[$className][$obj->ID] = $obj->ID;
							//now we are only going to add it, if it is available!
							if($obj->canPurchase()) {
								$useVariationsInstead = false;
								if($obj->hasExtension("ProductWithVariationDecorator")) {
									$variations = $obj->Variations();
									if($variations->count()) {
										$useVariationsInstead = true;
									}
								}
								if(!$useVariationsInstead) {
									$name = $obj->FullName ? $obj->FullName : $obj->getTitle();
									$array[$className.$obj->ID] = array(
										"ClassName" => $className,
										"ID" => $obj->ID,
										"Version" => $obj->Version,
										"Title" => $name
									);
								}
							}
						}
					}
					else {
						//echo $singleton->ClassName ." does not have $fieldName";
					}
				}
			}
		}
		//remove KEYS
		$finalArray = array();
		$count = 0;
		foreach($array as $item) {
			if($count < $countOfSuggestions) {
				$finalArray[] = $item;
			}
			$count++;
		}
		return $this->array2json($finalArray);
	}


	/**
	 * converts an Array into JSON and formats it nicely for easy debugging
	 * @param Array $array
	 * @return JSON
	 */
	protected function array2json($array){
		$json = Convert::array2json($array);
		return $json;
	}


}
