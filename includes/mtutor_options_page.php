<?php
/**
 * Generate the Options Page for the Wordpress Backend
 * 
 * @package mtutor_cardlinker
 * @author Pascal Kleindienst <http://www.codesection.de>
 * @license GPL2
 * @version 0.1
 */
class MTutor_Options_Page
{
    /**
     * @var array $options
     */
    public $options;
    
    /**
     * @staticvar mixed $options_page
     */
    public static $options_page;
    
    /**
     * Get the current options and register settings
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
        
        $this->register_settings_and_fields();
    }
    
    /**
     * Add the Menu Page and load the help Tab
     * 
     * @access public 
     * @static static
     */
    public static function add_menu_page()
    {
        MTutor_Options_Page::$options_page = add_options_page(
            'MtG-Tutor CardLinker', 
            'MtG-Tutor CardLinker', 
            'administrator', 
            __FILE__, 
            array(
                'MTutor_Options_Page', 
                'display_options_page'
            )
        );
        
        add_action('load-'.MTutor_Options_Page::$options_page, array('MTutor_Options_Page', 'add_help_text'));
    }
    
    /**
     * Display the Options Page
     * 
     * @access public
     */
    public static function display_options_page()
    {
        ?>
        <div class="wrap">
            <?php screen_icon();?>
            <h2><?php echo _e('MtG-Tutor CardLinker Settings', 'mtutor_cardlinker');?></h2>
                        
            <form method="post" action="options.php" enctype="multipart/form-data">
                <?php settings_fields('mt_cardlinker_options'); #!!Important for Securitx ?>
                <?php do_settings_sections(__FILE__);?>
                
                <p class="submit">
                    <input name="submit" type="submit" class="button-primary" value="<?php echo _e('Save Changes', 'mtutor_cardlinker');?>"/>
                </p>
            </form>
        </div>
        <?php
    }
    
    /**
     * Register Settings and section fields
     * 
     * @access public
     */
    public function register_settings_and_fields()
    {
        register_setting('mt_cardlinker_options', 'mt_cardlinker_options', array($this, 'mt_cardlinker_validate_settings')); //3rd param = optional cb
        add_settings_section('mt_cardlinker_main_section', __('Settings', 'mtutor_cardlinker'), array($this, 'mt_cardlinker_main_section_cb'), __FILE__);
        add_settings_field('mt_cardlinker_columns', __('Columns in deck:', 'mtutor_cardlinker'), array($this, 'mt_cardlinker_columns_setting'), __FILE__, 'mt_cardlinker_main_section');
        add_settings_field('mt_cardlinker_hover_image', __('Images on mouseover:', 'mtutor_cardlinker'), array($this, 'mt_cardlinker_hover_image_setting'), __FILE__, 'mt_cardlinker_main_section');
        add_settings_field('mt_cardlinker_theme', __('Theme for deck container:', 'mtutor_cardlinker'), array($this, 'mt_cardlinker_theme_setting'), __FILE__, 'mt_cardlinker_main_section');
        add_settings_field('mt_cardlinker_prob', __('Show starting-hand probability:', 'mtutor_cardlinker'), array($this, 'mt_cardlinker_prob_setting'), __FILE__, 'mt_cardlinker_main_section');
    }
    
    // optional section callback for main section
    public function mt_cardlinker_main_section_cb() { /* optional */ }
    
    /**
     * Validate the Settings
     * 
     * @access public
     * @param mixed $plugin_options
     * @return $plugin_options
     */
    public function mt_cardlinker_validate_settings($plugin_options)
    {        
        return $plugin_options;
    }
    
    /**
     * Setting for Columns in deck container
     * 
     * @access public
     */
    public function mt_cardlinker_columns_setting()
    {
        $items = array(1,2,3);
        foreach($items as $item){
            $checked = ( $this->options['mt_cardlinker_columns'] == $item ) ? 'checked="checked"' : '';
            echo '<input type="radio" value="'.$item.'" name="mt_cardlinker_options[mt_cardlinker_columns]" id="mt_cardlinker_columns_'.$item.'" '.$checked.' /> ';
            echo '<label for="mt_cardlinker_columns_'.$item.'">'.$item.'</label>';
            echo "<br/>";
        }
    }
    
    /**
     * Setting for displaying Hover Images of Cards
     * 
     * @access public
     */
    public function mt_cardlinker_hover_image_setting()
    {
        $items = array( 1 => __('Yes', 'mtutor_cardlinker'), 0 => __('No', 'mtutor_cardlinker'));
        foreach($items as $key => $item){
            $checked = ( $this->options['mt_cardlinker_hover_image'] == $key ) ? 'checked="checked"' : '';
            echo "<input type=\"radio\" value=\"$key\" name=\"mt_cardlinker_options[mt_cardlinker_hover_image]\" id=\"mt_cardlinker_hover_image_$key\" $checked /> ";
            echo '<label for="mt_cardlinker_hover_image_'.$key.'">'.$item.'</label>';
            echo "<br/>";
        }
    }
    
    /**
     * Setting for Theme of the deck container
     * 
     * @access public
     */
    public function mt_cardlinker_theme_setting()
    {
        $items = array(__('Standard', 'mtutor_cardlinker'), __('Own', 'mtutor_cardlinker'));
        foreach($items as $key => $item){
            $checked = ( $this->options['mt_cardlinker_theme'] == $key ) ? 'checked="checked"' : '';
            echo "<input type=\"radio\" value=\"$key\" name=\"mt_cardlinker_options[mt_cardlinker_theme]\" id=\"mt_cardlinker_theme_$key\" $checked /> ";
            echo '<label for="mt_cardlinker_theme_'.$key.'">'.$item.'</label>';
            echo "<br/>";
        }
    }
    
    /**
     * Setting for showing starthand probabillity of cards
     * 
     * @access public
     */
    public function mt_cardlinker_prob_setting()
    {
        $items = array( 1 => __('Yes', 'mtutor_cardlinker'), 0 => __('No', 'mtutor_cardlinker') );
        foreach($items as $key => $item){
            $checked = ( $this->options['mt_cardlinker_prob'] == $key ) ? 'checked="checked"' : '';
            echo "<input type=\"radio\" value=\"$key\" name=\"mt_cardlinker_options[mt_cardlinker_prob]\" id=\"mt_cardlinker_prob_$key\" $checked /> ";
            echo '<label for="mt_cardlinker_prob_'.$key.'">'.$item.'</label>';
            echo "<br/>";
        }
    }
    
    /**
     * Help Text for help tab
     * 
     * @access public
     * @static static
     */
    public static function add_help_text()
    {
        $screen = get_current_screen();
        
        // Check if current screen is the Options Page
        if ( $screen->id != MTutor_Options_Page::$options_page )
            return;
        
        load_plugin_textdomain('mtutor_cardlinker', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');     
        
        // Help messages
    	$overview = __('<p>This plugin contains two different shortcodes - [mt_card] and [mt_deck]. With the [mt_card] shortcode you can easily link Magic the Gathering Cards. With the [mt_deck] Shortcode you can link an entire deck or card list at once.</p>', 'mtutor_cardlinker');
        
        $card_shortcode = '<p>' . __('[mt_card]Black Lotus[mt_card] or [mt_card card="Black Lotus"]An awesome Card[/mt_card]', 'mtutor_cardlinker') . '</p>';
        $deck_shortcode = '<p>' . __('There are some options for the deck shortocode. You can give the deck a specific nam by adding the name attribute to the shortcode(ex. [mt_deck name = "Awesome Deck"]). You can group cards into blocks with "//", for example "// creatures" or "//Lands" (the number of cards in this group is added automatically). Comments are also allowed in the shortcode, they beginn either with "(" or "[" and end with ")" or "]". Here is an example', 'mtutor_cardlinker') .
"</p><pre>[mt_deck]3  Evolving Wilds
4  Forest
11  [I am a comment]Mountain
1  Raging Ravine
3  Terramorphic Expanse
4  Valakut, the Molten Pinnacle
2  Verdant Catacombs (I'm also a comment)

// Creatures
2  Avenger of Zendikar
1  Birds of Paradise
2  Oracle of Mul Daya
4  Overgrown Battlement
4  Primeval Titan
3  Solemn Simulacrum
1  Wurmcoil Engine

1  Dismember
3  Explore
3  Green Sun's Zenith
2  Harrow
1  Nature's Claim
4  Rampant Growth
1  Summoning Trap

//Sideboard
1  Gaea's Revenge
3  Memoricide
3  Nature's Claim
1  Obstinate Baloth
3  Pyroclasm
1  Slagstorm
2  Summoning Trap
1  Swamp
[/mt_card]</pre>";
        
        // Add Help Tabs
        $screen->add_help_tab( 
            array( 
               'id'         => 'mtutor_cardlinker_overview',
               'title'      => __('Overview', 'mtutor_cardlinker'),
               'content'    => $overview
            ) 
        );
        
        $screen->add_help_tab( 
            array( 
               'id'         => 'mtutor_cardlinker_shortcode_card',          
               'title'      => __('Card Shortcode', 'mtutor_cardlinker'),      
               'content'    => $card_shortcode 
            ) 
        );
        
        $screen->add_help_tab( 
            array( 
               'id'         => 'mtutor_cardlinker_shortcode_deck',
               'title'      => __('Deck Shortcode', 'mtutor_cardlinker'),      
               'content'    => $deck_shortcode
            ) 
        );
    }
}

// Init the Options Page, if where in the Backend
add_action('admin_init', function() {
    new MTutor_Options_Page();
});