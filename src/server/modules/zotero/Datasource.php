<?php
/**
 * Created by PhpStorm.
 * User: cboulanger
 * Date: 03.04.18
 * Time: 23:28
 */

namespace app\modules\zotero;

/**
 * Class Datasource
 * @package app\modules\extendedfields
 * @property $migrationNamespace
 */
class Datasource extends \app\models\BibliographicDatasource
{
  /**
   * The named id of the datasource schema
   */
  const SCHEMA_ID = "zotero";

  /**
   * @inheritdoc
   * @var string
   */
  static $name = "Zotero";

  /**
   * @inheritdoc
   * @var string
   */
  static $description = "This datasource connects to a Zotero server (read-only)";


  /**
   * Initialize the datasource, registers the models
   * @throws \InvalidArgumentException
   */
  public function init()
  {
    parent::init();
    $this->addModel( 'reference',   Reference::class,   'reference');
  }
}