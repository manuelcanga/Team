{head lang='en'}
    {title}Full Page{/title}
{/head}
{body}
    {main}
        {header}
        {/header}

        {wrapper id='site_content'}
            {wrapper id='content'}
                <h1>This Looks great!, doesn't it?</h1>
                <p><strong>[files:/demo01/example03/Gui.php(  index response ), /demo01/example03/views/index.tpl, /demo01/example03/css/styles.css]</strong></p>
                <p>In this example, we use {highlight}$this->addCss{/highlight} in GUI in order to give styles to our web.<br />
                We also use new wrappers Smarty:{literal} {main}, {header}, {footer} and even a generic {wrapper} {/literal}</p>
             {/wrapper}
        {/wrapper}

        {footer}
            <p><a href='/example03/with_includes/' class="example">next example with includes</a></p>
        {/footer}

    {/main}

{/body}

