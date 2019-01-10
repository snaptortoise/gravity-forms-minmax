=== Gravity Forms MIN/MAX Calculation ===
Contributors: georgemandis
Donate link: https://george.mand.is/support
Tags: gravity forms, math, calculations
Requires at least: 2.8.0
Tested up to: 5.0.3
Requires PHP: 5.3.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds MIN and MAX functions for Gravity Forms number field calculations.

== Description ==

Doing complex calculations in Gravity Forms can be a chore, but this plugin can make it slightly easier. This WordPress plugin adds `MIN()` and `MAX()` functions for Gravity Forms number fields calculations. It can be used to determine the highest or lowest value between any number of passed arguments, including merge tags.

== Installation ==

= From the WordPress Admin Area =

1. Log in to your WordPress admin area and navigate to the `Plugins` page
1. Click `Add New`, then click `Upload Plugin`
1. Click `Choose File`, then locate and select the plugin zip file
1. Click `Install Now`

= Using FTP =

2. Upload the entire `gravityforms-minmax` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Changelog ==

0.3.1: January 9, 2019

- Fixes bug where calculations and parantheses within MIN/MAX created errors.


0.3.0: January 9, 2019

- MIN/MAX function arguments can now contain calculations. For example: MIN(100 / 2, 75)

0.2.0: June 7, 2018

- MIN/MAX functions can now accommodate *any* number of arguments.