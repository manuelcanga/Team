{html lang="es"}
    {place name='metas'}

    {head}

    {title}{$SEO_TITLE}{/title}

    {place name='header'}
{body}

    {if (!$without_header)}
        {wrapper class="header"}
            {header id="body-header"}
                 {place name='template_header'}
            {/header}
        {/wrapper}
    {/if}



    {place name='template_top_content'}


    {if ( $without_content && $view)}
        {place name='main_view'}
    {else}
        {wrapper class="content"}
            <main class="{$type} {#RESPONSE#}"  id="{#COMPONENT#}">
                {place name='main_view'}
            </main>
        {/wrapper}
    {/if}

    {place name='template_bottom_content'}


    {if (!$without_footer)}
        {wrapper class="footer"}
           {footer class="main"}
              {place name='template_footer'}
           {/footer}
        {/wrapper}
    {/if}

{/body}
{/html}

