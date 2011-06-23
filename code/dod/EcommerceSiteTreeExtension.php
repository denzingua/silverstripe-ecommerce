<?php

/**
 *@description: adds a few functions to SiteTree to give each page some e-commerce related functionality.
 *
 *
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 * @package ecommerce
 * @sub-package integration
 *
 **/


class EcommerceSiteTreeExtension extends DataObjectDecorator {

	/**
	 *@return Boolean
	 **/
	function ShopClosed() {
		$siteConfig = DataObject::get_one("SiteConfig");
		return $siteConfig->ShopClosed;
	}

	/**
	 *@return Order
	 **/
	function Cart() {
		return ShoppingCart::current_order();
	}

	/**
	 *@return Integer
	 **/
	public function NumItemsInCart() {
		$order = ShoppingCart::current_order();
		if($order) {
			return $order->TotalItems();
		}
		return 0;
	}

	public function contentcontrollerInit() {
		Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
	}

}

class EcommerceSiteTreeExtension_Controller extends Extension {

	/**
	 *@return string
	 **/
	function SimpleCartLinkAjax() {
		return ShoppingCart_Controller::get_url_segment()."/showcart/";
	}

	/**
	 *@return Boolean
	 **/
	public function MoreThanOneItemInCart() {
		return $this->NumItemsInCart() > 1;
	}

	/**
	 *@return Float
	 **/
	public function SubTotalCartValue() {
		$order = ShoppingCart::current_order();
		return $order->SubTotal;
	}

	/**
	 *@return String (URLSegment)
	 **/
	public function AccountPageLink() {
		return AccountPage::find_link();
	}

	/**
	 *@return String (URLSegment)
	 **/
	public function CheckoutLink() {
		return CheckoutPage::find_link();
	}
	/**
	 *@return String (URLSegment)
	 **/
	public function CartPage() {
		return CartPage::find_link();
	}

	/**
	 *@return String (URLSegment)
	 **/
	public function OrderConfirmationPage() {
		return OrderConfirmationPage::find_link();
	}

}
