{* You may add additional attributes filters here *}
 
{set-block scope=root variable=cache_ttl}0{/set-block}

{* Example

<script type="text/javascript">
{if $current_user.is_logged_in}
GA_googleAddAttr("loggedin","Yes");
{else}
GA_googleAddAttr("loggedin","No");
{/if}
</script>

*}