<% include Order_ConfirmCountry %>

<table id="InformationTable" class="editable" cellspacing="0" cellpadding="0" summary="<% _t("Order.TABLESUMMARY","The contents of your cart are displayed in this form and summary of all fees associated with an order and a rundown of payments options.") %>">
	<thead>
		<tr>
			<th scope="col" class="left"><% _t("Order.PRODUCT","Product") %></th>
			<th scope="col" class="center"><% _t("Order.QUANTITY", "Quantity") %></th>
			<th scope="col" class="right"><% _t("Order.PRICE","Price") %><% if EcomConfig.Currency %> ($EcomConfig.Currency)<% end_if %></th>
			<th scope="col" class="right"><% _t("Order.TOTALPRICE","Total Price") %><% if EcomConfig.Currency %> ($EcomConfig.Currency)<% end_if %></th>
			<th scope="col" class="right"></th>
		</tr>
	</thead>
	<tfoot>
<% if Items %>
		<tr class="gap total summary hideOnZeroItems">
			<th colspan="3" scope="row"><% _t("Order.TOTAL","Total") %></th>
			<td class="right total" id="$AJAXDefinitions.TableTotalID">
				<span class="value">$Total.Nice</span>
				<% include Order_Content_DisplayPrice %>
			</td>
			<td>&nbsp;</td>
		</tr>
<% end_if %>
<% if CustomerViewableOrderStatusLogs %>
	<% loop CustomerViewableOrderStatusLogs %>
		<tr>
			<th class="left" scope="row">$Title</th>
			<td class="left" colspan="4"><% if CustomerNote %>$CustomerNote<% else %>no further information<% end_if %></td>
		</tr>
	<% end_loop %>
<% end_if %>
		<tr class="cartMessage">
			<td colspan="5" scope="row" class="center $CartStatusClass" id="$AJAXDefinitions.TableMessageID">$CartStatusMessage</td>
		</tr>
		<tr class="showOnZeroItems"<% if Items %> style="display: none"<% end_if %>>
			<td colspan="5" scope="row" class="center">
				$DisplayPage.NoItemsInOrderMessage
			</td>
		</tr>
	</tfoot>
	<tbody>
<% if Items %>
	<% loop Items %>
		<% if ShowInTable %>
			<% include Order_Content_Editable_OrderItemRow %>
		<% end_if %>
	<% end_loop %>

		<tr class="gap summary hideOnZeroItems">
			<th colspan="3" scope="row"><% _t("Order.SUBTOTAL","Sub-total") %></th>
			<td class="right" id="$AJAXDefinitions.TableSubTotalID">$SubTotal.Nice</td>
			<td>&nbsp;</td>
		</tr>

	<% if Modifiers %>
		<% loop Modifiers %>
			<% if ShowInTable %>
			<% include Order_Content_Editable_ModifierRow %>
			<% end_if %>
		<% end_loop %>
	<% end_if %>
<% end_if %>
	</tbody>
</table>


<% include ShoppingCartRequirements %>
