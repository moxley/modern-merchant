<?php
/**
 * @package mvc
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class mvc_ResultsNav
{
    public $count;
    public $offset;
    
    // TODO: Delete mvc_ResultsNav::getNav()
    ///**
    //* Returns a description of the "paged results" navigation links of search 
    //* results.
    //*
    //* Describes the page number of the random-access links, offset, 
    //* "previous page" offset, and the "next page" offset.
    //*
    //* @param int $count The number of results 
    //* @param int $offset The offset of the current results
    //* @param int $max_results The maximum number of results displayed per page
    //* @param int $max_links The maximum number of random-access navigation links
    //* @param int $extra_params Additional parameters to add to every link
    //* @return array Special data structure
    //*/
    //function getNav(
    //    $count, $offset, $max_results, $max_links, $extra_params=null
    //)
    //{
    //    $output = array(
    //        'numbered' => array(),
    //        'previous' => null,
    //        'next' => null
    //    );
    //    if( $count == 0 ) return $output;
    //    if( $max_results < 1 ) {
    //        throw new mvc_BusinessException("max_results must be positive integer");
    //    }
    //    
    //    // Calculate $current_page_index
    //    $current_page_index = intval($offset / $max_results);
    //
    //    // Calculate $first_page_index
    //    $first_page_index = intval($current_page_index / $max_links) * $max_links;
    //    
    //    // Calculate $num_links
    //    if( 
    //        $count - ($first_page_index * $max_results) >=
    //        ($max_results * $max_links)
    //    )
    //    {
    //        $num_links = $max_links;
    //    }
    //    else
    //    {
    //        $num_links = ceil(($count - ($first_page_index * $max_results)) / 
    //            $max_results);
    //    }
    //    if( $num_links == 1 && $count < ($max_results*$max_links) ) return $output;
    //    
    //    for( $link_index= 0; $link_index < $num_links; $link_index++ )
    //    {
    //        $link_data = array();
    //        
    //        // Set the page number
    //        $link_data["page_number"] = $first_page_index + $link_index+ 1;
    //        
    //        // Set the link flag
    //        if( ($first_page_index + $link_index) == $current_page_index )
    //        {
    //            $link_data["current_page"] = true;
    //        }
    //        else
    //        {
    //            $link_data["current_page"] = false;
    //        }
    //        
    //        // Set the offset
    //        $t_offset = ($first_page_index + $link_index) * $max_results;
    //        $params = "offset=" . intval($t_offset);
    //        
    //        // Set the extra parameters
    //        if( $extra_params != null )
    //        {
    //            $params .= "&". mvc_ResultsNav::createParams($extra_params);
    //        }
    //        $link_data["params"] = $params;
    //        
    //        $output["numbered"][$link_index] = $link_data;
    //    }
    //
    //    // Set "previous page" details
    //    if( $current_page_index > 0 )
    //    {
    //        $link_data = array();
    //        
    //        // Get offset
    //        $t_offset = ($current_page_index - 1) * $max_results;
    //        $params = "offset=".intval($t_offset);
    //        
    //        // Set the extra parameters
    //        if( $extra_params != null )
    //        {
    //            $params .= "&" . mvc_ResultsNav::createParams($extra_params);
    //        }
    //        $link_data["params"] = $params;
    //
    //        $output["previous"] = $link_data;
    //    }
    //    
    //    // Set "next page" details
    //    if( ($current_page_index+1) * $max_results < $count )
    //    {
    //        $link_data = array();
    //        
    //        // Get offset
    //        $t_offset = ($current_page_index + 1) * $max_results;
    //        $params = "offset=".intval($t_offset);
    //        
    //        // Set the extra parameters
    //        if( $extra_params != null )
    //        {
    //            $params .= "&". mvc_ResultsNav::createParams($extra_params);
    //        }
    //        $link_data["params"] = $params;
    //
    //        $output["next"] = $link_data;
    //    }
    //    
    //    return $output;
    //}
    
    function createParams($paramHash)
    {
        $returnString = "";
        $i = 0;
        foreach( $paramHash as $key=>$value )
        {
            if( $i>0 ) $returnString .= "&";
            $returnString .= urlencode($key).'='.urlencode($value);
            $i++;
        }
        
        return $returnString;
    }
    
}
