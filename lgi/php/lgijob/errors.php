<?php

/**
 * This class for representing error in lgijob
 * @author deepthi
 * @package lgijob
 *
 */
class LGIError
{
	const NOAPPLICATION=0;
	const NOSERVER   =1;
	const NOKEY=2;
	const NOCERT=3;
	const NOCA=4;
	const NOUSER=5;
	const NOGROUP=6;
	const NOJOBID=7;
	const RESPONSE=20;

	static $errormessage=array(
	LGIError::NOAPPLICATION => "No Application specified",
	LGIError::NOSERVER => "No Project server specified",
	LGIError::NOKEY => "No user key specified",
	LGIError::NOCERT => "No user certificate specified",
	LGIError::NOCA => "No CA certificate specified",
	LGIError::NOUSER => "No User specified",
	LGIError::NOGROUP => "No group specified",
	LGIError::NOJOBID => "No Job specified"
              
	);

	private $lasterrorno;
	private $lasterrortype=LGIErrorType::NOERROR;
	private $lasterrormessage;
	 
	/**
	 * set error number,error type and error message of this instance. 
	 * It overwrites the previous error.
	 * @param $errorno
	 * @param $errortype
	 * @param $message
	 */
	function setError($errorno,$errortype,$message="No Error")
	{
		$this->lasterrorno=$errorno;
		$this->lasterrortype=$errortype;
		$this->lasterrormessage=$message;
	}

	function getErrorType()
	{
		return $this->lasterrortype;
	}

	function getErrorNo()
	{
		return $this->lasterrorno;
	}

	/**
	 * Clear errors recorded.
	 */
	function initError()
	{
		$this->lasterrortype=LGIErrorType::NOERROR;
		$this->lasterrorno=0;
		$this->lasterrormessage="";
	}

	/**
	 * Returns the error message as string
	 * @return string
	 */
	function getErrorString()
	{
		switch($this->lasterrortype)
		{
			case LGIErrorType::NOERROR:
				return NULL;
				break;
			case LGIErrorType::INPUTERROR:
				return LGIError::$errormessage[$this->lasterrorno];
				break;
			case LGIErrorType::CURLERROR:
				return $this->lasterrormessage;
				break;
			case LGIErrorType::RESPONSEERROR:
				return $this->lasterrormessage;
				break;
			case LGIErrorType::EXECERROR:
				return $this->lasterrormessage;
				break;
			default:
				return NULL;
				break;

		}
		 
	}

}
 
class LGIErrorType
{
	const NOERROR=0;
	const INPUTERROR=1;
	const EXECERROR=2;
	const RESPONSEERROR=3;
	const CURLERROR=4;
	 
}

?>
