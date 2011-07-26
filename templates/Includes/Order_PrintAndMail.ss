	<% if EmailLink %>
	<div id="SendCopyOfReceipt">
		<p>
			<a href="$EmailLink">
				<% sprintf(_t("OrderConfirmation.SENDCOPYRECEIPT","send a copy of receipt to %s"),$Member.Email) %>
			</a>
		</p>
	</div>
	<% end_if %>
	
	<% if PrintLink %>
	<div id="SendCopyOfReceipt">
		<p>
			<a href="$PrintLink" onclick="javascript: window.open(this.href, \'print_order\', \'toolbar=0,scrollbars=1,location=1,statusbar=0,menubar=0,resizable=1,width=800,height=600,left = 50,top = 50\'); return false;">
				<% _t("OrderConfirmation.PRINTINVOICE","print invoice") %>
			</a>
		</p>
	</div>
	<% end_if %>
