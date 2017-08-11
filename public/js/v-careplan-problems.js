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
/******/ 	return __webpack_require__(__webpack_require__.s = 171);
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

/***/ 100:
/***/ (function(module, exports, __webpack_require__) {

var disposed = false
var Component = __webpack_require__(1)(
  /* script */
  __webpack_require__(101),
  /* template */
  __webpack_require__(102),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)
Component.options.__file = "/Users/michalis/Code/CLH/cpm-api/resources/assets/js/ccd-models/problems.vue"
if (Component.esModule && Object.keys(Component.esModule).some(function (key) {return key !== "default" && key.substr(0, 2) !== "__"})) {console.error("named exports are not supported in *.vue files.")}
if (Component.options.functional) {console.error("[vue-loader] problems.vue: functional components are not supported with templates, they should use render functions.")}

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-50157be8", Component.options)
  } else {
    hotAPI.reload("data-v-50157be8", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

module.exports = Component.exports


/***/ }),

/***/ 101:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });

/* harmony default export */ __webpack_exports__["default"] = ({
    data: function data() {
        return {
            problem: {
                id: '',
                patient_id: $('meta[name="patient_id"]').attr('content'),
                name: ''
            },
            problems: []
        };
    },


    mounted: function mounted() {
        this.loadProblems();
    },

    methods: {
        loadProblems: function loadProblems() {
            var self = this;

            var params = {
                params: {
                    patient_id: self.problem.patient_id
                }
            };

            window.axios.get('/CCDModels/Items/ProblemsItem', params).then(function (response) {
                self.problems = response.data;
            }, function (response) {
                console.log(response);
            });
        },

        addProblem: function addProblem() {
            if (this.problem.name) {
                var self = this;

                var payload = {
                    'problem': this.problem
                };

                window.axios.post('/CCDModels/Items/ProblemsItem/store', payload).then(function (response) {
                    var id = response.data.id.id;

                    self.problems.push({ id: id, patient_id: self.problem.patient_id, name: response.data.id.name });
                    self.problem = { id: '', patient_id: self.problem.patient_id, name: '' };
                }, function (response) {
                    console.log(response);
                });
            }
        },

        editProblem: function editProblem(index) {
            $('#problem-name-' + index).toggle();
            $('#problem-edit-' + index).toggle();
            $('.problem-edit-btn').hide();
            $('.problem-delete-btn').hide();
            $('#problem-save-btn-' + index).toggle();
        },

        updateProblem: function updateProblem(index) {
            var payload = {
                'problem': this.problems[index]
            };

            window.axios.post('/CCDModels/Items/ProblemsItem/update', payload).then(function (response) {
                $('#problem-name-' + index).toggle();
                $('#problem-edit-' + index).toggle();
                $('.problem-edit-btn').show();
                $('.problem-delete-btn').show();
                $('#problem-save-btn-' + index).toggle();
            }, function (response) {
                console.log(response);
            });
        },

        deleteProblem: function deleteProblem(index, e) {
            if (confirm("Are you sure you want to delete this problem?")) {
                var self = this;

                var payload = {
                    'problem': this.problems[index]
                };

                window.axios.post('/CCDModels/Items/ProblemsItem/destroy', payload).then(function (response) {
                    self.problems.splice(index, 1);
                }, function (response) {
                    console.log(response);
                });
            }
        },

        postEvents: function postEvents(index, e) {
            window.axios.post('/CCDModels/Items/ProblemsItem/store', this.problems).then(function (response) {}, function (response) {
                console.log(response);
            });
        }
    }
});

/***/ }),

/***/ 102:
/***/ (function(module, exports, __webpack_require__) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('div', {
    staticClass: "row",
    attrs: {
      "id": "problems"
    }
  }, [_c('div', {
    staticClass: "col-sm-12"
  }, [_c('div', {
    staticClass: "list-group"
  }, [_vm._l((_vm.problems), function(problemitem, index) {
    return [(problemitem.name) ? _c('div', {
      staticClass: "list-group-item",
      staticStyle: {
        "padding": "5px",
        "font-size": "12px"
      },
      on: {
        "submit": function($event) {
          $event.preventDefault();
        }
      }
    }, [_c('div', {
      staticClass: "row"
    }, [_c('div', {
      staticClass: "col-sm-10"
    }, [_c('div', {
      staticClass: "list-group-item-heading"
    }, [_c('span', {
      attrs: {
        "id": 'problem-name-' + index
      }
    }, [_vm._v(_vm._s(problemitem.name))]), _vm._v(" "), _c('textarea', {
      directives: [{
        name: "model",
        rawName: "v-model",
        value: (problemitem.name),
        expression: "problemitem.name"
      }],
      staticStyle: {
        "display": "none"
      },
      attrs: {
        "id": 'problem-edit-' + index,
        "rows": "10"
      },
      domProps: {
        "value": (problemitem.name)
      },
      on: {
        "input": function($event) {
          if ($event.target.composing) { return; }
          problemitem.name = $event.target.value
        }
      }
    }, [_vm._v(_vm._s(problemitem.name))]), _vm._v(" "), _c('input', {
      attrs: {
        "type": "hidden",
        "name": "id"
      },
      domProps: {
        "value": problemitem.id
      }
    }), _vm._v(" "), _c('input', {
      attrs: {
        "type": "hidden",
        "name": "patient_id"
      },
      domProps: {
        "value": problemitem.patient_id
      }
    })])]), _vm._v(" "), _c('div', {
      staticClass: "col-sm-2 text-right"
    }, [(problemitem.name) ? _c('p', {
      staticClass: "list-group-item-text"
    }, [_vm._v(_vm._s(problemitem.description))]) : _vm._e(), _vm._v(" "), (problemitem.name) ? _c('button', {
      staticClass: "btn btn-xs btn-danger problem-delete-btn",
      on: {
        "click": function($event) {
          $event.stopPropagation();
          $event.preventDefault();
          _vm.deleteProblem(index, _vm.problem)
        }
      }
    }, [_vm._m(0, true)]) : _vm._e(), _vm._v(" "), (problemitem.name) ? _c('button', {
      staticClass: "btn btn-xs btn-primary problem-edit-btn",
      on: {
        "click": function($event) {
          $event.stopPropagation();
          $event.preventDefault();
          _vm.editProblem(index, _vm.problem)
        }
      }
    }, [_vm._m(1, true)]) : _vm._e(), _vm._v(" "), (problemitem.name) ? _c('button', {
      staticClass: "btn btn-xs btn-success problem-save-btn",
      staticStyle: {
        "display": "none"
      },
      attrs: {
        "id": 'problem-save-btn-' + index
      },
      on: {
        "click": function($event) {
          $event.stopPropagation();
          $event.preventDefault();
          _vm.updateProblem(index, _vm.problem)
        }
      }
    }, [_vm._m(2, true)]) : _vm._e()])])]) : _vm._e()]
  })], 2)]), _vm._v(" "), _c('div', {
    staticClass: "col-sm-12"
  }, [_c('div', {
    staticClass: "panel panel-default"
  }, [_c('div', {
    staticClass: "panel-heading"
  }, [_vm._v("\n                Add a Problem\n            ")]), _vm._v(" "), _c('div', {
    staticClass: "panel-body"
  }, [_c('div', {
    staticClass: "row"
  }, [_c('div', {
    staticClass: "col-sm-9"
  }, [_c('input', {
    attrs: {
      "type": "hidden",
      "id": "patient_id",
      "name": "patient_id"
    },
    domProps: {
      "value": _vm.problem.patient_id
    }
  }), _vm._v(" "), _c('div', {
    staticClass: "form-group"
  }, [_c('input', {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: (_vm.problem.name),
      expression: "problem.name"
    }],
    staticClass: "form-control",
    attrs: {
      "placeholder": "Problem Name"
    },
    domProps: {
      "value": (_vm.problem.name)
    },
    on: {
      "input": function($event) {
        if ($event.target.composing) { return; }
        _vm.problem.name = $event.target.value
      }
    }
  })])]), _vm._v(" "), _c('div', {
    staticClass: "col-sm-3 text-right"
  }, [_c('button', {
    staticClass: "btn btn-success",
    on: {
      "click": function($event) {
        $event.stopPropagation();
        $event.preventDefault();
        _vm.addProblem()
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
     require("vue-hot-reload-api").rerender("data-v-50157be8", module.exports)
  }
}

/***/ }),

/***/ 171:
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(172);


/***/ }),

/***/ 172:
/***/ (function(module, exports, __webpack_require__) {

Vue.component('problemsList', __webpack_require__(100));

window.App = new Vue({
    el: '#v-careplan-problems'
});

/***/ })

/******/ });
//# sourceMappingURL=v-careplan-problems.js.map