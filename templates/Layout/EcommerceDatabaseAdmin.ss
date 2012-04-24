


<% if OverallConfig %>
<h3>Check your settings!</h3>
<p>Check your settings whenever you are working on this site to make sure they are all up-to-date and valid.</p>
<ul>
<% control OverallConfig %>
	<li><a href="$Link">$Title</a>: $Description</li>
<% end_control %>
</ul>
<% end_if %>

<% if EcommerceSetup %>
<h3>E-commerce Setup</h3>
<p>These actions are generally only run once to setup e-commerce.</p>
<ul>
<% control EcommerceSetup %>
	<li><a href="$Link">$Title</a>: $Description</li>
<% end_control %>
</ul>
<% end_if %>

<% if RegularMaintenance %>
<h3>Regular Maintenance</h3>
<p>These actions should be run as cron job or you should configure e-commerce in such a way that they run regularly.</p>
<ul>
<% control RegularMaintenance %>
	<li><a href="$Link">$Title</a>: $Description</li>
<% end_control %>
</ul>
<% end_if %>

<% if DebugActions %>
<h3>Debug</h3>
<p>Use the options listed below to debug your e-commerce application.</p>
<ul>
<% control DebugActions %>
	<li><a href="$Link">$Title</a>: $Description</li>
<% end_control %>
</ul>
<% end_if %>



<% if DataCleanups %>
<h3>Data Cleaning</h3>
<ul>
<% control DataCleanups %>
	<li><a href="$Link">$Title</a>: $Description</li>
<% end_control %>
</ul>
<% end_if %>



<% if Migrations %>
<h3>Migration</h3>
<p>Use the options listed below whenever your upgrade your source code.</p>
<ul>
<% control Migrations %>
	<li><a href="$Link">$Title</a>: $Description</li>
<% end_control %>
</ul>
<% end_if %>

<% if CrazyShit %>
<h3>You are MAD!</h3>
<p>Start again?</p>
<ul>
<% control CrazyShit %>
	<li><a href="$Link">$Title</a>: $Description</li>
<% end_control %>
</ul>
<% end_if %>

<% if Tests %>
<h3>Ecommerce Unit Tests</h3>
<ul>
	<li><a href="{$BaseHref}dev/tests/$AllTests"><strong>Run all ecommerce unit tests</strong></a></li>
<% control Tests %>
	<li><a href="{$BaseHref}dev/tests/$Class">$Name</a></li>
<% end_control %>
</ul>
<% end_if %>
