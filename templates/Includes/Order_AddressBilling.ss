<% if BillingAddress %>
	<% control BillingAddress %>
<address class="addressSection" cellspacing="0" cellpadding="0" id="BillingAddressSection">
	$FirstName $Surname<br />
	<% if Address %>$Address<br/><% end_if %>
	<% if Address2 %>$Address2<br /><% end_if %>
	<% if City %>$City<br /><% end_if %>
	<% if State %>$State<br /><% end_if %>
	<% if PostalCode %>$PostalCode<br /><% end_if %>
	<% if FullCountryName %>$FullCountryName<br /><% end_if %>
	<% if Phone %>$Phone<br /><% end_if %>
	<% if Email %>$Email<br /><% end_if %>
</address>
	<% end_control %>
<% else %>
<p>No billing address available.</p>
<% end_if %>
