=== SherloQ API Form Feeds ===
Contributors: geekmenina, carol-barone, micyo10, chrishilliard26
Tags: form, contact, mail, api
Requires at least: 4.5.0
Tested up to: 6.6.1
Stable tag: trunk
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Short Description ==

The SherloQ plugin allows you to send leads directly from your Contact Form 7 forms to SherloQTM. Your form submissions will be displayed in the SherloQ app, Dashboard, or custom CRM in real time.

== Description ==

SherloQ powered by IBM Watson

The SherloQ plugin allows you to send leads directly from your Contact Form 7 forms to SherloQTM.

Your form submissions will be displayed in the SherloQ app, Dashboard, or custom CRM in real time.

SherloQ powered by IBM Watson provides actionable intelligence for paid and organic search advertising as well as advanced machine learning and automation that feed back into Google Adwords and other advertising platforms, dramatically improving both ad and website performance.

Features:

* Filters out Javascript and HTML that may be present in form submissions.
* Parses Google query strings
* Includes log visibility to assist you with any issues that may arise.

Download the SherloQ app from Google Play or the App Store and create an account in order to begin, or if you are working on behalf of a client, fill out the developers request form here:

https://www.sherloq.app/developers

Your username is the email address used in registration.

Once your account has been created, a SherloQ customer support representative will contact you to provide a free API key. Your username and API key will be required during plugin setup. Then, simply select the form(s) you want to link to SherloQ on the SherloQ Integration page in the WordPress admin, and map your form fields to SherloQ’s field names in Contact Form 7’s form settings tab. That’s all there is to it.

Requirements:

1. Registration with the SherloQ app
1. A username, created during registration
1. An API Key, provided by SherloQ customer support, after registration.

== Installation ==

1. Upload the entire `sherloq` folder to the `/wp-content/plugins/` directory.
1. Make sure the Contact Form 7 plugin is activated.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Configure the forms to work with SherloQ feeds by adding your API username and key to the SherloQ Integration page in the WordPress admin.
1. Map the form fields to SherloQ in the Contact Form 7 form's settings tab.

== Frequently Asked Questions ==

* How do I get an API Key?
Your API Key will be provided by SherloQ customer support after you register on the SherloQ mobile app. If you have not heard from customer support, you can contact them by calling 646-989-3530 or emailing at support@sherloq.app.

== Screenshots ==

1. This is the initial SherloQ API Integration admin page in the WordPress admin.
2. This is the settings tab under an activated form using SherloQ.

== Changelog ==

= 1.8.4 =
* Minor update to README file needed for the plugin repository

= 1.8.3 =
* Tests for compatibility with WP 6.6
* Ups version and compatibility levels to latest

= 1.8.2 =
* Fixes issue with sending error emails on successful submissions

= 1.8.1 =
* Fixes issue with strpos function for last-name
* Adds error email messaging when the API fails

= 1.8.0 =
* Adds an optional last_name field assignment
* Adds optional blank assignment to the Sherloq fields in case you don't want to use one

= 1.7.0 =
* Adds an additional option called multiple_fields. When you set a dropdown in the admin to multiple_fields, it will send all additional fields as an array to be included and processed in the message.

= 1.6.3 =
* Removed filters against the server based variables that were causing fatal errors with PHP 8.0

= 1.6.2 =
* Updated API feed to ignore passing WP Zero Spam and Recaptcha fields
* Updated functionality to consider a different parameter for Contact Form 7 to identify an individual form. This makes it compatible with Contact Form 7 version 5.2.
* Tested against WordPress version 5.5.0

= 1.6.1 =
* Updated the additional_fields parameter to not include the g-recaptcha-response when using recaptcha on a form
* Updated the usernames of who contributed to the plugins' development

= 1.6.0 =
* Adds additional_fields to JSON output so that other potential fields in a form can be captured in SherloQ's API.

= 1.5.2 =
* Removing filter on form field matching so the data goes through correctly

= 1.5.1 =
* Removing filter on the username verification check so that the data goes through correctly to the API
* Adds username verification check to the logging table in the Setting tab

= 1.5.0 =
* Add a frontend logging view of the logging table in the Settings tab

= 1.3.0 =
* Configure the plugin for usage with a variable API key

= 1.2.2 =
* Capture timestamp, remote_ip, and user_agent with leads
* Add logging for API transactions

= 1.2.1 =
* Added feature to test username against hostname at separate end point

= 1.2.0 =
* Renamed plugin to reflect correct branding
* Added settings link on plugins page
* Additional activation actions
* Removed settings field for API URL

= 1.1.0 =
* Organized plugin structure with class-based pseudo-namespacing
* Added settings screen for main plugin settings
* Created settings panel added to form editor when a form is selected
* Added front-end javascript to capture marketing query strings
* Feeds chosen contents and related data from selected forms to API

= 1.0.0 =
* Basic functionality established

== Upgrade Notice ==

= 1.6.0 =
Allows forms with additional fields outside of the four main fields to be passed to SherloQ's API

= 1.5.0 =
This version adds an API logging table on the settings page to help debugging if something goes wrong
