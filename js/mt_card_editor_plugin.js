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
	tinymce.create('tinymce.plugins.mt_cardlinker', {
		init : function(ed, url) {
			ed.addCommand('mt_cardlinker_plugin', function() {
                // If card is selected add shortcode
                var sel_text = tinyMCE.activeEditor.selection.getContent();
                if(sel_text != '') {
                    var output = '[mt_card]' + sel_text + '[/mt_card]';        		
                    ed.execCommand('mceReplaceContent', false, output);
                    return;
                }
                
                // Open dialog
				ed.windowManager.open({
					file : url + '/mt_card_editor_dialog.html',
					width : 450 + parseInt(ed.getLang('mt_card_linker.delta_width', 0)),
					height : 200 + parseInt(ed.getLang('mt_card_linker.delta_height', 0)),
					inline : 1
				}, {
					plugin_url : url
				});
			});
            
            // Button
			ed.addButton('mt_cardlinker_button', {
                title : 'MtG-Tutor CardLinker - Verlinke eine einzelene MtG Karte', 
                cmd : 'mt_cardlinker_plugin', 
                image: url.substring(0, (url.length - 2)) + '/images/icon_card.png' 
            });
		},
        
        // Info about Plugin
		getInfo : function() {
			return {
				longname : 'MtG-Tutor CardLinker',
				author : 'Pascal Kleindienst',
				authorurl : 'http://www.codesection.de',
				infourl : 'http://www.mtg-tutor.de/gadgets',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});
	tinymce.PluginManager.add('mt_cardlinker', tinymce.plugins.mt_cardlinker);
})();