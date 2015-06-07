<div data-ng-controller="StatusController" data-ng-enter="Status" style="text-align: center;">
	<label for="RealmlistID">Pick realmlist: </label>
    <select data-ng-model="_this.RealmlistID" style="width: 187px;">
        <option id="RealmlistID" data-ng-repeat="Realmlist in _this.Realmlists" value="{{Realmlist.id}}" data-ng-selected="{{Realmlist.id == _this.RealmlistID}}">{{Realmlist.name}}</option>
    </select>
</div>
