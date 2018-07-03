import { Selector } from 'testcafe';
import { QxSelector, IdSelector } from './qx-testcafe';
let url = require('./get-app-url');

fixture('Running tests').page(url);

const loginButton = QxSelector(IdSelector('app/toolbar/login'));
const loginWindow = QxSelector(IdSelector('app/windows/login'));

test('Click on the Login button and expect to see the login window', async t => {
  await t
    .click(loginButton)
    .expect(loginWindow.visible).ok();
});

