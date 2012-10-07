<?php
/*
 Plugin Name: MtG-Tutor.de Cardlinker
 Plugin Uri: http://www.mtg-tutor.de/gadgets
 Description: Simple Shortcode
 Author: Pascal Kleindienst
 Author URI: http://www.codesection.de
 Version: 0.1
 
 License:
 ==============================================================================
 Copyright 2012 Pascal Kleindienst  (email : info@codesection.de)

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
/**
 * 
 */
class MTutor_Cardlinker
{   
    private $add_scripts = False;
    
    /**
     * Add needed filters and hooks
     */
    public function __construct()
    {
        $this->options = get_option(
            'mt_cardlinker_options',
            array(
                'mt_cardlinker_columns'     => 2,
                'mt_cardlinker_hover_image' => 1,
                'mt_cardlinker_theme'       => 0,                
                'mt_cardlinker_prob'        => 1
            )
        );
        
        load_plugin_textdomain('mtutor_cardlinker', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');     
                
        $this->deck_analyser = new MTutor_Deck_Analyser( $this->options );
        $this->add_shortcodes();
        $this->add_styles_and_scripts();
        $this->add_editor_buttons();
    }
      
    /**
     * Do shortcodes like [mt_card]Black Lotus[/mt_card] or [mt_card card="Black Lotus"]Awesome Card[/mt_card]
     * 
     * @access public
     * @param array $atts - Attributes e.g. card
     * @param string $content - Text between shortcode
     * @return string
     */
    public function card($atts, $content)
    {
        $card = $atts['card'];
        
        if( $card == '' && empty($content))
            return;
            
        if( $card == '')
            $card = $content;
            
        $this->add_scripts = True;
        return $this->deck_analyser->card_anchor($card, $content); 
    }
    
    /**
     * Do shortcodes like [mt_deck]...[/mt_deck]
     * 
     * @access public
     * @param array $atts - Attributes e.g. name
     * @param string $content - Text between shortcode
     * @return string
     */
    public function deck($atts, $deck)
    {
        $atts = shortcode_atts(
            array(
                'name' => 'Mein Deck',
            ), $atts
        );
        
        extract($atts);
        
        $this->add_scripts = True;
        $this->deck_analyser->prepare_deck($deck, $name);
                
        return $this->deck_analyser->get_deck_output();
    }
    
    /**
     * Add Shortcodes to WP
     * 
     * @access private
     */
    private function add_shortcodes()
    {
        add_shortcode('mt_card', array($this, 'card'));
        add_shortcode('mt_deck', array($this, 'deck'));
    }
    
    private function add_styles_and_scripts()
    {
        if($this->options['mt_cardlinker_theme'] == 0)
            wp_enqueue_style('mt_deck', plugins_url('css/mt_deck.css', __FILE__));
        
        if($this->options['mt_cardlinker_hover_image'] == 1) {
            wp_register_script('mt_card_hover', plugins_url('js/mt_card_hover.js', __FILE__), array('jquery'), '1.0', true);
            add_action('wp_footer', array($this, 'print_scripts'));
        }
    }
    
    /**
     * Add Buttons Filter to the WP Editor
     * 
     * @access private
     */
    private function add_editor_buttons()
    {
        if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
            return;
        
        if ( get_user_option('rich_editing') == true) {
            add_filter('mce_external_plugins', array($this, 'add_tinymce_plugins'));
            add_filter('mce_buttons', array($this, 'register_buttons'));
        }
    }
        
    /**
     * Add Buttons to the WP Editor
     * 
     * @access public
     */
    public function register_buttons($buttons)
    {
        array_push($buttons, "|", "mt_cardlinker_button" , "|", "mt_decklinker_button");
        return $buttons;
    }
    
    /**
     * Add JS for the Buttons
     * 
     * @access public
     */
    public function add_tinymce_plugins($plugin_array) 
    {
        $plugin_array['mt_cardlinker'] = plugins_url('js/mt_card_editor_plugin.js', __FILE__);
        $plugin_array['mt_decklinker'] = plugins_url('js/mt_deck_editor_plugin.js', __FILE__);
        return $plugin_array;
    }
    
    public function print_scripts()
    {
        if ( ! $this->add_scripts )
			return;
 
		wp_print_scripts('mt_card_hover');
    }
}

/**
 * Initiate to Plugin on WP Init
 */
add_action('init', 'mtgtutor_init_plugin');

function mtgtutor_init_plugin() {
    include dirname(__FILE__) . '/includes/mtutor_deck_analyser.php';
    new MTutor_Cardlinker();
}

/**
 * Initiate the admin menu
 */
add_action('admin_menu', 'mtgtutor_admin_menu');

function mtgtutor_admin_menu() {
    include_once dirname(__FILE__) . '/includes/mtutor_options_page.php';
    MTutor_Options_Page::add_menu_page();
}

// JUST for demo Use, REMOVE later
function my_refresh_mce($ver) {
  $ver += 3;
  return $ver;
}
add_filter( 'tiny_mce_version', 'my_refresh_mce');