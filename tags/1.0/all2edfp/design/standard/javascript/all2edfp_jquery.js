function positionBanner()
{
jQuery.each( jQuery( '.banner-hidden' ), function( index, item ){
    var id = jQuery(item).attr('id').replace(/_hidden/, '');
    try{
        var pos = jQuery( '#'+id ).offset();
        if (pos)
        {
            jQuery(item).css( pos );
        // Sets the height of placeholder div to the same as the ad
        jQuery( '#'+id ).css( 'height', jQuery(item).height() );
        jQuery( '#'+id ).css( 'width', jQuery(item).width() );
        }
    }catch(e){}
    });
}


$(document).ready(function() {
    positionBanner();
});
$(window).resize(function() {
    positionBanner();
});