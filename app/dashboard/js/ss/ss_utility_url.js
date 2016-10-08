
SAPO.namespace('Utility');SAPO.Utility.Url={_keyStr:'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=',getUrl:function()
{var url=false;url=location.href;return url;},getQueryString:function(string)
{if(string&&typeof(string)!='undefined'){var url=string;}else{var url=this.getUrl();}
var aParams={};if(url.match(/\?(.+)/i)){var queryStr=url.replace(/^(.*)\?([^\#]+)(\#(.*))?/g,"$2");if(queryStr.length>0){var aQueryStr=queryStr.split(/[;&]/);for(var i=0;i<aQueryStr.length;i++){var pairVar=aQueryStr[i].split('=');aParams[decodeURIComponent(pairVar[0])]=(typeof(pairVar[1])!='undefined'&&pairVar[1])?decodeURIComponent(pairVar[1]):false;}}}
return aParams;},genQueryString:function(uri,params)
{var hasQuestionMark=uri.indexOf('?')!==-1;var sep,pKey,pValue,parts=[uri];for(pKey in params){if(params.hasOwnProperty(pKey)){if(!hasQuestionMark){sep='?';hasQuestionMark=true;}
else{sep='&';}
pValue=params[pKey];if(typeof pValue!=='number'&&!pValue){pValue='';}
parts=parts.concat([sep,encodeURIComponent(pKey),'=',encodeURIComponent(pValue)]);}}
return parts.join('');},getAnchor:function(string)
{if(string&&typeof(string)!='undefined'){var url=string;}else{var url=this.getUrl();}
var anchor=false;if(url.match(/#(.+)/)){anchor=url.replace(/([^#]+)#(.*)/,"$2");}
return anchor;},getAnchorString:function(string)
{if(string&&typeof(string)!='undefined'){var url=string;}else{var url=this.getUrl();}
var aParams={};if(url.match(/#(.+)/i)){var anchorStr=url.replace(/^([^#]+)#(.*)?/g,"$2");if(anchorStr.length>0){var aAnchorStr=anchorStr.split(/[;&]/);for(var i=0;i<aAnchorStr.length;i++){var pairVar=aAnchorStr[i].split('=');aParams[decodeURIComponent(pairVar[0])]=(typeof(pairVar[1])!='undefined'&&pairVar[1])?decodeURIComponent(pairVar[1]):false;}}}
return aParams;},parseUrl:function(url)
{var aURL={};if(url&&typeof(url)!='undefined'&&typeof(url)=='string'){if(url.match(/^([^:]+):\/\//i)){var re1=new RegExp("^([^:]+)://([^/]+)/([^\\?]+)\\?([^#]+)#(.*)$","i");var re2=new RegExp("^([^:]+)://([^/]+)/([^\\?]+)\\?([^#]+)#?$","i");var re3=new RegExp("^([^:]+)://([^/]+)/([^\\?]+)\\??$","i");var re4=new RegExp("^([^:]+)://([^/]+)/?$","i");if(url.match(re1)){aURL['scheme']=url.replace(re1,"$1");aURL['host']=url.replace(re1,"$2");aURL['path']='/'+url.replace(re1,"$3");aURL['query']=url.replace(re1,"$4");aURL['fragment']=url.replace(re1,"$5");}else if(url.match(re2)){aURL['scheme']=url.replace(re2,"$1");aURL['host']=url.replace(re2,"$2");aURL['path']='/'+url.replace(re2,"$3");aURL['query']=url.replace(re2,"$4");aURL['fragment']=false;}else if(url.match(re3)){aURL['scheme']=url.replace(re3,"$1");aURL['host']=url.replace(re3,"$2");aURL['path']='/'+url.replace(re3,"$3");aURL['query']=false;aURL['fragment']=false;}else if(url.match(re4)){aURL['scheme']=url.replace(re4,"$1");aURL['host']=url.replace(re4,"$2");aURL['path']=false;aURL['query']=false;aURL['fragment']=false;}}else{var re1=new RegExp("^([^\\?]+)\\?([^#]+)#(.*)","i");var re2=new RegExp("^([^\\?]+)\\?([^#]+)#?","i");var re3=new RegExp("^([^\\?]+)\\??","i");if(url.match(re1)){aURL['scheme']=false;aURL['host']=false;aURL['path']=url.replace(re1,"$1");aURL['query']=url.replace(re1,"$2");aURL['fragment']=url.replace(re1,"$3");}else if(url.match(re2)){aURL['scheme']=false;aURL['host']=false;aURL['path']=url.replace(re2,"$1");aURL['query']=url.replace(re2,"$2");aURL['fragment']=false;}else if(url.match(re3)){aURL['scheme']=false;aURL['host']=false;aURL['path']=url.replace(re3,"$1");aURL['query']=false;aURL['fragment']=false;}}
if(aURL['host']){var regPort=new RegExp("^(.*)\\:(\\d+)$","i");if(aURL['host'].match(regPort)){var tmpHost1=aURL['host'];aURL['host']=tmpHost1.replace(regPort,"$1");aURL['port']=tmpHost1.replace(regPort,"$2");}else{aURL['port']=false;}
if(aURL['host'].match(/@/i)){var tmpHost2=aURL['host'];aURL['host']=tmpHost2.split('@')[1];var tmpUserPass=tmpHost2.split('@')[0];if(tmpUserPass.match(/\:/)){aURL['user']=tmpUserPass.split(':')[0];aURL['pass']=tmpUserPass.split(':')[1];}else{aURL['user']=tmpUserPass;aURL['pass']=false;}}}}
return aURL;},currentScriptElement:function(match)
{var aScripts=document.getElementsByTagName('script');if(typeof(match)=='undefined'){if(aScripts.length>0){return aScripts[(aScripts.length-1)];}else{return false;}}else{var curScript=false;var re=new RegExp(""+match+"","i");for(var i=0,total=aScripts.length;i<total;i++){curScript=aScripts[i];if(re.test(curScript.src)){return curScript;}}
return false;}},base64Encode:function(string)
{if(!SAPO.Utility.String||typeof(SAPO.Utility.String)=='undefined'){throw"SAPO.Utility.Url.base64Encode depends of SAPO.Utility.String, which has not been referred.";return false;}
var output="";var chr1,chr2,chr3,enc1,enc2,enc3,enc4;var i=0;var input=SAPO.Utility.String.utf8Encode(string);while(i<input.length){chr1=input.charCodeAt(i++);chr2=input.charCodeAt(i++);chr3=input.charCodeAt(i++);enc1=chr1>>2;enc2=((chr1&3)<<4)|(chr2>>4);enc3=((chr2&15)<<2)|(chr3>>6);enc4=chr3&63;if(isNaN(chr2)){enc3=enc4=64;}else if(isNaN(chr3)){enc4=64;}
output=output+
this._keyStr.charAt(enc1)+this._keyStr.charAt(enc2)+
this._keyStr.charAt(enc3)+this._keyStr.charAt(enc4);}
return output;},base64Decode:function(string)
{if(!SAPO.Utility.String||typeof(SAPO.Utility.String)=='undefined'){throw"SAPO.Utility.Url.base64Decode depends of SAPO.Utility.String, which has not been referred.";return false;}
var output="";var chr1,chr2,chr3;var enc1,enc2,enc3,enc4;var i=0;var input=string.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(i<input.length){enc1=this._keyStr.indexOf(input.charAt(i++));enc2=this._keyStr.indexOf(input.charAt(i++));enc3=this._keyStr.indexOf(input.charAt(i++));enc4=this._keyStr.indexOf(input.charAt(i++));chr1=(enc1<<2)|(enc2>>4);chr2=((enc2&15)<<4)|(enc3>>2);chr3=((enc3&3)<<6)|enc4;output=output+String.fromCharCode(chr1);if(enc3!=64){output=output+String.fromCharCode(chr2);}
if(enc4!=64){output=output+String.fromCharCode(chr3);}}
output=SAPO.Utility.String.utf8Decode(output);return output;}};