<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Http;

class Codes {

	protected $codes = [
	    /*1×× Informational*/
	    '_100' => 'Continue',
	    '_101' => 'Switching Protocols',
	    '_102' => 'Processing',

	    /*2×× Success*/
	    '_200' => 'OK',
	    '_201' => 'Created',
	    '_202' => 'Accepted',
	    '_203' => 'Non-authoritative Information',
	    '_204' => 'No Content',
	    '_205' => 'Reset Content',
	    '_206' => 'Partial Content',
	    '_207' => 'Multi-Status',
	    '_208' => 'Already Reported',
	    '_226' => 'IM Used',

	    /*3×× Redirection*/
	    '_300' => 'Multiple Choices',
	    '_301' => 'Moved Permanently',
	    '_302' => 'Found',
	    '_303' => 'See Other',
	    '_304' => 'Not Modified',
	    '_305' => 'Use Proxy',
	    '_307' => 'Temporary Redirect',
	    '_308' => 'Permanent Redirect',

	    /*4×× Client Error*/
	    '_400' => 'Bad Request',
	    '_401' => 'Unauthorized',
	    '_402' => 'Payment Required',
	    '_403' => 'Forbidden',
	    '_404' => 'Not Found',
	    '_405' => 'Method Not Allowed',
	    '_406' => 'Not Acceptable',
	    '_407' => 'Proxy Authentication Required',
	    '_408' => 'Request Timeout',
	    '_409' => 'Conflict',
	    '_410' => 'Gone',
	    '_411' => 'Length Required',
	    '_412' => 'Precondition Failed',
	    '_413' => 'Payload Too Large',
	    '_414' => 'Request-URI Too Long',
	    '_415' => 'Unsupported Media Type',
	    '_416' => 'Requested Range Not Satisfiable',
	    '_417' => 'Expectation Failed',
	    '_418' => 'I\'m a teapot',
	    '_421' => 'Misdirected Request',
	    '_422' => 'Unprocessable Entity',
	    '_423' => 'Locked',
	    '_424' => 'Failed Dependency',
	    '_426' => 'Upgrade Required',
	    '_428' => 'Precondition Required',
	    '_429' => 'Too Many Requests',
	    '_431' => 'Request Header Fields Too Large',
	    '_444' => 'Connection Closed Without Response',
	    '_451' => 'Unavailable For Legal Reasons',
	    '_499' => 'Client Closed Request',

	    /*5×× Server Error*/
	    '_500' => 'Internal Server Error',
	    '_501' => 'Not Implemented',
	    '_502' => 'Bad Gateway',
	    '_503' => 'Service Unavailable',
	    '_504' => 'Gateway Timeout',
	    '_505' => 'HTTP Version Not Supported',
	    '_506' => 'Variant Also Negotiates',
	    '_507' => 'Insufficient Storage',
	    '_508' => 'Loop Detected',
	    '_510' => 'Not Extended',
	    '_511' => 'Network Authentication Required',
	    '_599' => 'Network Connect Timeout Error',
	];

	public function getCode($code){
		$c = '_' . $code;

		if(isset($this->codes[$c])) {
			return $this->codes[$c];
		} else {
			return 'Undefined error encountered.';
		}
	}
}
?>