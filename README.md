# Gravity Forms MIN/MAX Calculations

Adds MIN and MAX functions for Gravity Forms number field calculations.

[https://wordpress.org/plugins/gf-minmax-calculation/](https://wordpress.org/plugins/gf-minmax-calculation/)


## Description

Doing complex calculations in Gravity Forms can be a chore, but this plugin can make it slightly easier. This WordPress plugin adds `MIN()` and `MAX()` functions for Gravity Forms number fields calculations. It can be used to determine the highest or lowest value between two passed arguments, including merge tags.

## Examples

This will return the smaller of the two fields (inserted as merge tags) and proceed to divide it by 2:

`MIN({Field:1}, {Field:2}) / 2` 

This will return the larger of the two fields and proceed to multiply by 4:

`MAX({Field:1}, {Field:2}) * 4`

This will return either the value of a field (if above zero) or zero:

`MAX({Field:1}, 0)`

This will return either the value of a field (if below zero) or zero:

`MIN({Field:1}, 0)`

## Installation

### From the WordPress Admin Area

1. Download the plugin zip file from [https://wordpress.org/plugins/gf-minmax-calculation/](https://wordpress.org/plugins/gf-minmax-calculation/)
1. Log in to your WordPress admin area and navigate to the `Plugins` page
1. Click `Add New`, then click `Upload Plugin`
1. Click `Choose File`, then locate and select the plugin zip file
1. Click `Install Now`

### Using FTP

2. Upload the entire `gravityforms-minmax` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

## Thanks

Thanks to [@michaeldozark](http://github.com/michaeldozark) for his [Gravity Forms exponents plugin](http://github.com/michaeldozark/gravityforms-exponents) which served as a good example of extending the built-in Gravity Forms calculation function.
