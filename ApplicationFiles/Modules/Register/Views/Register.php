<div data-ng-controller="RegisterController" data-ng-enter="Register">
    <span style="float: left; margin: 12px 0px 0px 0px;">
        <label for="register_username" style="cursor: pointer;">Username</label>
    </span>
    <span style="float: right;">
        <span id="username_availability"></span>
        <input type="text" data-focus-me="true" data-ng-model="_this.Username" style="width: 175px;" id="register_username" />
    </span>
    <div class="clear"></div>
    <br />
    <span style="float: left; margin: 12px 0px 0px 0px;">
        <label for="register_password" style="cursor: pointer;">Password</label>
    </span>
    <span style="float: right;">
        <span id="password_availability"></span>
        <input type="password" data-ng-model="_this.Password" style="width: 175px;" id="register_password" />
    </span>
    <div class="clear"></div>
    <br />
    <span style="float: left; margin: 12px 0px 0px 0px;">
        <label for="register_re_password" style="cursor: pointer;">Password confirm</label>
    </span>
    <span style="float: right;">
        <span id="password_re_availability"></span>
        <input type="password" data-ng-model="_this.PasswordConfirm" style="width: 175px;" id="register_re_password" />
    </span>
    <div class="clear"></div>
    <br />
    <span style="float: left; margin: 12px 0px 0px 0px;">
        <label for="register_email" style="cursor: pointer;">EMail</label>
    </span>
    <span style="float: right;">
        <span id="email_availability"></span>
        <input type="text" data-ng-model="_this.EMail" id="register_email" style="width: 175px;" />
    </span>
    <div class="clear"></div>
    <br />
    <span style="float: left; margin: 12px 0px 0px 0px;">Security question</span>
    <span style="float: right; text-align: right;" id="register_security_question">
        <select data-ng-model="_this.SecurityQuestionID" style="width: 187px;">
            <option data-ng-repeat="SecurityQuestion in _this.SecurityQuestions" value="{{SecurityQuestion.SecurityQuestionID}}" data-ng-selected="{{SecurityQuestion.SecurityQuestionID == _this.SecurityQuestionID}}">{{SecurityQuestion.Name}}</option>
        </select>
    </span>
    <div class="clear"></div>
    <br />
    <span style="float: left; margin: 12px 0px 0px 0px;">
        <label for="register_security_answer" style="cursor: pointer;">Security answer</label>
    </span>
    <span style="float: right;">
        <input type="text" data-ng-model="_this.SecurityAnswer" style="width: 175px;" id="register_security_answer" />
    </span>
    <div class="clear"></div>
    <br />
    <div data-compile="_this.RegisterAlert.Content"></div>
    <div class="clear"></div>
    <br />
    <span style="float: left; margin: 12px 0px 0px 0px; font-weight: bold;"></span><span style="float: right;">
        <input type="submit" value="Register account" data-ng-submit="Register" /></span><div class="clear"></div>
</div>
