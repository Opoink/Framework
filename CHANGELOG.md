Change namespace Zend to Laminas:
- Of\Controller\Sys\Sys
- Of\Controller\Sys\SystemInstallDatabase
- Of\Db\Createtable
- Of\Db\Entity
- Of\Db\Select


composer.json
> reuire laminas/laminas-db, and laminas/laminas-json instead of requiring the whole zend/laminas framework
