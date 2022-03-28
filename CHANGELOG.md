Add new feature for database creation page in system UI
- in the system UI, the creation of the installation data
- fix the database migration on the install/upgrade module, it is now accepting the added field from JSON schema and for collation, storage engine, etc.
- update \Of\Database\Migration\Columns to accept collation
- add new system UI page database index
- changing the layout to use a single page of VueJS
- changing the layout to use a single page of VueJS

Fix for PHP 8 or higher
- changes to support PHP 8XX
- Change phpdi version requirements
- Change laminas version requirements
- in Less parser line 5486 change the $rules param to be an optional param
- add fixes in entity setCollection() method, returning empty array if there was no data found, it should return null

Add support to look file in all installed modules, fix some error
- change on how the \Of\Controller\Filecontroller getRealPath method is looking for the file. instead of looking for a specific Vendor_Module we will now scan in all installed modules. but this is only if the Vendor_Module is not defined. it is still recommended to use include vendor/module in your URL to prevent loading time issues. sample URL with https://yourdomain.com/public/deploy1646964026/<vendor>/<module>/css/fontawesome/webfonts/fa-regular-400.woff2
- Fix copying files while in developer mode
- On \Of\Html\Context add method getPageName() this will return the current page name
- On \Of\Html\Context and in \Of\Http\Url add method linkActive($path) this will return link-active or link-part-active
- Change the error trace layout

Add support for SCSS stop the installation of base module
- Throw an error during installation of Opoink/Bmodule if file not found currenty the Opoink/Bmodule is under construction
- We will now add support for SCSS using "scssphp/scssphp": "^1.10.1" now added in composer.json file
- for xml layout, a new attribute "type" is added the value should be "less" or "scss" since Opoink use less css first, "less" is the default if type is not defined
- ScssBuilder class is added to parse scss files in all installed module.
- if scss is used as type "<css src='css/admin/<filesname>' type='scss' media='all' />" then opoink will look in all installed modules for the files  <filesname>.scss and compile it into 1 css result.


Change namespace Zend to Laminas:
- Of\Controller\Sys\Sys
- Of\Controller\Sys\SystemInstallDatabase
- Of\Db\Createtable
- Of\Db\Entity
- Of\Db\Select


composer.json
> reuire laminas/laminas-db, and laminas/laminas-json instead of requiring the whole zend/laminas framework
