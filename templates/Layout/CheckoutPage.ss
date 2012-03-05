<div id="Checkout">

	<h1 class="pagetitle">$Title</h1>

	<% if HasCheckoutSteps %><% if Steps %>
	<ul class="steps">
	<% control Steps %>
		<li class="$LinkingMode"><a href="$Link">$Title</a></li>
	<% end_control %>
	</ul>
	<% end_if %><% end_if %>

	<% include CartActionsAndMessages %>



<% if CanCheckout %>

	<!-- step 1 OrderItems -->
	<% if CanShowStep(orderitems) %>
	<div id="OrderItemsOuter" class="checkoutStep">

		<% if StepsContentHeading(1) %><h2 class="orderStepHeading">$StepsContentHeading(1)</h2><% end_if %>
		<% if StepsContentAbove(1) %><div class="above headerFooterDescription">$StepsContentAbove(1)</div><% end_if %>
		<% control Order %><% include Order_Content_Editable %><% end_control %>
		<% if ModifierForms %><% control ModifierForms %><div class="modifierFormInner">$Me</div><% end_control %><% end_if %>
		<% if StepsContentBelow(1) %><div class="below headerFooterDescription">$StepsContentBelow(1)</div><% end_if %>

		<% if HasCheckoutSteps %>
		<div class="checkoutStepPrevNextHolder next">
			<a href="{$Link}checkoutstep/orderformaddress/#OrderFormAddressOuter"><% _t("CONTINUE","Continue") %></a>
		</div>
		<% end_if %>

	</div>
	<% end_if %>


	<!-- step 2 OrderFormAddress -->
	<% if CanShowStep(orderformaddress) %>
	<div id="OrderFormAddressOuter" class="checkoutStep">

		<% if HasCheckoutSteps %>
		<div class="checkoutStepPrevNextHolder prev">
			<a href="{$Link}checkoutstep/orderitems/#OrderItemsOuter"><% _t("GOBACK","go back") %></a>
		</div>
		<% end_if %>

		<% if StepsContentHeading(2) %><h2 class="orderStepHeading">$StepsContentHeading(2)</h2><% end_if %>
		<% if StepsContentAbove(2) %><div class="above headerFooterDescription">$StepsContentAbove(2)</div><% end_if %>
		<div id="OrderFormAddressHolder">$OrderFormAddress</div>
		<% if StepsContentBelow(2) %><div class="below headerFooterDescription">$StepsContentBelow(2)</div><% end_if %>

	<!-- there is no next link here, because the member will have to submit the form -->
	</div>
	<% end_if %>



	<!-- add your own steps here... -->
	<!-- add your own steps here... -->
	<!-- add your own steps here... -->
	<!-- add your own steps here... -->


	<!-- step 3 Order confirmation and payment - ALWAYS the final step -->

	<% if IsFinalStep %>
	<div id="OrderConfirmationAndPayment" class="checkoutStep">

		<% if HasCheckoutSteps %>
		<div class="checkoutStepPrevNextHolder prev">
			<a href="{$Link}checkoutstep/orderformaddress/#OrderFormAddressOuter"><% _t("GOBACK","go back") %></a>
		</div>
		<% else %>
		<div class="checkoutStepPrevNextHolder prev">
			<a href="{$Link}"><% _t("GOBACK","go back") %></a>
		</div>
		<% end_if %>


		<% if StepsContentHeading(3) %><h2 class="orderStepHeading">$StepsContentHeading(3)</h2><% end_if %>
		<% if StepsContentAbove(3) %><div class="above headerFooterDescription">$StepsContentAbove(3)</div><% end_if %>
		<% control Order %><% include Order_Content %><% end_control %>
		<div id="OrderFormHolder">$OrderForm</div>
		<% if StepsContentBelow(3) %><div class="below headerFooterDescription">$StepsContentBelow(3)</div><% end_if %>

	</div>

	<% end_if %>


<% end_if %>
	<% if Content %><div id="ContentHolder">$Content</div><% end_if %>
</div>
