/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!**********************************!*\
  !*** ./src/assets/js/scripts.js ***!
  \**********************************/
/**
 * LearnDash Achievements frontend scripts.
 *
 * @since 1.0
 */
jQuery(document).ready(function ($) {
  // eslint-disable-next-line camelcase, no-var -- Kept for backward compatibility.
  var LD_Achievements = LD_Achievements || {};

  // eslint-disable-next-line camelcase -- Kept for consistency with the PHP variable and backward compatibility.
  if (LD_Achievements_Data.notifications.length === 0) {
    // eslint-disable-next-line camelcase -- Kept for consistency with the PHP variable and backward compatibility.
    LD_Achievements_Data.notifications = [];
  } else {
    // eslint-disable-next-line camelcase -- Kept for consistency with the PHP variable and backward compatibility.
    LD_Achievements_Data.notifications = JSON.parse(
    // eslint-disable-next-line camelcase -- Kept for consistency with the PHP variable and backward compatibility.
    LD_Achievements_Data.notifications);
  }

  /**
   * Frontend object.
   *
   * @since 1.0
   */
  // eslint-disable-next-line camelcase -- Kept for backward compatibility.
  LD_Achievements.frontend = {
    /**
     * Achievements data.
     *
     * @since 1.0
     *
     * @type {Object}
     */
    data: LD_Achievements_Data,
    // eslint-disable-line camelcase -- Kept for backward compatibility.

    /**
     * Initializes frontend scripts.
     *
     * @since 1.0
     */
    init() {
      this.data.notifications.forEach(function (notif) {
        // eslint-disable-next-line camelcase -- Kept for backward compatibility.
        LD_Achievements.frontend.show_notification(notif.title, notif.message, notif.image);
      });
    },
    /**
     * Shows notification.
     *
     * @since 1.0
     *
     * @param {string} title   Notification title.
     * @param {string} message Notification message.
     * @param {string} image   Notification image.
     *
     * @return {void}
     */
    show_notification(title = '', message = '', image = null) {
      const user_id = LD_Achievements_Data.user_id; // eslint-disable-line camelcase -- Kept for backward compatibility.

      let position = this.data.settings.hasOwnProperty('position') ? this.data.settings.position : 'top_right';

      // Convert position to camel case to match Noty library format.
      position = position.replace(/_(\w)/g, function (match, $1) {
        return $1.toUpperCase();
      });
      const html = `
				<div class="wrapper">
					<div class="image">
							<img src="${image}" alt="">
						</div>
						<div class="text">
							<div class="title">${title}</div>
							<div class="message">${message}</div>
						</div>
				</div>
			`;
      const htmlRtl = '<div class="wrapper rtl">' + '<div class="text">' + '<div class="title">' + title + '</div>' + '<div class="message">' + message + '</div>' + '</div>' + '<div class="image">' + '<img src="' + image + '" alt="">' + '</div>' + '<div class="clear"></div>' + '</div>';
      new Noty({
        type: 'success',
        layout: position,
        text: this.data.settings.rtl !== '1' ? html : htmlRtl,
        theme: 'learndash',
        closeWith: ['click', 'button'],
        timeout: this.data.settings.popup_time * 1000 || false,
        callbacks: {
          onShow() {
            LD_Achievements.frontend.delete_notification(user_id); // eslint-disable-line camelcase -- Kept for backward compatibility.
          }
        }
      }).show();
    },
    /**
     * Deletes notification.
     *
     * @since 1.0
     *
     * @param {number} userId User ID.
     *
     * @return {void}
     */
    delete_notification(userId) {
      $.ajax({
        url: this.data.ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'ld_achievement_delete_queue',
          user_id: userId
        }
      });
    }
  };
  LD_Achievements.frontend.init(); // eslint-disable-line camelcase -- Kept for backward compatibility.
});
/******/ })()
;