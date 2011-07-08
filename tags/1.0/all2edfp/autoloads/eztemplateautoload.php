<?php
/**
 * Template autoload definition for all2e Google DFP Ad Manager
 */

/**
* Look in the operator files for documentation on use and parameters definition.
*
* @var array $eZTemplateOperatorArray
*/

$eZTemplateOperatorArray = array();
$eZTemplateOperatorArray[] = array( 'script' => 'extension/all2edfp/autoloads/all2edfptemplatefunctions.php',
                                    'class' => 'all2eDFPTemplateFunctions',
                                    'operator_names' => array( 'all2edfp_require','all2edfp_addslots','all2edfp_fillslots','all2edfp_filters','all2edfp_showads' ) );
         

?>