<table width="100%">
    <tr>
        <th></th>
        <th class="aleft">Character</th>
        <th class="aleft">Level</th>
        <th></th>
        <th class="aleft">Ping</th>
    </tr>
    {Characters}
		<tr>
			<td>{Number}</td>
			<td><a href="{BaseURL}#/Character/{CharacterID}/Realmlist/{RealmlistID}">{Name}</a></td>
			<td>{Level} lvl</td>
			<td><img src="{BaseURL}Content/img/icon/class/{Class}.gif" title="Class" />&nbsp;<img src="{BaseURL}Content/img/icon/race/{Race}-{Gender}.gif" title="Race" /></td>
			<td>{Latency}</td>
		</tr>
    {/Characters}
</table>