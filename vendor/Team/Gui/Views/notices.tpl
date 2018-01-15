{if ($_.notices.type)}

	{if ("ERROR" == $_.notices.type)} 
		{$notices_classes="{$error_classes|default:'alert alert-block alert-danger'}"}
		{$html_icon="{$html_icon_error|default:''}"}
	{else}
		{$notices_classes="{$success_classes|default:'alert alert-block alert-success'}"}
		{$html_icon="{$html_icon_success|default:''}"}
	{/if}

	<div class="{$notices_classes}">
		{$close_html}

		{$html_before|default:'<p>'}
		{$html_icon}
		{$_.notices.msg}
		{$html_after|default:'</p>'}

		{if ($_.notices.details)}
			<ul>
			{foreach $_.notices.details as $_code =>$_detail}
					<li>{$_detail}</li>
			{/foreach}
			</ul> 
		{/if}

	</div>	
   
{/if}


