<% if Cart %><% control Cart %>
<div id="ShoppingCart">
	<h3 id="CartHeader"><% _t("Cart.HEADLINE","My Cart") %></h3>
<% if Items %>
	<table id="InformationTable" class="editable" cellspacing="0" cellpadding="0" summary="<% _t("TABLESUMMARY","The contents of your cart are displayed in this form and summary of all fees associated with an order and a rundown of payments options.") %>">
		<tbody>
	<% control Items %>
		<% if ShowInTable %>
			<tr id="$TableID" class="$Classes hideOnZeroItems">
				<td class="product title" scope="row">
					<% if Link %>
						<a id="$TableTitleID" href="$Link">$TableTitle</a>
					<% else %>
						<span id="$TableTitleID">$TableTitle</span>
					<% end_if %>
					<% if TableSubTitle %><div class="tableSubTitle">$TableSubTitle</div ><% end_if %>
				</td>
				<td class="center quantity">
					$QuantityField
				</td>
				<td class="right total" id="$TableTotalID">$Total.Nice</td>
			</tr>
		<% end_if %>
	<% end_control %>
			<tr class="gap summary hideOnZeroItems">
				<td colspan="2" scope="row"><% _t("Cart.SUBTOTAL","Sub-total") %></td>
				<td class="right" id="$TableSubTotalID">$SubTotal.Nice</td>
			</tr>
			<tr class="showOnZeroItems"<% if Items %> style="display: none"<% end_if %>>
				<td colspan="3" scope="row" class="center"><% _t("Cart.NOITEMS","There are no items in your cart") %></td>
			</tr>
		</tbody>
	</table>
	<p class="goToCart"><a href="$checkoutLink"><% _t("Product.GOTOCHECKOUTLINK","&raquo; Go to the checkout") %></a></p>
<% else %>
		<p class="noItems"><% _t("Cart.NOITEMS","There are no items in your cart") %>.</p>
<% end_if %>
</div>
<% end_control %><% end_if %>



<% include ShoppingCartRequirements %>
