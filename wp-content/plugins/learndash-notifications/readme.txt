=== LearnDash Notifications ===
Author: LearnDash
Author URI: https://learndash.com
Plugin URI: https://learndash.com/add-on/learndash-notifications/
LD Requires at least: 4.7.0
Slug: learndash-notifications
Tags: notifications, emails
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.6.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Send email notifications based on LearnDash actions.

== Description ==

Send email notifications based on LearnDash actions.

This add-on enables a new level of learner engagement within your LearnDash courses. Configure various notifications to be sent out automatically based on what learners do (and do not do) in a course.

This is a perfect tool for bolstering learner engagement, encouragement, promotions, and cross-selling.

= Add-on Features =

* Automatically Send Notifications
* 13 Available Triggers
* 34 Dynamic Shortcodes
* Delay Notifications
* Choose Recipients

See the [Add-on](https://learndash.com/add-on/learndash-notifications/) page for more information.

== Installation ==

If the auto-update is not working, verify that you have a valid LearnDash LMS license via LEARNDASH LMS > SETTINGS > LMS LICENSE.

Alternatively, you always have the option to update manually. Please note, a full backup of your site is always recommended prior to updating.

1. Deactivate and delete your current version of the add-on.
1. Download the latest version of the add-on from our [support site](https://support.learndash.com/article-categories/free/).
1. Upload the zipped file via PLUGINS > ADD NEW, or to wp-content/plugins.
1. Activate the add-on plugin via the PLUGINS menu.

== Changelog ==

= [1.6.4] =

* Deprecate - Classes: `LearnDash\Notifications\Container`, `LearnDash\Notifications\App`.
* Tweak - Added compatibility with LearnDash Core v4.13.0.
* Tweak - Updated functions: `learndash_notifications_extra_autoloading`.

= [1.6.3] =

* Deprecate - Classes: `LearnDash_Dependency_Check_LD_Notifications`, `LearnDash_Notifications`.
* Deprecate - Functions: `learndash_notifications`.
* Fix - "user hasn't completed a quiz" condition field doesn't have additional options.
* Fix - "X days before course expires" trigger doesn't work as expected.
* Fix - User enrolled into course notification is not triggered when a user is enroll via LD Zapier app course enrollment action.

= [1.6.2] =

* Feature - Add "Exclude pre-ordered course" option on Enroll into a course trigger.
* Fix - Undefined array key error on Essay question has been put into graded status trigger.
* Fix - Any object option in object field (course, lesson, etc) not saving.
* Fix - Fatal error when using Assignment has been uploaded trigger and Assignment has been uploaded condition.
* Fix - A scheduled lesson is available to user email not sending.
* Tweak - Better object select2 fields (course, lesson, etc) search results to support pagination.

= [1.6.1] =

* Feature - New action hooks 'learndash_notifications_email_sent' and 'learndash_notifications_email_failed'.
* Fix - Triggering objects settings in notification model don't work correctly filtering events that triggers set up notifications.
* Fix - User can't select specific quiz associated with a lesson because quiz was only direct child to topic. Now it can be direct child of course, lesson, or topic.

= [1.6.0] =

* Added - Multi triggers/conditions support
* Added - 'learndash_notifications_subscription_page_slug' filter to modify subscriptions page slug
* Fix - Disable course fields when using quiz trigger
* Fix - Send single email instead of multiple for "User hasn't logged in for X days" trigger when multiple courses are involved
* Fix - Reschedule drip lesson when user enroll date changes
* Fix - "User hasn't logged in for X days" trigger correctly sends to users now
* Fix - Prevent "A scheduled lesson is available to user" notifications sending from wrong course
* Fix - Display specified course lessons in notifications page listing column
* Fix - User receives quiz failed notification when quiz not failed
* Fix - Group leaders not receiving emails for groups they are leaders of
* Fix - Allow group URL in notifications shortcodes

= [1.5.4] =

* Added AJAX search support on notifications list posts filter
* Added improve LD posts selector logic especially for site with big number of courses, lessons, etc
* Added change post selectors in notification metabox to use select2 and dynamic AJAX options
* Updated update LearnDash strings using LD custom label
* Updated use learndash_quiz_submitted action hook instead of learndash_quiz_completed
* Fixed make sure retrieved group leaders has group leader role
* Fixed before course expires notification is sent before set value
* Fixed Make sure pre selected value is selected on edit screen
* Fixed group selector returns empty result
* Fixed use learndash_emails_send instead of wp_mail to sync with LD core
* Fixed before and after expiry notification is not sent when user re-enrolls
* Fixed issue when drip lesson triggered for all lessons

= [1.5.3] =

* Added a filter to switch the notification content to RTL learndash_notifications_email_rtl
* Updated the trigger "user hasn't logged in for X days" to group the emails into one rather than sending out mass separate emails
* Fixed Notification for drip lesson doesn't update the send time if enrollment date gets changed
* Fixed PHP warnings/notices

= [1.5.2] =

* Fixed issue where when a quiz notification was set in some rare instances this prevented the student from being able to complete the quiz

= [1.5.1] =

* Added ability to chose if the notification should only send one time or recurring for the trigger “User hasn’t logged In for “X” days
* Fixed email sending issue with group leaders
* Fixed emails sending in bulk on updating

= [1.5.0] =

* Added notifications can now be updated after they have been saved
* Added duration unit can now be minutes, hours, and days
* Updated the triggers codebase to improve notifications sending system
* Updated the log screen to make it easier to track what is happening within the system
* Removed the every minute cron and replaced with a single scheduled event to check rather than running blind checks

= [1.4.1] =

* Updated delay field unchangeable for edit to prevent issue with delayed emails
* Updated use of global delete function instead of create new queries in delete functions
* Updated remove `learndash_notifications_delete_delayed_emails_when_unenrolled` hooked function because it already exists in `includes/database.php`
* Updated use of `learndash_get_users_for_course()` to pull course users instead of access list meta only
* Fixed lesson available notification not queueing multiple notifications in DB if there are more than 1 notifications posts
* Fixed regex pattern for searching notifications by shortcode data key value pair
