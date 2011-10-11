<?php

/**
 * CheckoutPage is a CMS page-type that shows the order
 * details to the customer for their current shopping
 * cart on the site. It also lets the customer review
 * the items in their cart, and manipulate them (add more,
 * deduct or remove items completely). The most important
 * thing is that the {@link CheckoutPage_Controller} handles
 * the {@link OrderForm} form instance, allowing the customer
 * to fill out their shipping details, confirming their order
 * and making a payment.
 *
 * @see CheckoutPage_Controller->Order()
 * @see OrderForm
 * @see CheckoutPage_Controller->OrderForm()
 *
 * The CheckoutPage_Controller is also responsible for setting
 * up the modifier forms for each of the OrderModifiers that are
 * enabled on the site (if applicable - some don't require a form
 * for user input). A usual implementation of a modifier form would
 * be something like allowing the customer to enter a discount code
 * so they can receive a discount on their order.
 *
 * @see OrderModifier
 * @see CheckoutPage_Controller->ModifierForms()
 *
 * TO DO: get rid of all the messages...
 *
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 * @package: ecommerce
 * @sub-package: pages
 *
 **/

class CheckoutPage extends CartPage {

	public static $icon = 'ecommerce/images/icons/CheckoutPage';

	public static $db = array (
		'HasOrderSteps' => 'Boolean',
		'InvitationToCompleteOrder' => 'HTMLText',
		'AlreadyCompletedMessage' => 'HTMLText',
		'NoItemsInOrderMessage' => 'HTMLText',
		'NonExistingOrderMessage' => 'HTMLText',
		'MustLoginToCheckoutMessage' => 'HTMLText',
		'LoginToOrderLinkLabel' => 'Varchar(100)',
		'FinalizedOrderLinkLabel' => 'Varchar(100)',
		'CurrentOrderLinkLabel' => 'Varchar(100)',
		'StartNewOrderLinkLabel' => 'Varchar(100)'
	);

	public static $has_one = array (
		'TermsPage' => 'Page'
	);

	public static $defaults = array (
		'InvitationToCompleteOrder' => '<p>Please finalise your order below.</p>',
		'AlreadyCompletedMessage' => '<p>This order has already been completed.</p>',
		'NoItemsInOrderMessage' => '<p>There are no items in your order.</p>',
		'NonExistingOrderMessage' => '<p>Sorry, this order can not be found.</p>',
		'MustLoginToCheckoutMessage' => '<p>You must log in first before finalising this order.</p>',
		'LoginToOrderLinkLabel' => 'Plese log in to access this order',
		'FinalizedOrderLinkLabel' => 'View completed order',
		'CurrentOrderLinkLabel' => 'Go to current order',
		'StartNewOrderLinkLabel' => 'Start new order'
	);

	/**
	 * Returns the Terms and Conditions Page (if there is one).
	 * @return DataObject (Page)
	 */
	public static function find_terms_and_conditions_page() {
		$checkoutPage = DataObject::get_one('CheckoutPage', "\"ClassName\" = 'CheckoutPage'");
		if($checkoutPage) {
			return DataObject::get_by_id('Page', $checkoutPage->TermsPageID);
		}
	}

	/**
	 * Returns the link or the Link to the account page on this site
	 * @return String (URLSegment)
	 */
	public static function find_link() {
		if ($page = DataObject :: get_one('CheckoutPage', "\"ClassName\" = 'CheckoutPage'")) {
			return $page->Link();
		}
	}

	/**
	 * Returns the link to the checkout page on this site, using
	 * a specific Order ID that already exists in the database.
	 *
	 * @param int $orderID ID of the {@link Order}
	 * @param boolean $urlSegment If set to TRUE, only returns the URLSegment field
	 * @return string Link to checkout page
	 */
	public static function get_checkout_order_link($orderID) {
		if($page = self::find_link()) {
			return $page->Link("showorder") . "/" . $orderID . "/";
		}
	}

	/**
	 * Standard SS function, we only allow for one checkout page to exist
	 *@return Boolean
	 **/
	function canCreate($member = null) {
		return !DataObject :: get_one("CheckoutPage", "\"ClassName\" = 'CheckoutPage'");
	}

	/**
	 * Standard SS function
	 *@return FieldSet
	 **/
	function getCMSFields() {
		$fields = parent :: getCMSFields();
		$fields->removeFieldFromTab('Root.Content.Main', "Content");
		$fields->addFieldToTab('Root.Content.Process', new TreeDropdownField('TermsPageID', 'Terms and Conditions Page', 'SiteTree'));
		$fields->addFieldToTab('Root.Content.Process', new CheckboxField('HasOrderSteps', 'Checkout Process in Steps'));
		$fields->addFieldsToTab('Root.Content.Messages', array (
			new TabSet(
				"MessageOptions",
				new Tab(
					"NormalOrder",
					new HtmlEditorField('InvitationToCompleteOrder', 'Invitation to complete order ... shown when the customer can do a normal checkout', $row = 4)

				),
				new Tab(
					"NoItems",
					new HtmlEditorField('NoItemsInOrderMessage', 'No items in order - shown when the customer tries to checkout an order without items.', $row = 4)

				),
				new Tab(
					"NonExistingOrder",
					new HtmlEditorField('NonExistingOrderMessage', 'Non-existing Order - shown when the customer tries to load a non-existing order.', $row = 4)
				),
				new Tab(
					"AlreadyCompleted",
					new HtmlEditorField('AlreadyCompletedMessage', 'Already Completed - shown when the customer tries to checkout an already completed order', $row = 4)
				),
				new Tab(
					"OldOrder",
					new HtmlEditorField('MustLoginToCheckoutMessage', 'MustLoginToCheckoutMessage', $row = 4)

				),
				new Tab (
					"LinksAndLabels",
					new TextField('FinalizedOrderLinkLabel', 'Label for the link pointing to a completed order - e.g. click here to view the completed order'),
					new TextField('CurrentOrderLinkLabel', 'Label for the link pointing to the current order - e.g. click here to view current order'),
					new TextField('StartNewOrderLinkLabel', 'Label for starting new order - e.g. click here to start new order'),
					new TextField('LoginToOrderLinkLabel', 'Label for the link pointing to the order which requires a log in - e.g. click here to log in and view order')
				)
			)
		));
		$fields->addFieldToTab('Root.Content.AlwaysVisible', new HtmlEditorField('Content', 'General note', 7, 7));
		return $fields;
	}

}
class CheckoutPage_Controller extends CartPage_Controller {

	/**
	 * FOR STEP STUFF SEE BELOW
	 **/


	/**
	 * Standard SS function
	 * if set to false, user can edit order, if set to true, user can only review order
	 **/
	public function init() {
		parent::init();
		Requirements::javascript('ecommerce/javascript/EcomPayment.js');
		if($this->HasOrderSteps) {
			if(!$this->orderstep) {
				$this->currentStep = $this->checkoutSteps[0];
			}
		}
	}

	function processmodifierform($request) {
		$formName = $request->param("ID");
		if ($forms = $this->ModifierForms()) {
			foreach ($forms as $form) {
				$fullName = explode("/", $form->Name());
				$shortName = $fullName[1];
				if ($shortName == $formName) {
					return $form->submit($request->requestVars(), $form);
				}
			}
		}
	}

	/**
	 * Returns a DataObjectSet of {@link OrderModifierForm} objects. These
	 * forms are used in the OrderInformation HTML table for the user to fill
	 * in as needed for each modifier applied on the site.
	 *
	 * @return DataObjectSet
	 */
	function ModifierForms() {
		if ($this->currentOrder) {
			return $this->currentOrder->getModifierForms();
		}
	}


	/**
	 * Returns a form allowing a user to enter their
	 * details to checkout their order.
	 *
	 * @return OrderForm object
	 */
	function OrderForm() {
		$form = new OrderForm($this, 'OrderForm');
		$this->data()->extend('updateOrderForm', $form);
		//load session data
		if ($data = Session :: get("FormInfo.{$form->FormName()}.data")) {
			$form->loadDataFrom($data);
		}
		return $form;
	}

	/**
	 * Can the user proceed? It must be an editable order (see @link CartPage)
	 * and is must also contain items.
	 *
	 * @return boolean
	 */
	function CanCheckout() {
		return $this->currentOrder->Items()  && !$this->currentOrder->IsSubmitted();
	}


	function ModifierForm($request) {
		user_error("Make sure that you set the controller for your ModifierForm to a controller directly associated with the Modifier", E_USER_WARNING);
		return array ();
	}

	/**
	 * Returns a message explaining why the customer
	 * can't checkout the requested order.
	 *
	 */
	protected function workOutMessagesAndActions() {
		if(!$this->workedOutMessagesAndActions) {
			$this->actionLinks = new DataObjectSet();
			$checkoutLink = CheckoutPage::find_link();
			if($this->CanCheckout()) {
				//no action links...
				$this->message = $this->InvitationToCompleteOrder;
			}
			//not logged in, an order was requested, must login first!
			elseif (!Member::currentUserID() && $this->OrderID && !$this->currentOrder ) {
				$redirectLink = CheckoutPage::get_checkout_order_link($this->OrderID);
				//retrieve requested order by logging in
				$this->actionLinks->push(new ArrayData(array (
					"Title" => $this->LoginToOrderLinkLabel,
					"Link" => 'Security/login?BackURL=' . urlencode($redirectLink)
				)));
				// open current order
				$this->actionLinks->push(new ArrayData(array (
					"Title" => $this->CurrentOrderLinkLabel,
					"Link" => $checkoutLink
				)));
				$this->message = $this->MustLoginToCheckoutMessage;
			}
			//already logged in, but order can not be found: order does not exist!
			elseif (Member::currentUserID() && $this->OrderID && !$this->currentOrder) {
				$this->actionLinks->push(new arrayData(array (
					"Title" => $this->CurrentOrderLinkLabel,
					"Link" => $checkoutLink
				)));
				$this->message = $this->NonExistingOrderMessage;
			}
			//no items in basket
			elseif ($this->currentOrder && !$this->currentOrder->Items()) {
				//no action links
				$this->message = $this->NoItemsInOrderMessage;
			}
			//order can not be edited:
			elseif ($this->currentOrder && $this->currentOrder->IsSubmitted()) {
				//review order... in order confirmation page
				$this->actionLinks->push(new ArrayData(array (
					"Title" => $this->FinalizedOrderLinkLabel,
					"Link" => $this->currentOrder->Link()
				)));
				//start a new order
				$this->actionLinks->push(new ArrayData(array (
					"Title" => $this->StartNewOrderLinkLabel,
					"Link" => CartPage::new_order_link()
				)));
				$this->message = $this->AlreadyCompletedMessage;
			}
			else {
				$this->message = "An error occured in retrieving your order...";
			}
			$this->workedOutMessagesAndActions = true;
		}
	}
	/**
	 * STEP STUFF
	 *

	/**
	 *@var $currentStep Integer
	 * if set to zero (0), all steps will be included
	 **/
	protected $checkoutSteps = array(
		"orderitems",
		"ordermodifiers",
		"orderconfirmation",
		"orderformandpayment"
	);


	/**
	 *@var $currentStep Integer
	 **/
	protected $currentStep = "";

	/**
	 * Show only one step in the order process (e.g. only show OrderItems)
	 */
	function orderstep($request) {
		$step = $request->Param("ID");
		if($step) {
			if (in_array($step, $this->checkoutSteps)) {
				$this->currentStep = $step;
			}
		}
		return array ();
	}


	/**
	 *@param $part Strong (OrderItems, OrderModifiers, OrderForm, OrderPayment)
	 *@return Boolean
	 **/
	function CanShowStep($step) {
		if (!$this->currentStep) {
			return in_array($step, $this->checkoutSteps);
		}
		else {
			return $step == $this->currentStep;
		}
	}


}
