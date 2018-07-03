/* ************************************************************************

   qcl - the qooxdoo component library
  
   http://qooxdoo.org/contrib/project/qcl/
  
   Copyright:
     2007-2015 Christian Boulanger
  
   License:
     LGPL: http://www.gnu.org/licenses/lgpl.html
     EPL: http://www.eclipse.org/org/documents/epl-v10.php
     See the LICENSE file in the project's top-level directory for details.
  
   Authors:
   *  Christian Boulanger (cboulanger)
  
************************************************************************ */


/**
 * A mixin for to qx.ui.core.Widget that provides central access to widgets by
 * a global id stored at the application instance.
 *  TODO: registry needs to be in this mixin, not in the application
 */
qx.Mixin.define("qcl.application.MWidgetId",
{
  properties :  
  {
    widgetId : {
      check : "String",
      apply : "_applyWidgetId"
    }
  },
  members :
  {
    _applyWidgetId : function(value,oldValue) {
      this.getApplication().setWidgetById(value,this);
      if (typeof this.getContentElement === "function" ){
        this.getContentElement().setAttribute('id',value);
      }
    },
    /**
     * Set the widget id and return object
     * @param id {String}
     */
    withId: function(id){
      this.setWidgetId(id);
      return this;
    }
  }
});