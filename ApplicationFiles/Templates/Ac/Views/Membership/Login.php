<div data-ng-controller="Membership" id="Membership" data-ng-enter="Membership">
    <div data-compile="_this.LoginAlert.Content"></div>
    <div class="left" style="margin-top: 10px;">
        <label for="login_username">
            <strong>Account Name</strong>
        </label>
    </div>
    <div class="right">
        <input type="text" id="login_username" class="login_input" placeholder="Username..." data-ng-model="_this.Username" data-ng-required="true" />
    </div>
    <div class="clear"></div>
    <div class="left" style="margin-top: 10px">
        <label for="login_password">
            <strong>Password</strong>
        </label>
    </div>
    <div class="right">
        <input type="password" id="login_password" class="login_input" placeholder="Password..." data-ng-model="_this.Password" data-ng-required="true" />
    </div>
    <div class="clear"></div>
    <div class="left">
        <input type="checkbox" id="login_remember_me" class="left" style="width: auto;" data-ng-model="_this.RememberMe" />
        <label for="login_remember_me" class="noselect left" style="margin-top: 2px;">&nbsp;<strong>Remember me</strong></label>
    </div>
    <div class="right" style="margin-top: 2px;">
		<a href="#/ForgotPassword" class="noselect"><strong>Forgot Password?</strong></a>
    </div>
	<div class=	"clear"></div>
    <div class="right">
        <input type="button" name="submit" value="Login" data-ng-submit="Membership">
    </div>
    <div class="clear"></div>
</div>
