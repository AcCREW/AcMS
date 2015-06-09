<div data-ng-controller="Membership" id="Membership" data-ng-enter="Membership">
    <table id="login-table">
        <tr>
            <td>
                <div data-compile="_this.LoginAlert.Content"></div>
            </td>
        </tr>
        <tr>
            <td>
                <input type="text" autocomplete="off" name="login_username" id="login_username" placeholder="Account..." data-ng-model="_this.Username" data-ng-required="true" />
            </td>
        </tr>
        <tr>
            <td>
                <input type="password" autocomplete="off" name="login_password" id="login_password" placeholder="Password..." data-ng-model="_this.Password" data-ng-required="true" />
            </td>
        </tr>
        <tr>
            <td>
                <input type="submit" name="submit" value="Login" data-ng-submit="Membership" style="float: left; width: 80px;" />
                <div style="float: right;">
                    <a href="#/ForgotPassword">Forgot Password?</a><br />
                    <a href="#/Register">Members Registration</a>
                </div>
            </td>
        </tr>
    </table>
</div>
