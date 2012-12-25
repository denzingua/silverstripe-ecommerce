<?php

/**
 * @description:
 * The Order Confirmation page shows order history.
 * It also serves as the end point for the current order...
 * once submitted, the Order Confirmation page shows the
 * finalised detail of the order.
 *
 *
 * @authors: Nicolaas [at] Sunny Side Up .co.nz
 * @package: ecommerce
 * @sub-package: Pages
 * @inspiration: Silverstripe Ltd, Jeremy
 **/


class OrderConfirmationPage extends CartPage{

	/**
	 * Standard SS variable
	 * @var String
	 */
	public static $icon = 'ecommerce/images/icons/OrderConfirmationPage';

	/**
	 * Standard SS variable
	 * @var Array
	 */
	public static $db = array(
		'StartNewOrderLinkLabel' => 'Varchar(100)',
		'CopyOrderLinkLabel' => 'Varchar(100)',
		'PaymentSuccessfulHeader' => 'Varchar(255)',
		'PaymentNotSuccessfulHeader' => 'Varchar(255)',
		'PaymentSuccessfulMessage' => 'HTMLText',
		'PaymentNotSuccessfulMessage' => 'HTMLText'
	);

	/**
	 * Standard SS variable
	 * @var Array
	 */
	public static $defaults = array(
		"ShowInMenus" => false,
		"ShowInSearch" => false,
		"StartNewOrderLinkLabel" => "start new order",
		"CopyOrderLinkLabel" => "copy order items into a new order"
	);

	/**
	 * Standard SS method
	 * @var Boolean
	 */
	function canCreate($member = null) {
		return !DataObject::get_one("OrderConfirmationPage", "\"ClassName\" = 'OrderConfirmationPage'");
	}

	function customFieldLabels(){
		$newLabels = array(
			"StartNewOrderLinkLabel" => _t("EcommerceDBConfig.STARTNEWORDERLINKLABEL", 'Label for starting new order - e.g. click here to start new order'),
			"CopyOrderLinkLabel" => _t("EcommerceDBConfig.COPYORDERLINKLABEL", 'Label for copying order items into a new one  - e.g. click here start a new order with the current order items'),
			"PaymentSuccessfulHeader" => _t("EcommerceDBConfig.PAYMENTSUCCESSFULHEADER", "Message showing when order has been paid in full (usually at the top of the page)"),
			"PaymentNotSuccessfulHeader" => _t("EcommerceDBConfig.PAYMENTNOTSUCCESSFULHEADER", "Message showing when the order has not been paid in full (usually at the top of the page)"),
			"PaymentSuccessfulMessage" => _t("EcommerceDBConfig.PAYMENTSUCCESSFULMESSAGE", "Message showing when order has been paid in full"),
			"PaymentNotSuccessfulMessage" => _t("EcommerceDBConfig.PAYMENTNOTSUCCESSFULMESSAGE", "Message showing when the order has not been paid in full")
		);
		return $newLabels;
	}

	/**
	 * standard SS method for decorators.
	 * @param Array - $fields: array of fields to start with
	 * @return null ($fields variable is automatically updated)
	 */
	function fieldLabels($includerelations = true) {
		$defaultLabels = parent::fieldLabels();
		$newLabels = $this->customFieldLabels();
		$labels = array_merge($defaultLabels, $newLabels);
		$this->extend('updateFieldLabels', $labels);
		return $labels;
	}

	/**
	 *@return Fieldset
	 **/
	function getCMSFields(){
		$fields = parent::getCMSFields();
		$fields->removeFieldFromTab('Root.Messages.Messages.Actions',"ProceedToCheckoutLabel");
		$fields->removeFieldFromTab('Root.Messages.Messages.Actions',"ContinueShoppingLabel");
		$fields->removeFieldFromTab('Root.Messages.Messages.Actions',"ContinuePageID");
		$fields->removeFieldFromTab('Root.Messages.Messages.Actions',"SaveOrderLinkLabel");
		$fields->removeFieldFromTab('Root.Messages.Messages.Errors',"NoItemsInOrderMessage");
		$fieldLabels = $this->fieldLabels();
		$fields->addFieldToTab('Root.Messages.Messages.Actions', new TextField('StartNewOrderLinkLabel', $fieldLabels["StartNewOrderLinkLabel"]));
		$fields->addFieldToTab('Root.Messages.Messages.Actions', new TextField('CopyOrderLinkLabel', $fieldLabels["CopyOrderLinkLabel"]));
		$fields->addFieldToTab('Root.Messages.Messages.Payment', new HTMLEditorField('PaymentSuccessfulMessage', $fieldLabels["PaymentSuccessfulMessage"], 5));
		$fields->addFieldToTab('Root.Messages.Messages.Payment', new HTMLEditorField('PaymentNotSuccessfulMessage', $fieldLabels["PaymentNotSuccessfulMessage"], 5));
		$fields->addFieldsToTab('Root.Content.Messages.Payment', array(
			new HeaderField('Successful'),
			new TextField('PaymentSuccessfulHeader', $fieldLabels['PaymentSuccessfulHeader']),
			new HTMLEditorField('PaymentSuccessfulMessage', $fieldLabels['PaymentSuccessfulMessage'], 5),
			new HeaderField('Unsuccessful'),
			new TextField('PaymentNotSuccessfulHeader', $fieldLabels['PaymentNotSuccessfulHeader']),
			new HTMLEditorField('PaymentNotSuccessfulMessage', $fieldLabels["PaymentNotSuccessfulMessage"], 5)
		));
		return $fields;
	}


	/**
	 * Returns the link or the Link to the OrderConfirmationPage page on this site
	 * @return String (URLSegment)
	 */
	public static function find_link() {
		if($page = DataObject::get_one('OrderConfirmationPage', "\"ClassName\" = 'OrderConfirmationPage'")) {
			return $page->Link();
		}
		elseif($page = DataObject::get_one('OrderConfirmationPage')) {
			return $page->Link();
		}
		return CartPage::find_link();
	}

	/**
	 * Return a link to view the order on this page.
	 * @return String (URLSegment)
	 * @param int|string $orderID ID of the order
	 */
	public static function get_order_link($orderID) {
		return self::find_link(). 'showorder/' . $orderID . '/';
	}

	/**
	 * Return a link to copy the order to cart
	 * @return String (URLSegment)
	 * @param int $orderID ID of the order
	 */
	public static function new_order_link($orderID) {
		return self::find_link(). 'copyorder/' . $orderID . '/';
	}

	/**
	 * Return a link to view the order on this page.
	 * @return String (URLSegment)
	 * @param int|string $orderID ID of the order
	 */
	public static function get_email_link($orderID) {
		return self::find_link(). 'sendreceipt/' . $orderID . '/';
	}

	/**
	 * Return a link to view the order on this page.
	 * @return String (URLSegment)
	 * @param int|string $orderID ID of the order
	 */
	public function getOrderLink($orderID) {
		return self::get_order_link($orderID);
	}

	/**
	 * returns the Checkout_StepDescription assocatiated with the final step: the order confirmation.
	 * @return Checkout_StepDescription
	 */
	public function CurrentCheckoutStep($current = false) {
		$do = new CheckoutPage_StepDescription();
		$do->Link = $this->Link;
		$do->Heading = $this->MenuTitle;
		$do->Code = $this->URLSegment;
		$do->LinkingMode = "notCompleted";
		if($current) {
			$do->LinkingMode .= " current";
		}
		$do->Completed = 0;
		$do->ID = 99;
		return $do;
	}

}

class OrderConfirmationPage_Controller extends CartPage_Controller{

	/**
	 * standard controller function
	 **/
	function init() {
		//we retrieve the order in the parent page
		//the parent page also takes care of the security
		parent::init();
		Requirements::themedCSS('Order');
		Requirements::themedCSS('Order_Print', "print");
		Requirements::javascript('ecommerce/javascript/EcomPayment.js');
		Requirements::javascript('ecommerce/javascript/EcomPrintAndMail.js');
		//clear steps from checkout page otherwise in the next order
		//you go straight to the last step.
		Session::clear("CheckoutPageCurrentOrderID");
	}

	/**
	 * This method exists just so that template
	 * sets CurrentOrder variable
	 *
	 *@return array
	 **/
	function showorder($request) {
		if(isset($_REQUEST["print"])) {
			Requirements::clear();
			Requirements::themedCSS("typography"); // LEAVE HERE - NOT EASY TO INCLUDE VIA TEMPLATE
			Requirements::themedCSS("OrderReport"); // LEAVE HERE - NOT EASY TO INCLUDE VIA TEMPLATE
			Requirements::themedCSS("Order_Invoice", "print"); // LEAVE HERE - NOT EASY TO INCLUDE VIA TEMPLATE
			return $this->renderWith("Invoice");
		}
		elseif(isset($_REQUEST["packingslip"])) {
			Requirements::clear();
			Requirements::themedCSS("typography"); // LEAVE HERE - NOT EASY TO INCLUDE VIA TEMPLATE
			Requirements::themedCSS("OrderReport"); // LEAVE HERE - NOT EASY TO INCLUDE VIA TEMPLATE
			Requirements::themedCSS("Order_PackingSlip"); // LEAVE HERE - NOT EASY TO INCLUDE VIA TEMPLATE
			return $this->renderWith("PackingSlip");
		}
		return array();
	}

	/**
	 * Returns a dataobject set of the checkout steps if
	 * the OrderConfirmationPage is shown as part of the checkout process
	 * We repeat these here so that you can show the user that (s)he has reached the last step
	 *
	 * @param Int $number - if set, it returns that one step.
	 * @return Null | DataObject (CheckoutPage_Description) | ArrayList (CheckoutPage_Description)
	 */
	function CheckoutSteps($number = 0) {
		$where = '';
		if($number) {
			$where = "\"CheckoutPage_StepDescription\".\"ID\" = $number";
		}
		if(EcommerceConfig::get("OrderConfirmationPage_Controller", "include_as_checkout_step")) {
			if($this->currentOrder->SessionID && $this->currentOrder->SessionID == session_id()) {
				$dos = DataObject::get("CheckoutPage_StepDescription", $where, "\"ID\" ASC");
				if($number) {
					if($dos) {
						return $dos->First();
					}
				}
				$arrayList = new ArrayList(array());
				foreach($dos as $do) {
					$do->LinkingMode = "link completed";
					$do->Completed = 1;
					$do->Link = "";
					$arrayList->push($do);
				}
				$do = $this->CurrentCheckoutStep(true);
				if($do) {
					$arrayList->push($do);
				}
				return $arrayList;
			}
		}
	}

	/**
	 * returns the percentage of checkout steps done (0 - 100)
	 * @return Integer
	 */
	public function PercentageDone(){
		return 100;
	}

	/**
	 * Returns the form to cancel the current order,
	 * checking to see if they can cancel their order
	 * first of all.
	 *
	 * @return OrderForm_Cancel
	 */
	function CancelForm() {
		if($this->Order()) {
			if($this->currentOrder->canCancel()) {
				return new OrderForm_Cancel($this, 'CancelForm', $this->currentOrder);
			}
		}
		//once cancelled, you will be redirected to main page - hence we need this...
		if($this->orderID) {
			return array();
		}
	}

	/**
	 * show the payment form
	 *@return Form (OrderForm_Payment) or Null
	 **/
	function PaymentForm(){
		if($this->Order()){
			if($this->currentOrder->canPay()) {
				Requirements::javascript("ecommerce/javascript/EcomPayment.js");
				return new OrderForm_Payment($this, 'PaymentForm', $this->currentOrder);
			}
		}
	}

	/**
	 * This is an additional way to look at an order.
	 * The order is already retrieved from the
	 *@return Array
	 **/
	function retrieveorder(){
		return array();
	}

	/**
	 *@return Array - just so the template is still displayed
	 **/
	function sendreceipt($request) {
		if($o = $this->currentOrder) {
			if($m = $o->Member()) {
				if($m->Email) {
					$subject = _t("Account.COPYONLY", "--- COPY ONLY ---");
					$message = _t("Account.COPYONLY", "--- COPY ONLY ---");
					$o->sendReceipt($subject, $message, true);
					$this->message = _t('OrderConfirmationPage.RECEIPTSENT', 'An order receipt has been sent to: ').$m->Email.'.';
				}
				else {
					$this->message = _t('OrderConfirmationPage.RECEIPTNOTSENTNOTSENDING', 'Email could NOT be sent.');
				}
			}
			else {
				$this->message = _t('OrderConfirmationPage.RECEIPTNOTSENTNOEMAIL', 'No email could be found for sending this receipt.');
			}
		}
		else {
			$this->message = _t('OrderConfirmationPage.RECEIPTNOTSENTNOORDER', 'Order could not be found.');
		}
		$baseFolder = Director::baseFolder() ;
		if(!class_exists('Emogrifier')) {
			require_once(Director::baseFolder() . '/ecommerce/thirdparty/Emogrifier.php');
		}
		Requirements::clear();
		Requirements::themedCSS("typography"); // LEAVE HERE - NOT EASY TO INCLUDE VIA TEMPLATE
		Requirements::themedCSS("OrderReport"); // LEAVE HERE - NOT EASY TO INCLUDE VIA TEMPLATE
		Requirements::themedCSS("Order_Invoice", "print"); // LEAVE HERE - NOT EASY TO INCLUDE VIA TEMPLATE
		$html =  $this->renderWith("Order_ReceiptEmail");
		// if it's an html email, filter it through emogrifier
		$cssFileLocation = $baseFolder . "/". EcommerceConfig::get("Order_Email", "css_file_location");;
		$html .= "\r\n\r\n<!-- CSS can be found here: $cssFileLocation -->";
		$cssFileHandler = fopen($cssFileLocation, 'r');
		$css = fread($cssFileHandler,  filesize($cssFileLocation));
		fclose($cssFileHandler);
		$emog = new Emogrifier($html, $css);
		$html = $emog->emogrify();
		return $html;
	}

}



