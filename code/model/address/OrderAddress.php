<?php


/**
 * @description: each order has an address: a Shipping and a Billing address
 * This is a base-class for both.
 *
 *
 * @authors: Nicolaas [at] Sunny Side Up .co.nz
 * @package: ecommerce
 * @sub-package: address
 * @inspiration: Silverstripe Ltd, Jeremy
 **/

class OrderAddress extends DataObject {

	/**
	 * standard SS static definition
	 */
	public static $singular_name = "Order Address";
		function i18n_singular_name() { return _t("OrderAddress.ORDERADDRESS", "Order Address");}

	/**
	 * standard SS static definition
	 */
	public static $plural_name = "Order Addresses";
		function i18n_plural_name() { return _t("OrderAddress.ORDERADDRESSES", "Order Addresses");}

	/**
	 * standard SS static definition
	 */
	public static $casting = array(
		"FullName" => "Text",
		"FullString" => "Text",
		"JSONData" => "Text"
	);

	/**
	 * returns the id of the MAIN country field for template manipulation.
	 * Main means the one that is used as the primary one (e.g. for tax purposes).
	 * @see EcommerceConfig::get("OrderAddress", "use_shipping_address_for_main_region_and_country")
	 * @return String
	 */
	public static function get_country_field_ID() {
		if(EcommerceConfig::get("OrderAddress", "use_shipping_address_for_main_region_and_country")) {
			return "ShippingCountry";
		}
		else {
			return "Country";
		}
	}

	/**
	 * returns the id of the MAIN region field for template manipulation.
	 * Main means the one that is used as the primary one (e.g. for tax purposes).
	 * @return String
	 */
	public static function get_region_field_ID() {
		if(EcommerceConfig::get("OrderAddress", "use_shipping_address_for_main_region_and_country")) {
			return "ShippingRegion";
		}
		else {
			return "Region";
		}
	}

	/**
	 * There might be times when a modifier needs to make an address field read-only.
	 * In that case, this is done here.
	 *
	 * @var Array
	 */
	protected $readOnlyFields = array();

	/**
	 * sets a field to readonly state
	 * we use this when modifiers have been set that require a field to be a certain value
	 * for example - a PostalCode field maybe set in the modifier.
	 *
	 * @param String $fieldName
	 */
	function addReadOnlyField($fieldName) {
		$this->readOnlyFields[$fieldName] = $fieldName;
	}

	/**
	 * removes a field from the readonly state
	 *
	 * @param String $fieldName
	 */
	function removeReadOnlyField($fieldName) {
		unset($this->readOnlyFields[$fieldName]);
	}

	/**
	 * save edit status for speed's sake
	 * @var Boolean
	 */
	protected $_canEdit = null;

	/**
	 * save view status for speed's sake
	 * @var Boolean
	 */
	protected $_canView = null;


	/**
	 * standard SS method
	 * @param Member $member
	 * @return Boolean
	 **/
	function canCreate($member = null) {
		return true;
	}

	/**
	 * Standard SS method
	 * This is an important method.
	 * @param Member $member
	 * @return Boolean
	 **/
	function canView($member = null) {
		if(!$this->exists()) {
			return $this->canCreate($member);
		}
		if($this->_canView === null) {
			$this->_canView = false;
			if($this->Order()) {
				if($this->Order()->exists()) {
					if($this->Order()->canView($member)) {
						$this->_canView = true;
					}
				}
			}
		}
		return $this->_canView;
	}

	/**
	 * Standard SS method
	 * This is an important method.
	 * @param Member $member
	 * @return Boolean
	 **/
	function canEdit($member = null) {
		if(!$this->exists()) {
			return $this->canCreate($member);
		}
		if($this->_canEdit === null) {
			$this->_canEdit = false;
			if($this->Order()) {
				if($this->Order()->exists()) {
					if($this->Order()->canEdit($member)) {
						$this->_canEdit = true;
					}
				}
			}
		}
		return $this->_canEdit;
	}

	/**
	 * Determine which properties on the DataObject are
	 * searchable, and map them to their default {@link FormField}
	 * representations. Used for scaffolding a searchform for {@link ModelAdmin}.
	 *
	 * Some additional logic is included for switching field labels, based on
	 * how generic or specific the field type is.
	 *
	 * Used by {@link SearchContext}.
	 *
	 * @param array $_params
	 * 	'fieldClasses': Associative array of field names as keys and FormField classes as values
	 * 	'restrictFields': Numeric array of a field name whitelist
	 * @return FieldList
	 */
	public function scaffoldSearchFields($_params = null) {
		$fields = parent::scaffoldSearchFields($_params);
		$fields = parent::scaffoldSearchFields();
		$fields->replaceField("OrderID", new NumericField("OrderID", "Order Number"));
		return $fields;
	}

	/**
	 *
	 *
	 * @return FieldList
	 */
	protected function getEcommerceFields() {
		return new FieldList();
	}

	/**
	 * put together a textfield for a postal code field
	 *
	 * @param String $name - name of the field
	 * @return TextField
	 **/
	protected function getPostalCodeField($name) {
		$field = new TextField($name, _t('OrderAddress.POSTALCODE','Postal Code'));
		$postalCodeURL = EcommerceDBConfig::current_ecommerce_db_config()->PostalCodeURL;
		$postalCodeLabel = EcommerceDBConfig::current_ecommerce_db_config()->PostalCodeLabel;
		if($postalCodeURL && $postalCodeLabel){
			$prefix = EcommerceConfig::get("OrderAddress", "field_class_and_id_prefix");
			$field->setRightTitle('<a href="'.$postalCodeURL.'" id="'.$prefix.$name.'Link" class="'.$prefix.'postalCodeLink">'.$postalCodeLabel.'</a>');
		}
		return $field;
	}

	/**
	 * put together a dropdown for the region field
	 *
	 * @param String $name - name of the field
	 * @return DropdownField
	 **/
	protected function getRegionField($name) {
		if(EcommerceRegion::show()) {
			$regionsForDropdown = EcommerceRegion::list_of_allowed_entries_for_dropdown();
			$title = singleton("EcommerceRegion")->i18n_singular_name();
			$regionField = new DropdownField($name,$title, $regionsForDropdown);
			if(count($regionsForDropdown) < 2) {
				$regionField = $regionField->performReadonlyTransformation();
				if(count($regionsForDropdown) < 1) {
					$regionField = new HiddenField($name, '', 0);
				}
			}

		}
		else {
			//adding region field here as hidden field to make the code easier below...
			$regionField = new HiddenField($name, '', 0);
		}
		$prefix = EcommerceConfig::get("OrderAddress", "field_class_and_id_prefix");
		$regionField->addExtraClass($prefix.'ajaxRegionField');
		return $regionField;
	}

	/**
	 * put together a dropdown for the country field
	 *
	 * @param String $name - name of the field
	 * @return DropdownField
	 **/
	protected function getCountryField($name) {
		$countriesForDropdown = EcommerceCountry::list_of_allowed_entries_for_dropdown();
		$title = singleton("EcommerceCountry")->i18n_singular_name();
		$countryField = new DropdownField($name, $title, $countriesForDropdown, EcommerceCountry::get_country());
		if(count($countriesForDropdown) < 2) {
			$countryField = $countryField->performReadonlyTransformation();
			if(count($countriesForDropdown) < 1) {
				$countryField = new HiddenField($name, '', "not available");
			}
		}
		$prefix = EcommerceConfig::get("OrderAddress", "field_class_and_id_prefix");
		$countryField->addExtraClass($prefix.'ajaxCountryField');
		return $countryField;
	}

	/**
	 * makes selected fields into read only using the $this->readOnlyFields array
	 *
	 * @param FieldList $fields
	 * @return FieldList
	 */
	protected function makeSelectedFieldsReadOnly(FieldList $fields) {
		$this->extend("augmentMakeSelectedFieldsReadOnly");
		if(is_array($this->readOnlyFields) && count($this->readOnlyFields) ) {
			foreach($this->readOnlyFields as $readOnlyField) {
				if($oldField = $fields->fieldByName($readOnlyField)) {
					$fields->replaceField($readOnlyField, $oldField->performReadonlyTransformation());
				}
			}
		}
		return $fields;
	}

	/**
	 * Saves region - both shipping and billing fields are saved here for convenience sake (only one actually gets saved)
	 * NOTE: do not call this method SetCountry as this has a special meaning! *
	 *
	 * @param Integer -  RegionID
	 **/
	public function SetRegionFields($regionID) {
		$this->RegionID = $regionID;
		$this->ShippingRegionID = $regionID;
		$this->write();
	}

	/**
	 * Saves country - both shipping and billing fields are saved here for convenience sake (only one actually gets saved)
	 * NOTE: do not call this method SetCountry as this has a special meaning!
	 *
	 * @param String - CountryCode - e.g. NZ
	 */
	public function SetCountryFields($countryCode) {
		$this->Country = $countryCode;
		$this->ShippingCountry = $countryCode;
		$this->write();
	}

	/**
	 * Casted variable
	 * returns the full name of the person, e.g. "John Smith"
	 *
	 * @return String
	 */
	public function getFullName() {
		$fieldNameField = $this->fieldPrefix()."FirstName";
		$fieldFirst = $this->$fieldNameField;
		$lastNameField =  $this->fieldPrefix()."Surname";
		$fieldLast = $this->$lastNameField;
		return $fieldFirst.' '.$fieldLast;
	}
		public function FullName(){ return $this->getFullName();}

	/**
	 * Casted variable
	 * returns the full strng of the record
	 *
	 * @return String
	 */
	public function FullString(){ return $this->getFullString();}
	public function getFullString() {
		return $this->renderWith("Order_Address".str_replace("Address", "", $this->ClassName)."FullString");
	}

	/**
	 * returns a string that can be used to find out if two addresses are the same.
	 * @return String
	 */
	protected function comparisonString(){
		$comparisonString = "";
		$excludedFields = array("ID", "OrderID");
		$fields = $this->stat("db");
		$regionFieldName = $this->fieldPrefix()."RegionID";
		$fields[$regionFieldName] = $regionFieldName;
		if($fields) {
			foreach($fields as $field => $useless) {
				if(!in_array($field, $excludedFields)) {
					$comparisonString .= preg_replace('/\s+/', '', $this->$field);
				}
			}
		}
		return strtolower(trim($comparisonString));
	}

	/**
	 * @param String - $prefix = either "" or "Shipping"
	 * @return Array of fields for an Order DataObject
	 **/
	protected function getFieldNameArray($fieldPrefix = '') {
		$fieldNameArray = array(
			"Email",
			"Prefix",
			"FirstName",
			"Surname",
			"Address",
			"Address2",
			"City",
			"PostalCode",
			"RegionID",
			"Country",
			"Phone",
			"MobilePhone",
		);
		if($fieldPrefix) {
			foreach($fieldNameArray as $key => $value) {
				$fieldNameArray[$key] = $fieldPrefix.$value;
			}
		}
		return $fieldNameArray;
	}

	/**
	 * returns the field prefix string for shipping addresses
	 * @return String
	 **/
	protected function baseClassLinkingToOrder() {
		if($this instanceOf BillingAddress) {
			return "BillingAddress";
		}
		elseif($this instanceOf ShippingAddress) {
			return "ShippingAddress";
		}
	}

	/**
	 * returns the field prefix string for shipping addresses
	 * @return String
	 **/
	protected function fieldPrefix() {
		if($this->baseClassLinkingToOrder() == "BillingAddress") {
			return "";
		}
		else {
			return "Shipping";
		}
	}

	/**
	  *@todo: are there times when the Shipping rather than the Billing address should be linked?
	 * Copies the last address used by the member.
	 *
	 * @param Object (Member) $member
	 * @param Boolean $write - should the address be written
	 * @return DataObject (OrderAddress / ShippingAddress / BillingAddress)
	 **/
	public function FillWithLastAddressFromMember(Member $member, $write = false) {
		$excludedFields = array("ID", "OrderID");
		$fieldPrefix = $this->fieldPrefix();
		if($member && $member->exists()) {
			$oldAddress = $this->lastAddressFromMember($member);
			if($oldAddress) {
				$fieldNameArray = $this->getFieldNameArray($fieldPrefix);
				foreach($fieldNameArray as $field) {
					if(!$this->$field && isset($oldAddress->$field) && !in_array($field, $excludedFields)) {
						$this->$field = $oldAddress->$field;
					}
				}
			}
			//copy data from  member
			if($this instanceOf BillingAddress) {
				$this->Email = $member->Email;
			}
			$fieldNameArray = array("FirstName" => $fieldPrefix."FirstName", "Surname" => $fieldPrefix."Surname");
			foreach($fieldNameArray as $memberField => $fieldName) {
				//NOTE, we always override the Billing Address (which does not have a fieldPrefix)
				if(!$this->$fieldName || $this instanceOf BillingAddress) {$this->$fieldName = $member->$memberField;}
			}
		}
		if($write) {
			$this->write();
		}
		return $this;
	}

	/**
	 * Finds the last address used by this member
	 * @param Object (Member)
	 * @return Null | DataObject (ShippingAddress / BillingAddress)
	 **/
	protected function lastAddressFromMember(Member $member = null) {
		$addresses = $this->previousAddressesFromMember($member, true);
		if($addresses->count()) {
			return $addresses->First();
		}
	}

	/**
	 * Finds the last order used by this member
	 * @param Object (Member)
	 * @return Null | DataObject (Order)
	 **/
	protected function lastOrderFromMember(Member $member = null) {
		$orders = $this->previousOrdersFromMember($member, true);
		if($orders->count()) {
			return $orders->First();
		}
	}


	/**
	 * Finds previous addresses from the member of the current address
	 *
	 * @param Member $member
	 * @param Boolean $onlyLastRecord - only select one
	 * @param Boolean $keepDoubles - keep addresses that are the same (if set to false, only unique addresses are returned)
	 * @return ArrayList (BillingAddresses | ShippingAddresses)
	 **/
	protected function previousAddressesFromMember(Member $member = null, $onlyLastRecord = false, $keepDoubles = false) {
		$returnArrayList = new ArrayList();
		$orders = $this->previousOrdersFromMember($member, $onlyLastRecord);
		if($orders->count()) {
			$fieldName = $this->ClassName."ID";
			$array = $orders->map($fieldName, $fieldName);
			if(is_array($array) && count($array)) {
				$limit = null;
				if($onlyLastRecord) {
					$limit = 1;
				}
				$className = $this->ClassName;
				$addresses = $className::get()
					->filter(array(
						"ID" => $array,
						"Obsolete" => 0
					))
					->sort("LastEdited", "DESC")
					//WHY ??? Do we include Orders here as Inner Join?
					->innerJoin("Order", "\"Order\".\"$fieldName\" = \"".$this->ClassName."\".\"ID\"");
				if($addresses->count()) {
					$addressCompare = array();
					foreach($addresses as $address) {
						$comparisonString = $address->comparisonString();
						if((in_array($comparisonString, $addressCompare)) && (!$keepDoubles)) {

						}
						else {
							$addressCompare[$address->ID] = $comparisonString;
							$returnArrayList = $returnArrayList->push($address);
						}
					}
				}
			}
		}
		return $returnArrayList;
	}

	/**
	 * make an address obsolete and include all the addresses that are identical.
	 * @param Member $member
	 */
	public function MakeObsolete(Member $member = null){
		$addresses = $this->previousAddressesFromMember($member, $onlyLastRecord = false, $includeDoubles = true);
		$comparisonString = $this->comparisonString();
		if($addresses->count()) {
			foreach($addresses as $address) {
				if($address->comparisonString() == $comparisonString) {
					$address->Obsolete = 1;
					$address->write();
				}
			}
		}
		$this->Obsolete = 1;
		$this->write();
	}

	/**
	 * Finds previous orders from the member of the current address
	 *
	 * @param Member $member
	 * @param Boolean $onlyLastRecord - only return the last Order from Member (still returns DataList)
	 * @return DataList
	 **/
	protected function previousOrdersFromMember(Member $member = null, $onlyLastRecord = false) {
		if(!$member) {
			$member = $this->getMemberFromOrder();
		}
		if($member && $member->exists()) {
			$fieldName = $this->ClassName."ID";
			$list = Order::get_datalist_of_orders_with_submit_record()
				->filter(array("MemberID" => $member->ID))
				->exclude(array($fieldName => $this->ID));
			if($onlyLastRecord) {
				$list = $list->limit(1);
			}
			return $list;
		}
	}

	/**
	 * find the member associated with the current Order and address.
	 * @Note: this needs to be public to give DODS (extensions access to this)
	 * @todo: can wre write $this->Order() instead????
	 *
	 * @return DataObject (Member) | Null
	 **/
	public function getMemberFromOrder() {
		if($this->exists()) {
			if($order = $this->Order()) {
				if($order->exists()) {
					if($order->MemberID) {
						return Member::get()->byID($order->MemberID);
					}
				}
			}
		}
	}

	/**
	 * standard SS method
	 * We "hackishly" ensure that the OrderID is set to the right value.
	 *
	 */
	function onAfterWrite(){
		parent::onAfterWrite();
		if($this->exists()) {
			$order = Order::get()
				->filter(array($this->ClassName."ID" => $this->ID))
				->First();
			if($order && $order->ID != $this->OrderID) {
				$this->OrderID = $order->ID;
				$this->write();
			}
		}
	}

	/**
	 * returns the link that can be used to remove (make Obsolete) an address.
	 * @return String
	 */
	function RemoveLink(){
		return ShoppingCart_Controller::remove_address_link($this->ID, $this->ClassName);
	}

	/**
	 * converts an address into JSON
	 * @return String (JSON)
	 */
	function getJSONData(){return $this->JSONData();}
	function JSONData(){
		$jsArray = array();
		if(!isset($fields)) {
			$fields = $this->stat("db");
			$regionFieldName = $this->fieldPrefix()."RegionID";
			$fields[$regionFieldName] = $regionFieldName;
		}
		if($fields) {
			foreach($fields as $name => $field) {
				$jsArray[$name] = $this->$name;
			}
		}
		return Convert::array2json($jsArray);
	}


	/**
	 * returns the instance of EcommerceDBConfig
	 *
	 * @return EcommerceDBConfig
	 **/
	public function EcomConfig(){
		return EcommerceDBConfig::current_ecommerce_db_config();
	}


}

