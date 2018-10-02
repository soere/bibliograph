<?php

namespace app\modules\zotero;

use lib\channel\BroadcastEvent;
use lib\exceptions\UserErrorException;
use Yii;
use InvalidArgumentException;

use app\controllers\FolderController;

use app\models\Reference;
use lib\models\ITreeNode;
use yii\base\Event;
use yii\db\Exception;

/**
 * Adapter model for a Zotero collection
 *
 */
class Folder extends \app\models\Folder
{

  /*
  ---------------------------------------------------------------------------
     EVENT HANDLERS
  ---------------------------------------------------------------------------
  */

  /**
   * @todo Check if still needed
   * @return int
   */
  protected function getTransactionId()
  {
    return 0;
  }

  /**
   * Creates an Event that will be forwarded to the client to trigger a
   * change in the folder tree
   */
  protected function createUpdateNodeEvent($nodeData)
  {
    return new BroadcastEvent([
      'name' => static::EVENT_CLIENT_UPDATE,
      'data' => [
        'datasource' => static::getDatasource()->namedId,
        'modelType' => static::$modelType,
        'nodeData' => $nodeData,
        'transactionId' => $this->getTransactionId()
      ]]);
  }

  /**
   * Updates a parent node , i.e. recalculates the node children etc.
   * By default, opens the node
   * @param int|null $parentId
   *    Optional id of parent node. If omitted (normal case), the
   *    parentId of the current model instance is used
   * @param boolean $openNode
   *    Optional flag to open the parent node on the client. This doesn't
   *    save the "opened" state.
   * @throws \yii\db\Exception
   */
  public function updateParentNode($parentId=null, $openNode=true)
  {
    if( ! $parentId ){
      $parentId = $this->parentId;
    }
    $parent = static::findOne(['id' => $parentId]);
    if( ! $parent ) return;
    //Yii::debug("Updating parent node " . $parent->label, __METHOD__, __METHOD__);
    $parent->getChildCount(true); // this saves the parent
    $nodeData = FolderController::getNodeDataStatic($parent);
    if( $openNode ){
      $nodeData['bOpened'] = true;
    }
    //Yii::debug($nodeData, __METHOD__);
    // update new parent on client
    Yii::$app->eventQueue->add($this->createUpdateNodeEvent($nodeData));
  }

  /**
   * Triggered when a record is saved to dispatch events.
   * Inserts are dealt with in _afterInsert()
   *
   * @param bool $insert
   * @param array $changedAttributes
   * @return boolean
   * @throws Exception
   * @throws \Throwable
   */
  public function afterSave($insert, $changedAttributes)
  {
    // parent implemenattion
    parent::afterSave($insert, $changedAttributes);

    // do no emit events if in console mode
    if( Yii::$app->request->isConsoleRequest ) return true;

    // inserts
    if ($insert) {
      //Yii::debug("Inserting " . $this->label, __METHOD__);
      $this->_afterInsert();
      return true;
    }
    // dispatch events
    //Yii::debug("Updating " . $this->label . " " . json_encode($changedAttributes));
    foreach ($changedAttributes as $key => $oldValue) {
      switch ($key) {
        case "parentId":
          // skip if no parent id had been set (is the case when adding a node) or no valid user
          if ($oldValue===null or ! Yii::$app->user->getIdentity() ) return false;
          // update parents
          try {
            $this->updateParentNode();
            // update old parent also
            if( $oldValue){
              $this->updateParentNode($oldValue,false);
            }
          } catch (Exception $e) {
            Yii::error($e);
          }
          // move node
          Yii::$app->eventQueue->add(new BroadcastEvent([
            'name' => static::EVENT_CLIENT_MOVE,
            'data' => [
              'datasource' => static::getDatasource()->namedId,
              'modelType' => "folder",
              'nodeId' => $this->id,
              'parentId' => $this->parentId,
              'transactionId' => $this->getTransactionId()
            ]]));
      } // end switch
    } // end foreach

    // if attributes have changed and we have a valid user, update the node
    if (count($changedAttributes) > 0 and Yii::$app->user->getIdentity()) {
      try {
        $nodeData = FolderController::getNodeDataStatic($this);
      } catch (Exception $e) {
        throw new UserErrorException($e->getMessage(),null, $e);
      }
      Yii::$app->eventQueue->add($this->createUpdateNodeEvent($nodeData));
    }
    return true;
  }

  /**
   * Called when a new Active Record has been created
   *
   * @return void
   * @throws Exception
   * @throws \Throwable
   */
  protected function _afterInsert()
  {
    // skip if we don't have a logged-in user (e.g. in tests)
    if (!Yii::$app->user->getIdentity()) return;
    if($this->parentId){
      $this->updateParentNode();
    }
    Yii::$app->eventQueue->add(new BroadcastEvent([
      'name' => static::EVENT_CLIENT_ADD,
      'data' => [
        'datasource'  => static::getDatasource()->namedId,
        'modelType'   => static::$modelType,
        'nodeData'    => FolderController::getNodeDataStatic($this),
        'transactionId' => $this->getTransactionId()
      ]]));
  }

  /**
   * Called after an ActiveRecord has been deleted
   *
   * @return void
   * @throws Exception
   */
  public function afterDelete()
  {
    parent::afterDelete();
    $this->updateParentNode();
    Yii::$app->eventQueue->add(new BroadcastEvent([
      'name' => static::EVENT_CLIENT_DELETE,
      'data' => [
        'datasource' => static::getDatasource()->namedId,
        'modelType' => static::$modelType,
        'nodeId' => $this->id,
        'transactionId' => $this->getTransactionId()
      ]]));
  }

  //-------------------------------------------------------------
  // Protected methods
  //-------------------------------------------------------------

  /**
   * Returns the Folder objects of subfolders of this folder optionally ordered by a property
   * @param string|null $orderBy
   *    Optional propert name by which the returned ids should be ordered.
   *    Defaults to "position".
   * @return \yii\db\ActiveQuery
   */
  protected function getChildrenQuery($orderBy = "position")
  {
    return static::find()
      ->select("id")
      ->where(['parentId' => $this->id])
      ->orderBy($orderBy);
  }

  //-------------------------------------------------------------
  // ITreeNode Interface
  //-------------------------------------------------------------  

  /**
   * Returns the Folder objects of subfolders of this folder optionally ordered by a property
   * @param string|null $orderBy
   *    Optional propert name by which the returned ids should be ordered.
   *    Defaults to "position".
   * @return Folder[]|array
   */
  public function getChildren($orderBy = "position")
  {
    return $this->getChildrenQuery($orderBy)->all();
  }

  /**
   * Returns the ids of the child node ids optionally ordered by a property
   * @param string|null $orderBy
   *    Optional propert name by which the returned ids should be ordered.
   *    Defaults to "position".
   * @return array of ids
   */
  function getChildIds($orderBy = "position")
  {
    return $this->getChildrenQuery($orderBy)->select('id')->column();
  }

  /**
   * Returns the data of child nodes of a branch ordered by the order field
   * @param string|null $orderBy
   *    Optional propert name by which the returned data should be ordered.
   *    Defaults to "position".
   * @return array
   */
  function getChildrenData($orderBy = "position")
  {
    $query = static::find()
      ->where(['parentId' => $this->id])
      ->orderBy($orderBy);
    return $query->asArray()->all();
  }

  /**
   * Returns the number of children
   * @param bool $update
   *    If $update If true, recalculate the child count. Defaults to false.
   * @return int
   * @throws Exception
   */
  public function getChildCount($update = false)
  {
    if ($update or $this->childCount === null) {
      $this->childCount = $this->getChildrenQuery()->count();
      $this->save();
    }
    return $this->childCount;
  }

  /**
   * Returns the number of references linked to the folder
   * @param bool $update If true, calculate the reference count again. Defaults to false
   * @return int
   * @throws Exception
   */
  public function getReferenceCount($update = false)
  {
    if ($update or $this->referenceCount === null) {
      $this->referenceCount = $this->getReferences()->count();
      $this->save();
    }
    return $this->referenceCount;
  }

  /**
   * Returns the current position among the node's siblings
   * @return int
   */
  public function getPosition()
  {
    return $this->position;
  }

  /**
   * Change position within folder siblings. Returns itself
   * @param int|string $position New position, either absolute (integer)
   *   or relative ("+1", "-3" etc.)
   * return qcl_data_model_db_TreeNodeModel
   * @return $this
   * @throws InvalidArgumentException
   * @return $this
   */
  function changePosition($position)
  {
    // relative position
    if (is_string($position)) {
      if ($position[0] == "-" or $position[0] == "+") {
        $position = $this->position + (int)$position;
      } else {
        throw new InvalidArgumentException("Invalid relative position");
      }
    } elseif (!is_int($position)) {
      throw new InvalidArgumentException("Position must be relative or integer");
    }

    // siblings
    $query = Folder::find()->where(['parentId' => $this->parentId])->orderBy('position');
    $siblingCount = $query->count();

    // check position
    if ($position < 0 or $position >= $siblingCount) {
      throw new InvalidArgumentException("Invalid position");
    }

    // iterate over the parent node's children
    $index = 0;
    foreach ($query->all() as $sibling) {
      // it's me...
      if ($this->id == $sibling->id) {
        $sibling->position = $position;
        //$this->debug(sprintf("Setting node %s to position %s",$this->getLabel(), $position ),__CLASS__,__LINE__);
      } else {
        if ($index == $position) {
          //$this->debug("Skipping $index ",__CLASS__,__LINE__);
          $index++; // skip over target position
        }
        //$this->debug(sprintf( "Setting sibling node %s to position %s", $this->getLabel(), $index),__CLASS__,__LINE__);
        $sibling->position = $index++;
      }
      $sibling->save();
    }
    return $this;
  }

  /**
   * Set parent node
   * @param \app\models\Folder
   * @return int Old parent id
   * @throws Exception
   */
  public function setParent(\app\models\Folder $parentFolder)
  {
    $oldParentId = $this->parentId;
    $this->parentId = $parentFolder->id;
    $this->save();
    return $oldParentId;
  }

  /**
   * Returns the path of a node in the folder hierarchy as a
   * string of the node labels, separated by the a given character. If that character
   * exists in the folder labels, these occurrences will be escaped with '\'
   *
   * @param string $separator
   *    Separator character, defaults to "/"
   * @return string
   */
  public function labelPath($separator = "/")
  {
    // escape existing separator characters in label
    $path = str_replace($separator, '\\' . $separator, $this->label);
    $parentId = $this->parentId;
    while ($parentId) {
      $folder = Folder::findOne(['id' => $parentId]);
      if (!$folder) throw new \RuntimeException("Folder #$parentId does not exist.");
      $label = str_replace($separator, '\\' . $separator, $folder->label);
      $path = $label . $separator . $path;
      $parentId = $folder->parentId;
    }
    return $path;
  }
}