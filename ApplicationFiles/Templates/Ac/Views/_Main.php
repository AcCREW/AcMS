<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{SiteTitle}</title>
    {PreloadedCSS}<link rel="stylesheet" href="{Link}" />
    {/PreloadedCSS}
    <script type="text/javascript" src="{BaseURL}Content/js/angular/require.js" charset="utf-8"></script>

    <script>
    	var BaseURL = "{BaseURL}";
    	var CSRFTokenValue = "{CSRF_TOKEN_VALUE}";
    	var CSRFTokenName = "{CSRF_TOKEN_NAME}";
    	requirejs.config({
    		baseUrl: 'js/',
    		paths: {PreloadedJS},
    		shim: {PreloadedJSScheme}
    	});

    	requirejs(['AcGenerator'], function () {
    		angular.bootstrap(document.body, ['AcGenerator']);
    	});
    </script>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elemdnts and media queries -->
    <!--[if lt IE 9]>
      <script src="{BaseURL}ApplicationFiles/Templates/Default/js/libraries/html5shiv.min.js"></script>
      <script src="{BaseURL}ApplicationFiles/Templates/Default/js/libraries/respond.min.js"></script>
    <![endif]-->
</head>
<body data-ng-controller="AcController">
    <div class="wrapper" id="wrapper">
        <div class="container">
            <div class="menu">
                <a href="#/"><div class="item">Home</div></a>
				{IsLogged}
					<a href="#/Profile"><div class="item">Profile</div></a>
				{/IsLogged}
                {IsNotLogged}
					<a href="#/Register"><div class="item">Register</div></a>
				{/IsNotLogged}
				<a href="#/Status"><div class="item">Status</div></a>
                <a href="#/How"><div class="item">Connection Guide</div></a>
            </div>
            <div class="logo">
                <span>{SiteTitle}</span>
            </div>
            <div class="body">
                <div class="left_body" data-compile="RightContent"></div>
                <div class="right_body">
					{LeftContent}
                </div>
                <div class="clear"></div>
            </div>
        </div>
        <div class="clearfooter"></div>
    </div>
    <div class="push"></div>
    <div class="container">
        <div class="footer">
            <span class="left">&copy;2015 AcMS</span><span class="right">
			The page load for 0.0014</span>
        </div>
    </div>
</body>
</html>
