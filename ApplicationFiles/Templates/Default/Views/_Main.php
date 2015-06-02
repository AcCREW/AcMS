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
    <div id="navbar">
        <div class="wrapper">
            <ul class="menu">
                <li style="border-left: 1px solid #002630;">
                    <a href="#/">Home
                    </a>
                </li>
                <li>
                    <a href="#/Register"><span>Account</span></a>
                    <div style="left: 0;">
                        <ul>
                            <li><a href="#/Register"><span>Register</span></a></li>
                            <li><a href="#/ForgotPassword"><span>Forgot password</span></a></li>
                        </ul>
                    </div>
                </li>
                <li>
                    <a href="#/How">
                        <span>How to connect</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="wrapper">
        <div id="headerFix"></div>
        <div id="header">
            <a href="#/">
                <img src="{BaseURL}ApplicationFiles/Templates/Default/img/logo.png" id="logo" alt="logo" />
            </a>
        </div>
    </div>

    <div class="wrapper" id="body">
        <div id="left-body">
            {LeftContent}
        </div>
        <div id="right-body" data-compile="RightContent"></div>
    </div>
    <div class="wrapper" id="footer">
        <div class="footer-copyright">
            &copy; 2012-2014 All rights reserved to <a href="#/">{SiteTitle}</a><br />
            All img of this World of Warcraft web design are the property of their respective owners.<br />
        </div>

        <div class="footer-author">
            <!--This area should not be removed, this is a free template so all i ask for is to keep this area here not touched. -NicholasWalkerHD-->
            Powered by: <a href="#/">{Core}</a><br />
            Design by: <a href="http://thorgfx.com">Thor</a><br />
            Coded by: <a href="http://walkerhdd.com">NicholasWalkerHD</a>
        </div>
    </div>
</body>
</html>
