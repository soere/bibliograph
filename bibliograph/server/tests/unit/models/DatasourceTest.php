<?php

namespace app\tests\unit\models;

// for whatever reason, this is not loaded early enough
require_once __DIR__ . "/../../_bootstrap.php";

use app\tests\unit\models\Base;
use app\models\Datasource;

class DatasourceTest extends Base
{
  /**
   * @var \UnitTester
   */
  protected $tester;

  public function _fixtures()
  {
      return include __DIR__ . '/../../fixtures/_biblio_models.php';
  }

  public function testDatasourceExists()
  {
    $datasource = Datasource::findOne(['namedId'=>'test']);
    $this->assertEquals(false, is_null($datasource), "Cannot find datasource 'database1'");
    $this->assertEquals('mysql', $datasource->type);
  }

  public function testDatasourceInstance()
  {
    $datasource = Datasource::getInstanceFor('test');
    $this->assertEquals('app\models\BibliographicDatasource',\get_class($datasource));
    $this->assertEquals('mysql:host=localhost;port=3306;dbname=tests', $datasource->getConnection()->dsn);
  }

  public function testDatasourceModels()
  {
    $datasource = Datasource::getInstanceFor('test');
    $folderClass = $datasource->getClassFor('folder');
    $this->assertEquals( 'app\models\Folder', $folderClass );
    $this->assertEquals( 'test', $folderClass::getDatasource() );
    $this->assertSame( $folderClass::getDb(), $datasource->getConnection(), "Model does not inherit connection from datasource." );
    $folder = $folderClass::findOne(['label'=>'Hauptordner']);
    $this->assertFalse( is_null($folder), "Folder model not found." );
    $numEnglishRefs = $datasource->getClassFor('reference')::find()->where(['language'=>'English'])->count();
    $this->assertEquals( 15, $numEnglishRefs );
  }
}