<?php

/**
 * @description: see OrderStep.md
 *
 *
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 * @package: ecommerce
 * @sub-package: model
 *
 **/

class OrderStep extends DataObject {
	//database
	public static $db = array(
		"Name" => "Varchar(50)",
		"Code" => "Varchar(50)",
		"Description" => "Text",
		"CustomerMessage" => "HTMLText",
		//customer privileges
		"CustomerCanEdit" => "Boolean",
		"CustomerCanCancel" => "Boolean",
		"CustomerCanPay" => "Boolean",
		//What to show the customer...
		"ShowAsUncompletedOrder" => "Boolean",
		"ShowAsInProcessOrder" => "Boolean",
		"ShowAsCompletedOrder" => "Boolean",
		"HideStepFromCustomer" => "Boolean",
		//sorting index
		"Sort" => "Int"
	);

	public static $indexes = array(
		"Code" => true,
		"Sort" => true
	);

	public static $has_many = array(
		"Orders" => "Order",
		"OrderEmailRecords" => "OrderEmailRecord"
	);

	public static $field_labels = array(
		"Sort" => "Sorting Index",
		"CustomerCanEdit" => "Customer can edit order",
		"CustomerCanPay" => "Customer can pay order",
		"CustomerCanCancel" => "Customer can cancel order"
	);

	public static $summary_fields = array(
		"Name" => "Name",
		"CustomerCanEditNice" => "customer can edit",
		"CustomerCanPayNice" => "customer can pay",
		"CustomerCanCancelNice" => "customer can cancel",
		"ShowAsUncompletedOrderNice" => "show as uncomplete",
		"ShowAsInProcessOrderNice" => "show as in process",
		"ShowAsCompletedOrderNice" => "show as complete",
		"HideStepFromCustomerNice" => "hide step from customer"
	);

	public static $casting = array(
		"CustomerCanEditNice" => "Varchar",
		"CustomerCanPayNice" => "Varchar",
		"CustomerCanCancelNice" => "Varchar",
		"ShowAsUncompletedOrderNice" => "Varchar",
		"ShowAsInProcessOrderNice" => "Varchar",
		"ShowAsCompletedOrderNice" => "Varchar",
		"HideStepFromCustomerNice" => "Varchar"
	);

	public static $searchable_fields = array(
		'Name' => array(
			'title' => 'Name',
			'filter' => 'PartialMatchFilter'
		),
		'Code' => array(
			'title' => 'Code',
			'filter' => 'PartialMatchFilter'
		)
	);

	function CustomerCanEditNice() {return $this->getCustomerCanEditNice();}
	function getCustomerCanEditNice() {if($this->CustomerCanEdit) {return _t("OrderStep.YES", "Yes");}return _t("OrderStep.NO", "No");}

	function CustomerCanPayNice() {return $this->getCustomerCanPayNice();}
	function getCustomerCanPayNice() {if($this->CustomerCanPay) {return _t("OrderStep.YES", "Yes");}return _t("OrderStep.NO", "No");}

	function CustomerCanCancelNice() {return $this->getCustomerCanCancelNice();}
	function getCustomerCanCancelNice() {if($this->CustomerCanCancel) {return _t("OrderStep.YES", "Yes");}return _t("OrderStep.NO", "No");}

	function ShowAsUncompletedOrderNice() {return $this->getShowAsUncompletedOrderNice();}
	function getShowAsUncompletedOrderNice() {if($this->ShowAsUncompletedOrder) {return _t("OrderStep.YES", "Yes");}return _t("OrderStep.NO", "No");}

	function ShowAsInProcessOrderNice() {return $this->getShowAsInProcessOrderNice();}
	function getShowAsInProcessOrderNice() {if($this->ShowAsInProcessOrder) {return _t("OrderStep.YES", "Yes");}return _t("OrderStep.NO", "No");}

	function ShowAsCompletedOrderNice() {return $this->getShowAsCompletedOrderNice();}
	function getShowAsCompletedOrderNice() {if($this->ShowAsCompletedOrder) {return _t("OrderStep.YES", "Yes");}return _t("OrderStep.NO", "No");}

	function HideStepFromCustomerNice() {return $this->getHideStepFromCustomerNice();}
	function getHideStepFromCustomerNice() {if($this->HideStepFromCustomer) {return _t("OrderStep.YES", "Yes");}return _t("OrderStep.NO", "No");}

	public static $singular_name = "Order Step";
		function i18n_singular_name() { return _t("OrderStep.ORDERSTEP", "Order Step");}

	public static $plural_name = "Order Steps";
		function i18n_plural_name() { return _t("OrderStep.ORDERSTEPS", "Order Steps");}

	// SUPER IMPORTANT TO KEEP ORDER!
	public static $default_sort = "\"Sort\" ASC";

	public static function get_status_id_from_code($code) {
		if($otherStatus = DataObject::get_one("OrderStep", "\"Code\" = '".$code."'")) {
			return $otherStatus->ID;
		}
		return 0;
	}

	// MOST IMPORTANT DEFINITION!
	protected static $order_steps_to_include = array(
		"OrderStep_Created",
		"OrderStep_Submitted",
		"OrderStep_SentInvoice",
		"OrderStep_Paid",
		"OrderStep_Confirmed",
		"OrderStep_SentReceipt",
		"OrderStep_Sent",
		"OrderStep_Archived"
	);
		static function set_order_steps_to_include(array $a) {self::$order_steps_to_include = $a;}
		static function get_order_steps_to_include() {return(array)self::$order_steps_to_include;}
		static function add_order_step_to_include($s, $placeAfter) {
			array_splice(self::$order_steps_to_include, array_search($placeAfter, self::$order_steps_to_include) + 1, 0, $s);
		}
		static function remove_order_step_to_include($s) {
			foreach(self::$order_steps_to_include as $key => $step) {
				if($step == $s) {
					unset(self::$order_steps_to_include[$key]);
					return;
				}
			}
		}
		/**
		 *
		 *@return Array
		 **/
		static function get_codes_for_order_steps_to_include() {
			$newArray = array();
			$array = self::get_order_steps_to_include();
			if(is_array($array) && count($array)) {
				foreach($array as $className) {
					$code = singleton($className)->getMyCode();
					$newArray[$className] = strtoupper($code);
				}
			}
			return $newArray;
		}
		/**
		 *
		 *@return Array
		 **/
		static function get_not_created_codes_for_order_steps_to_include() {
			$array = self::get_codes_for_order_steps_to_include();
			if(is_array($array) && count($array)) {
				foreach($array as $className => $code) {
					if(DataObject::get_one($className)) {
						unset($array[$className]);
					}
				}
			}
			return $array;
		}

		/**
		 *
		 *@return String
		 **/
		function getMyCode() {
			$array = Object::uninherited_static($this->ClassName, 'defaults');
			if(!isset($array["Code"])) {user_error($this->class." does not have a default code specified");}
			return $array["Code"];
		}

	//IMPORTANT:: MUST HAVE Code must be defined!!!
	public static $defaults = array(
		"CustomerCanEdit" => 0,
		"CustomerCanCancel" => 0,
		"CustomerCanPay" => 1,
		"ShowAsUncompletedOrder" => 0,
		"ShowAsInProcessOrder" => 0,
		"ShowAsCompletedOrder" => 0,
		"Code" => "ORDERSTEP"
	);

	function populateDefaults() {
		parent::populateDefaults();
		$array = Object::uninherited_static($this->ClassName, 'defaults');
		if($array && count($array)) {
			foreach($array as $field => $value) {
				$this->$field = $value;
			}
		}
	}

	/**
	 *
	 *@return Fieldset
	 **/
	function getCMSFields() {
		$fields = parent::getCMSFields();
		//replacing
		$fields->addFieldToTab("Root.InternalDescription", new TextareaField("Description", _t("OrderStep.DESCRIPTION", "Explanation for internal use only"), 5));
		$fields->addFieldToTab("Root.CustomerMessage", new HTMLEditorField("CustomerMessage", _t("OrderStep.CUSTOMERMESSAGE", "Customer Message"), 5));
		//adding
		if(!$this->ID || !$this->isDefaultStatusOption()) {
			$fields->removeFieldFromTab("Root.Main", "Code");
			$fields->addFieldToTab("Root.Main", new DropdownField("ClassName", _t("OrderStep.TYPE", "Type"), self::get_not_created_codes_for_order_steps_to_include()), "Name");
		}
		if($this->isDefaultStatusOption()) {
			$fields->replaceField("Code", $fields->dataFieldByName("Code")->performReadonlyTransformation());
		}
		//headers
		$fields->addFieldToTab("Root.Main", new HeaderField("WARNING1", _t("OrderStep.CAREFUL", "CAREFUL! please edit with care"), 1), "Name");
		$fields->addFieldToTab("Root.Main", new HeaderField("WARNING2", _t("OrderStep.CUSTOMERCANCHANGE", "What can be changed during this step?"), 3), "CustomerCanEdit");
		$fields->addFieldToTab("Root.Main", new HeaderField("WARNING5", _t("OrderStep.ORDERGROUPS", "Order groups for customer?"), 3), "ShowAsUncompletedOrder");
		$fields->addFieldToTab("Root.Main", new HeaderField("WARNING7", _t("OrderStep.SORTINGINDEXHEADER", "Index Number (lower number come first)"), 3), "Sort");
		return $fields;
	}

	/**
	 * Allows the opportunity for the Order Step to add any fields to Order::getCMSFields
	 * Usually this is added before ActionNextStepManually
	 *@param FieldSet $fields
	 *@param Order $order
	 *@return FieldSet
	 **/
	function addOrderStepFields(&$fields, $order) {
		return $fields;
	}

	/**
	 *
	 *@return ValidationResult
	 **/
	function validate() {
		$result = DataObject::get_one(
			"OrderStep",
			" (\"Name\" = '".$this->Name."' OR \"Code\" = '".strtoupper($this->Code)."') AND \"OrderStep\".\"ID\" <> ".intval($this->ID));
		if($result) {
			return new ValidationResult(false, _t("OrderStep.ORDERSTEPALREADYEXISTS", "An order status with this name already exists. Please change the name and try again."));
		}
		$result = ($this->ClassName == "OrderStep" ? true : false);
		if($result) {
			return new ValidationResult(false, _t("OrderStep.ORDERSTEPCLASSNOTSELECTED", "You need to select the right order status class."));
		}
		return parent::validate();
	}


/**************************************************
* moving between statusses...
**************************************************/
	/**
		*initStep:
		* makes sure the step is ready to run.... (e.g. check if the order is ready to be emailed as receipt).
		* should be able to run this function many times to check if the step is ready
		*@see Order::doNextStatus
		*@param Order object
		*@return Boolean - true if the current step is ready to be run...
		**/
	public function initStep($order) {
		user_error("Please implement this in a subclass of OrderStep", E_USER_WARNING);
		return true;
	}

	/**
		*doStep:
	* should only be able to run this function once (init stops you from running it twice - in theory....)
		*runs the actual step
	*@see Order::doNextStatus
		*@param Order object
		*@return Boolean - true if run correctly
		**/
	public function doStep($order) {
		user_error("Please implement this in a subclass of OrderStep", E_USER_WARNING);
		return true;
	}

	/**
		*nextStep:
		*returns the next step (checks if everything is in place for the next step to run...)
	*@see Order::doNextStatus
		*@param Order object
		*@return DataObject | Null (next step OrderStep object)
		**/
	public function nextStep($order) {
		$nextOrderStepObject = DataObject::get_one("OrderStep", "\"Sort\" > ".$this->Sort);
		if($nextOrderStepObject) {
			return $nextOrderStepObject;
		}
		return null;
	}



/**************************************************
* Boolean checks
**************************************************/

	/**
	 *
	 *@return Boolean
	 **/
	public function hasPassed($code, $orIsEqualTo = false) {
		$otherStatus = DataObject::get_one("OrderStep", "\"Code\" = '".$code."'");
		if($otherStatus) {
			if($otherStatus->Sort < $this->Sort) {
				return true;
			}
			if($orIsEqualTo && $otherStatus->Code == $this->Code) {
				return true;
			}
		}
		else {
			user_error("could not find $code in OrderStep", E_USER_NOTICE);
		}
		return false;
	}

	/**
	 *
	 *@return Boolean
	 **/
	public function hasPassedOrIsEqualTo($code) {
		return $this->hasPassed($code, true);
	}

	/**
	 *
	 *@return Boolean
	 **/
	public function hasNotPassed($code) {
		return (bool)!$this->hasPassed($code, true);
	}

	/**
	 *
	 *@return Boolean
	 **/
	public function isBefore($code) {
		return (bool)!$this->hasPassed($code, false);
	}

	/**
	 *
	 *@return Boolean
	 **/
	protected function isDefaultStatusOption() {
		return in_array($this->Code, self::get_codes_for_order_steps_to_include());
	}

	//EMAIL

	/**
	 *
	 *@return Boolean
	 **/
	protected function hasBeenSent($order) {
		return DataObject::get_one("OrderEmailRecord", "\"OrderEmailRecord\".\"OrderID\" = ".$order->ID." AND \"OrderEmailRecord\".\"OrderStepID\" = ".$this->ID." AND	\"OrderEmailRecord\".\"Result\" = 1");
	}

/**************************************************
* Silverstripe Standard Data Object Methods
**************************************************/

	/**
	 *
	 *@return Boolean
	 **/
	public function canDelete($member = null) {
		if($order = DataObject::get_one("Order", "\"StatusID\" = ".$this->ID)) {
			return false;
		}
		if($this->isDefaultStatusOption()) {
			return false;
		}
		return true;
	}

	/**
	 *
	 *@return Boolean
	 **/
	public function canAdd($member = null) {
		$array = self::get_not_created_codes_for_order_steps_to_include();
		if(is_array($array) && count($array)) {
			return true;
		}
		return false;
	}


	function onBeforeWrite() {
		parent::onBeforeWrite();
		$this->Code = strtoupper($this->Code);
	}

	function onAfterDelete() {
		parent::onAfterDelete();
		$this->requireDefaultRecords();
	}


	//USED TO BE: Unpaid,Query,Paid,Processing,Sent,Complete,AdminCancelled,MemberCancelled,Cart
	function requireDefaultRecords() {
		parent::requireDefaultRecords();
		$orderStepsToInclude = self::get_order_steps_to_include();
		$codesToInclude = self::get_codes_for_order_steps_to_include();
		if($orderStepsToInclude && count($orderStepsToInclude) && count($codesToInclude)) {
			foreach($codesToInclude as $className => $code) {
				if(!DataObject::get_one($className)) {
					if(!DataObject::get_one("OrderStep", "\"Code\" = '".strtoupper($code)."'")) {
						$obj = new $className();
						$obj->Code = strtoupper($obj->Code);
						$obj->write();
						DB::alteration_message("Created \"$code\" as $className.", "created");
					}
				}
			}
		}
	}
}

/**
 * This is the first Order Step.
 *
 *
 *
 *
 **/


class OrderStep_Created extends OrderStep {

	public static $defaults = array(
		"CustomerCanEdit" => 1,
		"CustomerCanPay" => 1,
		"CustomerCanCancel" => 1,
		"Name" => "Create",
		"Code" => "CREATED",
		"Sort" => 10,
		"ShowAsUncompletedOrder" => 1
	);

	/**
	 * Can always run step.
	 *@param DataObject - $order Order
	 *@return Boolean
	 **/
	public function initStep($order) {
		return true;
	}

	/**
	 * Add the member to the order, in case the member is not an admin.
	 *@param DataObject - $order Order
	 *@return Boolean
	 **/
	public function doStep($order) {
		if(!$order->MemberID) {
			$m = Member::currentUser();
			if($m) {
				if(!$m->IsShopAdmin()) {
					$order->MemberID = $m->ID();
					$order->write();
				}
			}
		}
		return true;
	}

	/**
	 * We can run the next step, once any items have been added.
	 *@param DataObject - $order Order
	 *@return DataObject | Null (nextStep DataObject)
	 **/
	public function nextStep($order) {
		if($order->TotalItems()) {
			return parent::nextStep($order);
		}
		return null;
	}

	/**
	 * Allows the opportunity for the Order Step to add any fields to Order::getCMSFields
	 *@param FieldSet $fields
	 *@param Order $order
	 *@return FieldSet
	 **/
	function addOrderStepFields(&$fields, $order) {
		if(!$order->IsSubmitted()) {
			//LINE BELOW IS NOT REQUIRED
			$header = _t("OrderStep.SUBMITORDER", "Submit Order");
			$label = _t("OrderStep.SUBMITNOW", "Submit Now");
			$msg = _t("OrderStep.MUSTDOSUBMITRECORD", "<p>Tick the box below to submit this order.</p>");
			$problems = array();
			if(!$order->Items()) {
				$problems[] = "There are no items associated with this order.";
			}
			if(!$order->MemberID) {
				$problems[] = "There is no customer associated with this order.";
			}
			if(!$order->BillingAddressID) {
				$problems[] = "There is no billing address associated with this order.";
			}
			elseif($billingAddress = $order->BillingAddress()) {
				if(!$billingAddress->FirstName) {
					$problems[] = "There is no -- first name -- recorded the billing address.";
				}
				if(!$billingAddress->Surname) {
					$problems[] = "There is no -- Surname -- recorded the billing address.";
				}
				if(!$billingAddress->Country) {
					$problems[] = "There is no -- Country -- recorded the billing address.";
				}
			}
			if(count($problems)) {
				$msg = "<p>You can not submit this order because:</p> <ul><li>".implode("</li><li>", $problems)."</li></ul>";
			}
			$fields->addFieldToTab("Root.Next", new HeaderField("CreateSubmitRecordHeader", $header, 3), "ActionNextStepManually");
			$fields->addFieldToTab("Root.Next", new LiteralField("CreateSubmitRecordMessage", $msg), "ActionNextStepManually");
			if(!$problems) {
				$fields->addFieldToTab("Root.Next", new CheckboxField("SubmitOrderViaCMS", $label), "ActionNextStepManually");
			}
		}
		return $fields;
	}



}

class OrderStep_Submitted extends OrderStep {

	static $db = array(
		"SaveOrderAsHTML" => "Boolean",
		"SaveOrderAsSerializedObject" => "Boolean",
		"SaveOrderAsJSON" => "Boolean"
	);

	static $defaults = array(
		"CustomerCanEdit" => 0,
		"CustomerCanPay" => 1,
		"CustomerCanCancel" => 0,
		"Name" => "Submit",
		"Code" => "SUBMITTED",
		"Sort" => 20,
		"ShowAsInProcessOrder" => 1,
		"SaveOrderAsHTML" => 1,
		"SaveOrderAsSerializedObject" => 0,
		"SaveOrderAsJSON" => 0
	);


	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->addFieldToTab("Root.Main", new HeaderField("HOWTOSAVESUBMITTEDORDER", _t("OrderStep.HOWTOSAVESUBMITTEDORDER", "How would you like to make a backup of your order at the moment it is submitted?"), 3), "SaveOrderAsHTML");
		return $fields;
	}

	/**
	 * Can run this step once any items have been submitted.
	 *@param DataObject - $order Order
	 *@return Boolean
	 **/
	public function initStep($order) {
		return (bool) $order->TotalItems();
	}

	/**
	 * Add a member to the order - in case he / she is not a shop admin.
	 *@param DataObject - $order Order
	 *@return Boolean
	 **/
	public function doStep($order) {
		if(!$order->IsSubmitted()) {
			$className = OrderStatusLog::get_order_status_log_class_used_for_submitting_order();
			if(class_exists($className)) {
				$obj = new $className();
				if($obj instanceOf OrderStatusLog) {
					$obj->OrderID = $order->ID;
					$obj->Title = $this->Name;
					$saved = false;
					if($this->SaveOrderAsJSON)												{$obj->OrderAsJSON = $order->ConvertToJSON(); $saved = true;}
					if($this->SaveOrderAsHTML)												{$obj->OrderAsHTML = $order->ConvertToHTML(); $saved = true;}
					if($this->SaveOrderAsSerializedObject || !$saved)	{$obj->OrderAsString = $order->ConvertToString();$saved = true; }
					$obj->write();
				}
				else {
					user_error('OrderStatusLog::$order_status_log_class_used_for_submitting_order refers to a class that is NOT an instance of OrderStatusLog');
				}

			}
			else {
				user_error('OrderStatusLog::$order_status_log_class_used_for_submitting_order refers to a non-existing class');
			}
		}
		return true;
	}

	/**
	 * go to next step if order has been submitted.
	 *@param DataObject - $order Order
	 *@return DataObject | Null	(next step OrderStep)
	 **/
	public function nextStep($order) {
		if($order->IsSubmitted()) {
			return parent::nextStep($order);
		}
		return null;
	}


	/**
	 * Allows the opportunity for the Order Step to add any fields to Order::getCMSFields
	 *@param FieldSet $fields
	 *@param Order $order
	 *@return FieldSet
	 **/
	function addOrderStepFields(&$fields, $order) {
		$msg = _t("OrderStep.CANADDGENERALLOG", " ... if you want to make some notes about this step then do this here...");
		$fields->addFieldToTab("Root.Next", $order->OrderStatusLogsTable("OrderStatusLog", $msg),"ActionNextStepManually");
		return $fields;
	}

}



class OrderStep_SentInvoice extends OrderStep {

	static $db = array(
		"SendInvoiceToCustomer" => "Boolean"
	);

	public static $defaults = array(
		"CustomerCanEdit" => 0,
		"CustomerCanCancel" => 0,
		"CustomerCanPay" => 1,
		"Name" => "Send invoice",
		"Code" => "INVOICED",
		"Sort" => 25,
		"ShowAsInProcessOrder" => 1,
		"SendInvoiceToCustomer" => 1
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->addFieldToTab("Root.Main", new HeaderField("ACTUALLYSENDINVOICE", _t("OrderStep.ACTUALLYSENDINVOICE", "Actually send the invoice? "), 3), "SendInvoiceToCustomer");
		return $fields;
	}

	/**
	 * can run step once order has been submitted.
	 *@param DataObject $order Order
	 *@return Boolean
	 **/
	public function initStep($order) {
		return $order->IsSubmitted();
	}

	/**
	 * send invoice to customer
	 * or in case this is not selected, it will send a message to the shop admin only
	 * The latter is useful in case the payment does not go through (and no receipt is received).
	 * @param DataObject $order Order
	 * @return Boolean
	 **/
	public function doStep($order) {
		if($this->SendInvoiceToCustomer){
			if(!$this->hasBeenSent($order)) {
				return $order->sendInvoice($this->CustomerMessage);
			}
		}
		else {
			if(!$this->hasBeenSent($order)) {
				return $order->sendError($subject =  _t("OrderStep.NEWORDERHASBEENPLACED", "New order has been placed"), $message = _t("OrderStep.THISMESSAGENOTSENTTOCUSTOMER", "This message was not sent to the customer"), $resend = false);
			}
		}
		return true;
	}

	/**
	 * can do next step once the invoice has been sent or in case the invoice does not need to be sent.
	 * @param DataObject $order Order
	 * @return DataObject | Null	(next step OrderStep object)
	 **/
	public function nextStep($order) {
		if(!$this->SendInvoiceToCustomer || $this->hasBeenSent($order)) {
			return parent::nextStep($order);
		}
		return null;
	}

	/**
	 * Allows the opportunity for the Order Step to add any fields to Order::getCMSFields
	 *@param FieldSet $fields
	 *@param Order $order
	 *@return FieldSet
	 **/
	function addOrderStepFields(&$fields, $order) {
		$msg = _t("OrderStep.CANADDGENERALLOG", " ... if you want to make some notes about this step then do this here...");
		$fields->addFieldToTab("Root.Next", $order->OrderStatusLogsTable("OrderStatusLog", $msg),"ActionNextStepManually");
		return $fields;
	}


}

class OrderStep_Paid extends OrderStep {

	public static $defaults = array(
		"CustomerCanEdit" => 0,
		"CustomerCanCancel" => 0,
		//the one below may seem a bit paradoxical, but the thing is that the customer can pay up to and inclusive of this step
		//that ist he code PAID means that the Order has been paid ONCE this step is completed
		"CustomerCanPay" => 1,
		"Name" => "Pay",
		"Code" => "PAID",
		"Sort" => 30,
		"ShowAsInProcessOrder" => 1
	);

	public function initStep($order) {
		return true;
	}

	public function doStep($order) {
		return true;
	}

	/**
	 * can go to next step if order has been paid
	 *@param DataObject $order Order
	 *@return DataObject | Null	(next step OrderStep object)
	 **/
	public function nextStep($order) {
		if($order->IsPaid()) {
			return parent::nextStep($order);
		}
		return null;
	}

	/**
	 * Allows the opportunity for the Order Step to add any fields to Order::getCMSFields
	 *@param FieldSet $fields
	 *@param Order $order
	 *@return FieldSet
	 **/
	function addOrderStepFields(&$fields, $order) {
		if(!$order->IsPaid()) {
			$header = _t("OrderStep.SUBMITORDER", "Order NOT Paid");
			$msg = _t("OrderStep.ORDERNOTPAID", "This order can not be completed, because it has not been paid. You can either create a payment or change the status of any existing payment to <i>success</i>.");
			$fields->addFieldToTab("Root.Next", new HeaderField("NotPaidHeader", $header, 3), "ActionNextStepManually");
			$fields->addFieldToTab("Root.Next", new LiteralField("NotPaidMessage", '<p>'.$msg.'</p>'), "ActionNextStepManually");
		}
		return $fields;
	}



}


class OrderStep_Confirmed extends OrderStep {

	public static $defaults = array(
		"CustomerCanEdit" => 0,
		"CustomerCanCancel" => 0,
		"CustomerCanPay" => 0,
		"Name" => "Confirm",
		"Code" => "CONFIRMED",
		"Sort" => 35,
		"ShowAsInProcessOrder" => 1
	);

	public function initStep($order) {
		return true;
	}

	public function doStep($order) {
		return true;
	}

	/**
	 * can go to next step if order payment has been confirmed...
	 *@param DataObject $order Order
	 *@return DataObject | Null - DataObject = OrderStep
	 **/
	public function nextStep($order) {
		if(DataObject::get_one("OrderStatusLog_PaymentCheck", "\"OrderID\" = ".$order->ID." AND \"PaymentConfirmed\" = 1")) {
			return parent::nextStep($order);
		}
		return null;
	}


	/**
	 * Allows the opportunity for the Order Step to add any fields to Order::getCMSFields
	 *@param FieldSet $fields
	 *@param Order $order
	 *@return FieldSet
	 **/
	function addOrderStepFields(&$fields, $order) {
		$msg = _t("OrderStep.MUSTDOPAYMENTCHECK", " ... To move this order to the next step you must carry out a payment check (is the money in the bank?) by creating a record here (click me)");
		$fields->addFieldToTab("Root.Next", $order->OrderStatusLogsTable("OrderStatusLog_PaymentCheck", $msg),"ActionNextStepManually");
		return $fields;
	}


}



class OrderStep_SentReceipt extends OrderStep {

	static $db = array(
		"SendReceiptToCustomer" => "Boolean"
	);

	public static $defaults = array(
		"CustomerCanEdit" => 0,
		"CustomerCanCancel" => 0,
		"CustomerCanPay" => 0,
		"Name" => "Send receipt",
		"Code" => "RECEIPTED",
		"Sort" => 40,
		"ShowAsInProcessOrder" => 1,
		"SendReceiptToCustomer" => 1
	);


	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->addFieldToTab("Root.Main", new HeaderField("ACTUALLYSENDRECEIPT", _t("OrderStep.ACTUALLYSENDRECEIPT", "Actually send the receipt?"), 3), "SendReceiptToCustomer");
		return $fields;
	}

	public function initStep($order) {
		return $order->IsPaid();
	}

	public function doStep($order) {
		if($this->SendReceiptToCustomer){
			if(!$this->hasBeenSent($order)) {
				return $order->sendReceipt($this->CustomerMessage);
			}
		}
		else {
			if(!$this->hasBeenSent($order)) {
				return $order->sendError($subject =  _t("OrderStep.NEWORDERHASBEENPLACED", "Order has been placed"), $message = _t("OrderStep.THISMESSAGENOTSENTTOCUSTOMER", "This message was not sent to the customer"), $resend = false);
			}
		}
		return true;
	}

	/**
	 * can continue if receipt has been sent or if there is no need to send a receipt.
	 *@param DataObject $order Order
	 *@return DataObject | Null - DataObject = next OrderStep
	 **/
	public function nextStep($order) {
		if(!$this->SendReceiptToCustomer || $this->hasBeenSent($order)) {
			return parent::nextStep($order);
		}
		return null;
	}


	/**
	 * Allows the opportunity for the Order Step to add any fields to Order::getCMSFields
	 *@param FieldSet $fields
	 *@param Order $order
	 *@return FieldSet
	 **/
	function addOrderStepFields(&$fields, $order) {
		$msg = _t("OrderStep.CANADDGENERALLOG", " ... if you want to make some notes about this step then do this here...)");
		$fields->addFieldToTab("Root.Next", $order->OrderStatusLogsTable("OrderStatusLog", $msg),"ActionNextStepManually");
		return $fields;
	}



}


class OrderStep_Sent extends OrderStep {

	static $db = array(
		"SendDetailsToCustomer" => "Boolean",
		"EmailSubject" => "Varchar(255)"
	);

	public static $defaults = array(
		"CustomerCanEdit" => 0,
		"CustomerCanCancel" => 0,
		"CustomerCanPay" => 0,
		"Name" => "Send order",
		"Code" => "SENT",
		"Sort" => 50,
		"ShowAsCompletedOrder" => 1
	);


	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->addFieldToTab("Root.Main", new HeaderField("ACTUALLYSENDDETAILS", _t("OrderStep.ACTUALLYSENDDETAILS", "Send details to the customer?"), 3), "SendDetailsToCustomer");
		return $fields;
	}

	/**
	 * Explains the current order step.
	 * @return String
	 */
	function description(){
		_t("OrderStep.SENTDESCRIPTON", "During this step we record the delivery details for the order such as the courrier ticket number and whatever else is relevant.")
	}

	public function initStep($order) {
		return true;
	}


	public function doStep($order) {
		if($this->SendDetailsToCustomer){
			if(!$this->hasBeenSent($order)) {
				$subject = $this->EmailSubject ? $this->EmailSubject : _t("OrderStep.OR", "Your order has been dispatched");
				return $order->sendStatusChange($subject, $this->CustomerMessage);
			}
		}
		return true;
	}

	/**
	 *
	 *@param DataObject $order Order
	 *@return DataObject | Null - DataObject = OrderStep
	 **/
	public function nextStep($order) {
		if(DataObject::get_one("OrderStatusLog_DispatchPhysicalOrder", "\"OrderID\" = ".$order->ID)) {
			return parent::nextStep($order);
		}
		return null;
	}

	/**
	 * Allows the opportunity for the Order Step to add any fields to Order::getCMSFields
	 *@param FieldSet $fields
	 *@param Order $order
	 *@return FieldSet
	 **/
	function addOrderStepFields(&$fields, $order) {
		$msg = _t("OrderStep.MUSTENTERDISPATCHRECORD", " ... To move this order to the next step you enter the dispatch details in the logs.");
		$fields->addFieldToTab("Root.Next", $order->OrderStatusLogsTable("OrderStatusLog_DispatchPhysicalOrder", $msg),"ActionNextStepManually");
		return $fields;
	}


}


class OrderStep_Archived extends OrderStep {

	public static $defaults = array(
		"CustomerCanEdit" => 0,
		"CustomerCanCancel" => 0,
		"CustomerCanPay" => 0,
		"Name" => "Archived order",
		"Code" => "ARCHIVED",
		"Sort" => 55,
		"ShowAsCompletedOrder" => 1
	);

	/**
	 * Explains the current order step.
	 * @return String
	 */
	function description(){
		_t("OrderStep.ARCHIVEDDESCRIPTON", "This is typically the last step in the order process. Nothing needs to be done to the order anymore.  We keep the order in the system for record-keeping and statistical purposes.")
	}

	public function initStep($order) {
		return true;
	}

	public function doStep($order) {
		return true;
	}

	/**
	 *
	 *@param DataObject $order Order
	 *@return DataObject | Null - DataObject = OrderStep
	 **/
	public function nextStep($order) {
		//IMPORTANT
		return null;
	}

	/**
	 * Allows the opportunity for the Order Step to add any fields to Order::getCMSFields
	 *@param FieldSet $fields
	 *@param Order $order
	 *@return FieldSet
	 **/
	function addOrderStepFields(&$fields, $order) {
		$msg = _t("OrderStep.CANADDGENERALLOG", " ... if you want to make some notes about this order then do this here ...");
		$fields->addFieldToTab("Root.Next", $order->OrderStatusLogsTable("OrderStatusLog_Archived", $msg),"ActionNextStepManually");
		return $fields;
	}


}


