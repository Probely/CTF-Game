


window.PixelsCampSecurityContest = function()
{
    this.init();
}


window.PixelsCampSecurityContest.prototype = {

    init: function()
    {
        this._debug = false;

        this.counterWSConnect = 0;

        this.timeToEnd = 0;
        this.timeElm = null;

        this._countDown();

        this._runProcessInfoTimer();

        try {
            this.socket = false;
            this.host = 'wss://game-ctf.pixels.camp/ws';

            var ok = true;
            ok = this._connect();
            if(!ok){
                return;
            }

            this._addEvents();
            this._addPageEvents();

        } catch(e) {
            //console.log('ERROR '+e);
        }

    },

    _runProcessInfoTimer: function()
    {

        var setInt = setInterval(function(){
                    this._processInfo();
                }.bindObj(this), (1000 * 10)); // intervalo de update
    },

    _countDown: function()
    {
        this.timeElm = document.getElementById('timer');
        if(this.timeElm != null) {
            var toEnd = this.timeElm.getAttribute('time');
            toEnd = parseInt(toEnd, 10);

            this.timeToEnd = toEnd;

            setInterval(function() {
                    this._goingDown();
                    }.bindObj(this), 1000);

            if(this._debug) {
                console.log(toEnd);
            }
        }
    },

    _blinkIt: function()
    {
        this.setIntBlink = setInterval(function(){
                if(SAPO.Dom.Css.getStyle(this.timeElm, 'opacity') == '0.5') {
                    SAPO.Dom.Css.setOpacity(this.timeElm, '1');
                    //this.timeElm.style.visibility = 'visible';
                } else {
                    SAPO.Dom.Css.setOpacity(this.timeElm, '0.5');
                    //this.timeElm.style.visibility = 'hidden';
                }
                }.bindObj(this), 500);
    },

    _goingDown: function()
    {
        this.timeToEnd--;

        if(this.timeToEnd <= 0) {
            this.timeToEnd = 0;
            this.timeElm.style.color = 'red';

            /*if(this.setIntBlink) {
                clearInterval(this.setIntBlink);
            }*/
        }

        if(this.timeToEnd <= 61 && this.timeElm.style.color != 'red') {
            setTimeout(function() {
                this.timeElm.style.color = 'red';
                //this._blinkIt();
            }.bindObj(this), 1000);
        }

        var hours = false;
        var minutes = false;
        var seconds = false;

        var hours = Math.floor(this.timeToEnd / (60 * 60));

        var divisor_for_minutes = this.timeToEnd % (60 * 60);
        var minutes = Math.floor(divisor_for_minutes / 60);

        var divisor_for_seconds = divisor_for_minutes % 60;
        var seconds = Math.ceil(divisor_for_seconds);

        hours = (hours < 10) ? '0'+hours : hours;
        minutes = (minutes < 10) ? '0'+minutes : minutes;
        seconds = (seconds < 10) ? '0'+seconds : seconds;

        this.timeElm.innerHTML = ' '+hours+':'+minutes+':'+seconds+' ';

        if(this.timeToEnd <= 0) {
            var elmToRemove = document.getElementById('current_question');
            if(elmToRemove != null) {
                elmToRemove.parentNode.removeChild(elmToRemove);
            }
        }
    },

    _connect: function()
    {
        try {
            if(("WebSocket" in window)) {

                this.socket = new WebSocket(this.host);
                if(this._debug) {
                    console.log('using websocket ' + this.socket.readyState, ' :: ' , new Date());
                }
            }/* else if(("MozWebSocket" in window)) {
                this.socket = new MozWebSocket(this.host);
                if(this._debug) {
                    console.log(this.socket.readyState);
                }
            }*/ else {
                alert('your browser does not support websockets... choose another one :]');
                return false;
            }
        } catch(e) {
        }
        this.socket.onopen = this._onOpen.bindObj(this);
        this.socket.onmessage = this._onMessage.bindObj(this);
        this.socket.onclose = this._onClose.bindObj(this);

                
        return true;
    },

    _addEvents: function()
    {
        // add onunload to window - 
        window.onbeforeunload = function() { this.socket.close(); }.bindObj(this);
    },

    _onOpen: function()
    {
        if(this._debug) {
            console.log('websocket -> socket open');
            console.log(this.socket.readyState);
        }
        this.socket.send('im in');
        this._runPing();
    },

    _onMessage: function(msg)
    {
        if(this._debug) {
            console.log('websocket -> mensagem recebida :: ' , msg);
        }
        var res = msg.data;

        var obj = eval('['+res+']');
        if(obj.length > 0) {
            obj = obj[0];

            if(this._debug) {
                console.log(obj);
            }

            if(typeof(obj.reload) != 'undefined' && obj.reload == true) {
                this._processInfo();

                if(typeof(obj.msg) != 'undefined') {
                    this._showMessage(obj.msg);
                }
            }
        }
    },

    _onClose: function()
    {
        if(this._debug) {
            console.log('websocket -> closed');
            console.log(this.socket.readyState);
        }
        //if(this.counterWSConnect) {
            setTimeout(function() {
                    this.counterWSConnect++;
                    if(this._debug) {
                        console.log('will try connect again... '+this.counterWSConnect, ' :: ', new Date());
                    }
                    this._connect();
                }.bindObj(this), (1000 * 3));
        //}
    },

    _runPing: function() 
    {
        if(this._debug) {
            console.log('sending ping...');
        }
        this.socket.send('ping');
        var _that = this;
        setTimeout(function() {
                    _that._runPing();
                }, 30000);
    },

    _addPageEvents: function()
    {
        return;
        var elm = document.getElementById('send');
        if(elm != null) {
            elm.onclick = function() {
                //
                // TODO with events on page
                //
                var rand = Math.round(Math.random() * 99999);
                this.socket.send(rand+' qq coisa');
            }.bindObj(this);
        }
    },

    _getUpdatedInfo: function(cat, q)
    {
        if(cat && q) {
            var aParams = [
                'cat='+cat,
                'q='+q
                ];
        } else {
            var aParams = [];
        }

        var opt = {
            method: 'GET',
            parameters: aParams.join('&'),
            onSuccess: this._updateMatrix.bindObj(this)
        };

        new SAPO.Communication.Ajax('json.php', opt);
    },

    _updateMatrix: function(obj)
    {
        var res = eval('['+obj.responseText+']');
        if(res.length > 0) {
            res = res[0];


            if(typeof(res.board) != 'undefined' && typeof(res.leaderboard) != 'undefined') {
                this._processMatrixInfo(res.board);
                this._processScoreInfo(res.leaderboard);
                this._processTimeInfo(res.time);
                this._processHints(res.hints);
            }
            if(this._debug) {
                console.log(res);
            }
        }
    },

    _processInfo: function(data)
    {
        var aQueryString = SAPO.Utility.Url.getQueryString();

        if(typeof(aQueryString['cat']) && typeof(aQueryString['q'])) {
            var cat = aQueryString.cat;
            var q = aQueryString.q;


            this._getUpdatedInfo(cat, q);

            if(this._debug) {
                console.log(aQueryString);
            }
        }
    },

    _processMatrixInfo: function(data)
    {
        if(typeof(data) == 'object') {

            var elmHCat = document.getElementById('h_cat');
            var elmHQ = document.getElementById('h_q');
            if(elmHCat != null && elmHQ != null) {
                var h_cat_value = elmHCat.value;
                var h_q_value = elmHQ.value;
            } else {
                var h_cat_value = false;
                var h_q_value = false;
            }
		if(typeof(data.allanswered) != 'undefined' && data.allanswered === true) {
			var allAnswered = true;
		} else {
			var allAnswered = false;
		}
		var hasLead = false;
		var hasReload = false;

            var curKey = false;
            var curValue = false;
            for(var i in data) {
                for(var j in data[i]) {
                    curKey = i+'_'+j;
                    curKey = curKey.toLowerCase();
                    curKey = curKey.replace(/\ /g, '');

                    curValue = data[i][j];

                    var elm = document.getElementById(curKey);
                    if(elm) {
                        var aElms = elm.getElementsByTagName('a');
                        if(aElms.length > 0) {
                            var aElm = aElms[0];
                        }
                        if(aElm && (curValue == 'open' || curValue == 'lead' || curValue == 'lead current')) {
				if(curValue == 'lead') {
					hasLead = true;
				}
                            aElm.href = '?cat='+i+'&q='+j;
                            aElm.onclick = '';
                        }
                        if(aElm && curValue == 'answered') {
                            if(i == h_cat_value && j == h_q_value) {
				hasReload = true;
			    }
                            aElm.href = '#';
                            aElm.onclick = 'return false';
                        }
                        //if(curValue != '') {
                            elm.className = curValue;
                        //}
                    }
                }
            }

		if(typeof(data.selectmode) != 'undefined' && data.selectmode === false && !hasLead && !h_cat_value && !h_q_value && !allAnswered) {
			//alert('2 vou fazer reload '+location.href +' # '+location.pathname);
		    window.location.href = window.location.href;
		}
		//if(hasReload && hasLead) {
		if(hasReload && !allAnswered) {
			//alert('1');
            var elmMessage = document.getElementById('message');
            if(elmMessage != null && SAPO.Dom.Css.hasClassName(elmMessage, 'success')) {
                setTimeout(function() { // delay for a moment
		        window.location.href = window.location.pathname;
                }, 3000);
            } else {
		        window.location.href = window.location.pathname;
            }
		}
        }
    },

    _processScoreInfo: function(data)
    {
        var elmList = document.getElementById('leaderboard_list');

        elmList.innerHTML = '';

        if(data.length > 0) {
            var cur = false;
            for(var i=0, total=data.length; i < total; i++) {
                cur = data[i];
                var li = document.createElement('li');
                if(typeof(cur.me) != 'undefined' && cur.me === true) {
                    li.className = 'you';
                }
                if(cur.team == null) {
                    li.innerHTML = ' <span class="score">('+cur.points+')</span>';
                } else {
                    li.innerHTML = cur.team+' <span class="score">('+cur.points+')</span>';
                }

                elmList.appendChild(li);
            }
        }

    },

    _processTimeInfo: function(data)
    {
        this.timeToEnd = data;
        //this.timeToEnd = 10;
        if(this._debug) {
            console.log('by ajax -> '+data);
        }
    },

    _processHints: function(data)
    {
        var elmList = document.getElementById('hintslist');

        var newHtml = ''
        for (var i=0; i < data.length; i++) {
            newHtml += "<p><span>"+data[i].timestamp+"</span>"+data[i].hint+"</p>";
        }
        elmList.innerHTML = newHtml;

        elmList.parentNode.style.display = (data.length == 0) ? 'none' : 'block';
    },

    _processOtherStuff: function(data)
    {
        return;
        if(data) {
            document.getElementById('out3').innerHTML = '<pre>'+data+'</pre>';
        }
    },

    _showMessage: function(msg)
    {
        msg = '<p><span>From ADMIN:</span> '+msg+'</p>';
        var elm = document.getElementById('message_info');
        if(elm != null) {
            elm.innerHTML = msg;
            return;
        }
        elm = document.createElement('div');
        elm.id="message_info";
        elm.className = 'success';
        elm.innerHTML = msg;

        var elmPrev = document.getElementById('timer');
        if(elmPrev == null) {
            return;
        }
        elmPrev.nextSibling.parentNode.insertBefore(elm, elmPrev.nextSibling);
    },

    debug: function(){}

};
