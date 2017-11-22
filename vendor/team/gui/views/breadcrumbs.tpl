<section id="breadcrumbs" >
<ol typeof="BreadcrumbList" vocab="http://schema.org/">
    <li typeof="ListItem" property="itemListElement">
        <a href="/" typeof="WebPage" property="item" title="El Septimo Arte">
            <span property="name">{$breadcrumbs_home|default:'Home'}</span>
        </a>
        <meta content="1" property="position">
    </li>
    {$i=1}
    {foreach #BREADCRUMB# as $_crumb}
        {$i=$i+1}
        <li typeof="ListItem" property="itemListElement">
           {$breadcrumbs_separator|default:'/'}
		   {if ($_crumb.url != '')}<a href="{$_crumb.url}" typeof="WebPage" property="item">{/if}
                <span property="name">{$_crumb.name}</span>
			{if ($_crumb.url != '')}</a>{/if}
            <meta content="{$i}" property="position">
        </li>
    {/foreach}
</ol>
</section>
