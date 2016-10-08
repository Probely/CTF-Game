//var host = 'wss://game.sec.codebits.eu:12345/echo'; 
var host = 'wss://game-ctf.pixels.camp/ws';
var socket = false;

function startWebsockets() {
    if(("WebSocket" in window)) {
        socket = new WebSocket(host);
    } /*else if(("MozWebSocket" in window)) {
        socket = new MozWebSocket(host);
    }*/ else {
        alert('your browser does not support websockets... choose another on :]');
    }

    if(!socket) {
        alert('error');
    }

    socket.onopen = function() { 
        console.log('connected'); 
        _runPing();
    };
    socket.onmessage = function(m) { console.log('message received'); };
    socket.onclose = function() { 
        console.log('closed'); 
        setTimeout(function() {
                console.log('Reconnecting...');
                startWebsockets();
            }, 3000);
    };
}
startWebsockets();

function _runPing() {
    socket.send('ping');
    setTimeout(function() {
            _runPing();
            }, 30000);
}

function sendMessage(event) {
    
    if(event.preventDefault) {
        event.preventDefault();
    }
    if(window.attachEvent) {
        event.returnValue = false;
    }
    if(event.cancel !== null) {
        event.cancel = true;
    }

    var elm = document.getElementById('text_message');
    if(elm == null) {
        return false;
    }
    
    var msgToSend = elm.value;
    
    var oObj = {
        sekjdfSAEwelnfsdWT: msgToSend
    };
    
    //var str = SAPO.Utility.Serialize.get(oObj);
    var str = JSON.stringify(oObj);
    
    //alert('will send: '+str);
    
    socket.send(str);

    return false;
}
