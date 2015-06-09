<div data-ng-controller="StatusController" data-ng-enter="Status" style="text-align: center;">
    <div class="row">
        <div class="col-md-3 col-md-offset-2">
            <label class="left label" for="RealmlistID"><span class="black">Pick realmlist</span></label>
        </div>
        <div class="col-md-5">
            <select data-ng-model="_this.RealmlistID" style="width: 187px;">
                <option id="RealmlistID" data-ng-repeat="Realmlist in _this.Realmlists" value="{{Realmlist.id}}" data-ng-selected="{{Realmlist.id == _this.RealmlistID}}">{{Realmlist.name}}</option>
            </select>
        </div>
    </div>
</div>
