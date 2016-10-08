
SAPO.namespace('Utility');SAPO.Utility.Serialize={_convertToUnicode:true,_toUnicode:function(theString)
{if(!this._convertToUnicode){var _m={'\b':'\\b','\t':'\\t','\n':'\\n','\f':'\\f','\r':'\\r','"':'\\"','\\':'\\\\'};if(/["\\\x00-\x1f]/.test(theString)){theString=theString.replace(/([\x00-\x1f\\"])/g,function(a,b){var c=_m[b];if(c){return c;}
c=b.charCodeAt();return'\\u00'+Math.floor(c/16).toString(16)+(c%16).toString(16);});}
return theString;}else{var unicodeString='';var inInt=false;var theUnicode=false;var i=0;var charCount=0;var total=theString.length;while(i<total){inInt=theString.charCodeAt(i);if((inInt>=32&&inInt<=126)||inInt==8||inInt==9||inInt==10||inInt==12||inInt==13||inInt==32||inInt==34||inInt==47||inInt==58||inInt==92){if(inInt==34||inInt==92||inInt==47){theUnicode='\\'+theString.charAt(i);}else if(inInt==8){theUnicode='\\b';}else if(inInt==9){theUnicode='\\t';}else if(inInt==10){theUnicode='\\n';}else if(inInt==12){theUnicode='\\f';}else if(inInt==13){theUnicode='\\r';}else{theUnicode=theString.charAt(i);}}else{if(this._convertToUnicode){theUnicode=theString.charCodeAt(i).toString(16)+''.toUpperCase();while(theUnicode.length<4){theUnicode='0'+theUnicode;}
theUnicode='\\u'+theUnicode;}else{theUnicode=theString.charAt(i);}}
unicodeString+=theUnicode;i++;}
return unicodeString;}},_serialize:function(param)
{var formated='';if(typeof(param)=='object'&&param!==null){if(param.constructor==Array){formated='['+this._removeLastComma(this._format(param))+']';}else if(param.constructor==Object){formated='{'+this._removeLastComma(this._format(param))+'}';}
return formated;}else{return param;}},_format:function(param)
{var formated='';var tmpValue=false;var hasKey=false;if(typeof(param)=='object'&&param!==null&&param.constructor==Object){hasKey=true;}
for(var key in param){if(param.hasOwnProperty(key)){tmpValue=param[key];if(tmpValue===null){if(hasKey){formated+='"'+key+'": null,';}else{formated+='null,';}}else if(typeof(tmpValue)=='string'){if(hasKey){formated+='"'+key+'": "'+this._toUnicode(tmpValue)+'",';}else{formated+='"'+this._toUnicode(tmpValue)+'",';}}else if(typeof(tmpValue)=='number'){if(hasKey){formated+='"'+key+'": '+tmpValue+',';}else{formated+=''+tmpValue+',';}}else if(tmpValue===true||tmpValue===false){if(hasKey){formated+='"'+key+'": '+(tmpValue?'true':'false')+',';}else{formated+=''+(tmpValue?'true':'false')+',';}}else if(typeof(tmpValue)=='object'&&tmpValue!==null&&tmpValue.constructor==Array){if(hasKey){formated+='"'+key+'": ['+this._removeLastComma(this._format(tmpValue))+'],';}else{formated+='['+this._removeLastComma(this._format(tmpValue))+'],';}}else if(typeof(tmpValue)=='object'&&tmpValue!==null&&tmpValue.constructor==Object){if(hasKey){formated+='"'+key+'": {'+this._removeLastComma(this._format(tmpValue))+'},';}else{formated+='{'+this._removeLastComma(this._format(tmpValue))+'},';}}}}
return formated;},_removeLastComma:function(string)
{var len=string.length;if(string.substring((len-1),len)==','){return string.substring(0,(len-1));}
return string;},get:function(jsObject,convertToUnicode)
{if(typeof(convertToUnicode)!='undefined'){if(convertToUnicode===false){this._convertToUnicode=false;}else{this._convertToUnicode=true;}}
if(!this._convertToUnicode&&typeof JSON!=="undefined"){return JSON.stringify(jsObject);}
return this._serialize(jsObject);},debug:function(param){}};