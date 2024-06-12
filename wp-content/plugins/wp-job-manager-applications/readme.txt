=== Job Applications ===
Contributors: mikejolley, jakeom, panoskountanis, adamkheckler, drawmyface, gnodesign, onubrooks
Requires at least: 6.1
Tested up to: 6.4
Stable tag: 3.2.0
Requires PHP: 7.4
License: GNU General Public License v3.0

Lets candidates submit applications to jobs which are stored on the employers jobs page, rather than simply emailed.

= Documentation =

Usage instructions for this plugin can be found here: [https://wpjobmanager.com/document/applications/](https://wpjobmanager.com/document/applications/).

= Support Policy =

For support, please visit [https://wpjobmanager.com/support/](https://wpjobmanager.com/support/).

We will not offer support for:

1. Customisations of this plugin or any plugins it relies upon
2. Conflicts with "premium" themes from ThemeForest and similar marketplaces (due to bad practice and not being readily available to test)
3. CSS Styling (this is customisation work)

If you need help with customisation you will need to find and hire a developer capable of making the changes.

== Installation ==

To install this plugin, please refer to the guide here: [http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation)

== Changelog ==

2024-04-29 - version 3.2.0
* Add application data to job statistics
* Add support for ReCAPTCHA version 3

2024-02-02 - version 3.1.0
* Fix Variable Scoping Issue in job_application_email_add_shortcodes
* Fix CSV Export: Encode all fields to UTF-8
* Update Resume Manager integration
* Add privacy policy suggestion for Applications
* Add support for new settings page layout
* Add hooks to allow extra columns to be added to the form editor

2023-11-17 - version 3.0.2
* Update supported versions.

2023-10-10 - version 3.0.1
* Fix past-applications.php template not linking to jobs.

2023-05-02 - version 3.0.0
* Enhancement: Add support for multiple application forms
* Fix: Handle listing applications with empty full name
* Fix: Template security improvements
* Dev - Move application forms to a custom post type. Update related hooks with new form ID parameter.
