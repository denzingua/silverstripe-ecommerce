<?php

/**
 * CartWidget displays the current contents of the user's cart.
 * @Author jemery
 */

class CartWidget extends Widget{

	static $title = "Shopping Cart";
	static $cmsTitle = "Shopping Cart";
	static $description = "Displays the current contents of the user's cart.";

	function Cart(){
		return ShoppingCart::current_order();
	}

}
