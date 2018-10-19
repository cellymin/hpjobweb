<?php  if(!defined('PATH_LC')){exit;}
/**
 * A class for reading Microsoft Excel (97/2003) Spreadsheets.
 *
 * Version 2.21
 *
 * Enhanced and maintained by Matt Kruse < http://mattkruse.com >
 * Maintained at http://code.google.com/p/php-excel-reader/
 *
 * Format parsing and MUCH more contributed by:
 *    Matt Roxburgh < http://www.roxburgh.me.uk >
 *
 * DOCUMENTATION
 * =============
 *   http://code.google.com/p/php-excel-reader/wiki/Documentation
 *
 * CHANGE LOG
 * ==========
 *   http://code.google.com/p/php-excel-reader/wiki/ChangeHistory
 *
 * DISCUSSION/SUPPORT
 * ==================
 *   http://groups.google.com/group/php-excel-reader-discuss/topics
 *
 * --------------------------------------------------------------------------
 *
 * Originally developed by Vadim Tkachenko under the name PHPExcelReader.
 * (http://sourceforge.net/projects/phpexcelreader)
 * Based on the Java version by Andy Khan (http://www.andykhan.com).  Now
 * maintained by David Sanders.  Reads only Biff 7 and Biff 8 formats.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Spreadsheet
 * @package	Spreadsheet_Excel_Reader
 * @author	 Vadim Tkachenko <vt@apachephp.com>
 * @license	http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version	CVS: $Id: reader.php 19 2007-03-13 12:42:41Z shangxiao $
 * @link	   http://pear.php.net/package/Spreadsheet_Excel_Reader
 * @see		OLE, Spreadsheet_Excel_Writer
 * --------------------------------------------------------------------------
 */

define('NUM_BIG_BLOCK_DEPOT_BLOCKS_POS', 0x2c);
define('SMALL_BLOCK_DEPOT_BLOCK_POS', 0x3c);
define('ROOT_START_BLOCK_POS', 0x30);
define('BIG_BLOCK_SIZE', 0x200);
define('SMALL_BLOCK_SIZE', 0x40);
define('EXTENSION_BLOCK_POS', 0x44);
define('NUM_EXTENSION_BLOCK_POS', 0x48);
define('PROPERTY_STORAGE_BLOCK_SIZE', 0x80);
define('BIG_BLOCK_DEPOT_BLOCKS_POS', 0x4c);
define('SMALL_BLOCK_THRESHOLD', 0x1000);
// property storage offsets
define('SIZE_OF_NAME_POS', 0x40);
define('TYPE_POS', 0x42);
define('START_BLOCK_POS', 0x74);
define('SIZE_POS', 0x78);
define('IDENTIFIER_OLE', pack("CCCCCCCC",0xd0,0xcf,0x11,0xe0,0xa1,0xb1,0x1a,0xe1));


function GetInt4d($data, $pos) {
	$value = ord($data[$pos]) | (ord($data[$pos+1])	<< 8) | (ord($data[$pos+2]) << 16) | (ord($data[$pos+3]) << 24);
	if ($value>=4294967294) {
		$value=-2;
	}
	return $value;
}

// http://uk.php.net/manual/en/function.getdate.php
function gmgetdate($ts = null){
	$k = array('seconds','minutes','hours','mday','wday','mon','year','yday','weekday','month',0);
	return(array_comb($k,explode(":",gmdate('s:i:G:j:w:n:Y:z:l:F:U',is_null($ts)?time():$ts))));
	} 

// Added for PHP4 compatibility
function array_comb($array1, $array2) {
	$out = array();
	foreach ($array1 as $key => $value) {
		$out[$value] = $array2[$key];
	}
	return $out;
}

function kv($data,$pos) {
	return ord($data[$pos]) | ord($data[$pos+1])<<8;
}

class OLERead {
	var $data = '';
	function OLERead(){	}

	function read($sFileName){
		// check if file exist and is readable (Darko Miljanovic)
		if(!is_readable($sFileName)) {
			$this->error = 1;
			return false;
		}
		$this->data = @file_get_contents($sFileName);
		if (!$this->data) {
			$this->error = 1;
			return false;
   		}
   		if (substr($this->data, 0, 8) != IDENTIFIER_OLE) {
			$this->error = 1;
			return false;
   		}
		$this->numBigBlockDepotBlocks = GetInt4d($this->data, NUM_BIG_BLOCK_DEPOT_BLOCKS_POS);
		$this->sbdStartBlock = GetInt4d($this->data, SMALL_BLOCK_DEPOT_BLOCK_POS);
		$this->rootStartBlock = GetInt4d($this->data, ROOT_START_BLOCK_POS);
		$this->extensionBlock = GetInt4d($this->data, EXTENSION_BLOCK_POS);
		$this->numExtensionBlocks = GetInt4d($this->data, NUM_EXTENSION_BLOCK_POS);

		$bigBlockDepotBlocks = array();
		$pos = BIG_BLOCK_DEPOT_BLOCKS_POS;
		$bbdBlocks = $this->numBigBlockDepotBlocks;
		if ($this->numExtensionBlocks != 0) {
			$bbdBlocks = (BIG_BLOCK_SIZE - BIG_BLOCK_DEPOT_BLOCKS_POS)/4;
		}

		for ($i = 0; $i < $bbdBlocks; $i++) {
			$bigBlockDepotBlocks[$i] = GetInt4d($this->data, $pos);
			$pos += 4;
		}


		for ($j = 0; $j < $this->numExtensionBlocks; $j++) {
			$pos = ($this->extensionBlock + 1) * BIG_BLOCK_SIZE;
			$blocksToRead = min($this->numBigBlockDepotBlocks - $bbdBlocks, BIG_BLOCK_SIZE / 4 - 1);

			for ($i = $bbdBlocks; $i < $bbdBlocks + $blocksToRead; $i++) {
				$bigBlockDepotBlocks[$i] = GetInt4d($this->data, $pos);
				$pos += 4;
			}

			$bbdBlocks += $blocksToRead;
			if ($bbdBlocks < $this->numBigBlockDepotBlocks) {
				$this->extensionBlock = GetInt4d($this->data, $pos);
			}
		}

		// readBigBlockDepot
		$pos = 0;
		$index = 0;
		$this->bigBlockChain = array();

		for ($i = 0; $i < $this->numBigBlockDepotBlocks; $i++) {
			$pos = ($bigBlockDepotBlocks[$i] + 1) * BIG_BLOCK_SIZE;
			//echo "pos = $pos";
			for ($j = 0 ; $j < BIG_BLOCK_SIZE / 4; $j++) {
				$this->bigBlockChain[$index] = GetInt4d($this->data, $pos);
				$pos += 4 ;
				$index++;
			}
		}

		// readSmallBlockDepot();
		$pos = 0;
		$index = 0;
		$sbdBlock = $this->sbdStartBlock;
		$this->smallBlockChain = array();

		while ($sbdBlock != -2) {
		  $pos = ($sbdBlock + 1) * BIG_BLOCK_SIZE;
		  for ($j = 0; $j < BIG_BLOCK_SIZE / 4; $j++) {
			$this->smallBlockChain[$index] = GetInt4d($this->data, $pos);
			$pos += 4;
			$index++;
		  }
		  $sbdBlock = $this->bigBlockChain[$sbdBlock];
		}


		// readData(rootStartBlock)
		$block = $this->rootStartBlock;
		$pos = 0;
		$this->entry = $this->__readData($block);
		$this->__readPropertySets();
	}

	function __readData($bl) {
		$block = $bl;
		$pos = 0;
		$data = '';
		while ($block != -2)  {
			$pos = ($block + 1) * BIG_BLOCK_SIZE;
			$data = $data.substr($this->data, $pos, BIG_BLOCK_SIZE);
			$block = $this->bigBlockChain[$block];
		}
		return $data;
	 }

	function __readPropertySets(){
		$offset = 0;
		while ($offset < strlen($this->entry)) {
			$d = substr($this->entry, $offset, PROPERTY_STORAGE_BLOCK_SIZE);
			$nameSize = ord($d[SIZE_OF_NAME_POS]) | (ord($d[SIZE_OF_NAME_POS+1]) << 8);
			$type = ord($d[TYPE_POS]);
			$startBlock = GetInt4d($d, START_BLOCK_POS);
			$size = GetInt4d($d, SIZE_POS);
			$name = '';
			for ($i = 0; $i < $nameSize ; $i++) {
				$name .= $d[$i];
			}
			$name = str_replace("\x00", "", $name);
			$this->props[] = array (
				'name' => $name,
				'type' => $type,
				'startBlock' => $startBlock,
				'size' => $size);
			if ((strtolower($name) == "workbook") || ( strtolower($name) == "book")) {
				$this->wrkbook = count($this->props) - 1;
			}
			if ($name == "Root Entry") {
				$this->rootentry = count($this->props) - 1;
			}
			$offset += PROPERTY_STORAGE_BLOCK_SIZE;
		}

	}


	function getWorkBook(){
		if ($this->props[$this->wrkbook]['size'] < SMALL_BLOCK_THRESHOLD){
			$rootdata = $this->__readData($this->props[$this->rootentry]['startBlock']);
			$streamData = '';
			$block = $this->props[$this->wrkbook]['startBlock'];
			$pos = 0;
			while ($block != -2) {
	  			  $pos = $block * SMALL_BLOCK_SIZE;
				  $streamData .= substr($rootdata, $pos, SMALL_BLOCK_SIZE);
				  $block = $this->smallBlockChain[$block];
			}
			return $streamData;
		}else{
			$numBlocks = $this->props[$this->wrkbook]['size'] / BIG_BLOCK_SIZE;
			if ($this->props[$this->wrkbook]['size'] % BIG_BLOCK_SIZE != 0) {
				$numBlocks++;
			}

			if ($numBlocks == 0) return '';
			$streamData = '';
			$block = $this->props[$this->wrkbook]['startBlock'];
			$pos = 0;
			while ($block != -2) {
			  $pos = ($block + 1) * BIG_BLOCK_SIZE;
			  $streamData .= substr($this->data, $pos, BIG_BLOCK_SIZE);
			  $block = $this->bigBlockChain[$block];
			}
			return $streamData;
		}
	}

}

define('SPREADSHEET_EXCEL_READER_BIFF8',			 0x600);
define('SPREADSHEET_EXCEL_READER_BIFF7',			 0x500);
define('SPREADSHEET_EXCEL_READER_WORKBOOKGLOBALS',   0x5);
define('SPREADSHEET_EXCEL_READER_WORKSHEET',		 0x10);
define('SPREADSHEET_EXCEL_READER_TYPE_BOF',		  0x809);
define('SPREADSHEET_EXCEL_READER_TYPE_EOF',		  0x0a);
define('SPREADSHEET_EXCEL_READER_TYPE_BOUNDSHEET',   0x85);
define('SPREADSHEET_EXCEL_READER_TYPE_DIMENSION',	0x200);
define('SPREADSHEET_EXCEL_READER_TYPE_ROW',		  0x208);
define('SPREADSHEET_EXCEL_READER_TYPE_DBCELL',	   0xd7);
define('SPREADSHEET_EXCEL_READER_TYPE_FILEPASS',	 0x2f);
define('SPREADSHEET_EXCEL_READER_TYPE_NOTE',		 0x1c);
define('SPREADSHEET_EXCEL_READER_TYPE_TXO',		  0x1b6);
define('SPREADSHEET_EXCEL_READER_TYPE_RK',		   0x7e);
define('SPREADSHEET_EXCEL_READER_TYPE_RK2',		  0x27e);
define('SPREADSHEET_EXCEL_READER_TYPE_MULRK',		0xbd);
define('SPREADSHEET_EXCEL_READER_TYPE_MULBLANK',	 0xbe);
define('SPREADSHEET_EXCEL_READER_TYPE_INDEX',		0x20b);
define('SPREADSHEET_EXCEL_READER_TYPE_SST',		  0xfc);
define('SPREADSHEET_EXCEL_READER_TYPE_EXTSST',	   0xff);
define('SPREADSHEET_EXCEL_READER_TYPE_CONTINUE',	 0x3c);
define('SPREADSHEET_EXCEL_READER_TYPE_LABEL',		0x204);
define('SPREADSHEET_EXCEL_READER_TYPE_LABELSST',	 0xfd);
define('SPREADSHEET_EXCEL_READER_TYPE_NUMBER',	   0x203);
define('SPREADSHEET_EXCEL_READER_TYPE_NAME',		 0x18);
define('SPREADSHEET_EXCEL_READER_TYPE_ARRAY',		0x221);
define('SPREADSHEET_EXCEL_READER_TYPE_STRING',	   0x207);
define('SPREADSHEET_EXCEL_READER_TYPE_FORMULA',	  0x406);
define('SPREADSHEET_EXCEL_READER_TYPE_FORMULA2',	 0x6);
define('SPREADSHEET_EXCEL_READER_TYPE_FORMAT',	   0x41e);
define('SPREADSHEET_EXCEL_READER_TYPE_XF',		   0xe0);
define('SPREADSHEET_EXCEL_READER_TYPE_BOOLERR',	  0x205);
define('SPREADSHEET_EXCEL_READER_TYPE_FONT',	  0x0031);
define('SPREADSHEET_EXCEL_READER_TYPE_PALETTE',	  0x0092);
define('SPREADSHEET_EXCEL_READER_TYPE_UNKNOWN',	  0xffff);
define('SPREADSHEET_EXCEL_READER_TYPE_NINETEENFOUR', 0x22);
define('SPREADSHEET_EXCEL_READER_TYPE_MERGEDCELLS',  0xE5);
define('SPREADSHEET_EXCEL_READER_UTCOFFSETDAYS' ,	25569);
define('SPREADSHEET_EXCEL_READER_UTCOFFSETDAYS1904', 24107);
define('SPREADSHEET_EXCEL_READER_MSINADAY',		  86400);
define('SPREADSHEET_EXCEL_READER_TYPE_HYPER',	     0x01b8);
define('SPREADSHEET_EXCEL_READER_TYPE_COLINFO',	     0x7d);
define('SPREADSHEET_EXCEL_READER_TYPE_DEFCOLWIDTH',  0x55);
define('SPREADSHEET_EXCEL_READER_TYPE_STANDARDWIDTH', 0x99);
define('SPREADSHEET_EXCEL_READER_DEF_NUM_FORMAT',	"%s");


/*
* Main Class
*/
class Spreadsheet_Excel_Reader {

	// MK: Added to make data retrieval easier
	var $colnames = array();
	var $colindexes = array();
	var $standardColWidth = 0;
	var $defaultColWidth = 0;

	function myHex($d) {
		if ($d < 16) return "0" . dechex($d);
		return dechex($d);
	}
	
	function dumpHexData($data, $pos, $length) {
		$info = "";
		for ($i = 0; $i <= $length; $i++) {
			$info .= ($i==0?"":" ") . $this->myHex(ord($data[$pos + $i])) . (ord($data[$pos + $i])>31? "[" . $data[$pos + $i] . "]":'');
		}
		return $info;
	}

	function getCol($col) {
		if (is_string($col)) {
			$col = strtolower($col);
			if (array_key_exists($col,$this->colnames)) {
				$col = $this->colnames[$col];
			}
		}
		return $col;
	}

	// PUBLIC API FUNCTIONS
	// --------------------

	function val($row,$col,$sheet=0) {
		$col = $this->getCol($col);
		if (array_key_exists($row,$this->sheets[$sheet]['cells']) && array_key_exists($col,$this->sheets[$sheet]['cells'][$row])) {
			return $this->sheets[$sheet]['cells'][$row][$col];
		}
		return "";
	}
	function value($row,$col,$sheet=0) {
		return $this->val($row,$col,$sheet);
	}
	function info($row,$col,$type='',$sheet=0) {
		$col = $this->getCol($col);
		if (array_key_exists('cellsInfo',$this->sheets[$sheet])
				&& array_key_exists($row,$this->sheets[$sheet]['cellsInfo'])
				&& array_key_exists($col,$this->sheets[$sheet]['cellsInfo'][$row])
				&& array_key_exists($type,$this->sheets[$sheet]['cellsInfo'][$row][$col])) {
			return $this->sheets[$sheet]['cellsInfo'][$row][$col][$type];
		}
		return "";
	}
	function type($row,$col,$sheet=0) {
		return $this->info($row,$col,'type',$sheet);
	}
	function raw($row,$col,$sheet=0) {
		return $this->info($row,$col,'raw',$sheet);
	}
	function rowspan($row,$col,$sheet=0) {
		$val = $this->info($row,$col,'rowspan',$sheet);
		if ($val=="") { return 1; }
		return $val;
	}
	function colspan($row,$col,$sheet=0) {
		$val = $this->info($row,$col,'colspan',$sheet);
		if ($val=="") { return 1; }
		return $val;
	}
	function hyperlink($row,$col,$sheet=0) {
		$link = $this->sheets[$sheet]['cellsInfo'][$row][$col]['hyperlink'];
		if ($link) {
			return $link['link'];
		}
		return '';
	}
	function rowcount($sheet=0) {
		return $this->sheets[$sheet]['numRows'];
	}
	function colcount($sheet=0) {
		return $this->sheets[$sheet]['numCols'];
	}
	function colwidth($col,$sheet=0) {
		// Col width is actually the width of the number 0. So we have to estimate and come close
		return $this->colInfo[$sheet][$col]['width']/9142*200; 
	}
	function colhidden($col,$sheet=0) {
		return !!$this->colInfo[$sheet][$col]['hidden'];
	}
	function rowheight($row,$sheet=0) {
		return $this->rowInfo[$sheet][$row]['height'];
	}
	function rowhidden($row,$sheet=0) {
		return !!$this->rowInfo[$sheet][$row]['hidden'];
	}
	
	// GET THE CSS FOR FORMATTING
	// ==========================
	function style($row,$col,$sheet=0,$properties='') {
		$css = "";
		$font=$this->font($row,$col,$sheet);
		if ($font!="") {
			$css .= "font-family:$font;";
		}
		$align=$this->align($row,$col,$sheet);
		if ($align!="") {
			$css .= "text-align:$align;";
		}
		$height=$this->height($row,$col,$sheet);
		if ($height!="") {
			$css .= "font-size:$height"."px;";
		}
		$bgcolor=$this->bgColor($row,$col,$sheet);
		if ($bgcolor!="") {
			$bgcolor = $this->colors[$bgcolor];
			$css .= "background-color:$bgcolor;";
		}
		$color=$this->color($row,$col,$sheet);
		if ($color!="") {
			$css .= "color:$color;";
		}
		$bold=$this->bold($row,$col,$sheet);
		if ($bold) {
			$css .= "font-weight:bold;";
		}
		$italic=$this->italic($row,$col,$sheet);
		if ($italic) {
			$css .= "font-style:italic;";
		}
		$underline=$this->underline($row,$col,$sheet);
		if ($underline) {
			$css .= "text-decoration:underline;";
		}
		// Borders
		$bLeft = $this->borderLeft($row,$col,$sheet);
		$bRight = $this->borderRight($row,$col,$sheet);
		$bTop = $this->borderTop($row,$col,$sheet);
		$bBottom = $this->borderBottom($row,$col,$sheet);
		$bLeftCol = $this->borderLeftColor($row,$col,$sheet);
		$bRightCol = $this->borderRightColor($row,$col,$sheet);
		$bTopCol = $this->borderTopColor($row,$col,$sheet);
		$bBottomCol = $this->borderBottomColor($row,$col,$sheet);
		// Try to output the minimal required style
		if ($bLeft!="" && $bLeft==$bRight && $bRight==$bTop && $bTop==$bBottom) {
			$css .= "border:" . $this->lineStylesCss[$bLeft] .";";
		}
		else {
			if ($bLeft!="") { $css .= "border-left:" . $this->lineStylesCss[$bLeft] .";"; }
			if ($bRight!="") { $css .= "border-right:" . $this->lineStylesCss[$bRight] .";"; }
			if ($bTop!="") { $css .= "border-top:" . $this->lineStylesCss[$bTop] .";"; }
			if ($bBottom!="") { $css .= "border-bottom:" . $this->lineStylesCss[$bBottom] .";"; }
		}
		// Only output border colors if there is an actual border specified
		if ($bLeft!="" && $bLeftCol!="") { $css .= "border-left-color:" . $bLeftCol .";"; }
		if ($bRight!="" && $bRightCol!="") { $css .= "border-right-color:" . $bRightCol .";"; }
		if ($bTop!="" && $bTopCol!="") { $css .= "border-top-color:" . $bTopCol . ";"; }
		if ($bBottom!="" && $bBottomCol!="") { $css .= "border-bottom-color:" . $bBottomCol .";"; }
		
		return $css;
	}
	
	// FORMAT PROPERTIES
	// =================
	function format($row,$col,$sheet=0) {
		return $this->info($row,$col,'format',$sheet);
	}
	function formatIndex($row,$col,$sheet=0) {
		return $this->info($row,$col,'formatIndex',$sheet);
	}
	function formatColor($row,$col,$sheet=0) {
		return $this->info($row,$col,'formatColor',$sheet);
	}
	
	// CELL (XF) PROPERTIES
	// ====================
	function xfRecord($row,$col,$sheet=0) {
		$xfIndex = $this->info($row,$col,'xfIndex',$sheet);
		if ($xfIndex!="") {
			return $this->xfRecords[$xfIndex];
		}
		return null;
	}
	function xfProperty($row,$col,$sheet,$prop) {
		$xfRecord = $this->xfRecord($row,$col,$sheet);
		if ($xfRecord!=null) {
			return $xfRecord[$prop];
		}
		return "";
	}
	function align($row,$col,$sheet=0) {
		return $this->xfProperty($row,$col,$sheet,'align');
	}
	function bgColor($row,$col,$sheet=0) {
		return $this->xfProperty($row,$col,$sheet,'bgColor');
	}
	function borderLeft($row,$col,$sheet=0) {
		return $this->xfProperty($row,$col,$sheet,'borderLeft');
	}
	function borderRight($row,$col,$sheet=0) {
		return $this->xfProperty($row,$col,$sheet,'borderRight');
	}
	function borderTop($row,$col,$sheet=0) {
		return $this->xfProperty($row,$col,$sheet,'borderTop');
	}
	function borderBottom($row,$col,$sheet=0) {
		return $this->xfProperty($row,$col,$sheet,'borderBottom');
	}
	function borderLeftColor($row,$col,$sheet=0) {
		return $this->colors[$this->xfProperty($row,$col,$sheet,'borderLeftColor')];
	}
	function borderRightColor($row,$col,$sheet=0) {
		return $this->colors[$this->xfProperty($row,$col,$sheet,'borderRightColor')];
	}
	function borderTopColor($row,$col,$sheet=0) {
		return $this->colors[$this->xfProperty($row,$col,$sheet,'borderTopColor')];
	}
	function borderBottomColor($row,$col,$sheet=0) {
		return $this->colors[$this->xfProperty($row,$col,$sheet,'borderBottomColor')];
	}

	// FONT PROPERTIES
	// ===============
	function fontRecord($row,$col,$sheet=0) {
	    $xfRecord = $this->xfRecord($row,$col,$sheet);
		if ($xfRecord!=null) {
			$font = $xfRecord['fontIndex'];
			if ($font!=null) {
				return $this->fontRecords[$font];
			}
		}
		return null;
	}
	function fontProperty($row,$col,$sheet=0,$prop) {
		$font = $this->fontRecord($row,$col,$sheet);
		if ($font!=null) {
			return $font[$prop];
		}
		return false;
	}
	function fontIndex($row,$col,$sheet=0) {
		return $this->xfProperty($row,$col,$sheet,'fontIndex');
	}
	function color($row,$col,$sheet=0) {
		$formatColor = $this->formatColor($row,$col,$sheet);
		if ($formatColor!="") {
			return $formatColor;
		}
		$ci = $this->fontProperty($row,$col,$sheet,'color');
                return $this->rawColor($ci);
        }
        function rawColor($ci) {
		if (($ci <> 0x7FFF) && ($ci <> '')) {
			return $this->colors[$ci];
		}
		return "";
	}
	function bold($row,$col,$sheet=0) {
		return $this->fontProperty($row,$col,$sheet,'bold');
	}
	function italic($row,$col,$sheet=0) {
		return $this->fontProperty($row,$col,$sheet,'italic');
	}
	function underline($row,$col,$sheet=0) {
		return $this->fontProperty($row,$col,$sheet,'under');
	}
	function height($row,$col,$sheet=0) {
		return $this->fontProperty($row,$col,$sheet,'height');
	}
	function font($row,$col,$sheet=0) {
		return $this->fontProperty($row,$col,$sheet,'font');
	}
	
	// DUMP AN HTML TABLE OF THE ENTIRE XLS DATA
	// =========================================
	function dump($row_numbers=false,$col_letters=false,$sheet=0,$table_class='excel') {
		$out = "<table class=\"$table_class\" cellspacing=0>";
		if ($col_letters) {
			$out .= "<thead>\n\t<tr>";
			if ($row_numbers) {
				$out .= "\n\t\t<th>&nbsp</th>";
			}
			for($i=1;$i<=$this->colcount($sheet);$i++) {
				$style = "width:" . ($this->colwidth($i,$sheet)*1) . "px;";
				if ($this->colhidden($i,$sheet)) {
					$style .= "display:none;";
				}
				$out .= "\n\t\t<th style=\"$style\">" . strtoupper($this->colindexes[$i]) . "</th>";
			}
			$out .= "</tr></thead>\n";
		}
		
		$out .= "<tbody>\n";
		for($row=1;$row<=$this->rowcount($sheet);$row++) {
			$rowheight = $this->rowheight($row,$sheet);
			$style = "height:" . ($rowheight*(4/3)) . "px;";
			if ($this->rowhidden($row,$sheet)) {
				$style .= "display:none;";
			}
			$out .= "\n\t<tr style=\"$style\">";
			if ($row_numbers) {
				$out .= "\n\t\t<th>$row</th>";
			}
			for($col=1;$col<=$this->colcount($sheet);$col++) {
				// Account for Rowspans/Colspans
				$rowspan = $this->rowspan($row,$col,$sheet);
				$colspan = $this->colspan($row,$col,$sheet);
				for($i=0;$i<$rowspan;$i++) {
					for($j=0;$j<$colspan;$j++) {
						if ($i>0 || $j>0) {
							$this->sheets[$sheet]['cellsInfo'][$row+$i][$col+$j]['dontprint']=1;
						}
					}
				}
				if(!$this->sheets[$sheet]['cellsInfo'][$row][$col]['dontprint']) {
					$style = $this->style($row,$col,$sheet);
					if ($this->colhidden($col,$sheet)) {
						$style .= "display:none;";
					}
					$out .= "\n\t\t<td style=\"$style\"" . ($colspan > 1?" colspan=$colspan":"") . ($rowspan > 1?" rowspan=$rowspan":"") . ">";
					$val = $this->val($row,$col,$sheet);
					if ($val=='') { $val="&nbsp;"; }
					else { 
						$val = htmlspecialchars($val);
						$link = $this->hyperlink($row,$col,$sheet);
						if ($link!='') {
							$val = "<a href=\"$link\">$val</a>";
						}
					}
					$out .= "<nobr>".nl2br($val)."</nobr>";
					$out .= "</td>";
				}
			}
			$out .= "</tr>\n";
		}
		$out .= "</tbody></table>";
		return $out;
	}
	
	// --------------
	// END PUBLIC API


	var $boundsheets = array();
	var $formatRecords = array();
	var $fontRecords = array();
	var $xfRecords = array();
	var $colInfo = array();
   	var $rowInfo = array();
	
	var $sst = array();
	var $sheets = array();

	var $data;
	var $_ole;
	var $_defaultEncoding = "UTF-8";
	var $_defaultFormat = SPREADSHEET_EXCEL_READER_DEF_NUM_FORMAT;
	var $_columnsFormat = array();
	var $_rowoffset = 1;
	var $_coloffset = 1;

	/**
	 * List of default date formats used by Excel
	 */
	var $dateFormats = array (
		0xe => "m/d/Y",
		0xf => "M-d-Y",
		0x10 => "d-M",
		0x11 => "M-Y",
		0x12 => "h:i a",
		0x13 => "h:i:s a",
		0x14 => "H:i",
		0x15 => "H:i:s",
		0x16 => "d/m/Y H:i",
		0x2d => "i:s",
		0x2e => "H:i:s",
		0x2f => "i:s.S"
	);

	/**
	 * Default number formats used by Excel
	 */
	var $numberFormats = array(
		0x1 => "0",
		0x2 => "0.00",
		0x3 => "#,##0",
		0x4 => "#,##0.00",
		0x5 => "\$#,##0;(\$#,##0)",
		0x6 => "\$#,##0;[Red](\$#,##0)",
		0x7 => "\$#,##0.00;(\$#,##0.00)",
		0x8 => "\$#,##0.00;[Red](\$#,##0.00)",
		0x9 => "0%",
		0xa => "0.00%",
		0xb => "0.00E+00",
		0x25 => "#,##0;(#,##0)",
		0x26 => "#,##0;[Red](#,##0)",
		0x27 => "#,##0.00;(#,##0.00)",
		0x28 => "#,##0.00;[Red](#,##0.00)",
		0x29 => "#,##0;(#,##0)",  // Not exactly
		0x2a => "\$#,##0;(\$#,##0)",  // Not exactly
		0x2b => "#,##0.00;(#,##0.00)",  // Not exactly
		0x2c => "\$#,##0.00;(\$#,##0.00)",  // Not exactly
		0x30 => "##0.0E+0"
	);

    var $colors = Array(
        0x00 => "#000000",
        0x01 => "#FFFFFF",
        0x02 => "#FF0000",
        0x03 => "#00FF00",
        0x04 => "#0000FF",
        0x05 => "#FFFF00",
        0x06 => "#FF00FF",
        0x07 => "#00FFFF",
        0x08 => "#000000",
        0x09 => "#FFFFFF",
        0x0A => "#FF0000",
        0x0B => "#00FF00",
        0x0C => "#0000FF",
        0x0D => "#FFFF00",
        0x0E => "#FF00FF",
        0x0F => "#00FFFF",
        0x10 => "#800000",
        0x11 => "#008000",
        0x12 => "#000080",
        0x13 => "#808000",
        0x14 => "#800080",
        0x15 => "#008080",
        0x16 => "#C0C0C0",
        0x17 => "#808080",
        0x18 => "#9999FF",
        0x19 => "#993366",
        0x1A => "#FFFFCC",
        0x1B => "#CCFFFF",
        0x1C => "#660066",
        0x1D => "#FF8080",
        0x1E => "#0066CC",
        0x1F => "#CCCCFF",
        0x20 => "#000080",
        0x21 => "#FF00FF",
        0x22 => "#FFFF00",
        0x23 => "#00FFFF",
        0x24 => "#800080",
        0x25 => "#800000",
        0x26 => "#008080",
        0x27 => "#0000FF",
        0x28 => "#00CCFF",
        0x29 => "#CCFFFF",
        0x2A => "#CCFFCC",
        0x2B => "#FFFF99",
        0x2C => "#99CCFF",
        0x2D => "#FF99CC",
        0x2E => "#CC99FF",
        0x2F => "#FFCC99",
        0x30 => "#3366FF",
        0x31 => "#33CCCC",
        0x32 => "#99CC00",
        0x33 => "#FFCC00",
        0x34 => "#FF9900",
        0x35 => "#FF6600",
        0x36 => "#666699",
        0x37 => "#969696",
        0x38 => "#003366",
        0x39 => "#339966",
        0x3A => "#003300",
        0x3B => "#333300",
        0x3C => "#993300",
        0x3D => "#993366",
        0x3E => "#333399",
        0x3F => "#333333",
        0x40 => "#000000",
        0x41 => "#FFFFFF",

        0x43 => "#000000",
        0x4D => "#000000",
        0x4E => "#FFFFFF",
        0x4F => "#000000",
        0x50 => "#FFFFFF",
        0x51 => "#000000",

        0x7FFF => "#000000"
    );

	var $lineStyles = array(
		0x00 => "",
		0x01 => "Thin",
		0x02 => "Medium",
		0x03 => "Dashed",
		0x04 => "Dotted",
		0x05 => "Thick",
		0x06 => "Double",
		0x07 => "Hair",
		0x08 => "Medium dashed",
		0x09 => "Thin dash-dotted",
		0x0A => "Medium dash-dotted",
		0x0B => "Thin dash-dot-dotted",
		0x0C => "Medium dash-dot-dotted",
		0x0D => "Slanted medium dash-dotted"
	);	

	var $lineStylesCss = array(
		"Thin" => "1px solid", 
		"Medium" => "2px solid", 
		"Dashed" => "1px dashed", 
		"Dotted" => "1px dotted", 
		"Thick" => "3px solid", 
		"Double" => "double", 
		"Hair" => "1px solid", 
		"Medium dashed" => "2px dashed", 
		"Thin dash-dotted" => "1px dashed", 
		"Medium dash-dotted" => "2px dashed", 
		"Thin dash-dot-dotted" => "1px dashed", 
		"Medium dash-dot-dotted" => "2px dashed", 
		"Slanted medium dash-dotte" => "2px dashed" 
	);
	
	function read16bitstring($data, $start) {
		$len = 0;
		while (ord($data[$start + $len]) + ord($data[$start + $len + 1]) > 0) $len++;
		return substr($data, $start, $len);
	}
	
	// ADDED by Matt Kruse for better formatting
	function _format_value($format,$num,$f) {
		// 49==TEXT format
		// http://code.google.com/p/php-excel-reader/issues/detail?id=7
		if ( (!$f && $format=="%s") || ($f==49) || ($format=="GENERAL") ) { 
			return array('string'=>$num, 'formatColor'=>null); 
		}

		// Custom pattern can be POSITIVE;NEGATIVE;ZERO
		// The "text" option as 4th parameter is not handled
		$parts = explode(";",$format);
		$pattern = $parts[0];
		// Negative pattern
		if (count($parts)>2 && $num==0) {
			$pattern = $parts[2];
		}
		// Zero pattern
		if (count($parts)>1 && $num<0) {
			$pattern = $parts[1];
			$num = abs($num);
		}

		$color = "";
		$matches = array();
		$color_regex = "/^\[(BLACK|BLUE|CYAN|GREEN|MAGENTA|RED|WHITE|YELLOW)\]/i";
		if (preg_match($color_regex,$pattern,$matches)) {
			$color = strtolower($matches[1]);
			$pattern = preg_replace($color_regex,"",$pattern);
		}
		
		// In Excel formats, "_" is used to add spacing, which we can't do in HTML
		$pattern = preg_replace("/_./","",$pattern);
		
		// Some non-number characters are escaped with \, which we don't need
		$pattern = preg_replace("/\\\/","",$pattern);
		
		// Some non-number strings are quoted, so we'll get rid of the quotes
		$pattern = preg_replace("/\"/","",$pattern);

		// TEMPORARY - Convert # to 0
		$pattern = preg_replace("/\#/","0",$pattern);

		// Find out if we need comma formatting
		$has_commas = preg_match("/,/",$pattern);
		if ($has_commas) {
			$pattern = preg_replace("/,/","",$pattern);
		}

		// Handle Percentages
		if (preg_match("/\d(\%)([^\%]|$)/",$pattern,$matches)) {
			$num = $num * 100;
			$pattern = preg_replace("/(\d)(\%)([^\%]|$)/","$1%$3",$pattern);
		}

		// Handle the number itself
		$number_regex = "/(\d+)(\.?)(\d*)/";
		if (preg_match($number_regex,$pattern,$matches)) {
			$left = $matches[1];
			$dec = $matches[2];
			$right = $matches[3];
			if ($has_commas) {
				$formatted = number_format($num,strlen($right));
			}
			else {
				$sprintf_pattern = "%1.".strlen($right)."f";
				$formatted = sprintf($sprintf_pattern, $num);
			}
			$pattern = preg_replace($number_regex, $formatted, $pattern);
		}

		return array(
			'string'=>$pattern,
			'formatColor'=>$color
		);
	}

	/**
	 * Constructor
	 *
	 * Some basic initialisation
	 */
	function Spreadsheet_Excel_Reader($file='',$store_extended_info=true,$outputEncoding='') {
		$this->_ole = new OLERead();
		$this->setUTFEncoder('iconv');
		if ($outputEncoding != '') { 
			$this->setOutputEncoding($outputEncoding);
		}
		for ($i=1; $i<245; $i++) {
			$name = strtolower(( (($i-1)/26>=1)?chr(($i-1)/26+64):'') . chr(($i-1)%26+65));
			$this->colnames[$name] = $i;
			$this->colindexes[$i] = $name;
		}
		$this->store_extended_info = $store_extended_info;
		if ($file!="") {
			$this->read($file);
		}
	}

	/**
	 * Set the encoding method
	 */
	function setOutputEncoding($encoding) {
		$this->_defaultEncoding = $encoding;
	}

	/**
	 *  $encoder = 'iconv' or 'mb'
	 *  set iconv if you would like use 'iconv' for encode UTF-16LE to your encoding
	 *  set mb if you would like use 'mb_convert_encoding' for encode UTF-16LE to your encoding
	 */
	function setUTFEncoder($encoder = 'iconv') {
		$this->_encoderFunction = '';
		if ($encoder == 'iconv') {
			$this->_encoderFunction = function_exists('iconv') ? 'iconv' : '';
		} elseif ($encoder == 'mb') {
			$this->_encoderFunction = function_exists('mb_convert_encoding') ? 'mb_convert_encoding' : '';
		}
	}

	function setRowColOffset($iOffset) {
		$this->_rowoffset = $iOffset;
		$this->_coloffset = $iOffset;
	}

	/**
	 * Set the default number format
	 */
	function setDefaultFormat($sFormat) {
		$this->_defaultFormat = $sFormat;
	}

	/**
	 * Force a column to use a certain format
	 */
	function setColumnFormat($column, $sFormat) {
		$this->_columnsFormat[$column] = $sFormat;
	}

	/**
	 * Read the spreadsheet file using OLE, then parse
	 */
	function read($sFileName) {
		$res = $this->_ole->read($sFileName);

		// oops, something goes wrong (Darko Miljanovic)
		if($res === false) {
			// check error code
			if($this->_ole->error == 1) {
				// bad file
				die('The filename ' . $sFileName . ' is not readable');
			}
			// check other error codes here (eg bad fileformat, etc...)
		}
		$this->data = $this->_ole->getWorkBook();
		$this->_parse();
	}

	/**
	 * Parse a workbook
	 *
	 * @access private
	 * @return bool
	 */
	function _parse() {
		$pos = 0;
		$data = $this->data;

		$code = kv($data,$pos);
		$length = kv($data,$pos+2);
		$version = kv($data,$pos+4);
		$substreamType = kv($data,$pos+6);

		$this->version = $version;

		if (($version != SPREADSHEET_EXCEL_READER_BIFF8) &&
			($version != SPREADSHEET_EXCEL_READER_BIFF7)) {
			return false;
		}

		if ($substreamType != SPREADSHEET_EXCEL_READER_WORKBOOKGLOBALS){
			return false;
		}

		$pos += $length + 4;

		$code = kv($data,$pos);
		$length = kv($data,$pos+2);

		while ($code != SPREADSHEET_EXCEL_READER_TYPE_EOF) {
			switch ($code) {
				case SPREADSHEET_EXCEL_READER_TYPE_SST:
					$spos = $pos + 4;
					$limitpos = $spos + $length;
					$uniqueStrings = $this->_GetInt4d($data, $spos+4);
					$spos += 8;
					for ($i = 0; $i < $uniqueStrings; $i++) {
						// Read in the number of characters
						if ($spos == $limitpos) {
							$opcode = kv($data,$spos);
							$conlength = kv($data,$spos+2);
							if ($opcode != 0x3c) {
								return -1;
							}
							$spos += 4;
							$limitpos = $spos + $conlength;
						}
						$numChars = ord($data[$spos]) | (ord($data[$spos+1]) << 8);
						$spos += 2;
						$optionFlags = ord($data[$spos]);
						$spos++;
						$asciiEncoding = (($optionFlags & 0x01) == 0) ;
						$extendedString = ( ($optionFlags & 0x04) != 0);

						// See if string contains formatting information
						$richString = ( ($optionFlags & 0x08) != 0);

						if ($richString) {
							// Read in the crun
							$formattingRuns = kv($data,$spos);
							$spos += 2;
						}

						if ($extendedString) {
							// Read in cchExtRst
							$extendedRunLength = $this->_GetInt4d($data, $spos);
							$spos += 4;
						}

						$len = ($asciiEncoding)? $numChars : $numChars*2;
						if ($spos + $len < $limitpos) {
							$retstr = substr($data, $spos, $len);
							$spos += $len;
						}
						else{
							// found countinue
							$retstr = substr($data, $spos, $limitpos - $spos);
							$bytesRead = $limitpos - $spos;
							$charsLeft = $numChars - (($asciiEncoding) ? $bytesRead : ($bytesRead / 2));
							$spos = $limitpos;

							while ($charsLeft > 0){
								$opcode = kv($data,$spos);
								$conlength = kv($data,$spos+2);
								if ($opcode != 0x3c) {
									return -1;
								}
								$spos += 4;
								$limitpos = $spos + $conlength;
								$option = ord($data[$spos]);
								$spos += 1;
								if ($asciiEncoding && ($option == 0)) {
									$len = min($charsLeft, $limitpos - $spos); // min($charsLeft, $conlength);
									$retstr .= substr($data, $spos, $len);
									$charsLeft -= $len;
									$asciiEncoding = true;
								}
								elseif (!$asciiEncoding && ($option != 0)) {
									$len = min($charsLeft * 2, $limitpos - $spos); // min($charsLeft, $conlength);
									$retstr .= substr($data, $spos, $len);
									$charsLeft -= $len/2;
									$asciiEncoding = false;
								}
								elseif (!$asciiEncoding && ($option == 0)) {
									// Bummer - the string starts off as Unicode, but after the
									// continuation it is in straightforward ASCII encoding
									$len = min($charsLeft, $limitpos - $spos); // min($charsLeft, $conlength);
									for ($j = 0; $j < $len; $j++) {
										$retstr .= $data[$spos + $j].chr(0);
									}
									$charsLeft -= $len;
									$asciiEncoding = false;
								}
								else{
									$newstr = '';
									for ($j = 0; $j < strlen($retstr); $j++) {
										$newstr = $retstr[$j].chr(0);
									}
									$retstr = $newstr;
									$len = min($charsLeft * 2, $limitpos - $spos); // min($charsLeft, $conlength);
									$retstr .= substr($data, $spos, $len);
									$charsLeft -= $len/2;
									$asciiEncoding = false;
								}
								$spos += $len;
							}
						}
						$retstr = ($asciiEncoding) ? $retstr : $this->_encodeUTF16($retstr);

						if ($richString){
							$spos += 4 * $formattingRuns;
						}

						// For extended strings, skip over the extended string data
						if ($extendedString) {
							$spos += $extendedRunLength;
						}
						$this->sst[]=$retstr;
					}
					break;
				case SPREADSHEET_EXCEL_READER_TYPE_FILEPASS:
					return false;
					break;
				case SPREADSHEET_EXCEL_READER_TYPE_NAME:
					break;
				case SPREADSHEET_EXCEL_READER_TYPE_FORMAT:
					$indexCode = kv($data,$pos+4);
					if ($version == SPREADSHEET_EXCEL_READER_BIFF8) {
						$numchars = kv($data,$pos+6);
						if (ord($data[$pos+8]) == 0){
							$formatString = substr($data, $pos+9, $numchars);
						} else {
							$formatString = substr($data, $pos+9, $numchars*2);
						}
					} else {
						$numchars = ord($data[$pos+6]);
						$formatString = substr($data, $pos+7, $numchars*2);
					}
					$this->formatRecords[$indexCode] = $formatString;
					break;
				case SPREADSHEET_EXCEL_READER_TYPE_FONT:
						$height = kv($data,$pos+4);
						$option = kv($data,$pos+6);
						$color = kv($data,$pos+8);
						$weight = kv($data,$pos+10);
						$under  = ord($data[$pos+14]);
						$font = "";
						// Font name
						$numchars = ord($data[$pos+18]);
						if ((ord($data[$pos+19]) & 1) == 0){
						    $font = substr($data, $pos+20, $numchars);
						} else {
						    $font = substr($data, $pos+20, $numchars*2);
						    $font =  $this->_encodeUTF16($font); 
						}
						$this->fontRecords[] = array(
								'height' => $height / 20,
								'italic' => !!($option & 2),
								'color' => $color,
								'under' => !($under==0),
								'bold' => ($weight==700),
								'font' => $font,
								'raw' => $this->dumpHexData($data, $pos+3, $length)
								);
					    break;

				case SPREADSHEET_EXCEL_READER_TYPE_PALETTE:
						$colors = ord($data[$pos+4]) | ord($data[$pos+5]) << 8;
						for ($coli = 0; $coli < $colors; $coli++) {
						    $colOff = $pos + 2 + ($coli * 4);
  						    $colr = ord($data[$colOff]);
  						    $colg = ord($data[$colOff+1]);
  						    $colb = ord($data[$colOff+2]);
							$this->colors[0x07 + $coli] = '#' . $this->myhex($colr) . $this->myhex($colg) . $this->myhex($colb);
						}
					    break;

				case SPREADSHEET_EXCEL_READER_TYPE_XF:
						$fontIndexCode = (ord($data[$pos+4]) | ord($data[$pos+5]) << 8) - 1;
						$fontIndexCode = max(0,$fontIndexCode);
						$indexCode = ord($data[$pos+6]) | ord($data[$pos+7]) << 8;
						$alignbit = ord($data[$pos+10]) & 3;
						$bgi = (ord($data[$pos+22]) | ord($data[$pos+23]) << 8) & 0x3FFF;
						$bgcolor = ($bgi & 0x7F);
//						$bgcolor = ($bgi & 0x3f80) >> 7;
						$align = "";
						if ($alignbit==3) { $align="right"; }
						if ($alignbit==2) { $align="center"; }

						$fillPattern = (ord($data[$pos+21]) & 0xFC) >> 2;
						if ($fillPattern == 0) {
							$bgcolor = "";
						}

						$xf = array();
						$xf['formatIndex'] = $indexCode;
						$xf['align'] = $align;
						$xf['fontIndex'] = $fontIndexCode;
						$xf['bgColor'] = $bgcolor;
						$xf['fillPattern'] = $fillPattern;

						$border = ord($data[$pos+14]) | (ord($data[$pos+15]) << 8) | (ord($data[$pos+16]) << 16) | (ord($data[$pos+17]) << 24);
						$xf['borderLeft'] = $this->lineStyles[($border & 0xF)];
						$xf['borderRight'] = $this->lineStyles[($border & 0xF0) >> 4];
						$xf['borderTop'] = $this->lineStyles[($border & 0xF00) >> 8];
						$xf['borderBottom'] = $this->lineStyles[($border & 0xF000) >> 12];
						
						$xf['borderLeftColor'] = ($border & 0x7F0000) >> 16;
						$xf['borderRightColor'] = ($border & 0x3F800000) >> 23;
						$border = (ord($data[$pos+18]) | ord($data[$pos+19]) << 8);

						$xf['borderTopColor'] = ($border & 0x7F);
						$xf['borderBottomColor'] = ($border & 0x3F80) >> 7;
												
						if (array_key_exists($indexCode, $this->dateFormats)) {
							$xf['type'] = 'date';
							$xf['format'] = $this->dateFormats[$indexCode];
							if ($align=='') { $xf['align'] = 'right'; }
						}elseif (array_key_exists($indexCode, $this->numberFormats)) {
							$xf['type'] = 'number';
							$xf['format'] = $this->numberFormats[$indexCode];
							if ($align=='') { $xf['align'] = 'right'; }
						}else{
							$isdate = FALSE;
							$formatstr = '';
							if ($indexCode > 0){
								if (isset($this->formatRecords[$indexCode]))
									$formatstr = $this->formatRecords[$indexCode];
								if ($formatstr!="") {
									$tmp = preg_replace("/\;.*/","",$formatstr);
									$tmp = preg_replace("/^\[[^\]]*\]/","",$tmp);
									if (preg_match("/[^hmsday\/\-:\s\\\,AMP]/i", $tmp) == 0) { // found day and time format
										$isdate = TRUE;
										$formatstr = $tmp;
										$formatstr = str_replace(array('AM/PM','mmmm','mmm'), array('a','F','M'), $formatstr);
										// m/mm are used for both minutes and months - oh SNAP!
										// This mess tries to fix for that.
										// 'm' == minutes only if following h/hh or preceding s/ss
										$formatstr = preg_replace("/(h:?)mm?/","$1i", $formatstr);
										$formatstr = preg_replace("/mm?(:?s)/","i$1", $formatstr);
										// A single 'm' = n in PHP
										$formatstr = preg_replace("/(^|[^m])m([^m]|$)/", '$1n$2', $formatstr);
										$formatstr = preg_replace("/(^|[^m])m([^m]|$)/", '$1n$2', $formatstr);
										// else it's months
										$formatstr = str_replace('mm', 'm', $formatstr);
										// Convert single 'd' to 'j'
										$formatstr = preg_replace("/(^|[^d])d([^d]|$)/", '$1j$2', $formatstr);
										$formatstr = str_replace(array('dddd','ddd','dd','yyyy','yy','hh','h'), array('l','D','d','Y','y','H','g'), $formatstr);
										$formatstr = preg_replace("/ss?/", 's', $formatstr);
									}
								}
							}
							if ($isdate){
								$xf['type'] = 'date';
								$xf['format'] = $formatstr;
								if ($align=='') { $xf['align'] = 'right'; }
							}else{
								// If the format string has a 0 or # in it, we'll assume it's a number
								if (preg_match("/[0#]/", $formatstr)) {
									$xf['type'] = 'number';
									if ($align=='') { $xf['align']='right'; }
								}
								else {
								$xf['type'] = 'other';
								}
								$xf['format'] = $formatstr;
								$xf['code'] = $indexCode;
							}
						}
						$this->xfRecords[] = $xf;
					break;
				case SPREADSHEET_EXCEL_READER_TYPE_NINETEENFOUR:
					$this->nineteenFour = (ord($data[$pos+4]) == 1);
					break;
				case SPREADSHEET_EXCEL_READER_TYPE_BOUNDSHEET:
						$rec_offset = $this->_GetInt4d($data, $pos+4);
						$rec_typeFlag = ord($data[$pos+8]);
						$rec_visibilityFlag = ord($data[$pos+9]);
						$rec_length = ord($data[$pos+10]);

						if ($version == SPREADSHEET_EXCEL_READER_BIFF8){
							$chartype =  ord($data[$pos+11]);
							if ($chartype == 0){
								$rec_name	= substr($data, $pos+12, $rec_length);
							} else {
								$rec_name	= $this->_encodeUTF16(substr($data, $pos+12, $rec_length*2));
							}
						}elseif ($version == SPREADSHEET_EXCEL_READER_BIFF7){
								$rec_name	= substr($data, $pos+11, $rec_length);
						}
					$this->boundsheets[] = array('name'=>$rec_name,'offset'=>$rec_offset);
					break;

			}

			$pos += $length + 4;
			$code = ord($data[$pos]) | ord($data[$pos+1])<<8;
			$length = ord($data[$pos+2]) | ord($data[$pos+3])<<8;
		}

		foreach ($this->boundsheets as $key=>$val){
			$this->sn = $key;
			$this->_parsesheet($val['offset']);
		}
		return true;
	}

	/**
	 * Parse a worksheet
	 */
	function _parsesheet($spos) {
		$cont = true;
		$data = $this->data;
		// read BOF
		$code = ord($data[$spos]) | ord($data[$spos+1])<<8;
		$length = ord($data[$spos+2]) | ord($data[$spos+3])<<8;

		$version = ord($data[$spos + 4]) | ord($data[$spos + 5])<<8;
		$substreamType = ord($data[$spos + 6]) | ord($data[$spos + 7])<<8;

		if (($version != SPREADSHEET_EXCEL_READER_BIFF8) && ($version != SPREADSHEET_EXCEL_READER_BIFF7)) {
			return -1;
		}

		if ($substreamType != SPREADSHEET_EXCEL_READER_WORKSHEET){
			return -2;
		}
		$spos += $length + 4;
		while($cont) {
			$lowcode = ord($data[$spos]);
			if ($lowcode == SPREADSHEET_EXCEL_READER_TYPE_EOF) break;
			$code = $lowcode | ord($data[$spos+1])<<8;
			$length = ord($data[$spos+2]) | ord($data[$spos+3])<<8;
			$spos += 4;
			$this->sheets[$this->sn]['maxrow'] = $this->_rowoffset - 1;
			$this->sheets[$this->sn]['maxcol'] = $this->_coloffset - 1;
			unset($this->rectype);
			switch ($code) {
				case SPREADSHEET_EXCEL_READER_TYPE_DIMENSION:
					if (!isset($this->numRows)) {
						if (($length == 10) ||  ($version == SPREADSHEET_EXCEL_READER_BIFF7)){
							$this->sheets[$this->sn]['numRows'] = ord($data[$spos+2]) | ord($data[$spos+3]) << 8;
							$this->sheets[$this->sn]['numCols'] = ord($data[$spos+6]) | ord($data[$spos+7]) << 8;
						} else {
							$this->sheets[$this->sn]['numRows'] = ord($data[$spos+4]) | ord($data[$spos+5]) << 8;
							$this->sheets[$this->sn]['numCols'] = ord($data[$spos+10]) | ord($data[$spos+11]) << 8;
						}
					}
					break;
				case SPREADSHEET_EXCEL_READER_TYPE_MERGEDCELLS:
					$cellRanges = ord($data[$spos]) | ord($data[$spos+1])<<8;
					for ($i = 0; $i < $cellRanges; $i++) {
						$fr =  ord($data[$spos + 8*$i + 2]) | ord($data[$spos + 8*$i + 3])<<8;
						$lr =  ord($data[$spos + 8*$i + 4]) | ord($data[$spos + 8*$i + 5])<<8;
						$fc =  ord($data[$spos + 8*$i + 6]) | ord($data[$spos + 8*$i + 7])<<8;
						$lc =  ord($data[$spos + 8*$i + 8]) | ord($data[$spos + 8*$i + 9])<<8;
						if ($lr - $fr > 0) {
							$this->sheets[$this->sn]['cellsInfo'][$fr+1][$fc+1]['rowspan'] = $lr - $fr + 1;
						}
						if ($lc - $fc > 0) {
							$this->sheets[$this->sn]['cellsInfo'][$fr+1][$fc+1]['colspan'] = $lc - $fc + 1;
						}
					}
					break;
				case SPREADSHEET_EXCEL_READER_TYPE_RK:
				case SPREADSHEET_EXCEL_READER_TYPE_RK2:
					$row = ord($data[$spos]) | ord($data[$spos+1])<<8;
					$column = ord($data[$spos+2]) | ord($data[$spos+3])<<8;
					$rknum = $this->_GetInt4d($data, $spos + 6);
					$numValue = $this->_GetIEEE754($rknum);
					$info = $this->_getCellDetails($spos,$numValue,$column);
					$this->addcell($row, $column, $info['string'],$info);
					break;
				case SPREADSHEET_EXCEL_READER_TYPE_LABELSST:
					$row		= ord($data[$spos]) | ord($data[$spos+1])<<8;
					$column	 = ord($data[$spos+2]) | ord($data[$spos+3])<<8;
					$xfindex	= ord($data[$spos+4]) | ord($data[$spos+5])<<8;
					$index  = $this->_GetInt4d($data, $spos + 6);
					$this->addcell($row, $column, $this->sst[$index], array('xfIndex'=>$xfindex) );
					break;
				case SPREADSHEET_EXCEL_READER_TYPE_MULRK:
					$row		= ord($data[$spos]) | ord($data[$spos+1])<<8;
					$colFirst   = ord($data[$spos+2]) | ord($data[$spos+3])<<8;
					$colLast	= ord($data[$spos + $length - 2]) | ord($data[$spos + $length - 1])<<8;
					$columns	= $colLast - $colFirst + 1;
					$tmppos = $spos+4;
					for ($i = 0; $i < $columns; $i++) {
						$numValue = $this->_GetIEEE754($this->_GetInt4d($data, $tmppos + 2));
						$info = $this->_getCellDetails($tmppos-4,$numValue,$colFirst + $i + 1);
						$tmppos += 6;
						$this->addcell($row, $colFirst + $i, $info['string'], $info);
					}
					break;
				case SPREADSHEET_EXCEL_READER_TYPE_NUMBER:
					$row	= ord($data[$spos]) | ord($data[$spos+1])<<8;
					$column = ord($data[$spos+2]) | ord($data[$spos+3])<<8;
					$tmp = unpack("ddouble", substr($data, $spos + 6, 8)); // It machine machine dependent
					if ($this->isDate($spos)) {
						$numValue = $tmp['double'];
					}
					else {
						$numValue = $this->createNumber($spos);
					}
					$info = $this->_getCellDetails($spos,$numValue,$column);
					$this->addcell($row, $column, $info['string'], $info);
					break;

				case SPREADSHEET_EXCEL_READER_TYPE_FORMULA:
				case SPREADSHEET_EXCEL_READER_TYPE_FORMULA2:
					$row	= ord($data[$spos]) | ord($data[$spos+1])<<8;
					$column = ord($data[$spos+2]) | ord($data[$spos+3])<<8;
					if ((ord($data[$spos+6])==0) && (ord($data[$spos+12])==255) && (ord($data[$spos+13])==255)) {
						//String formula. Result follows in a STRING record
						// This row/col are stored to be referenced in that record
						// http://code.google.com/p/php-excel-reader/issues/detail?id=4
						$previousRow = $row;
						$previousCol = $column;
					} elseif ((ord($data[$spos+6])==1) && (ord($data[$spos+12])==255) && (ord($data[$spos+13])==255)) {
						//Boolean formula. Result is in +2; 0=false,1=true
						// http://code.google.com/p/php-excel-reader/issues/detail?id=4
                        if (ord($this->data[$spos+8])==1) {
                            $this->addcell($row, $column, "TRUE");
                        } else {
                            $this->addcell($row, $column, "FALSE");
                        }
					} elseif ((ord($data[$spos+6])==2) && (ord($data[$spos+12])==255) && (ord($data[$spos+13])==255)) {
						//Error formula. Error code is in +2;
					} elseif ((ord($data[$spos+6])==3) && (ord($data[$spos+12])==255) && (ord($data[$spos+13])==255)) {
						//Formula result is a null string.
						$this->addcell($row, $column, '');
					} else {
						// result is a number, so first 14 bytes are just like a _NUMBER record
						$tmp = unpack("ddouble", substr($data, $spos + 6, 8)); // It machine machine dependent
							  if ($this->isDate($spos)) {
								$numValue = $tmp['double'];
							  }
							  else {
								$numValue = $this->createNumber($spos);
							  }
						$info = $this->_getCellDetails($spos,$numValue,$column);
						$this->addcell($row, $column, $info['string'], $info);
					}
					break;
				case SPREADSHEET_EXCEL_READER_TYPE_BOOLERR:
					$row	= ord($data[$spos]) | ord($data[$spos+1])<<8;
					$column = ord($data[$spos+2]) | ord($data[$spos+3])<<8;
					$string = ord($data[$spos+6]);
					$this->addcell($row, $column, $string);
					break;
                case SPREADSHEET_EXCEL_READER_TYPE_STRING:
					// http://code.google.com/p/php-excel-reader/issues/detail?id=4
					if ($version == SPREADSHEET_EXCEL_READER_BIFF8){
						// Unicode 16 string, like an SST record
						$xpos = $spos;
						$numChars =ord($data[$xpos]) | (ord($data[$xpos+1]) << 8);
						$xpos += 2;
						$optionFlags =ord($data[$xpos]);
						$xpos++;
						$asciiEncoding = (($optionFlags &0x01) == 0) ;
						$extendedString = (($optionFlags & 0x04) != 0);
                        // See if string contains formatting information
						$richString = (($optionFlags & 0x08) != 0);
						if ($richString) {
							// Read in the crun
							$formattingRuns =ord($data[$xpos]) | (ord($data[$xpos+1]) << 8);
							$xpos += 2;
						}
						if ($extendedString) {
							// Read in cchExtRst
							$extendedRunLength =$this->_GetInt4d($this->data, $xpos);
							$xpos += 4;
						}
						$len = ($asciiEncoding)?$numChars : $numChars*2;
						$retstr =substr($data, $xpos, $len);
						$xpos += $len;
						$retstr = ($asciiEncoding)? $retstr : $this->_encodeUTF16($retstr);
					}
					elseif ($version == SPREADSHEET_EXCEL_READER_BIFF7){
						// Simple byte string
						$xpos = $spos;
						$numChars =ord($data[$xpos]) | (ord($data[$xpos+1]) << 8);
						$xpos += 2;
						$retstr =substr($data, $xpos, $numChars);
					}
					$this->addcell($previousRow, $previousCol, $retstr);
					break;
				case SPREADSHEET_EXCEL_READER_TYPE_ROW:
					$row	= ord($data[$spos]) | ord($data[$spos+1])<<8;
					$rowInfo = ord($data[$spos + 6]) | ((ord($data[$spos+7]) << 8) & 0x7FFF);
					if (($rowInfo & 0x8000) > 0) {
						$rowHeight = -1;
					} else {
						$rowHeight = $rowInfo & 0x7FFF;
					}
					$rowHidden = (ord($data[$spos + 12]) & 0x20) >> 5;
					$this->rowInfo[$this->sn][$row+1] = Array('height' => $rowHeight / 20, 'hidden'=>$rowHidden );
					break;
				case SPREADSHEET_EXCEL_READER_TYPE_DBCELL:
					break;
				case SPREADSHEET_EXCEL_READER_TYPE_MULBLANK:
					$row = ord($data[$spos]) | ord($data[$spos+1])<<8;
					$column = ord($data[$spos+2]) | ord($data[$spos+3])<<8;
					$cols = ($length / 2) - 3;
					for ($c = 0; $c < $cols; $c++) {
						$xfindex = ord($data[$spos + 4 + ($c * 2)]) | ord($data[$spos + 5 + ($c * 2)])<<8;
						$this->addcell($row, $column + $c, "", array('xfIndex'=>$xfindex));
					}
					break;
				case SPREADSHEET_EXCEL_READER_TYPE_LABEL:
					$row	= ord($data[$spos]) | ord($data[$spos+1])<<8;
					$column = ord($data[$spos+2]) | ord($data[$spos+3])<<8;
					$this->addcell($row, $column, substr($data, $spos + 8, ord($data[$spos + 6]) | ord($data[$spos + 7])<<8));
					break;
				case SPREADSHEET_EXCEL_READER_TYPE_EOF:
					$cont = false;
					break;
				case SPREADSHEET_EXCEL_READER_TYPE_HYPER:
					//  Only handle hyperlinks to a URL
					$row	= ord($this->data[$spos]) | ord($this->data[$spos+1])<<8;
					$row2   = ord($this->data[$spos+2]) | ord($this->data[$spos+3])<<8;
					$column = ord($this->data[$spos+4]) | ord($this->data[$spos+5])<<8;
					$column2 = ord($this->data[$spos+6]) | ord($this->data[$spos+7])<<8;
					$linkdata = Array();
					$flags = ord($this->data[$spos + 28]);
					$udesc = "";
					$ulink = "";
					$uloc = 32;
					$linkdata['flags'] = $flags;
					if (($flags & 1) > 0 ) {   // is a type we understand
						//  is there a description ?
						if (($flags & 0x14) == 0x14 ) {   // has a description
							$uloc += 4;
							$descLen = ord($this->data[$spos + 32]) | ord($this->data[$spos + 33]) << 8;
							$udesc = substr($this->data, $spos + $uloc, $descLen * 2);
							$uloc += 2 * $descLen;
						}
						$ulink = $this->read16bitstring($this->data, $spos + $uloc + 20);
						if ($udesc == "") {
							$udesc = $ulink;
						}
					}
					$linkdata['desc'] = $udesc;
					$linkdata['link'] = $this->_encodeUTF16($ulink);
					for ($r=$row; $r<=$row2; $r++) { 
						for ($c=$column; $c<=$column2; $c++) {
							$this->sheets[$this->sn]['cellsInfo'][$r+1][$c+1]['hyperlink'] = $linkdata;
						}
					}
					break;
				case SPREADSHEET_EXCEL_READER_TYPE_DEFCOLWIDTH:
					$this->defaultColWidth  = ord($data[$spos+4]) | ord($data[$spos+5]) << 8; 
					break;
				case SPREADSHEET_EXCEL_READER_TYPE_STANDARDWIDTH:
					$this->standardColWidth  = ord($data[$spos+4]) | ord($data[$spos+5]) << 8; 
					break;
				case SPREADSHEET_EXCEL_READER_TYPE_COLINFO:
					$colfrom = ord($data[$spos+0]) | ord($data[$spos+1]) << 8;
					$colto = ord($data[$spos+2]) | ord($data[$spos+3]) << 8;
					$cw = ord($data[$spos+4]) | ord($data[$spos+5]) << 8; 
					$cxf = ord($data[$spos+6]) | ord($data[$spos+7]) << 8; 
					$co = ord($data[$spos+8]); 
					for ($coli = $colfrom; $coli <= $colto; $coli++) {
						$this->colInfo[$this->sn][$coli+1] = Array('width' => $cw, 'xf' => $cxf, 'hidden' => ($co & 0x01), 'collapsed' => ($co & 0x1000) >> 12);
					}
					break;

				default:
					break;
			}
			$spos += $length;
		}

		if (!isset($this->sheets[$this->sn]['numRows']))
			 $this->sheets[$this->sn]['numRows'] = $this->sheets[$this->sn]['maxrow'];
		if (!isset($this->sheets[$this->sn]['numCols']))
			 $this->sheets[$this->sn]['numCols'] = $this->sheets[$this->sn]['maxcol'];
		}

		function isDate($spos) {
			$xfindex = ord($this->data[$spos+4]) | ord($this->data[$spos+5]) << 8;
			return ($this->xfRecords[$xfindex]['type'] == 'date');
		}

		// Get the details for a particular cell
		function _getCellDetails($spos,$numValue,$column) {
			$xfindex = ord($this->data[$spos+4]) | ord($this->data[$spos+5]) << 8;
			$xfrecord = $this->xfRecords[$xfindex];
			$type = $xfrecord['type'];

			$format = $xfrecord['format'];
			$formatIndex = $xfrecord['formatIndex'];
			$fontIndex = $xfrecord['fontIndex'];
			$formatColor = "";
			$rectype = '';
			$string = '';
			$raw = '';

			if (isset($this->_columnsFormat[$column + 1])){
				$format = $this->_columnsFormat[$column + 1];
			}

			if ($type == 'date') {
				// See http://groups.google.com/group/php-excel-reader-discuss/browse_frm/thread/9c3f9790d12d8e10/f2045c2369ac79de
				$rectype = 'date';
				// Convert numeric value into a date
				$utcDays = floor($numValue - ($this->nineteenFour ? SPREADSHEET_EXCEL_READER_UTCOFFSETDAYS1904 : SPREADSHEET_EXCEL_READER_UTCOFFSETDAYS));
				$utcValue = ($utcDays) * SPREADSHEET_EXCEL_READER_MSINADAY;
				$dateinfo = gmgetdate($utcValue);

				$raw = $numValue;
				$fractionalDay = $numValue - floor($numValue) + .0000001; // The .0000001 is to fix for php/excel fractional diffs

				$totalseconds = floor(SPREADSHEET_EXCEL_READER_MSINADAY * $fractionalDay);
				$secs = $totalseconds % 60;
				$totalseconds -= $secs;
				$hours = floor($totalseconds / (60 * 60));
				$mins = floor($totalseconds / 60) % 60;
				$string = date ($format, mktime($hours, $mins, $secs, $dateinfo["mon"], $dateinfo["mday"], $dateinfo["year"]));
			} else if ($type == 'number') {
				$rectype = 'number';
				$formatted = $this->_format_value($format, $numValue, $formatIndex);
				$string = $formatted['string'];
				$formatColor = $formatted['formatColor'];
				$raw = $numValue;
			} else{
				if ($format=="") {
					$format = $this->_defaultFormat;
				}
				$rectype = 'unknown';
				$formatted = $this->_format_value($format, $numValue, $formatIndex);
				$string = $formatted['string'];
				$formatColor = $formatted['formatColor'];
				$raw = $numValue;
			}

			return array(
				'string'=>$string,
				'raw'=>$raw,
				'rectype'=>$rectype,
				'format'=>$format,
				'formatIndex'=>$formatIndex,
				'fontIndex'=>$fontIndex,
				'formatColor'=>$formatColor,
				'xfIndex'=>$xfindex
			);

		}


	function createNumber($spos) {
		$rknumhigh = $this->_GetInt4d($this->data, $spos + 10);
		$rknumlow = $this->_GetInt4d($this->data, $spos + 6);
		$sign = ($rknumhigh & 0x80000000) >> 31;
		$exp =  ($rknumhigh & 0x7ff00000) >> 20;
		$mantissa = (0x100000 | ($rknumhigh & 0x000fffff));
		$mantissalow1 = ($rknumlow & 0x80000000) >> 31;
		$mantissalow2 = ($rknumlow & 0x7fffffff);
		$value = $mantissa / pow( 2 , (20- ($exp - 1023)));
		if ($mantissalow1 != 0) $value += 1 / pow (2 , (21 - ($exp - 1023)));
		$value += $mantissalow2 / pow (2 , (52 - ($exp - 1023)));
		if ($sign) {$value = -1 * $value;}
		return  $value;
	}

	function addcell($row, $col, $string, $info=null) {
		$this->sheets[$this->sn]['maxrow'] = max($this->sheets[$this->sn]['maxrow'], $row + $this->_rowoffset);
		$this->sheets[$this->sn]['maxcol'] = max($this->sheets[$this->sn]['maxcol'], $col + $this->_coloffset);
		$this->sheets[$this->sn]['cells'][$row + $this->_rowoffset][$col + $this->_coloffset] = $string;
		if ($this->store_extended_info && $info) {
			foreach ($info as $key=>$val) {
				$this->sheets[$this->sn]['cellsInfo'][$row + $this->_rowoffset][$col + $this->_coloffset][$key] = $val;
			}
		}
	}


	function _GetIEEE754($rknum) {
		if (($rknum & 0x02) != 0) {
				$value = $rknum >> 2;
		} else {
			//mmp
			// I got my info on IEEE754 encoding from
			// http://research.microsoft.com/~hollasch/cgindex/coding/ieeefloat.html
			// The RK format calls for using only the most significant 30 bits of the
			// 64 bit floating point value. The other 34 bits are assumed to be 0
			// So, we use the upper 30 bits of $rknum as follows...
			$sign = ($rknum & 0x80000000) >> 31;
			$exp = ($rknum & 0x7ff00000) >> 20;
			$mantissa = (0x100000 | ($rknum & 0x000ffffc));
			$value = $mantissa / pow( 2 , (20- ($exp - 1023)));
			if ($sign) {
				$value = -1 * $value;
			}
			//end of changes by mmp
		}
		if (($rknum & 0x01) != 0) {
			$value /= 100;
		}
		return $value;
	}

	function _encodeUTF16($string) {
		$result = $string;
		if ($this->_defaultEncoding){
			switch ($this->_encoderFunction){
				case 'iconv' :	 $result = iconv('UTF-16LE', $this->_defaultEncoding, $string);
								break;
				case 'mb_convert_encoding' :	 $result = mb_convert_encoding($string, $this->_defaultEncoding, 'UTF-16LE' );
								break;
			}
		}
		return $result;
	}

	function _GetInt4d($data, $pos) {
		$value = ord($data[$pos]) | (ord($data[$pos+1]) << 8) | (ord($data[$pos+2]) << 16) | (ord($data[$pos+3]) << 24);
		if ($value>=4294967294) {
			$value=-2;
		}
		return $value;
	}

}



/*
 * PHP QR Code encoder
 *
 * This file contains MERGED version of PHP QR Code library.
 * It was auto-generated from full version for your convenience.
 *
 * This merged version was configured to not requre any external files,
 * with disabled cache, error loging and weker but faster mask matching.
 * If you need tune it up please use non-merged version.
 *
 * For full version, documentation, examples of use please visit:
 *
 *    http://phpqrcode.sourceforge.net/
 *    https://sourceforge.net/projects/phpqrcode/
 *
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia <deltalab at poczta dot fm>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */



/*
 * Version: 1.1.4
 * Build: 2010100721
 */



//---- qrconst.php -----------------------------





/*
 * PHP QR Code encoder
 *
 * Common constants
 *
 * Based on libqrencode C library distributed under LGPL 2.1
 * Copyright (C) 2006, 2007, 2008, 2009 Kentaro Fukuchi <fukuchi@megaui.net>
 *
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia <deltalab at poczta dot fm>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

// Encoding modes

define('QR_MODE_NUL', -1);
define('QR_MODE_NUM', 0);
define('QR_MODE_AN', 1);
define('QR_MODE_8', 2);
define('QR_MODE_KANJI', 3);
define('QR_MODE_STRUCTURE', 4);

// Levels of error correction.

define('QR_ECLEVEL_L', 0);
define('QR_ECLEVEL_M', 1);
define('QR_ECLEVEL_Q', 2);
define('QR_ECLEVEL_H', 3);

// Supported output formats

define('QR_FORMAT_TEXT', 0);
define('QR_FORMAT_PNG',  1);

class qrstr {
    public static function set(&$srctab, $x, $y, $repl, $replLen = false) {
        $srctab[$y] = substr_replace($srctab[$y], ($replLen !== false)?substr($repl,0,$replLen):$repl, $x, ($replLen !== false)?$replLen:strlen($repl));
    }
}



//---- merged_config.php -----------------------------




/*
 * PHP QR Code encoder
 *
 * Config file, tuned-up for merged verion
 */

define('QR_CACHEABLE', false);       // use cache - more disk reads but less CPU power, masks and format templates are stored there
define('QR_CACHE_DIR', false);       // used when QR_CACHEABLE === true
define('QR_LOG_DIR', false);         // default error logs dir

define('QR_FIND_BEST_MASK', true);                                                          // if true, estimates best mask (spec. default, but extremally slow; set to false to significant performance boost but (propably) worst quality code
define('QR_FIND_FROM_RANDOM', 2);                                                       // if false, checks all masks available, otherwise value tells count of masks need to be checked, mask id are got randomly
define('QR_DEFAULT_MASK', 2);                                                               // when QR_FIND_BEST_MASK === false

define('QR_PNG_MAXIMUM_SIZE',  1024);                                                       // maximum allowed png image width (in pixels), tune to make sure GD and PHP can handle such big images




//---- qrtools.php -----------------------------




/*
 * PHP QR Code encoder
 *
 * Toolset, handy and debug utilites.
 *
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia <deltalab at poczta dot fm>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

class QRtools {

    //----------------------------------------------------------------------
    public static function binarize($frame)
    {
        $len = count($frame);
        foreach ($frame as &$frameLine) {

            for($i=0; $i<$len; $i++) {
                $frameLine[$i] = (ord($frameLine[$i])&1)?'1':'0';
            }
        }

        return $frame;
    }

    //----------------------------------------------------------------------
    public static function tcpdfBarcodeArray($code, $mode = 'QR,L', $tcPdfVersion = '4.5.037')
    {
        $barcode_array = array();

        if (!is_array($mode))
            $mode = explode(',', $mode);

        $eccLevel = 'L';

        if (count($mode) > 1) {
            $eccLevel = $mode[1];
        }

        $qrTab = QRcode::text($code, false, $eccLevel);
        $size = count($qrTab);

        $barcode_array['num_rows'] = $size;
        $barcode_array['num_cols'] = $size;
        $barcode_array['bcode'] = array();

        foreach ($qrTab as $line) {
            $arrAdd = array();
            foreach(str_split($line) as $char)
                $arrAdd[] = ($char=='1')?1:0;
            $barcode_array['bcode'][] = $arrAdd;
        }

        return $barcode_array;
    }

    //----------------------------------------------------------------------
    public static function clearCache()
    {
        self::$frames = array();
    }

    //----------------------------------------------------------------------
    public static function buildCache()
    {
        QRtools::markTime('before_build_cache');

        $mask = new QRmask();
        for ($a=1; $a <= QRSPEC_VERSION_MAX; $a++) {
            $frame = QRspec::newFrame($a);
            if (QR_IMAGE) {
                $fileName = QR_CACHE_DIR.'frame_'.$a.'.png';
                QRimage::png(self::binarize($frame), $fileName, 1, 0);
            }

            $width = count($frame);
            $bitMask = array_fill(0, $width, array_fill(0, $width, 0));
            for ($maskNo=0; $maskNo<8; $maskNo++)
                $mask->makeMaskNo($maskNo, $width, $frame, $bitMask, true);
        }

        QRtools::markTime('after_build_cache');
    }

    //----------------------------------------------------------------------
    public static function log($outfile, $err)
    {
        if (QR_LOG_DIR !== false) {
            if ($err != '') {
                if ($outfile !== false) {
                    file_put_contents(QR_LOG_DIR.basename($outfile).'-errors.txt', date('Y-m-d H:i:s').': '.$err, FILE_APPEND);
                } else {
                    file_put_contents(QR_LOG_DIR.'errors.txt', date('Y-m-d H:i:s').': '.$err, FILE_APPEND);
                }
            }
        }
    }

    //----------------------------------------------------------------------
    public static function dumpMask($frame)
    {
        $width = count($frame);
        for($y=0;$y<$width;$y++) {
            for($x=0;$x<$width;$x++) {
                echo ord($frame[$y][$x]).',';
            }
        }
    }

    //----------------------------------------------------------------------
    public static function markTime($markerId)
    {
        list($usec, $sec) = explode(" ", microtime());
        $time = ((float)$usec + (float)$sec);

        if (!isset($GLOBALS['qr_time_bench']))
            $GLOBALS['qr_time_bench'] = array();

        $GLOBALS['qr_time_bench'][$markerId] = $time;
    }

    //----------------------------------------------------------------------
    public static function timeBenchmark()
    {
        self::markTime('finish');

        $lastTime = 0;
        $startTime = 0;
        $p = 0;

        echo '<table cellpadding="3" cellspacing="1">
                    <thead><tr style="border-bottom:1px solid silver"><td colspan="2" style="text-align:center">BENCHMARK</td></tr></thead>
                    <tbody>';

        foreach($GLOBALS['qr_time_bench'] as $markerId=>$thisTime) {
            if ($p > 0) {
                echo '<tr><th style="text-align:right">till '.$markerId.': </th><td>'.number_format($thisTime-$lastTime, 6).'s</td></tr>';
            } else {
                $startTime = $thisTime;
            }

            $p++;
            $lastTime = $thisTime;
        }

        echo '</tbody><tfoot>
                <tr style="border-top:2px solid black"><th style="text-align:right">TOTAL: </th><td>'.number_format($lastTime-$startTime, 6).'s</td></tr>
            </tfoot>
            </table>';
    }

}

//##########################################################################

QRtools::markTime('start');




//---- qrspec.php -----------------------------




/*
 * PHP QR Code encoder
 *
 * QR Code specifications
 *
 * Based on libqrencode C library distributed under LGPL 2.1
 * Copyright (C) 2006, 2007, 2008, 2009 Kentaro Fukuchi <fukuchi@megaui.net>
 *
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia <deltalab at poczta dot fm>
 *
 * The following data / specifications are taken from
 * "Two dimensional symbol -- QR-code -- Basic Specification" (JIS X0510:2004)
 *  or
 * "Automatic identification and data capture techniques -- 
 *  QR Code 2005 bar code symbology specification" (ISO/IEC 18004:2006)
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */



class QRspec {

    public static $capacity = array(
        array(  0,    0, 0, array(   0,    0,    0,    0)),
        array( 21,   26, 0, array(   7,   10,   13,   17)), // 1
        array( 25,   44, 7, array(  10,   16,   22,   28)),
        array( 29,   70, 7, array(  15,   26,   36,   44)),
        array( 33,  100, 7, array(  20,   36,   52,   64)),
        array( 37,  134, 7, array(  26,   48,   72,   88)), // 5
        array( 41,  172, 7, array(  36,   64,   96,  112)),
        array( 45,  196, 0, array(  40,   72,  108,  130)),
        array( 49,  242, 0, array(  48,   88,  132,  156)),
        array( 53,  292, 0, array(  60,  110,  160,  192)),
        array( 57,  346, 0, array(  72,  130,  192,  224)), //10
        array( 61,  404, 0, array(  80,  150,  224,  264)),
        array( 65,  466, 0, array(  96,  176,  260,  308)),
        array( 69,  532, 0, array( 104,  198,  288,  352)),
        array( 73,  581, 3, array( 120,  216,  320,  384)),
        array( 77,  655, 3, array( 132,  240,  360,  432)), //15
        array( 81,  733, 3, array( 144,  280,  408,  480)),
        array( 85,  815, 3, array( 168,  308,  448,  532)),
        array( 89,  901, 3, array( 180,  338,  504,  588)),
        array( 93,  991, 3, array( 196,  364,  546,  650)),
        array( 97, 1085, 3, array( 224,  416,  600,  700)), //20
        array(101, 1156, 4, array( 224,  442,  644,  750)),
        array(105, 1258, 4, array( 252,  476,  690,  816)),
        array(109, 1364, 4, array( 270,  504,  750,  900)),
        array(113, 1474, 4, array( 300,  560,  810,  960)),
        array(117, 1588, 4, array( 312,  588,  870, 1050)), //25
        array(121, 1706, 4, array( 336,  644,  952, 1110)),
        array(125, 1828, 4, array( 360,  700, 1020, 1200)),
        array(129, 1921, 3, array( 390,  728, 1050, 1260)),
        array(133, 2051, 3, array( 420,  784, 1140, 1350)),
        array(137, 2185, 3, array( 450,  812, 1200, 1440)), //30
        array(141, 2323, 3, array( 480,  868, 1290, 1530)),
        array(145, 2465, 3, array( 510,  924, 1350, 1620)),
        array(149, 2611, 3, array( 540,  980, 1440, 1710)),
        array(153, 2761, 3, array( 570, 1036, 1530, 1800)),
        array(157, 2876, 0, array( 570, 1064, 1590, 1890)), //35
        array(161, 3034, 0, array( 600, 1120, 1680, 1980)),
        array(165, 3196, 0, array( 630, 1204, 1770, 2100)),
        array(169, 3362, 0, array( 660, 1260, 1860, 2220)),
        array(173, 3532, 0, array( 720, 1316, 1950, 2310)),
        array(177, 3706, 0, array( 750, 1372, 2040, 2430)) //40
    );

    //----------------------------------------------------------------------
    public static function getDataLength($version, $level)
    {
        return self::$capacity[$version][QRCAP_WORDS] - self::$capacity[$version][QRCAP_EC][$level];
    }

    //----------------------------------------------------------------------
    public static function getECCLength($version, $level)
    {
        return self::$capacity[$version][QRCAP_EC][$level];
    }

    //----------------------------------------------------------------------
    public static function getWidth($version)
    {
        return self::$capacity[$version][QRCAP_WIDTH];
    }

    //----------------------------------------------------------------------
    public static function getRemainder($version)
    {
        return self::$capacity[$version][QRCAP_REMINDER];
    }

    //----------------------------------------------------------------------
    public static function getMinimumVersion($size, $level)
    {

        for($i=1; $i<= QRSPEC_VERSION_MAX; $i++) {
            $words  = self::$capacity[$i][QRCAP_WORDS] - self::$capacity[$i][QRCAP_EC][$level];
            if($words >= $size)
                return $i;
        }

        return -1;
    }

    //######################################################################

    public static $lengthTableBits = array(
        array(10, 12, 14),
        array( 9, 11, 13),
        array( 8, 16, 16),
        array( 8, 10, 12)
    );

    //----------------------------------------------------------------------
    public static function lengthIndicator($mode, $version)
    {
        if ($mode == QR_MODE_STRUCTURE)
            return 0;

        if ($version <= 9) {
            $l = 0;
        } else if ($version <= 26) {
            $l = 1;
        } else {
            $l = 2;
        }

        return self::$lengthTableBits[$mode][$l];
    }

    //----------------------------------------------------------------------
    public static function maximumWords($mode, $version)
    {
        if($mode == QR_MODE_STRUCTURE)
            return 3;

        if($version <= 9) {
            $l = 0;
        } else if($version <= 26) {
            $l = 1;
        } else {
            $l = 2;
        }

        $bits = self::$lengthTableBits[$mode][$l];
        $words = (1 << $bits) - 1;

        if($mode == QR_MODE_KANJI) {
            $words *= 2; // the number of bytes is required
        }

        return $words;
    }

    // Error correction code -----------------------------------------------
    // Table of the error correction code (Reed-Solomon block)
    // See Table 12-16 (pp.30-36), JIS X0510:2004.

    public static $eccTable = array(
        array(array( 0,  0), array( 0,  0), array( 0,  0), array( 0,  0)),
        array(array( 1,  0), array( 1,  0), array( 1,  0), array( 1,  0)), // 1
        array(array( 1,  0), array( 1,  0), array( 1,  0), array( 1,  0)),
        array(array( 1,  0), array( 1,  0), array( 2,  0), array( 2,  0)),
        array(array( 1,  0), array( 2,  0), array( 2,  0), array( 4,  0)),
        array(array( 1,  0), array( 2,  0), array( 2,  2), array( 2,  2)), // 5
        array(array( 2,  0), array( 4,  0), array( 4,  0), array( 4,  0)),
        array(array( 2,  0), array( 4,  0), array( 2,  4), array( 4,  1)),
        array(array( 2,  0), array( 2,  2), array( 4,  2), array( 4,  2)),
        array(array( 2,  0), array( 3,  2), array( 4,  4), array( 4,  4)),
        array(array( 2,  2), array( 4,  1), array( 6,  2), array( 6,  2)), //10
        array(array( 4,  0), array( 1,  4), array( 4,  4), array( 3,  8)),
        array(array( 2,  2), array( 6,  2), array( 4,  6), array( 7,  4)),
        array(array( 4,  0), array( 8,  1), array( 8,  4), array(12,  4)),
        array(array( 3,  1), array( 4,  5), array(11,  5), array(11,  5)),
        array(array( 5,  1), array( 5,  5), array( 5,  7), array(11,  7)), //15
        array(array( 5,  1), array( 7,  3), array(15,  2), array( 3, 13)),
        array(array( 1,  5), array(10,  1), array( 1, 15), array( 2, 17)),
        array(array( 5,  1), array( 9,  4), array(17,  1), array( 2, 19)),
        array(array( 3,  4), array( 3, 11), array(17,  4), array( 9, 16)),
        array(array( 3,  5), array( 3, 13), array(15,  5), array(15, 10)), //20
        array(array( 4,  4), array(17,  0), array(17,  6), array(19,  6)),
        array(array( 2,  7), array(17,  0), array( 7, 16), array(34,  0)),
        array(array( 4,  5), array( 4, 14), array(11, 14), array(16, 14)),
        array(array( 6,  4), array( 6, 14), array(11, 16), array(30,  2)),
        array(array( 8,  4), array( 8, 13), array( 7, 22), array(22, 13)), //25
        array(array(10,  2), array(19,  4), array(28,  6), array(33,  4)),
        array(array( 8,  4), array(22,  3), array( 8, 26), array(12, 28)),
        array(array( 3, 10), array( 3, 23), array( 4, 31), array(11, 31)),
        array(array( 7,  7), array(21,  7), array( 1, 37), array(19, 26)),
        array(array( 5, 10), array(19, 10), array(15, 25), array(23, 25)), //30
        array(array(13,  3), array( 2, 29), array(42,  1), array(23, 28)),
        array(array(17,  0), array(10, 23), array(10, 35), array(19, 35)),
        array(array(17,  1), array(14, 21), array(29, 19), array(11, 46)),
        array(array(13,  6), array(14, 23), array(44,  7), array(59,  1)),
        array(array(12,  7), array(12, 26), array(39, 14), array(22, 41)), //35
        array(array( 6, 14), array( 6, 34), array(46, 10), array( 2, 64)),
        array(array(17,  4), array(29, 14), array(49, 10), array(24, 46)),
        array(array( 4, 18), array(13, 32), array(48, 14), array(42, 32)),
        array(array(20,  4), array(40,  7), array(43, 22), array(10, 67)),
        array(array(19,  6), array(18, 31), array(34, 34), array(20, 61)),//40
    );

    //----------------------------------------------------------------------
    // CACHEABLE!!!

    public static function getEccSpec($version, $level, array &$spec)
    {
        if (count($spec) < 5) {
            $spec = array(0,0,0,0,0);
        }

        $b1   = self::$eccTable[$version][$level][0];
        $b2   = self::$eccTable[$version][$level][1];
        $data = self::getDataLength($version, $level);
        $ecc  = self::getECCLength($version, $level);

        if($b2 == 0) {
            $spec[0] = $b1;
            $spec[1] = (int)($data / $b1);
            $spec[2] = (int)($ecc / $b1);
            $spec[3] = 0;
            $spec[4] = 0;
        } else {
            $spec[0] = $b1;
            $spec[1] = (int)($data / ($b1 + $b2));
            $spec[2] = (int)($ecc  / ($b1 + $b2));
            $spec[3] = $b2;
            $spec[4] = $spec[1] + 1;
        }
    }

    // Alignment pattern ---------------------------------------------------

    // Positions of alignment patterns.
    // This array includes only the second and the third position of the
    // alignment patterns. Rest of them can be calculated from the distance
    // between them.

    // See Table 1 in Appendix E (pp.71) of JIS X0510:2004.

    public static $alignmentPattern = array(
        array( 0,  0),
        array( 0,  0), array(18,  0), array(22,  0), array(26,  0), array(30,  0), // 1- 5
        array(34,  0), array(22, 38), array(24, 42), array(26, 46), array(28, 50), // 6-10
        array(30, 54), array(32, 58), array(34, 62), array(26, 46), array(26, 48), //11-15
        array(26, 50), array(30, 54), array(30, 56), array(30, 58), array(34, 62), //16-20
        array(28, 50), array(26, 50), array(30, 54), array(28, 54), array(32, 58), //21-25
        array(30, 58), array(34, 62), array(26, 50), array(30, 54), array(26, 52), //26-30
        array(30, 56), array(34, 60), array(30, 58), array(34, 62), array(30, 54), //31-35
        array(24, 50), array(28, 54), array(32, 58), array(26, 54), array(30, 58), //35-40
    );


    /** --------------------------------------------------------------------
     * Put an alignment marker.
     * @param frame
     * @param width
     * @param ox,oy center coordinate of the pattern
     */
    public static function putAlignmentMarker(array &$frame, $ox, $oy)
    {
        $finder = array(
            "\xa1\xa1\xa1\xa1\xa1",
            "\xa1\xa0\xa0\xa0\xa1",
            "\xa1\xa0\xa1\xa0\xa1",
            "\xa1\xa0\xa0\xa0\xa1",
            "\xa1\xa1\xa1\xa1\xa1"
        );

        $yStart = $oy-2;
        $xStart = $ox-2;

        for($y=0; $y<5; $y++) {
            QRstr::set($frame, $xStart, $yStart+$y, $finder[$y]);
        }
    }

    //----------------------------------------------------------------------
    public static function putAlignmentPattern($version, &$frame, $width)
    {
        if($version < 2)
            return;

        $d = self::$alignmentPattern[$version][1] - self::$alignmentPattern[$version][0];
        if($d < 0) {
            $w = 2;
        } else {
            $w = (int)(($width - self::$alignmentPattern[$version][0]) / $d + 2);
        }

        if($w * $w - 3 == 1) {
            $x = self::$alignmentPattern[$version][0];
            $y = self::$alignmentPattern[$version][0];
            self::putAlignmentMarker($frame, $x, $y);
            return;
        }

        $cx = self::$alignmentPattern[$version][0];
        for($x=1; $x<$w - 1; $x++) {
            self::putAlignmentMarker($frame, 6, $cx);
            self::putAlignmentMarker($frame, $cx,  6);
            $cx += $d;
        }

        $cy = self::$alignmentPattern[$version][0];
        for($y=0; $y<$w-1; $y++) {
            $cx = self::$alignmentPattern[$version][0];
            for($x=0; $x<$w-1; $x++) {
                self::putAlignmentMarker($frame, $cx, $cy);
                $cx += $d;
            }
            $cy += $d;
        }
    }

    // Version information pattern -----------------------------------------

    // Version information pattern (BCH coded).
    // See Table 1 in Appendix D (pp.68) of JIS X0510:2004.

    // size: [QRSPEC_VERSION_MAX - 6]

    public static $versionPattern = array(
        0x07c94, 0x085bc, 0x09a99, 0x0a4d3, 0x0bbf6, 0x0c762, 0x0d847, 0x0e60d,
        0x0f928, 0x10b78, 0x1145d, 0x12a17, 0x13532, 0x149a6, 0x15683, 0x168c9,
        0x177ec, 0x18ec4, 0x191e1, 0x1afab, 0x1b08e, 0x1cc1a, 0x1d33f, 0x1ed75,
        0x1f250, 0x209d5, 0x216f0, 0x228ba, 0x2379f, 0x24b0b, 0x2542e, 0x26a64,
        0x27541, 0x28c69
    );

    //----------------------------------------------------------------------
    public static function getVersionPattern($version)
    {
        if($version < 7 || $version > QRSPEC_VERSION_MAX)
            return 0;

        return self::$versionPattern[$version -7];
    }

    // Format information --------------------------------------------------
    // See calcFormatInfo in tests/test_qrspec.c (orginal qrencode c lib)

    public static $formatInfo = array(
        array(0x77c4, 0x72f3, 0x7daa, 0x789d, 0x662f, 0x6318, 0x6c41, 0x6976),
        array(0x5412, 0x5125, 0x5e7c, 0x5b4b, 0x45f9, 0x40ce, 0x4f97, 0x4aa0),
        array(0x355f, 0x3068, 0x3f31, 0x3a06, 0x24b4, 0x2183, 0x2eda, 0x2bed),
        array(0x1689, 0x13be, 0x1ce7, 0x19d0, 0x0762, 0x0255, 0x0d0c, 0x083b)
    );

    public static function getFormatInfo($mask, $level)
    {
        if($mask < 0 || $mask > 7)
            return 0;

        if($level < 0 || $level > 3)
            return 0;

        return self::$formatInfo[$level][$mask];
    }

    // Frame ---------------------------------------------------------------
    // Cache of initial frames.

    public static $frames = array();

    /** --------------------------------------------------------------------
     * Put a finder pattern.
     * @param frame
     * @param width
     * @param ox,oy upper-left coordinate of the pattern
     */
    public static function putFinderPattern(&$frame, $ox, $oy)
    {
        $finder = array(
            "\xc1\xc1\xc1\xc1\xc1\xc1\xc1",
            "\xc1\xc0\xc0\xc0\xc0\xc0\xc1",
            "\xc1\xc0\xc1\xc1\xc1\xc0\xc1",
            "\xc1\xc0\xc1\xc1\xc1\xc0\xc1",
            "\xc1\xc0\xc1\xc1\xc1\xc0\xc1",
            "\xc1\xc0\xc0\xc0\xc0\xc0\xc1",
            "\xc1\xc1\xc1\xc1\xc1\xc1\xc1"
        );

        for($y=0; $y<7; $y++) {
            QRstr::set($frame, $ox, $oy+$y, $finder[$y]);
        }
    }

    //----------------------------------------------------------------------
    public static function createFrame($version)
    {
        $width = self::$capacity[$version][QRCAP_WIDTH];
        $frameLine = str_repeat ("\0", $width);
        $frame = array_fill(0, $width, $frameLine);

        // Finder pattern
        self::putFinderPattern($frame, 0, 0);
        self::putFinderPattern($frame, $width - 7, 0);
        self::putFinderPattern($frame, 0, $width - 7);

        // Separator
        $yOffset = $width - 7;

        for($y=0; $y<7; $y++) {
            $frame[$y][7] = "\xc0";
            $frame[$y][$width - 8] = "\xc0";
            $frame[$yOffset][7] = "\xc0";
            $yOffset++;
        }

        $setPattern = str_repeat("\xc0", 8);

        QRstr::set($frame, 0, 7, $setPattern);
        QRstr::set($frame, $width-8, 7, $setPattern);
        QRstr::set($frame, 0, $width - 8, $setPattern);

        // Format info
        $setPattern = str_repeat("\x84", 9);
        QRstr::set($frame, 0, 8, $setPattern);
        QRstr::set($frame, $width - 8, 8, $setPattern, 8);

        $yOffset = $width - 8;

        for($y=0; $y<8; $y++,$yOffset++) {
            $frame[$y][8] = "\x84";
            $frame[$yOffset][8] = "\x84";
        }

        // Timing pattern

        for($i=1; $i<$width-15; $i++) {
            $frame[6][7+$i] = chr(0x90 | ($i & 1));
            $frame[7+$i][6] = chr(0x90 | ($i & 1));
        }

        // Alignment pattern
        self::putAlignmentPattern($version, $frame, $width);

        // Version information
        if($version >= 7) {
            $vinf = self::getVersionPattern($version);

            $v = $vinf;

            for($x=0; $x<6; $x++) {
                for($y=0; $y<3; $y++) {
                    $frame[($width - 11)+$y][$x] = chr(0x88 | ($v & 1));
                    $v = $v >> 1;
                }
            }

            $v = $vinf;
            for($y=0; $y<6; $y++) {
                for($x=0; $x<3; $x++) {
                    $frame[$y][$x+($width - 11)] = chr(0x88 | ($v & 1));
                    $v = $v >> 1;
                }
            }
        }

        // and a little bit...
        $frame[$width - 8][8] = "\x81";

        return $frame;
    }

    //----------------------------------------------------------------------
    public static function debug($frame, $binary_mode = false)
    {
        if ($binary_mode) {

            foreach ($frame as &$frameLine) {
                $frameLine = join('<span class="m">&nbsp;&nbsp;</span>', explode('0', $frameLine));
                $frameLine = join('&#9608;&#9608;', explode('1', $frameLine));
            }

            ?>
            <style>
                .m { background-color: white; }
            </style>
            <?php
            echo '<pre><tt><br/ ><br/ ><br/ >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            echo join("<br/ >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $frame);
            echo '</tt></pre><br/ ><br/ ><br/ ><br/ ><br/ ><br/ >';

        } else {

            foreach ($frame as &$frameLine) {
                $frameLine = join('<span class="m">&nbsp;</span>',  explode("\xc0", $frameLine));
                $frameLine = join('<span class="m">&#9618;</span>', explode("\xc1", $frameLine));
                $frameLine = join('<span class="p">&nbsp;</span>',  explode("\xa0", $frameLine));
                $frameLine = join('<span class="p">&#9618;</span>', explode("\xa1", $frameLine));
                $frameLine = join('<span class="s">&#9671;</span>', explode("\x84", $frameLine)); //format 0
                $frameLine = join('<span class="s">&#9670;</span>', explode("\x85", $frameLine)); //format 1
                $frameLine = join('<span class="x">&#9762;</span>', explode("\x81", $frameLine)); //special bit
                $frameLine = join('<span class="c">&nbsp;</span>',  explode("\x90", $frameLine)); //clock 0
                $frameLine = join('<span class="c">&#9719;</span>', explode("\x91", $frameLine)); //clock 1
                $frameLine = join('<span class="f">&nbsp;</span>',  explode("\x88", $frameLine)); //version
                $frameLine = join('<span class="f">&#9618;</span>', explode("\x89", $frameLine)); //version
                $frameLine = join('&#9830;', explode("\x01", $frameLine));
                $frameLine = join('&#8901;', explode("\0", $frameLine));
            }

            ?>
            <style>
                .p { background-color: yellow; }
                .m { background-color: #00FF00; }
                .s { background-color: #FF0000; }
                .c { background-color: aqua; }
                .x { background-color: pink; }
                .f { background-color: gold; }
            </style>
            <?php
            echo "<pre><tt>";
            echo join("<br/ >", $frame);
            echo "</tt></pre>";

        }
    }

    //----------------------------------------------------------------------
    public static function serial($frame)
    {
        return gzcompress(join("\n", $frame), 9);
    }

    //----------------------------------------------------------------------
    public static function unserial($code)
    {
        return explode("\n", gzuncompress($code));
    }

    //----------------------------------------------------------------------
    public static function newFrame($version)
    {
        if($version < 1 || $version > QRSPEC_VERSION_MAX)
            return null;

        if(!isset(self::$frames[$version])) {

            $fileName = QR_CACHE_DIR.'frame_'.$version.'.dat';

            if (QR_CACHEABLE) {
                if (file_exists($fileName)) {
                    self::$frames[$version] = self::unserial(file_get_contents($fileName));
                } else {
                    self::$frames[$version] = self::createFrame($version);
                    file_put_contents($fileName, self::serial(self::$frames[$version]));
                }
            } else {
                self::$frames[$version] = self::createFrame($version);
            }
        }

        if(is_null(self::$frames[$version]))
            return null;

        return self::$frames[$version];
    }

    //----------------------------------------------------------------------
    public static function rsBlockNum($spec)     { return $spec[0] + $spec[3]; }
    public static function rsBlockNum1($spec)    { return $spec[0]; }
    public static function rsDataCodes1($spec)   { return $spec[1]; }
    public static function rsEccCodes1($spec)    { return $spec[2]; }
    public static function rsBlockNum2($spec)    { return $spec[3]; }
    public static function rsDataCodes2($spec)   { return $spec[4]; }
    public static function rsEccCodes2($spec)    { return $spec[2]; }
    public static function rsDataLength($spec)   { return ($spec[0] * $spec[1]) + ($spec[3] * $spec[4]);    }
    public static function rsEccLength($spec)    { return ($spec[0] + $spec[3]) * $spec[2]; }

}



//---- qrimage.php -----------------------------




/*
 * PHP QR Code encoder
 *
 * Image output of code using GD2
 *
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia <deltalab at poczta dot fm>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

define('QR_IMAGE', true);

class QRimage {

    //----------------------------------------------------------------------
    public static function png($frame, $filename = false, $pixelPerPoint = 4, $outerFrame = 4,$saveandprint=FALSE)
    {
        $image = self::image($frame, $pixelPerPoint, $outerFrame);

        if ($filename === false) {
            Header("Content-type: image/png");
            ImagePng($image);
        } else {
            if($saveandprint===TRUE){
                ImagePng($image, $filename);
                header("Content-type: image/png");
                ImagePng($image);
            }else{
                ImagePng($image, $filename);
            }
        }

        ImageDestroy($image);
    }

    //----------------------------------------------------------------------
    public static function jpg($frame, $filename = false, $pixelPerPoint = 8, $outerFrame = 4, $q = 85)
    {
        $image = self::image($frame, $pixelPerPoint, $outerFrame);

        if ($filename === false) {
            Header("Content-type: image/jpeg");
            ImageJpeg($image, null, $q);
        } else {
            ImageJpeg($image, $filename, $q);
        }

        ImageDestroy($image);
    }

    //----------------------------------------------------------------------
    private static function image($frame, $pixelPerPoint = 4, $outerFrame = 4)
    {
        $h = count($frame);
        $w = strlen($frame[0]);

        $imgW = $w + 2*$outerFrame;
        $imgH = $h + 2*$outerFrame;

        $base_image =ImageCreate($imgW, $imgH);

        $col[0] = ImageColorAllocate($base_image,255,255,255);
        $col[1] = ImageColorAllocate($base_image,0,0,0);

        imagefill($base_image, 0, 0, $col[0]);

        for($y=0; $y<$h; $y++) {
            for($x=0; $x<$w; $x++) {
                if ($frame[$y][$x] == '1') {
                    ImageSetPixel($base_image,$x+$outerFrame,$y+$outerFrame,$col[1]);
                }
            }
        }

        $target_image =ImageCreate($imgW * $pixelPerPoint, $imgH * $pixelPerPoint);
        ImageCopyResized($target_image, $base_image, 0, 0, 0, 0, $imgW * $pixelPerPoint, $imgH * $pixelPerPoint, $imgW, $imgH);
        ImageDestroy($base_image);

        return $target_image;
    }
}



//---- qrinput.php -----------------------------




/*
 * PHP QR Code encoder
 *
 * Input encoding class
 *
 * Based on libqrencode C library distributed under LGPL 2.1
 * Copyright (C) 2006, 2007, 2008, 2009 Kentaro Fukuchi <fukuchi@megaui.net>
 *
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia <deltalab at poczta dot fm>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

define('STRUCTURE_HEADER_BITS',  20);
define('MAX_STRUCTURED_SYMBOLS', 16);

class QRinputItem {

    public $mode;
    public $size;
    public $data;
    public $bstream;

    public function __construct($mode, $size, $data, $bstream = null)
    {
        $setData = array_slice($data, 0, $size);

        if (count($setData) < $size) {
            $setData = array_merge($setData, array_fill(0,$size-count($setData),0));
        }

        if(!QRinput::check($mode, $size, $setData)) {
            throw new Exception('Error m:'.$mode.',s:'.$size.',d:'.join(',',$setData));
            return null;
        }

        $this->mode = $mode;
        $this->size = $size;
        $this->data = $setData;
        $this->bstream = $bstream;
    }

    //----------------------------------------------------------------------
    public function encodeModeNum($version)
    {
        try {

            $words = (int)($this->size / 3);
            $bs = new QRbitstream();

            $val = 0x1;
            $bs->appendNum(4, $val);
            $bs->appendNum(QRspec::lengthIndicator(QR_MODE_NUM, $version), $this->size);

            for($i=0; $i<$words; $i++) {
                $val  = (ord($this->data[$i*3  ]) - ord('0')) * 100;
                $val += (ord($this->data[$i*3+1]) - ord('0')) * 10;
                $val += (ord($this->data[$i*3+2]) - ord('0'));
                $bs->appendNum(10, $val);
            }

            if($this->size - $words * 3 == 1) {
                $val = ord($this->data[$words*3]) - ord('0');
                $bs->appendNum(4, $val);
            } else if($this->size - $words * 3 == 2) {
                $val  = (ord($this->data[$words*3  ]) - ord('0')) * 10;
                $val += (ord($this->data[$words*3+1]) - ord('0'));
                $bs->appendNum(7, $val);
            }

            $this->bstream = $bs;
            return 0;

        } catch (Exception $e) {
            return -1;
        }
    }

    //----------------------------------------------------------------------
    public function encodeModeAn($version)
    {
        try {
            $words = (int)($this->size / 2);
            $bs = new QRbitstream();

            $bs->appendNum(4, 0x02);
            $bs->appendNum(QRspec::lengthIndicator(QR_MODE_AN, $version), $this->size);

            for($i=0; $i<$words; $i++) {
                $val  = (int)QRinput::lookAnTable(ord($this->data[$i*2  ])) * 45;
                $val += (int)QRinput::lookAnTable(ord($this->data[$i*2+1]));

                $bs->appendNum(11, $val);
            }

            if($this->size & 1) {
                $val = QRinput::lookAnTable(ord($this->data[$words * 2]));
                $bs->appendNum(6, $val);
            }

            $this->bstream = $bs;
            return 0;

        } catch (Exception $e) {
            return -1;
        }
    }

    //----------------------------------------------------------------------
    public function encodeMode8($version)
    {
        try {
            $bs = new QRbitstream();

            $bs->appendNum(4, 0x4);
            $bs->appendNum(QRspec::lengthIndicator(QR_MODE_8, $version), $this->size);

            for($i=0; $i<$this->size; $i++) {
                $bs->appendNum(8, ord($this->data[$i]));
            }

            $this->bstream = $bs;
            return 0;

        } catch (Exception $e) {
            return -1;
        }
    }

    //----------------------------------------------------------------------
    public function encodeModeKanji($version)
    {
        try {

            $bs = new QRbitrtream();

            $bs->appendNum(4, 0x8);
            $bs->appendNum(QRspec::lengthIndicator(QR_MODE_KANJI, $version), (int)($this->size / 2));

            for($i=0; $i<$this->size; $i+=2) {
                $val = (ord($this->data[$i]) << 8) | ord($this->data[$i+1]);
                if($val <= 0x9ffc) {
                    $val -= 0x8140;
                } else {
                    $val -= 0xc140;
                }

                $h = ($val >> 8) * 0xc0;
                $val = ($val & 0xff) + $h;

                $bs->appendNum(13, $val);
            }

            $this->bstream = $bs;
            return 0;

        } catch (Exception $e) {
            return -1;
        }
    }

    //----------------------------------------------------------------------
    public function encodeModeStructure()
    {
        try {
            $bs =  new QRbitstream();

            $bs->appendNum(4, 0x03);
            $bs->appendNum(4, ord($this->data[1]) - 1);
            $bs->appendNum(4, ord($this->data[0]) - 1);
            $bs->appendNum(8, ord($this->data[2]));

            $this->bstream = $bs;
            return 0;

        } catch (Exception $e) {
            return -1;
        }
    }

    //----------------------------------------------------------------------
    public function estimateBitStreamSizeOfEntry($version)
    {
        $bits = 0;

        if($version == 0)
            $version = 1;

        switch($this->mode) {
            case QR_MODE_NUM:        $bits = QRinput::estimateBitsModeNum($this->size);    break;
            case QR_MODE_AN:        $bits = QRinput::estimateBitsModeAn($this->size);    break;
            case QR_MODE_8:            $bits = QRinput::estimateBitsMode8($this->size);    break;
            case QR_MODE_KANJI:        $bits = QRinput::estimateBitsModeKanji($this->size);break;
            case QR_MODE_STRUCTURE:    return STRUCTURE_HEADER_BITS;
            default:
                return 0;
        }

        $l = QRspec::lengthIndicator($this->mode, $version);
        $m = 1 << $l;
        $num = (int)(($this->size + $m - 1) / $m);

        $bits += $num * (4 + $l);

        return $bits;
    }

    //----------------------------------------------------------------------
    public function encodeBitStream($version)
    {
        try {

            unset($this->bstream);
            $words = QRspec::maximumWords($this->mode, $version);

            if($this->size > $words) {

                $st1 = new QRinputItem($this->mode, $words, $this->data);
                $st2 = new QRinputItem($this->mode, $this->size - $words, array_slice($this->data, $words));

                $st1->encodeBitStream($version);
                $st2->encodeBitStream($version);

                $this->bstream = new QRbitstream();
                $this->bstream->append($st1->bstream);
                $this->bstream->append($st2->bstream);

                unset($st1);
                unset($st2);

            } else {

                $ret = 0;

                switch($this->mode) {
                    case QR_MODE_NUM:        $ret = $this->encodeModeNum($version);    break;
                    case QR_MODE_AN:        $ret = $this->encodeModeAn($version);    break;
                    case QR_MODE_8:            $ret = $this->encodeMode8($version);    break;
                    case QR_MODE_KANJI:        $ret = $this->encodeModeKanji($version);break;
                    case QR_MODE_STRUCTURE:    $ret = $this->encodeModeStructure();    break;

                    default:
                        break;
                }

                if($ret < 0)
                    return -1;
            }

            return $this->bstream->size();

        } catch (Exception $e) {
            return -1;
        }
    }
};

//##########################################################################

class QRinput {

    public $items;

    private $version;
    private $level;

    //----------------------------------------------------------------------
    public function __construct($version = 0, $level = QR_ECLEVEL_L)
    {
        if ($version < 0 || $version > QRSPEC_VERSION_MAX || $level > QR_ECLEVEL_H) {
            throw new Exception('Invalid version no');
            return NULL;
        }

        $this->version = $version;
        $this->level = $level;
    }

    //----------------------------------------------------------------------
    public function getVersion()
    {
        return $this->version;
    }

    //----------------------------------------------------------------------
    public function setVersion($version)
    {
        if($version < 0 || $version > QRSPEC_VERSION_MAX) {
            throw new Exception('Invalid version no');
            return -1;
        }

        $this->version = $version;

        return 0;
    }

    //----------------------------------------------------------------------
    public function getErrorCorrectionLevel()
    {
        return $this->level;
    }

    //----------------------------------------------------------------------
    public function setErrorCorrectionLevel($level)
    {
        if($level > QR_ECLEVEL_H) {
            throw new Exception('Invalid ECLEVEL');
            return -1;
        }

        $this->level = $level;

        return 0;
    }

    //----------------------------------------------------------------------
    public function appendEntry(QRinputItem $entry)
    {
        $this->items[] = $entry;
    }

    //----------------------------------------------------------------------
    public function append($mode, $size, $data)
    {
        try {
            $entry = new QRinputItem($mode, $size, $data);
            $this->items[] = $entry;
            return 0;
        } catch (Exception $e) {
            return -1;
        }
    }

    //----------------------------------------------------------------------

    public function insertStructuredAppendHeader($size, $index, $parity)
    {
        if( $size > MAX_STRUCTURED_SYMBOLS ) {
            throw new Exception('insertStructuredAppendHeader wrong size');
        }

        if( $index <= 0 || $index > MAX_STRUCTURED_SYMBOLS ) {
            throw new Exception('insertStructuredAppendHeader wrong index');
        }

        $buf = array($size, $index, $parity);

        try {
            $entry = new QRinputItem(QR_MODE_STRUCTURE, 3, buf);
            array_unshift($this->items, $entry);
            return 0;
        } catch (Exception $e) {
            return -1;
        }
    }

    //----------------------------------------------------------------------
    public function calcParity()
    {
        $parity = 0;

        foreach($this->items as $item) {
            if($item->mode != QR_MODE_STRUCTURE) {
                for($i=$item->size-1; $i>=0; $i--) {
                    $parity ^= $item->data[$i];
                }
            }
        }

        return $parity;
    }

    //----------------------------------------------------------------------
    public static function checkModeNum($size, $data)
    {
        for($i=0; $i<$size; $i++) {
            if((ord($data[$i]) < ord('0')) || (ord($data[$i]) > ord('9'))){
                return false;
            }
        }

        return true;
    }

    //----------------------------------------------------------------------
    public static function estimateBitsModeNum($size)
    {
        $w = (int)$size / 3;
        $bits = $w * 10;

        switch($size - $w * 3) {
            case 1:
                $bits += 4;
                break;
            case 2:
                $bits += 7;
                break;
            default:
                break;
        }

        return $bits;
    }

    //----------------------------------------------------------------------
    public static $anTable = array(
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
        36, -1, -1, -1, 37, 38, -1, -1, -1, -1, 39, 40, -1, 41, 42, 43,
        0,  1,  2,  3,  4,  5,  6,  7,  8,  9, 44, -1, -1, -1, -1, -1,
        -1, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24,
        25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, -1, -1, -1, -1, -1,
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1
    );

    //----------------------------------------------------------------------
    public static function lookAnTable($c)
    {
        return (($c > 127)?-1:self::$anTable[$c]);
    }

    //----------------------------------------------------------------------
    public static function checkModeAn($size, $data)
    {
        for($i=0; $i<$size; $i++) {
            if (self::lookAnTable(ord($data[$i])) == -1) {
                return false;
            }
        }

        return true;
    }

    //----------------------------------------------------------------------
    public static function estimateBitsModeAn($size)
    {
        $w = (int)($size / 2);
        $bits = $w * 11;

        if($size & 1) {
            $bits += 6;
        }

        return $bits;
    }

    //----------------------------------------------------------------------
    public static function estimateBitsMode8($size)
    {
        return $size * 8;
    }

    //----------------------------------------------------------------------
    public function estimateBitsModeKanji($size)
    {
        return (int)(($size / 2) * 13);
    }

    //----------------------------------------------------------------------
    public static function checkModeKanji($size, $data)
    {
        if($size & 1)
            return false;

        for($i=0; $i<$size; $i+=2) {
            $val = (ord($data[$i]) << 8) | ord($data[$i+1]);
            if( $val < 0x8140
                || ($val > 0x9ffc && $val < 0xe040)
                || $val > 0xebbf) {
                return false;
            }
        }

        return true;
    }

    /***********************************************************************
     * Validation
     **********************************************************************/

    public static function check($mode, $size, $data)
    {
        if($size <= 0)
            return false;

        switch($mode) {
            case QR_MODE_NUM:       return self::checkModeNum($size, $data);   break;
            case QR_MODE_AN:        return self::checkModeAn($size, $data);    break;
            case QR_MODE_KANJI:     return self::checkModeKanji($size, $data); break;
            case QR_MODE_8:         return true; break;
            case QR_MODE_STRUCTURE: return true; break;

            default:
                break;
        }

        return false;
    }


    //----------------------------------------------------------------------
    public function estimateBitStreamSize($version)
    {
        $bits = 0;

        foreach($this->items as $item) {
            $bits += $item->estimateBitStreamSizeOfEntry($version);
        }

        return $bits;
    }

    //----------------------------------------------------------------------
    public function estimateVersion()
    {
        $version = 0;
        $prev = 0;
        do {
            $prev = $version;
            $bits = $this->estimateBitStreamSize($prev);
            $version = QRspec::getMinimumVersion((int)(($bits + 7) / 8), $this->level);
            if ($version < 0) {
                return -1;
            }
        } while ($version > $prev);

        return $version;
    }

    //----------------------------------------------------------------------
    public static function lengthOfCode($mode, $version, $bits)
    {
        $payload = $bits - 4 - QRspec::lengthIndicator($mode, $version);
        switch($mode) {
            case QR_MODE_NUM:
                $chunks = (int)($payload / 10);
                $remain = $payload - $chunks * 10;
                $size = $chunks * 3;
                if($remain >= 7) {
                    $size += 2;
                } else if($remain >= 4) {
                    $size += 1;
                }
                break;
            case QR_MODE_AN:
                $chunks = (int)($payload / 11);
                $remain = $payload - $chunks * 11;
                $size = $chunks * 2;
                if($remain >= 6)
                    $size++;
                break;
            case QR_MODE_8:
                $size = (int)($payload / 8);
                break;
            case QR_MODE_KANJI:
                $size = (int)(($payload / 13) * 2);
                break;
            case QR_MODE_STRUCTURE:
                $size = (int)($payload / 8);
                break;
            default:
                $size = 0;
                break;
        }

        $maxsize = QRspec::maximumWords($mode, $version);
        if($size < 0) $size = 0;
        if($size > $maxsize) $size = $maxsize;

        return $size;
    }

    //----------------------------------------------------------------------
    public function createBitStream()
    {
        $total = 0;

        foreach($this->items as $item) {
            $bits = $item->encodeBitStream($this->version);

            if($bits < 0)
                return -1;

            $total += $bits;
        }

        return $total;
    }

    //----------------------------------------------------------------------
    public function convertData()
    {
        $ver = $this->estimateVersion();
        if($ver > $this->getVersion()) {
            $this->setVersion($ver);
        }

        for(;;) {
            $bits = $this->createBitStream();

            if($bits < 0)
                return -1;

            $ver = QRspec::getMinimumVersion((int)(($bits + 7) / 8), $this->level);
            if($ver < 0) {
                throw new Exception('WRONG VERSION');
                return -1;
            } else if($ver > $this->getVersion()) {
                $this->setVersion($ver);
            } else {
                break;
            }
        }

        return 0;
    }

    //----------------------------------------------------------------------
    public function appendPaddingBit(&$bstream)
    {
        $bits = $bstream->size();
        $maxwords = QRspec::getDataLength($this->version, $this->level);
        $maxbits = $maxwords * 8;

        if ($maxbits == $bits) {
            return 0;
        }

        if ($maxbits - $bits < 5) {
            return $bstream->appendNum($maxbits - $bits, 0);
        }

        $bits += 4;
        $words = (int)(($bits + 7) / 8);

        $padding = new QRbitstream();
        $ret = $padding->appendNum($words * 8 - $bits + 4, 0);

        if($ret < 0)
            return $ret;

        $padlen = $maxwords - $words;

        if($padlen > 0) {

            $padbuf = array();
            for($i=0; $i<$padlen; $i++) {
                $padbuf[$i] = ($i&1)?0x11:0xec;
            }

            $ret = $padding->appendBytes($padlen, $padbuf);

            if($ret < 0)
                return $ret;

        }

        $ret = $bstream->append($padding);

        return $ret;
    }

    //----------------------------------------------------------------------
    public function mergeBitStream()
    {
        if($this->convertData() < 0) {
            return null;
        }

        $bstream = new QRbitstream();

        foreach($this->items as $item) {
            $ret = $bstream->append($item->bstream);
            if($ret < 0) {
                return null;
            }
        }

        return $bstream;
    }

    //----------------------------------------------------------------------
    public function getBitStream()
    {

        $bstream = $this->mergeBitStream();

        if($bstream == null) {
            return null;
        }

        $ret = $this->appendPaddingBit($bstream);
        if($ret < 0) {
            return null;
        }

        return $bstream;
    }

    //----------------------------------------------------------------------
    public function getByteStream()
    {
        $bstream = $this->getBitStream();
        if($bstream == null) {
            return null;
        }

        return $bstream->toByte();
    }
}






//---- qrbitstream.php -----------------------------




/*
 * PHP QR Code encoder
 *
 * Bitstream class
 *
 * Based on libqrencode C library distributed under LGPL 2.1
 * Copyright (C) 2006, 2007, 2008, 2009 Kentaro Fukuchi <fukuchi@megaui.net>
 *
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia <deltalab at poczta dot fm>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

class QRbitstream {

    public $data = array();

    //----------------------------------------------------------------------
    public function size()
    {
        return count($this->data);
    }

    //----------------------------------------------------------------------
    public function allocate($setLength)
    {
        $this->data = array_fill(0, $setLength, 0);
        return 0;
    }

    //----------------------------------------------------------------------
    public static function newFromNum($bits, $num)
    {
        $bstream = new QRbitstream();
        $bstream->allocate($bits);

        $mask = 1 << ($bits - 1);
        for($i=0; $i<$bits; $i++) {
            if($num & $mask) {
                $bstream->data[$i] = 1;
            } else {
                $bstream->data[$i] = 0;
            }
            $mask = $mask >> 1;
        }

        return $bstream;
    }

    //----------------------------------------------------------------------
    public static function newFromBytes($size, $data)
    {
        $bstream = new QRbitstream();
        $bstream->allocate($size * 8);
        $p=0;

        for($i=0; $i<$size; $i++) {
            $mask = 0x80;
            for($j=0; $j<8; $j++) {
                if($data[$i] & $mask) {
                    $bstream->data[$p] = 1;
                } else {
                    $bstream->data[$p] = 0;
                }
                $p++;
                $mask = $mask >> 1;
            }
        }

        return $bstream;
    }

    //----------------------------------------------------------------------
    public function append(QRbitstream $arg)
    {
        if (is_null($arg)) {
            return -1;
        }

        if($arg->size() == 0) {
            return 0;
        }

        if($this->size() == 0) {
            $this->data = $arg->data;
            return 0;
        }

        $this->data = array_values(array_merge($this->data, $arg->data));

        return 0;
    }

    //----------------------------------------------------------------------
    public function appendNum($bits, $num)
    {
        if ($bits == 0)
            return 0;

        $b = QRbitstream::newFromNum($bits, $num);

        if(is_null($b))
            return -1;

        $ret = $this->append($b);
        unset($b);

        return $ret;
    }

    //----------------------------------------------------------------------
    public function appendBytes($size, $data)
    {
        if ($size == 0)
            return 0;

        $b = QRbitstream::newFromBytes($size, $data);

        if(is_null($b))
            return -1;

        $ret = $this->append($b);
        unset($b);

        return $ret;
    }

    //----------------------------------------------------------------------
    public function toByte()
    {

        $size = $this->size();

        if($size == 0) {
            return array();
        }

        $data = array_fill(0, (int)(($size + 7) / 8), 0);
        $bytes = (int)($size / 8);

        $p = 0;

        for($i=0; $i<$bytes; $i++) {
            $v = 0;
            for($j=0; $j<8; $j++) {
                $v = $v << 1;
                $v |= $this->data[$p];
                $p++;
            }
            $data[$i] = $v;
        }

        if($size & 7) {
            $v = 0;
            for($j=0; $j<($size & 7); $j++) {
                $v = $v << 1;
                $v |= $this->data[$p];
                $p++;
            }
            $data[$bytes] = $v;
        }

        return $data;
    }

}




//---- qrsplit.php -----------------------------




/*
 * PHP QR Code encoder
 *
 * Input splitting classes
 *
 * Based on libqrencode C library distributed under LGPL 2.1
 * Copyright (C) 2006, 2007, 2008, 2009 Kentaro Fukuchi <fukuchi@megaui.net>
 *
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia <deltalab at poczta dot fm>
 *
 * The following data / specifications are taken from
 * "Two dimensional symbol -- QR-code -- Basic Specification" (JIS X0510:2004)
 *  or
 * "Automatic identification and data capture techniques -- 
 *  QR Code 2005 bar code symbology specification" (ISO/IEC 18004:2006)
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
class QRsplit {

    public $dataStr = '';
    public $input;
    public $modeHint;

    //----------------------------------------------------------------------
    public function __construct($dataStr, $input, $modeHint)
    {
        $this->dataStr  = $dataStr;
        $this->input    = $input;
        $this->modeHint = $modeHint;
    }

    //----------------------------------------------------------------------
    public static function isdigitat($str, $pos)
    {
        if ($pos >= strlen($str))
            return false;

        return ((ord($str[$pos]) >= ord('0'))&&(ord($str[$pos]) <= ord('9')));
    }

    //----------------------------------------------------------------------
    public static function isalnumat($str, $pos)
    {
        if ($pos >= strlen($str))
            return false;

        return (QRinput::lookAnTable(ord($str[$pos])) >= 0);
    }

    //----------------------------------------------------------------------
    public function identifyMode($pos)
    {
        if ($pos >= strlen($this->dataStr))
            return QR_MODE_NUL;

        $c = $this->dataStr[$pos];

        if(self::isdigitat($this->dataStr, $pos)) {
            return QR_MODE_NUM;
        } else if(self::isalnumat($this->dataStr, $pos)) {
            return QR_MODE_AN;
        } else if($this->modeHint == QR_MODE_KANJI) {

            if ($pos+1 < strlen($this->dataStr))
            {
                $d = $this->dataStr[$pos+1];
                $word = (ord($c) << 8) | ord($d);
                if(($word >= 0x8140 && $word <= 0x9ffc) || ($word >= 0xe040 && $word <= 0xebbf)) {
                    return QR_MODE_KANJI;
                }
            }
        }

        return QR_MODE_8;
    }

    //----------------------------------------------------------------------
    public function eatNum()
    {
        $ln = QRspec::lengthIndicator(QR_MODE_NUM, $this->input->getVersion());

        $p = 0;
        while(self::isdigitat($this->dataStr, $p)) {
            $p++;
        }

        $run = $p;
        $mode = $this->identifyMode($p);

        if($mode == QR_MODE_8) {
            $dif = QRinput::estimateBitsModeNum($run) + 4 + $ln
                + QRinput::estimateBitsMode8(1)         // + 4 + l8
                - QRinput::estimateBitsMode8($run + 1); // - 4 - l8
            if($dif > 0) {
                return $this->eat8();
            }
        }
        if($mode == QR_MODE_AN) {
            $dif = QRinput::estimateBitsModeNum($run) + 4 + $ln
                + QRinput::estimateBitsModeAn(1)        // + 4 + la
                - QRinput::estimateBitsModeAn($run + 1);// - 4 - la
            if($dif > 0) {
                return $this->eatAn();
            }
        }

        $ret = $this->input->append(QR_MODE_NUM, $run, str_split($this->dataStr));
        if($ret < 0)
            return -1;

        return $run;
    }

    //----------------------------------------------------------------------
    public function eatAn()
    {
        $la = QRspec::lengthIndicator(QR_MODE_AN,  $this->input->getVersion());
        $ln = QRspec::lengthIndicator(QR_MODE_NUM, $this->input->getVersion());

        $p = 0;

        while(self::isalnumat($this->dataStr, $p)) {
            if(self::isdigitat($this->dataStr, $p)) {
                $q = $p;
                while(self::isdigitat($this->dataStr, $q)) {
                    $q++;
                }

                $dif = QRinput::estimateBitsModeAn($p) // + 4 + la
                    + QRinput::estimateBitsModeNum($q - $p) + 4 + $ln
                    - QRinput::estimateBitsModeAn($q); // - 4 - la

                if($dif < 0) {
                    break;
                } else {
                    $p = $q;
                }
            } else {
                $p++;
            }
        }

        $run = $p;

        if(!self::isalnumat($this->dataStr, $p)) {
            $dif = QRinput::estimateBitsModeAn($run) + 4 + $la
                + QRinput::estimateBitsMode8(1) // + 4 + l8
                - QRinput::estimateBitsMode8($run + 1); // - 4 - l8
            if($dif > 0) {
                return $this->eat8();
            }
        }

        $ret = $this->input->append(QR_MODE_AN, $run, str_split($this->dataStr));
        if($ret < 0)
            return -1;

        return $run;
    }

    //----------------------------------------------------------------------
    public function eatKanji()
    {
        $p = 0;

        while($this->identifyMode($p) == QR_MODE_KANJI) {
            $p += 2;
        }

        $ret = $this->input->append(QR_MODE_KANJI, $p, str_split($this->dataStr));
        if($ret < 0)
            return -1;

        return $run;
    }

    //----------------------------------------------------------------------
    public function eat8()
    {
        $la = QRspec::lengthIndicator(QR_MODE_AN, $this->input->getVersion());
        $ln = QRspec::lengthIndicator(QR_MODE_NUM, $this->input->getVersion());

        $p = 1;
        $dataStrLen = strlen($this->dataStr);

        while($p < $dataStrLen) {

            $mode = $this->identifyMode($p);
            if($mode == QR_MODE_KANJI) {
                break;
            }
            if($mode == QR_MODE_NUM) {
                $q = $p;
                while(self::isdigitat($this->dataStr, $q)) {
                    $q++;
                }
                $dif = QRinput::estimateBitsMode8($p) // + 4 + l8
                    + QRinput::estimateBitsModeNum($q - $p) + 4 + $ln
                    - QRinput::estimateBitsMode8($q); // - 4 - l8
                if($dif < 0) {
                    break;
                } else {
                    $p = $q;
                }
            } else if($mode == QR_MODE_AN) {
                $q = $p;
                while(self::isalnumat($this->dataStr, $q)) {
                    $q++;
                }
                $dif = QRinput::estimateBitsMode8($p)  // + 4 + l8
                    + QRinput::estimateBitsModeAn($q - $p) + 4 + $la
                    - QRinput::estimateBitsMode8($q); // - 4 - l8
                if($dif < 0) {
                    break;
                } else {
                    $p = $q;
                }
            } else {
                $p++;
            }
        }

        $run = $p;
        $ret = $this->input->append(QR_MODE_8, $run, str_split($this->dataStr));

        if($ret < 0)
            return -1;

        return $run;
    }

    //----------------------------------------------------------------------
    public function splitString()
    {
        while (strlen($this->dataStr) > 0)
        {
            if($this->dataStr == '')
                return 0;

            $mode = $this->identifyMode(0);

            switch ($mode) {
                case QR_MODE_NUM: $length = $this->eatNum(); break;
                case QR_MODE_AN:  $length = $this->eatAn(); break;
                case QR_MODE_KANJI:
                    if ($hint == QR_MODE_KANJI)
                        $length = $this->eatKanji();
                    else    $length = $this->eat8();
                    break;
                default: $length = $this->eat8(); break;

            }

            if($length == 0) return 0;
            if($length < 0)  return -1;

            $this->dataStr = substr($this->dataStr, $length);
        }
    }

    //----------------------------------------------------------------------
    public function toUpper()
    {
        $stringLen = strlen($this->dataStr);
        $p = 0;

        while ($p<$stringLen) {
            $mode = self::identifyMode(substr($this->dataStr, $p), $this->modeHint);
            if($mode == QR_MODE_KANJI) {
                $p += 2;
            } else {
                if (ord($this->dataStr[$p]) >= ord('a') && ord($this->dataStr[$p]) <= ord('z')) {
                    $this->dataStr[$p] = chr(ord($this->dataStr[$p]) - 32);
                }
                $p++;
            }
        }

        return $this->dataStr;
    }

    //----------------------------------------------------------------------
    public static function splitStringToQRinput($string, QRinput $input, $modeHint, $casesensitive = true)
    {
        if(is_null($string) || $string == '\0' || $string == '') {
            throw new Exception('empty string!!!');
        }

        $split = new QRsplit($string, $input, $modeHint);

        if(!$casesensitive)
            $split->toUpper();

        return $split->splitString();
    }
}



//---- qrrscode.php -----------------------------




/*
 * PHP QR Code encoder
 *
 * Reed-Solomon error correction support
 * 
 * Copyright (C) 2002, 2003, 2004, 2006 Phil Karn, KA9Q
 * (libfec is released under the GNU Lesser General Public License.)
 *
 * Based on libqrencode C library distributed under LGPL 2.1
 * Copyright (C) 2006, 2007, 2008, 2009 Kentaro Fukuchi <fukuchi@megaui.net>
 *
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia <deltalab at poczta dot fm>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

class QRrsItem {

    public $mm;                  // Bits per symbol
    public $nn;                  // Symbols per block (= (1<<mm)-1)
    public $alpha_to = array();  // log lookup table
    public $index_of = array();  // Antilog lookup table
    public $genpoly = array();   // Generator polynomial
    public $nroots;              // Number of generator roots = number of parity symbols
    public $fcr;                 // First consecutive root, index form
    public $prim;                // Primitive element, index form
    public $iprim;               // prim-th root of 1, index form
    public $pad;                 // Padding bytes in shortened block
    public $gfpoly;

    //----------------------------------------------------------------------
    public function modnn($x)
    {
        while ($x >= $this->nn) {
            $x -= $this->nn;
            $x = ($x >> $this->mm) + ($x & $this->nn);
        }

        return $x;
    }

    //----------------------------------------------------------------------
    public static function init_rs_char($symsize, $gfpoly, $fcr, $prim, $nroots, $pad)
    {
        // Common code for intializing a Reed-Solomon control block (char or int symbols)
        // Copyright 2004 Phil Karn, KA9Q
        // May be used under the terms of the GNU Lesser General Public License (LGPL)

        $rs = null;

        // Check parameter ranges
        if($symsize < 0 || $symsize > 8)                     return $rs;
        if($fcr < 0 || $fcr >= (1<<$symsize))                return $rs;
        if($prim <= 0 || $prim >= (1<<$symsize))             return $rs;
        if($nroots < 0 || $nroots >= (1<<$symsize))          return $rs; // Can't have more roots than symbol values!
        if($pad < 0 || $pad >= ((1<<$symsize) -1 - $nroots)) return $rs; // Too much padding

        $rs = new QRrsItem();
        $rs->mm = $symsize;
        $rs->nn = (1<<$symsize)-1;
        $rs->pad = $pad;

        $rs->alpha_to = array_fill(0, $rs->nn+1, 0);
        $rs->index_of = array_fill(0, $rs->nn+1, 0);

        // PHP style macro replacement ;)
        $NN =& $rs->nn;
        $A0 =& $NN;

        // Generate Galois field lookup tables
        $rs->index_of[0] = $A0; // log(zero) = -inf
        $rs->alpha_to[$A0] = 0; // alpha**-inf = 0
        $sr = 1;

        for($i=0; $i<$rs->nn; $i++) {
            $rs->index_of[$sr] = $i;
            $rs->alpha_to[$i] = $sr;
            $sr <<= 1;
            if($sr & (1<<$symsize)) {
                $sr ^= $gfpoly;
            }
            $sr &= $rs->nn;
        }

        if($sr != 1){
            // field generator polynomial is not primitive!
            $rs = NULL;
            return $rs;
        }

        /* Form RS code generator polynomial from its roots */
        $rs->genpoly = array_fill(0, $nroots+1, 0);

        $rs->fcr = $fcr;
        $rs->prim = $prim;
        $rs->nroots = $nroots;
        $rs->gfpoly = $gfpoly;

        /* Find prim-th root of 1, used in decoding */
        for($iprim=1;($iprim % $prim) != 0;$iprim += $rs->nn)
            ; // intentional empty-body loop!

        $rs->iprim = (int)($iprim / $prim);
        $rs->genpoly[0] = 1;

        for ($i = 0,$root=$fcr*$prim; $i < $nroots; $i++, $root += $prim) {
            $rs->genpoly[$i+1] = 1;

            // Multiply rs->genpoly[] by  @**(root + x)
            for ($j = $i; $j > 0; $j--) {
                if ($rs->genpoly[$j] != 0) {
                    $rs->genpoly[$j] = $rs->genpoly[$j-1] ^ $rs->alpha_to[$rs->modnn($rs->index_of[$rs->genpoly[$j]] + $root)];
                } else {
                    $rs->genpoly[$j] = $rs->genpoly[$j-1];
                }
            }
            // rs->genpoly[0] can never be zero
            $rs->genpoly[0] = $rs->alpha_to[$rs->modnn($rs->index_of[$rs->genpoly[0]] + $root)];
        }

        // convert rs->genpoly[] to index form for quicker encoding
        for ($i = 0; $i <= $nroots; $i++)
            $rs->genpoly[$i] = $rs->index_of[$rs->genpoly[$i]];

        return $rs;
    }

    //----------------------------------------------------------------------
    public function encode_rs_char($data, &$parity)
    {
        $MM       =& $this->mm;
        $NN       =& $this->nn;
        $ALPHA_TO =& $this->alpha_to;
        $INDEX_OF =& $this->index_of;
        $GENPOLY  =& $this->genpoly;
        $NROOTS   =& $this->nroots;
        $FCR      =& $this->fcr;
        $PRIM     =& $this->prim;
        $IPRIM    =& $this->iprim;
        $PAD      =& $this->pad;
        $A0       =& $NN;

        $parity = array_fill(0, $NROOTS, 0);

        for($i=0; $i< ($NN-$NROOTS-$PAD); $i++) {

            $feedback = $INDEX_OF[$data[$i] ^ $parity[0]];
            if($feedback != $A0) {
                // feedback term is non-zero

                // This line is unnecessary when GENPOLY[NROOTS] is unity, as it must
                // always be for the polynomials constructed by init_rs()
                $feedback = $this->modnn($NN - $GENPOLY[$NROOTS] + $feedback);

                for($j=1;$j<$NROOTS;$j++) {
                    $parity[$j] ^= $ALPHA_TO[$this->modnn($feedback + $GENPOLY[$NROOTS-$j])];
                }
            }

            // Shift
            array_shift($parity);
            if($feedback != $A0) {
                array_push($parity, $ALPHA_TO[$this->modnn($feedback + $GENPOLY[0])]);
            } else {
                array_push($parity, 0);
            }
        }
    }
}

//##########################################################################

class QRrs {

    public static $items = array();

    //----------------------------------------------------------------------
    public static function init_rs($symsize, $gfpoly, $fcr, $prim, $nroots, $pad)
    {
        foreach(self::$items as $rs) {
            if($rs->pad != $pad)       continue;
            if($rs->nroots != $nroots) continue;
            if($rs->mm != $symsize)    continue;
            if($rs->gfpoly != $gfpoly) continue;
            if($rs->fcr != $fcr)       continue;
            if($rs->prim != $prim)     continue;

            return $rs;
        }

        $rs = QRrsItem::init_rs_char($symsize, $gfpoly, $fcr, $prim, $nroots, $pad);
        array_unshift(self::$items, $rs);

        return $rs;
    }
}



//---- qrmask.php -----------------------------




/*
 * PHP QR Code encoder
 *
 * Masking
 *
 * Based on libqrencode C library distributed under LGPL 2.1
 * Copyright (C) 2006, 2007, 2008, 2009 Kentaro Fukuchi <fukuchi@megaui.net>
 *
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia <deltalab at poczta dot fm>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

define('N1', 3);
define('N2', 3);
define('N3', 40);
define('N4', 10);

class QRmask {

    public $runLength = array();

    //----------------------------------------------------------------------
    public function __construct()
    {
        $this->runLength = array_fill(0, QRSPEC_WIDTH_MAX + 1, 0);
    }

    //----------------------------------------------------------------------
    public function writeFormatInformation($width, &$frame, $mask, $level)
    {
        $blacks = 0;
        $format =  QRspec::getFormatInfo($mask, $level);

        for($i=0; $i<8; $i++) {
            if($format & 1) {
                $blacks += 2;
                $v = 0x85;
            } else {
                $v = 0x84;
            }

            $frame[8][$width - 1 - $i] = chr($v);
            if($i < 6) {
                $frame[$i][8] = chr($v);
            } else {
                $frame[$i + 1][8] = chr($v);
            }
            $format = $format >> 1;
        }

        for($i=0; $i<7; $i++) {
            if($format & 1) {
                $blacks += 2;
                $v = 0x85;
            } else {
                $v = 0x84;
            }

            $frame[$width - 7 + $i][8] = chr($v);
            if($i == 0) {
                $frame[8][7] = chr($v);
            } else {
                $frame[8][6 - $i] = chr($v);
            }

            $format = $format >> 1;
        }

        return $blacks;
    }

    //----------------------------------------------------------------------
    public function mask0($x, $y) { return ($x+$y)&1;                       }
    public function mask1($x, $y) { return ($y&1);                          }
    public function mask2($x, $y) { return ($x%3);                          }
    public function mask3($x, $y) { return ($x+$y)%3;                       }
    public function mask4($x, $y) { return (((int)($y/2))+((int)($x/3)))&1; }
    public function mask5($x, $y) { return (($x*$y)&1)+($x*$y)%3;           }
    public function mask6($x, $y) { return ((($x*$y)&1)+($x*$y)%3)&1;       }
    public function mask7($x, $y) { return ((($x*$y)%3)+(($x+$y)&1))&1;     }

    //----------------------------------------------------------------------
    private function generateMaskNo($maskNo, $width, $frame)
    {
        $bitMask = array_fill(0, $width, array_fill(0, $width, 0));

        for($y=0; $y<$width; $y++) {
            for($x=0; $x<$width; $x++) {
                if(ord($frame[$y][$x]) & 0x80) {
                    $bitMask[$y][$x] = 0;
                } else {
                    $maskFunc = call_user_func(array($this, 'mask'.$maskNo), $x, $y);
                    $bitMask[$y][$x] = ($maskFunc == 0)?1:0;
                }

            }
        }

        return $bitMask;
    }

    //----------------------------------------------------------------------
    public static function serial($bitFrame)
    {
        $codeArr = array();

        foreach ($bitFrame as $line)
            $codeArr[] = join('', $line);

        return gzcompress(join("\n", $codeArr), 9);
    }

    //----------------------------------------------------------------------
    public static function unserial($code)
    {
        $codeArr = array();

        $codeLines = explode("\n", gzuncompress($code));
        foreach ($codeLines as $line)
            $codeArr[] = str_split($line);

        return $codeArr;
    }

    //----------------------------------------------------------------------
    public function makeMaskNo($maskNo, $width, $s, &$d, $maskGenOnly = false)
    {
        $b = 0;
        $bitMask = array();

        $fileName = QR_CACHE_DIR.'mask_'.$maskNo.DIRECTORY_SEPARATOR.'mask_'.$width.'_'.$maskNo.'.dat';

        if (QR_CACHEABLE) {
            if (file_exists($fileName)) {
                $bitMask = self::unserial(file_get_contents($fileName));
            } else {
                $bitMask = $this->generateMaskNo($maskNo, $width, $s, $d);
                if (!file_exists(QR_CACHE_DIR.'mask_'.$maskNo))
                    mkdir(QR_CACHE_DIR.'mask_'.$maskNo);
                file_put_contents($fileName, self::serial($bitMask));
            }
        } else {
            $bitMask = $this->generateMaskNo($maskNo, $width, $s, $d);
        }

        if ($maskGenOnly)
            return;

        $d = $s;

        for($y=0; $y<$width; $y++) {
            for($x=0; $x<$width; $x++) {
                if($bitMask[$y][$x] == 1) {
                    $d[$y][$x] = chr(ord($s[$y][$x]) ^ (int)$bitMask[$y][$x]);
                }
                $b += (int)(ord($d[$y][$x]) & 1);
            }
        }

        return $b;
    }

    //----------------------------------------------------------------------
    public function makeMask($width, $frame, $maskNo, $level)
    {
        $masked = array_fill(0, $width, str_repeat("\0", $width));
        $this->makeMaskNo($maskNo, $width, $frame, $masked);
        $this->writeFormatInformation($width, $masked, $maskNo, $level);

        return $masked;
    }

    //----------------------------------------------------------------------
    public function calcN1N3($length)
    {
        $demerit = 0;

        for($i=0; $i<$length; $i++) {

            if($this->runLength[$i] >= 5) {
                $demerit += (N1 + ($this->runLength[$i] - 5));
            }
            if($i & 1) {
                if(($i >= 3) && ($i < ($length-2)) && ($this->runLength[$i] % 3 == 0)) {
                    $fact = (int)($this->runLength[$i] / 3);
                    if(($this->runLength[$i-2] == $fact) &&
                        ($this->runLength[$i-1] == $fact) &&
                        ($this->runLength[$i+1] == $fact) &&
                        ($this->runLength[$i+2] == $fact)) {
                        if(($this->runLength[$i-3] < 0) || ($this->runLength[$i-3] >= (4 * $fact))) {
                            $demerit += N3;
                        } else if((($i+3) >= $length) || ($this->runLength[$i+3] >= (4 * $fact))) {
                            $demerit += N3;
                        }
                    }
                }
            }
        }
        return $demerit;
    }

    //----------------------------------------------------------------------
    public function evaluateSymbol($width, $frame)
    {
        $head = 0;
        $demerit = 0;

        for($y=0; $y<$width; $y++) {
            $head = 0;
            $this->runLength[0] = 1;

            $frameY = $frame[$y];

            if ($y>0)
                $frameYM = $frame[$y-1];

            for($x=0; $x<$width; $x++) {
                if(($x > 0) && ($y > 0)) {
                    $b22 = ord($frameY[$x]) & ord($frameY[$x-1]) & ord($frameYM[$x]) & ord($frameYM[$x-1]);
                    $w22 = ord($frameY[$x]) | ord($frameY[$x-1]) | ord($frameYM[$x]) | ord($frameYM[$x-1]);

                    if(($b22 | ($w22 ^ 1))&1) {
                        $demerit += N2;
                    }
                }
                if(($x == 0) && (ord($frameY[$x]) & 1)) {
                    $this->runLength[0] = -1;
                    $head = 1;
                    $this->runLength[$head] = 1;
                } else if($x > 0) {
                    if((ord($frameY[$x]) ^ ord($frameY[$x-1])) & 1) {
                        $head++;
                        $this->runLength[$head] = 1;
                    } else {
                        $this->runLength[$head]++;
                    }
                }
            }

            $demerit += $this->calcN1N3($head+1);
        }

        for($x=0; $x<$width; $x++) {
            $head = 0;
            $this->runLength[0] = 1;

            for($y=0; $y<$width; $y++) {
                if($y == 0 && (ord($frame[$y][$x]) & 1)) {
                    $this->runLength[0] = -1;
                    $head = 1;
                    $this->runLength[$head] = 1;
                } else if($y > 0) {
                    if((ord($frame[$y][$x]) ^ ord($frame[$y-1][$x])) & 1) {
                        $head++;
                        $this->runLength[$head] = 1;
                    } else {
                        $this->runLength[$head]++;
                    }
                }
            }

            $demerit += $this->calcN1N3($head+1);
        }

        return $demerit;
    }


    //----------------------------------------------------------------------
    public function mask($width, $frame, $level)
    {
        $minDemerit = PHP_INT_MAX;
        $bestMaskNum = 0;
        $bestMask = array();

        $checked_masks = array(0,1,2,3,4,5,6,7);

        if (QR_FIND_FROM_RANDOM !== false) {

            $howManuOut = 8-(QR_FIND_FROM_RANDOM % 9);
            for ($i = 0; $i <  $howManuOut; $i++) {
                $remPos = rand (0, count($checked_masks)-1);
                unset($checked_masks[$remPos]);
                $checked_masks = array_values($checked_masks);
            }

        }

        $bestMask = $frame;

        foreach($checked_masks as $i) {
            $mask = array_fill(0, $width, str_repeat("\0", $width));

            $demerit = 0;
            $blacks = 0;
            $blacks  = $this->makeMaskNo($i, $width, $frame, $mask);
            $blacks += $this->writeFormatInformation($width, $mask, $i, $level);
            $blacks  = (int)(100 * $blacks / ($width * $width));
            $demerit = (int)((int)(abs($blacks - 50) / 5) * N4);
            $demerit += $this->evaluateSymbol($width, $mask);

            if($demerit < $minDemerit) {
                $minDemerit = $demerit;
                $bestMask = $mask;
                $bestMaskNum = $i;
            }
        }

        return $bestMask;
    }

    //----------------------------------------------------------------------
}




//---- qrencode.php -----------------------------




/*
 * PHP QR Code encoder
 *
 * Main encoder classes.
 *
 * Based on libqrencode C library distributed under LGPL 2.1
 * Copyright (C) 2006, 2007, 2008, 2009 Kentaro Fukuchi <fukuchi@megaui.net>
 *
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia <deltalab at poczta dot fm>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

class QRrsblock {
    public $dataLength;
    public $data = array();
    public $eccLength;
    public $ecc = array();

    public function __construct($dl, $data, $el, &$ecc, QRrsItem $rs)
    {
        $rs->encode_rs_char($data, $ecc);

        $this->dataLength = $dl;
        $this->data = $data;
        $this->eccLength = $el;
        $this->ecc = $ecc;
    }
};

//##########################################################################

class QRrawcode {
    public $version;
    public $datacode = array();
    public $ecccode = array();
    public $blocks;
    public $rsblocks = array(); //of RSblock
    public $count;
    public $dataLength;
    public $eccLength;
    public $b1;

    //----------------------------------------------------------------------
    public function __construct(QRinput $input)
    {
        $spec = array(0,0,0,0,0);

        $this->datacode = $input->getByteStream();
        if(is_null($this->datacode)) {
            throw new Exception('null imput string');
        }

        QRspec::getEccSpec($input->getVersion(), $input->getErrorCorrectionLevel(), $spec);

        $this->version = $input->getVersion();
        $this->b1 = QRspec::rsBlockNum1($spec);
        $this->dataLength = QRspec::rsDataLength($spec);
        $this->eccLength = QRspec::rsEccLength($spec);
        $this->ecccode = array_fill(0, $this->eccLength, 0);
        $this->blocks = QRspec::rsBlockNum($spec);

        $ret = $this->init($spec);
        if($ret < 0) {
            throw new Exception('block alloc error');
            return null;
        }

        $this->count = 0;
    }

    //----------------------------------------------------------------------
    public function init(array $spec)
    {
        $dl = QRspec::rsDataCodes1($spec);
        $el = QRspec::rsEccCodes1($spec);
        $rs = QRrs::init_rs(8, 0x11d, 0, 1, $el, 255 - $dl - $el);


        $blockNo = 0;
        $dataPos = 0;
        $eccPos = 0;
        for($i=0; $i<QRspec::rsBlockNum1($spec); $i++) {
            $ecc = array_slice($this->ecccode,$eccPos);
            $this->rsblocks[$blockNo] = new QRrsblock($dl, array_slice($this->datacode, $dataPos), $el,  $ecc, $rs);
            $this->ecccode = array_merge(array_slice($this->ecccode,0, $eccPos), $ecc);

            $dataPos += $dl;
            $eccPos += $el;
            $blockNo++;
        }

        if(QRspec::rsBlockNum2($spec) == 0)
            return 0;

        $dl = QRspec::rsDataCodes2($spec);
        $el = QRspec::rsEccCodes2($spec);
        $rs = QRrs::init_rs(8, 0x11d, 0, 1, $el, 255 - $dl - $el);

        if($rs == NULL) return -1;

        for($i=0; $i<QRspec::rsBlockNum2($spec); $i++) {
            $ecc = array_slice($this->ecccode,$eccPos);
            $this->rsblocks[$blockNo] = new QRrsblock($dl, array_slice($this->datacode, $dataPos), $el, $ecc, $rs);
            $this->ecccode = array_merge(array_slice($this->ecccode,0, $eccPos), $ecc);

            $dataPos += $dl;
            $eccPos += $el;
            $blockNo++;
        }

        return 0;
    }

    //----------------------------------------------------------------------
    public function getCode()
    {
        $ret;

        if($this->count < $this->dataLength) {
            $row = $this->count % $this->blocks;
            $col = $this->count / $this->blocks;
            if($col >= $this->rsblocks[0]->dataLength) {
                $row += $this->b1;
            }
            $ret = $this->rsblocks[$row]->data[$col];
        } else if($this->count < $this->dataLength + $this->eccLength) {
            $row = ($this->count - $this->dataLength) % $this->blocks;
            $col = ($this->count - $this->dataLength) / $this->blocks;
            $ret = $this->rsblocks[$row]->ecc[$col];
        } else {
            return 0;
        }
        $this->count++;

        return $ret;
    }
}

//##########################################################################

class QRcode {

    public $version;
    public $width;
    public $data;

    //----------------------------------------------------------------------
    public function encodeMask(QRinput $input, $mask)
    {
        if($input->getVersion() < 0 || $input->getVersion() > QRSPEC_VERSION_MAX) {
            throw new Exception('wrong version');
        }
        if($input->getErrorCorrectionLevel() > QR_ECLEVEL_H) {
            throw new Exception('wrong level');
        }

        $raw = new QRrawcode($input);

        QRtools::markTime('after_raw');

        $version = $raw->version;
        $width = QRspec::getWidth($version);
        $frame = QRspec::newFrame($version);

        $filler = new FrameFiller($width, $frame);
        if(is_null($filler)) {
            return NULL;
        }

        // inteleaved data and ecc codes
        for($i=0; $i<$raw->dataLength + $raw->eccLength; $i++) {
            $code = $raw->getCode();
            $bit = 0x80;
            for($j=0; $j<8; $j++) {
                $addr = $filler->next();
                $filler->setFrameAt($addr, 0x02 | (($bit & $code) != 0));
                $bit = $bit >> 1;
            }
        }

        QRtools::markTime('after_filler');

        unset($raw);

        // remainder bits
        $j = QRspec::getRemainder($version);
        for($i=0; $i<$j; $i++) {
            $addr = $filler->next();
            $filler->setFrameAt($addr, 0x02);
        }

        $frame = $filler->frame;
        unset($filler);


        // masking
        $maskObj = new QRmask();
        if($mask < 0) {

            if (QR_FIND_BEST_MASK) {
                $masked = $maskObj->mask($width, $frame, $input->getErrorCorrectionLevel());
            } else {
                $masked = $maskObj->makeMask($width, $frame, (intval(QR_DEFAULT_MASK) % 8), $input->getErrorCorrectionLevel());
            }
        } else {
            $masked = $maskObj->makeMask($width, $frame, $mask, $input->getErrorCorrectionLevel());
        }

        if($masked == NULL) {
            return NULL;
        }

        QRtools::markTime('after_mask');

        $this->version = $version;
        $this->width = $width;
        $this->data = $masked;

        return $this;
    }

    //----------------------------------------------------------------------
    public function encodeInput(QRinput $input)
    {
        return $this->encodeMask($input, -1);
    }

    //----------------------------------------------------------------------
    public function encodeString8bit($string, $version, $level)
    {
        if(string == NULL) {
            throw new Exception('empty string!');
            return NULL;
        }

        $input = new QRinput($version, $level);
        if($input == NULL) return NULL;

        $ret = $input->append($input, QR_MODE_8, strlen($string), str_split($string));
        if($ret < 0) {
            unset($input);
            return NULL;
        }
        return $this->encodeInput($input);
    }

    //----------------------------------------------------------------------
    public function encodeString($string, $version, $level, $hint, $casesensitive)
    {

        if($hint != QR_MODE_8 && $hint != QR_MODE_KANJI) {
            throw new Exception('bad hint');
            return NULL;
        }

        $input = new QRinput($version, $level);
        if($input == NULL) return NULL;

        $ret = QRsplit::splitStringToQRinput($string, $input, $hint, $casesensitive);
        if($ret < 0) {
            return NULL;
        }

        return $this->encodeInput($input);
    }

    //----------------------------------------------------------------------
    public static function png($text, $outfile = false, $level = QR_ECLEVEL_L, $size = 3, $margin = 4, $saveandprint=false)
    {
        $enc = QRencode::factory($level, $size, $margin);
        return $enc->encodePNG($text, $outfile, $saveandprint=false);
    }

    //----------------------------------------------------------------------
    public static function text($text, $outfile = false, $level = QR_ECLEVEL_L, $size = 3, $margin = 4)
    {
        $enc = QRencode::factory($level, $size, $margin);
        return $enc->encode($text, $outfile);
    }

    //----------------------------------------------------------------------
    public static function raw($text, $outfile = false, $level = QR_ECLEVEL_L, $size = 3, $margin = 4)
    {
        $enc = QRencode::factory($level, $size, $margin);
        return $enc->encodeRAW($text, $outfile);
    }
}

//##########################################################################

class FrameFiller {

    public $width;
    public $frame;
    public $x;
    public $y;
    public $dir;
    public $bit;

    //----------------------------------------------------------------------
    public function __construct($width, &$frame)
    {
        $this->width = $width;
        $this->frame = $frame;
        $this->x = $width - 1;
        $this->y = $width - 1;
        $this->dir = -1;
        $this->bit = -1;
    }

    //----------------------------------------------------------------------
    public function setFrameAt($at, $val)
    {
        $this->frame[$at['y']][$at['x']] = chr($val);
    }

    //----------------------------------------------------------------------
    public function getFrameAt($at)
    {
        return ord($this->frame[$at['y']][$at['x']]);
    }

    //----------------------------------------------------------------------
    public function next()
    {
        do {

            if($this->bit == -1) {
                $this->bit = 0;
                return array('x'=>$this->x, 'y'=>$this->y);
            }

            $x = $this->x;
            $y = $this->y;
            $w = $this->width;

            if($this->bit == 0) {
                $x--;
                $this->bit++;
            } else {
                $x++;
                $y += $this->dir;
                $this->bit--;
            }

            if($this->dir < 0) {
                if($y < 0) {
                    $y = 0;
                    $x -= 2;
                    $this->dir = 1;
                    if($x == 6) {
                        $x--;
                        $y = 9;
                    }
                }
            } else {
                if($y == $w) {
                    $y = $w - 1;
                    $x -= 2;
                    $this->dir = -1;
                    if($x == 6) {
                        $x--;
                        $y -= 8;
                    }
                }
            }
            if($x < 0 || $y < 0) return null;

            $this->x = $x;
            $this->y = $y;

        } while(ord($this->frame[$y][$x]) & 0x80);

        return array('x'=>$x, 'y'=>$y);
    }

} ;

//##########################################################################

class QRencode {

    public $casesensitive = true;
    public $eightbit = false;

    public $version = 0;
    public $size = 3;
    public $margin = 4;

    public $structured = 0; // not supported yet

    public $level = QR_ECLEVEL_L;
    public $hint = QR_MODE_8;

    //----------------------------------------------------------------------
    public static function factory($level = QR_ECLEVEL_L, $size = 3, $margin = 4)
    {
        $enc = new QRencode();
        $enc->size = $size;
        $enc->margin = $margin;

        switch ($level.'') {
            case '0':
            case '1':
            case '2':
            case '3':
                $enc->level = $level;
                break;
            case 'l':
            case 'L':
                $enc->level = QR_ECLEVEL_L;
                break;
            case 'm':
            case 'M':
                $enc->level = QR_ECLEVEL_M;
                break;
            case 'q':
            case 'Q':
                $enc->level = QR_ECLEVEL_Q;
                break;
            case 'h':
            case 'H':
                $enc->level = QR_ECLEVEL_H;
                break;
        }

        return $enc;
    }

    //----------------------------------------------------------------------
    public function encodeRAW($intext, $outfile = false)
    {
        $code = new QRcode();

        if($this->eightbit) {
            $code->encodeString8bit($intext, $this->version, $this->level);
        } else {
            $code->encodeString($intext, $this->version, $this->level, $this->hint, $this->casesensitive);
        }

        return $code->data;
    }

    //----------------------------------------------------------------------
    public function encode($intext, $outfile = false)
    {
        $code = new QRcode();

        if($this->eightbit) {
            $code->encodeString8bit($intext, $this->version, $this->level);
        } else {
            $code->encodeString($intext, $this->version, $this->level, $this->hint, $this->casesensitive);
        }

        QRtools::markTime('after_encode');

        if ($outfile!== false) {
            file_put_contents($outfile, join("\n", QRtools::binarize($code->data)));
        } else {
            return QRtools::binarize($code->data);
        }
    }

    //----------------------------------------------------------------------
    public function encodePNG($intext, $outfile = false,$saveandprint=false)
    {
        try {

            ob_start();
            $tab = $this->encode($intext);
            $err = ob_get_contents();
            ob_end_clean();

            if ($err != '')
                QRtools::log($outfile, $err);

            $maxSize = (int)(QR_PNG_MAXIMUM_SIZE / (count($tab)+2*$this->margin));

            QRimage::png($tab, $outfile, min(max(1, $this->size), $maxSize), $this->margin,$saveandprint);

        } catch (Exception $e) {

            QRtools::log($outfile, $e->getMessage());

        }
    }
} ?>