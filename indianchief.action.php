<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * IndianChief implementation : © Michael Ihde <mike.ihde@randomwalking.com>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * indianchief.action.php
 *
 * IndianChief main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/indianchief/indianchief/myAction.html", ...)
 *
 */
  
  
  class action_indianchief extends APP_GameAction
  { 
    // Constructor: please do not modify
   	public function __default()
  	{
  	    if( self::isArg( 'notifwindow') )
  	    {
            $this->view = "common_notifwindow";
  	        $this->viewArgs['table'] = self::getArg( "table", AT_posint, true );
  	    }
  	    else
  	    {
            $this->view = "indianchief_indianchief";
            self::trace( "Complete reinitialization of board game" );
      }
  	} 
    
    public function meldCards()
    {
        self::setAjaxMode();     
        $cards_raw = self::getArg( "cards", AT_numberlist, true );
        
        // Removing last ';' if exists
        if( substr( $cards_raw, -1 ) == ';' )
            $cards_raw = substr( $cards_raw, 0, -1 );
        if( $cards_raw == '' )
            $cards = array();
        else
            $cards = explode( ';', $cards_raw );

        $this->game->meldCards( $cards );
        self::ajaxResponse( );    
    }

    public function endHand()
    {
        self::setAjaxMode();     
        $this->game->endHand( );
        self::ajaxResponse( );    
    }

    public function takeCard()
    {
        self::setAjaxMode();     
        $card_taken = self::getArg( "card_taken", AT_posint, false, -1 );
        $card_given = self::getArg( "card_given", AT_posint, false, -1 );
        

        $this->game->takeCard( $card_taken, $card_given );
        self::ajaxResponse( );    
    }

  	// TODO: defines your action entry points there


    /*
    
    Example:
  	
    public function myAction()
    {
        self::setAjaxMode();     

        // Retrieve arguments
        // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
        $arg1 = self::getArg( "myArgument1", AT_posint, true );
        $arg2 = self::getArg( "myArgument2", AT_posint, true );

        // Then, call the appropriate method in your game logic, like "playCard" or "myAction"
        $this->game->myAction( $arg1, $arg2 );

        self::ajaxResponse( );
    }
    
    */

  }
  

