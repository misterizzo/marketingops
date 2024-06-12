"use strict";

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

if (typeof ImgPreviewer === 'undefined') {
  var _ImgPreviewer =
  /*#__PURE__*/
  function () {
    function _ImgPreviewer(selector) {
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

      _classCallCheck(this, _ImgPreviewer);

      this.selector = selector;
      this.options = options;
      this.initialize();
    }

    _createClass(_ImgPreviewer, [{
      key: "initialize",
      value: function initialize() {
        var _this = this;

        // Initialize properties and other setup
        this.index = 0;
        this.imageElements = [];
        this.config = Object.assign({
          ratio: 0.9,
          zoom: {
            min: 0.1,
            max: 5,
            step: 0.1
          },
          opacity: 0.6,
          scrollbar: true
        }, this.options);
        this.uniqueId = 0;
        this.previewContainerId = null;
        this.generateUniqueIds();
        this.previewContainer = null;
        this.historyInfo = null;
        this.imageEl = null;
        this.body = document.body || document.getElementsByTagName('body')[0];
        this.windowWidth = window.innerWidth;
        this.windowHeight = window.innerHeight;
        window.addEventListener('resize', function () {
          _this.handleResize();
        });
        this.render();
        this.update();
        this.bindEvents();
      }
    }, {
      key: "handleResize",
      value: function handleResize() {
        var _this2 = this;

        if (this.historyInfo != null) {
          if (this.historyInfo && this.historyInfo.width && this.historyInfo.height) {
            var index = this.index;
            var ratio = this.config.ratio;
            var imageElements = this.imageElements;
            var scrollbarvar = this.config.scrollbar; // Load the image for preview

            var preloaderimg = new Image();
            preloaderimg.src = imageElements[index].href;

            preloaderimg.onload = function () {
              // Retrieve width and height of the loaded image
              var width = preloaderimg.width;
              var height = preloaderimg.height;
              var screenWidth = window.innerWidth; //window.screen.width;

              var screenHeight = window.innerHeight; //window.screen.height;

              var maxWidth = 300;

              if (screenWidth <= 768) {
                // Check if the width is greater than the maximum width
                if (width > maxWidth) {
                  // Calculate the ratio to resize the image proportionally
                  var wratio = maxWidth / width;
                  width *= wratio;
                  height *= wratio;
                }
              }

              var startX = (window.innerWidth - width) / 2;
              var startY = (window.innerHeight - height) / 2; // Set up history information for the image

              _this2.currentImageScale = _this2.calcScaleNums(width, height, ratio);
              _this2.historyInfo = {
                startX: startX,
                startY: startY,
                width: width,
                // Store the width
                height: height,
                // Store the height
                endX: 0,
                // window.innerWidth / 2 - width / 2 - startX,
                endY: 0,
                // window.innerHeight / 2 - height / 2 - startY,
                scale: _this2.currentImageScale,
                rotate: 0
              }; // Set image source and style for preview 

              _this2.imageEl.src = imageElements[index].href;

              _this2.setImageBaseStyle(_this2.imageEl, width, height, startX, startY);

              setTimeout(function () {
                _this2.setImageAnimationParams(_this2.historyInfo);
              }); // Show the preview container and toggle scrollbar if necessary

              _this2.previewContainer.classList.add('show');

              !scrollbarvar && _this2.toggleScrollBar(false);
            };
          }
        }
      }
    }, {
      key: "generateUniqueIds",
      value: function generateUniqueIds() {
        // Generate unique IDs for preview containers based on the selector
        this.uniqueId = this.selector.replace(/[^a-zA-Z0-9]/g, '');
        this.previewContainerId = 'image-preview-container' + this.uniqueId;
      }
    }, {
      key: "render",
      value: function render() {
        // Create preview container
        this.previewContainer = document.createElement('div');
        this.previewContainer.classList.add('image-preview-container'); // Generate a unique ID for the preview container
        //this.uniqueId = this.selector.replace(/[^a-zA-Z0-9]/g, '');

        this.previewContainer.id = 'image-preview-container-' + this.uniqueId; //this.previewContainerId = 'image-preview-container' + this.uniqueId;
        // Generate unique IDs for other elements within the preview container

        var previewImageId = 'preview-image-' + this.uniqueId;
        var prevButtonId = 'prev-button-' + this.uniqueId;
        var nextButtonId = 'next-button-' + this.uniqueId;
        var closeButtonId = 'close-button-' + this.uniqueId;
        var resetButtonId = 'reset-button-' + this.uniqueId;
        var rotateLeftButtonId = 'rotate-left-button-' + this.uniqueId;
        var rotateRightButtonId = 'rotate-right-button-' + this.uniqueId; // Set the HTML content of the preview container with unique IDs

        this.previewContainer.innerHTML = "\n            <div class=\"preview-header\">\n                <div class=\"nums\">\n                    <p>\n                        <span id=\"current-index-".concat(this.uniqueId, "\"></span>\n                        &nbsp;/&nbsp;\n                        <span id=\"total-nums-").concat(this.uniqueId, "\"></span>\n                    </p>\n                </div>\n                <div class=\"tool-btn\">\n                    <button id=\"").concat(rotateLeftButtonId, "\" data-tooltip=\"Rotate Right\"><i class=\"iconfont icon-xuanzhuan\"></i></button>\n                    <button id=\"").concat(rotateRightButtonId, "\" data-tooltip=\"Rotate Left\"><i class=\"iconfont icon-xuanzhuan1\"></i></button>\n                    <button id=\"").concat(resetButtonId, "\" data-tooltip=\"Reset\"><i class=\"iconfont icon-zhongzhi\"></i></button>\n                    <button id=\"").concat(closeButtonId, "\" data-tooltip=\"Close\"><i class=\"iconfont icon-account-practice-lesson-close\"></i></button>\n                </div>\n            </div>\n            <div class=\"image-container\">\n                <button id=\"prev\" class=\"").concat(prevButtonId, "\" data-tooltip=\"Prev\"><i class=\"iconfont icon-shangyige\"></i></button>\n                <div class=\"img-content\" id=\"image-content-").concat(this.uniqueId, "\"><img id=\"").concat(previewImageId, "\" src=\"\" alt=\"\" /></div>\n                <button id=\"next\" class=\"").concat(nextButtonId, "\" data-tooltip=\"Next\"><i class=\"iconfont icon-xiayige\"></i></button>\n            </div>"); // Append the preview container to the body

        this.body.appendChild(this.previewContainer); // Set the image element reference based on the unique ID

        this.imageEl = document.getElementById(previewImageId);
      }
    }, {
      key: "update",
      value: function update() {
        var _this3 = this;

        // Update image elements
        this.imageElements = document.querySelectorAll("".concat(this.selector, " a:not(.sp-hidden-items)"));
        this.imageElements.forEach(function (item, index) {
          item.onclick = function (e) {
            _this3.handleOpen(e, index);

            _this3.taggleModel(true);

            _this3.updateIndex(index);
          };
        });
      } // Get element position

    }, {
      key: "getElementRect",
      value: function getElementRect(el) {
        return el.getBoundingClientRect();
      } // Calculate scale numbers

    }, {
      key: "calcScaleNums",
      value: function calcScaleNums(width, height, ratio) {
        var scaleX = this.windowWidth * ratio / width;
        var scaleY = this.windowHeight * ratio / height; //return scaleX > scaleY ? scaleY : scaleX;

        var scale = scaleX > scaleY ? scaleY : scaleX; // Ensure that scale is below 1

        if (scale > 1) {
          scale = 1;
        }

        return scale;
      } // Set image base style

    }, {
      key: "setImageBaseStyle",
      value: function setImageBaseStyle(el, width, height, left, top) {
        el.style.cssText = "width:".concat(width, "px;height:").concat(height, "px;position:fixed; top:").concat(top, "px; left:").concat(left, "px;");
      } // Toggle model display

    }, {
      key: "taggleModel",
      value: function taggleModel(flag) {
        this.previewContainer.style.display = flag ? 'block' : 'none';
      }
    }, {
      key: "toggleModel",
      value: function toggleModel(flag) {
        this.previewContainer.style.display = flag ? 'block' : 'none';
      }
    }, {
      key: "setImageAnimationParams",
      value: function setImageAnimationParams(historyInfo) {
        this.imageEl.style.setProperty('--offsetX', "".concat(historyInfo.endX, "px"));
        this.imageEl.style.setProperty('--offsetY', "".concat(historyInfo.endY + 30, "px"));
        this.imageEl.style.setProperty('--scale', "".concat(historyInfo.scale));
        this.imageEl.style.setProperty('--rotate', "".concat(historyInfo.rotate, "deg"));
      }
    }, {
      key: "useIndexUpdateImage",
      value: function useIndexUpdateImage(index) {
        var _this4 = this;

        var ratio = this.config.ratio;
        var height = 100;
        var width = 100;
        var src = this.imageElements[index].href;
        var preloaderimg1 = new Image();
        preloaderimg1.src = src;

        preloaderimg1.onload = function () {
          width = preloaderimg1.width;
          height = preloaderimg1.height;
          var screenWidth = window.innerWidth;
          var screenHeight = window.innerHeight;
          var maxWidth = 300;

          if (screenWidth <= 768) {
            if (width > maxWidth) {
              var wratio = maxWidth / width;
              width *= wratio;
              height *= wratio;
            }
          }

          var startX = (window.innerWidth - width) / 2;
          var startY = (window.innerHeight - height) / 2;

          _this4.imageEl.classList.add('moving');

          _this4.setImageBaseStyle(_this4.imageEl, width, height, startX, startY);

          _this4.historyInfo = {
            startX: startX,
            startY: startY,
            width: width,
            height: height,
            endX: 0,
            endY: 0,
            scale: _this4.calcScaleNums(width, height, ratio),
            rotate: 0
          };
          _this4.imageEl.src = src;

          _this4.setImageAnimationParams(_this4.historyInfo);

          setTimeout(function () {
            _this4.imageEl.classList.remove('moving');
          });
        };
      }
    }, {
      key: "bindEvents",
      value: function bindEvents() {
        var _this5 = this;

        var previewContainer = this.previewContainer; //document.getElementById(this.previewContainerId);

        if (!previewContainer) return; // Retrieve unique IDs for buttons

        var prevButtonId = 'prev-button-' + this.uniqueId;
        var nextButtonId = 'next-button-' + this.uniqueId;
        var closeButtonId = 'close-button-' + this.uniqueId;
        var resetButtonId = 'reset-button-' + this.uniqueId;
        var rotateLeftButtonId = 'rotate-left-button-' + this.uniqueId;
        var rotateRightButtonId = 'rotate-right-button-' + this.uniqueId; // Bind event listeners using unique IDs

        previewContainer.querySelector("#".concat(closeButtonId)).addEventListener('click', function () {
          _this5.handleClose();
        });
        previewContainer.querySelector(".".concat(prevButtonId)).addEventListener('click', function () {
          _this5.prev();
        });
        previewContainer.querySelector(".".concat(nextButtonId)).addEventListener('click', function () {
          _this5.next();
        });
        previewContainer.querySelector("#".concat(resetButtonId)).addEventListener('click', function () {
          _this5.handleReset();
        });
        previewContainer.querySelector("#".concat(rotateLeftButtonId)).addEventListener('click', function () {
          _this5.handelRotateLeft();
        });
        previewContainer.querySelector("#".concat(rotateRightButtonId)).addEventListener('click', function () {
          _this5.handelRotateRight();
        });
        previewContainer.addEventListener('click', function (event) {
          if (event.target.classList.contains('image-container')) {
            _this5.handleClose();
          }
        });
        document.addEventListener('keydown', function (event) {
          if (event.key === 'Escape') {
            _this5.handleClose();
          }
        });
      }
    }, {
      key: "handleReset",
      value: function handleReset() {
        this.imageEl.style.top = "".concat(this.historyInfo.startY, "px");
        this.imageEl.style.left = "".concat(this.historyInfo.startX, "px");
        this.imageEl.style.setProperty('--rotate', '0deg');
        this.imageEl.style.setProperty('--scale', "".concat(this.historyInfo.scale));
        this.historyInfo.rotate = 0;
      } // Rotate image left

    }, {
      key: "handelRotateLeft",
      value: function handelRotateLeft() {
        this.historyInfo.rotate -= 90;
        this.imageEl.style.setProperty('--rotate', "".concat(this.historyInfo.rotate, "deg"));
      } // Rotate image right

    }, {
      key: "handelRotateRight",
      value: function handelRotateRight() {
        this.historyInfo.rotate += 90;
        this.imageEl.style.setProperty('--rotate', "".concat(this.historyInfo.rotate, "deg"));
      }
    }, {
      key: "runAnimation",
      value: function runAnimation(el, options, callback) {
        var requestAnimationFrame = window.requestAnimationFrame || window.mozRequestAnimationFrame || window.webkitRequestAnimationFrame || window.msRequestAnimationFrame;
        var cancelAnimationFrame = window.cancelAnimationFrame || window.mozCancelAnimationFrame;
        var start = options.start || 0;
        var end = options.end || 0;
        var step = options.step;
        var playing = null;

        function running() {
          if (step > 0 && start < end || step < 0 && start > end) {
            start += step;
            el.style[options.style] = options.template.replace('$', start);
            playing = requestAnimationFrame(running);
          } else {
            callback && callback();
            cancelAnimationFrame(playing);
          }
        }

        running();
      }
    }, {
      key: "handleOpen",
      value: function handleOpen(e, index) {
        var _this6 = this;

        // Prevent default behavior of the event
        e.preventDefault(); // Retrieve necessary data and elements

        var ratio = this.config.ratio;
        var imageElements = this.imageElements;
        var scrollbarvar = this.config.scrollbar; // Load the image for preview

        var preloaderimg = new Image();
        preloaderimg.src = imageElements[index].href;

        preloaderimg.onload = function () {
          // Retrieve width and height of the loaded image
          var width = preloaderimg.width;
          var height = preloaderimg.height;
          var screenWidth = window.innerWidth; //window.screen.width;

          var screenHeight = window.innerHeight; //window.screen.height;

          var maxWidth = 300;

          if (screenWidth <= 768) {
            // Check if the width is greater than the maximum width
            if (width > maxWidth) {
              // Calculate the ratio to resize the image proportionally
              var wratio = maxWidth / width;
              width *= wratio;
              height *= wratio;
            }
          }

          var startX = (window.innerWidth - width) / 2;
          var startY = (window.innerHeight - height) / 2; // Set up history information for the image

          _this6.currentImageScale = _this6.calcScaleNums(width, height, ratio);
          _this6.historyInfo = {
            startX: startX,
            startY: startY,
            width: width,
            // Store the width
            height: height,
            // Store the height
            endX: 0,
            // window.innerWidth / 2 - width / 2 - startX,
            endY: 0,
            // window.innerHeight / 2 - height / 2 - startY,
            scale: _this6.currentImageScale,
            rotate: 0
          }; // Set image source and style for preview 

          _this6.imageEl.src = imageElements[index].href;

          _this6.setImageBaseStyle(_this6.imageEl, width, height, startX, startY);

          setTimeout(function () {
            _this6.setImageAnimationParams(_this6.historyInfo);
          }); // Show the preview container and toggle scrollbar if necessary

          _this6.previewContainer.classList.add('show');

          !scrollbarvar && _this6.toggleScrollBar(false);
        };
      }
    }, {
      key: "handleClose",
      value: function handleClose() {
        var _this7 = this;

        // Retrieve opacity and current image element
        var opacity = this.config.opacity;
        var current = this.imageElements[this.index]; // Hide the image

        this.imageEl.style.display = 'none'; // Run animation to fade out the preview container

        this.runAnimation(this.previewContainer, {
          start: opacity,
          end: 0,
          step: -0.05,
          style: 'background',
          template: 'rgba(0, 0, 0, $)'
        }, function () {
          // Reset image source and styles
          _this7.imageEl.src = '';
          _this7.imageEl.style = '';
          _this7.previewContainer.style = "";

          _this7.previewContainer.classList.remove('hiding');

          _this7.toggleModel(false);
        }); // Remove classes and toggle scrollbar

        this.previewContainer.classList.remove('show');
        this.previewContainer.classList.add('hiding');
        !this.config.scrollbar && this.toggleScrollBar(true);
      }
    }, {
      key: "prev",
      value: function prev() {
        if (this.index !== 0) {
          // Check if it's not the first image
          this.index -= 1; // Decrement the index to move to the previous image

          this.updateIndex(this.index); // Update the index display

          this.useIndexUpdateImage(this.index); // Load and display the previous image
        }
      }
    }, {
      key: "next",
      value: function next() {
        if (this.index < this.imageElements.length - 1) {
          // Check if it's not the last image
          this.index += 1; // Increment the index to move to the next image

          this.updateIndex(this.index); // Update the index display

          this.useIndexUpdateImage(this.index); // Load and display the next image
        }
      }
    }, {
      key: "updateIndex",
      value: function updateIndex(index) {
        // Update the index property to the provided index
        this.index = index; // Find the elements by their unique IDs

        var totalNumsElement = document.getElementById("total-nums-".concat(this.uniqueId));
        var currentIndexElement = document.getElementById("current-index-".concat(this.uniqueId)); // Check if the elements exist before updating their innerText values

        if (totalNumsElement && currentIndexElement) {
          totalNumsElement.innerText = this.imageElements.length;
          currentIndexElement.innerText = index + 1;
        } else {
          console.error("Total numbers element or current index element not found.");
        }
      }
    }, {
      key: "setImageStyles",
      value: function setImageStyles(width, height, setRotate) {
        this.historyInfo.endX = width / 2 - this.historyInfo.width / 2 - this.historyInfo.startX;
        this.historyInfo.endY = height / 2 - this.historyInfo.height / 2 - this.historyInfo.startY;
        this.historyInfo.scale = this.historyInfo._scale = this.calcScaleNums(width, this.historyInfo.width, height, this.historyInfo.height, this.mergeOptions.fillRatio || this.defaultOptions.fillRatio);

        if (setRotate) {
          this.historyInfo.rotate = 0;
          this.imageEl.style.setProperty('--rotate', "0");
        }

        this.setImageBaseStyle(this.imageEl, width, height, this.historyInfo.startX - 1, this.historyInfo.startY);
        this.imageEl.style.setProperty('--offsetX', "".concat(this.historyInfo.endX, "px"));
        this.imageEl.style.setProperty('--offsetY', "".concat(this.historyInfo.endY, "px"));
        this.imageEl.style.setProperty('--scale', "".concat(this.historyInfo.scale));
      }
    }]);

    return _ImgPreviewer;
  }(); // Export ImgPreviewer


  window.ImgPreviewer = _ImgPreviewer;
}