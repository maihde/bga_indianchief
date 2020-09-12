<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * IndianChief implementation : © Michael Ihde <mike.ihde@randomwalking.com>
  * 
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  * 
  * indianchief.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class IndianChief extends Table
{
	function __construct( )
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
        
        self::initGameStateLabels( array(
            "currentThiefOffset" => 10,
            "currentRound" => 20,
        ) );  
        
        $this->cards = self::getNew( "module.common.deck" );
        $this->cards->init( "card" );
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "indianchief";
    }	

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {    
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];
        $start_points = 0;

        // Create players
        $sql = "INSERT INTO player (player_id, player_score, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$start_points','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";
        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );
        
        self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();
        
        /************ Start the game initialization *****/

        // Init global values with their initial values
        self::setGameStateInitialValue( 'currentThiefOffset', 0 );
        self::setGameStateInitialValue( 'currentRound', 0 );

        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        self::initStat( "player", "thiefPoints", -99 );
        self::initStat( "player", "beggarPoints", -99 );
        self::initStat( "player", "poorManPoints", -99 );
        self::initStat( "player", "lawyerPoints", -99 );
        self::initStat( "player", "richManPoints", -99 );
        self::initStat( "player", "doctorPoints", -99 );
        self::initStat( "player", "indianChiefPoints", -99 );

        // Setup the cards - one deck for 2-3 players, two decks for 4-8 players
        $num_decks = 1;
        if (count($players) > 3) {
            $num_decks = 2;
        }
        
        $cards = array();
        for ($x = 0; $x < $num_decks; $x++) {
            foreach( $this->colors as  $color_id => $color ) // spade, heart, diamond, club
            {
                for( $value=2; $value<=14; $value++ )   //  2, 3, 4, ... K, A
                {
                    $cards[] = array( 'type' => $color_id, 'type_arg' => $value, 'nbr' => 1);
                }
            }
        }

        // Create the deck
        $this->cards->createCards( $cards, 'deck' );

        // Shuffle the deck
        $this->cards->shuffle( 'deck' );

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();
    
        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
    

        $players = self::loadPlayersBasicInfos();

        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );

        // Cards in player hand      
        $result['hand'] = $this->cards->getCardsInLocation( 'hand', $current_player_id );

        // Cards about to be meld
        $result['temporary'] = $this->cards->getCardsInLocation( 'temporary', $current_player_id );
  
        $players = self::loadPlayersBasicInfos();
        $result['cardsontable'] = array();
        foreach( $players as $player_id => $player )
        {
            $result['cardsontable'][$player_id] = $this->cards->getCardsInLocation( 'cardsontable', $player_id );
        }

        $totalScores = self::getCollectionFromDb( "SELECT player_id, player_score FROM player", true );

        $newScores = array();
        foreach( $totalScores as $player_id => $totalScore )
        {
            $newScores[$player_id] = array();
            $newScores[$player_id]["thiefPoints"] = self::getStat('thiefPoints', $player_id);
            $newScores[$player_id]["beggarPoints"] = self::getStat('beggarPoints', $player_id);
            $newScores[$player_id]["poorManPoints"] = self::getStat('poorManPoints', $player_id);
            $newScores[$player_id]["lawyerPoints"] = self::getStat('lawyerPoints', $player_id);
            $newScores[$player_id]["richManPoints"] = self::getStat('richManPoints', $player_id);
            $newScores[$player_id]["doctorPoints"] = self::getStat('doctorPoints', $player_id);
            $newScores[$player_id]["indianChiefPoints"] = self::getStat('indianChiefPoints', $player_id);
            $newScores[$player_id]["totalPoints"] = $totalScore;
        }
        $result['scores'] = $newScores;
  
        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        // TODO: compute and return the game progression
        $progression = ((self::getGameStateValue( "currentRound" ) - 1) / 7.0) * 100.0;

        return $progression;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////  

    /*
        cardValue:
        
        Convert a card object into it's point value, Ace=1, Face Cards=10
    */
    function cardValue($card) {
        if ($card['type_arg'] == 14) { // A
            $value = 1;
        } else if ($card['type_arg'] >= 11) { // J, Q, K
            $value = 10;
        } else {
            $value = $card['type_arg'];
        }
        return $value;
    }

    function cardSuitName($card) {
        return $this->colors[ $card['type'] ][ 'name'] ;
    }

    function cardShortSuitName($card) {
        return $this->colors[ $card['type'] ][ 'namesh'] ;
    }

    function cardRankName($card) {
        return $this->values_label[ $card['type_arg'] ] ;
    }

    function cardName($card) {
        return self::cardRankName($card).' of '.self::cardSuitName($card).'s';
    }

    function cardShortName($card) {
        return self::cardRankName($card).self::cardShortSuitName($card);
    }

    function cardNames($cards) {
        $card_names = array();
        foreach($cards as $card) {
            array_push($card_names, self::cardName($card));
        }
        return implode(',', $card_names);
    }

    function cardShortNames($cards) {
        $card_names = array();
        foreach($cards as $card) {
            array_push($card_names, self::cardShortName($card));
        }
        return implode(',', $card_names);
    }



    /*
        cardSort:
        
        Sort cards based off rank lowest to highest
    */
    function cardSort($a, $b)
    {
        if (($a['type_arg'] == $b['type_arg']) && ($a['type'] == $b['type'])){
            return 0;
        } else {
            return ($a['type_arg'] < $b['type_arg']) ? -1 : 1;
        }
    }

    /*
        reverseCardSort:
        
        Sort cards based off rank highest to lowest
    */
    function reverseCardSort($a, $b)
    {
        if (($a['type_arg'] == $b['type_arg']) && ($a['type'] == $b['type'])){
            return 0;
        } else {
            return ($a['type_arg'] < $b['type_arg']) ? 1 : -1;
        }
    }

    /*
        scoreThief:
        
        The player earns a score for this meld equal to the point value of the card.
    */
    function scoreThief( $cards) {
        return self::cardValue($cards[0]);
    }

    /*
        scoreBeggar:

        For each card played by _other_ players of the same rank as a card from this meld,
        the player who makes this meld scores 2 points.
    */
    function scoreBeggar($cards_melded, $player_id, $cards_ontable) {
        $score = 0;
        foreach( $cards_ontable as $card_ontable ) {
            // We only score matches with other players cards
            if ($card_ontable['location_arg'] == $player_id) {
                continue;
            } else if (
                ($card_ontable['type_arg'] == $cards_melded[0]['type_arg']) ||
                ($card_ontable['type_arg'] == $cards_melded[1]['type_arg'])
            ) {
                $score += 2;
            }
        }
        return $score;
    }

    /*
        scorePoorMan:
        
        The score earned is thus the total sum of card ranks of cards in the
        suit of Spades melded by the player.
    */
    function scorePoorMan( $cards) {
        $score = 0;
        foreach( $cards as $card )
        {
            if ( $card['type'] == 1  ) { // spade
                $score += self::cardValue($card);
            }
        }

        return $score;
    }

    /*
        scoreLawyer:
        
        In order to earn points for the meld, the ranks of the four cards in the meld
        must total to exactly 25. If they do so, the player scores 25 points for the
        meld, otherwise he scores 0 for the meld.
    */
    function scoreLawyer( $cards) {
        $score = 0;
        foreach( $cards as $card )
        {
            $score += self::cardValue($card);
        }

        if ($score != 25) {
            $score = 0;
        }

        return $score;
    }

    /*
        scoreRichMan:
        
        the score for this meld should be recorded on the scoresheet as a
        negative value, as the sum total of the five cards
    */
    function scoreRichMan( $cards) {
        $score = 0;
        foreach( $cards as $card )
        {
            $score += self::cardValue($card);
        }

        $score *= -1;
        
        return $score;
    }

    /*
        scoreDoctor:

        In order to score any points for the meld, the meld must fulfill very stringent
        requirements; It must contain at least one Ace, it must contain at least one card
        in the suit of Hearts, and no two cards in the meld may be of the same rank. If
        the meld does fulfill this criteria, it scores 10 points per card in the suit which
        has the most cards represented in the meld.
    */
    function scoreDoctor( $cards) {
        $has_ace = false;
        $has_heart = false;
        $has_no_duplicates = false;

        $score = 0;
        $ranks = array();
        $suits = array_fill_keys( array(1,2,3,4), 0);

        foreach( $cards as $card )
        {
            if ( $card['type'] == 2  ) { // heart
                $has_heart = true;
            }
            if ( $card['type_arg'] == 14  ) { // A
                $has_ace = true;
            }
            $ranks[$card['type_arg']] = 1;
            $suits[$card['type']] += 1; 
        }

        if (count($ranks) == 6) {
            $has_no_duplicates = true;
        }

        if ($has_ace && $has_heart && $has_no_duplicates) {
            $score = max($suits) * 10;
        } else {
            $score = 0;
        }
        
        return $score;
    }

    /*
        scoreIndianChiefTwoCard:

        The sum of the two cards are added together and only the ones digit of the
        total is counted.
    */
    function scoreIndianChiefTwoCard($cardA, $cardB) {
        $score = (self::cardValue($cardA) + self::cardValue($cardB)) % 10;
        return $score;
    }

    /*
        scoreIndianChiefPoker:

        Five of a Kind  50
        Straight Flush	45
        Four of a Kind	40
        Full House	    35
        Flush	        30
        Straight        25
        Three of a Kind	20
        Two Pairs       15
        One Pair        10
        Mixed Cards	     5
    */
    function scoreIndianChiefPoker($cards) {
        usort($cards, "self::cardSort");

        $is_straight_flush = false;
        $is_full_house = false;
        $is_flush = false;
        $is_straight = false;
        $is_five_of_a_kind = false;
        $is_four_of_a_kind = false;
        $is_three_of_a_kind = false;
        $is_two_pairs = false;
        $is_one_pair = false;
        
        $matching_run = 1;
        $sequence_run = 1;
        $suit_run = 1;

        $last_card = $cards[0];
        for ($x = 1; $x < 5; $x++) {
            $current_card = $cards[$x];
            
            // see if we have matching ranks
            if ($last_card['type'] == $current_card['type']) {
                $suit_run += 1;
            } else {
                $suit_run = 1;
            }
            if ($last_card['type_arg'] == $current_card['type_arg']) {
                $matching_run += 1;
            } else {
                $matching_run = 1;
            }
            
            if ($last_card['type_arg'] == $current_card['type_arg'] - 1) {
                $sequence_run += 1;
            } else if (($sequence_run == 4) && ($cards[0]['type_arg'] == 2) && ($current_card['type_arg'] == 14)) {
                $sequence_run += 1;
            } else {
                $sequence_run = 1;
            }

            if ($matching_run == 2) {
                // if we have already seen one-pair then
                // this must be the second pair
                if ($is_one_pair == true) {
                    $is_two_pairs = true;
                }
                if ($is_three_of_a_kind == true) {
                    $is_full_house = true;
                }
                $is_one_pair = true;
            } else if ($matching_run == 3) {
                $is_three_of_a_kind = true;
                if ($is_two_pairs) {
                    $is_full_house = true;
                }
            } else if ($matching_run == 4) {
                $is_four_of_a_kind = true;
            } else if ($matching_run == 5) {
                $is_five_of_a_kind = true;
            }
            $last_card = $current_card;
        }

        // see if we have a flush
        if ($suit_run == 5) {
            $is_flush = true;
        }

        // five in a row is a sequence
        if ($sequence_run == 5) {
            $is_straight = true;
            if ($is_flush) {
                $is_straight_flush = true;
            }
        }
        
        $score = 5;
        if ($is_five_of_a_kind) {
            $score = 50;
        } else if ($is_straight_flush) {
            $score = 45;
        } else if ($is_four_of_a_kind) {
            $score = 40;
        } else if ($is_full_house) {
            $score = 35;
        } else if ($is_flush) {
            $score = 30;
        } else if ($is_straight) {
            $score = 25;
        } else if ($is_three_of_a_kind) {
            $score = 20;
        } else if ($is_two_pairs) {
            $score = 15;
        } else if ($is_one_pair) {
            $score = 10;
        }

        return $score;
    }

    /*
        scoreIndianChief:

        seven cards are further split into two different melds,
        a two card meld and a 5 card meld, each of which is scored independently.
    */
    function scoreIndianChief( $cards) {
        $max_score = 0;
        // automatically select the highest scoring hand
        for ($x = 0; $x < 7; $x++) {
            for ($y = $x; $y < 7; $y++) {
                $poker_hand = array();
                for ($i = 0; $i < 7; $i++) {
                    if (($i != $x) && ($i != $y)) {
                        array_push ( $poker_hand, $cards[$i] );
                    }
                }

                $twocard_score = self::scoreIndianChiefTwoCard($cards[$x], $cards[$y]);
                $poker_score = self::scoreIndianChiefPoker($poker_hand);
                $score = $twocard_score + $poker_score;

                $max_score = max($max_score, $score);
            }
        }

        return $max_score;
    }

    /*
        getThiefMelds:

        Returns the remaining thief melds, as a thief is resolved it will
        no longer be returned in this list (because the player will have zero 
        cards on the table).  As such, this acts like a stack of thieves that
        need to be resolved.
    */
    function getThiefMelds() {
        $thieves = array();
    
        $players = self::loadPlayersBasicInfos();

        // Iterate across all player hands on the table;
        // if someone has one card on the table, they played thief
        // order thiefs by rank; if there is a tie choose randomly 
        // when a thief steals their card replaces the other on 
        // the table and their hand on the table is now empty
        // once no theifs are left on the table the round continues
        foreach( $players as $player_id => $player )
        {
            $cards_melded = array_values( $this->cards->getCardsInLocation( 'cardsontable', $player_id ) );
            if (count($cards_melded) == 1) {
                array_push($thieves, $cards_melded[0]);
            }
        }

        usort($thieves, "self::cardSort");

        return $thieves;
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

    function meldCards( $card_ids )
    {
        self::checkAction( "meldCards" );
        
        // !! Here we have to get CURRENT player (= player who send the request) and not
        //    active player, cause we are in a multiple active player state and the "active player"
        //    correspond to nothing.
        $player_id = self::getCurrentPlayerId();
        
        $num_cards_meld = count( $card_ids );

        if( $num_cards_meld < 0 )
            throw new feException( self::_("You must meld at least one card") );

        if( $num_cards_meld > 7 )
            throw new feException( self::_("You must meld no more than eight cards") );

        
        switch ($num_cards_meld) {
            case 1:
                if ( self::getStat('thiefPoints', $player_id) != -99)
                    throw new feException( self::_("You have already played the thief meld") );
                break;
            case 2:
                if ( self::getStat('beggarPoints', $player_id) != -99)
                    throw new feException( self::_("You have already played the beggar meld") );
                break;
            case 3:
                if ( self::getStat('poorManPoints', $player_id) != -99)
                    throw new feException( self::_("You have already played the poor man meld") );
                break;
            case 4:
                if ( self::getStat('lawyerPoints', $player_id) != -99)
                    throw new feException( self::_("You have already played the lawyer meld") );
                break;
            case 5:
                if ( self::getStat('richManPoints', $player_id) != -99)
                    throw new feException( self::_("You have already played the rich man meld") );
                break;
            case 6:
                if ( self::getStat('doctorPoints', $player_id) != -99)
                    throw new feException( self::_("You have already played the doctor meld") );
                break;
            case 7:
                if ( self::getStat('indianChiefPoints', $player_id) != -99)
                    throw new feException( self::_("You have already played the indian chief meld") );
                break;
            default:
                throw new feException( self::_("Invalid number of cards meld") );
        }
    
        // Check if these cards are in player hands
        $cards = $this->cards->getCards( $card_ids );
        
        if( count( $cards ) != $num_cards_meld )
            throw new feException( self::_("Some of these cards don't exist") );
        
        foreach( $cards as $card )
        {
            if( $card['location'] != 'hand' || $card['location_arg'] != $player_id )
                throw new feException( self::_("Some of these cards are not in your hand") );
        }
                
        // Allright, these cards can be meld
        // (note: we place the cards in some temporary location in order that other players cannot see them)
        $this->cards->moveCards( $card_ids, "temporary", $player_id );

        // Notify the player so we can make these cards meld
        self::notifyPlayer( $player_id, "meldCards", "", array(
            "cards" => $cards
        ) );

        // Make this player unactive now
        // (and tell the machine state to use transtion "playCards" if all players are now unactive
        $this->gamestate->setPlayerNonMultiactive( $player_id, "playCards" );
    }

    function undoMeld( )
    {
        $this->gamestate->checkPossibleAction( "undoMeld" );
        
        $player_id = self::getCurrentPlayerId();

        $cards_to_meld = $this->cards->getCardsInLocation( 'temporary', $player_id );

        $this->cards->moveAllCardsInLocation( "temporary", "hand", $player_id, $player_id );

        $this->gamestate->setPlayersMultiactive( array( $player_id ), "playCards" );

        self::notifyPlayer( $player_id, "undoMeld", "", array(
            "cards" => $cards_to_meld 
        ) );
    }

    function endHand() {
        self::checkAction( "endHand" );

        $player_id = self::getCurrentPlayerId();
    }

    function takeCard($card_taken_id, $card_given_id) {
        // Check that the takeCard action is valid
        self::checkAction( "takeCard" );

        $thieves = $this->getThiefMelds();

        $players = self::loadPlayersBasicInfos();

        $player_id = self::getCurrentPlayerId();

        $current_thief = self::getGameStateValue( 'currentThiefOffset' );

        if ($thieves[$current_thief]['location_arg'] != $player_id)
            throw new feException( self::_("You aren't currently the active thief") );


        if (($card_taken_id >= 0) && ($card_given_id >= 0)) {
            $card_taken = $this->cards->getCard( $card_taken_id );

            $card_given = $this->cards->getCard( $card_given_id );

            if( $card_given['location'] != 'cardsontable' || $card_given['location_arg'] != $player_id )
                throw new feException( self::_("You can only give your melded thief card") );

            if( $card_taken['location'] != 'cardsontable' || $card_taken['location_arg'] == $player_id )
                throw new feException( self::_("You can only take a card someone else has played") );

            // Your card is played into their table, another thief may steal it
            $this->cards->moveCard( $card_given_id, "cardsontable", $card_taken['location_arg'] );
            // The card you take is moved into your hand and cannot be stolen
            $this->cards->moveCard( $card_taken_id, "hand", $player_id );
            
            $player_taken_name = $players[ $card_taken['location_arg' ] ]['player_name'];
            $card_taken_name = self::cardShortName( $card_taken);
            $card_given_name = self::cardShortName( $card_given);
            self::notifyAllPlayers( "takeCard", clienttranslate('${player_name} steals [${card_taken_name}] from '.$player_taken_name.'and gives [${card_given_name}]'), array(
                "card_taken" => $card_taken,
                "card_taken_name" => $card_taken_name,
                "card_given_name" => $card_given_name,
                "card_given" => $card_given,
                "taken_from_player_id" => $card_taken['location_arg'],
                "player_id" => $player_id,
                "player_name" => $players[$player_id]['player_name'],
            ) );
        } else {
            // if a thief skips, we need to jump over them in the offset position
            self::notifyAllPlayers( "skipThief", clienttranslate('${player_name} skips thief action'), array(
                "player_id" => $player_id,
                "player_name" => $players[$player_id]['player_name'],
            ) );
            self::incGameStateValue( 'currentThiefOffset', 1 );
        }

        $this->gamestate->nextState( "nextThief" );
    }

    function endGame() {
        self::checkAction( "endGame" );

        $this->gamestate->nextState( "endGame" );
    }
    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    /*
    
    Example for game state "MyGameState":
    
    function argMyGameState()
    {
        // Get some values from the current game situation in database...
    
        // return values:
        return array(
            'variable1' => $value1,
            'variable2' => $value2,
            ...
        );
    }    
    */

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        stNewHand:

        When a hand stards, each player is dealt cards to replenish their hand to 8 cards.
        Then, all the cards on the table are picked up and shuffled into the deck.

        It's technically possible that there are not enough cards during the deal to replenish
        every players cards in an 8-player game.  If that happens, the cards on the table
        are picked up and shuffled to create 

    */
    function stNewHand()
    {
        self::incGameStateValue("currentRound", 1);
        self::setGameStateValue('currentThiefOffset', 0);

        // Replenish each players hand to 8 cards
        $players = self::loadPlayersBasicInfos();
        foreach( $players as $player_id => $player )
        {
            $num_cards_in_hand = $this->cards->countCardsInLocation('hand', $player_id );

            $cards_to_deal = 8 - $num_cards_in_hand;

            // We don't have enough cards in the deck so we need to pick-up
            // the cards on the table early
            if ($this->cards->countCardInLocation('deck') < $cards_to_deal) {
                $this->cards->moveAllCardsInLocation( "cardsontable", "deck" );
                $this->cards->shuffle( 'deck' );
            }
            if ($cards_to_deal > 0) {
                $this->cards->pickCards( $cards_to_deal, 'deck', $player_id );
            }
            
            // Notify player about his cards
            $cards_in_hand = $this->cards->getCardsInLocation( 'hand', $player_id );
            self::notifyPlayer( $player_id, 'newHand', '', array( 
                'cards' => $cards_in_hand
            ) );
        }  

        // Take back all cards on table to deck
        $this->cards->moveAllCardsInLocation( "cardsontable", "deck" );

        // Shuffle the Deck (it's okay if we are reshuffling)
        $this->cards->shuffle( 'deck' );

        $this->gamestate->nextState( "" );
    }

    /*
        stMeldCards:

        All players select cards to meld concurrently.

    */
    function stMeldCards()
    {        
        $this->gamestate->setAllPlayersMultiactive();
    }

    /*
        stEndHand:

        At the end of the hand, the cards that each player chose to meld are displayed 
        and then scored. 

    */
    function stEndHand()
    {
        $currentRound = self::getGameStateValue("currentRound");

        // Move the cards the player has decided to meld onto the table
        $this->cards->moveAllCardsInLocationKeepOrder( "temporary", "cardsontable" );
       
        // Update scores according to the number of disc on board
        $players = self::loadPlayersBasicInfos();
        foreach( $players as $player_id => $player )
        {
            $cards_melded = array_values( $this->cards->getCardsInLocation( 'cardsontable', $player_id ) );
            $cards_ontable = array_values( $this->cards->getCardsInLocation( 'cardsontable' ) );

            $num_cards_meld = count( $cards_melded );
            switch ($num_cards_meld) {
                case 1:
                    $meld_played = "Thief";
                    $points = self::scoreThief($cards_melded);
                    self::setStat($points, 'thiefPoints', $player_id);
                    break;
                case 2:
                    $meld_played = "Beggar";
                    $points = self::scoreBeggar($cards_melded, $player_id, $cards_ontable);
                    self::setStat($points, 'beggarPoints', $player_id);
                    break;
                case 3:
                    $meld_played = "Poor Man";
                    $points = self::scorePoorMan($cards_melded);
                    self::setStat($points, 'poorManPoints', $player_id);
                    break;
                case 4:
                    $meld_played = "Lawyer";
                    $points = self::scoreLawyer($cards_melded);
                    self::setStat($points, 'lawyerPoints', $player_id);
                    break;
                case 5:
                    $meld_played = "Rich Man";
                    $points = self::scoreRichMan($cards_melded);
                    self::setStat($points, 'richManPoints', $player_id);
                    break;
                case 6:
                    $meld_played = "Doctor";
                    $points = self::scoreDoctor($cards_melded);
                    self::setStat($points, 'doctorPoints', $player_id);
                    break;
                case 7:
                    $meld_played = "Indian Chief";
                    $points = self::scoreIndianChief($cards_melded);
                    self::setStat($points, 'indianChiefPoints', $player_id);
                    break;
                default:
                    throw new feException( self::_("Invalid number of cards meld") );
            }
           

            $player_name = $player['player_name'];
            $card_names = self::cardShortNames($cards_melded);
            $msg = $player_name.' plays '.$meld_played.' ['.$card_names.'] for '.$points." points";

            self::notifyAllPlayers( 'playCards', clienttranslate($msg), array(
                'cards' =>  $cards_melded,
                'player_id' => $player_id,
                'player_name' => $player_name,
            ) );

            $sql = "UPDATE player SET player_score=player_score+$points WHERE player_id='$player_id' " ;
            self::DbQuery( $sql );
        }

        // Inform all players of the new scores
        $totalScores = self::getCollectionFromDb( "SELECT player_id, player_score FROM player", true );

        $newScores = array();
        foreach( $totalScores as $player_id => $totalScore )
        {
            $newScores[$player_id] = array();
            $newScores[$player_id]["thiefPoints"] = self::getStat('thiefPoints', $player_id);
            $newScores[$player_id]["beggarPoints"] = self::getStat('beggarPoints', $player_id);
            $newScores[$player_id]["poorManPoints"] = self::getStat('poorManPoints', $player_id);
            $newScores[$player_id]["lawyerPoints"] = self::getStat('lawyerPoints', $player_id);
            $newScores[$player_id]["richManPoints"] = self::getStat('richManPoints', $player_id);
            $newScores[$player_id]["doctorPoints"] = self::getStat('doctorPoints', $player_id);
            $newScores[$player_id]["indianChiefPoints"] = self::getStat('indianChiefPoints', $player_id);
            $newScores[$player_id]["totalPoints"] = $totalScore;
        }

        self::notifyAllPlayers( "newScores", "", array(
            "scores" => $newScores
        ) );

        $currentRound = self::getGameStateValue( "currentRound" );

        if ($currentRound == 7) {
            $this->gamestate->nextState( "endGame" );
        } else {
            $thieves = $this->getThiefMelds();
            if (count($thieves) == 0) {
                $this->gamestate->nextState( "newHand" );
            } else {
                $current_thief = self::getGameStateValue( 'currentThiefOffset' );
                $this->gamestate->changeActivePlayer( $thieves[$current_thief]['location_arg'] );
                $this->gamestate->nextState( "thiefTurn" );
            }
        }
    }

    /*
        stResolveThief:
    */
    function stResolveThief() {
        $currentRound = self::getGameStateValue("currentRound");

        // At the end of the game, there is no reason to resolve the thief
        if ($currentRound == 7) {
            $this->gamestate->nextState( "endGame" );
        } else {
            $thieves = $this->getThiefMelds();
            $current_thief = self::getGameStateValue( 'currentThiefOffset' );
            $this->gamestate->changeActivePlayer( $thieves[$current_thief]['location_arg'] );
            // If there are no thief melds, we can go immediately to the next hand
            if ((count($thieves) == 0) || ($current_thief >= count($thieves))) {
                $this->gamestate->nextState( "newHand" );
            } else {
                $this->gamestate->changeActivePlayer( $thieves[$current_thief]['location_arg'] );
            }
        }
    }

    /*
        stNextThief:
    */
    function stNextThief() {
        $thieves = $this->getThiefMelds();
        $current_thief = self::getGameStateValue( 'currentThiefOffset' );

        if ((count($thieves) == 0) || ($current_thief >= count($thieves))) {
            $this->gamestate->nextState( "newHand" );
        } else {
            $this->gamestate->changeActivePlayer( $thieves[$current_thief]['location_arg'] );
            $this->gamestate->nextState( "nextThief" );  
        }
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */

    function zombieTurn( $state, $active_player )
    {
    	$statename = $state['name'];
    	
        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                case 'thiefTurn':
                    $this->takeCard(-1, -1);
                default:
                    $this->gamestate->nextState( "zombiePass" );
                	break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            switch ($statename) {
                case 'meldCards':
                    // Pick any meld that hasn't been done
                    $this->zombieMeld( $active_player );
                    break;
                default:
                	break;
            }

            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive( $active_player, '' );
            
            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }
    
///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */
    
    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345
        
        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }    
}
