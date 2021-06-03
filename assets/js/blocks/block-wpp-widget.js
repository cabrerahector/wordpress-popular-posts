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
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/Block/Widget/widget.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/Block/Widget/edit.js":
/*!**********************************!*\
  !*** ./src/Block/Widget/edit.js ***!
  \**********************************/
/*! exports provided: WPPWidgetBlockEdit */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "WPPWidgetBlockEdit", function() { return WPPWidgetBlockEdit; });
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../utils */ "./src/Block/utils.js");
function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _getPrototypeOf(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); return true; } catch (e) { return false; } }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }


var ServerSideRender = wp.editor.ServerSideRender;
var _wp$element = wp.element,
    Component = _wp$element.Component,
    Fragment = _wp$element.Fragment;
var BlockControls = wp.blockEditor.BlockControls;
var _wp$components = wp.components,
    CheckboxControl = _wp$components.CheckboxControl,
    SelectControl = _wp$components.SelectControl,
    TextControl = _wp$components.TextControl,
    Toolbar = _wp$components.Toolbar,
    Button = _wp$components.Button,
    Disabled = _wp$components.Disabled;
var __ = wp.i18n.__;
var endpoint = '';
var WPPWidgetBlockEdit = /*#__PURE__*/function (_Component) {
  _inherits(WPPWidgetBlockEdit, _Component);

  var _super = _createSuper(WPPWidgetBlockEdit);

  function WPPWidgetBlockEdit(props) {
    var _this;

    _classCallCheck(this, WPPWidgetBlockEdit);

    _this = _super.call(this, props);
    _this.state = {
      error: null,
      editMode: true,
      themes: null
    };
    return _this;
  }

  _createClass(WPPWidgetBlockEdit, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      var attributes = this.props.attributes;
      this.setState({
        editMode: attributes._editMode
      });
      this.getThemes();
    }
  }, {
    key: "getThemes",
    value: function getThemes() {
      var _this2 = this;

      wp.apiFetch({
        path: 'wordpress-popular-posts/v1/themes'
      }).then(function (themes) {
        _this2.setState({
          themes: themes
        });
      }, function (error) {
        _this2.setState({
          error: error,
          themes: null
        });
      });
    }
  }, {
    key: "getBlockControls",
    value: function getBlockControls() {
      var _this$props = this.props,
          attributes = _this$props.attributes,
          setAttributes = _this$props.setAttributes;

      var _self = this;

      function onPreviewChange() {
        var editMode = !_self.state.editMode;

        _self.setState({
          editMode: editMode
        });

        setAttributes({
          _editMode: editMode
        });
      }

      return /*#__PURE__*/React.createElement(BlockControls, null, /*#__PURE__*/React.createElement(Toolbar, null, /*#__PURE__*/React.createElement(Button, {
        label: this.state.editMode ? __('Preview', 'wordpress-popular-posts') : __('Preview', 'wordpress-popular-posts'),
        icon: this.state.editMode ? "format-image" : "edit",
        onClick: onPreviewChange
      })));
    }
  }, {
    key: "render",
    value: function render() {
      var _this$props2 = this.props,
          isSelected = _this$props2.isSelected,
          className = _this$props2.className,
          attributes = _this$props2.attributes,
          setAttributes = _this$props2.setAttributes;

      function onTitleChange(value) {
        setAttributes({
          title: Object(_utils__WEBPACK_IMPORTED_MODULE_0__["sanitize_text_field"])(value)
        });
      }

      function onLimitChange(value) {
        var limit = Number.isInteger(Number(value)) && Number(value) > 0 ? value : 10;
        setAttributes({
          limit: Number(limit)
        });
      }

      function onOrderByChange(value) {
        setAttributes({
          order_by: value
        });
      }

      function onTimeRangeChange(value) {
        setAttributes({
          range: value
        });
      }

      function onTimeQuantityChange(value) {
        var qty = Number.isInteger(Number(value)) && Number(value) > 0 ? value : 24;
        setAttributes({
          time_quantity: Number(qty)
        });
      }

      function onTimeUnitChange(value) {
        setAttributes({
          time_unit: value
        });
      }

      function onFreshnessChange(value) {
        setAttributes({
          freshness: value
        });
      }

      function onPostTypeChange(value) {
        setAttributes({
          post_type: Object(_utils__WEBPACK_IMPORTED_MODULE_0__["sanitize_text_field"])(value)
        });
      }

      function onPostIDExcludeChange(value) {
        //let new_value = value.replace(/[^0-9-\,]/, '');
        var new_value = value.replace(/[^0-9\,]/, '');
        setAttributes({
          pid: new_value
        });
      }

      function onAuthorChange(value) {
        var new_value = value.replace(/[^0-9\,]/, '');
        setAttributes({
          pid: new_value
        });
      }

      function onThemeChange(value) {
        setAttributes({
          theme: value
        });
      }

      var classes = className;
      classes += this.state.editMode ? ' in-edit-mode' : '';
      classes += isSelected ? ' is-selected' : '';
      var themes = [{
        label: __('None', 'wordpress-popular-posts'),
        value: ''
      }];

      if (this.state.themes) {
        for (var theme in this.state.themes) {
          themes.push({
            label: this.state.themes[theme].json.name,
            value: theme
          });
        }
      }

      return [this.getBlockControls(), /*#__PURE__*/React.createElement("div", {
        className: classes
      }, this.state.editMode && /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(TextControl, {
        label: __('Title', 'wordpress-popular-posts'),
        value: attributes.title,
        onChange: onTitleChange
      }), /*#__PURE__*/React.createElement(TextControl, {
        label: __('Limit', 'wordpress-popular-posts'),
        value: attributes.limit,
        onChange: onLimitChange
      }), /*#__PURE__*/React.createElement(SelectControl, {
        label: __('Sort posts by', 'wordpress-popular-posts'),
        value: attributes.order_by,
        options: [{
          label: __('Total views', 'wordpress-popular-posts'),
          value: 'views'
        }, {
          label: __('Comments', 'wordpress-popular-posts'),
          value: 'comments'
        }],
        onChange: onOrderByChange
      }), /*#__PURE__*/React.createElement(SelectControl, {
        label: __('Time Range', 'wordpress-popular-posts'),
        value: attributes.range,
        options: [{
          label: __('Last 24 Hours', 'wordpress-popular-posts'),
          value: 'last24hours'
        }, {
          label: __('Last 7 days', 'wordpress-popular-posts'),
          value: 'last7days'
        }, {
          label: __('Last 30 days', 'wordpress-popular-posts'),
          value: 'last30days'
        }, {
          label: __('All-time', 'wordpress-popular-posts'),
          value: 'all'
        }, {
          label: __('Custom', 'wordpress-popular-posts'),
          value: 'custom'
        }],
        onChange: onTimeRangeChange
      }), 'custom' == attributes.range && /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(TextControl, {
        label: __('Time Quantity', 'wordpress-popular-posts'),
        value: attributes.time_quantity,
        onChange: onTimeQuantityChange
      }), /*#__PURE__*/React.createElement(SelectControl, {
        label: __('Time Unit', 'wordpress-popular-posts'),
        value: attributes.time_unit,
        options: [{
          label: __('Minute(s)', 'wordpress-popular-posts'),
          value: 'minute'
        }, {
          label: __('Hour(s)', 'wordpress-popular-posts'),
          value: 'hour'
        }, {
          label: __('Day(s)', 'wordpress-popular-posts'),
          value: 'day'
        }],
        onChange: onTimeUnitChange
      })), /*#__PURE__*/React.createElement(CheckboxControl, {
        label: __('Display only posts published within the selected Time Range', 'wordpress-popular-posts'),
        checked: attributes.freshness,
        onChange: onFreshnessChange
      }), /*#__PURE__*/React.createElement("p", {
        className: "not-a-legend"
      }, /*#__PURE__*/React.createElement("strong", null, __('Filters', 'wordpress-popular-posts'))), /*#__PURE__*/React.createElement(TextControl, {
        label: __('Post type(s)', 'wordpress-popular-posts'),
        help: __('Post types must be comma separated.', 'wordpress-popular-posts'),
        value: attributes.post_type,
        onChange: onPostTypeChange
      }), /*#__PURE__*/React.createElement(TextControl, {
        label: __('Post ID(s) to exclude', 'wordpress-popular-posts'),
        help: __('IDs must be comma separated.', 'wordpress-popular-posts'),
        value: attributes.pid,
        onChange: onPostIDExcludeChange
      }), /*#__PURE__*/React.createElement(TextControl, {
        label: __('Author ID(s)', 'wordpress-popular-posts'),
        help: __('IDs must be comma separated.', 'wordpress-popular-posts'),
        value: attributes.author,
        onChange: onAuthorChange
      }), /*#__PURE__*/React.createElement("p", {
        className: "not-a-legend"
      }, /*#__PURE__*/React.createElement("strong", null, __('HTML Markup settings', 'wordpress-popular-posts'))), /*#__PURE__*/React.createElement(SelectControl, {
        label: __('Theme', 'wordpress-popular-posts'),
        value: attributes.theme,
        options: themes,
        onChange: onThemeChange
      })), !this.state.editMode && /*#__PURE__*/React.createElement(Disabled, null, /*#__PURE__*/React.createElement(ServerSideRender, {
        block: this.props.name,
        attributes: {
          title: attributes.title,
          limit: attributes.limit,
          offset: attributes.offset,
          order_by: attributes.order_by,
          range: attributes.range,
          time_quantity: attributes.time_quantity,
          time_unit: attributes.time_unit,
          freshness: attributes.freshness,
          post_type: attributes.post_type,
          pid: attributes.pid,
          author: attributes.author,
          theme: attributes.theme
        }
      })))];
    }
  }]);

  return WPPWidgetBlockEdit;
}(Component);

/***/ }),

/***/ "./src/Block/Widget/widget.js":
/*!************************************!*\
  !*** ./src/Block/Widget/widget.js ***!
  \************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _icons__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../icons */ "./src/Block/icons.js");
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./edit */ "./src/Block/Widget/edit.js");


var registerBlockType = wp.blocks.registerBlockType;
var __ = wp.i18n.__;
registerBlockType('wordpress-popular-posts/widget', {
  title: 'WordPress Popular Posts',
  category: 'widgets',
  icon: _icons__WEBPACK_IMPORTED_MODULE_0__["default"].flame,
  description: __('A highly customizable block that displays your most popular posts.', 'wordpress-popular-posts'),
  keywords: ['popular', 'posts', 'trending', 'popularity'],
  attributes: {
    _editMode: {
      type: 'boolean',
      "default": true
    },
    title: {
      type: 'string'
    },
    limit: {
      type: 'number',
      "default": 10
    },
    offset: {
      type: 'number',
      "default": 0
    },
    order_by: {
      type: 'string',
      "default": 'views'
    },
    range: {
      type: 'string',
      "default": 'last24hours'
    },
    time_quantity: {
      type: 'number',
      "default": 24
    },
    time_unit: {
      type: 'string',
      "default": 'hour'
    },
    freshness: {
      type: 'boolean',
      "default": false
    },
    post_type: {
      type: 'string',
      "default": 'post'
    },
    pid: {
      type: 'string',
      "default": ''
    },
    author: {
      type: 'string',
      "default": ''
    },
    theme: {
      type: 'string',
      "default": ''
    }
  },
  supports: {},
  edit: _edit__WEBPACK_IMPORTED_MODULE_1__["WPPWidgetBlockEdit"],
  save: function save() {
    return null;
  }
});

/***/ }),

/***/ "./src/Block/icons.js":
/*!****************************!*\
  !*** ./src/Block/icons.js ***!
  \****************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
var icons = {};
icons.flame = /*#__PURE__*/React.createElement("svg", {
  viewBox: "0 0 248 379"
}, /*#__PURE__*/React.createElement("path", {
  fill: "#fff",
  d: "M-83,96q0-122.5,0-245H165q0,131,0,262a31.87,31.87,0,0,1-.95-4.33A123.87,123.87,0,0,0,153.47,68.3c-12.28-27.74-31.1-50.64-53-71.21C75.67-26.13,55.85-52,54.32-87.87c-.79-18.47.81-36.24,11.59-52.15,1.08-1.59.38-4.4.5-6.64-2.43.1-5.5-.7-7.18.47a140.91,140.91,0,0,0-17.12,13.72C19.49-110.67,3-84.6-9.51-56A149,149,0,0,0-21.86-3.77c-2,39.4,11.38,73.46,36.17,103.51,1.74,2.11,3.51,4.2,5.27,6.3l-.67,1.07c-3.94-1.07-8-1.83-11.82-3.24C-25.17,91.94-52.36,58.57-51.12,21c.1-2.91.21-6.45-3.51-6.49-2,0-4.76,2.16-5.79,4.09-9.4,17.55-16.35,36-19.73,55.73C-81.38,81.49-82.07,88.76-83,96Z",
  transform: "translate(83 149)"
}), /*#__PURE__*/React.createElement("path", {
  fill: "#ba2f2f",
  d: "M-83,96c.93-7.24,1.62-14.51,2.85-21.7,3.38-19.69,10.33-38.18,19.73-55.73,1-1.93,3.83-4.11,5.79-4.09,3.72,0,3.61,3.58,3.51,6.49-1.25,37.59,25.94,71,58.2,82.89,3.82,1.41,7.87,2.18,11.82,3.24l.67-1.07c-1.76-2.1-3.52-4.19-5.27-6.3C-10.49,69.68-23.88,35.63-21.86-3.77A149,149,0,0,1-9.51-56c12.48-28.62,29-54.69,51.62-76.5a140.91,140.91,0,0,1,17.12-13.72c1.68-1.18,4.75-.37,7.18-.47-.13,2.24.58,5-.5,6.64-10.78,15.9-12.37,33.68-11.59,52.15,1.53,35.89,21.35,61.74,46.11,85,21.94,20.57,40.76,43.47,53,71.21a123.87,123.87,0,0,1,10.59,40.36A31.87,31.87,0,0,0,165,113v9c-.7,4.24-1.17,8.54-2.13,12.73-10.74,46.51-37.08,78.75-84.34,91.58C72.16,228,65.52,228.79,59,230H43a25.19,25.19,0,0,0-3.12-1.18c-10-2.37-20.21-4.12-30-7.12-45.83-14-75.19-44.64-89-90.24-2.28-7.52-2.64-15.63-3.88-23.46Q-83,102-83,96ZM61.63-143.61c-6.24,5.39-12.87,10.38-18.64,16.22A229,229,0,0,0-8.77-46.26,138.37,138.37,0,0,0-16.63,23c4.69,32.54,20.21,59.59,42.4,83.23,1.34,1.43,2.7,2.83,4.8,5-15.23,1-28-3.3-39.74-10.64-29.74-18.62-46-45.23-46.8-81a138.75,138.75,0,0,0-7.46,14.67A178.29,178.29,0,0,0-78.24,93.09C-80.9,129.7-68,160.25-42.78,185.71c28.91,29.16,65.19,41.42,105.43,38.91,43.82-2.73,80.34-35.08,93.53-79.39,8.68-29.18,3.11-56.71-10.29-83.15C134.15,38.92,117.71,19.34,99,1.57,85-11.65,71.34-25.28,62.72-42.69,46.33-75.79,44.36-109.22,61.63-143.61Z",
  transform: "translate(83 149)"
}), /*#__PURE__*/React.createElement("path", {
  fill: "#fff",
  d: "M-83,108c1.25,7.84,1.61,15.94,3.88,23.46,13.79,45.6,43.15,76.21,89,90.24,9.82,3,20,4.76,30,7.12A25.19,25.19,0,0,1,43,230H-83Q-83,169-83,108Z",
  transform: "translate(83 149)"
}), /*#__PURE__*/React.createElement("path", {
  fill: "#fff",
  d: "M59,230c6.52-1.21,13.16-2,19.53-3.69,47.26-12.83,73.6-45.07,84.34-91.58,1-4.18,1.43-8.48,2.13-12.73V230Z",
  transform: "translate(83 149)"
}), /*#__PURE__*/React.createElement("path", {
  fill: "#ba2f2f",
  d: "M61.63-143.61c-17.28,34.39-15.3,67.82,1.09,100.92C71.34-25.28,85-11.65,99,1.57c18.75,17.77,35.2,37.35,46.94,60.51,13.4,26.44,19,54,10.29,83.15-13.18,44.31-49.71,76.66-93.53,79.39-40.25,2.51-76.52-9.75-105.43-38.91C-68,160.25-80.9,129.7-78.24,93.09A178.29,178.29,0,0,1-63.45,34.31,138.75,138.75,0,0,1-56,19.64c.77,35.79,17.06,62.4,46.8,81C2.54,108,15.33,112.3,30.56,111.3c-2.1-2.21-3.46-3.62-4.8-5C3.57,82.62-11.94,55.57-16.63,23A138.37,138.37,0,0,1-8.77-46.26,229,229,0,0,1,43-127.38C48.76-133.23,55.39-138.22,61.63-143.61Z",
  transform: "translate(83 149)"
}));
/* harmony default export */ __webpack_exports__["default"] = (icons);

/***/ }),

/***/ "./src/Block/utils.js":
/*!****************************!*\
  !*** ./src/Block/utils.js ***!
  \****************************/
/*! exports provided: sanitize_text_field */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "sanitize_text_field", function() { return sanitize_text_field; });
function sanitize_text_field(value) {
  var decoder = document.createElement('div');
  decoder.innerHTML = value;
  var sanitized = decoder.textContent;
  return sanitized;
}

/***/ })

/******/ });
//# sourceMappingURL=block-wpp-widget.js.map