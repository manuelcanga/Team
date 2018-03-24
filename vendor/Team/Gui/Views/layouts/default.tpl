{head lang="es"}
    {place name='metas'}

    {title}{#SEO_TITLE#}{/title}

    {place name='header'}

{/head}
{body}
    {place name='template_body_top'}

    {if (!$without_header)}
        {wrapper class="header" wrapper='template_header'}
            {place name='template_header_top'}

            {header class='main'}
                 {place name='template_header'}
            {/header}

            {place name='template_header_bottom'}
        {/wrapper}
    {/if}


	{wrapper class='content' wrapper='template_content'}
		{place name='template_top_page'}


		{if ( $without_content)}
            {view}
		{else}
		    {wrapper class="content" wrapper='template_view'}
		        {main class="{$type} {#RESPONSE#}"  id="{#COMPONENT#}"}
                    {place name='template_top_view'}

                    {view}

                    {place name='template_bottom_view'}
		        {/main}
		    {/wrapper}
		{/if}

		{place name='template_bottom_content'}
    {/wrapper}


    {if (!$without_footer)}
        {wrapper class="footer"  wrapper='template_footer'}
            {place name='template_footer_top'}

            {footer class="main"}
              {place name='template_footer'}
           {/footer}

            {place name='template_footer_bottom'}
        {/wrapper}
    {/if}

    {place name='template_body_bottom'}
{/body}