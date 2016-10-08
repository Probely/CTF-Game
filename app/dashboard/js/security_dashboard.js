

window.PixelsCampSecurityContestDashboard = function()
{
    this.init();
}


window.PixelsCampSecurityContestDashboard.prototype = {

    init: function()
    {
        this._debug = false;

        this.ajax = false;

        this.counterWSConnect = 0;

        this.timeToEnd = 0;
        this.timeElm = null;

        this._countDown();

        this._processInfo();

        this._runProcessInfoTimer();

    },

    _runProcessInfoTimer: function()
    {
        var setInt = setInterval(function(){
		    location.reload();
            //this._processInfo();
        }.bindObj(this), (1000 * 15)); // intervalo de update
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
        }

        if(this.timeToEnd <= 61 && this.timeElm.style.color != 'red') {
            setTimeout(function() {
                this.timeElm.style.color = 'red';
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


    _addPageEvents: function()
    {
        return;
    },

    _getUpdatedInfo: function()
    {
        if(this.ajax) {
            this.ajax.transport.abort();
            this.ajax = false;
        }
        var opt = {
            method: 'GET',
            onSuccess: this._updateMatrix.bindObj(this)
        };

        this.ajax = new SAPO.Communication.Ajax('dashjson.php', opt);
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
            }
            if(this._debug) {
                console.log(res);
            }
        }
        this.ajax = false;
    },

    _processInfo: function(data)
    {
        this._getUpdatedInfo();
    },

    _processMatrixInfo: function(data)
    {
        //return false;
        if(typeof(data) == 'object') {
            var curKey = false;
            var curClass = false;
            var aCurTeams = false;
            for(var i in data) {
                for(var j in data[i]) {
                    curKey = i+'_'+j;
                    curKey = curKey.toLowerCase();
                    curKey = curKey.replace(/\ /g, '');

                    curClass = data[i][j]['class'];
                    aCurTeams = data[i][j]['teams'];

                    var elm = document.getElementById(curKey);
                    if(elm) {

                        switch(curClass) {
                            case 'open':
                                elm.className = 'open';
                                var str = '<p>'+j+'</p>';
                                if(aCurTeams.length > 0) {
                                    str += '<h3>Teams Completed:</h3><ul>';
                                    for(var k=0, tK = aCurTeams.length; k < tK; k++) {
                                        str += '<li class="team1">'+aCurTeams[k]+'</li>';
                                    }
                                    str += '</ul>';
                                }
                                elm.innerHTML = str;
                            break;

                            case 'closed':
                                elm.className = 'closed';
                                elm.str += j;
                            break;

                            case 'lead':
                                elm.className = 'lead';
                                elm.innerHTML = '<p>'+j+'</p><h3><strong>Lead Question</strong></h3>';
                            break;
                        }
                    }
                }
            }
        }
    },

    _processScoreInfo: function(data)
    {
        var elmList = document.getElementById('leaderboard_list');

        if(elmList == null) {
            return;
        }

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

    _processOtherStuff: function(data)
    {
        return;
        if(data) {
            document.getElementById('out3').innerHTML = '<pre>'+data+'</pre>';
        }
    },


    debug: function(){}

};


