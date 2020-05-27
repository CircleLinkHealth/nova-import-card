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
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
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
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/Card.vue?vue&type=script&lang=js&":
/*!***************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/Card.vue?vue&type=script&lang=js& ***!
  \***************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
/* harmony default export */ __webpack_exports__["default"] = ({
  props: ['card' // The following props are only available on resource detail cards...
  // 'resource',
  // 'resourceId',
  // 'resourceName',
  ],
  data: function data() {
    return {
      amount: '',
      errors: null,
      sendingInvites: false
    };
  },
  methods: {
    redirectToInvitesDashboard: function redirectToInvitesDashboard() {
      // tried to redirect using Action::push() or simple redirect in controller, but it doesnt work, no errors / feedback.
      // Keeping this solution temporarily
      window.location.href = this.card.redirect_to;
    },
    title: function title() {
      return this.card.is_redirect ? "Invite By Practice" : "Enrollment Invites";
    },
    sendInvites: function sendInvites(color, amount) {
      var _this = this;

      this.sendingInvites = true;

      if (this.amount === '') {
        this.sendingInvites = false;
        alert('Invitations number to be send is required');
        return;
      }

      Nova.request().post('/nova-vendor/enrollment-invites/enrollment-invites', {
        color: color,
        amount: amount,
        practice_id: this.card.practice_id,
        is_patient: this.card.is_patient
      }).then(function (response) {
        _this.amount = '';
        _this.sendingInvites = false;

        _this.$toasted.success(response.data.message);
      })["catch"](function (error) {
        _this.sendingInvites = false;
        _this.amount = '';

        _this.$toasted.error(error.response.data);
      });
    }
  },
  mounted: function mounted() {}
});

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/Card.vue?vue&type=template&id=b9bc2c0a&":
/*!*******************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/Card.vue?vue&type=template&id=b9bc2c0a& ***!
  \*******************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("card", { staticClass: "flex flex-col items-left" }, [
    _c(
      "div",
      { staticClass: "px-3 py-3" },
      [
        _c("h1", { staticClass: "text-left text-3xl text-80 font-light" }, [
          _vm._v(_vm._s(this.title()))
        ]),
        _vm._v(" "),
        _c("div", { staticClass: "py-2" }, [
          !this.card.is_redirect
            ? _c("span", { staticClass: "flex" }, [
                _c("label", { attrs: { for: "amount" } }, [
                  _vm._v(
                    "\n                       Input number of patients to "
                  ),
                  _c("br"),
                  _vm._v(" send enrollment sms/emails to:\n                   ")
                ]),
                _vm._v(" "),
                _c("input", {
                  directives: [
                    {
                      name: "model",
                      rawName: "v-model",
                      value: _vm.amount,
                      expression: "amount"
                    }
                  ],
                  staticStyle: {
                    border: "1px solid #5cc0dd",
                    "max-width": "100px",
                    "margin-left": "10px"
                  },
                  attrs: {
                    type: "number",
                    id: "amount",
                    name: "amount",
                    disabled: _vm.sendingInvites,
                    required: ""
                  },
                  domProps: { value: _vm.amount },
                  on: {
                    input: function($event) {
                      if ($event.target.composing) {
                        return
                      }
                      _vm.amount = $event.target.value
                    }
                  }
                })
              ])
            : _vm._e()
        ]),
        _vm._v(" "),
        _vm.sendingInvites
          ? _c("loader", { attrs: { width: "30" } })
          : _vm._e(),
        _vm._v(" "),
        _c("div", { staticClass: "flex" }, [
          _vm.errors
            ? _c(
                "div",
                _vm._l(_vm.errors, function(error) {
                  return _c("p", { staticClass: "text-danger mb-1" }, [
                    _vm._v(_vm._s(error))
                  ])
                }),
                0
              )
            : _vm._e()
        ]),
        _vm._v(" "),
        !this.card.is_redirect
          ? _c("div", [
              _c("div", { staticClass: "invite-buttons" }, [
                !this.card.is_patient
                  ? _c(
                      "div",
                      {
                        staticClass: "button",
                        staticStyle: { display: "flex" }
                      },
                      [
                        _c(
                          "a",
                          {
                            staticClass:
                              "btn btn-default btn-primary ml-auto mt-auto",
                            staticStyle: {
                              cursor: "pointer",
                              "background-color": "#4baf50",
                              "white-space": "nowrap",
                              width: "275px",
                              "margin-right": "10px"
                            },
                            attrs: { disabled: _vm.sendingInvites },
                            on: {
                              click: function($event) {
                                return _vm.sendInvites("#4baf50", _vm.amount)
                              }
                            }
                          },
                          [
                            _vm._v(
                              "\n                  Send SMS/Emails (Green Btn.)\n              "
                            )
                          ]
                        ),
                        _vm._v(" "),
                        _c(
                          "a",
                          {
                            staticClass:
                              "btn btn-default btn-primary ml-auto mt-auto",
                            staticStyle: {
                              cursor: "pointer",
                              "background-color": "#b1284c",
                              "white-space": "nowrap",
                              width: "275px"
                            },
                            attrs: { disabled: _vm.sendingInvites },
                            on: {
                              click: function($event) {
                                return _vm.sendInvites("#b1284c", _vm.amount)
                              }
                            }
                          },
                          [
                            _vm._v(
                              "\n                  Send SMS/Emails (Red Btn.)\n              "
                            )
                          ]
                        )
                      ]
                    )
                  : _vm._e(),
                _vm._v(" "),
                this.card.is_patient
                  ? _c(
                      "div",
                      {
                        staticClass: "button",
                        attrs: { disabled: _vm.sendingInvites }
                      },
                      [
                        _c(
                          "a",
                          {
                            staticClass:
                              "btn btn-default btn-primary ml-auto mt-auto",
                            staticStyle: {
                              cursor: "pointer",
                              "background-color": "#4baf50"
                            },
                            on: {
                              click: function($event) {
                                return _vm.sendInvites("#4baf50", _vm.amount)
                              }
                            }
                          },
                          [_vm._v("Send Invite")]
                        )
                      ]
                    )
                  : _vm._e()
              ])
            ])
          : _c("div", { staticClass: "button" }, [
              _c(
                "a",
                {
                  staticClass: "btn btn-default btn-primary ml-auto mt-auto",
                  staticStyle: {
                    cursor: "pointer",
                    "background-color": "#4baf50"
                  },
                  on: {
                    click: function($event) {
                      return _vm.redirectToInvitesDashboard()
                    }
                  }
                },
                [_vm._v("Invite by practice")]
              )
            ])
      ],
      1
    )
  ])
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js":
/*!********************************************************************!*\
  !*** ./node_modules/vue-loader/lib/runtime/componentNormalizer.js ***!
  \********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return normalizeComponent; });
/* globals __VUE_SSR_CONTEXT__ */

// IMPORTANT: Do NOT use ES2015 features in this file (except for modules).
// This module is a runtime utility for cleaner component module output and will
// be included in the final webpack user bundle.

function normalizeComponent (
  scriptExports,
  render,
  staticRenderFns,
  functionalTemplate,
  injectStyles,
  scopeId,
  moduleIdentifier, /* server only */
  shadowMode /* vue-cli only */
) {
  // Vue.extend constructor export interop
  var options = typeof scriptExports === 'function'
    ? scriptExports.options
    : scriptExports

  // render functions
  if (render) {
    options.render = render
    options.staticRenderFns = staticRenderFns
    options._compiled = true
  }

  // functional template
  if (functionalTemplate) {
    options.functional = true
  }

  // scopedId
  if (scopeId) {
    options._scopeId = 'data-v-' + scopeId
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
    hook = shadowMode
      ? function () {
        injectStyles.call(
          this,
          (options.functional ? this.parent : this).$root.$options.shadowRoot
        )
      }
      : injectStyles
  }

  if (hook) {
    if (options.functional) {
      // for template-only hot-reload because in that case the render fn doesn't
      // go through the normalizer
      options._injectStyles = hook
      // register for functional component in vue file
      var originalRender = options.render
      options.render = function renderWithStyleInjection (h, context) {
        hook.call(context)
        return originalRender(h, context)
      }
    } else {
      // inject component registration as beforeCreate hook
      var existing = options.beforeCreate
      options.beforeCreate = existing
        ? [].concat(existing, hook)
        : [hook]
    }
  }

  return {
    exports: scriptExports,
    options: options
  }
}


/***/ }),

/***/ "./resources/js/card.js":
/*!******************************!*\
  !*** ./resources/js/card.js ***!
  \******************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

Nova.booting(function (Vue, router, store) {
  Vue.component('enrollment-invites', __webpack_require__(/*! ./components/Card */ "./resources/js/components/Card.vue")["default"]);
});

/***/ }),

/***/ "./resources/js/components/Card.vue":
/*!******************************************!*\
  !*** ./resources/js/components/Card.vue ***!
  \******************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _Card_vue_vue_type_template_id_b9bc2c0a___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Card.vue?vue&type=template&id=b9bc2c0a& */ "./resources/js/components/Card.vue?vue&type=template&id=b9bc2c0a&");
/* harmony import */ var _Card_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Card.vue?vue&type=script&lang=js& */ "./resources/js/components/Card.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _Card_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _Card_vue_vue_type_template_id_b9bc2c0a___WEBPACK_IMPORTED_MODULE_0__["render"],
  _Card_vue_vue_type_template_id_b9bc2c0a___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/Card.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/Card.vue?vue&type=script&lang=js&":
/*!*******************************************************************!*\
  !*** ./resources/js/components/Card.vue?vue&type=script&lang=js& ***!
  \*******************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Card_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./Card.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/Card.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Card_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/Card.vue?vue&type=template&id=b9bc2c0a&":
/*!*************************************************************************!*\
  !*** ./resources/js/components/Card.vue?vue&type=template&id=b9bc2c0a& ***!
  \*************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Card_vue_vue_type_template_id_b9bc2c0a___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./Card.vue?vue&type=template&id=b9bc2c0a& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/Card.vue?vue&type=template&id=b9bc2c0a&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Card_vue_vue_type_template_id_b9bc2c0a___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Card_vue_vue_type_template_id_b9bc2c0a___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/sass/card.scss":
/*!**********************************!*\
  !*** ./resources/sass/card.scss ***!
  \**********************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 0:
/*!***************************************************************!*\
  !*** multi ./resources/js/card.js ./resources/sass/card.scss ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! /Users/michalis/Code/Antonis/cpm-web/nova-components/EnrollmentInvites/resources/js/card.js */"./resources/js/card.js");
module.exports = __webpack_require__(/*! /Users/michalis/Code/Antonis/cpm-web/nova-components/EnrollmentInvites/resources/sass/card.scss */"./resources/sass/card.scss");


/***/ })

/******/ });