const Koa = require('koa')
const Router = require('koa-router')
const views = require('koa-views')
const bodyParser = require('koa-body')
const path = require('path')

const app = new Koa()
const router = new Router()

app.use(views(path.resolve(__dirname, 'views'), {
    extension: 'pug'
}))

app.use(bodyParser({
    urlencoded: true
}))

router.all('/', async (ctx) => {
    await ctx.render('index')
})

router.post('/echo', async (ctx) => {
    let data = ctx.request.body
    await ctx.render('echo', data)
})

app.use(router.routes())
app.listen(5000)
