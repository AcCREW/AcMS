angular.module('AcGenerator', ['oc.lazyLoad', 'ngProgress', 'ui.router'], function ($compileProvider) {
    $compileProvider.directive('compile', function ($compile) {
        return function (scope, element, attrs) {
            scope.$watch(
			  function (scope) {
			      return scope.$eval(attrs.compile);
			  },
			  function (value) {
			      element.html(value);
			      $compile(element.contents())(scope);
			  }
			);
        };
    });
}).run(['$rootScope', '$state', '$stateParams', function ($rootScope, $state, $stateParams) {
    $rootScope.$state = $state;
    $rootScope.$stateParams = $stateParams;
}]).factory('userService', function () {
    var _UserService = {};

    _UserService.UpdateJS = function (vData) {
        angular.forEach(vData, function (vValue, sKey) {
            if (vValue == 'true' || vValue == 'false') {
                vValue = vValue === 'true';
            }
            eval('_UserService.' + sKey + ' = vValue;');
        });
    }

    return _UserService;
}).service('AcHTTP', function ($http, userService, $window, $sce) {
    this.Request = function (sURL, vData) {
        var request = $http({
            method: 'GET',
            url: sURL,
            params: vData,
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        });

        return (request.then(function (response) { return response.data }, function (response) { return new Error(response.statusText, response.status); }));
    }

    this.PostRequest = function (sURL, vData) {
        eval("vData." + CSRFTokenName + " = '" + CSRFTokenValue + "';");

        var request = $http({
            method: 'POST',
            url: sURL,
            data: vData,
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        });

        return (request.then(function (response) { return response.data }, function (response) { return new Error(response.statusText, response.status); }));
    }

    this.Submit = function (sModule, vData) {
        vData.Module = sModule;
        vData.Action = 1001;
        var request = this.PostRequest(BaseURL + 'index.php', vData);

        request.then(function (_Data) {
            if (_Data.Location !== undefined) {
                $window.location.href = _Data.Location;
            }
            if (_Data.UpdateJS !== undefined) {
                userService.UpdateJS(_Data.UpdateJS);
            }
            if (_Data.Dump !== undefined) {
                console.dir(_Data.Dump);
            }
        }, function (Error) {
            console.log(Error);
        });
    };
}).directive('focusMe', function ($timeout, $parse) {
    return {
        //scope: true,   // optionally create a child scope
        link: function (scope, element, attrs) {
            var model = $parse(attrs.focusMe);
            scope.$watch(model, function (value) {
                if (value === true) {
                    $timeout(function () {
                        element[0].focus();
                    });
                }
            });
        }
    };
}).directive('input', function ($parse) {
    return {
        restrict: 'E',
        require: '?ngModel',
        link: function (scope, element, attrs) {
            if (attrs.ngModel && attrs.value) {
                $parse(attrs.ngModel).assign(scope, attrs.value);
            }
        }
    };
}).directive('span', function ($parse) {
    return {
        restrict: 'E',
        require: '?ngBind',
        link: function (scope, element, attrs) {
            if (attrs.ngBind && attrs.$$element.context.innerHTML) {
                $parse(attrs.ngBind).assign(scope, attrs.$$element.context.innerHTML);
            }
        }
    };
}).directive('ngRequired', function ($parse) {
    return {
        require: ['?ngModel', '?ngRequired'],
        link: function (scope, element, attrs) {
            if (scope.RequiredElements === undefined) {
                scope.RequiredElements = Array();
            }
            scope.RequiredElements.push(element);
        }
    };
}).directive('ngEnter', function ($parse, AcHTTP, userService) {
    return function (scope, element, attrs) {
        element.bind("keypress", function (event) {
            if (event.which === 13) {
                if (scope.RequiredElements !== undefined) {
                    var bFocused = false;
                    for (var i = 0; i < scope.RequiredElements.length; i++) {
                        eval("var vValue = scope." + scope.RequiredElements[i].context.attributes.getNamedItem('data-ng-model').value + ";");
                        if (!vValue) {
                            if (!bFocused) {
                                scope.RequiredElements[i].focus();
                                bFocused = true;
                            }
                            scope.RequiredElements[i].removeClass('ng-untouched');
                            scope.RequiredElements[i].addClass('ng-touched');
                        }
                    }
                    if (bFocused) {
                        return;
                    }
                }
                eval("AcHTTP.Submit('" + attrs.ngEnter + "', userService." + attrs.ngEnter + ");");
            }
        });
    };
}).directive('ngSubmit', function ($parse, AcHTTP, userService) {
    return {
        link: function (scope, element, attrs) {
            element.on("click", function (event) {
                if (scope.RequiredElements !== undefined) {
                    var bFocused = false;
                    for (var i = 0; i < scope.RequiredElements.length; i++) {
                        eval("var vValue = scope." + scope.RequiredElements[i].context.attributes.getNamedItem('data-ng-model').value + ";");
                        if (!vValue) {
                            if(!bFocused) {
                                scope.RequiredElements[i].focus();
                                bFocused = true;
                            }
                            scope.RequiredElements[i].addClass('ng-touched');
                            scope.RequiredElements[i].removeClass('ng-untouched');
                        }
                    }
                    if (bFocused) {
                        return;
                    }
                }
                eval("AcHTTP.Submit('" + attrs.ngSubmit + "', userService." + attrs.ngSubmit + ");");
            });
        }
    };
}).config(['$ocLazyLoadProvider', '$stateProvider', '$urlRouterProvider', function ($ocLazyLoadProvider, $stateProvider, $urlRouterProvider) {
    $ocLazyLoadProvider.config({
        loadedModules: [],
        jsLoader: requirejs,
        debug: false
    });

    $urlRouterProvider
      .otherwise('/Index');

    var states = [
        { name: 'State', url: '/{State:[a-zA-Z]{1,10000}}' },
        { name: 'State.RecordID', url: '/{RecordID:[0-9]{1,9}}' },
        { name: 'State.RecordIDPage', url: '/{RecordID:[0-9]{1,9}}/Page/{Page:[0-9]{1,9}}' },
        { name: 'State.Page', url: '/Page/{Page:[0-9]{1,9}}' }
    ];

    for (var i = 0; i < states.length; i++) {
        $stateProvider.state(states[i].name, states[i]);
    }
}]).controller('AcController', function ($scope, $ocLazyLoad, ngProgress, AcHTTP, $state, userService, $window) {
    $scope.arHTMLCache = Array();
    $scope.CurrentModule = $state.current.name; // defualt Index
    $scope.CurrentRecordID = null;
    $scope.CurrentPage = 1;

    $scope.$on('$stateChangeStart', function (event, toState, toParams, fromState, fromParams) {
        if (toParams != fromParams) {
            if (toParams.RecordID !== undefined) {
                $scope.CurrentRecordID = toParams.RecordID;
            } else {
                $scope.CurrentRecordID = null;
            }
            if (toParams.Page !== undefined) {
                $scope.CurrentPage = toParams.Page;
            } else {
                $scope.CurrentPage = 1;
            }
        }
        $scope.CurrentModule = toParams.State;
        if (toParams.State == fromParams.State && toParams != fromParams) {
            $scope._Load($scope.CurrentModule);
        }
    });

    $scope.$watch('CurrentModule', function (sNewModule, sOldModule) {
        $scope._Load(sNewModule);
    });

    $scope.GoTo = function (sModule) {
        $scope.CurrentModule = sModule;
    }

    $scope._Load = function (sModule) {
        if (sModule == "" || sModule == undefined) {
            return;
        }

        var CurrentRecordID = $scope.CurrentRecordID;
        if (CurrentRecordID !== undefined && CurrentRecordID != null) {
            sModule += '.' + CurrentRecordID;
        }
        var CurrentPage = $scope.CurrentPage;
        if (CurrentPage !== undefined && CurrentPage != null) {
            sModule += '.' + CurrentPage;
        }

        if ($scope.arHTMLCache[sModule] !== undefined) {
            var _Data = $scope.arHTMLCache[sModule];
            $scope.RightContent = _Data.Content;
            document.title = _Data.SiteTitle + ' - ' + _Data.ModuleTitle;

            return;
        }

        var CurrentStateURI = sModule.split(".");
        sModuleName = CurrentStateURI[0];
        ngProgress.reset().start();
        AcHTTP.Request(BaseURL + 'index.php', { "Module": sModuleName, "RecordID": CurrentRecordID, "Page": CurrentPage, "Action": 1000 }).then(function (_Data) {
            if (_Data.__Type == 'Error') {
                $scope.RightContent = _Data.Message;
                ngProgress.complete();
                return;
            }
            if (_Data.Location !== undefined) {
                $window.location.href = _Data.Location;
                return;
            }
            if (_Data.RequireAngularJS === true) {
                $ocLazyLoad.load({
                    name: sModuleName,
                    files: ['../ApplicationFiles/Modules/' + sModuleName + '/js/' + sModuleName]
                }).then(function () {
                    eval("if (userService." + sModuleName + " === undefined) { userService." + sModuleName + " = {} }");
                    if (_Data.UpdateJS !== undefined) {
                        userService.UpdateJS(_Data.UpdateJS);
                    }
                    $scope.RightContent = _Data.Content;
                    $scope.arHTMLCache[sModule] = _Data;
                    document.title = _Data.SiteTitle + ' - ' + _Data.ModuleTitle;
                    ngProgress.complete();
                }, function (e) {
                    console.log(e);
                    ngProgress.complete();
                });
            } else {
                $scope.RightContent = _Data.Content;
                $scope.arHTMLCache[sModule] = _Data;
                document.title = _Data.SiteTitle + ' - ' + _Data.ModuleTitle;
                ngProgress.complete();
            }
        }, function (Error) {
            ngProgress.complete();
            console.log(Error);
        });
    }
}).controller('Membership', function ($scope, userService) {
    if (userService.Membership === undefined) {
        userService.Membership = {}
    }

    $scope._this = userService.Membership;
});