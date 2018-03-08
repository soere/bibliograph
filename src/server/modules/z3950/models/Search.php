<?php
/* ************************************************************************

   Bibliograph: Collaborative Online Reference Management

   http://www.bibliograph.org

   Copyright:
     2007-2010 Christian Boulanger

   License:
     LGPL: http://www.gnu.org/licenses/lgpl.html
     EPL: http://www.eclipse.org/org/documents/epl-v10.php
     See the LICENSE file in the project's top-level directory for details.

   Authors:
     * Chritian Boulanger (cboulanger)

************************************************************************ */

namespace app\modules\z3950\models;

use app\models\User;
use lib\models\BaseModel;

class Search extends BaseModel
{

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['created', 'modified'], 'safe'],
      [['hits', 'UserId'], 'integer'],
      [['query'], 'string', 'max' => 500],
    ];
  }

  //-------------------------------------------------------------
  // Relations
  //-------------------------------------------------------------

  /**
   * Public to avoid magic property access
   * @return \yii\db\ActiveQuery
   */
  public function getUser()
  {
    return $this->hasOne(User::class, [ 'id' => 'UserId' ] );
  }

  public function getResults()
  {
    return $this->hasMany(Result::class, ['SearchId' => 'id'] );
  }

  //-------------------------------------------------------------
  // Overrridden methods
  //-------------------------------------------------------------

  /**
   * @return bool
   */
  public function beforeDelete()
  {
    if( parent::beforeDelete() ){
      foreach( (array) $this->getResults()->all() as $result ){
        $result->delete();
      }
    }
    return false;
  }

}