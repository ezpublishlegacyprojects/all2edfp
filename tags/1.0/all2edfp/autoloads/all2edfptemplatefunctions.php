<?php
/**
 * all2eDFPTemplateFunctions implements Google DFP Ad Manager functions for eZ publish
 */

class all2eDFPTemplateFunctions
{
    /*!
     Constructor
    */
    function all2eDFPTemplateFunctions()
    {
    }
    
    function operatorList()
    {
        return array( 'all2edfp_require','all2edfp_addslots','all2edfp_fillslots','all2edfp_filters','all2edfp_showads');
    }

    function namedParameterPerOperator()
    {
        return true;
    }
    
    function namedParameterList()
    {
        static $def = null;
        if ( $def === null )
        {
            $def = array( 'all2edfp_require' => array( 	'name' => array( 'type' => 'string',
                                                  		'required' => true,
                                                  		'default' => ''),
                                               			'div_id' => array( 'type' => 'string',
                                                  		'required' => true,
                                                  		'default' => '' ))
                        );

            $def['all2edfp_addslots'] = array();
            $def['all2edfp_fillslots'] = array();
            $def['all2edfp_filters'] = array();
            $def['all2edfp_showads'] = array();

        }
        return $def;
    }    
    
    /**
    * Template operator function for all functions defined on this class
    *
    * @param eZTemplate $tpl
    * @param string $operatorName
    * @param array $operatorParameters
    * @param string $rootNamespace
    * @param string $currentNamespace
    * @param null|mixed $operatorValue
    * @param array $namedParameters
    */
    function modify( eZTemplate $tpl, $operatorName, array $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, array $namedParameters )
    {
        $ret = '';
        $ini = eZINI::instance('all2edfp.ini');
        $publisherID = $ini->variable( 'DFPSettings','publisherID' );
        
                    
        switch ( $operatorName )
        {
            // stores the used Ad unit  
            case 'all2edfp_require':
            {
                $value = array( array ( 'name' => $namedParameters['name'],
                                		'div_id' => $namedParameters['div_id']
                                        ));
                
                self::setPersistentArray( 'dfp_ad_units',  $value , $tpl, true, false );
            } break;
            // returns the GA_googleAddSlot html snippets
            case 'all2edfp_addslots':
            {
                $ret = all2edfp::buildSlots( self::getPersistentVariable('dfp_ad_units'), $publisherID );
            } break;
            // returns the GA_googleFillSlot's html snippets
            case 'all2edfp_fillslots':
            {
                $ret = all2edfp::buildFillSlots( self::getPersistentVariable('dfp_ad_units') );
            } break;
            // returns the GA_googleAddAttr filter html snippet
            case 'all2edfp_filters':
            {
                 $ret = self::getFilterArray($tpl);
            } break;
            case 'all2edfp_showads':
            {
                 $excludePattern = $ini->variable( 'DFPSettings','excludePattern' );
                 $ret = all2edfp::showAds($excludePattern);
            } break;            
        }
        $operatorValue = $ret;
    }

    /**
    * Function for setting values to deal with persistent_variable either from
    * template or internally on {@link self::$persistentVariable}
    *
    * @internal
    * @param string $key Key to store values on
    * @param string|array $value Value(s) to store
    * @param eZTemplate $tpl Template object to get values from
    * @param bool $append Append or prepend value?
    * @param bool $arrayUnique Make sure array is unique to remove duplicates
    * @param bool $returnArrayDiff Return diff against existing values instead of resulting array
    * @param bool $override Override/Wipe out values or merge?
    * @return array
    */
    static public function setPersistentArray( $key, $value, eZTemplate $tpl, $append = true, $arrayUnique = false, $returnArrayDiff = false, $override = false )
    {
        $isPageLayout = false;
        $persistentVariable = array();
        if ( $tpl->hasVariable('module_result') )
        {
            $isPageLayout = true;
            $moduleResult = $tpl->variable('module_result');
        }

        if ( isset( $moduleResult['content_info']['persistent_variable'] ) )
        {
            $persistentVariable = $moduleResult['content_info']['persistent_variable'];
        }
        else if ( !$isPageLayout && $tpl->hasVariable('persistent_variable') )
        {
           $persistentVariable = $tpl->variable('persistent_variable');
        }
        else if ( self::$persistentVariable !== null )
        {
            $persistentVariable = self::$persistentVariable;
        }

        if ( $persistentVariable === false || !is_array( $persistentVariable ) )
        {
            // Give warning if value is not array as we depend on it
            if ( !$isPageLayout && $persistentVariable )
            {
                eZDebug::writeError( 'persistent_variable was not an array and where cleared!', __METHOD__ );
            }
            $persistentVariable = array();
        }

        // make a copy in case we need to diff value in the end
        $persistentVariableCopy = $persistentVariable;

        if ( !$override )
        {
            if ( isset( $persistentVariable[ $key ] ) && is_array( $persistentVariable[ $key ] ) )
            {
                if ( is_array( $value ) )
                {
                    if ( $append )
                        $persistentVariable[ $key ] = array_merge( $persistentVariable[ $key ], $value );
                    else
                        $persistentVariable[ $key ] = array_merge( $value, $persistentVariable[ $key ] );
                }
                else if ( $append )
                    $persistentVariable[ $key ][] = $value;
                else
                    $persistentVariable[ $key ] = array_merge( array( $value ), $persistentVariable[ $key ] );
            }
            else
            {
                if ( is_array( $value ) )
                    $persistentVariable[ $key ] = $value;
                else
                    $persistentVariable[ $key ] = array( $value );
            }
        }
        else
        {
            $persistentVariable[ $key ] = $value;
        }

        if ( $arrayUnique && isset( $persistentVariable[$key][1] ) )
        {
            $persistentVariable[$key] = array_unique( $persistentVariable[$key] );
        }

        // set the finnished array in the template
        if ( $isPageLayout )
        {
            if ( isset( $moduleResult['content_info']['persistent_variable'] ) )
            {
                $moduleResult['content_info']['persistent_variable'] = $persistentVariable;
                $tpl->setVariable('module_result', $moduleResult );
            }
        }
        else
        {
            $tpl->setVariable('persistent_variable', $persistentVariable );
        }

        // storing the value internally as well in case this is not a view that supports persistent_variable (ezpagedata will look for it)
        self::$persistentVariable = $persistentVariable;

        if ( $returnArrayDiff && isset( $persistentVariableCopy[ $key ][0] ) )
            return array_diff( $persistentVariable[ $key ], $persistentVariableCopy[ $key ] );

        return $persistentVariable[$key];
    }

    /**
    * Reusable function for getting internal persistent_variable
    *
    * @internal
    * @param string $key Optional, return all values if null
    * @return array|string
    */
    static public function getPersistentVariable( $key = null )
    {
        if ( $key !== null )
        {
            if ( isset( self::$persistentVariable[ $key ] ) )
                return self::$persistentVariable[ $key ];
            return null;
        }
        return self::$persistentVariable;
    }

    /**
    * Reusable function for getting filter array
    *
	* @param eZTemplate $tpl Template object to get values from
    */
    static public function getFilterArray( eZTemplate $tpl )
    {
        $filters = array();
        
        if ( $tpl->hasVariable('module_result') )
        {
            $moduleResult = $tpl->variable('module_result');
        }
        
        $filters['URL'] = $moduleResult['uri'];
        $filters['Node'] = $moduleResult['node_id'];
        
        foreach ($moduleResult['path'] as $key => $item)
        {
            $text = eZURLAliasML::convertToAlias($item['text']); 
            $filters['Level_'.$key] = $text;
            $filters['Level_'.$key.'_Node'] = $item['node_id'];
        }
        
        $currentUser = eZUser::currentUser();

        if ( $currentUser->isLoggedIn() )
        {        
            $filters['logged_in'] = "yes";
        }
        else {
            $filters['logged_in'] = "no";
        }
        
        $ret = all2edfp::buildFilters($filters);
        
        return $ret;
    }    
    
    // Internal version of the $persistent_variable used on view that don't support it
    static protected $persistentVariable = null;

    // Internal flag for already loaded types
    static protected $loaded = array();
}
?>