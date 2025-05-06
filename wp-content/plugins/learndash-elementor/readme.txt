=== Elementor for LearnDash ===
Author: LearnDash
Author URI: https://learndash.com
Plugin URI: https://learndash.com/
Slug: learndash-elementor
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.8

LearnDash LMS add-on to add support native Elementor templates and widgets.

== Description ==

Integrate LearnDash LMS with Elementor.

= Integration Features =

* Native Elementor templates and widgets

See the [Add-on](https://www.learndash.com/support/docs/add-ons/compatibility/) page for more information.

== Installation ==

If the auto-update is not working, verify that you have a valid LearnDash LMS license via LEARNDASH LMS > SETTINGS > LMS LICENSE.

Alternatively, you always have the option to update manually. Please note, a full backup of your site is always recommended prior to updating.

1. Deactivate and delete your current version of the add-on.
1. Download the latest version of the add-on from our [support site](https://support.learndash.com/article-categories/free/).
1. Upload the zipped file via PLUGINS > ADD NEW, or to wp-content/plugins.
1. Activate the add-on plugin via the PLUGINS menu.

== Changelog ==

= [1.0.8] =

* Deprecate - Classes: `LearnDash\Elementor\Container`, `LearnDash\Elementor\App`.
* Tweak - Added compatibility with LearnDash Core v4.13.0.
* Tweak - Updated functions: `learndash_elementor_extra_autoloading`.

= [1.0.7] =

* Fix - Show step content when not assigned to a course.
* Fix - Show quiz content on global single quiz template.
* Fix - Show quiz content when using shortcode in template.
* Fix - Undefined array key - "globals/typography?id=secondary".

= [1.0.6] =

* Feature - Disable auto insert widgets setting.
* Fix - Prevent templates from duplicating on refresh.
* Fix - Automatically load course certificate/content/infobar widgets in template if widgets not inserted.
* Fix - Steps content not displaying for non admin users.
* Fix - Video progression content not displaying when using Elementor Pro.

= [1.0.5] =

* Deprecate - Classes: `LearnDash_Dependency_Check_LD_Elementor`, `LearnDash_Elementor_Shortcodes_TinyMCE`, `LearnDash_Elementor`.
* Fix - LearnDash elements missing on Elementor theme builder template.
* Fix - Some LearnDash elements missing on course/lesson/topic edit page.
* Fix - Materials tab don't render on course and lesson page when using course/lesson template.
* Fix - Module and materials tab are duplicated when using Elementor template and BuddyBoss theme.
* Fix - Course content widget appears twice on course page.
* Fix - Elementor elements other than course/lesson/topic content don't appear on Elementor page.
* Tweak - Update general plugin structure to adopt modern app structure.

= [1.0.4] =

* Updated deprecated functions from LearnDash core
* Fixed LearnDash shortcode button not working in Elementor Text Editor widget
* Fixed allowing access to scheduled lessons
* Fixed deprecated _register_controls function from Elementor
* Fixed Quiz List Topics filter showing lessons instead of topics

= [1.0.3] =

* Fixed PHP notices/warnings
* Fixed LearnDash Elementor widgets not appearing when other Elementor add-ons are active
* Fixed Lesson content not showing for some users
* Fixed Unlimited quiz retries when using an Elementor quiz template
* Fixed LearnDash shortcode wizard not working in Elementor text element
* Removed "Show Progress Bar” on Lesson, Topic, and Quiz list widgets

= [1.0.2] =

* Fixed conflict with other premium Elementor add-on plugins

= [1.0.1] =

* Updated Assignment widget output within Lesson and Topic templates
* Updated issue with duplicate output via `post_content` widget
* Updated style settings on some widgets
* Added support for Course Grid settings in some widgets

= [1.0.0] =

* Initial release
