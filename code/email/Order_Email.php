<?php


/**
 * @Description: Email spefically for communicating with customer about order.
 *
 *
 * @authors: Nicolaas [at] Sunny Side Up .co.nz
 * @package: ecommerce
 * @sub-package: email
 * @inspiration: Silverstripe Ltd, Jeremy
 **/

class Order_Email extends Email {

	/**
	 * returns the standard from email address (e.g. the shop admin email address)
	 * @return String
	 */
	static function get_from_email() {
		$ecommerceConfig = EcommerceDBConfig::current_ecommerce_db_config();
		if($ecommerceConfig && $ecommerceConfig->ReceiptEmail) {
			return $ecommerceConfig->ReceiptEmail;
		}
		else {
			return Email::getAdminEmail();
		}
	}

	/**
	 * returns the subject for the email (doh!).
	 * @return String
	 */
	static function get_subject() {
		$siteConfig = SiteConfig::current_site_config();
		if($siteConfig && $siteConfig->Title) {
			return _t("Order_Email.SALEUPDATE", "Sale Update [OrderNumber] from ").$siteConfig->Title;
		}
		else {
			return _t("Order_Email.SALEUPDATE", "Sale Update [OrderNumber] ");
		}
	}

	/**
	 *
	 * @param Null|String $messageID - ID for the message, you can leave this blank
	 * @param Order $order - the order to which the email relates
	 * @param Boolean $resend - should the email be resent even if it has been sent already?
	 * @return Boolean - TRUE for success and FALSE for failure.
	 */
	public function send($messageID = null, $order, $resend = false) {
		if(!$this->subject) {
			$this->subject = self::get_subject();
		}
		$this->subject = str_replace("[OrderNumber]", $order->ID, $this->subject);
		if(!$this->hasBeenSent($order) || $resend) {
			if(EcommerceConfig::get("Order_Email", "copy_to_admin_for_all_emails") && ($this->to != Email::getAdminEmail())) {
				$this->setBcc(Email::getAdminEmail());
			}
			if(EcommerceConfig::get("Order_Email", "send_all_emails_plain")) {
				$result = parent::sendPlain($messageID);
			}
			else {
				$result = parent::send($messageID);
			}
			$this->createRecord($result, $order);
			return $result;
		}
	}


	/**
	 * @param Boolean $result: how did the email go? 1 = sent, 0 = not sent
	 * @param Order $order: the order to which the email is associated.
	 * @return DataObject (OrderEmailRecord)
	 **/
	protected function createRecord($result, $order) {
		$obj = new OrderEmailRecord();
		$obj->From = $this->emailToVarchar($this->from);
		$obj->To = $this->emailToVarchar($this->to);
		$obj->Subject = $this->subject;
		$obj->Content = $this->body;
		$obj->Result = $result ? 1 : 0;
		$obj->OrderID = $order->ID;
		$obj->OrderStepID = $order->StatusID;
		if(Email::$send_all_emails_to) {
			$obj->To .= Email::$send_all_emails_to;
		}
		$obj->write();
		return $obj;
	}

	/**
	 * converts an Email to A Varchar
	 * @param String $email - emal address
	 * @return String - returns email address without &gt; and &lt;
	 */
	function emailToVarchar($email) {
		$email = str_replace(array("<", ">", '"', "'"), " - ", $email);
		return $email;
	}

	/**
	 * Checks if an email has been sent for this Order for this status.
	 * @param Order $order
	 * @return boolean
	 **/
	function hasBeenSent($order) {
		if(DataObject::get_one("OrderEmailRecord", "\"OrderEmailRecord\".\"OrderID\" = ".$order->ID." AND \"OrderEmailRecord\".\"OrderStepID\" = ".intval($order->StatusID)." AND  \"OrderEmailRecord\".\"Result\" = 1")) {
			return true;
		}
		return false;
	}

	/**
	 * moves CSS to inline CSS in email
	 * @param Boolean $isPlain - should we send the email as HTML or as TEXT
	 * @author Mark Guinn
	 */
	protected function parseVariables($isPlain = false) {
		if(!class_exists('Emogrifier')) {
			require_once(Director::baseFolder() . '/ecommerce/thirdparty/Emogrifier.php');
		}
		parent::parseVariables($isPlain);
		// if it's an html email, filter it through emogrifier
		$cssFileLocation = Director::baseFolder()."/".EcommerceConfig::get("Order_Email", "css_file_location");
		$cssFileHandler = fopen($cssFileLocation, 'r');
		$css = fread($cssFileHandler,  filesize($cssFileLocation));
		fclose($cssFileHandler);
		$emog = new Emogrifier($this->body, $css);
		$this->body = $emog->emogrify();
	}

	/**
	 * returns the instance of EcommerceDBConfig
	 *
	 * @return EcommerceDBConfig
	 **/
	public function EcomConfig(){
		return EcommerceDBConfig::current_ecommerce_db_config();
	}

	/**
	 * Debug helper method.
	 * Can be called from /shoppingcart/debug/
	 * @return String
	 */
	public function debug() {
		$html =  "
			<h2>".$this->ClassName."</h2><ul>";
		$fields = Object::get_static($this->ClassName, "db");
		foreach($fields as  $key => $type) {
			$html .= "<li><b>$key ($type):</b> ".$this->$key."</li>";
		}
		$fields = Object::get_static($this->ClassName, "casting");
		foreach($fields as  $key => $type) {
			$method = "get".$key;
			$html .= "<li><b>$key ($type):</b> ".$this->$method()." </li>";
		}
		$html .= "</ul>";
		return $html;
	}
}
