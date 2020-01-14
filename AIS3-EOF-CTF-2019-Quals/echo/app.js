const express = require('express');
const bodyParser = require('body-parser');


const app = express();
app.use(bodyParser.urlencoded({ extended: true }));

app.get('/echo.zip', (req, res) => {
    res.sendfile(`${__dirname}/echo.zip`);
});

app.get('/', (req, res) => {
    res.render('index.ejs');
});

app.post('/', (req, res) => {
    let data = req.body;
    res.render('echo.ejs', data);
});

app.listen(49007);
