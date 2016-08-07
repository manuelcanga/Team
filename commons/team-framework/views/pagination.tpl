<div {if ($class)}class="{$class}"{/if} {if ($id)}id="{$id}"{/if}>
	{if (false !== $pagination.goFirst)}
		<div class="go_first"><a href="{$pagination.goFirst}">{$pagination_first|default:'&lt;&lt;'}</a></div>
		{$pagination_first_separator|default:''}
	{/if}
	{if (false !== $pagination.goPrev)}
		<div class="go_prev"><a href="{$pagination.goPrev}" class="go_prev">{$pagination_prev|default:'&lt;'}</a></div>
	{/if}

	<{$pagination_list|default:'ul'} class="{if (false !== $pagination.goFirst)}goFirst{/if} {if (false !== $pagination.goEnd)}goEnd{/if}">
		{foreach $pagination $_page}
		        <li class="{$_page.classes}"><a href="{$_page.url}">{$_page}</a></li>
		{/foreach}
	</{$pagination_list|default:'ul'}>

	{if (false !== $pagination.goNext)}
		<div class="go_next"><a href="{$pagination.goNext}">{$pagination_next|default:'&gt;'}</a></div>
	{/if}


	{if (false !== $pagination.goEnd)}
		{$pagination_end_separator|default:''}
		<div class="go_end"><a href="{$pagination.goEnd}">{$pagination_end|default:'&gt;&gt;'}</a></div>
	{/if}

</div>
