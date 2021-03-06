/* ************************************************************************

  Bibliograph. The open source online bibliographic data manager

  http://www.bibliograph.org

  Copyright: 
    2018 Christian Boulanger

  License: 
    MIT license
    See the LICENSE file in the project's top-level directory for details.

  Authors: 
    Christian Boulanger (@cboulanger) info@bibliograph.org

************************************************************************ */

/**
 * A JSONRPC 2.0 client. This uses a fork of the NPM package "raptor-client"
 * under the hood. See https://github.com/cboulanger/raptor-client
 */
qx.Class.define("qcl.io.JsonRpcClient", {
  extend: qx.core.Object,

  /**
   * Create a new instance  
   * @param {String} url 
   *    The url of the endpoint of the JSONRPC service
   * @param {String} token
   *    The authorizatio token which will be sent in the Authorization header as
   *    "Bearer <token>"
   */
  construct: function(url) {
    qx.util.Validate.checkUrl(url);
    this.__client = window.raptor( url );
    qx.event.message.Bus.subscribe("bibliograph.token.change",(e) => {
      this.setToken( e.getData() );
    });
  },

  properties: {

    /** 
     * If the last request has resulted in an error, it is stored here.
     * The error object takes the form { message, code } */
    error: {
      nullable: true,
      check: "Object",
      apply: "_applyError"
    },

    /** 
     * Set authentication token
     * */
    token: {
      nullable: true,
      check: "String",
      apply: "_applyToken"
    },

  },

  events: {
    /** Fired when something happens */
    changeSituation: "qx.event.type.Data"
  },

  members: {
    /** The client object */
    __client: null,

    /** The url template string */
    __url: null,

    /*
    ---------------------------------------------------------------------------
     API
    ---------------------------------------------------------------------------
    */    

    /**
     * Sends a jsonrpc request to the server. An error will be caught
     * and displayed in a dialog. In this case, the returned promise 
     * resolves to null
     * @param method {String} The service method
     * @param params {Array} The parameters of the method
     * @return {Promise<*>}
     */
    send : async function( method, params=[] ){
      qx.core.Assert.assertArray(params);
      this.setError(null);
      try{
        let result = await this.__client.send( method, params);
        return this._handleResult(result, method);
      } catch( e ) {
        this.setError(e);
        this._showMethodCallErrorMessage(method);
        return null;
      }
    },

    /**
     * Sends a jsonrpc notification to the server. An error will be caught
     * and displayed in a dialog. In this case, the returned promise 
     * resolves to null
     * @param method {String} The service method
     * @param params {Array} The parameters of the method
     * @return {Promise<void>}
     */
     notify : async function( method, params=[] ){
      qx.core.Assert.assertArray(params);
      this.setError(null); 
      try {
         await this.__client.notify( method, params );
      } catch( e ) {
        this.setError(e);
        this._showMethodCallErrorMessage(method);
        return null;
      }
    }, 

    /**
     * Returns a descriptive message of the last error, if available
     * @return {String}
     */
    getErrorMessage(){
      let e = this.getError()
      if( ! e ){
        return undefined;
      }
      if ( typeof e.rpcData == "string" ){
        return e.rpcData;
      }
      if ( typeof e.message == "string" ){
        // shorten message
        let msg = e.message.substring(0,100);
        // use only the first part
        if( msg.includes(":") ){
          msg = msg.substring(0,msg.indexOf(':'));
        }
        return msg;
      }
      return "Unknown Error";
    },

    /*
    ---------------------------------------------------------------------------
     INTERNAL METHODS
    ---------------------------------------------------------------------------
    */    

    /** applys the error property */
    _applyError : function( value, old ){
      if( value ){
        console.log( value );
      }
    },

    /** applys the token property */
    _applyToken : function( value, old ){
      this.__client.setAuthToken(value);
    },
  
    /**
     * Displays an error that the method call failed.
     * @param method
     * @private
     */
    _showMethodCallErrorMessage : function(method)
    {
      let app = this.getApplication();
      let msg =
            app.tr( "Error calling remote method '%1': %2.", method, this.getErrorMessage()) + " " +
            app.tr("If the error persists, contact the administrator.");
      dialog.Dialog.error( msg ); // @todo use one instance!
    },
  
    /**
     * Shows error dialog when authentication failed
     * @param method
     * @private
     */
    _showAuthErrorMessageAndLogOut : function(method)
    {
      let app = this.getApplication();
      if (app.__authErrorDialog) {
        this.error(`Authentication failed for method '${method}.'`);
        return;
      }
      let msg =
            app.tr("A login problem occurred, which is usually due to a database upgrade. Press 'OK' to reload the application.") + " " +
            app.tr("If the error persists, contact the administrator.");
      app.__authErrorDialog = dialog.Dialog.error( msg );
      app.__authErrorDialog.promise()
        .then(()=>
          app.getAccessManager().logout()
            .then(()=>window.location.reload())
            .catch(()=>
              app.getAccessManager().logout()
              .then(()=>window.location.reload())
            )
        );
      this.warn(`Authentication failed for method '${method}.'`);
    },

    /**
     * Event Transport protocol:
     * {
     *   "type" : "ServiceResult"
     *   "events" : [ { "name": "...", "data": <event data> }],
     *   "data" : <result data>
     * }
     */
    _handleResult : function( result, method ){
      // a null result is typically a token problem
      if (result===null){
        this._showAuthErrorMessageAndLogOut(method);
        this.error(`Authentication failed for method '${method}.'`);
      }
      // we are only interested in objects (but not arrays)
      if(  qx.lang.Type.isArray(result) || ! qx.lang.Type.isObject(result) ){
        return result;
      }
      // we're only interested in ServiceResult DTOs
      if( result.type !== "ServiceResult" ) return result;
      // dispatch events as messages
      if( ! qx.lang.Type.isArray(result.events) ){
        this.warn("Invalid event property in ServiceResult DTO!");
        return;
      }
      result.events.forEach(event => {
        qx.event.message.Bus.dispatchByName( event.name, event.data);
      });
      return result.data;
    }


  },

  /**
   * Destructor
   */
  destruct: function() {
    delete this.__client;
  }
});
