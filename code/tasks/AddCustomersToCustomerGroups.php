<?php



class AddCustomersToCustomerGroups extends BuildTask {

	protected $title = "Add Customers to Customer Group";

	protected $description = "Takes all the members that have ordered something and adds them to the customer group.";

	function run($request) {
		$customerGroup = EcommerceRole::get_customer_group();;
		if($customerGroup) {
			$allCombos = DB::query("
				SELECT \"Group_Members\".\"ID\", \"Group_Members\".\"MemberID\", \"Group_Members\".\"GroupID\"
				FROM \"Group_Members\"
				WHERE \"Group_Members\".\"GroupID\" = ".$customerGroup->ID.";"
			);
			//make an array of all combos
			$alreadyAdded = array();
			$alreadyAdded[-1] = -1;
			if($allCombos) {
				foreach($allCombos as $combo) {
					$alreadyAdded[$combo["MemberID"]] = $combo["MemberID"];
				}
			}
			$unlistedMembers = DataObject::get(
				"Member",
				$where = "\"Member\".\"ID\" NOT IN (".implode(",",$alreadyAdded).")",
				$sort = "",
				$join = "INNER JOIN \"Order\" ON \"Order\".\"MemberID\" = \"Member\".\"ID\""
			);

			//add combos
			if($unlistedMembers) {
				$existingMembers = $customerGroup->Members();
				foreach($unlistedMembers as $member) {
					$existingMembers->add($member);
					DB::alteration_message("Added member to customers: ".$member->Email, "created");
				}
			}
		}
		else {
			DB::alteration_message("NO customer group found", "deleted");
		}
	}


}
