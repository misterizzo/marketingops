/*!
 * Variation Swatches for WooCommerce
 *
 * Author: Emran Ahmed ( emran.bd.08@gmail.com )
 * Date: 2/16/2025, 5:38:38 PM
 * Released under the GPLv3 license.
 */
/******/ (function() { // webpackBootstrap
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other entry modules.
!function() {
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e2) { throw _e2; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e3) { didErr = true; err = _e3; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }
function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }
function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }
function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }
function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }
function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }
// ================================================================
// WooCommerce Variation Swatches
// ================================================================
/*global wp, _, wc_add_to_cart_variation_params, woo_variation_swatches_options */

(function (window) {
  'use strict';

  /**
   * Converts various string/number/boolean inputs to their boolean equivalent
   * Mimics PHP's filter_var($var, FILTER_VALIDATE_BOOLEAN) behavior
   *
   * Returns true for:
   * - true
   * - "true", "yes", "on", "1" (case-insensitive)
   * - 1, "1"
   *
   * Returns false for:
   * - false
   * - "false", "no", "off", "0" (case-insensitive)
   * - 0, "0"
   * - null, undefined
   * - empty string
   * - any other value
   *
   * @param {*} value - The value to convert to boolean
   * @returns {boolean} - The boolean equivalent of the input
   */
  function filterBoolean(value) {
    // Handle null, undefined, and empty string
    if (!value) {
      return false;
    }

    // Handle actual boolean values
    if (typeof value === 'boolean') {
      return value;
    }

    // Convert to string and lowercase for consistent comparison
    var strValue = String(value).toLowerCase().trim();

    // Values that should return true
    var trueValues = ['1', 'true', 'yes', 'on'];
    if (trueValues.includes(strValue)) {
      return true;
    }

    // Handle numeric 1
    if (value === 1) {
      return true;
    }

    // All other values return false
    return false;
  }
  function isWooVariationSwatchesAPIRequest(options) {
    return !!options.path && options.path.indexOf('woo-variation-swatches') !== -1 || !!options.url && options.url.indexOf('woo-variation-swatches') !== -1;
  }
  window.createMiddlewareForExtraQueryParams = function () {
    var args = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
    return function (options, next) {
      if (isWooVariationSwatchesAPIRequest(options) && Object.keys(args).length > 0) {
        for (var _i = 0, _Object$entries = Object.entries(args); _i < _Object$entries.length; _i++) {
          var _Object$entries$_i = _slicedToArray(_Object$entries[_i], 2),
            key = _Object$entries$_i[0],
            value = _Object$entries$_i[1];
          if (typeof options.url === 'string' && !wp.url.hasQueryArg(options.url, key)) {
            options.url = wp.url.addQueryArgs(options.url, _defineProperty({}, key, value));
          }
          if (typeof options.path === 'string' && !wp.url.hasQueryArg(options.path, key)) {
            options.path = wp.url.addQueryArgs(options.path, _defineProperty({}, key, value));
          }
        }
      }
      return next(options);
    };
  };

  /*
  wp.apiFetch.use((options, next) =>  createMiddlewareForExtraQueryParams({'lang':'en'}));
  */

  var Plugin = function ($) {
    return /*#__PURE__*/function () {
      function _class2(element, options, name) {
        _classCallCheck(this, _class2);
        _defineProperty(this, "defaults", {});
        // Assign
        this.name = name;
        this.element = element;
        this.$element = $(element);
        this.settings = $.extend(true, {}, this.defaults, options);
        this.product_variations = this.$element.data('product_variations') || [];
        this.is_ajax_variation = this.product_variations.length < 1;
        this.product_id = this.$element.data('product_id');
        this.reset_variations = this.$element.find('.reset_variations');
        this.attributeFields = this.$element.find('.variations select');
        this.attributeSwatchesFields = this.$element.find('ul.variable-items-wrapper');
        this.selected_item_template = "<span class=\"woo-selected-variation-item-name\" data-default=\"\"></span>";
        this.$element.addClass('wvs-loaded');

        // Call
        this.init();
        this.update();

        // Trigger
        $(document).trigger('woo_variation_swatches_loaded', this);
      }
      _createClass(_class2, [{
        key: "isAjaxVariation",
        value: function isAjaxVariation() {
          //this.product_variations = this.$element.data('product_variations') || []
          return this.is_ajax_variation; // = this.product_variations.length < 1
        }
      }, {
        key: "init",
        value: function init() {
          this.prepareLabel();
          this.prepareItems();
          this.setupItems();
          this.setupEvents();
          this.setUpStockInfo();
          this.deselectNonAvailable();
        }
      }, {
        key: "prepareLabel",
        value: function prepareLabel() {
          var _this = this;
          // Append Selected Item Template
          if (filterBoolean(woo_variation_swatches_options.show_variation_label)) {
            this.$element.find('.variations .label').each(function (index, el) {
              $(el).append(_this.selected_item_template);
            });
          }
        }
      }, {
        key: "prepareItems",
        value: function prepareItems() {
          this.attributeSwatchesFields.each(function (i, el) {
            $(el).parent().addClass('woo-variation-items-wrapper');
          });
        }
      }, {
        key: "setupItems",
        value: function setupItems() {
          var _this2 = this;
          var self = this;
          this.attributeSwatchesFields.each(function (i, element) {
            var selected = '';
            var select = $(element).parent().find('select.woo-variation-raw-select');
            var available_options = select.find('option');
            var disabled_out_of_stock_options = select.find('option:disabled');
            var enabled_out_of_stock_options = select.find('option.enabled.out-of-stock');
            var current = select.find('option:selected');
            var eq = select.find('option').eq(1);
            var attribute_name = $(element).data('attribute_name');
            var all_options = $(element).data('attribute_values');
            var current_options = [];
            var available_out_of_stocks = [];
            var disabled_out_of_stocks = [];

            // Available Options
            available_options.each(function () {
              if ($(this).val() !== '') {
                current_options.push($(this).val());
                selected = current.length === 0 ? eq.val() : current.val();
              }
            });

            // Settings: Disabled Out Of Stock Item
            disabled_out_of_stock_options.each(function () {
              if ($(this).val() !== '') {
                disabled_out_of_stocks.push($(this).val());
              }
            });

            // Settings: Enabled Out Of Stock Item
            enabled_out_of_stock_options.each(function () {
              if ($(this).val() !== '') {
                available_out_of_stocks.push($(this).val());
              }
            });
            var in_stocks = _.difference(current_options, disabled_out_of_stocks);
            var disabled_values = [].concat(_toConsumableArray(_.difference(all_options, current_options)), disabled_out_of_stocks);
            _this2.setupItem(element, selected, in_stocks, available_out_of_stocks, disabled_values);
          });
        }
      }, {
        key: "setupItem",
        value: function setupItem(element, selected, in_stocks, out_of_stocks, disabled_values) {
          var _this3 = this;
          var $selected_variation_item = $(element).parent().prev().find('.woo-selected-variation-item-name');

          // Mark Selected
          $(element).find('li.variable-item').each(function (index, el) {
            var attribute_value = $(el).attr('data-value');
            var attribute_title = $(el).attr('data-title');

            // Resetting LI
            $(el).removeClass('selected disabled no-stock').addClass('disabled');
            $(el).attr('aria-checked', 'false');
            $(el).attr('tabindex', '-1');
            $(el).attr('data-wvstooltip-out-of-stock', '');
            $(el).find('input.variable-item-radio-input:radio').prop('disabled', true).prop('checked', false);

            // To Prevent blink
            if (selected.length < 1 && filterBoolean(woo_variation_swatches_options.show_variation_label)) {
              $selected_variation_item.text('');
            }

            // Ajax variation
            if (_this3.isAjaxVariation()) {
              $(el).find('input.variable-item-radio-input:radio').prop('disabled', false);
              $(el).removeClass('selected disabled no-stock');

              // Selected
              if (attribute_value === selected) {
                $(el).addClass('selected');
                $(el).attr('aria-checked', 'true');
                $(el).attr('tabindex', '0');
                $(el).find('input.variable-item-radio-input:radio').prop('disabled', false).prop('checked', true);
                if (filterBoolean(woo_variation_swatches_options.show_variation_label)) {
                  $selected_variation_item.text("".concat(woo_variation_swatches_options.variation_label_separator, " ").concat(attribute_title));
                }
                $(el).trigger('wvs-item-updated', [selected, attribute_value]);
              }
            } else {
              // Default Selected
              // We can't use es6 includes for IE11
              // in_stocks.includes(attribute_value)
              // _.contains(in_stocks, attribute_value)
              // _.includes(in_stocks, attribute_value)

              if (_.includes(in_stocks, attribute_value)) {
                $(el).removeClass('selected disabled');
                $(el).removeAttr('aria-hidden');
                $(el).attr('tabindex', '0');
                $(el).find('input.variable-item-radio-input:radio').prop('disabled', false);

                // Selected
                if (attribute_value === selected) {
                  $(el).addClass('selected');
                  $(el).attr('aria-checked', 'true');
                  $(el).find('input.variable-item-radio-input:radio').prop('checked', true);
                  if (filterBoolean(woo_variation_swatches_options.show_variation_label)) {
                    $selected_variation_item.text("".concat(woo_variation_swatches_options.variation_label_separator, " ").concat(attribute_title));
                  }
                  $(el).trigger('wvs-item-updated', [selected, attribute_value]);
                }
              }

              // Out of Stock
              if (_.includes(out_of_stocks, attribute_value)) {
                $(el).attr('data-wvstooltip-out-of-stock', woo_variation_swatches_options.out_of_stock_tooltip_text);
                if (filterBoolean(woo_variation_swatches_options.clickable_out_of_stock)) {
                  $(el).removeClass('disabled').addClass('no-stock');
                }
              }

              // Disabled
              if (_.includes(disabled_values, attribute_value)) {
                $(el).attr('data-wvstooltip-out-of-stock', woo_variation_swatches_options.unavailable_tooltip_text);
              }
            }
          });
        }
      }, {
        key: "setupEvents",
        value: function setupEvents() {
          var $element = this.$element;
          this.attributeSwatchesFields.each(function (i, element) {
            var select = $(element).parent().find('select.woo-variation-raw-select');

            // Trigger Select event based on list
            if (filterBoolean(woo_variation_swatches_options.clear_on_reselect)) {
              // Non Selected Item Should Select
              $(element).on('click.wvs', 'li.variable-item:not(.selected):not(.radio-variable-item)', function (event) {
                event.preventDefault();
                event.stopPropagation();
                var attribute_name = $(this).data('attribute_name');
                var attribute_value = $(this).data('value');
                select.val(attribute_value).trigger('change');
                select.trigger('click');
                $(this).trigger('wvs-selected-item', [attribute_name, attribute_value, select, $element]); // Custom Event for li
              });

              // Selected Item Should un Select
              $(element).on('click.wvs', 'li.variable-item.selected:not(.radio-variable-item)', function (event) {
                event.preventDefault();
                event.stopPropagation();
                var attribute_name = $(this).data('attribute_name');
                var attribute_value = $(this).data('value');
                select.val('').trigger('change');
                select.trigger('click');
                $(this).trigger('wvs-unselected-item', [attribute_name, attribute_value, select, $element]); // Custom Event for li
              });

              // RADIO

              // On Click trigger change event on Radio button
              $(element).on('click.wvs', 'input.variable-item-radio-input:radio', function (event) {
                event.stopPropagation();
                $(this).trigger('change.wvs', {
                  radioChange: true
                });
              });
              $(element).on('change.wvs', 'input.variable-item-radio-input:radio', function (event, params) {
                event.preventDefault();
                event.stopPropagation();
                if (params && params.radioChange) {
                  var attribute_name = $(this).data('attribute_name');
                  var attribute_value = $(this).val();
                  var is_selected = $(this).parent('li.radio-variable-item').hasClass('selected');
                  if (is_selected) {
                    select.val('').trigger('change');
                    $(this).closest('li.radio-variable-item').trigger('wvs-unselected-item', [attribute_name, attribute_value, select, $element]); // Custom Event for li
                  } else {
                    select.val(attribute_value).trigger('change');
                    $(this).closest('li.radio-variable-item').trigger('wvs-selected-item', [attribute_name, attribute_value, select, $element]); // Custom Event for li
                  }
                  select.trigger('click');
                }
              });
            } else {
              $(element).on('click.wvs', 'li.variable-item:not(.radio-variable-item)', function (event) {
                event.preventDefault();
                event.stopPropagation();
                var attribute_name = $(this).data('attribute_name');
                var attribute_value = $(this).data('value');
                select.val(attribute_value).trigger('change');
                select.trigger('click');
                $(this).trigger('wvs-selected-item', [attribute_name, attribute_value, select, $element]); // Custom Event for li
              });

              // Radio
              $(element).on('change.wvs', 'input.variable-item-radio-input:radio', function (event) {
                event.preventDefault();
                event.stopPropagation();
                var attribute_name = $(this).data('attribute_name');
                var attribute_value = $(this).val();
                select.val(attribute_value).trigger('change');
                select.trigger('click');

                // Radio
                $(this).closest('li.radio-variable-item').removeClass('selected disabled no-stock').addClass('selected');
                $(this).closest('li.radio-variable-item').trigger('wvs-selected-item', [attribute_name, attribute_value, select, $element]); // Custom Event for li
              });
            }

            // Keyboard Access
            $(element).on('keydown.wvs', 'li.variable-item:not(.disabled)', function (event) {
              if (event.keyCode && 32 === event.keyCode || event.key && ' ' === event.key || event.keyCode && 13 === event.keyCode || event.key && 'enter' === event.key.toLowerCase()) {
                event.preventDefault();
                $(this).trigger('click');
              }
            });
          });
          this.$element.on('click.wvs', '.woo-variation-swatches-variable-item-more', function (event) {
            event.preventDefault();
            $(this).parent().removeClass('enabled-display-limit-mode enabled-catalog-display-limit-mode');
            $(this).remove();
          });
          this.$element.find('[data-wvstooltip]').each(function (i, element) {
            $(element).on('mouseenter', function (event) {
              var rect = element.getBoundingClientRect();
              var tooltip = window.getComputedStyle(element, ':before');
              var arrow = window.getComputedStyle(element, ':after');
              var arrowHeight = parseInt(arrow.getPropertyValue('border-top-width'), 10);
              var tooltipHeight = parseInt(tooltip.getPropertyValue('height'), 10);
              var tooltipWidth = parseInt(tooltip.getPropertyValue('width'), 10);
              var offset = 2;
              var calculateTooltipPosition = tooltipHeight + arrowHeight + offset;
              element.classList.toggle('wvs-tooltip-position-bottom', rect.top < calculateTooltipPosition);
              var width = tooltipWidth / 2;
              var position = rect.left + rect.width / 2;

              // Left
              var left = width - position;
              var isLeft = width > position;
              var computedRight = width + position;
              var isRight = document.body.clientWidth < computedRight;
              var right = document.body.clientWidth - computedRight;
              element.style.setProperty('--horizontal-position', "0px");
              if (isLeft) {
                element.style.setProperty('--horizontal-position', "".concat(left + offset, "px"));
              }
              if (isRight) {
                element.style.setProperty('--horizontal-position', "".concat(right - offset, "px"));
              }
              //
            });
          });
        }
      }, {
        key: "extractAttributes",
        value: function extractAttributes(selected) {
          var result = new Set();
          var _iterator = _createForOfIteratorHelper(this.product_variations),
            _step;
          try {
            for (_iterator.s(); !(_step = _iterator.n()).done;) {
              var variation = _step.value;
              var attributes = variation.attributes;
              for (var attribute_name in attributes) {
                if (attributes[attribute_name].length > 0) {
                  result.add(attribute_name);
                }
              }
            }
          } catch (err) {
            _iterator.e(err);
          } finally {
            _iterator.f();
          }
          result["delete"](selected);
          return Array.from(result);
        }
      }, {
        key: "getUnavailableAttributes",
        value: function getUnavailableAttributes(currentChosen, selected) {
          var availableVariations = this.findMatchingVariations(this.product_variations, currentChosen).filter(function (variation) {
            if (filterBoolean(woo_variation_swatches_options.disable_out_of_stock)) {
              return variation.is_in_stock;
            }
            return true;
          });
          if (availableVariations.length === 0) {
            return this.extractAttributes(selected);
          }
          return [];
        }
      }, {
        key: "deselectNonAvailable",
        value: function deselectNonAvailable() {
          var _this4 = this;
          if (!filterBoolean(woo_variation_swatches_options.deselect_unavailable)) {
            return;
          }
          this.$element.on('wvs-selected-item.wvs', function (event, attribute_name, attribute_value) {
            var _this4$getChosenAttri = _this4.getChosenAttributes(),
              data = _this4$getChosenAttri.data;
            var currentAttribute = _objectSpread(_objectSpread({}, data), {}, _defineProperty({}, attribute_name, attribute_value));
            var unavailableAttributes = _this4.getUnavailableAttributes(currentAttribute, attribute_name);
            if (unavailableAttributes.length > 0) {
              var _iterator2 = _createForOfIteratorHelper(unavailableAttributes),
                _step2;
              try {
                for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
                  var unavailable = _step2.value;
                  // Unselect unmatched
                  _this4.attributeSwatchesFields.find("li[data-attribute_name=\"".concat(unavailable, "\"]")).removeClass('selected');
                  _this4.attributeFields.filter("[data-attribute_name=\"".concat(unavailable, "\"]")).each(function (i, el) {
                    $(el).val('').trigger('change');
                  });
                }

                // Select current
              } catch (err) {
                _iterator2.e(err);
              } finally {
                _iterator2.f();
              }
              _this4.attributeSwatchesFields.filter("[data-attribute_name=\"".concat(attribute_name, "\"]")).each(function () {
                $(this).find("[data-value=\"".concat(attribute_value, "\"]")).removeClass('disabled').addClass('selected');
              });
              _this4.attributeFields.filter("[data-attribute_name=\"".concat(attribute_name, "\"]")).each(function (i, el) {
                $(el).val(attribute_value).trigger('change');
              });
            }
          });
        }
      }, {
        key: "update",
        value: function update() {
          var _this5 = this;
          // this.$element.off('woocommerce_variation_has_changed.wvs')
          this.$element.on('woocommerce_variation_has_changed.wvs', function (event) {
            // Don't use any propagation. It will disable composite product functionality
            // event.stopPropagation();

            _this5.setupItems();
          });
        }
      }, {
        key: "setUpStockInfo",
        value: function setUpStockInfo() {
          var _this6 = this;
          if (filterBoolean(woo_variation_swatches_options.show_variation_stock)) {
            var max_stock_label = parseInt(woo_variation_swatches_options.stock_label_threshold, 10);
            this.$element.on('wvs-selected-item.wvs', function (event) {
              var attributes = _this6.getChosenAttributes();
              var variations = _this6.findStockVariations(_this6.product_variations, attributes);
              if (attributes.count > 1 && attributes.count === attributes.chosenCount) {
                _this6.resetStockInfo();
              }
              if (attributes.count > 1 && attributes.count === attributes.mayChosenCount) {
                variations.forEach(function (data) {
                  var stockInfoSelector = "[data-attribute_name=\"".concat(data.attribute_name, "\"] > [data-value=\"").concat(data.attribute_value, "\"]");
                  if (data.variation.is_in_stock && data.variation.max_qty && data.variation.variation_stock_left && data.variation.max_qty <= max_stock_label) {
                    _this6.$element.find("".concat(stockInfoSelector, " .wvs-stock-left-info")).attr('data-wvs-stock-info', data.variation.variation_stock_left);
                    _this6.$element.find(stockInfoSelector).addClass('wvs-show-stock-left-info');
                  } else {
                    _this6.$element.find(stockInfoSelector).removeClass('wvs-show-stock-left-info');
                    _this6.$element.find("".concat(stockInfoSelector, " .wvs-stock-left-info")).attr('data-wvs-stock-info', '');
                  }
                });
              }
            });
            this.$element.on('hide_variation.wvs', function () {
              _this6.resetStockInfo();
            });
          }
        }
      }, {
        key: "resetStockInfo",
        value: function resetStockInfo() {
          this.$element.find('.variable-item').removeClass('wvs-show-stock-left-info');
          this.$element.find('.wvs-stock-left-info').attr('data-wvs-stock-info', '');
        }
      }, {
        key: "getChosenAttributes",
        value: function getChosenAttributes() {
          var data = {};
          var count = 0;
          var chosen = 0;
          this.attributeFields.each(function () {
            var attribute_name = $(this).data('attribute_name') || $(this).attr('name');
            var value = $(this).val() || '';
            if (value.length > 0) {
              chosen++;
            }
            count++;
            data[attribute_name] = value;
          });
          return {
            'count': count,
            'chosenCount': chosen,
            'mayChosenCount': chosen + 1,
            'data': data
          };
        }
      }, {
        key: "findStockVariations",
        value: function findStockVariations(allVariations, selectedAttributes) {
          var found = [];
          for (var _i2 = 0, _Object$entries2 = Object.entries(selectedAttributes.data); _i2 < _Object$entries2.length; _i2++) {
            var _Object$entries2$_i = _slicedToArray(_Object$entries2[_i2], 2),
              attribute_name = _Object$entries2$_i[0],
              attribute_value = _Object$entries2$_i[1];
            if (attribute_value.length === 0) {
              var values = this.$element.find("ul[data-attribute_name='".concat(attribute_name, "']")).data('attribute_values') || [];
              var _iterator3 = _createForOfIteratorHelper(values),
                _step3;
              try {
                for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
                  var value = _step3.value;
                  var compare = _.extend(selectedAttributes.data, _defineProperty({}, attribute_name, value));
                  var matched_variation = this.findMatchingVariations(allVariations, compare);
                  if (matched_variation.length > 0) {
                    var variation = matched_variation.shift();
                    var data = {};
                    data['attribute_name'] = attribute_name;
                    data['attribute_value'] = value;
                    data['variation'] = variation;
                    found.push(data);
                  }
                }
              } catch (err) {
                _iterator3.e(err);
              } finally {
                _iterator3.f();
              }
            }
          }
          return found;
        }
      }, {
        key: "findMatchingVariations",
        value: function findMatchingVariations(variations, attributes) {
          var matching = [];
          for (var i = 0; i < variations.length; i++) {
            var variation = variations[i];
            if (this.isMatch(variation.attributes, attributes)) {
              matching.push(variation);
            }
          }
          return matching;
        }
      }, {
        key: "findMatchingVariations2",
        value: function findMatchingVariations2(variations, attributes) {
          return variations.filter(function (variation) {
            return Object.entries(attributes).every(function (_ref) {
              var _ref2 = _slicedToArray(_ref, 2),
                attr_name = _ref2[0],
                attr_value = _ref2[1];
              var var_attr_value = variation.attributes[attr_name];
              var notMatch = var_attr_value !== undefined && attr_value !== undefined && var_attr_value.length !== 0 && attr_value.length !== 0 && var_attr_value !== attr_value;
              return !notMatch;
            });
          });
        }
      }, {
        key: "isMatch",
        value: function isMatch(variation_attributes, attributes) {
          var match = true;
          for (var attr_name in variation_attributes) {
            if (variation_attributes.hasOwnProperty(attr_name)) {
              var val1 = variation_attributes[attr_name];
              var val2 = attributes[attr_name];
              if (val1 !== undefined && val2 !== undefined && val1.length !== 0 && val2.length !== 0 && val1 !== val2) {
                match = false;
              }
            }
          }
          return match;
        }
      }, {
        key: "destroy",
        value: function destroy() {
          this.$element.removeClass('wvs-loaded');
          this.$element.removeData(this.name);
        }
      }]);
      return _class2;
    }();
  }(jQuery);
  var jQueryPlugin = function ($) {
    return function (PluginName, ClassName) {
      $.fn[PluginName] = function (options) {
        var _this7 = this;
        for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
          args[_key - 1] = arguments[_key];
        }
        return this.each(function (index, element) {
          var $element = $(element);
          // let $element = $(this)

          var data = $element.data(PluginName);
          if (!data) {
            data = new ClassName($element, $.extend({}, options), PluginName);
            $element.data(PluginName, data);
          }
          if (typeof options === 'string') {
            if (_typeof(data[options]) === 'object') {
              return data[options];
            }
            if (typeof data[options] === 'function') {
              var _data;
              return (_data = data)[options].apply(_data, args);
            }
          }
          return _this7;
        });
      };

      // Constructor
      $.fn[PluginName].Constructor = ClassName;

      // Short hand
      $[PluginName] = function (options) {
        var _$;
        for (var _len2 = arguments.length, args = new Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {
          args[_key2 - 1] = arguments[_key2];
        }
        return (_$ = $({}))[PluginName].apply(_$, [options].concat(args));
      };

      // No Conflict
      $.fn[PluginName].noConflict = function () {
        return $.fn[PluginName];
      };
    };
  }(jQuery);
  jQueryPlugin('WooVariationSwatches', Plugin);
})(window);
}();
// This entry need to be wrapped in an IIFE because it need to be isolated against other entry modules.
!function() {
/*global wp, _, wc_add_to_cart_variation_params, woo_variation_swatches_options */
jQuery(function ($) {
  try {
    $(document).on('woo_variation_swatches_init', function () {
      $('.variations_form:not(.wvs-loaded)').WooVariationSwatches(); // Any custom product with .woo_variation_swatches_variations_form class to support

      $('.woo_variation_swatches_variations_form:not(.wvs-loaded)').WooVariationSwatches(); // Yith Composite Product

      $('.ywcp_inner_selected_container:not(.wvs-loaded)').WooVariationSwatches();
    }); //.trigger('woo_variation_swatches_init')
  } catch (err) {
    // If failed (conflict?) log the error but don't stop other scripts breaking.
    window.console.log('Variation Swatches:', err);
  } // Init WooVariationSwatches after wc_variation_form script loaded.


  $(document).on('wc_variation_form.wvs', function (event) {
    $(document).trigger('woo_variation_swatches_init');
  }); // Try to cover global ajax data complete

  $(document).ajaxComplete(function (event, request, settings) {
    _.delay(function () {
      $('.variations_form:not(.wvs-loaded)').each(function () {
        $(this).wc_variation_form();
      });
    }, 1000);
  }); // Composite Product Load
  // JS API: https://docs.woocommerce.com/document/composite-products/composite-products-js-api-reference/

  $(document.body).on('wc-composite-initializing', '.composite_data', function (event, composite) {
    composite.actions.add_action('component_options_state_changed', function (self) {
      $(self.$component_content).find('.variations_form').WooVariationSwatches('destroy');
    });
    /* composite.actions.add_action('active_scenarios_updated', (self) => {
       console.log('active_scenarios_updated')
       $(self.$component_content).find('.variations_form').removeClass('wvs-loaded wvs-pro-loaded')
     })*/
  });
});
}();
/******/ })()
;