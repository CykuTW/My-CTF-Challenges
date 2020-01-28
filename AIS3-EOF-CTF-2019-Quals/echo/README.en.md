## Solution

The only one feature of this challenge is the following 4 lines of code:

```javascript
app.post('/', (req, res) => {
    let data = req.body;
    res.render('echo.ejs', data);
});
```

The key point is that it directly pass user-controllable object `req.body` into `res.render` function.

Let's look at the definition of `res.render`. Express.js provides some options to control the rendering behavior of the template. However, it does not isolate these options from the local variables passed in, which causes some issues in this scenario.

```javascript
// express/lib/response.js
res.render = function render(view, options, callback);
```

After reviewing the code, we can find that the property `.settings['view options']` from object `req.body` will be used as options in EJS.

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

In compilation process of EJS, the property `outputFunctionName` of options will be concatenated into compiled JavaScript code.

```javascript
// ejs/lib/ejs.js
compile: function () {
    var opts = this.opts;

    if (opts.outputFunctionName) {
        prepended += '  var ' + opts.outputFunctionName + ' = __append;' + '\n';
    }
}
```

So if we give something like `settings[view options][outputFunctionName]=balabala`, the string `balabala` will be executed as JavaScript, means we can do Remote Code Execution!