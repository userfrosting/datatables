# datatables
Datatables plugin for UserFrosting. 
## By [Srinivas Nukala](https://srinivasnukala.com)
Copyright (c) 2016, free to use in personal and commercial software as per the [license](licenses/UserFrosting.md).

## Important Note
This plugin requires an update to userfrosting configuration files. Most of the changes are harmless. But if you use the 
- https://github.com/userfrosting/datatables/blob/master/userfrosting/config-userfrosting.php#L36-L39 
          'js.path.relative' => "",
          'css.path.relative' => "",

- https://github.com/userfrosting/datatables/blob/master/userfrosting/config-userfrosting.php#L62-L65
                'js-relative'       => "",
                'css-relative'      => "",

then you will have to work thru some changes in the configuration. 

These changes are needed to remove the /css and /js prefixes for the CSS and JS files so we can keep the datatable asset files in one directory instead of splitting these across /css and /js directories under public. 

So this  will also need a slight (harmless) update to the initialize.php to add /css and /js to the paths.
https://github.com/userfrosting/datatables/blob/master/userfrosting/initialize.php#L193-L283

## Installation

#### Update config-userfrosting.php
Set the css.path.relative, js.path.relative and js-relative, css-relative values to "" (blank)

#### Update initialize.php
Update the path in the initialize.php registerCSS and registerJS functions to add css/ and js/ - this will compensate for the variables that were updated in the step above.

#### Copy public/assets directory
This contains the datatables javascript libraries along with some plugins. These need to be in public/asset/... directory, for this plugin to work.

#### Copy 2 plugins 
 - Copy userfrosting/plugins/ufdatatables (this is the main datatables plugin)
 - Copy userfrosting/plugins/accplugin (this is an account management plugin that uses the datatables). We can add a ton of features using the setFormatters() functions, i will add more examples in the near future.

### Not just launch the plugin page 
- First login as the administrator 
- Then go to http://yourufhome/accplugin

If you did everything right then you should see bootstrap tab panel, with Users and Groups with Datatables showing the details.

The Users datatable has edit functionality, just click on the ID link User-1 and it will open a row for edit, you can just update the values and click save. 

### Additional updaes and features
I have a bunch of features that i will upload to the plugin, you can use the setFormatters function to implement some cool functionality dynamically. 

https://github.com/userfrosting/datatables/blob/master/userfrosting/plugins/accplugin/controllers/cdUserDTController.php#L91

Stay tuned for more updates.
