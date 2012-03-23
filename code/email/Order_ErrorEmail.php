<?php

/**
 * @Description: This class handles the error email which can be sent
 * to the admin only if something untowards is happening.
 *
 * At present, this class is used to send any email that goes to admin only.
 *
 * @authors: Nicolaas
 *
 * @package: ecommerce
 * @sub-package: email
 *
 **/

class Order_ErrorEmail extends Order_Email {

	protected $ss_template = 'Order_ErrorEmail';

}
