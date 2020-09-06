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
 * indianchief.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in indianchief_indianchief.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */
  
  require_once( APP_BASE_PATH."view/common/game.view.php" );
  
  class view_indianchief_indianchief extends game_view
  {
    function getGameName() {
        return "indianchief";
    }    
  	function build_page( $viewArgs )
  	{		
  	    // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count( $players );

        /*********** Place your code below:  ************/
        $this->page->begin_block( "indianchief_indianchief", "player" );
        foreach( $players as $player_id => $player )
        {
            $this->page->insert_block( "player", array( "PLAYER_ID" => $player_id,
                                                        "PLAYER_NAME" => $players[$player_id]['player_name'],
                                                        "PLAYER_COLOR" => $players[$player_id]['player_color'],
                                                      ) );
        }

        $this->page->begin_block( "indianchief_indianchief", "player_score" );
        foreach( $players as $player_id => $player )
        {
            $this->page->insert_block( "player_score", array( "PLAYER_ID" => $player_id,
                                                              "PLAYER_NAME" => $players[$player_id]['player_name'],
                                                              "PLAYER_COLOR" => $players[$player_id]['player_color'],
                                                              "PLAYER_SCORE" => 0,
                                                              "PLAYER_THIEF_SCORE" => $this->game->getStat('thiefPoints', $player_id),
                                                              "PLAYER_BEGGAR_SCORE" => $this->game->getStat('beggarPoints', $player_id),
                                                              "PLAYER_POORMAN_SCORE" => $this->game->getStat('poorManPoints', $player_id),
                                                              "PLAYER_LAWYER_SCORE" => $this->game->getStat('lawyerPoints', $player_id),
                                                              "PLAYER_RICHMAN_SCORE" => $this->game->getStat('richManPoints', $player_id),
                                                              "PLAYER_DOCTOR_SCORE" => $this->game->getStat('doctorPoints', $player_id),
                                                              "PLAYER_INDIANCHIEF_SCORE" => $this->game->getStat('indianChiefPoints', $player_id),
                                                      ) );
        }

        $this->tpl['MY_HAND'] = self::_("My hand");

        /*********** Do not change anything below this line  ************/
  	}
  }
  

