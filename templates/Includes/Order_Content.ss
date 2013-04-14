<table id="InformationTable" class="infotable readonly">
	<thead>
		<tr>
			<th scope="col" class="left product title"><% _t("Order.PRODUCT","Product") %></th>
			<th scope="col" class="center quantity"><% _t("Order.QUANTITY", "Quantity") %></th>
			<th scope="col" class="right unitprice"><% _t("Order.PRICE","Price") %> ($CurrencyUsed.Code)</th>
			<th scope="col" class="right total"><% _t("Order.TOTALPRICE","Total Price") %> ($CurrencyUsed.Code)</th>
		</tr>
	</thead>
	<% if Items %>
	<tfoot>
		<tr class="gap total summary">
			<th colspan="3" scope="row" class="threeColHeader"><% _t("Order.TOTAL","Total") %></th>
			<td class="right total" id="$AJAXDefinitions.TableTotalID">
				<span class="value">$TotalAsMoney.NiceDefaultFormat</span>
				<% include Order_Content_DisplayPrice %>
			</td>
		</tr>
	</tfoot>
	<% end_if %>
	<tbody>
	<% if Items %>
		<% loop Items %>
		<tr  class="itemRow $EvenOdd $FirstLast">
			<td class="product title">
				<% if Link %>
					<a href="$Link" target="_blank">$TableTitle.XML</a>
				<% else %>
					<span class="tableTitle">$TableTitle.XML</span>
				<% end_if %>
				<span class="tableSubTitle">$TableSubTitle</span>
			</td>
			<td class="center quantity">$Quantity</td>
			<td class="right unitprice">$UnitPriceAsMoney.NiceDefaultFormat</td>
			<td class="right total">$CalculatedTotalAsMoney.NiceDefaultFormat</td>
		</tr>
		<% end_loop %>

		<tr class="gap summary" id="SubTotal">
			<th colspan="3" scope="row" class="threeColHeader subtotal"><% _t("Order.SUBTOTAL","Sub-total") %></th>
			<td class="right">$SubTotalAsMoney.NiceDefaultFormat</td>
		</tr>

		<% loop Modifiers %>
			<% if ShowInTable %>
		<tr class="modifierRow $EvenOdd $FirstLast $Classes <% if HideInAjaxUpdate %> hideForNow<% end_if %>">
			<td colspan="3" scope="row">$TableTitle</td>
			<td class="right total">$CalculatedTotalAsMoney.NiceDefaultFormat</td>
		</tr>
			<% end_if %>
		<% end_loop %>
	<% else %>
		<tr class="showOnZeroItems">
			<td colspan="4" class="center">
				<% _t("Order.NOITEMS","There are <strong>no</strong> items in your cart.") %>
			</td>
		</tr>
	<% end_if %>
	</tbody>
</table>
