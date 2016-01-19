<?php

/*==============================================================================
 * Mediawiki PHP extension for chess FEN diagrams rendering
 *
 * Copyright (C) 2007-2016  Michael Peeters <https://github.com/xeyownt>
 *
 * This file is part of the FenTT MediaWiki extension
 * <http://www.mediawiki.org/wiki/Extension:FenTT>.
 *
 * The FenTT MediaWiki extension is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License as
 * published by * the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * The FenTT MediaWiki extension is distributed in the hope that it will be
 * useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 *==============================================================================
 */

if (defined('MEDIAWIKI')) {
    $wgHooks['ParserFirstCallInit'][] = 'MWFenTT::onParserFirstCallInit';

    $wgResourceModules['ext.FenTT'] = array(
	    'localBasePath' => __DIR__,
	    'remoteExtPath' => 'FenTT',
	    'styles'        => 'FenTT.css',
	    'position'      => 'top',
    );

    $wgExtensionCredits['parserhook'][] = array(
        'name'        => 'FenTT',
        'version'     => '1.0.0',
        'author'      => 'Michael Peeters',
        'url'         => 'http://www.mediawiki.org/wiki/Extension:FenTT',
        'description' => 'Render high-quality chess diagrams in FEN notation using TrueType fonts and CSS style.'
    );

    class MWFenTT {
        private static $css_module_added = false;

	    static function onParserFirstCallInit( Parser $parser ) {
		    // Register parser handler for tag <fentt>
		    $parser->setHook( 'fentt', 'MWFenTT::renderFentt' );
	    }

        static function renderFentt( $input, array $args, Parser $parser, PPFrame $frame ) {
            if( ! self::$css_module_added ) {
                // Tell ResourceLoader that we need our css module
                $parser->getOutput()->addModuleStyles( 'ext.FenTT' );
                self::$css_module_added = true;
            }
            return FenTT::renderFentt($input,$args);
        }
    }
}

class FenBoard {
    const VALUE_BORDER_SOLID                       = "solid";
    const VALUE_BORDER_DOUBLE                      = "double";
    const VALUE_BORDER_SQUARE                      = "square";
    const VALUE_BORDER_ROUND                       = "round";
    const VALUE_BORDER_PAD                         = "pad";
    const VALUE_MODE_COLOR                         = "color";
    const VALUE_MODE_BW                            = "bw";
    const VALUE_CLASS_BW_TABLE                     = "bwfentt";
    const VALUE_CLASS_COLOR_TABLE                  = "fentt";
    const VALUE_CLASS_BW_SPAN_HL                   = "bwhl";
    const VALUE_CLASS_COLOR_SPAN_HL                = "hl";

    private static $is_decoded                     = false;
    private static $U_MFB                          = '\u00A0'; // Use '\u00A0' for blank (nb-sp)

    private static $FEN                            = 'KQRBNPkqrbnp.+x';  // Modified FEN notation (White pieces - Dark pieces - extra symbols)
    private static $COORD_COLS                     = 'abcdefgh';
    private static $COORD_ROWS                     = '8<br/>7<br/>6<br/>5<br/>4<br/>3<br/>2<br/>1<br/>';
    private static $FRAME_COLS                     = 'abcdefgh';
    private static $FRAME_ROWS                     = '12345678';

    private static $U_MERIDA_LIGHT                 = '\u2654\u2655\u2656\u2657\u2658\u2659\u265A\u265B\u265C\u265D\u265E\u265F\u00A0\u2022\u2715'; // Merida pieces on light square (White pieces - Black pieces - extra symbols)
    private static $U_MERIDA_DARK                  = '\uE154\uE155\uE156\uE157\uE158\uE159\uE15A\uE15B\uE15C\uE15D\uE15E\uE15F\uE100\uE122\uE115'; // Merida pieces on dark square (White pieces - Black pieces - extra symbols)
    private static $U_MERIDA_PIECE_BG              = '\uE254\uE255\uE256\uE257\uE258\uE259\uE254\uE255\uE256\uE257\uE258\uE259\u00A0\u00A0\u00A0'; //Merida Chess - pieces background

    private static $U_MERIDA_BOARD_SQUARE_ROW      = '\u00A0\uE200\u00A0\uE200\u00A0\uE200\u00A0\uE200\u00A0';   // light dark light dark light dark light dark light
    private static $U_MERIDA_FRAME_BLANK           = '\u00A0';
    private static $U_MERIDA_FRAME_PADDING         = '\u00A0\u00A0\u00A0\u00A0\u00A0\u00A0\u00A0\u00A0\u00A0\u00A0';
    private static $U_MERIDA_FRAME_COL_PADDING     = '\u00A0\u00A0\u00A0\u00A0\u00A0\u00A0\u00A0\u00A0\u00A0\u00A0';
    private static $U_MERIDA_FRAME_ROW_PADDING     = '\u00A0<br/>\u00A0<br/>\u00A0<br/>\u00A0<br/>\u00A0<br/>\u00A0<br/>\u00A0<br/>\u00A0<br/>';

    private static $U_MERIDA_FRAME_D_BOTTOM        = '\uE336\uE336\uE336\uE336\uE336\uE336\uE336\uE336';
    private static $U_MERIDA_FRAME_D_BOTTOM_COORD  = '\uE348\uE349\uE34A\uE34B\uE34C\uE34D\uE34E\uE34F';
    private static $U_MERIDA_FRAME_D_LEFT          = '\uE333\uE333\uE333\uE333\uE333\uE333\uE333\uE333';
    private static $U_MERIDA_FRAME_D_LEFT_COORD    = '\uE340\uE341\uE342\uE343\uE344\uE345\uE346\uE347';
    private static $U_MERIDA_FRAME_D_RIGHT         = '\uE334\uE334\uE334\uE334\uE334\uE334\uE334\uE334';
    private static $U_MERIDA_FRAME_D_RIGHT_COORD   = '\uE350\uE351\uE352\uE353\uE354\uE355\uE356\uE357';
    private static $U_MERIDA_FRAME_D_ROUND_CORNER  = '\uE338\uE339\uE33A\uE33B';
    private static $U_MERIDA_FRAME_D_SQUARE_CORNER = '\uE330\uE332\uE335\uE337';
    private static $U_MERIDA_FRAME_D_TOP           = '\uE331\uE331\uE331\uE331\uE331\uE331\uE331\uE331';
    private static $U_MERIDA_FRAME_D_TOP_COORD     = '\uE358\uE359\uE35A\uE35B\uE35C\uE35D\uE35E\uE35F';

    private static $U_MERIDA_FRAME_S_BOTTOM        = '\uE306\uE306\uE306\uE306\uE306\uE306\uE306\uE306';
    private static $U_MERIDA_FRAME_S_BOTTOM_COORD  = '\uE318\uE319\uE31A\uE31B\uE31C\uE31D\uE31E\uE31F';
    private static $U_MERIDA_FRAME_S_LEFT          = '\uE303\uE303\uE303\uE303\uE303\uE303\uE303\uE303';
    private static $U_MERIDA_FRAME_S_LEFT_COORD    = '\uE310\uE311\uE312\uE313\uE314\uE315\uE316\uE317';
    private static $U_MERIDA_FRAME_S_RIGHT         = '\uE304\uE304\uE304\uE304\uE304\uE304\uE304\uE304';
    private static $U_MERIDA_FRAME_S_RIGHT_COORD   = '\uE320\uE321\uE322\uE323\uE324\uE325\uE326\uE327';
    private static $U_MERIDA_FRAME_S_ROUND_CORNER  = '\uE308\uE309\uE30A\uE30B';
    private static $U_MERIDA_FRAME_S_SQUARE_CORNER = '\uE300\uE302\uE305\uE307';
    private static $U_MERIDA_FRAME_S_TOP           = '\uE301\uE301\uE301\uE301\uE301\uE301\uE301\uE301';
    private static $U_MERIDA_FRAME_S_TOP_COORD     = '\uE328\uE329\uE32A\uE32B\uE32C\uE32D\uE32E\uE32F';

    private $fencode;
    private $border;
    private $mode;
    private $cstyle;
    private $cclass;
    private $parsed_ok;
    private $isBorderShownTop                      = false;
    private $isBorderShownBottom                   = false;
    private $isBorderShownLeft                     = false;
    private $isBorderShownRight                    = false;
    private $isCoordShownTop                       = false;
    private $isCoordShownBottom                    = false;
    private $isCoordShownLeft                      = false;
    private $isCoordShownRight                     = false;
    private $isBorderStyleSimple                   = true;
    private $isCornerStyleRound                    = true;
    private $isCoordPadded                         = false;
    private $coordOriginCol                        = 0;
    private $coordOriginRow                        = 0;
    private $rowcount                              = 0;
    private $colcount                              = 0;

    function __construct($fencode,$border="",$mode="",$cstyle="",$cclass="") {
        $this->fencode=$fencode;
        $this->border=explode(" ",$border);
        $this->mode=$mode;
        $this->cstyle=$cstyle;
        $this->cclass=$cclass;

        if(! self::$is_decoded) {
            self::$is_decoded = true;

            mb_internal_encoding("UTF-8");
            self::$U_MFB                          = json_decode('"'.self::$U_MFB.'"');
            self::$U_MERIDA_LIGHT                 = json_decode('"'.self::$U_MERIDA_LIGHT.'"');
            self::$U_MERIDA_DARK                  = json_decode('"'.self::$U_MERIDA_DARK.'"');
            self::$U_MERIDA_PIECE_BG              = json_decode('"'.self::$U_MERIDA_PIECE_BG.'"');
            self::$U_MERIDA_BOARD_SQUARE_ROW      = json_decode('"'.self::$U_MERIDA_BOARD_SQUARE_ROW.'"');
            self::$U_MERIDA_FRAME_BLANK           = json_decode('"'.self::$U_MERIDA_FRAME_BLANK.'"');
            self::$U_MERIDA_FRAME_PADDING         = json_decode('"'.self::$U_MERIDA_FRAME_PADDING.'"');
            self::$U_MERIDA_FRAME_COL_PADDING     = json_decode('"'.self::$U_MERIDA_FRAME_COL_PADDING.'"');
            self::$U_MERIDA_FRAME_ROW_PADDING     = json_decode('"'.self::$U_MERIDA_FRAME_ROW_PADDING.'"');
            self::$U_MERIDA_FRAME_D_BOTTOM        = json_decode('"'.self::$U_MERIDA_FRAME_D_BOTTOM.'"');
            self::$U_MERIDA_FRAME_D_BOTTOM_COORD  = json_decode('"'.self::$U_MERIDA_FRAME_D_BOTTOM_COORD.'"');
            self::$U_MERIDA_FRAME_D_LEFT          = json_decode('"'.self::$U_MERIDA_FRAME_D_LEFT.'"');
            self::$U_MERIDA_FRAME_D_LEFT_COORD    = json_decode('"'.self::$U_MERIDA_FRAME_D_LEFT_COORD.'"');
            self::$U_MERIDA_FRAME_D_RIGHT         = json_decode('"'.self::$U_MERIDA_FRAME_D_RIGHT.'"');
            self::$U_MERIDA_FRAME_D_RIGHT_COORD   = json_decode('"'.self::$U_MERIDA_FRAME_D_RIGHT_COORD.'"');
            self::$U_MERIDA_FRAME_D_ROUND_CORNER  = json_decode('"'.self::$U_MERIDA_FRAME_D_ROUND_CORNER.'"');
            self::$U_MERIDA_FRAME_D_SQUARE_CORNER = json_decode('"'.self::$U_MERIDA_FRAME_D_SQUARE_CORNER.'"');
            self::$U_MERIDA_FRAME_D_TOP           = json_decode('"'.self::$U_MERIDA_FRAME_D_TOP.'"');
            self::$U_MERIDA_FRAME_D_TOP_COORD     = json_decode('"'.self::$U_MERIDA_FRAME_D_TOP_COORD.'"');
            self::$U_MERIDA_FRAME_S_BOTTOM        = json_decode('"'.self::$U_MERIDA_FRAME_S_BOTTOM.'"');
            self::$U_MERIDA_FRAME_S_BOTTOM_COORD  = json_decode('"'.self::$U_MERIDA_FRAME_S_BOTTOM_COORD.'"');
            self::$U_MERIDA_FRAME_S_LEFT          = json_decode('"'.self::$U_MERIDA_FRAME_S_LEFT.'"');
            self::$U_MERIDA_FRAME_S_LEFT_COORD    = json_decode('"'.self::$U_MERIDA_FRAME_S_LEFT_COORD.'"');
            self::$U_MERIDA_FRAME_S_RIGHT         = json_decode('"'.self::$U_MERIDA_FRAME_S_RIGHT.'"');
            self::$U_MERIDA_FRAME_S_RIGHT_COORD   = json_decode('"'.self::$U_MERIDA_FRAME_S_RIGHT_COORD.'"');
            self::$U_MERIDA_FRAME_S_ROUND_CORNER  = json_decode('"'.self::$U_MERIDA_FRAME_S_ROUND_CORNER.'"');
            self::$U_MERIDA_FRAME_S_SQUARE_CORNER = json_decode('"'.self::$U_MERIDA_FRAME_S_SQUARE_CORNER.'"');
            self::$U_MERIDA_FRAME_S_TOP           = json_decode('"'.self::$U_MERIDA_FRAME_S_TOP.'"');
            self::$U_MERIDA_FRAME_S_TOP_COORD     = json_decode('"'.self::$U_MERIDA_FRAME_S_TOP_COORD.'"');
        }

        // Parse FEN code and border attribute
        $this->parsed_ok = $this->parseFen() && $this->parseBorder();

        // print "DEBUG: __construct($this->fencode," . implode(" ",$this->border) . ",$this->mode,$this->cstyle,$this->cclass)\n";
    }

	function border_isNSEO($nseo)
	{
		if(strlen($nseo) > 4) {
            return false;
        }
		for($x=0; $x<strlen($nseo); $x++)
		{
            if(!strchr("nseo",$nseo[$x])) {
                return false;
            }
		}
		return true;
	}

	private function border_isCoordinate($coord)
	{
		if (strlen($coord) != 2) {
            return false;
        }
		$coordOriginCol = ord($coord[0]) - 0x60;
		$coordOriginRow = ord($coord[1]) - 0x30;
		return ($coordOriginCol>=1) && ($coordOriginCol<=8) && ($coordOriginCol>=1) && ($coordOriginRow<=8);
	}

    private function parseFen() {
        // Remove carriage returns, tabs, white spaces
        $this->fencode=str_replace(array("\r","\n","\t"," "),"",$this->fencode);

        // Replace 1,2...,8 by multiple occurences of '.' (empty cells)
        $this->fencode=str_replace(
            array("1","2","3","4","5","6","7","8"),
            array(".","..","...","....",".....","......",".......","........"),
            $this->fencode);

        // Get "flat" FEN code *array* (without highlighting, etc), and get row/col count
        $flatfencode=str_replace(array("(",")"),"",$this->fencode);
        $flatfencode=explode("/",$flatfencode);
        $this->colcount = strlen($flatfencode[0]);
        $this->rowcount = count($flatfencode);

        // Check that each row has the same number of columns
        $check_ok=($this->rowcount <= 8) && ($this->colcount <= 8);
        foreach($flatfencode as $row) {
            $check_ok = $check_ok && (strlen($row) == $this->colcount);
        }

        // Convert FEN code string into FEN code *array*
        $this->fencode=explode("/",$this->fencode);

        return $check_ok;
    }

    private function parseBorder() {
        $isCoordShown = false;

        foreach($this->border as $attr) {
    		if($attr == self::VALUE_BORDER_SOLID) {
				$this->isBorderShownTop    = true;
				$this->isBorderShownBottom = true;
				$this->isBorderShownLeft   = true;
				$this->isBorderShownRight  = true;
				$this->isBorderStyleSimple = true;
    		}
    		elseif($attr == self::VALUE_BORDER_DOUBLE) {
				$this->isBorderShownTop    = true;
				$this->isBorderShownBottom = true;
				$this->isBorderShownLeft   = true;
				$this->isBorderShownRight  = true;
				$this->isBorderStyleSimple = false;
    		}
    		elseif($this->border_isCoordinate($attr)) {
    			$isCoordShown = true;
    			$this->coordOriginCol = ord($attr[0]) - 0x61;
    			$this->coordOriginRow = ord($attr[1]) - 0x31;
				if(($this->coordOriginCol + $this->colcount > 8) || ($this->coordOriginRow + $this->rowcount > 8))
				{
					$this->coordOriginCol = 0;
					$this->coordOriginRow = 0;
				}
				$this->isCoordShownLeft   = $this->isBorderShownLeft   = ($this->coordOriginCol                   == 0);
				$this->isCoordShownRight  = $this->isBorderShownRight  = ($this->coordOriginCol + $this->colcount == 8);
				$this->isCoordShownBottom = $this->isBorderShownBottom = ($this->coordOriginRow                   == 0);
				$this->isCoordShownTop    = $this->isBorderShownTop    = ($this->coordOriginRow + $this->rowcount == 8);
    		}
    		elseif($attr == self::VALUE_BORDER_SQUARE) {
				$this->isBorderShownTop    = true;
				$this->isBorderShownBottom = true;
				$this->isBorderShownLeft   = true;
				$this->isBorderShownRight  = true;
				$this->isCornerStyleRound  = false;
    		}
    		elseif($attr == self::VALUE_BORDER_ROUND) {
				$this->isBorderShownTop    = true;
				$this->isBorderShownBottom = true;
				$this->isBorderShownLeft   = true;
				$this->isBorderShownRight  = true;
				$this->isCornerStyleRound  = true;
    		}
	    	elseif($attr == self::VALUE_BORDER_PAD) {
	    		$this->isCoordPadded = true;
	    	}
	    	elseif($this->border_isNSEO($attr)) {
				$this->isCoordShownTop    = false;
				$this->isCoordShownBottom = false;
				$this->isCoordShownLeft   = false;
				$this->isCoordShownRight  = false;
				for($x=0; $x<strlen($attr); $x++) {
					switch ($attr[$x]) {
						case "n": $this->isCoordShownTop    = true; break;
						case "s": $this->isCoordShownBottom = true; break;
						case "e": $this->isCoordShownRight  = true; break;
						case "o": $this->isCoordShownLeft   = true; break;
					}
				}
				if(!$isCoordShown) {
					// Border can't be overridden if board origin is already specified
					$this->isBorderShownLeft   = $this->isCoordShownLeft;
					$this->isBorderShownRight  = $this->isCoordShownRight;
					$this->isBorderShownBottom = $this->isCoordShownBottom;
					$this->isBorderShownTop    = $this->isCoordShownTop;
				}
			}
    	}

    	//Coordinates shown only if isCoordShown is true
		$this->isCoordShownLeft   = $isCoordShown && $this->isCoordShownLeft;
		$this->isCoordShownRight  = $isCoordShown && $this->isCoordShownRight;
		$this->isCoordShownBottom = $isCoordShown && $this->isCoordShownBottom;
		$this->isCoordShownTop    = $isCoordShown && $this->isCoordShownTop;

        return true;
    }

    private function generateColorHTML() {
        if(! $this->parsed_ok) {
			return '<span style="color:red; font-weight: bold; font-family:monospace;">PARSE ERROR</span>';
        }

		if( ($this->colcount == 0) || ($this->rowcount == 0) ) {
			return "";			//empty chess tag
		}

	    $generatedHTML="";
		$this->cclass = self::VALUE_CLASS_COLOR_TABLE . ($this->cclass == "" ? "" : " " . $this->cclass);

    	/* ----- Chess table ---------------------------------------------------------------------------------------------------- */
    	$generatedHTML	.=	'<table class="' . $this->cclass . '"'
    					. 	($this->cstyle != "" ? ' style="'.$this->cstyle.'"' : "")
    					. 	'>';

    	/* ----- Top cols ------------------------------------------------------------------------------------------------------- */

	    if($this->isCoordShownTop) {
	    	$generatedHTML.='<tr>';
	    	$generatedHTML.=($this->isCoordShownLeft || $this->isCoordPadded ? "<td>&nbsp;</td>" : "") .
	    	            '<td class="cols">' . substr(self::$COORD_COLS,$this->coordOriginCol,$this->colcount) . '</td>' .
	    	            ($this->isCoordShownRight || $this->isCoordPadded ? "<td>&nbsp;</td>" : "");
	    	$generatedHTML.='</tr>';
	    }
		elseif($this->isCoordPadded) {
	    	$generatedHTML.='<tr>';
	    	$generatedHTML.=($this->isCoordShownLeft || $this->isCoordPadded ? "<td>&nbsp;</td>" : "") .
	    	            '<td class="cols">' . mb_substr(self::$U_MERIDA_FRAME_COL_PADDING,0,$this->colcount,"UTF-8") . '</td>' .
	    	            ($this->isCoordShownRight || $this->isCoordPadded ? "<td>&nbsp;</td>" : "");
	    	$generatedHTML.='</tr>';
		}
    	/* ----- Left rows ------------------------------------------------------------------------------------------------------ */

        $generatedHTML.='<tr>';

	    if($this->isCoordShownLeft) {
	    	$generatedHTML.='<td class="rows">';
	    	$generatedHTML.=substr(self::$COORD_ROWS,(8-$this->coordOriginRow-$this->rowcount)*6,$this->rowcount*6);
	    	$generatedHTML.='</td>';
	    }
		elseif($this->isCoordPadded) {
	    	$generatedHTML.='<td class="rows">';
			$generatedHTML.=mb_substr(self::$U_MERIDA_FRAME_ROW_PADDING,0,$this->rowcount*6,"UTF-8");
	    	$generatedHTML.='</td>';
		}

    	/* ----- Board ---------------------------------------------------------------------------------------------------------- */

    	$generatedSq = "";
    	$generatedBg = "";
    	$generatedFg = "";

	    $isTopLeftSquareLight = ($this->coordOriginRow . $this->coordOriginCol . $this->rowcount) & 1;		/* 0 light, 1 dark*/
	    for($row=0; $row<$this->rowcount; $row++) {
		    $isALightSquare = ($row + $isTopLeftSquareLight) & 1 ? false : true;
		    $inputRow       = $this->fencode[$row];
		    $outputSqRow    = "";
		    $outputBgRow    = "";
		    $outputFgRow    = "";
		    $isHLOn         = false;

	        for($col=0; $col<strlen($inputRow); $col++) {
	    		$cell=$inputRow[$col];

        		if($cell == "(") {
                    if(!$isHLOn) {
                        $outputSqRow .= '<span class="'.self::VALUE_CLASS_COLOR_SPAN_HL.'">';
                    }
                    $isHLOn = true;
                }
	        	elseif($cell == ")") {
                    if($isHLOn) {
                        $outputSqRow .= '</span>';
                    }
                    $isHLOn = false;
                }
		        else {
					$idx=strpos(self::$FEN,$cell);
					if($idx!==false) {
						$outputFgRow .= mb_substr(self::$U_MERIDA_LIGHT,$idx,1,"UTF-8");
						$outputBgRow .= mb_substr(self::$U_MERIDA_PIECE_BG,$idx,1,"UTF-8");
					}
					$outputSqRow .= $isALightSquare ? mb_substr(self::$U_MERIDA_BOARD_SQUARE_ROW,0,1,"UTF-8") : mb_substr(self::$U_MERIDA_BOARD_SQUARE_ROW,1,1,"UTF-8");
		        	$isALightSquare = !$isALightSquare;
	        	}
			}
		    $generatedSq.=$outputSqRow . ($isHLOn ? '</span>' : "") . "<br/>";
		    $generatedBg.=$outputBgRow . "<br/>";
		    $generatedFg.=$outputFgRow . "<br/>";
		}

		$boardStyle=($this->isBorderShownTop ? "" : "border-top:none !important;")
						.($this->isBorderShownBottom ? "" : "border-bottom:none !important;")
						.($this->isBorderShownLeft ? "" : "border-left:none !important;")
						.($this->isBorderShownRight ? "" : "border-right:none !important;");

    	$generatedHTML	.=	'<td class="board" style="width: '.$this->colcount.'em;'.$boardStyle.'">'
    					.	'<div class="board">'
    					.	'<div class="sq">'.$generatedSq.'</div>'
    					.	'<div class="pcbg">'.$generatedBg.'</div>'
    					.	'<div class="pcfg">'.$generatedFg.'</div>'
    					.	'</div></td>';

    	/* ----- Right Rows ----------------------------------------------------------------------------------------------------- */

	    if($this->isCoordShownRight)
	    {
	    	$generatedHTML.='<td class="rows">';
	    	$generatedHTML.=substr(self::$COORD_ROWS,(8-$this->coordOriginRow-$this->rowcount)*6,$this->rowcount*6);
	    	$generatedHTML.='</td>';
	    }
		else if($this->isCoordPadded)
		{
	    	$generatedHTML.='<td class="rows">';
			$generatedHTML.=mb_substr(self::$U_MERIDA_FRAME_ROW_PADDING,0,$this->rowcount*6,"UTF-8");
	    	$generatedHTML.='</td>';
		}

    	$generatedHTML.='</tr>';

    	/* ----- Bottom Cols --- ------------------------------------------------------------------------------------------------ */

	    if($this->isCoordShownBottom)
	    {
	    	$generatedHTML.='<tr>';
	    	$generatedHTML.=($this->isCoordShownLeft || $this->isCoordPadded ? "<td>&nbsp;</td>" : "") .
	    	            '<td class="cols">' . substr(self::$COORD_COLS,$this->coordOriginCol,$this->colcount) . '</td>' .
	    	            ($this->isCoordShownRight || $this->isCoordPadded ? "<td>&nbsp;</td>" : "");
	    	$generatedHTML.='</tr>';
	    }
		else if($this->isCoordPadded)
		{
	    	$generatedHTML.='<tr>';
	    	$generatedHTML.=($this->isCoordShownLeft || $this->isCoordPadded ? "<td>&nbsp;</td>" : "") .
	    	            '<td class="cols">' . mb_substr(self::$U_MERIDA_FRAME_COL_PADDING,0,$this->colcount,"UTF-8") . '</td>' .
	    	            ($this->isCoordShownRight || $this->isCoordPadded ? "<td>&nbsp;</td>" : "");
	    	$generatedHTML.='</tr>';
		}

    	$generatedHTML.='</table>';

	    return $generatedHTML;
    }

	private function generateBWHTML() {
        if(! $this->parsed_ok) {
			return '<span style="color:red; font-weight: bold; font-family:monospace;">PARSE ERROR</span>';
        }

		if( ($this->colcount == 0) || ($this->rowcount == 0) ) {
			return "";			//empty chess tag
		}

		if($this->isBorderStyleSimple) {
			$boardTop = $this->isCoordShownTop
							? ($this->isBorderShownTop ? self::$U_MERIDA_FRAME_S_TOP_COORD : self::$FRAME_COLS)
							: ($this->isBorderShownTop ? self::$U_MERIDA_FRAME_S_TOP : self::$U_MERIDA_FRAME_PADDING);
			$boardRight = $this->isCoordShownRight
							? ($this->isBorderShownRight ? self::$U_MERIDA_FRAME_S_RIGHT_COORD : self::$FRAME_ROWS)
							: ($this->isBorderShownRight ? self::$U_MERIDA_FRAME_S_RIGHT : self::$U_MERIDA_FRAME_PADDING);
			$boardBottom = $this->isCoordShownBottom
							? ($this->isBorderShownBottom ? self::$U_MERIDA_FRAME_S_BOTTOM_COORD : self::$FRAME_COLS)
							: ($this->isBorderShownBottom ? self::$U_MERIDA_FRAME_S_BOTTOM : self::$U_MERIDA_FRAME_PADDING);
			$boardLeft = $this->isCoordShownLeft
							? ($this->isBorderShownLeft ? self::$U_MERIDA_FRAME_S_LEFT_COORD : self::$FRAME_ROWS)
							: ($this->isBorderShownLeft ? self::$U_MERIDA_FRAME_S_LEFT : self::$U_MERIDA_FRAME_PADDING);
			$boardCorner = $this->isCornerStyleRound ? self::$U_MERIDA_FRAME_S_ROUND_CORNER : self::$U_MERIDA_FRAME_S_SQUARE_CORNER;
		}
		else {
			$boardTop = $this->isCoordShownTop
							? ($this->isBorderShownTop ? self::$U_MERIDA_FRAME_D_TOP_COORD : self::$FRAME_COLS)
							: ($this->isBorderShownTop ? self::$U_MERIDA_FRAME_D_TOP : self::$U_MERIDA_FRAME_PADDING);
			$boardRight = $this->isCoordShownRight
							? ($this->isBorderShownRight ? self::$U_MERIDA_FRAME_D_RIGHT_COORD : self::$FRAME_ROWS)
							: ($this->isBorderShownRight ? self::$U_MERIDA_FRAME_D_RIGHT : self::$U_MERIDA_FRAME_PADDING);
			$boardBottom = $this->isCoordShownBottom
							? ($this->isBorderShownBottom ? self::$U_MERIDA_FRAME_D_BOTTOM_COORD : self::$FRAME_COLS)
							: ($this->isBorderShownBottom ? self::$U_MERIDA_FRAME_D_BOTTOM : self::$U_MERIDA_FRAME_PADDING);
			$boardLeft = $this->isCoordShownLeft
							? ($this->isBorderShownLeft ? self::$U_MERIDA_FRAME_D_LEFT_COORD : self::$FRAME_ROWS)
							: ($this->isBorderShownLeft ? self::$U_MERIDA_FRAME_D_LEFT : self::$U_MERIDA_FRAME_PADDING);
			$boardCorner = $this->isCornerStyleRound ? self::$U_MERIDA_FRAME_D_ROUND_CORNER : self::$U_MERIDA_FRAME_D_SQUARE_CORNER;
		}

	    $generatedHTML="";
		$divWidth =	(($this->isCoordShownLeft || $this->isBorderShownLeft || $this->isCoordPadded) ? 1 : 0)
	                 + $this->colcount
	                 +(($this->isCoordShownRight || $this->isBorderShownRight || $this->isCoordPadded) ? 1 : 0);

		$this->cclass = self::VALUE_CLASS_BW_TABLE . ($this->cclass == "" ? "" : " " . $this->cclass);

    	/* ----- Chess table ---------------------------------------------------------------------------------------------------- */
    	$generatedHTML.='<table class="' . $this->cclass . '"' . ($this->cstyle != "" ? ' style="'.$this->cstyle.'"' : "") . '>' .'<tr><td>';

	    if($this->isCoordShownTop || $this->isBorderShownTop || $this->isCoordPadded) {
	    	$generatedHTML.= ($this->isBorderShownLeft && $this->isBorderShownTop
	    					? mb_substr($boardCorner,0,1)
	    					: ($this->isBorderShownLeft || $this->isCoordShownLeft || $this->isCoordPadded ? self::$U_MERIDA_FRAME_BLANK : ""))
	    	  	        .	mb_substr($boardTop,$this->coordOriginCol,$this->colcount)
						.	($this->isBorderShownRight && $this->isBorderShownTop
	    					? mb_substr($boardCorner,1,1)
	    	  	        	: ($this->isBorderShownRight || $this->isCoordShownRight || $this->isCoordPadded ? self::$U_MERIDA_FRAME_BLANK : ""))
	    	            .	"<br/>";
	    }

	    $isTopLeftSquareLight = ($this->coordOriginRow . $this->coordOriginCol . $this->rowcount) & 1;		/* 0 light, 1 dark*/
	    for($row=0; $row<$this->rowcount; $row++) {
		    $isALightSquare = ($row+$isTopLeftSquareLight)&1 ? false : true;
		    $inputRow       = $this->fencode[$row];
		    $outputRow      = "";
		    $isHLOn         = false;

	        for($col=0; $col<strlen($inputRow); $col++) {
	    		$cell=mb_substr($inputRow,$col,1);

        		if($cell == "(") {
                    if(!$isHLOn) {
                        $outputRow .= '<span class="'.self::VALUE_CLASS_BW_SPAN_HL.'">';
                    }
                    $isHLOn = true;
                }
	        	elseif($cell == ")") {
                    if($isHLOn) {
                        $outputRow .= '</span>';
                    }
                    $isHLOn = false;
                }
		        else {
					$idx=strpos(self::$FEN,$cell);
					if($idx!==false) {
						$outputRow .= $isALightSquare ? mb_substr(self::$U_MERIDA_LIGHT,$idx,1) : mb_substr(self::$U_MERIDA_DARK,$idx,1);
					}
		        	$isALightSquare = !$isALightSquare;
	        	}
			}
	    	$generatedHTML	.=	(($this->isCoordShownLeft || $this->isBorderShownLeft || $this->isCoordPadded) ? mb_substr($boardLeft,$this->coordOriginRow+$this->rowcount-1-$row,1) : "")
	    					.	$outputRow
						    .	($isHLOn ? '</span>' : "")
	    					.	(($this->isCoordShownRight || $this->isBorderShownRight || $this->isCoordPadded) ? mb_substr($boardRight,$this->coordOriginRow+$this->rowcount-1-$row,1) : "")
						    .	"<br/>";
		}

	    if($this->isCoordShownBottom || $this->isBorderShownBottom || $this->isCoordPadded) {
	    	$generatedHTML.=	($this->isBorderShownLeft && $this->isBorderShownBottom
	    					? mb_substr($boardCorner,2,1)
	    					: ($this->isBorderShownLeft || $this->isCoordShownLeft || $this->isCoordPadded ? self::$U_MERIDA_FRAME_BLANK : ""))
	    	  	        .	mb_substr($boardBottom,$this->coordOriginCol,$this->colcount)
						.	($this->isBorderShownRight && $this->isBorderShownBottom
	    	  	        	? mb_substr($boardCorner,3,1)
	    	  	        	: ($this->isBorderShownRight || $this->isCoordShownRight || $this->isCoordPadded ? self::$U_MERIDA_FRAME_BLANK : ""))
	    	            .	"<br/>";
	    }

    	$generatedHTML.='<div class="nowrap" style="width:'.$divWidth.'em"></div></td></tr></table>';

	    return $generatedHTML;
	}

    public function GetHTML() {
        switch($this->mode) {
        case self::VALUE_MODE_COLOR:
            // return "<c:chess>" . $this->generateColorHTML() . "</c:chess>";
            return $this->generateColorHTML();
            break;
        case self::VALUE_MODE_BW:
            // return "<c:chess>" . $this->generateBWHTML() . "</c:chess>";
            return $this->generateBWHTML();
            break;
        default:
			return "<span style=\"color:red; font-weight: bold; font-family:monospace;\">Unknown mode $this->mode.</span>";
        }
    }

    public function isempty() {
        return ($this->rowcount == 0) || ($this->colcount == 0);
    }
}

class FenTT {
    const BORDER = "border";  // "solid", "double", "square", "round", "pad", origin, nseo
    const MODE   = "mode";    // "color", "bw"
    const CSTYLE  = "cstyle";
    const CCLASS  = "cclass";
    private static $mode_default = FenBoard::VALUE_MODE_COLOR;

	// Render <fentt>
	static public function renderFentt( $input, array $args ) {
	    $border = isset($args[self::BORDER]) ? $args[self::BORDER] : "";
	    $mode = isset($args[self::MODE]) ? $args[self::MODE] : self::$mode_default;
	    $cstyle = isset($args[self::CSTYLE]) ? $args[self::CSTYLE] : "";
	    $cclass = isset($args[self::CCLASS]) ? $args[self::CCLASS] : "";
        $fencode = $input;

        // Generate html for current tag
        $board = new FenBoard($fencode,$border,$mode,$cstyle,$cclass);

        if($board->isempty()) {
            self::$mode_default = $mode;             // Empty tag - store current mode as default
        }

        return $board->GetHTML();
	}
}

?>
