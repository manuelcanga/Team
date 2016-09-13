{html lang="es"}
    {place name='metas'}

    {head}

    {title}{$SEO_TITLE}{/title}

    {place name='header'}
{body}
    {place name='template_body_top'}

    {if (!$without_header)}
        {wrapper class="header"}
            {place name='template_header_top'}

            {header class='main'}
                 {place name='template_header'}
            {/header}

            {place name='template_header_bottom'}
        {/wrapper}
    {/if}


	{wrapper class='content'}
		{place name='template_top_content'}


		{if ( $without_content && $view)}
		    {place name='main_view'}
		{else}
		    {wrapper class="content"}
		        <main class="{$type} {#RESPONSE#}"  id="{#COMPONENT#}">
                    {place name='template_top_main'}

                    {place name='main_view'}

                    {place name='template_bottom_main'}
		        </main>
		    {/wrapper}
		{/if}

		{place name='template_bottom_content'}
    {/wrapper}


    {if (!$without_footer)}
        {wrapper class="footer"}
            {place name='template_footer_top'}

            {footer class="main"}
              {place name='template_footer'}
           {/footer}

            {place name='template_footer_bottom'}
        {/wrapper}
    {/if}

    {place name='template_body_bottom'}
{/body}
{/html}

