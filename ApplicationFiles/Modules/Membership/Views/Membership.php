<div data-ng-controller="Membership" id="Membership">
    {User}
    <span style="float: left;">Welcome</span><span style="float: right; font-weight: bold;">{Username} - <a href="#/Profile">Profile</a> - <a href="{BaseURL}index.php?Action=-1">Logout</a></span><span style="clear: both; display: block;"></span>
    <span style="float: left;">Account ID</span><span style="float: right; font-weight: bold;">{UserID}</span><span style="clear: both; display: block;"></span>
    <span style="float: left;">Vote Points (vp)</span><span style="float: right; font-weight: bold;">{VotePoints}</span><span style="clear: both; display: block;"></span>
    <span style="float: left;">Donate Points (dp)</span><span style="float: right; font-weight: bold;">{DonatePoints}</span><span style="clear: both; display: block;"></span>
    <span style="float: left;">Posts</span><span style="float: right; font-weight: bold;">{Posts}</span><span style="clear: both; display: block;"></span>
    <span style="float: left;">Current IP</span><span style="float: right; font-weight: bold;">{UserIP}</span><span style="clear: both; display: block;"></span>
    <span style="float: left;">Last Login IP</span><span style="float: right; font-weight: bold;">{LastIP}</span><span style="clear: both; display: block;"></span>
    <span style="float: left;">Last Login</span><span style="float: right; font-weight: bold;">{LastLogin}</span><span style="clear: both; display: block;"></span>
    <hr style="margin: 5px 0px 5px 0px;" />
    <span style="float: left;">Nickname</span>
    <span style="float: right; font-weight: bold;">{Name} 
		<a href="{BaseURL}index.php/profile/change_settings">
            <img src="{BaseURL}content/img/edit.png"></a>
    </span><span style="clear: both; display: block;"></span>
    {/User}
</div>
