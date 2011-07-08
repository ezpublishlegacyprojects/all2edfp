{* check if ads are allowed on current page *}
{if all2edfp_showads()}

    {def $publisherid = ezini( 'DFPSettings', 'publisherID', 'all2edfp.ini' )}
    {def $jsframework = ezini( 'DFPSettings', 'JSFRamework', 'all2edfp.ini' )}

    <script type="text/javascript" src="http://partner.googleadservices.com/gampad/google_service.js"></script>
    <script type="text/javascript">
        GS_googleAddAdSenseService("{$publisherid}");
        GS_googleEnableAllServices();
    </script>

    {* implement custom targeting filters *}
    {include uri='design:all2edfp/custom_targeting_filters.tpl'}
    
    {* implement default targeting filters *}
    {all2edfp_filters()}
    
    {* Generate Ad slots *}
    {all2edfp_addslots()}

    <script type="text/javascript">
        GA_googleFetchAds();
    </script>
	
    {* Generated Fill Slots will be rendered outside the page and moved via the JS function to the corresponding div placement *}
    {all2edfp_fillslots()}
    
    {* Load Javascript needed to move the generated divs *} 
    {if eq( $jsframework, 'jQuery' )}
        {ezscript_require('all2edfp_jquery.js')}
    {elseif eq( $jsframework, 'mootools' )}
        {ezscript_require('all2edfp_mootools.js')}
    {/if}
{/if}