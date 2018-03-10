<?php

namespace app\modules\z3950;

use Yii;
use app\models\{
  Datasource, Role, Schema, User
};
use app\modules\z3950\models\{
  Datasource as Z3950Datasource, Search
};
use Exception;
use lib\exceptions\RecordExistsException;
use RuntimeException;



/**
 * z3950 module definition class
 */
class Module extends \lib\Module
{

  /**
   * A string constant defining the category for logging and translation
   */
  const CATEGORY="z3950";

  /**
   * @inheritdoc
   */
  public $controllerNamespace = 'app\modules\z3950\controllers';

  /**
   * The path to the directory containing the Z39.50 "Explain" xml files
   * Must have a trailing slash.
   * @var string
   */
  public $serverDataPath = __DIR__ . '/data/servers/';


  /**
   * Installs the plugin.
   * @param boolean $enabled
   *    Whether the module should be enabled after installation (defaults to false)
   * @return boolean
   * @throws RuntimeException
   * @throws Exception
   */
  public function install($enabled = false)
  {
    // check prerequisites
    $error = "";
    if (!function_exists("yaz_connect")) {
      $error = "Missing PHP-YAZ extension. ";
    }

    if (!class_exists("XSLTProcessor")) {
      $error .= "Missing XSL extension. ";
    }

    exec(BIBUTILS_PATH . "xml2bib", $output, $return_val);
    if (!str_contains($output, "bibutils")) {
      $error .= "Could not call bibutils: $output";
    }
    if ($error !== "") {
      throw new RuntimeException("Z39.50 module could not be initialized:" . $error);
    }

    // register datasource
    try {
      Schema::register("z3950", Schema::class);
    } catch (RecordExistsException $e) {
      // ignore
    }

    // preferences and permissions
    $app = Yii::$app;
    $this->addPreference("lastDatasource", "z3950_voyager", true);
    try {
      $app->accessManager->addPermissions(["z3950.manage"], [
        Role::findByNamedId("admin"),
        Role::findByNamedId("manager")
      ]);
    } catch (\yii\db\Exception $e) {
      //ignore
    }
    // create datasources
    $this->createDatasources();

    // register module
    return parent::install(true);
  }

  /**
   * Returns an associative array, keys being the names of the Z39.50 databases, values the
   * paths to the xml EXPLAIN files
   * @return array
   */
  protected function getExplainFileList()
  {
    static $data = null;
    if ($data === null) {
      $data = array();
      $serverDataPath = $this->serverDataPath;
      foreach (scandir($serverDataPath) as $file) {
        if ($file[0] == "." or !ends_with($file, ".xml")) continue;
        $path = "$serverDataPath /$file";
        $explain = simplexml_load_file($path);
        $serverInfo = $explain->serverInfo;
        $database = (string)$serverInfo->database;
        $data[$database] = $path;
      }
    }
    return $data;
  }

  /**
   * Create bibliograph datasources from Z39.50 explain files
   * @throws Exception
   */
  public function createDatasources()
  {
    $manager = Yii::$app->datasourceManager;

    // Deleting old datasources
    $z3950Datasources = Datasource::find()->where(['schema' => 'z3950']);
    foreach ($z3950Datasources as $datasource) {
      $namedId = $datasource->namedId;
      Yii::debug("Deleting Z39.50 datasource '$namedId'...", self::CATEGORY);
      $manager->delete($namedId);
    }
    // Adding new datasources from XML files
    foreach ($this->getExplainFileList() as $database => $filepath) {
      $datasourceName = "z3950_" . $database;
      $explainDoc = simplexml_load_file($filepath);
      $title = substr((string)$explainDoc->databaseInfo->title, 0, 100);
      $datasource = $manager->create($datasourceName, "z3950");
      $datasource->setAttributes([
        'title' => $title,
        'hidden' => true, // should not show up as selectable datasource
        'resourcepath' => $filepath
      ]);
      $datasource->save();
      Yii::info("Added Z39.50 datasource '$title'", self::CATEGORY);
    }
  }



  /**
   * Returns the query as a string constructed from the
   * query data object
   * @param object $queryData
   * @return string
   */
  public function getQueryString($queryData)
  {
    $query = $queryData->query->cql;
    return $this->fixQueryString($query);
  }

  /**
   * Checks the query and optimizes it before
   * sending it to the remote server
   * @param $query
   * @return string
   */
  public function fixQueryString($query)
  {
    // todo: identify DOI
    if (substr($query, 0, 3) == "978") {
      $query = 'isbn=' . $query;
    } elseif (!strstr($query, "=")) {
      $query = 'all="' . $query . '"';
    }
    return $query;
  }


  /**
   * Called when a user logs out
   * @param User $user
   */
  public function clearSearchData(User $user)
  {
    Yii::debug("Clearing search data for {$user->name}...'");
    /** @var Z3950Datasource[] $z3950Datasources */
    $z3950Datasources = Datasource::find()->where(['schema' => 'z3950']);
    // delete all search records of this user in all of the z39.50 caches
    foreach ($z3950Datasources as $datasource) {
      $hits = Search::deleteAll(["UserId" => $user->id]);
      if ($hits) {
        Yii::info(
          "Deleted $hits search records of user '{$user->name}' in '$datasource'.",
          self::CATEGORY
        );
      }
    }
  }
}
