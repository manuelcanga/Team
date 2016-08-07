{literal}
<style type="text/css">
#team_debug {background-color: #fff; color: #222; font-family: sans-serif;}
#team_debug pre {margin: 0; font-family: monospace;}
#team_debug a:link {color: #009; text-decoration: none; background-color: #fff;}
#team_debug a:hover {text-decoration: underline;}
#team_debug.center {text-align: center;}
#team_debug.center table { text-align: left; width: 100%}
#team_debug.center  > table {margin: 1em auto; border-collapse: collapse; border: 0;max-width: 934px; width: 100%;  box-shadow: 1px 2px 3px #ccc;}
#team_debug.center th {text-align: center !important;}
#team_debug td,#team_debug th {border: 1px solid #666; font-size: 75%; vertical-align: baseline; padding: 4px 5px;}
#team_debug h1 {font-size: 150%; text-align: center; padding: 10px;margin:0}
#team_debug h2 {font-size: 125%;}
#team_debug .p {text-align: left;}
#team_debug .e {background-color: #ccf; min-width: 30%; font-weight: bold;}
#team_debug .h {background-color: #99c; font-weight: bold;}
#team_debug .v {background-color: #ddd; width: 70%; overflow-x: auto; word-wrap: break-word;}
#team_debug .v i {color: #999;}
#team_debug .r {text-align: right;}
#team_debug img {float: right; border: 0;}
#team_debug hr {max-width: 934px; width: 100%; background-color: #ccc; border: 0; height: 1px;}
#team_debug hr.f {width: 100%;}
</style>
{/literal}

{function show_array list=[]}
	<table><tbody>

	{foreach $list as $key=>$value}
		{if (is_array($value))}
			<tr>
				<td class="e">{$key}</td>
				<td class="v">
					{show_array list=$value}
				</td>
			</tr>
		{else}
			<tr><td class="e">{$key}</td><td class="v">{$value}</td></tr>
		{/if}
	{/foreach}
	</tbody></table>

{/function}

<div id="team_debug" class="center">
	{if ($label)}
		<table><tbody><tr class="h"><td><h1>{$label}</h1></td></tr></tbody></table>
	{/if}

	{if ($sublabel)}
	<table><tbody><tr class="v"><td>{$sublabel}</td></tr></tbody></table>
	{/if}

	{show_array list=$vars}

	{if ($file && $line)}
	<table><tbody><tr class="v r"><td><strong>From {$file}: {$line}</strong></td></tr></tbody></table>
	{/if}

</div>
<hr class='f' />
