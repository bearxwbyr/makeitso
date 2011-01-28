<?php
/* makeitso
 *
 * The MIT License
 *
 * Copyright (c) 2010, Gary McGhee, Buzzware Solutions <contact@buzzware.com.au>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 */

require_once 'Console_Getargs_Combined.php';
require_once 'MakeItSoUtilities.php';

/**
 * Description of MakeItHow
 *
 * @author gary
 */
class MakeItHowBase {

	var $how;							// path to MakeItHow.php
	var $workingPath;			// current working path
	var $task = 'main';		// task to execute
	var $pars = array();	
	
	static function loadClass($how,$pars) {
		if (file_exists($how = realpath($how))) {
			print("Loading How file ".$how." ...\n");
			require_once $how;
			$result = new MakeItHow($pars);
			return $result;
		} else {
			print("Error! How file ".$how." doesn't exist\n");
			exit(1);
		}
	}

	static function getXpathValue($xml,$path) {
		$nodes = $xml->content->xpath($path);		// get matching nodes
		if (count($nodes)==0)
			return null;
		$node = $nodes[0];						// get first node
		$result = (string) $node[0];	// get text of node
		return $result;								// return text
	}

	function __construct($pars = NULL) {
		$this->pars = ($pars==NULL ? $pars : Console_Getargs_Combined::getArgs());
		$this->workingPath = getcwd();
	}

	function setXmlSimpleItems($whatXml) {
		foreach ($whatXml->content->simpleItems->item as $item) {
			$name = (string) $item['name'];
			$value = (string) $item[0];
			$this->{$name} = $value;
		}
	}
	
	function setCommandLineSimpleItems($pars) {
		foreach ($pars as $key => $value) {
			if (!is_numeric($key))
				$this->{$key} = $value;
		}
	}	

	function findXmlFile($whatname) {
		if ($whatname && file_exists($whatname = realpath($whatname))) {
			return $whatname;
		}
		$whatname = realpath($this->workingPath . DIRECTORY_SEPARATOR . 'MakeItWhat.xml');
		if (file_exists($whatname))
			return $whatname;		// found in working path
		return null;							// not found
	}

	function loadWhatXml($whatname) {
		print "Loading what file ".$whatname." ...\n\n";
		$filestring = file_get_contents($whatname); // load $whatname to $filestring
		$whatXml = new SimpleXMLElement($filestring);
		$this->setXmlSimpleItems($whatXml);
		return $whatXml;
	}

	function callTask($task) {
		$result = NULL;
		if ($task && method_exists($this,$task)) {
			print "Calling task ".$task." ...\n\n";
			$result = $this->{$task}();
		} else {
			print "Failed calling task ".$task." - task doesn't exist\n";
			exit(1);			
		}
		return $result;
	}

	// override this to configure differently
	function configureWhat() {
		if ($whatname = isset($this->pars['what']) ? $this->pars['what'] : null)
			$what = $this->findXmlFile($whatname);
		if ($what)
			$this->loadWhatXml($what);
		$this->setCommandLineSimpleItems($this->pars);
		if (isset($this->pars[1]))
			$this->task = $this->pars[1];		
	}
}
?>
