
var config = {
    port: 31310
};
var WebSocketServer = require('ws').Server;

var wss = new WebSocketServer({port: config.port}, function(a) {
	console.log('Running on port ' + config.port);
});



wss.on('connection', function connection(ws) {

	ws.on('message', function(message) {
        console.log('NEW MESSAGE', message);
        try {
		    var aMsg = JSON.parse(message);
        } catch(ex) {
            var aMsg = message;
        }
        var isObj = false; 
        var adminMessage;
        if(typeof(aMsg) !== 'object') {
            return;
        }
        if(typeof(aMsg) === 'object' && 'sekjdfSAEwelnfsdWT' in aMsg) {
            adminMessage = aMsg.sekjdfSAEwelnfsdWT;
        }

        var objToSend = {reload: true};
        if(adminMessage) {
            objToSend.msg = adminMessage;
        }
        wss.clients.forEach(function(wsCli) {
            if(wsCli.readyState === wsCli.OPEN) {
                wsCli.send(JSON.stringify(objToSend));
            }
        });
    	//console.log('received: ', aMsg);
  	});

    ws.on('close', function(ws) {
        console.log('User left server... Users online: ' + wss.clients.length);
    });
  	//ws.send({msg: 'Hello user :) ');

    console.log('NEw user connected... Total users: '+ wss.clients.length);
});


