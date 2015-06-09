<div data-ng-controller="Membership" id="Membership">
    {User}
		<div class="left">Welcome</div><div class="right black">{Username} - <a href="#/Profile">Profile</a> - <a href="{BaseURL}index.php?Action=-1">Logout</a></div><div class="clear"></div>
		<div class="left">Account ID</div><div class="right black">{UserID}</div><div class="clear"></div>
		<div class="left">Vote Points (vp)</div><div class="right black">{VotePoints}</div><div class="clear"></div>
		<div class="left">Donate Points (dp)</div><div class="right black">{DonatePoints}</div><div class="clear"></div>
		<div class="left">Posts</div><div class="right black">{Posts}</div><div class="clear"></div>
		<div class="left">Current IP</div><div class="right black">{UserIP}</div><div class="clear"></div>
		<div class="left">Last Login IP</div><div class="right black">{LastIP}</div><div class="clear"></div>
		<div class="left">Last Login</div><div class="right black">{LastLogin}</div><div class="clear"></div>
		<hr style="margin: 5px 0px 5px 0px;"/>
		<div class="left">Nickname</div>
		<div class="right black">{Name} 
			<a href="{BaseURL}index.php/profile/change_settings">
				<img src="{BaseURL}Content/img/edit.png"></a>
		</div><div class="clear"></div>
    {/User}
</div>
