<html>
<body>
{foreach $allData as $data => $value}
	{if ('view' !=$data)}
		<p><strong>{$data}:</strong> {$value}</p>
	{/if}
{/foreach}
</body>
</html>
