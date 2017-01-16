<div id="menubar">
        <ul id="menu">
          <li {if (#RESPONSE# == 'index')}class="selected"{/if}><a href="/{#COMPONENT#}/">Home</a></li>
          <li {if (#RESPONSE# == 'news')}class="selected"{/if}><a href="/{#COMPONENT#}/news">News</a></li>
          <li {if (#RESPONSE# == 'about')}class="selected"{/if}><a href="/{#COMPONENT#}/about">About</a></li>
        </ul>
</div>