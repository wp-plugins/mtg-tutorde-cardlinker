/**
 * jQuery for hover images on the cards
 * Images used from gatherer
 *
 * @author Pascal Kleindienst <http://www.codesection.de>
 * @package mtutor_cardlinker
 * @version 0.1
 * @license GPL2
 * @url http://www.mtg-tutor.de/gadgets
 */
(function($) {
    // Cache
    var cache = [];
    
    // Create an id for a card 
    function hover_id( card ) {
        card = card.split('+').join('_');
        card = card.split('#').join('_');
        card = card.split('.').join('_');
        card = card.split("'").join('');
        return ('mt_hover_' + card );
    }
    
    // Hover effect
	jQuery('.cardlink')
        .on('mouseenter', function(e) {
            var $this = $(this),
                position = $this.position(),
                url = $this.attr('href'),
                card = url.split('http://www.mtg-tutor.de/search/').join(''),
                src = 'http://gatherer.wizards.com/Handlers/Image.ashx?size=small&type=card&name=' + card;
            
            // Check if Card is in Cache, then provide cache image
            if( $.inArray( hover_id(card), cache) > -1 ) {
                // Active cached image and position it
                $('#' + hover_id(card) )
                    .addClass('active')
                    .css({
                        'left': position.left,
                        'top': position.top + $this.outerHeight() + 5,
                    })
                    .fadeIn();
                return;
            }
            
            // Create a new img tag
            $('<img />', {'class': 'tmp_card_hover active'}).insertAfter( $this );
            
            // Fill the image tag with needed informations      
            $('img.tmp_card_hover.active')
                .attr('src', src) 
                .attr('id', hover_id(card) )               
                .css({
                    'display': 'none',
                    'position': 'absolute',
                    'left': position.left,
                    'top': position.top + $this.outerHeight() + 5,
                    'width': '223px',
                    'height': '310px',
                    'z-index': '9999'
                })
                .fadeIn();
            
            // Add Card Img to cache
            cache.push( hover_id(card) );
    	})
        .on('mouseleave', function(e) {
            // Remove active state and hide img
            $('img.tmp_card_hover.active').removeClass('active').fadeOut();
        });
})(jQuery);