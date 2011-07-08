// Mootools implementation 
function positionBanner()
{
    $$( '.banner-hidden' ).each( function( item ){
        var id = $(item).getProperty('id').replace(/_hidden/, '');
        try{
            var pos = $(id).getCoordinates();
			
            if (pos)
            {
				//alert("Banner: "+id);
                // Show the banner directly over the placeholder
                $(item).setStyles(
                {
                  "left": pos.left +"px",
                  "top":  pos.top  +"px"
                });
                // Sets the height of placeholder div to the same as the ad
                $(id).setStyle( 'height', $(item).getSize().y );
                $(id).setStyle( 'width', $(item).getSize().x );

            }
        }catch(e){}
    });
}

window.addEvent('domready', positionBanner );
window.addEvent('resize', positionBanner );