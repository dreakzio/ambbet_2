let request = require('request')
const express = require('express')
const querystring = require('querystring')
const cheerio = require('cheerio')
const router = express.Router()
const j = request.jar()
request = request.defaults({
  jar: j
})

const loginUrl = `https://www.scbeasy.com/online/easynet/page/lgn/login.aspx`
const accUrl = `https://www.scbeasy.com/online/easynet/page/acc/acc_bnk_tst.aspx`
const logoutUrl = `https://www.scbeasy.com/online/easynet/page/lgn/logout.aspx`
const acc_page = `https://www.scbeasy.com/online/easynet/page/acc/acc_mpg.aspx`

router.route('/scb')
  .post(async (req, response) => {
    const body = req.body
    const username = body.username
    const password = body.password
    let transaction = []
    let dataLogin = `LANG=T&LOGIN=${username}&PASSWD=${password}&lgin.x=50&lgin.y=16`
    let allBalance = 0
    console.log('LogingIn....')
    request.post({ 
      headers: {'content-Type': 'application/x-www-form-urlencoded'},
      form: dataLogin,
      uri: loginUrl
    }, (err, res, body) => {
      if(err){
        return res.json(err)
      }else{
        console.log("LoginSuccess!")
        const modify = body.split("VALUE=")
        const SESSIONEASY = modify[1].split(`"`)
        const token = SESSIONEASY[1]
        const dataAcc = `SESSIONEASY=${token}`
        // console.log(modify[1])
        const dataAccpage = `SESSIONEASY=${token}&undefined=undefined`
        request.post({
          headers: {'content-Type': 'application/x-www-form-urlencoded'},
          form: dataAccpage,
          uri: acc_page
        },(err, res, body) => {
          const $ = cheerio.load(body)
          allBalance = $('#DataProcess_SaCaGridView > tbody > tr:nth-child(3) > td > table > tbody > tr > td:nth-child(4) > strong').text()
          let balance = allBalance.split(" ")
          let index = balance.length - 1
          allBalance = balance[index]
          console.log('Loading data....')
          request.post({
            headers: {'content-Type': 'application/x-www-form-urlencoded'},
            form: dataAcc,
            uri: accUrl
          }, (err, res, body) => {
            const $ = cheerio.load(body)
            // console.log(table)
            let transactionTable = $('table #DataProcess_GridView > tbody > tr').each( (i, e) => {
              let tds = $(e).find('td')
              let date = $(tds[0]).text()
              let time = $(tds[1]).text()
              let type = $(tds[2]).text()
              let credit = $(tds[5]).text()
              let name = $(tds[6]).text()
              let date_temp1 = date.split("/")
              let datetime = date_temp1[2]+"-"+date_temp1[1]+"-"+date_temp1[0]+" "+time+":00"
              //FORMAT VALUE
              // console.log(name)
              let format = name.split(" ")
              credit = credit.split("+")
              name = format[1]
              let bankNumber = format[2]
              let bankName
              if(name){
                bankName = name.replace("(", "")
                bankName = bankName.replace(")", "")
              }
              
              if(name != "SCB" && name){
                let bankNumberTemp = bankNumber.split("X")
                bankNumber = bankNumberTemp[1]
              }

              if(format[1] && bankNumber && type != "X2"){
                let dataTable = {
                  datetime,
                  credit: credit[1],
                  name: bankName,
                  bankNumber
                }
                transaction.push(dataTable)
              }
              
            })
            console.log("LoadingSuccess!!")
            request.post({
              headers: {'content-Type': 'application/x-www-form-urlencoded'},
              uri: logoutUrl
            }, (err, res, body) => {
              console.log("Logout...")
              console.log("------------------------------------")
              response.json({total_balance: allBalance, transaction})
            })

          })
        })
      }
    })
    
  })

module.exports = router
