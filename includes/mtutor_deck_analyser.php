<?php
/**
 * Shortcode functionallity
 * 
 * @package mtutor_cardlinker
 * @author Pascal Kleindienst <http://www.codesection.de>
 * @license GPL2
 * @version 0.1
 */
class MTutor_Deck_Analyser
{
    private $deck;
    private $name;
    private $total;
    private $group_rows;
    private $options;
    
    public function __construct( $options = array() )
    {
        $this->options = $options;
    }
    
    /**
     * Create an anchor tag for a card
     * 
     * @access public
     * @param string $card - Card to link
     * @param string $content - Alternative anchor text
     * @return string
     */
    public function card_anchor($card, $content = "")
    {
        // If no content is provided, take the card name as the anchor text
        if($content === '') 
            $content = $card;
            
        $url = "http://www.mtg-tutor.de/search/" . $this->encode_seo_url($card);
        
        return '<a class="cardlink" href="' . $url . '" title="' . $content . '">' . $content . '</a>';
    }
    
    /**
     * Prepare the deck, so it can displayed gracefully
     * 
     * @access public
     * @param string $deck - Deck content
     * @param string $name - Name of the deck
     */
    public function prepare_deck($deck, $name)
    {
        // Save some information about the deck
        $this->deck     = $deck;
        $this->name     = $name;
        $this->total    = 0;
        $this->group_rows = array('_total' => 0);
        
        // Extract the content from the deck
        $this->extract_groups();
        $this->extract_cards();
    }
    
    /**
     * Extract the groups from the deck (simply split by <p> tags)
     * 
     * @access private
     */
    private function extract_groups()
    {
        $this->deck = explode("</p>", $this->deck);
    }
    
    /**
     * Extract all Cards from the current deck content
     * 
     * @access private
     */
    private function extract_cards()
    {
        $data = array();
        foreach($this->deck AS $key => $group) {
            // Get the rows from a group
            $rows = explode("\n", $group);
            $rows = array_map('strip_tags', $rows);
            $rows = array_filter($rows);
            
            // If it's not an empty group, set the group rows for this group to zero
            if($group !== '')
                $this->group_rows[$key] = 0;
            
            // Create a new Object which contains information about the current group
            $data[$key] = new stdClass();
            $data[$key]->name   = null;
            $data[$key]->cards  = array();
            $data[$key]->number = 0;
            
            // Loop through each row to get the card informations
            foreach($rows AS $card => $row) {
                // skip empty rows
                if($row === '')
                    continue;
                
                // If the row beginns with //, make a new group
                if( substr( trim($row), 0, 2) == '//' ) {
                    $key                = strip_tags($row);
                    $tmp_name           = trim( substr($key, 2) );
                    $data[$key]->name   = ($tmp_name === 'sideboard') ? 'Sideboard' : $tmp_name;
                    continue;
                }
                
                // Increment the group rows
                $this->group_rows[$key]++;
                $this->group_rows['_total']++;
                 
                // extract the card and card amount from the row
                $card_num_pattern   = '([\d]*)[x]?';                // All with a digit and x, is optional
                $comment_pattern    = '(\([^\)]*\)|\[[^\]]*\])?';   // All text between brackets or parentheses, is optional 
                $card_pattern       = '([^\(|^\[|^\)|^\]]*)';       // All Text until bracket or parenthesis
                preg_match("/$card_num_pattern\s*$comment_pattern\s*$card_pattern\s*$comment_pattern/is",
                     $row, $matches);
                
                // Add amount of cards to total amount if the card is not in the sideboard
                if( $data[$key]->name !== 'Sideboard' )
                    $this->total += ((int)$matches[1] > 0) ? (int)$matches[1] : 1;
                
                // Add data to the object
                $data[$key]->number += (int)$matches[1];
                
                $data[$key]->cards[$card] = new stdClass();
                $data[$key]->cards[$card]->number   = $matches[1];
                $data[$key]->cards[$card]->card     = htmlspecialchars_decode(strip_tags( trim($matches[3])) );
                $data[$key]->cards[$card]->comments = array(
                    'before'    => strip_tags($matches[2]),
                    'after'     => strip_tags($matches[4])
                );
            }
        }
        
        // Save as deck
        $this->deck = $data;
    }
    
    /**
     * Output the Deck as html
     * 
     * @access public
     * @return string
     */
    public function get_deck_output()
    {
        // Determine the rows per column, and the amount of columns
        $rows_per_col   = ceil($this->group_rows['_total'] / $this->options['mt_cardlinker_columns']);
        $rows_in_col    = 0;
        $bp_done        = false;
        
        // Cardlist
        $card_list = '';
        
        // Column container
        $col_1_container = '<div class="mt_col1">';
        $col_2_container = '<div class="mt_col2">';
        $col_3_container = '<div class="mt_col3">';
        
        // loop through the groups
        foreach($this->deck AS $key => $list) {
            // Is a break needed
            $breakpoint = ($rows_per_col < $rows_in_col || 
                $rows_per_col + $rows_per_col/2 <= $rows_in_col + $this->group_rows[$key]);
            
            // Current rows in column
            $rows_in_col += $this->group_rows[$key];
            $cards = '';
            
            // loop through the cards
            foreach($list->cards AS $card) {
                // Comments before a card
                $comment_before = '';
                if($card->comments['before'] !== '') {
                    $comment_before = '
                        <span class="mt_card_comment mt_card_comment_before">'.esc_attr($card->comments['before']).'</span> ';
                }
                
                // Comments after a card
                $comment_after = '';
                if($card->comments['after'] !== '') {
                    $comment_after = '
                        <span class="mt_card_comment mt_card_comment_after">'.esc_attr($card->comments['after']).'</span> ';
                }
                
                // Cardprob, but only if activated and not in sideboard
                $card_prob = '';
                if($list->name !== 'Sideboard' && $this->options['mt_cardlinker_prob'] == 1)  {
                    $card_prob = 
                        '<span class="mt_card_prob"> ' . $this->card_prob( $card->number, true ) . ' </span>';
                }
                
                // Add card to cards string
                $cards .= '
                    <li class="mt_card">
                        <span class="mt_card_number">'.$card->number.'</span> ' .
                            $comment_before . 
                            $this->card_anchor( $card->card ) . 
                            $comment_after .
                            $card_prob .
                    '</li>
                ';
            }
            
            // Skip empty groups
            if($cards === '' && $list->name == null)
                continue;
            
            // Heading for current group
            if( $list->name === 'Sideboard' || $list->name !== null) 
                $heading = '<li class="mt_heading">' . $list->name . ' <span class="mt_total">(' . $list->number . ' Karten)</span></li>';
            else 
                $heading = '';
            
            // Add a new column if needed
            if($this->options['mt_cardlinker_columns'] == 3 && $bp_done && $breakpoint) {
                $card_list .= '</div>' . $col_3_container;
            }
            
            if($this->options['mt_cardlinker_columns'] == 2 && !$bp_done && $breakpoint ) {
                $card_list .= '</div>' . $col_2_container;
                $bp_done = true;
            }
            
            // Add group to the card list
            $group_class = ($list->name === 'Sideboard' ) ? 'mt_sideboard_group' :'mt_main_group';
            $card_list .= '
                <ul class="'.$group_class.'">
                    ' . $heading . '
                    ' . $cards . '
                </ul>
            ';
        }
        
        // generate the output
        $output = '
        <div class="mt_deck mt_columns_'.$this->options['mt_cardlinker_columns'].'">
            <header><h3> ' . esc_attr($this->name) . ' </h3></header>
            ' . $col_1_container . 
                    $card_list 
            . '</div>
            <footer><small>' . sprintf( __('%s cards in deck', 'mtutor_cardlinker'), $this->total) . '</small></footer>
        </div>
        ';
        return $output;
    }
    
    /**
     * Startprobability
     * PRODUCT[ (total-num-i) / (total-i) ] for i = 0 until i < 7 
     *      with total = "total cards"" and num = "number of this card"
     * 
     * @access private
     * @param int $num - Number of this card
     * @param boolean $percentage - Set % at end or not
     * @return int
     */
    private function card_prob($num, $percentage = False)
    {
        if($num === '' || !is_numeric($num))
            return;
            
        $percent = 1;
        
    	for($i = 0; $i<7; $i++) 
    		$percent *= ($this->total - $num - $i) / ($this->total - $i);
    	
        // Round result
        $percent = (1-$percent) * 100 * 100;
    	$percent = floor($percent) / 100;
        
        if($percentage)
            return $percent . '%';
        
    	return $percent;
    }
    
    /**
     * Encode an url so that it is seo friendly
     * 
     * @access private
     * @param string $url - URL to encode
     * @return string
     */
    private function encode_seo_url( $url ) 
    {
        $url = str_replace( 
            array(' ', '"', '\'', '/', '&#8217;', '!', ',', ':', '(', ')', '&#8220;', '&#8221;'), 
            array('+', '', '%27', '-', "'"), 
            $url
        );
        
        return $url;
    }
}