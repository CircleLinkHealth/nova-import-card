var express = require('express');
var https = require('https')
var path = require('path');
var favicon = require('serve-favicon');
var logger = require('morgan');
var cookieParser = require('cookie-parser');
var bodyParser = require('body-parser');

var index = require('./routes/index');
var patients = require('./routes/patients');

var app = express();
var expressWs = require('./sockets/socket.js')(app)

if ((process.env.NODE_ENV || 'development') === 'development') {
  process.env.NODE_TLS_REJECT_UNAUTHORIZED = "0"
}

// view engine setup
app.set('views', path.join(__dirname, 'views'));
app.set('view engine', 'ejs');

// uncomment after placing your favicon in /public
//app.use(favicon(path.join(__dirname, 'public', 'favicon.ico')));
app.use(logger('dev'));
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: false }));
app.use(cookieParser());
app.use(express.static(path.join(__dirname, 'public')));

app.use('/', index);
app.use('/patients', patients);

// catch 404 and forward to error handler
app.use(function(req, res, next) {
  var err = new Error('Not Found');
  err.status = 404;
  next(err);
});

// error handler
app.use(function(err, req, res, next) {
  // set locals, only providing error in development
  res.locals.message = err.message;
  res.locals.error = req.app.get('env') === 'development' ? err : {};

  // render the error page
  res.status(err.status || 500);
  res.render('error');
});

app.server = https.createServer(app);
app.server.listen(process.env.PORT || 8888, function(){
  console.log('Listening on port ' + app.server.address().port); //Listening on port 8888
})

// var listener = app.listen(process.env.PORT || 8888, function(){
//   console.log('Listening on port ' + listener.address().port); //Listening on port 8888
// });

module.exports = app;

require('axios').post('https://cpm-web.dev/api/v2.1/pagetimer', { patientId: '2601',
  providerId: '3864',
  totalTime: 5000,
  wsUrl: 'ws://localhost:8888/time',
  programId: '29',
  urlFull: 'https://cpm-web.dev/manage-patients/2601/notes',
  urlShort: '/manage-patients/2601/notes',
  ipAddr: '127.0.0.1',
  activity: 'Notes/Offline Activities Review',
  title: 'patient.note.index',
  submitUrl: 'https://cpm-web.dev/api/v2.1/pagetimer',
  startTime: '2017-11-08 11:51:48' })
.then(function (response) {
  console.log(response)
}).catch(function (err) {
  console.error(err)
})