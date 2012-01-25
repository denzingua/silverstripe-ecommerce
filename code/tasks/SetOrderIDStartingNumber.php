<?php

class SetOrderIDStartingNumber extends BuildTask{

	protected $title = "Set Order ID Starting Number";

	protected $description = "sets the starting order number with all order numbers following this number.";

	function run($request){

		//set starting order number ID
		$number = intval(Order::get_order_id_start_number());
		$currentMax = 0;
		//set order ID
		if($number) {
			$count = DB::query("SELECT COUNT( \"ID\" ) FROM \"Order\" ")->value();
		 	if($count > 0) {
				$currentMax = DB::Query("SELECT MAX( \"ID\" ) FROM \"Order\"")->value();
			}
			if($number > $currentMax) {
				DB::query("ALTER TABLE \"Order\"  AUTO_INCREMENT = $number ROW_FORMAT = DYNAMIC ");
				DB::alteration_message("Change OrderID start number to ".$number, "created");
			}
			else {
				DB::alteration_message("Can not set OrderID start number to ".$number." because this number has already been used.", "deleted");
			}
		}
		else {
			DB::alteration_message("Starting OrderID has not been set.", "deleted");
		}

	}

}
