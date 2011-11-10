<?php


/**
 * @description:
 * - cleans up old (abandonned) carts...
 * - sets up default records
 *
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 * @package: ecommerce
 * @sub-package: setup
 *
 **/


class EcommerceDefaultRecords extends DatabaseAdmin {

	function run() {

		// ACCOUNT PAGE
		if(!DataObject::get_one('AccountPage')) {
			$page = new AccountPage();
			$page->Title = 'Account';
			$page->Content = '<p>This is the account page. It is used for shop users to login and change their member details if they have an account.</p>';
			$page->URLSegment = 'account';
			$page->ShowInMenus = 0;
			$page->writeToStage('Stage');
			$page->publish('Stage', 'Live');
			DB::alteration_message('Account page \'Account\' created', 'created');
		}
		// ACCOUNT PAGE
		if(!DataObject::get_one('CartPage')) {
			$page = new CartPage();
			$page->Title = 'Cart';
			$page->Content = '<p>This is the account page. It is used for shop users to login and change their member details if they have an account.</p>';
			$page->URLSegment = 'account';
			$page->ShowInMenus = 0;
			$page->writeToStage('Stage');
			$page->publish('Stage', 'Live');
			DB::alteration_message('Account page \'Account\' created', 'created');
		}


		//CHECKOUT PAGE

		$update = array();
		if(!$page = DataObject::get_one('CheckoutPage')) {
			$page = new CheckoutPage();
			$page->Content = '<p>This is the checkout page. The order summary and order form appear below this content.</p>';
			$page->MetaTitle = 'Checkout';
			$page->MenuTitle = 'Checkout';
			$page->Title = 'Checkout';
			$update[] = 'Checkout page \'Checkout\' created';
			$page->URLSegment = 'checkout';
			$page->ShowInMenus = 0;
			if($page->TermsPageID == 0 && $termsPage = DataObject::get_one('Page', "\"URLSegment\" = 'terms-and-conditions'")) {
				$page->TermsPageID = $termsPage->ID;
				$update[] = 'added terms page';
			}
		}
		if($page) {
			if(!$page->PurchaseComplete) {$page->PurchaseComplete = '<p>'._t('Checkout.PURCHASECOMPLETE','Your purchase is complete.').'</p>'; $update[] = "added PurchaseComplete content";}
			if(!$page->ChequeMessage) {$page->ChequeMessage = '<p>'._t('Checkout.CHEQUEMESSAGE','Please note: Your goods will not be dispatched until we receive your payment.').'</p>'; $update[] = "added ChequeMessage content";}
			if(!$page->CurrentOrderLinkLabel) {$page->CurrentOrderLinkLabel = _t('Checkout.CURRENTORDERLINKLABEL','view current order'); $update[] = "added CurrentOrderLinkLabel content";}
			if(!$page->SaveOrderLinkLabel) {$page->SaveOrderLinkLabel = _t('Checkout.SAVEORDERLINKLABEL','start new order'); $update[] = "added SaveOrderLinkLabel content";}
			if(!$page->NonExistingOrderMessage) {$page->NonExistingOrderMessage = '<p>'._t('Checkout.NONEXISTINGORDERMESSAGE','We can not find your order.').'</p>'; $update[] = "added NonExistingOrderMessage content";}
			if(!$page->NoItemsInOrderMessage) {$page->NoItemsInOrderMessage = '<p>'._t('Checkout.NONITEMSINORDERMESSAGE','There are no items in your order. Please add some products first.').'</p>'; $update[] = "added NoItemsInOrderMessage content";}
			if(!$page->LoginToOrderLinkLabel) {$page->LoginToOrderLinkLabel = '<p>'._t('Checkout.LOGINTOORDERLINKLABEL','log in and view order').'</p>'; $update[] = "added LoginToOrderLinkLabel content";}
			if(count($update)) {
				$page->writeToStage('Stage');
				$page->publish('Stage', 'Live');
				DB::alteration_message("create / updated checkout page: ".implode("<br />", $update), 'created');
			}
		}

	}


	function addproducts() {


		// PRODUCT PAGE

		if(!DataObject::get_one('Product')) {
			if(!DataObject::get_one('ProductGroup')) singleton('ProductGroup')->requireDefaultRecords();
			if($group = DataObject::get_one('ProductGroup', '', true, "\"ParentID\" DESC")) {
				$content = '<p>This is a <em>product</em>. It\'s description goes into the Content field as a standard SilverStripe page would have it\'s content. This is an ideal place to describe your product.</p>';

				$page1 = new Product();
				$page1->Title = 'Example product';
				$page1->Content = $content . '<p>You may also notice that we have checked it as a featured product and it will be displayed on the main Products page.</p>';
				$page1->URLSegment = 'example-product';
				$page1->ParentID = $group->ID;
				$page1->Price = '15.00';
				$page1->FeaturedProduct = true;
				$page1->writeToStage('Stage');
				$page1->publish('Stage', 'Live');
				DB::alteration_message('Product page \'Example product\' created', 'created');

				$page2 = new Product();
				$page2->Title = 'Example product 2';
				$page2->Content = $content;
				$page2->URLSegment = 'example-product-2';
				$page2->ParentID = $group->ID;
				$page2->Price = '25.00';
				$page2->writeToStage('Stage');
				$page2->publish('Stage', 'Live');
				DB::alteration_message('Product page \'Example product 2\' created', 'created');
			}
		}

		// PRODUCT GROUPS
		if(!DataObject::get_one('ProductGroup')) {
			$page1 = new ProductGroup();
			$page1->Title = 'Products';
			$page1->Content = "
				<p>This is the top level products page, it uses the <em>product group</em> page type, and it allows you to show your products checked as 'featured' on it. It also allows you to nest <em>product group</em> pages inside it.</p>
				<p>For example, you have a product group called 'DVDs', and inside you have more product groups like 'sci-fi', 'horrors' or 'action'.</p>
				<p>In this example we have setup a main product group (this page), with a nested product group containing 2 example products.</p>
			";
			$page1->URLSegment = 'products';
			$page1->NumberOfProductsPerPage = 5;
			$page1->writeToStage('Stage');
			$page1->publish('Stage', 'Live');
			DB::alteration_message('Product group page \'Products\' created', 'created');

			$page2 = new ProductGroup();
			$page2->Title = 'Example product group';
			$page2->Content = '<p>This is a nested <em>product group</em> within the main <em>product group</em> page. You can add a paragraph here to describe what this product group is about, and what sort of products you can expect to find in it.</p>';
			$page2->URLSegment = 'example-product-group';
			$page1->NumberOfProductsPerPage = 5;
			$page2->ParentID = $page1->ID;
			$page2->writeToStage('Stage');
			$page2->publish('Stage', 'Live');
			DB::alteration_message('Product group page \'Example product group\' created', 'created');
		}
	}

	/**
	 * This method (removeallorders) is useful when you have placed a whole bunch of practice orders
	 * and you want to go live with the same Database - but without all the practice orders....
	 *
	 **/
	function removeallorders() {

	}
}

class EcommerceDefaultRecords_DataObject extends DataObject {

	function requireDefaultRecords() {
		parent::requireDefaultRecords();
		$customerGroup = DataObject::get_one("Group", "\"Code\" = '".EcommerceRole::get_customer_group_code()."' ");
		if(!$customerGroup) {
			$customerGroup = new Group();
			$customerGroup->Code = EcommerceRole::get_customer_group_code();
			$customerGroup->Title = EcommerceRole::get_customer_group_name();
			$customerGroup->write();
			Permission::grant( $customerGroup->ID, EcommerceRole::get_customer_permission_code());
			DB::alteration_message(EcommerceRole::get_customer_group_name().' Group created',"created");
		}
		elseif(DB::query("SELECT * FROM \"Permission\" WHERE \"GroupID\" = '".$customerGroup->ID."' AND \"Code\" LIKE '".EcommerceRole::get_customer_permission_code()."'")->numRecords() == 0 ) {
			Permission::grant($customerGroup->ID, EcommerceRole::get_customer_permission_code());
			DB::alteration_message(EcommerceRole::get_customer_group_name().' permissions granted',"created");
		}
		$adminGroup = DataObject::get_one("Group", "\"Code\" = '".EcommerceRole::get_admin_group_code()."' ");
		if(!$adminGroup) {
			$adminGroup = new Group();
			$adminGroup->Code = EcommerceRole::get_admin_group_code();
			$adminGroup->Title = EcommerceRole::get_admin_group_name();
			$adminGroup->write();
			Permission::grant( $adminGroup->ID, EcommerceRole::get_admin_permission_code());
			DB::alteration_message(EcommerceRole::get_admin_group_name().' Group created',"created");
		}
		elseif(DB::query("SELECT * FROM \"Permission\" WHERE \"GroupID\" = '".$adminGroup->ID."' AND \"Code\" LIKE '".EcommerceRole::get_admin_permission_code()."'")->numRecords() == 0 ) {
			Permission::grant($adminGroup->ID, EcommerceRole::get_admin_permission_code());
			DB::alteration_message(EcommerceRole::get_admin_group_name().' permissions granted',"created");
		}
		$permissionRole = DataObject::get_one("PermissionRole", "\"Title\" = '".EcommerceRole::get_admin_role_title()."'");
		if(!$permissionRole) {
			$permissionRole = new PermissionRole();
			$permissionRole->Title = EcommerceRole::get_admin_role_title();
			$permissionRole->OnlyAdminCanApply = true;
			$permissionRole->write();
		}
		if($permissionRole) {
			$permissionArray = EcommerceRole::get_admin_role_permission_codes();
			if(is_array($permissionArray) && count($permissionArray) && $permissionRole) {
				foreach($permissionArray as $permissionCode) {
					$permissionRoleCode = DataObject::get_one("PermissionRoleCode", "\"Code\" = '$permissionCode'");
					if(!$permissionRoleCode) {
						$permissionRoleCode = new PermissionRoleCode();
						$permissionRoleCode->Code = $permissionCode;
						$permissionRoleCode->RoleID = $permissionRole->ID;
						$permissionRoleCode->write();
					}
				}
			}
			if($adminGroup) {
				$existingGroups = $permissionRole->Groups();
				$existingGroups->add($adminGroup);
			}
		}
	}
}
