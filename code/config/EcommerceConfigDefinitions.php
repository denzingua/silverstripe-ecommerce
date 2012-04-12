<?php



/**
 * This class sets out the static config variables for e-commerce.
 * It also adds the definitions of any classes that extend
 * EcommerceConfigDefitions.
 **/


class EcommerceConfigDefinitions extends Object {


	/**
	 * Tells us what version of e-commerce we are using
	 *
	 * @var Float
	 */
	private $version = 0.9;

	/**
	 * Tells us the version of e-commerce in use.
	 * @return Int
	 */
	public function Version(){
		return $this->version;
	}


	/**
	 * Tells us the svn revision of e-commerce in use.
	 * @return Int
	 */
	public function SvnVersion(){
		$svnrev = "0";
		$file = Director::baseFolder()."/ecommerce/.svn/entries";
		if(file_exists($file)){
			$svn = @File($file);
			if($svn && isset($svn[3])){
				$svnrev = $svn[3];
			}
		}
		return $svnrev;
	}


	/**
	 * Get a list of all definitions required for e-commerce.
	 * We have this here so that we can check that all static variables have been defined.
	 * We can also use this list for clean formatting.
	 *
	 * This list is for developers only
	 *
	 * @param String $className - only return for this class name
	 * @param String #variable - only return this variable (must define class name as well)
	 * @return Array | String
	 */
	public function Definitions($className = "", $variable = "") {
		$array = array(
		################### PAGES #####################
			"CheckoutPage_Controller" => array(
				"checkout_steps" => "The Checkout Steps.  This can be defined as you like, but the last step should always be: orderconfirmationandpayment."
			),
			"ProductGroup" => array(
				"sort_options" => "associative sort options array with sub-keys of Title and SQL, e.g. 'default' = array('Title' => 'default', 'SQL' => 'Title DESC')",
				"filter_options" => "associative filters options array with sub-keys of Title and SQL, e.g. 'default' = array('Featured' => 'default', 'SQL' => 'Featured = 1')",
				"allow_short_display_style" => "Does the shop admin have the option to show products as a condensed list (little detail per product) in the product group page? Note that a template may need to be themed for this so you want to be careful to make this available. You can consider using this for product groups with lots of products.",
				"allow_more_detail_display_style" => "Does the shop admin have the option to show products as a expanded list (lots of details per product) in the product group page? Note that a template may need to be themed for this so you want to be careful to make this available. You can consider using this for product groups with only a few products. ",
			),
			"Product_Image" =>  array(
				"thumbnail_width" => "Thumbnail width in pixels. For thumbnails, we use paddedResize.",
				"thumbnail_height" => "Thumbnail height in pixels. For thumbnails, we use paddedResize.",
				"content_image_width" => "Width for the content image. We use these settings to improve image quality and to set strict standard sizes. For the content and large image we use the SetWidth method.",
				"large_image_width" => "Width for the large (zoom) image. We use these settings to improve image quality and to set strict standard sizes. For the content and large image we use the SetWidth method."
			),
			"Order" =>  array(
				"modifiers" => "This is the single most important setting.  here you determine what modifiers are being added to every order.  You can just add them as a non-associative array.  However, their order is important!",
				"maximum_ignorable_sales_payments_difference" => "The maximum allowable difference between the Order Total and the Payment Total. 	If this value is, for example, 10 cents and the total amount outstanding for an order is less than ten cents, than the order is considered 'paid'",
				"order_id_start_number" => "The starting number for the order number. For example, if you enter 1000 here then the first order will have number 1000, the next one 1001 and so on.",
				"template_id_prefix" => "If you end up with conflicts in your templates (e.g. having the same ID twice) then you can use this variable to set an prefix to all PHP generated IDs in all templates. We use these PHP generated IDs for AJAX templates - where HTML, JS and PHP need to work together. "
			),
			"OrderStatusLog" => array(
				"available_log_classes_array" => "Tells us what order log classes are to be used. OrderStatusLog_Submitted should always be used and does not need to be listed here.",
				"order_status_log_class_used_for_submitting_order" => "This is the log class used to record the submission of the order.  It is crucial to set this to the right log class, as a lot of the functionality in e-commerce depends on it: ",
			),
			"OrderStep" => array(
				"order_steps_to_include" => "Another very important definition.  These are the steps that the order goes through from creation to archiving.  A bunch of standard steps have been included in the e-commerce module, but this is also a place where you can add / remove your own customisations (steps) as required by your individual project.",
			),
			"OrderStep_Confirmed" => array(
				"list_of_things_to_check"   => "One of the steps in the order steps sequence is the Order Confirmation.  This is when the Shop Admin looks at all the detail in the order and confirms it is ready to be completed.  Here you can create an HTML list of items to check (e.g. has it been paid, do you have the products in stock, is there a delivery address, etc....)",
			),
			"OrderAddress" => array(
				"use_separate_shipping_address" => "Do the goods need to get shipped and if so, do we allow these goods to be shipped to a different address than the billing address?",
				"use_shipping_address_for_main_region_and_country" => "In determing the country/region from which the order originated. For, for example, tax purposes - we use the Billing Address (@see Order::Country). However, we can also choose the Shipping Address by setting this variable to TRUE.",
				"field_class_and_id_prefix" => "In case you have some conflicts in the class / IDs for formfields then you can use this variable to add a few characters in front of the classes / IDs",
			),
			"Buyable" => array(
				"array_of_buyables" => "Array of classes (e.g. Product, ProductVariation, etc...) that are buyable.",
				"order_item_class_name_post_fix" => "For every buyable class, we expected a related OrderItem class.  The name is determined by this post fix. The default post-fix is _OrderItem.  With this post fix Product would have an OrderItem class called Product_OrderItem. ProductVariation links to a class called ProductVariation_OrderItem and MyDataObject will add MyDataObject_OrderItem to the cart (note the order item gets added to the cart rather than the Object itself). The Order Item should extend OrderItem and also have information about the object being sold (e.g. price). The reason we dont sell Products but rather Product_OrderItems is because there is only one product, but we have many Product_OrderItems.",
			),
			"EcommerceRole" => array(
				"allow_customers_to_setup_accounts" => "Allow customers to become 'members' when they purchase items. If this is false then customers can never setup an account.",
				"automatic_membership" => "When this is set to TRUE, any purchasers are automatically added as members even if they do not enter a password. When set to false, customers are only added as members if they enter a password.",
				"automatically_update_member_details" => "When set to true, the member fields (e.g. email, surname, first name) will be automatically updated from the billing address.  That is, if the customers enters a different email or surname in the billing field then the member record will be updated based on these new values.",
				"customer_group_code" => "Code for the customer member group.",
				"customer_group_name" => "Title (name) for the customer member group.",
				"customer_permission_code" => "Permission code for the customer member group.",
				"admin_group_code" => "Code for the shop administrator member group.",
				"admin_group_name" => "Title (name) for the shop administrator member group.",
				"admin_permission_code" => "Permission code for the shop administrator member group.",
				"admin_role_title" => "Role title for the shop administrator member group.",
				"admin_role_permission_codes" => "Permission codes for the shop administrator member group.",
			),
			"OrderModifierForm" => array(
				"controller_class" => "The controller class is used for Order Modifers forms.",
				"validator_class" => "The validator class is used for Order Modifer forms.",
			),
			"OrderStatusLogForm" => array(
				"controller_class" => "The controller class is used for OrderStatusLogForm forms.",
				"validator_class" => "The validator class is used for OrderStatusLogForm forms.",
			),
			"EcommerceCountry" => array(
				"allowed_country_codes" => "To what countries are you selling.  You can leave this as an emptry array, in case you are selling to anyone or you can restrict it to just one country or a handful.  It should also be formatted as an array.",
			),
			"Order_Email" => array(
				"send_all_emails_plain" => "Should all the emails be send as plain text?  Not recommended.",
				"css_file_location" => "This is a really useful setting where you can specify the location for a css file that is 'injected' into the customer emails. ",
				"copy_to_admin_for_all_emails" => "Send a copy to the shop administrator for every email sent?",
			),
			"ExpiryDateField" => array(
				"short_months" => "Should we use short codes for the Expiry Date Field (e.g. Jan rather than January)?",
			),
			"CartPage_Controller" => array(
				"session_code" => "Code name for session variable used in Cart Page.  This session variable is used to retain a message.",
			),
			"ShoppingCart" => array(
				"session_code" => "The code use for the session variable that stores the Order ID.",
				"cleanup_every_time" => "Are carts are cleaned up all the time (if this is set to FALSE then we recommend you setup a cron job to clean old carts)?",
				"default_param_filters" => "Advanced filtering in the shopping cart.  Not currently being used. ",
				"response_class" => "Class used for ajax responses.",
			),
			"ShoppingCart_Controller" => array(
				"url_segment" => "URL Segment used for the shopping cart."
			),
			"EcommercePaymentController" => array(
				"url_segment" => "URL Segment used for the payment process."
			),
			"EcommerceConfigAjax" => array(
				"definitions_class_name" => "Class Name (string) for the class used to define and name all the ajax IDs and Classes."
			),
			"FlatTaxModifier" => array(
				"name" => "Name of the tax - e.g. VAT",
				"rate" => "Rate of the tax - e.g. 0.1",
				"exclusive" => "Are the prices on the site inclusive or exclusive of GST?",
			),
			"SimpleShippingModifier" => array(
				"default_charge" => "default charge for shipping",
				"charges_by_country" => "charges by country",
			),
			"StoreAdmin" => array(
				"managed_models" => "An array of data object classes that are managed as 'Store' configuration items.  You can add to these by using StoreAdmin::add_managed_model, or by adding to this array.  This configuration is used a lot to add extra menu items. ",
				//"collection_controller_class" => "The controller for the collection.  ",
				//"record_controller_class" => "The controller for the record. ",
			),
			"ProductsAndGroupsModelAdmin" => array(
				"managed_models" => "An array of data object classes that are managed as 'Store' configuration items.  You can add to these by using StoreAdmin::add_managed_model, or by adding to this array.  This configuration is used a lot to add extra menu items. ",
				//"collection_controller_class" => "The controller for the collection.  ",
				//"record_controller_class" => "The controller for the record. ",
			),
			"SalesAdmin" => array(
				"managed_models" => "An array of data object classes that are managed as 'Store' configuration items.  You can add to these by using StoreAdmin::add_managed_model, or by adding to this array.  This configuration is used a lot to add extra menu items. ",
				//"collection_controller_class" => "The controller for the collection.  ",
				//"record_controller_class" => "The controller for the record. ",
			),
			"RecalculateTheNumberOfProductsSold" => array(
				"number_sold_calculation_type" => "Method for calculating the total number of items sold.  We either COUNT the number of orders or we make a SUM of the number of items sold. ",
			),
			"CartCleanupTask" => array(
				"clear_days" => "If set to zero, all objects will be cleared. If set to ten, objects older than ten days will be cleared.",
				"maximum_number_of_objects_deleted" => "This sets the total number of objects to be cleaned per clean.  We can keep this low to reduce time per clean and to reduce risks.",
				"never_delete_if_linked_to_member" => "If set to TRUE, then orders with a member linked to it will never be deleted.",
				"linked_objects_array" => "An array of objects that are linked to orders. When we delete an order we also delete these linked objects."
			),
		);
		//add more stuff through extensions
		$this->extend("moreDefinitions", $array);
		//add more stuff through child classes
		$childClasses = ClassInfo::subclassesFor($this);
		if(is_array($childClasses) && count($childClasses)) {
			foreach($childClasses as $class) {
				if($class != $this->class) {
					$childObject = new $class();
					$array = array_merge($array, $childObject->Definitions());
				}
			}
		}
		//return what is appropriate
		if($className && $variable) {
			return $array[$className][$variable];
		}
		elseif($className) {
			return $array[$className];
		}
		else {
			return $array;
		}
	}
}
