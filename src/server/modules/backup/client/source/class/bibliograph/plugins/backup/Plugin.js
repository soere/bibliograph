/* ************************************************************************

  Bibliograph: Online Collaborative Reference Management

   Copyright:
     2007-2018 Christian Boulanger

   License:
     LGPL: http://www.gnu.org/licenses/lgpl.html
     EPL: http://www.eclipse.org/org/documents/epl-v10.php
     See the LICENSE file in the project's top-level directory for details.

   Authors:
     * Christian Boulanger (cboulanger)

************************************************************************ */

/*global qx qcl dialog backup*/

/**
 * Plugin Initializer Class
 * 
 */
qx.Class.define("bibliograph.plugins.backup.Plugin",
{
  extend: qcl.application.BasePlugin,
  include: [qx.locale.MTranslation, qcl.access.MPermissions],
  type: "singleton",
  members:
  {
    permissions: {
      create_backup: {
        depends: "backup.create",
        granted: true,
        updateEvent: "app:changeDatasource",
        condition: app => app.getDatasource() !== null
      }
    },
    
    /**
     * Returns the name of the plugin
     * @returns {string}
     */
    getName : function()
    {
      return "Backup plugin";
    },
    
    init : function()
    {
      // vars
      let app         = this.getApplication();
      let systemMenu  = app.getWidgetById("app/toolbar/menus/system");
      let permMgr     = app.getAccessManager().getPermissionManager();
      
      // add backup menu
      let backupMenuButton = new qx.ui.menu.Button();
      backupMenuButton.setLabel(this.tr('Backup'));
      this.bindVisibility("backup.create", backupMenuButton);
      let backupMenu = new qx.ui.menu.Menu();
      backupMenuButton.setMenu(backupMenu);
      systemMenu.add(backupMenuButton);
      
      // backup progress widget
      let progressMeter = new qcl.ui.dialog.ServerProgress("backupProgressDialog","backup/progress");
      progressMeter.set({
        hideWhenCompleted : true
      });      
      
      // create backup button
      let createBackupButton = new qx.ui.menu.Button(this.tr('Create Backup'));
      this.bindEnabled(this.permissions.create_backup, createBackupButton);
      backupMenu.add(createBackupButton);
      
      createBackupButton.addListener("execute", async () => {
        let comment = await dialog.Dialog.prompt(this.tr('You can enter a description for this backup (optional)')).promise();
        if (comment === undefined ) return;
        progressMeter.setMessage(this.tr("Saving backup..."));
        progressMeter.setMethod("create");
        progressMeter.start({
          datasource: this.getApplication().getDatasource(),
          comment
        });
      });
      
      // restore Backup
      let restoreBackupButton = new qx.ui.menu.Button(this.tr('Restore Backup'));
      backupMenu.add(restoreBackupButton);
      restoreBackupButton.addListener("execute", () => {
        let token = Math.random().toString().substring(2);
        this.__token= token;
        app.getRpcClient('backup/ui').send('confirm-restore',[app.getDatasource(),token]);
      });
      qx.event.message.Bus.getInstance().subscribe("backup.restore", e => {
        let data = e.getData();
        if ( data.token !== this.__token ) {
          this.warn("Invalid message token.");
          return;
        }
        progressMeter.setMessage(this.tr("Restoring backup..."));
        progressMeter.setMethod("restore");
        progressMeter.start({
          datasource: data.datasource,
          file: data.file
        });
      });
      // called after a backup has been restored
      qx.event.message.Bus.getInstance().subscribe("backup.restored", e => {
        let data = e.getData();
        if (data.datasource !== app.getDatasource()) return;
        let msg = this.tr("The datasource has just been restored to a previous state and will now be reloaded.");
        dialog.Dialog.alert(msg, ()=> {
          app.getWidgetById("app/treeview").reload();
          app.getWidgetById("app/tableview").reload();
          app.setModelId(0);
        }, this);
      });
      
      // delete Backup
      // let deleteBackupButton = new qx.ui.menu.Button();
      // deleteBackupButton.setLabel(this.tr('Delete old backups'));
      // this.bindVisibility("backup.delete", deleteBackupButton);
      // backupMenu.add(deleteBackupButton);
      // deleteBackupButton.addListener("execute", () => {
      //   app.getRpcClient('backup/ui').send('choose-delete',[app.getDatasource()]);
      // }, this);
  
      // download Backup
      // let downloadBackupButton = new qx.ui.menu.Button();
      // downloadBackupButton.setLabel(this.tr('Download backup'));
      // this.bindVisibility("backup.download", downloadBackupButton);
      // backupMenu.add(downloadBackupButton);
      // downloadBackupButton.addListener("execute", function(e) {
      //   app.getRpcClient('backup/ui').send('choose-download',[app.getDatasource()]);
      // }, this);
    }
  }
});

