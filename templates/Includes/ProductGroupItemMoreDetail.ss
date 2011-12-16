<div class="productImage">
<% if Image %>
	<a href="$Link"><img src="$Image.Thumbnail.URL" alt="<% sprintf(_t("Product.IMAGE","%s image"),$Title) %>" /></a>
<% else %>
	<a href="$Link" class="noImage"><img src="$DefaultImageLink" alt="<% _t("Product.NOIMAGEAVAILABLE","no image available") %>"></a>
<% end_if %>
</div>
<div class="limtedContentHolder">$Content.Summary</div>
<% include ProductActions %>
<p class="moreInformation"><a href="$Link">more info ...</a></p>
