<?php

namespace app\modules\zotero;

use Yii;

/**
 * @inheritdoc
 */
class Reference extends \app\models\Reference
{


  /**
   * Returns the schema object used by this model
   * @return \app\schema\BibtexSchema
   */
  public static function getSchema()
  {
    static $schema = null;
    if (is_null($schema)) {
      $schema = new ReferenceSchema();
    }
    return $schema;
  }
}
