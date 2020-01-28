## Solution

這道題目唯一的功能就是下面的 4 行程式碼：

```javascript
app.post('/', (req, res) => {
    let data = req.body;
    res.render('echo.ejs', data);
});
```

其中關鍵就在於它直接將使用者可完全控制的 req.body 物件傳遞給 res.render 函式，於是讓我們看一下這個函式的定義：

```javascript
// express/lib/response.js
res.render = function render(view, options, callback);
```

原本應該要傳遞進來的 local variables (data) 會在第二個位置，奇怪變數名字怎麼突然變成 options 惹？原因是因為 express.js 提供了一些設定值可以傳遞給 template engine 以控制模板渲染的行為，然而它卻沒有將這些 options 與傳遞進來的 local variables 隔離開，於是就產生了問題。

因為題目是 ejs，所以讓我們追進看看 ejs 如何實作，前面不重要的部分就跳過，總而言之會類似 `ejs.renderFile(view, options, callback)` 這樣子作呼叫 XD

```javascript
// ejs/lib/ejs.js
exports.renderFile = function () {
  var args = Array.prototype.slice.call(arguments);
  var filename = args.shift();
  var cb;
  var opts = {filename: filename};
  var data;
  var viewOpts;

  cb = args.pop();
  data = args.shift();

  viewOpts = data.settings['view options'];
  if (viewOpts) {
    utils.shallowCopy(opts, viewOpts);
  }

  return tryHandleCache(opts, data, cb);
```

移除了一些不重要的程式碼，簡化後就像上面那樣子，data 就是我們最初傳遞進來的 `req.body` 物件，如果有 `.settings['view options']` 的屬性，就會把這屬性的物件存到 opts 中，然後傳遞給 `tryHandleCache`，接著就會依序呼叫：
```
# ejs/lib/ejs.js
tryHandleCache(opts, data, cb)
    handleCache(opts)(data)
        compile(template, opts)
            new Template(template, opts)
                Template.compile()
```

在 Template 的建構函式裡可以看到 opts.outputFunctionName 被儲存起來

```javascript
// ejs/lib/ejs.js
function Template(text, opts) {
    options.outputFunctionName = opts.outputFunctionName;
    this.opts = options;
```

最後就是 compile 中會把 outputFunctionName 取出來拼接進模板編譯成的 JavaScript 中，所以控制了這個變數就能寫進任意 JavaScript 程式碼實現 Remote Code Execution！

```javascript
// ejs/lib/ejs.js
compile: function () {
    var opts = this.opts;

    if (opts.outputFunctionName) {
        prepended += '  var ' + opts.outputFunctionName + ' = __append;' + '\n';
    }
}
```