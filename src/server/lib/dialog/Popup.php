<?php
/* ************************************************************************

   Bibliograph: Collaborative Online Reference Management

   http://www.bibliograph.org

   Copyright:
     2007-2017 Christian Boulanger

   License:
     LGPL: http://www.gnu.org/licenses/lgpl.html
     EPL: http://www.eclipse.org/org/documents/epl-v10.php
     See the LICENSE file in the project's top-level directory for details.

   Authors:
     * Chritian Boulanger (cboulanger)

************************************************************************ */

namespace lib\dialog;

class Popup extends Dialog
{

  /**
   * The type of the dialog widget
   */
  const TYPE = "popup";

  /**
   * The message shown in the dialog
   * @var string
   */
  public $message ="";

  /**
   * @param $value
   * @return $this
   */
  public function setMessage(string $value){$this->message=$value; return $this;}

  /**
   * @inheritdoc
   */
  public function sendToClient()
  {
    static::create(
      $this->message,
      $this->service,
      $this->method,
      $this->params
    );
  }

  /**
   * Hides the popup on the client
   */
  static function hide()
  {
    (new self())->setMessage("")->sendToClient();
  }

  /**
   * Returns an event to the client which displays or hides the application popup
   * @param string $message 
   *    The message text
   * @param string $callbackService 
   *    Optional service that will be called when the user clicks on the OK button
   * @param string $callbackMethod 
   *    Optional service method
   * @param array $callbackParams 
   *    Optional service params
   * @deprecated Please use (new Popup())->setMessage()-> ... sendToClient() instead
   */
  public static function create( $message, $callbackService=null, $callbackMethod=null, $callbackParams=null )
  {
    static::addToEventQueue( array(
     'type' => "popup",
     'properties' => array(
        'message' => $message
      ),
     'service' => $callbackService,
     'method'  => $callbackMethod,
     'params'  => $callbackParams
    ));
  }
}
