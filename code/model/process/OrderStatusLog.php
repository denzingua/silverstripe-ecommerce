<?php
/**
 * @description: see OrderStep.md
 *
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 * @package: ecommerce
 * @sub-package: cms
 *
 **/


class OrderStatusLog extends DataObject {

	public static $db = array(
		'Title' => 'Varchar(100)',
		'Note' => 'HTMLText',
		'EmailCustomer' => 'Boolean',
		'EmailSent' => 'Boolean',
		'InternalUseOnly' => 'Boolean'
	);

	public static $has_one = array(
		"Author" => "Member",
		"Order" => "Order"
	);

	public static $casting = array(
		"CustomerNote" => "HTMLText",
		"Type" => "Varchar",
		"EmailCustomerNice" => "Varchar",
		"EmailSentNice" => "Varchar",
		"InternalUseOnlyNice" => "Varchar"
	);

	public static $summary_fields = array(
		"Created" => "Date",
		"Type" => "Type",
		"Title" => "Title",
		"EmailSentNice" => "Email sent to customer",
		"InternalUseOnlyNice" => "Internal use only"
	);

	public static $defaults = array(
		"InternalUseOnly" => true
	);




	function EmailCustomerNice() {return $this->getEmailCustomerNice();}
	function getEmailCustomerNice() {if($this->EmailCustomer) { return _t("OrderStatusLog.YES", "Yes");} return _t("OrderStatusLog.No", "No");}

	function EmailSentNice() {return $this->getEmailSentNice();}
	function getEmailSentNice() {if($this->EmailSent) { return _t("OrderStatusLog.YES", "Yes");} return _t("OrderStatusLog.No", "No");}

	function InternalUseOnlyNice() {return $this->getInternalUseOnlyNice();}
	function getInternalUseOnlyNice() {if($this->InternalUseOnly) { return _t("OrderStatusLog.YES", "Yes");} return _t("OrderStatusLog.No", "No");}

	/**
	 * $available_log_classes_array tells us what order log classes are to be used.
	 * OrderStatusLog_Submitted should always be used and does not need to be listed here.
	 *@var Array - $available_log_classes_array
	 **/
	protected static $available_log_classes_array = array("OrderStatusLog_PaymentCheck");
		static function get_available_log_classes_array() {return self::$available_log_classes_array;}
		static function set_available_log_classes_array(array $a) {self::$available_log_classes_array = $a;}
		static function add_available_log_classes_array($s) {
			if(!in_array($s, self::$available_log_classes_array)) {
				self::$available_log_classes_array[] = $s;
			}
		}

	/**
	 * the order status log class used to record that the order has been submitted.
	 *@var String - $order_status_log_class_used_for_submitting_order
	 **/
	protected static $order_status_log_class_used_for_submitting_order = "OrderStatusLog_Submitted";
		static function set_order_status_log_class_used_for_submitting_order($s) {self::$order_status_log_class_used_for_submitting_order = $s;}
		static function get_order_status_log_class_used_for_submitting_order() {return self::$order_status_log_class_used_for_submitting_order;}

	/**
	*
	*@return Boolean
	**/
	public function canView($member = null) {
		if(!$member) {
			$member = Member::currentUser();
		}
		if(EcommerceRole::current_member_is_shop_admin($member)) {
			return true;
		}
		if(!$this->InternalUseOnly) {
			if($this->Order()) {
				if($this->Order()->MemberID == $member->ID) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	*
	*@return Boolean
	**/
	public function canDelete($member = null) {
		return false;
	}

	/**
	*
	*@return Boolean
	**/
	public function canCreate($member = null) {
		return true;
	}

	/**
	*
	*@return Boolean
	**/
	public function canEdit($member = null) {
		if($o = $this->Order()) {
			return $o->canEdit($member);
		}
		return false;
	}

	public static $searchable_fields = array(
		'OrderID' => array(
			'field' => 'NumericField',
			'title' => 'Order Number'
		),
		"Title" => "PartialMatchFilter",
		"Note" => "PartialMatchFilter"
	);


	public static $singular_name = "Order Log Entry";
		function i18n_singular_name() { return _t("OrderStatusLog.ORDERLOGENTRY", "Order Log Entry");}

	public static $plural_name = "Order Log Entries";
		function i18n_plural_name() { return _t("OrderStatusLog.ORDERLOGENTRIES", "Order Log Entries");}

	public static $default_sort = "\"Created\" DESC";

	function populateDefaults() {
		parent::populateDefaults();
		$this->AuthorID = Member::currentUserID();
	}

	/**
	*
	*@return FieldSet
	**/
	function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->dataFieldByName("Note")->setRows(3);
		$fields->replaceField("EmailSent", $fields->dataFieldByName("EmailSent")->performReadonlyTransformation());
		$fields->replaceField("AuthorID", $fields->dataFieldByName("AuthorID")->performReadonlyTransformation());
		if($this->OrderID) {
			$fields->replaceField("OrderID", $fields->dataFieldByName("OrderID")->performReadonlyTransformation());
		}
		if($this->InternalUseOnly) {
			$fields->removeByName("EmailCustomer");
		}
		$classes = ClassInfo::subclassesFor("OrderStatusLog");
		$dropdownArray = array();
		$availableLogs = self::get_available_log_classes_array();
		if(!is_array($availableLogs)) {
			$availableLogs = array();
		}
		$availableLogs = array_merge($availableLogs, array(OrderStatusLog::get_order_status_log_class_used_for_submitting_order()));
		if($classes) {
			foreach($classes as $className) {
				$obj = singleton($className);
				if($obj) {
					if(in_array($className, $availableLogs )) {
						$dropdownArray[$className] = $obj->i18n_singular_name();
					}
				}
			}
		}
		if(count($dropdownArray)) {
			$fields->addFieldToTab("Root.Main", new DropdownField("ClassName", "Type", $dropdownArray), "Title");
		}
		return $fields;
	}

	/**
	*
	*@return String
	**/
	function Type() {return $this->getType();}
	function getType() {
		return $this->i18n_singular_name();
	}

	/**
	*
	*@return Fieldset
	**/
	function scaffoldSearchFields(){
		$fields = parent::scaffoldSearchFields();
		$fields->replaceField("OrderID", new NumericField("OrderID", "Order Number"));
		return $fields;
	}

	function onBeforeWrite() {
		parent::onBeforeWrite();
		if(!$this->AuthorID && $m = Member::currentUser()) {
			$this->AuthorID = $m->ID;
		}
		if(!$this->Title) {
			$this->Title = "Order Update";
		}
		if($this->InternalUseOnly) {
			$this->EmailCustomer = 0;
		}
	}

	function onAfterWrite(){
		parent::onAfterWrite();
		if($this->EmailCustomer && !$this->EmailSent && !$this->InternalUseOnly) {
			$outcome = $this->order()->sendStatusChange($this->Title, $this->CustomerNote());
			if($outcome) {
				//can not do a proper write here for risk of ending up in a loop
				DB::query("UPDATE \"OrderStatusLog\" SET \"EmailSent\" = 1 WHERE  \"OrderStatusLog\".\"ID\" = ".$this->ID.";");
			}
			else {
				DB::query("UPDATE \"OrderStatusLog\" SET \"EmailCustomer\" = 0 WHERE  \"OrderStatusLog\".\"ID\" = ".$this->ID.";");
			}
		}
	}

	/**
	*
	*@return String
	**/
	function CustomerNote() {return $this->getCustomerNote();}
	function getCustomerNote() {
		return $this->Note;
	}

}

/**
 * OrderStatusLog_Submitted is an important class that is created when an order is submitted.
 * It is created by the order and it signifies to the OrderStep to continue to the next step.
 **/

class OrderStatusLog_Submitted extends OrderStatusLog {

	public static $db = array(
		"OrderAsHTML" => "HTMLText",
		"OrderAsString" => "Text",
		"OrderAsJSON" => "Text"
	);

	public static $defaults = array(
		"InternalUseOnly" => true
	);

	public static $casting = array(
		"HTMLRepresentation" => "HTMLText"
	);

	public static $singular_name = "Submitted Order";
		function i18n_singular_name() { return _t("OrderStatusLog.SUBMITTEDORDER", "Submitted Order - Fulltext Backup");}

	public static $plural_name = "Submitted Orders";
		function i18n_plural_name() { return _t("OrderStatusLog.SUBMITTEDORDERS", "Submitted Orders - Fulltext Backup");}

	/**
	 * This record is not editable
	 *@return Boolean
	 **/
	public function canDelete($member = null) {
		return false;
	}

	/**
	 * This record is not editable
	 *@return Boolean
	 **/
	public function canEdit($member = null) {
		return false;
	}


	/**
	* can only be created when the order is submitted
	*@return Boolean
	**/
	public function canCreate($member = null) {
		return true;
	}

	function HTMLRepresentation(){return $this->getHTMLRepresentation();}
	function getHTMLRepresentation(){
		if($this->OrderAsHTML) {
			return $this->OrderAsHTML;
		}
		elseif($this->OrderAsString) {
			return unserialize($this->OrderAsString);
		}
		else {
			return $this->OrderAsJSON;
		}
	}
}

class OrderStatusLog_Cancel extends OrderStatusLog {

	public static $defaults = array(
		"Title" => "Order Cancelled",
		"EmailCustomer" => false,
		"EmailSent" => false,
		"InternalUseOnly" => false
	);

	public static $singular_name = "Cancelled Order";
		function i18n_singular_name() { return _t("OrderStatusLog.SUBMITTEDORDER", "Cancelled Order");}

	public static $plural_name = "Cancelled Orders";
		function i18n_plural_name() { return _t("OrderStatusLog.SUBMITTEDORDERS", "Cancelled Orders");}

	/**
	 * This record is not editable
	 *@return Boolean
	 **/
	public function canDelete($member = null) {
		return false;
	}

	/**
	 * This record is not editable
	 *@return Boolean
	 **/
	public function canEdit($member = null) {
		return false;
	}


	/**
	* can only be created when the order is submitted
	*@return Boolean
	**/
	public function canCreate($member = null) {
		return false;
	}


}
class OrderStatusLog_Dispatch extends OrderStatusLog {

	public static $defaults = array(
		"InternalUseOnly" => true
	);

	public static $singular_name = "Order Log Dispatch Entry";
		function i18n_singular_name() { return _t("OrderStatusLog.ORDERLOGDISPATCHENTRY", "Order Log Dispatch Entry");}

	public static $plural_name = "Order Log Dispatch Entries";
		function i18n_plural_name() { return _t("OrderStatusLog.ORDERLOGDISPATCHENTRIES", "Order Log Dispatch Entries");}

	/**
	 * Only shop admin can delete this
	 *@return Boolean
	 **/
	public function canDelete($member = null) {
		return EcommerceRole::current_member_is_shop_admin($member);
	}


}
class OrderStatusLog_DispatchElectronicOrder extends OrderStatusLog_Dispatch {

	public static $db = array(
		'Link' => 'Text',
	);

	public static $singular_name = "Order Log Electronic Dispatch Entry";
		function i18n_singular_name() { return _t("OrderStatusLog.ORDERLOGELECTRONICDISPATCHENTRY", "Order Log Electronic Dispatch Entry");}

	public static $plural_name = "Order Log Electronic Dispatch Entries";
		function i18n_plural_name() { return _t("OrderStatusLog.ORDERLOGELECTRONICDISPATCHENTRIES", "Order Log Electronic Dispatch Entries");}

}

class OrderStatusLog_DispatchPhysicalOrder extends OrderStatusLog_Dispatch {

	public static $db = array(
		'DispatchedBy' => 'Varchar(100)',
		'DispatchedOn' => 'Date',
		'DispatchTicket' => 'Varchar(100)',
	);

	public static $indexes = array(
		"DispatchedOn" => true
	);

	public static $searchable_fields = array(
		'OrderID' => array(
			'field' => 'NumericField',
			'title' => 'Order Number'
		),
		"Title" => "PartialMatchFilter",
		"Note" => "PartialMatchFilter",
		"DispatchedBy" => "PartialMatchFilter",
		'DispatchTicket' => 'PartialMatchFilter'
	);

	public static $summary_fields = array(
		"DispatchedOn" => "Date",
		"DispatchedBy" => "Dispatched By",
		"OrderID" => "Order ID",
		"EmailCustomerNice" => "Customer Emailed",
		"EmailSentNice" => "Email Sent"
	);


	public static $defaults = array(
		"InternalUseOnly" => false,
		"EmailCustomer" => true
	);

	public static $singular_name = "Order Log Physical Dispatch Entry";
		function i18n_singular_name() { return _t("OrderStatusLog.ORDERLOGPHYSICALDISPATCHENTRY", "Order Log Physical Dispatch Entry");}

	public static $plural_name = "Order Log Physical Dispatch Entries";
		function i18n_plural_name() { return _t("OrderStatusLog.ORDERLOGPHYSICALDISPATCHENTRIES", "Order Log Physical Dispatch Entries");}


	public static $default_sort = "\"DispatchedOn\" DESC, \"Created\" DESC";

	function populateDefaults() {
		parent::populateDefaults();
		$sc = DataObject::get_one("SiteConfig");
		if($sc) {
			$this->Title = $sc->DispatchEmailSubject;
		}
		$this->DispatchedOn =  date('Y-m-d');
		$this->DispatchedBy =  Member::currentUser()->getTitle();
	}

	/**
	*
	*@return FieldSet
	**/
	function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->replaceField("EmailSent", $fields->dataFieldByName("EmailSent")->performReadonlyTransformation());
		$fields->replaceField("DispatchedOn", new TextField("DispatchedOn", "Dispatched on (Year - month - date): "));
		return $fields;
	}

	function onBeforeWrite() {
		parent::onBeforeWrite();
		if(!$this->Title) {
			$sc = DataObject::get_one("SiteConfig");
			if($sc) {
				$this->Title = $sc->DispatchEmailSubject;
			}
		}
		if(!$this->DispatchedOn) {
			$this->DispatchedOn = DBField::create('Date', date('Y-m-d'));
		}
	}

	/**
	*
	*@return String
	*@To do: move formatting to template
	**/
	function getCustomerNote() {
		return $this->renderWith("LogDispatchPhysicalOrderCustomerNote");
	}

	function CustomerNote() {
		return $this->getCustomerNote();
	}

}

/**
 *@Description: We use this payment check class to double check that payment has arrived against
 * the order placed.  We do this independently of Order as a double-check.  It is important
 * that we do this because the main risk in an e-commerce operation is a fake payment.
 * Any e-commerce operator may set up their own policies on what a payment check
 * entails exactly.  It could include a bank reconciliation or even a phone call to the customer.
 * it is important here that we do not add any payment details. Rather, all we have is a tickbox
 * to state that the checks have been run.

 **/
class OrderStatusLog_PaymentCheck extends OrderStatusLog {

	public static $defaults = array(
		"InternalUseOnly" => true
	);

	public static $db = array(
		'PaymentConfirmed' => "Boolean",
	);

	/**
	*
	*@return Boolean
	**/
	public function canDelete($member = null) {
		return false;
	}

	protected static $true_and_false_definitions = array(
		"yes" => 1,
		"no" => 0
	);
		static function set_true_and_false_definitions(array $a) {self::$true_and_false_definitions = $a;}
		static function get_true_and_false_definitions() {return self::$true_and_false_definitions;}

	public static $searchable_fields = array(
		'OrderID' => array(
			'field' => 'NumericField',
			'title' => 'Order Number'
		),
		"PaymentConfirmed" => true
	);

	public static $summary_fields = array(
		"Created" => "Date",
		"Author.Title" => "Checked by",
		"PaymentConfirmedNice" => "Payment Confirmed"
	);

	public static $casting = array(
		"PaymentConfirmedNice" => "Varchar"
	);

	function PaymentConfirmedNice() {return $this->getPaymentConfirmedNice();}
	function getPaymentConfirmedNice() {if($this->PaymentConfirmed) {return _t("OrderStatusLog.YES", "yes");}return _t("OrderStatusLog.No", "no");}

	public static $singular_name = "Payment Confirmation";
		function i18n_singular_name() { return _t("OrderStatusLog.PAYMENTCONFIRMATION", "Payment Confirmation");}

	public static $plural_name = "Payment Confirmations";
		function i18n_plural_name() { return _t("OrderStatusLog.PAYMENTCONFIRMATIONS", "Payment Confirmations");}

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName("Title");
		$fields->removeByName("Note");
		$fields->addFieldsToTab('Root.Main', new CheckboxField("PaymentConfirmed", _t("OrderStatusLog.CONFIRMED", "Payment is confirmed")));
		return $fields;
	}


	/**
	*
	*@return String
	**/
	function getCustomerNote() {
		if($this->Author()) {
			if($this->PaymentConfirmed) {
				return _t("OrderStatus.PAYMENTCONFIRMEDBY", "Payment Confirmed by: ").$this->Author()->getTitle()." | ".$this->Created;
			}
			else {
				return _t("OrderStatus.PAYMENTDECLINEDBY", "Payment DECLINED by: ").$this->Author()->getTitle()." | ".$this->Created;
			}
		}
	}

	function CustomerNote(){
		return $this->getCustomerNote();
	}

}



/**
 * OrderStatusLog_Submitted is an important class that is created when an order is submitted.
 * It is created by the order and it signifies to the OrderStep to continue to the next step.
 **/

class OrderStatusLog_Archived extends OrderStatusLog {


	public static $defaults = array(
		"InternalUseOnly" => false
	);


	public static $singular_name = "Archived Order - Additional Note";
		function i18n_singular_name() { return _t("OrderStatusLog.ARCHIVEDORDERS", "Archived Order - Additional Note");}

	public static $plural_name = "Archived Order - Additional Notes";
		function i18n_plural_name() { return _t("OrderStatusLog.ARCHIVEDORDERS", "Archived Order - Additional Notes");}

	/**
	 * This record is not editable
	 *@return Boolean
	 **/
	public function canDelete($member = null) {
		return false;
	}

	/**
	 * This record is not editable
	 *@return Boolean
	 **/
	public function canEdit($member = null) {
		return true;
	}


	/**
	* can only be created when the order is submitted
	*@return Boolean
	**/
	public function canCreate($member = null) {
		return true;
	}

}

