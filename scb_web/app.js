const express = require('express')
const app = express()
const morgan = require('morgan')
const methodOverride = require('method-override')
const bodyParser = require('body-parser')
//const PORT = process.env.APP_PORT
const PORT = 8000


const scb = require('./routes/scb')

app.use(bodyParser.json())
app.use(bodyParser.urlencoded({ extended: true }))
app.use(morgan('dev'))
app.use(methodOverride())

app.get('/check', (req, res) => {
  res.send('[SCB] API is running...')
})

app.use('/api', scb)

app.use((req, res, next) => {
  res.header('Access-Control-Allow-Origin', '*')
  res.header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, PATCH, DELETE')
  res.header('Access-Control-Allow-Headers', "Origin, X-Requested-With, Content-Type, Accept")
  res.header('Access-Control-Allow-Credentials', true)

  next()
})

app.listen(PORT, () => {
  console.log('app is on PORT: ', PORT)
})
