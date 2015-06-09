<div data-ng-controller="RegisterController" data-ng-enter="Register">
    <div class="row">
        <div class="col-md-3 col-md-offset-2">
            <label class="left label" for="register_username"><span class="black"> Username</span></label>
        </div>
        <div class="col-md-5">
            <input type="text" data-focus-me="true" data-ng-model="_this.Username" id="register_username" data-ng-required="true" />
        </div>
    </div>
	<br />

    <div class="row">
        <div class="col-md-3 col-md-offset-2">
            <label class="left label" for="register_password"><span class="black"> Password</span></label>
        </div>
        <div class="col-md-5">
            <input type="password" data-ng-model="_this.Password" class="right" id="register_password" data-ng-required="true" />
        </div>
    </div>
	<br />

    <div class="row">
        <div class="col-md-3 col-md-offset-2">
            <label class="left label" for="register_re_password"><span class="black"> Password confirm</span></label>
        </div>
        <div class="col-md-5">
            <input type="password" data-ng-model="_this.PasswordConfirm" class="right" id="register_re_password" data-ng-required="true" />
        </div>
    </div>
	<br />

    <div class="row">
        <div class="col-md-3 col-md-offset-2">
            <label class="left label" for="register_email"><span class="black"> EMail</span></label>
        </div>
        <div class="col-md-5">
            <input type="text" data-ng-model="_this.EMail" id="register_email" class="right" data-ng-required="true" />
        </div>
    </div>
	<br />

    <div class="row">
        <div class="col-md-3 col-md-offset-2">
            <label class="left label" for="register_security_question"><span class="black"> Security question</span></label>
        </div>
        <div class="col-md-5">
            <select data-ng-model="_this.SecurityQuestionID" id="register_security_question" class="right" data-ng-required="true">
                <option data-ng-repeat="SecurityQuestion in _this.SecurityQuestions" value="{{SecurityQuestion.SecurityQuestionID}}" data-ng-selected="{{SecurityQuestion.SecurityQuestionID == _this.SecurityQuestionID}}">{{SecurityQuestion.Name}}</option>
            </select>
        </div>
    </div>
	<br />

	<div class="row">
        <div class="col-md-3 col-md-offset-2">
            <label class="left label" for="register_security_answer"><span class="black"> Security answer</span></label>
        </div>
        <div class="col-md-5">
			<input type="text" data-ng-model="_this.SecurityAnswer" class="right" id="register_security_answer" data-ng-required="true" />
        </div>
    </div>
	<br />

	<div class="row">
        <div class="col-md-12" data-compile="_this.RegisterAlert.Content">
		</div>
	</div>

	<div class="row">
        <div class="col-md-5 col-md-offset-5">
			<input type="submit" value="Register account" data-ng-submit="Register" class="right" />
		</div>
	</div>
</div>
