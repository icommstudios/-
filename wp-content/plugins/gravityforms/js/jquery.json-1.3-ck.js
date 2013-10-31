/*
 * jQuery JSON Plugin
 * version: 1.0 (2008-04-17)
 *
 * This document is licensed as free software under the terms of the
 * MIT License: http://www.opensource.org/licenses/mit-license.php
 *
 * Brantley Harris technically wrote this plugin, but it is based somewhat
 * on the JSON.org website's http://www.json.org/json2.js, which proclaims:
 * "NO WARRANTY EXPRESSED OR IMPLIED. USE AT YOUR OWN RISK.", a sentiment that
 * I uphold.  I really just cleaned it up.
 *
 * It is also based heavily on MochiKit's serializeJSON, which is
 * copywrited 2005 by Bob Ippolito.
 */(function($){function toIntegersAtLease(e){return e<10?"0"+e:e}Date.prototype.toJSON=function(e){return this.getUTCFullYear()+"-"+toIntegersAtLease(this.getUTCMonth())+"-"+toIntegersAtLease(this.getUTCDate())};var escapeable=/["\\\x00-\x1f\x7f-\x9f]/g,meta={"\b":"\\b","	":"\\t","\n":"\\n","\f":"\\f","\r":"\\r",'"':'\\"',"\\":"\\\\"};$.quoteString=function(e){return'"'+e.replace(escapeable,function(e){var t=meta[e];if(typeof t=="string")return t;t=e.charCodeAt();return"\\u00"+Math.floor(t/16).toString(16)+(t%16).toString(16)})+'"'};$.toJSON=function(e,t){var n=typeof e;if(n=="undefined")return"undefined";if(n=="number"||n=="boolean")return e+"";if(e===null)return"null";if(n=="string"){var r=$.quoteString(e);return r}if(n=="object"&&typeof e.toJSON=="function")return e.toJSON(t);if(n!="function"&&typeof e.length=="number"){var i=[];for(var s=0;s<e.length;s++)i.push($.toJSON(e[s],t));return t?"["+i.join(",")+"]":"["+i.join(", ")+"]"}if(n=="function")throw new TypeError("Unable to convert object of type 'function' to json.");var i=[];for(var o in e){var u;n=typeof o;if(n=="number")u='"'+o+'"';else{if(n!="string")continue;u=$.quoteString(o)}var a=$.toJSON(e[o],t);if(typeof a!="string")continue;t?i.push(u+":"+a):i.push(u+": "+a)}return"{"+i.join(", ")+"}"};$.compactJSON=function(e){return $.toJSON(e,!0)};$.evalJSON=function(src){return eval("("+src+")")};$.secureEvalJSON=function(src){var filtered=src;filtered=filtered.replace(/\\["\\\/bfnrtu]/g,"@");filtered=filtered.replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,"]");filtered=filtered.replace(/(?:^|:|,)(?:\s*\[)+/g,"");if(/^[\],:{}\s]*$/.test(filtered))return eval("("+src+")");throw new SyntaxError("Error parsing JSON, source is not valid.")}})(jQuery);