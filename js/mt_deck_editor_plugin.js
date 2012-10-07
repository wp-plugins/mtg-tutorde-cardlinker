/**
 * TinyMCE Plugin for adding a button to the WP editor
 *
 * @author Pascal Kleindienst <http://www.codesection.de>
 * @package mtutor_cardlinker
 * @version 0.1
 * @license GPL2
 * @url http://www.mtg-tutor.de/gadgets
 */
(function() {
	tinymce.create('tinymce.plugins.mt_decklinker', {
		init : function(ed, url) {
			ed.addCommand('mt_decklinker_plugin', function() {
                // If deck is selected, add shortcode
                var sel_text = tinyMCE.activeEditor.selection.getContent();   
                if(sel_text != '') {
                    var output = '[mt_deck]' + sel_text + '[/mt_deck]';        		
                    ed.execCommand('mceReplaceContent', false, output);
                    return;
                }
                
                // Open dialog
				ed.windowManager.open({
					file : url + '/mt_deck_editor_dialog.html',
					width : 450 + parseInt(ed.getLang('mt_decklinker_plugin.delta_width', 0)),
					height : 525 + parseInt(ed.getLang('mt_decklinker_plugin.delta_height', 0)),
					inline : 1
				}, {
					plugin_url : url
				});
			});
            
            // Add button
			ed.addButton('mt_decklinker_button', {
                title : 'MtG-Tutor DeckLinker - Verlinke ein komplettes Deck', 
                cmd : 'mt_decklinker_plugin', 
                image: url.substring(0, (url.length - 2)) + '/images/icon_deck.png' 
            });
		},
        
        // Info about Plugin
		getInfo : function() {
            return {
				longname : 'MtG-Tutor DeckLinker',
				author : 'Pascal Kleindienst',
				authorurl : 'http://www.codesection.de',
				infourl : 'http://www.mtg-tutor.de/gadgets',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});
	tinymce.PluginManager.add('mt_decklinker', tinymce.plugins.mt_decklinker);
})();