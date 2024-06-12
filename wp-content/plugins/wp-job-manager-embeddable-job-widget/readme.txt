=== WP Job Manager - Embeddable Job Widget ===
Contributors: mikejolley
Requires at least: 5.8
Tested up to: 6.4
Stable tag: 1.1.6
Requires PHP: 7.4
License: GNU General Public License v3.0

Lets users generate and embed a widget containing your job listings on their own sites via a form added to your site with the shortcode [embeddable_job_widget_generator].

= Documentation =

Usage instructions for this plugin can be found here: [https://wpjobmanager.com/document/embeddable-job-widget/](https://wpjobmanager.com/document/embeddable-job-widget/).

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

2023-11-17 - version 1.1.6
* Fix: Only run has_shortcode if content is not null (#148)
* Fix: Fix EJW plugin link (#138)

2021-07-21 - version 1.1.5
* Fix: Fix file path for older versions of script

2021-05-04 - version 1.1.4
* Add featured jobs filter to the form.
* Fix potential XSS vulnerability.

2021-02-09 - version 1.1.3
* Fix issue with `page` reserved term on embed pagination.
* Fix issue with undefined arguments in PHP 7.4.

2020-08-11 - version 1.1.2
* Replace use of deprecated `get_the_job_type` function.
* Fix query parameters double escaping.
