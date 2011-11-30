<h3 class="orderInfo"><% _t("Order.ORDERINFORMATION","Order Information") %></h3>
<% include Order_ConfirmCountry %>
<table id="InformationTable" class="editable" cellspacing="0" cellpadding="0" summary="<% _t("Order.TABLESUMMARY","The contents of your cart are displayed in this form and summary of all fees associated with an order and a rundown of payments options.") %>">
	<thead>
		<tr>
			<th scope="col" class="left"><% _t("Order.PRODUCT","Product") %></th>
			<th scope="col" class="center"><% _t("Order.QUANTITY", "Quantity") %></th>
			<th scope="col" class="right"><% _t("Order.PRICE","Price") %> ($Currency)</th>
			<th scope="col" class="right"><% _t("Order.TOTALPRICE","Total Price") %> ($Currency)</th>
			<th scope="col" class="right"></th>
		</tr>
	</thead>
	<tfoot>
<% if CustomerViewableOrderStatusLogs %>
	<% control CustomerViewableOrderStatusLogs %>
		<tr>
			<th class="left" scope="row">$Title</th>
			<td class="left" colspan="4"><% if CustomerNote %>$CustomerNote<% else %>no further information<% end_if %></td>
		</tr>
	<% end_control %>
<% end_if %>
		<tr class="cartMessage">
			<td colspan="5" scope="row" class="center $CartStatusClass" id="$TableMessageID">$CartStatusMessage</td>
		</tr>
		<tr class="showOnZeroItems"<% if Items %> style="display: none"<% end_if %>>
			<td colspan="5" scope="row" class="center"><% _t("Order.NOITEMS","There are <strong>no</strong> items in your cart.") %></td>
		</tr>
	</tfoot>
	<tbody>
		<% if Items %>
			<% control Items %>
				<% if ShowInTable %>
		<tr id="$TableID" class="$Classes hideOnZeroItems">
			<td<% if Link %><% else %> id="$TableTitleID"<% end_if %> class="product title" scope="row">
				<% if Link %>
					<a id="$TableTitleID" href="$Link" title="<% sprintf(_t("Order.READMORE","Click here to read more on &quot;%s&quot;"),$TableTitle) %>">$TableTitle</a>
				<% else %>
					$TableTitle
				<% end_if %>
				<% if TableSubTitle %><div class="tableSubTitle">$TableSubTitle</div ><% end_if %>
			</td>
			<td class="center quantity">
				$QuantityField
			</td>
			<td class="right unitprice">$UnitPrice.Nice</td>
			<td class="right total" id="$TableTotalID">$Total.Nice</td>
			<td class="right remove">
				<strong>
					<a class="ajaxQuantityLink" href="$removeallLink" title="<% sprintf(_t("Order.REMOVEALL","Remove all of &quot;%s&quot; from your cart"),$TableTitle) %>">
						<img src="ecommerce/images/remove.gif" alt="x"/>
					</a>
				</strong>
			</td>
		</tr>
				<% end_if %>
			<% end_control %>

		<tr class="gap summary hideOnZeroItems">
			<th colspan="3" scope="row"><% _t("Order.SUBTOTAL","Sub-total") %></th>
			<td class="right" id="$TableSubTotalID">$SubTotal.Nice</td>
			<td>&nbsp;</td>
		</tr>

			<% if Modifiers %>
			<% control Modifiers %>
				<% if ShowInTable %>
		<tr id="$TableID" class="$Classes hideOnZeroItems<% if HideInAjaxUpdate %> hideForNow<% end_if %>">
			<td <% if Link %><% else %> id="$TableTitleID"<% end_if %> colspan="3" scope="row">
				<% if Link %>
					<a id="$TableTitleID" href="$Link" title="<% sprintf(_t("Order.READMORE","Click here to read more on &quot;%s&quot;"),$TableTitle) %>">$TableTitle</a>
				<% else %>
					$TableTitle
				<% end_if %>
				<% if TableSubTitle %><div class="tableSubTitle">$TableSubTitle</div ><% end_if %>
			</td>
			<td class="right total" id="$TableTotalID">$TableValue.Nice</td>
			<td class="right remove">
				<% if CanBeRemoved %>
					<strong>
						<a class="ajaxQuantityLink" href="$RemoveLink" title="<% sprintf(_t("Order.REMOVE","Remove &quot;%s&quot; from your order"),$TableTitle) %>">
							<img src="ecommerce/images/remove.gif" alt="x"/>
						</a>
					</strong>
				<% end_if %>
			</td>
		</tr>
				<% end_if %>
			<% end_control %>
			<% end_if %>
		<tr class="gap total summary hideOnZeroItems">
			<th colspan="3" scope="row"><% _t("Order.TOTAL","Total") %></th>
			<td class="right total" id="$TableTotalID">$Total.Nice $Currency</td>
			<td>&nbsp;</td>
		</tr>
		<% end_if %>
	</tbody>
</table>

<% include Order_OrderStatusLogs_PreSubmit %>

<% include ShoppingCartRequirements %>
