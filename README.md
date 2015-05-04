# Forms: 3rd-Party Dynamic Fields #

**Contributors:** zaus, spkane

**Donate link:** http://drzaus.com/donate

**Tags:** contact form, form, contact form 7, CF7, gravity forms, GF, CRM, mapping, 3rd-party service, services, remote request, dynamic fields, get params, referer

**Requires at least:** 3.0

**Tested up to:** 4.1

**Stable tag:** trunk

**License:** GPLv2 or later

Wordpress Plugin -- Provides some dynamic field values via placeholder to [Forms 3rdparty Integration](https://github.com/zaus/forms-3rdparty-integration).

## Description ##

Using pre-configured placeholders like `##UID##`, `##REFERER##`, or `##SITEURL##`, add dynamic fields to the normally map-only or static-only [Forms: 3rdparty Integration](http://wordpress.org/plugins/forms-3rdparty-integration/) plugin.

## Installation ##

1. Unzip, upload plugin folder to your plugins directory (`/wp-content/plugins/`)
2. Make sure [Contact Form 7]  or [Gravity Forms] is installed
2. Make sure [Forms: 3rdparty Integration](http://wordpress.org/plugins/forms-3rdparty-integration/) is installed
3. Activate plugin
4. Go to new admin subpage _"3rdparty Services"_ under the CF7 "Contact" menu or Gravity Forms "Forms" menu and configure services + field mapping.
5. Configure the new "Dynamic Fields" section to optionally attach the dynamic values to the notification messaging (and how)
6. Using the additional collapsed metabox for examples, add dynamic placeholders as "static values" to the service mapping (check 'Is Value?' column).  Double-click examples to populate each textbox after selecting it.

[Contact Form 7]: http://wordpress.org/extend/plugins/contact-form-7/ "Contact Form 7"

[Gravity Forms]: http://www.gravityforms.com/ "Gravity Forms"


## Frequently Asked Questions ##

### I need help ###

Submit an issue to the [GitHub issue tracker] in addition to / instead of the WP Support Forums.

[GitHub issue tracker]: https://github.com/zaus/forms-3rdparty-dynamicfields/issues "GitHub issue tracker"


### How do I add / configure a service? ###

See "base plugin" [Forms: 3rdparty Integration](http://wordpress.org/plugins/forms-3rdparty-integration/).


Expand the box "Dynamic Placeholder Examples" below the 'save' button for allowed dynamic fields.

Additionally, you'll need to check the 'Is Value?' column.

### How do I add GET parameters to my service post? ###

Use the placeholder `##GET:{urlparam}##` as the static value, which will attach the value `XYZ` from the url in `?urlparam=XYZ`.

### How do I add COOKIE parameters to my service post? ###

Use the placeholder `##COOKIE:{hamburgler}##` as the static value, which will attach the value `XYZ` from the cookie named `hamburgler`.

### What else can I attach? ###

These are also listed within the plugin settings under the "[?] Dynamic Placeholder Examples" metabox -- double click each option for automatic entry.  Live "example previews" are also shown for each within the metabox.

* `##TIMESTAMP##` = the current timestamp (from [`time`](http://php.net/manual/en/function.time.php))
* `##DATE_ISO##` = date, formatted to ISO 8601 "Y-m-d\TH:i:sP" (PHP5)
* `##DATE##` = wordpress-formatted current date (`date_i18n( get_option('date_format'), time() );`)
* `##TIME##` = wordpress-formatted current date (`date_i18n( get_option('date_format'), time() );`)
* `##UID##` = a unique id
* `##IP##` = the visitor IP
* `##SITEURL##` = the site url, according to wordpress ([`get_site_url`](http://codex.wordpress.org/Function_Reference/get_site_url))
* `##NETWORKSITEURL##` = the network site url, according to wordpress ([`network_site_url`](http://codex.wordpress.org/Function_Reference/network_site_url))
* `##SITENAME##` = the site name, according to wordpress (`get_bloginfo('name')`)
* `##ADMINEMAIL##` = the admin email, according to wordpress (`get_bloginfo('admin_email')`)
* `##PAGEURL##` = the current page (permalink) that has the form, according to wordpress ([`get_permalink`](http://codex.wordpress.org/Function_Reference/get_permalink))
* `##REQUESTURL##` = the current page, according to PHP
* `##REFERER##` = the referer, according to PHP
* `##WPREFERER##` = the referer, according to wordpress ([`wp_get_referer`](http://codex.wordpress.org/Function_Reference/wp_get_referer))
* `##GET:{` = prefix to attach querystring parameters (see section above)
* `##COOKIE:{` = prefix to attach cookies (see section above)
* `=` = prefix to perform calculations on other input fields, like `{input_1} / 12 + round({input_2} / 2)`


## Screenshots ##

__None available.__

## Changelog ##

### 0.6 ###
* calculations via prefix `=` using non-eval parser https://github.com/jlawrence11/eos

### 0.5 ###
* IP

### 0.4 ###
Addressed github issues #1, #2, #3

* wpreferer
* explicit mention of how to use in readme (already in plugin metabox)
* cookies

### 0.3.3 ###
Minor bugfixes to pageurl and referer

### 0.3.2 ###
* added REFERER
* more translated text
* handles nested values

### 0.3 ###
GET parameters.

### 0.2 ###
Attaches to notification.

### 0.1 ###
Base version - dynamic field replacement

## Upgrade Notice ##

None.