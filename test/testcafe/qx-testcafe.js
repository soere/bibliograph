import { Selector, ClientFunction } from 'testcafe';

export const getPageHTML = ClientFunction(() => document.documentElement.outerHTML);
export const IdSelector = Selector(id => document.getElementById(id));
export const QxSelector = (selector) => {
  // browser-side methods
  const browserAPI = {
    getQxProperty: function(domNode, key){
      return qx.ui.core.Widget.getWidgetByElement(domNode).get(key);
    }
  };
  // NodeJS-side methods
  const serverAPI = {
    findByQxClass: function(clazz){
      return this
        .find(`div[qxclass='${clazz}']`)
    },
    findButtonLabelWithText: function(text){
      return this
        .find("div[qxclass='qx.ui.form.Button']")
        .find("div[qxclass='qx.ui.basic.Label']")
        .withText(text);
    }
  }
  selector = selector.addCustomMethods(browserAPI);
  Object.assign(selector, serverAPI);
  return selector;
}