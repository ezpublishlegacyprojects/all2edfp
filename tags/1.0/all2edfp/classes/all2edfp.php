<?php
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: all2edfp
// SOFTWARE RELEASE: 1.0
// COPYRIGHT NOTICE: Copyright (C) 2011 Norman Leunter <info@all2e.com> all2e GmbH
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: 
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
// ## END COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
//

/**
 * Class all2edfp implements Google DFP Ad Manager functions
 */
class all2edfp
{
    /**
    * Constructor
    */
    protected function __construct()
    {
    }

    /**
    * Builds the javascript Add Slot tag(s) based on input array
    *
    * @param array $slotarray persistant variable array
    */
    static function buildSlots( $slotarray, $publisherID )
    {
        $ret = "<script type='text/javascript'>\r\n";
        $slotlist = array();
        
        // analyse slot array
        if( $slotarray )
        {
	        foreach ($slotarray as $slot)
	        {        
	            if( isset($slotlist[$slot['name']]) )
	            {
	                $slotlist[$slot['name']]++;
	            }
	            else{
	                $slotlist[$slot['name']] = 1;
	            }
	        }
        }
        
        //generate slots
        foreach ($slotlist as $key => $value)
        {
            // slot appears more than once
            if ($value > 1)
            {
                $i = 1;
                while ($i <= $value)
                {
                	$ret .= "GA_googleAddSlot('".$publisherID."', '".$key."_".$i."');\r\n"; 
                	$i++;
                }
            }
            else{
                $ret .= "GA_googleAddSlot('".$publisherID."', '".$key."');\r\n";    
            }
        }
        
        $ret .= "</script>\r\n";
        
        return $ret;
    }
    
    /**
    * Builds the javascript Fill Slot tag(s) based on input array
    *
    * @param array $slotarray persistant variable array
    */
    static function buildFillSlots( $slotarray )
    {
        $ret = ''; 
        $slotlist = array();
        
        $ini = eZINI::instance('all2edfp.ini');
                
        // analyse slot array
        if( $slotarray )
        {
            foreach ($slotarray as $slot)
            {            
                if( isset($slotlist[$slot['name']]['count']) )
                {
                    $slotlist[$slot['name']]['count']++;
                }
                else{
                    $slotlist[$slot['name']]['count'] = 1;
                }
                $slotlist[$slot['name']]['offset'] = 1;
            }
            
            foreach ($slotarray as $slot)
            {
                $style = "";
                $styles = array();
                
                if ( $ini->hasVariable( 'Styles', $slot['name'] ) )
                {
                    $styles = $ini->variable( 'Styles',$slot['name']);
                    
                    foreach ($styles as $key => $value)
                    {
                        $style.= "$key: $value;";    
                    }
                }
                
                $ret .= "<div id='".$slot['div_id']."_hidden' class='banner-hidden' style='position:absolute;top:-1000px;left:-1000px; $style'>\r\n";
                $ret .= "<script type='text/javascript'>\r\n";
                
                // Ad Unit is used multimple times -> append number
                if( $slotlist[$slot['name']]['count'] > 1 )
                {
                    $ret .= "GA_googleFillSlot('".$slot['name']."_".$slotlist[$slot['name']]['offset']."');\r\n";
                }
                else 
                {
                    $ret .= "GA_googleFillSlot('".$slot['name']."');\r\n";    
                }
                $slotlist[$slot['name']]['offset']++;
                
                $ret .= "</script>\r\n";
                $ret .= "</div>\r\n";    
            }
        }
        return $ret;
    }

    /**
    * Builds the default filters for custom targeting
    *
    * @param array $filters
    */    
    static function buildFilters( $filters )
    {
        $ret = "<script type='text/javascript'>\r\n";
        
        foreach ($filters as $key => $value)
        {
            $ret .= "GA_googleAddAttr('".$key."', '".$value."');\r\n";
        }
        
        $ret .= "</script>\r\n";
        return $ret;
    }

    /*
    * Checks if the eZGlobalRequestURI is excluded via ini Settings excludePattern[]
    * 
    * @param array $excludePattern
    */    
    static function showAds($excludePattern)
    {
        $showads = true;
        foreach( $excludePattern as $pattern )
        {
            $hit = preg_match($pattern, $GLOBALS['eZGlobalRequestURI'] );
            if ($hit)
            {
                $showads = false;
                break;
            }
            
        }
        return $showads;
    }
}