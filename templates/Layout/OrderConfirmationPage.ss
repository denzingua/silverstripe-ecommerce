<div id="OrderConformation">

	<h1 class="pagetitle">$Title</h1>

	<% if Content %><div id="ContentHolder">$Content</div><% end_if %>


<% if Order %>
	<% control Order %>
		<% include Order %>
	<% end_control %>
	<div id="PaymentForm">$PaymentForm</div>
	<div id="CancelForm">$CancelForm</div>
<% else %>
	<p class="message bad"><% _t("OrderConfirmationPage.COULDNOTBEFOUND","Your order could not be found.") %></p>
<% end_if %>

	<h3><% _t("OrderConfirmation.NEXT", "Next") %></h3>
	<% include CartActionsAndMessages %>

</div>

