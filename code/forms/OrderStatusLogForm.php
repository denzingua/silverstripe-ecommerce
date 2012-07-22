<?php


/**
 * @description: this class is the base class for Order Log Forms in the checkout form...
 *
 * @see OrderLog
 *
 *
 * @authors: Nicolaas [at] Sunny Side Up .co.nz
 * @package: ecommerce
 * @sub-package: forms
 * @inspiration: Silverstripe Ltd, Jeremy
 **/

class OrderStatusLogForm extends Form {


	protected $order;


	/**
	 *NOTE: we semi-enforce using the OrderLog_Controller here to deal with the submission of the OrderStatusLogForm
	 * You can use your own Logs or an extension of OrderLog_Controller by setting the first parameter (optionalController)
	 * to your own controller.
	 *
	 *@param $optionalController Controller
	 *@param $name String
	 *@param $fields FieldSet
	 *@param $actions FieldSet
	 *@param $validator SS_Validator
	 **/

	function __construct($optionalController = null, $name, FieldSet $fields, FieldSet $actions,$optionalValidator = null){
		if(!$optionalController) {
			$controllerClassName = EcommerceConfig::get("OrderStatusLogForm", "controller_class");
			$optionalController = new $controllerClassName();
		}
		if(!$optionalValidator) {
			$validatorClassName = EcommerceConfig::get("OrderStatusLogForm", "validator_class");
			$optionalValidator = new $validatorClassName();
		}
		parent::__construct($optionalController, $name, $fields, $actions, $optionalValidator);
		Requirements::themedCSS($this->ClassName);
		Requirements::javascript(THIRDPARTY_DIR."/jquery-form/jquery.form.js");
		//add JS for the Log - added in Log
	}

}



/**
 * This controller allows you to submit Log forms from anywhere on the site, especially the cart / checkout page.
 */
class OrderStatusLogForm_Controller extends Controller{

	protected $currentOrder = null;

	static $allowed_actions = array(
		'removeLog'
	);

	public function init() {
		parent::init();
		$this->currentOrder = ShoppingCart::current_order();
		$this->initVirtualMethods();
	}

	/**
	 * Inits the virtual methods from the name of the Log forms to
	 * redirect the action method to the form class
	 */
	protected function initVirtualMethods() {
		if($this->currentOrder) {
			if($forms = $this->currentOrder->getLogForms($this)) {
				foreach($forms as $form) {
					$this->addWrapperMethod($form->Name(), 'getOrderStatusLogForm');
					self::$allowed_actions[] = $form->Name(); // add all these forms to the list of allowed actions also
				}
			}
		}
	}

	/**
	 * Return a specific {@link OrderStatusLogForm} by it's name.
	 *
	 * @param string $name The name of the form to return
	 * @return Form
	 */
	protected function getOrderStatusLogForm($name) {
		if($this->currentOrder) {
			if($forms = $this->currentOrder->getLogForms($this)) {
				foreach($forms as $form) {
					if($form->Name() == $name) return $form;
				}
			}
		}
	}

	function Link($action = null){
		$action = ($action)? "/$action/" : "";
		return $this->class.$action;
	}

	function removeLog(){
		//See issue 149
	}

}


class OrderStatusLogForm_Validator extends RequiredFields{


}
