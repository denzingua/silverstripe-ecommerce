<li class="productItem $FirstLast">
	<div class="productImage">
	<% if Image %>
		<a href="$Link"><img src="$Image.Thumbnail.URL" alt="<% sprintf(_t("Product.IMAGE","%s image"),$Title) %>" /></a>
	<% else %>
		<a href="$Link" class="noImage"><img src="$DefaultImageLink" alt="<% _t("Product.NOIMAGEAVAILABLE","no image available") %>"></a>
	<% end_if %>
	</div>
	<h3 class="productTitle"><a href="$Link">$Title</a></h3>
</li>
