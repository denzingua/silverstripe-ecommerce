<div id="AccountPage">
	<% if Message %><p id="AccountPageMessage" class="message">$Message</p><% end_if %>
	<% if OrderConfirmationPageLink %><p id="OrderConfirmationPageLink"><a href="$OrderConfirmationPageLink">view past orders</a></p>
	<div id="MemberForm" class="typography">$MemberForm</div>
	<% if Content %><div id="ContentHolder">$Content</div><% end_if %>
</div>



