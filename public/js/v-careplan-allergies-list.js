/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 175);
/******/ })
/************************************************************************/
/******/ ({

/***/ 1:
/***/ (function(module, exports) {

/* globals __VUE_SSR_CONTEXT__ */

// this module is a runtime utility for cleaner component module output and will
// be included in the final webpack user bundle

module.exports = function normalizeComponent (
  rawScriptExports,
  compiledTemplate,
  injectStyles,
  scopeId,
  moduleIdentifier /* server only */
) {
  var esModule
  var scriptExports = rawScriptExports = rawScriptExports || {}

  // ES6 modules interop
  var type = typeof rawScriptExports.default
  if (type === 'object' || type === 'function') {
    esModule = rawScriptExports
    scriptExports = rawScriptExports.default
  }

  // Vue.extend constructor export interop
  var options = typeof scriptExports === 'function'
    ? scriptExports.options
    : scriptExports

  // render functions
  if (compiledTemplate) {
    options.render = compiledTemplate.render
    options.staticRenderFns = compiledTemplate.staticRenderFns
  }

  // scopedId
  if (scopeId) {
    options._scopeId = scopeId
  }

  var hook
  if (moduleIdentifier) { // server build
    hook = function (context) {
      // 2.3 injection
      context =
        context || // cached call
        (this.$vnode && this.$vnode.ssrContext) || // stateful
        (this.parent && this.parent.$vnode && this.parent.$vnode.ssrContext) // functional
      // 2.2 with runInNewContext: true
      if (!context && typeof __VUE_SSR_CONTEXT__ !== 'undefined') {
        context = __VUE_SSR_CONTEXT__
      }
      // inject component styles
      if (injectStyles) {
        injectStyles.call(this, context)
      }
      // register component module identifier for async chunk inferrence
      if (context && context._registeredComponents) {
        context._registeredComponents.add(moduleIdentifier)
      }
    }
    // used by ssr in case component is cached and beforeCreate
    // never gets called
    options._ssrRegister = hook
  } else if (injectStyles) {
    hook = injectStyles
  }

  if (hook) {
    var functional = options.functional
    var existing = functional
      ? options.render
      : options.beforeCreate
    if (!functional) {
      // inject component registration as beforeCreate hook
      options.beforeCreate = existing
        ? [].concat(existing, hook)
        : [hook]
    } else {
      // register for functioal component in vue file
      options.render = function renderWithStyleInjection (h, context) {
        hook.call(context)
        return existing(h, context)
      }
    }
  }

  return {
    esModule: esModule,
    exports: scriptExports,
    options: options
  }
}


/***/ }),

/***/ 114:
/***/ (function(module, exports, __webpack_require__) {

var disposed = false
var Component = __webpack_require__(1)(
  /* script */
  __webpack_require__(115),
  /* template */
  __webpack_require__(116),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)
Component.options.__file = "/Users/michalis/Code/CLH/cpm-api/resources/assets/js/ccd-models/allergies.vue"
if (Component.esModule && Object.keys(Component.esModule).some(function (key) {return key !== "default" && key.substr(0, 2) !== "__"})) {console.error("named exports are not supported in *.vue files.")}
if (Component.options.functional) {console.error("[vue-loader] allergies.vue: functional components are not supported with templates, they should use render functions.")}

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-7e95c7f6", Component.options)
  } else {
    hotAPI.reload("data-v-7e95c7f6", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

module.exports = Component.exports


/***/ }),

/***/ 115:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });

/* harmony default export */ __webpack_exports__["default"] = ({
    data: function data() {
        return {
            allergy: {
                id: '',
                patient_id: $('meta[name="patient_id"]').attr('content'),
                name: ''
            },
            allergies: []
        };
    },


    mounted: function mounted() {
        this.loadAllergies();
    },

    methods: {
        loadAllergies: function loadAllergies() {
            var self = this;

            var params = {
                params: {
                    patient_id: self.allergy.patient_id
                }
            };

            window.axios.get('/CCDModels/Items/AllergiesItem', params).then(function (response) {
                self.allergies = response.data;
            }, function (response) {
                console.log(response);
            });
        },

        addAllergy: function addAllergy() {

            if (this.allergy.name) {
                var self = this;

                var payload = {
                    'allergy': this.allergy
                };

                window.axios.post('/CCDModels/Items/AllergiesItem/store', payload).then(function (response) {
                    var id = response.data.id.id;

                    self.allergies.push({
                        id: id,
                        patient_id: self.allergy.patient_id,
                        name: response.data.id.allergen_name
                    });

                    //reset new allergy
                    self.allergy = {
                        id: '',
                        patient_id: self.allergy.patient_id,
                        name: ''
                    };
                }, function (response) {
                    console.log(response);
                });
            }
        },

        editAllergy: function editAllergy(index) {
            // hide text
            $('#allergy-name-' + index).toggle();

            // show textarea
            $('#allergy-edit-' + index).toggle();

            // hide all edit buttons
            $('.allergy-edit-btn').hide();
            $('.allergy-delete-btn').hide();

            // show save button
            $('#allergy-save-btn-' + index).toggle();
        },

        updateAllergy: function updateAllergy(index) {
            var payload = {
                allergy: this.allergies[index]
            };

            window.axios.post('/CCDModels/Items/AllergiesItem/update', payload).then(function (response) {
                // show text
                $('#allergy-name-' + index).toggle();

                // hide textarea
                $('#allergy-edit-' + index).toggle();

                // show all edit buttons
                $('.allergy-edit-btn').show();
                $('.allergy-delete-btn').show();

                // hide save button
                $('#allergy-save-btn-' + index).toggle();
            }, function (response) {
                console.log(response);
            });
        },

        deleteAllergy: function deleteAllergy(index, e) {
            if (confirm("Are you sure you want to delete this allergy?")) {
                var self = this;

                var payload = {
                    'allergy': self.allergies[index]
                };

                window.axios.post('/CCDModels/Items/AllergiesItem/destroy', payload).then(function (response) {
                    self.allergies.splice(index, 1);
                }, function (response) {
                    console.log(response);
                });
            }
        },

        postEvents: function postEvents(index, e) {
            window.axios.post('/CCDModels/Items/AllergiesItem/store', this.allergies).then(function (response) {}, function (response) {
                console.log(response);
            });
        }
    }
});

/***/ }),

/***/ 116:
/***/ (function(module, exports, __webpack_require__) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('div', {
    staticClass: "row",
    attrs: {
      "id": "allergies"
    }
  }, [_c('div', {
    staticClass: "col-sm-12"
  }, [_c('div', {
    staticClass: "list-group"
  }, [_vm._l((_vm.allergies), function(allergyitem, index) {
    return [(allergyitem.name) ? _c('div', {
      staticClass: "list-group-item",
      staticStyle: {
        "padding": "5px",
        "font-size": "12px"
      },
      attrs: {
        "href": "#"
      },
      on: {
        "submit": function($event) {
          $event.preventDefault();
        }
      }
    }, [_c('div', {
      staticClass: "row"
    }, [_c('div', {
      staticClass: "col-sm-9"
    }, [(allergyitem.name) ? _c('div', {
      staticClass: "list-group-item-heading"
    }, [_c('span', {
      attrs: {
        "id": 'allergy-name-' + index
      }
    }, [_vm._v(_vm._s(allergyitem.name))]), _vm._v(" "), _c('textarea', {
      directives: [{
        name: "model",
        rawName: "v-model",
        value: (allergyitem.name),
        expression: "allergyitem.name"
      }],
      staticStyle: {
        "display": "none"
      },
      attrs: {
        "id": 'allergy-edit-' + index,
        "rows": "10"
      },
      domProps: {
        "value": (allergyitem.name)
      },
      on: {
        "input": function($event) {
          if ($event.target.composing) { return; }
          allergyitem.name = $event.target.value
        }
      }
    }, [_vm._v(_vm._s(allergyitem.name))]), _vm._v(" "), _c('input', {
      attrs: {
        "type": "hidden",
        "name": "id"
      },
      domProps: {
        "value": allergyitem.id
      }
    }), _vm._v(" "), _c('input', {
      attrs: {
        "type": "hidden",
        "name": "patient_id"
      },
      domProps: {
        "value": allergyitem.patient_id
      }
    })]) : _vm._e()]), _vm._v(" "), _c('div', {
      staticClass: "col-sm-3 text-right"
    }, [(allergyitem.name) ? _c('p', {
      staticClass: "list-group-item-text"
    }, [_vm._v("\n                                " + _vm._s(allergyitem.description) + "\n                            ")]) : _vm._e(), _vm._v(" "), (allergyitem.name) ? _c('button', {
      staticClass: "btn btn-xs btn-danger allergy-delete-btn",
      on: {
        "click": function($event) {
          $event.stopPropagation();
          $event.preventDefault();
          _vm.deleteAllergy(index, _vm.allergy)
        }
      }
    }, [_vm._m(0, true)]) : _vm._e(), _vm._v(" "), (allergyitem.name) ? _c('button', {
      staticClass: "btn btn-xs btn-primary allergy-edit-btn",
      on: {
        "click": function($event) {
          $event.stopPropagation();
          $event.preventDefault();
          _vm.editAllergy(index, _vm.allergy)
        }
      }
    }, [_vm._m(1, true)]) : _vm._e(), _vm._v(" "), (allergyitem.name) ? _c('button', {
      staticClass: "btn btn-xs btn-success allergy-save-btn",
      staticStyle: {
        "display": "none"
      },
      attrs: {
        "id": 'allergy-save-btn-' + index
      },
      on: {
        "click": function($event) {
          $event.stopPropagation();
          $event.preventDefault();
          _vm.updateAllergy(index, _vm.allergy)
        }
      }
    }, [_vm._m(2, true)]) : _vm._e()])])]) : _vm._e()]
  })], 2)]), _vm._v(" "), _c('div', {
    staticClass: "col-sm-12"
  }, [_c('div', {
    staticClass: "panel panel-default"
  }, [_c('div', {
    staticClass: "panel-heading"
  }, [_vm._v("\n                Add an Allergy\n            ")]), _vm._v(" "), _c('div', {
    staticClass: "panel-body"
  }, [_c('div', {
    staticClass: "row"
  }, [_c('div', {
    staticClass: "col-sm-10"
  }, [_c('input', {
    attrs: {
      "type": "hidden",
      "id": "patient_id",
      "name": "patient_id"
    },
    domProps: {
      "value": _vm.allergy.patient_id
    }
  }), _vm._v(" "), _c('div', {
    staticClass: "form-group"
  }, [_c('input', {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: (_vm.allergy.name),
      expression: "allergy.name"
    }],
    staticClass: "form-control",
    attrs: {
      "placeholder": "Allergy Name"
    },
    domProps: {
      "value": (_vm.allergy.name)
    },
    on: {
      "input": function($event) {
        if ($event.target.composing) { return; }
        _vm.allergy.name = $event.target.value
      }
    }
  })])]), _vm._v(" "), _c('div', {
    staticClass: "col-sm-2 text-right"
  }, [_c('button', {
    staticClass: "btn btn-success",
    on: {
      "click": function($event) {
        $event.stopPropagation();
        $event.preventDefault();
        _vm.addAllergy()
      }
    }
  }, [_vm._m(3)])])])])])])])
},staticRenderFns: [function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('span', [_c('i', {
    staticClass: "glyphicon glyphicon-remove"
  })])
},function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('span', [_c('i', {
    staticClass: "glyphicon glyphicon-pencil"
  })])
},function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('span', [_c('i', {
    staticClass: "glyphicon glyphicon-ok"
  })])
},function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('span', [_c('i', {
    staticClass: "glyphicon glyphicon-plus"
  }), _vm._v(" Add")])
}]}
module.exports.render._withStripped = true
if (false) {
  module.hot.accept()
  if (module.hot.data) {
     require("vue-hot-reload-api").rerender("data-v-7e95c7f6", module.exports)
  }
}

/***/ }),

/***/ 175:
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(176);


/***/ }),

/***/ 176:
/***/ (function(module, exports, __webpack_require__) {

Vue.component('allergiesList', __webpack_require__(114));

window.App = new Vue({
    el: '#v-careplan-allergies-list'
});

/***/ })

/******/ });
//# sourceMappingURL=v-careplan-allergies-list.js.map