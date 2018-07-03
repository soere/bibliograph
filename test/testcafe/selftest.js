const assert = require("assert");
import {Selector} from 'testcafe';
const getElementById = Selector(id => document.querySelector(`#${id}`));
fixture('Example page')
    .page('http://devexpress.github.io/testcafe/example');

test('Type the developer name, obtain the header text and check it', async t => {
    await t
        .typeText('#developer-name', 'John Smith')
        .click('#submit-button');

    const articleHeader = await getElementById('article-header');
    const headerText = articleHeader.innerText;

    assert.equal(headerText, 'Thank you, John Smith!');
});

var http = require("http");
var url = require('./get-app-url');
http.get(url, function (res) {
    var statusCode = res.statusCode;
    if (statusCode !== 200) {
        console.log(` Request Failed.\n Status Code: ${statusCode}`);
        res.resume();
        return;
    }
    console.log(` Request Success.\n Status Code: ${statusCode}`);
});