/*******************************************************************************
 *
 * Bibliograph: Online Collaborative Reference Management
 *
 * Copyright: 2007-2014 Christian Boulanger
 *
 * License: LGPL: http://www.gnu.org/licenses/lgpl.html EPL:
 * http://www.eclipse.org/org/documents/epl-v10.php See the LICENSE file in the
 * project's top-level directory for details.
 *
 * Authors: Christian Boulanger (cboulanger)
 *
 ******************************************************************************/

/*global qx qcl bibliograph*/

/**
 * @asset(bibliograph/icon/button-reload.png)
 */
qx.Class.define("bibliograph.ui.window.FolderTreeWindowUi",
{
  extend : bibliograph.ui.window.FolderTreeWindow,
  construct : function()
  {
    this.base(arguments);
    this.__qxtCreateUI();
  },
  members : {
    __qxtCreateUI : function()
    {
      //connecting autogenerated id with 'this'
      var folderTreeWindow = this;
      folderTreeWindow.setWidth(300);
      folderTreeWindow.setHeight(400);
      folderTreeWindow.setCaption("Please select a folder");
      var qxGrow1 = new qx.ui.layout.Grow();
      folderTreeWindow.setLayout(qxGrow1);

      // blocker
      var root = qx.core.Init.getApplication().getRoot();
      this.__blocker = new qx.ui.core.Blocker(root);
      this.__blocker.setOpacity( 0.5 );
      this.__blocker.setColor( "black" );

      // events
      folderTreeWindow.addListener("appear", function(e)
      {
        this.center();
        this.__blocker.blockContent( this.getZIndex() - 1 );
      }, this);

      folderTreeWindow.addListener("disappear", function(e) {
        this.__blocker.unblock();
      }, this);

      qx.event.message.Bus.getInstance().subscribe("logout", function(e) {
        folderTreeWindow.close()
      }, this)

      // tree widget
      var treeWidget = new qcl.ui.treevirtual.TreeView();
      this.treeWidget = treeWidget;
      treeWidget.setServiceName("bibliograph.folder");
      treeWidget.setColumnHeaders(['Folders', '#']);
      treeWidget.setModelType("folder");
      folderTreeWindow.add(treeWidget);
      treeWidget.addListener("appear", function(e) {
        this.treeWidget.setDatasource(this.getApplication().getDatasource());
      }, this);

      var qxVbox1 = new qx.ui.layout.VBox(5, null, null);
      qxVbox1.setSpacing(5);
      treeWidget.setLayout(qxVbox1);
      var qxVbox2 = new qx.ui.layout.VBox(null, null, null);
      var treeWidgetContainer = new qx.ui.container.Composite();
      treeWidgetContainer.setLayout(qxVbox2)
      treeWidgetContainer.setHeight(null);
      treeWidget.add(treeWidgetContainer, {
        flex : 1
      });
      this.treeWidget.setTreeWidgetContainer(treeWidgetContainer);
      var qxHbox1 = new qx.ui.layout.HBox(5, null, null);
      var qxComposite1 = new qx.ui.container.Composite();
      qxComposite1.setLayout(qxHbox1)
      treeWidget.add(qxComposite1);
      qxHbox1.setSpacing(5);

      // Reload
      var qxButton1 = new qx.ui.form.Button();
      qxButton1.setIcon("bibliograph/icon/button-reload.png");
      qxComposite1.add(qxButton1);
      qxButton1.addListener("execute", function(e)
      {
        this.treeWidget.clearTreeCache();
        this.treeWidget.reload();
      }, this);

      // Cancel
      var qxButton2 = new qx.ui.form.Button();
      qxButton2.setLabel(this.tr('Cancel'));
      qxComposite1.add(qxButton2);
      qxButton2.addListener("execute", function(e) {
        this.hide();
      }, this);

      // Select
      var qxButton3 = new qx.ui.form.Button();
      qxButton3.setLabel(this.tr('Select'));
      qxComposite1.add(qxButton3);
      qxButton3.addListener("execute", function(e){
        this.hide();
        this.fireDataEvent("nodeSelected", treeWidget.getSelectedNode());
      }, this);
    }
  }
});
