/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./blocks/certificate-builder/index.js":
/*!**********************************************************!*\
  !*** ./blocks/certificate-builder/index.js + 10 modules ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("// ESM COMPAT FLAG\n__webpack_require__.r(__webpack_exports__);\n\n;// CONCATENATED MODULE: external [\"wp\",\"blocks\"]\nconst external_wp_blocks_namespaceObject = window[\"wp\"][\"blocks\"];\n;// CONCATENATED MODULE: external [\"wp\",\"i18n\"]\nconst external_wp_i18n_namespaceObject = window[\"wp\"][\"i18n\"];\n;// CONCATENATED MODULE: ./blocks/certificate-builder/style.scss\n// extracted by mini-css-extract-plugin\n\n;// CONCATENATED MODULE: external \"React\"\nconst external_React_namespaceObject = window[\"React\"];\n;// CONCATENATED MODULE: external [\"wp\",\"blockEditor\"]\nconst external_wp_blockEditor_namespaceObject = window[\"wp\"][\"blockEditor\"];\n;// CONCATENATED MODULE: external [\"wp\",\"components\"]\nconst external_wp_components_namespaceObject = window[\"wp\"][\"components\"];\n;// CONCATENATED MODULE: ./blocks/helper/font.js\n/**\r\n * Build the selector\r\n *\r\n * @param arr\r\n * @param font\r\n * @returns {{list: [], currFont: any}}\r\n */\nfunction getActiveFont(arr, font) {\n  let list = [];\n  let currFont = undefined;\n  if (font === '') {\n    font = arr[0][0];\n  }\n  arr.forEach((v, i) => {\n    let key = v[0];\n    let info = v[1];\n    list.push({\n      label: info.name,\n      value: key\n    });\n    if (key === font) {\n      currFont = info;\n      currFont.key = key;\n    }\n  });\n  // the currentfont is empty, this can happen because\n  if (currFont === undefined) {\n    currFont = arr[0][0];\n  }\n  return {\n    list: list,\n    currFont: currFont\n  };\n}\n\n/**\r\n * Build the font stack styles\r\n *\r\n * @param baseUrl\r\n * @param fonts\r\n * @returns {string}\r\n */\nfunction buildFontStyle(baseUrl, fonts) {\n  let fontStyle = '';\n  for (const [key, v] of Object.entries(fonts)) {\n    let fontUrl;\n    if (v.custom === true) {\n      fontUrl = v['R'];\n    } else {\n      fontUrl = baseUrl + '/' + v['R'];\n    }\n    fontStyle += `\n\t\t@font-face {\n\t\t\tfont-family: \"${key}\";\n\t\t\tsrc: url(\"${fontUrl}\") format('truetype');\n\t\t\tfont-weight: 400;\n\t\t\tfont-style: normal\n\t\t}\n\t`;\n    if (v['B'] !== undefined) {\n      let fontUrl_B = baseUrl + '/' + v['B'];\n      if (v.custom === true) {\n        fontUrl_B = v['B'];\n      }\n      fontStyle += `\n\t\t@font-face {\n\t\t\tfont-family: \"${key}\";\n\t\t\tsrc: url(\"${fontUrl_B}\") format('truetype');\n\t\t\tfont-weight: bold;\n\t\t\tfont-style: normal\n\t\t}\n\t\t`;\n    }\n    if (v['I'] !== undefined) {\n      let fontUrl_I = baseUrl + '/' + v['I'];\n      if (v.custom === true) {\n        fontUrl_I = v['I'];\n      }\n      fontStyle += `\n\t\t@font-face {\n\t\t\tfont-family: \"${key}\";\n\t\t\tsrc: url(\"${fontUrl_I}\") format('truetype');\n\t\t\tfont-weight: 400;\n\t\t\tfont-style: italic\n\t\t}\n\t\t`;\n    }\n    if (v['BI'] !== undefined) {\n      let fontUrl_BI = baseUrl + '/' + v['BI'];\n      if (v.custom === true) {\n        fontUrl_BI = v['BI'];\n      }\n      fontStyle += `\n\t\t@font-face {\n\t\t\tfont-family: \"${key}\";\n\t\t\tsrc: url(\"${fontUrl_BI}\") format('truetype');\n\t\t\tfont-weight: bold;\n\t\t\tfont-style: italic\n\t\t}\n\t\t`;\n    }\n  }\n  return fontStyle;\n}\n;// CONCATENATED MODULE: ./blocks/certificate-builder/editor.scss\n// extracted by mini-css-extract-plugin\n\n;// CONCATENATED MODULE: ./blocks/certificate-builder/edit.js\n\n/**\n * Retrieves the translation of text.\n *\n * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/\n *\n * cspell:ignore freeserif .\n */\n\n/**\n * React hook that is used to mark the block wrapper element.\n * It provides all the necessary props like the class name.\n *\n * @see https://developer.wordpress.org/block-editor/packages/packages-block-editor/#useBlockProps\n */\n\n\n\n/**\n * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.\n * Those files can contain any CSS code that gets applied to the editor.\n *\n * @see https://www.npmjs.com/package/@wordpress/scripts#using-css\n */\n\n\n/**\n * The edit function describes the structure of your block in the context of the\n * editor. This represents what the editor will render when the block is used.\n *\n * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit\n *\n * @return {JSX.Element} Element to render.\n */\nfunction Edit(props) {\n  const {\n    attributes,\n    setAttributes,\n    className\n  } = props;\n  let {\n    id = 0,\n    backgroundImage = '',\n    pageSize = 'LETTER',\n    pageOrientation = 'L',\n    font = '',\n    useFont = false,\n    pageHeight = 0,\n    pageWidth = 0,\n    containerWidth = 70,\n    spacing = 1,\n    rtl = false,\n    viewPort = true\n  } = attributes;\n  const hasBackground = id !== 0;\n  //update post id\n  const onSelectMedia = imageObject => {\n    if (imageObject.type !== 'image' && imageObject.type !== 'attachment') {\n      return;\n    }\n    setAttributes({\n      id: imageObject.id,\n      backgroundImage: imageObject.url\n    });\n    setTimeout(function () {\n      let height = jQuery('#certificate-builder-blocks').height();\n      let width = jQuery('#certificate-builder-blocks').width();\n      setAttributes({\n        pageHeight: height,\n        pageWidth: width\n      });\n    });\n  };\n  const ALLOWED_MEDIA_TYPES = ['image'];\n  const PAGE_SIZES = [{\n    label: (0,external_wp_i18n_namespaceObject.__)('Letter/USLetter (default)', 'learndash-certificate-builder'),\n    value: 'LETTER'\n  }, {\n    label: (0,external_wp_i18n_namespaceObject.__)('A4', 'learndash-certificate-builder'),\n    value: 'A4'\n  }];\n  const PAGE_ORIENTATION = [{\n    label: (0,external_wp_i18n_namespaceObject.__)('Landscape (default)', 'learndash-certificate-builder'),\n    value: 'L'\n  }, {\n    label: (0,external_wp_i18n_namespaceObject.__)('Portrait', 'learndash-certificate-builder'),\n    value: 'P'\n  }];\n  let styles = {};\n  if ('' !== backgroundImage) {\n    styles.position = 'relative';\n  }\n  let useCoreFont = () => {\n    return `#certificate-builder-inner-blocks, #certificate-builder-inner-blocks * {\n\t\tfont-family: \"freeserif\", serif;\n\t}`;\n  };\n  let info = getActiveFont(Object.entries(certificate_builder.fonts), font);\n  if (font === '' || font === null) {\n    setAttributes({\n      font: info.currFont.key\n    });\n  }\n  let fontDefine = buildFontStyle(certificate_builder.font_url, certificate_builder.fonts);\n  let fontStyle = '';\n  if (useFont === false) {\n    fontStyle = useCoreFont();\n  } else {\n    fontStyle = `\n\t#certificate-builder-inner-blocks, #certificate-builder-inner-blocks * {\n\t\tfont-family: \"${info.currFont.key}\", \"freeserif\", serif;\n\t}}\n\t`;\n  }\n  const MyToggleControl = (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.ToggleControl, {\n    label: (0,external_wp_i18n_namespaceObject.__)(\"Use custom font\", 'learndash-certificate-builder'),\n    checked: useFont,\n    onChange: value => {\n      setAttributes({\n        useFont: value\n      });\n    }\n  });\n  const controls = (0,external_React_namespaceObject.createElement)(external_React_namespaceObject.Fragment, null, (0,external_React_namespaceObject.createElement)(external_wp_blockEditor_namespaceObject.BlockControls, null, hasBackground && (0,external_React_namespaceObject.createElement)(external_React_namespaceObject.Fragment, null, (0,external_React_namespaceObject.createElement)(external_wp_blockEditor_namespaceObject.MediaReplaceFlow, {\n    mediaId: id,\n    mediaURL: backgroundImage,\n    allowedTypes: ALLOWED_MEDIA_TYPES,\n    accept: \"image/jpeg, image/png\",\n    onSelect: onSelectMedia\n  }))), (0,external_React_namespaceObject.createElement)(external_wp_blockEditor_namespaceObject.InspectorControls, null, hasBackground && (0,external_React_namespaceObject.createElement)(external_React_namespaceObject.Fragment, null, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.PanelBody, {\n    title: (0,external_wp_i18n_namespaceObject.__)('Spacing', 'learndash-certificate-builder')\n  }, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.RangeControl, {\n    label: (0,external_wp_i18n_namespaceObject.__)(\"Container size (%)\", 'learndash-certificate-builder'),\n    value: containerWidth,\n    min: 50,\n    max: 100,\n    onChange: newSize => {\n      setAttributes({\n        containerWidth: newSize\n      });\n    }\n  }), (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.RangeControl, {\n    label: (0,external_wp_i18n_namespaceObject.__)(\"Margin bottom (rem)\", 'learndash-certificate-builder'),\n    value: spacing,\n    min: 0,\n    max: 10,\n    step: 0.1,\n    onChange: value => {\n      setAttributes({\n        spacing: value\n      });\n    }\n  })), (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.PanelBody, {\n    title: (0,external_wp_i18n_namespaceObject.__)('Options', 'learndash-certificate-builder')\n  }, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.SelectControl, {\n    label: (0,external_wp_i18n_namespaceObject.__)(\"PDF Page Size\", 'learndash-certificate-builder'),\n    value: pageSize,\n    options: PAGE_SIZES,\n    onChange: size => {\n      setAttributes({\n        pageSize: size\n      });\n    }\n  }), (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.SelectControl, {\n    label: (0,external_wp_i18n_namespaceObject.__)(\"PDF Page Orientation\", 'learndash-certificate-builder'),\n    value: pageOrientation,\n    options: PAGE_ORIENTATION,\n    onChange: o => {\n      setAttributes({\n        pageOrientation: o\n      });\n    }\n  }), (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.ToggleControl, {\n    label: (0,external_wp_i18n_namespaceObject.__)(\"Fixed Viewport\", 'learndash-certificate-builder'),\n    checked: viewPort,\n    onChange: value => {\n      setAttributes({\n        viewPort: value\n      });\n    }\n  }), (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.ToggleControl, {\n    label: (0,external_wp_i18n_namespaceObject.__)(\"RTL\", 'learndash-certificate-builder'),\n    checked: rtl,\n    onChange: value => {\n      setAttributes({\n        rtl: value\n      });\n    }\n  })), (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.PanelBody, {\n    title: (0,external_wp_i18n_namespaceObject.__)('Fonts', 'learndash-certificate-builder')\n  }, MyToggleControl, useFont && (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.SelectControl, {\n    label: (0,external_wp_i18n_namespaceObject.__)(\"Font family\", 'learndash-certificate-builder'),\n    value: font,\n    options: info.list,\n    onChange: o => {\n      setAttributes({\n        font: o\n      });\n    }\n  })))));\n  if (!hasBackground) {\n    return (0,external_React_namespaceObject.createElement)(\"div\", null, controls, (0,external_React_namespaceObject.createElement)(\"div\", null, (0,external_React_namespaceObject.createElement)(external_wp_blockEditor_namespaceObject.MediaPlaceholder, {\n      allowedTypes: ALLOWED_MEDIA_TYPES,\n      multiple: false,\n      labels: {\n        title: (0,external_wp_i18n_namespaceObject.__)('Certificate Background', 'learndash-certificate-builder')\n      },\n      onSelect: onSelectMedia,\n      accept: \"image/jpeg, image/png\"\n    })));\n  }\n  let spaceStyle = `#certificate-builder-inner-blocks .wp-block:not(.wp-block-column) {\n      margin-bottom: ${spacing}rem !important;\n    }\n    #certificate-builder-inner-blocks .wp-block:last-child {\n        margin-bottom:0 !important;\n    }\n    `;\n  let sizeRatio = {\n    'LETTER_L': [1056, 816],\n    'LETTER_P': [816, 1056],\n    'A4_L': [1122, 793],\n    'A4_P': [793, 1122]\n  };\n  //we use full width and calculate height\n  let key = pageSize + '_' + pageOrientation;\n  let size = sizeRatio[key];\n  let width = size[0];\n  let height = size[1];\n  styles.width = width + 'px';\n  styles.height = height + 'px';\n  if (viewPort === false) {\n    styles.width = '100%';\n    styles.height = 'auto';\n  }\n  let rtlStyle = '';\n  if (rtl) {\n    rtlStyle = `#certificate-builder-inner-blocks{\n\t\t\tdirection: rtl;\n\t\t}`;\n  }\n  return (0,external_React_namespaceObject.createElement)(\"div\", null, (0,external_React_namespaceObject.createElement)(\"style\", null, spaceStyle, fontDefine, fontStyle, rtlStyle), controls, (0,external_React_namespaceObject.createElement)(\"div\", {\n    id: \"certificate-builder-blocks\",\n    className: className\n  }, (0,external_React_namespaceObject.createElement)(\"div\", {\n    style: styles\n  }, (0,external_React_namespaceObject.createElement)(\"img\", {\n    src: backgroundImage,\n    style: {\n      opacity: 1,\n      width: '100%',\n      height: 'auto'\n    }\n  }), (0,external_React_namespaceObject.createElement)(\"div\", {\n    id: 'certificate-builder-inner-blocks',\n    style: {\n      position: 'absolute',\n      top: 0,\n      width: '100%',\n      height: 'auto'\n    }\n  }, (0,external_React_namespaceObject.createElement)(\"div\", {\n    style: {\n      width: containerWidth + '%',\n      margin: 'auto'\n    }\n  }, (0,external_React_namespaceObject.createElement)(external_wp_blockEditor_namespaceObject.InnerBlocks, {\n    templateLock: false,\n    allowedBlocks: certificate_builder.allowed_blocks\n  }))))));\n}\n;// CONCATENATED MODULE: ./blocks/certificate-builder/save.js\n\n/**\n * Retrieves the translation of text.\n *\n * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/\n */\n\n\n/**\n * The save function defines the way in which the different attributes should\n * be combined into the final markup, which is then serialized by the block\n * editor into `post_content`.\n *\n * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#save\n *\n * @return {WPElement} Element to render.\n */\nfunction save({\n  attributes,\n  setAttributes,\n  className\n}) {\n  return (0,external_React_namespaceObject.createElement)(\"div\", null, (0,external_React_namespaceObject.createElement)(external_wp_blockEditor_namespaceObject.InnerBlocks.Content, null));\n}\n;// CONCATENATED MODULE: ./blocks/certificate-builder/index.js\n/**\r\n * Registers a new block provided a unique name and an object defining its behavior.\r\n *\r\n * @see https://developer.wordpress.org/block-editor/developers/block-api/#registering-a-block\r\n */\n\n\n/**\r\n * Retrieves the translation of text.\r\n *\r\n * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/\r\n */\n\n\n/**\r\n * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.\r\n * All files containing `style` keyword are bundled together. The code used\r\n * gets applied both to the front of your site and to the editor.\r\n *\r\n * @see https://www.npmjs.com/package/@wordpress/scripts#using-css\r\n */\n\n\n/**\r\n * Internal dependencies\r\n */\n\n\n\n/**\r\n * Every block starts by registering a new block type definition.\r\n *\r\n * @see https://developer.wordpress.org/block-editor/developers/block-api/#registering-a-block\r\n */\n(0,external_wp_blocks_namespaceObject.registerBlockType)('learndash/ld-certificate-builder', {\n  /**\r\n   * This is the display title for your block, which can be translated with `i18n` functions.\r\n   * The block inserter will show this name.\r\n   */\n  title: (0,external_wp_i18n_namespaceObject.__)('LearnDash Certificate Builder', 'learndash-certificate-builder'),\n  /**\r\n   * This is a short description for your block, can be translated with `i18n` functions.\r\n   * It will be shown in the Block Tab in the Settings Sidebar.\r\n   */\n  description: (0,external_wp_i18n_namespaceObject.__)('LearnDash certificate builder', 'learnDash-certificate-builder'),\n  /**\r\n   * Blocks are grouped into categories to help users browse and discover them.\r\n   * The categories provided by core are `common`, `embed`, `formatting`, `layout` and `widgets`.\r\n   */\n  category: 'learndash-blocks',\n  /**\r\n   * An icon property should be specified to make it easier to identify a block.\r\n   * These can be any of WordPress’ Dashicons, or a custom svg element.\r\n   */\n  icon: 'welcome-learn-more',\n  /**\r\n   * Optional block extended support features.\r\n   */\n  supports: {\n    // Removes support for an HTML mode.\n    html: false,\n    align: ['full']\n  },\n  multiple: false,\n  attributes: {\n    id: {\n      type: 'int',\n      default: 0\n    },\n    post_id: {\n      type: 'int',\n      default: 0\n    },\n    backgroundImage: {\n      type: 'string',\n      default: ''\n    },\n    font: {\n      type: 'string',\n      default: ''\n    },\n    useFont: {\n      type: 'boolean',\n      default: false\n    },\n    pageSize: {\n      type: 'string',\n      default: 'LETTER'\n    },\n    pageOrientation: {\n      type: 'string',\n      default: 'L'\n    },\n    align: {\n      type: 'string',\n      default: 'full'\n    },\n    pageHeight: {\n      type: 'int',\n      default: 0\n    },\n    pageWidth: {\n      type: 'int',\n      default: 0\n    },\n    containerWidth: {\n      type: 'int',\n      default: 70\n    },\n    spacing: {\n      type: 'number',\n      default: 1\n    },\n    rtl: {\n      type: 'boolean',\n      default: false\n    },\n    viewPort: {\n      type: 'boolean',\n      default: true\n    }\n  },\n  /**\r\n   * @see ./edit.js\r\n   */\n  edit: Edit,\n  /**\r\n   * @see ./save.js\r\n   */\n  save: save\n});\n\n//# sourceURL=webpack://learndash-certificate-builder/./blocks/certificate-builder/index.js_+_10_modules?");

/***/ }),

/***/ "./blocks/index.js":
/*!*************************!*\
  !*** ./blocks/index.js ***!
  \*************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

eval("__webpack_require__(/*! ./certificate-builder/index */ \"./blocks/certificate-builder/index.js\");\n\n//# sourceURL=webpack://learndash-certificate-builder/./blocks/index.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"certificate-builder": 0,
/******/ 			"./style-certificate-builder": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = globalThis["webpackChunklearndash_certificate_builder"] = globalThis["webpackChunklearndash_certificate_builder"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["./style-certificate-builder"], () => (__webpack_require__("./blocks/index.js")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;