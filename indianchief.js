/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * IndianChief implementation : © Michael Ihde <mike.ihde@randomwalking.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * indianchief.js
 *
 * IndianChief user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/stock"
],
function (dojo, declare) {
    return declare("bgagame.indianchief", ebg.core.gamegui, {
        constructor: function(){              
            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;
            this.cardwidth = 72;
            this.cardheight = 96;
        },
        
        /*
            setup:
            
            This method must set up the game user interface according to current game situation specified
            in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */
        
        setup: function( gamedatas )
        {            
            // Setting up player boards
            for( var player_id in gamedatas.players )
            {
                var player = gamedatas.players[player_id];
                         
                // TODO: Setting up players boards if needed
            }

            this.playerHand = new ebg.stock();
            this.playerHand.create( this, $('myhand'), this.cardwidth, this.cardheight );
            this.playerHand.image_items_per_row = 13;
            dojo.connect( this.playerHand, 'onChangeSelection', this, 'onPlayerHandSelectionChanged' );

            // Create cards types:
            for( var color=1;color<=4;color++ )
            {
                for( var value=2;value<=14;value++ )
                {
                    // Build card type id
                    var card_type_id = this.getCardUniqueId( color, value );
                    this.playerHand.addItemType( card_type_id, card_type_id, g_gamethemeurl+'img/cards.jpg', card_type_id );
                }
            }

            // Cards in player's hand
            for( var i in this.gamedatas.hand )
            {
                var card = this.gamedatas.hand[i];
                var color = card.type;
                var value = card.type_arg;
                this.playerHand.addToStockWithId( this.getCardUniqueId( color, value ), card.id );
            }
                      
            // Cards played on table
            this.tableCards = {}
            for(  var player_id in this.gamedatas.cardsontable )
            {
                var cards = this.gamedatas.cardsontable[player_id];
                this.tableCards[player_id] = new ebg.stock();
                this.tableCards[player_id].create( this, $(`playertablecards_${player_id}`), this.cardwidth, this.cardheight );
                this.tableCards[player_id].image_items_per_row = 13;
                this.tableCards[player_id].setSelectionMode(0);
                dojo.connect( this.tableCards[player_id], 'onChangeSelection', this, 'onTableCardsSelectionChanged' );

                // Create cards types:
                for( var color=1;color<=4;color++ )
                {
                    for( var value=2;value<=14;value++ )
                    {
                        // Build card type id
                        var card_type_id = this.getCardUniqueId( color, value );
                        this.tableCards[player_id].addItemType( card_type_id, card_type_id, g_gamethemeurl+'img/cards.jpg', card_type_id );
                    }
                }

                for( var i in cards )
                {
                    var card = cards[i];
                    var color = card.type;
                    var value = card.type_arg;
                    this.tableCards[player_id].addToStockWithId( this.getCardUniqueId( color, value ), card.id );
                }

            }

            if ( this.gamedatas.temporary ) {
                for( var i in this.gamedatas.temporary )
                {
                    var card = this.gamedatas.temporary[i];
                    var color = card.type;
                    var value = card.type_arg;
                    this.tableCards[this.player_id].addToStockWithId( this.getCardUniqueId( color, value ), card.id );
                }
            }

            this.updateScores( this.gamedatas.scores );
 
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

        },
       

        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {            
            switch( stateName )
            {
            
            /* Example:
            
            case 'myGameState':
            
                // Show some HTML block at this game state
                dojo.style( 'my_html_block_id', 'display', 'block' );
                
                break;
           */
           
            case 'meldCards':
                this.setMeldSelectionMode();
                break;  
            case 'thiefTurn':
                this.setThiefTurnSelectionMode();
                break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {            
            switch( stateName )
            {
            
            /* Example:
            
            case 'myGameState':
            
                // Hide the HTML block we are displaying only during this game state
                dojo.style( 'my_html_block_id', 'display', 'none' );
                
                break;
           */
           
           
            case 'dummmy':
                break;
            }               
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {                      
            if( this.isCurrentPlayerActive() )
            {            
                switch( stateName )
                {
                case 'meldCards':
                    this.addActionButton( 'meldCards_button', _('Meld Cards'), 'onMeldCards' ); 
                    break;
                case 'thiefTurn':
                    this.addActionButton( 'takeCard_button', _('Take Card'), 'onTakeCard' ); 
                    this.addActionButton( 'skipTakeCard_button', _('Skip'), 'onSkipTakeCard' ); 
                    break;
                }
            } else if (!this.isSpectator) {
                switch( stateName )
                {
                case 'meldCards':
                    this.addActionButton( 'undoMeld_button', _('Undo Meld'), 'onUndoMeld' ); 
                    break;

                } 
            }
        },        

        ///////////////////////////////////////////////////
        //// Utility methods
        
        /*
        
            Here, you can defines some utility methods that you can use everywhere in your javascript
            script.
        
        */
        getCardUniqueId: function( color, value )
        {
            return (color-1)*13+(value-2);
        },

        /*
            New scores have been provided for the game, usually occurs after
            then hand has been played.
        */
        updateScores: function( scores )
        {
            var point_types = ['thief', 'beggar', 'poorMan', 'lawyer', 'richMan', 'doctor', 'indianChief'];

            this.scores = scores;
            for( var player_id in scores )
            {
                var newScore = scores[ player_id ]['totalPoints'];
                if (this.scoreCtrl[ player_id ]) {
                    this.scoreCtrl[ player_id ].toValue( newScore );
                }

                for (var i in point_types) {
                    var point_type = point_types[i];
                    if (scores[ player_id ][`${point_type}Points`] !== "-99") {
                        $(`score${point_type}_${player_id}`).innerHTML = scores[ player_id ][`${point_type}Points`];
                    } else {
                        $(`score${point_type}_${player_id}`).innerHTML = "";
                    }
                }

                $(`scoretotal_${player_id}`).innerHTML = newScore;
            }
        },

        setThiefTurnSelectionMode: function() {
            if( this.isCurrentPlayerActive() ) {   
                for ( var player_id in this.tableCards) {
                    if (player_id != this.player_id) {
                        this.tableCards[player_id].setSelectionMode(1);
                    } else {
                        this.tableCards[player_id].setSelectionMode(0);
                    }
                }
                this.playerHand.setSelectionMode(0); 
            } else {
                for ( var player_id in this.tableCards) {
                    this.tableCards[player_id].setSelectionMode(0);
                }
                this.playerHand.setSelectionMode(0);
            }  
        },

        setMeldSelectionMode: function() {
            for ( var player_id in this.tableCards) {
                this.tableCards[player_id].setSelectionMode(0);
            }
            this.playerHand.setSelectionMode(2); 
        },

        ///////////////////////////////////////////////////
        //// Player's action
        onPlayerHandSelectionChanged: function(  )
        {
            var items = this.playerHand.getSelectedItems();

            if( items.length > 0 )
            {
                if( this.checkAction( 'meldCards' ) )
                {
                    // Can meld cards => let the player select some cards
                }
                else
                {
                    this.playerHand.unselectAll();
                }                
            }
        },

        onTableCardsSelectionChanged: function( dom_id )
        {
            // TODO - if we played thief, we can select any one card to steal

            // TODO - if we played indian chief, we need to select two cards for the kicker

            // Otherwise we cannot select any cards
            //this.tableCards[player_id].unselectAll();
        },

        onMeldCards: function()
        {
            if( this.checkAction( 'meldCards' ) )
            {
                var items = this.playerHand.getSelectedItems();

                if( items.length < 1 )
                {
                    this.showMessage( _("You must meld at least one card"), 'error' );
                    return;
                }

                if( items.length > 7 )
                {
                    this.showMessage( _("You must meld no more than seven cards"), 'error' );
                    return;
                }

                // Can meld cards => let the player select some cards
                var meld_types = ['thief', 'beggar', 'poorMan', 'lawyer', 'richMan', 'doctor', 'indianChief'];
                var meld = meld_types[items.length-1];
                if (this.scores[this.player_id][`${meld}Points`] != -99) {
                    console.log(this.scores, items.length)
                    this.showMessage( _(`You have already played the ${meld} meld.`), 'error' );
                    return;
                }

                // TODO verify that the user hasn't already used a particular meld
                
                // Meld these cards
                var to_meld = '';
                for( var i in items )
                {
                    to_meld += items[i].id+';';
                }
                this.ajaxcall( "/indianchief/indianchief/meldCards.html", { cards: to_meld }, this, function( result ) {
                }, function( is_error) { } );                
            }        
        },

        onUndoMeld: function()
        {
            this.ajaxcall( "/indianchief/indianchief/undoMeld.html", { }, this, function( result ) {
            }, function( is_error) { } );
        },

        onEndHand: function()
        {
            for ( var player_id in this.tableCards) {
                this.tableCards[player_id].updateDisplay();
            }
            if( this.checkAction( 'endHand' ) )
            {
                this.ajaxcall( "/indianchief/indianchief/endHand.html", { }, this, function( result ) {
                }, function( is_error) { } );                
            }        
        },

        onTakeCard: function()
        {
            console.log("On take card");
            var player_meld = this.tableCards[this.player_id].getAllItems();
            if( player_meld.length !== 1 ) {
                this.showMessage( _("You are not a thief and cannot take a card"), 'error' );
                return;
            }

            var items = this.tableCards[this.player_id].getSelectedItems();
            if (( items.length !== 0 ) || (player_id === this.player_id)) {
                this.showMessage( _("You cannot only steal your own card"), 'error' );
                return;
            }

            var card_given = player_meld[0];

            var card_taken = null;
            for ( var player_id in this.tableCards) {
                if (player_id == this.player_id) {
                    continue;
                }
                items = this.tableCards[player_id].getSelectedItems();
                if ( items.length == 1 ) {
                    if (card_taken !==  null) {
                        this.showMessage( _("You can only steal one card"), 'error' );
                        return;
                    } else {
                        card_taken = items[0];
                    }
                } else if (items.length > 1 ) {
                    this.showMessage( _("You can only steal one card"), 'error' );
                    return;
                }
            }

            if (card_taken === null) {
                this.showMessage( _("You must take one card or skip"), 'error' );
                return;
            }

            if( this.checkAction( 'takeCard' ) && (card_taken != null) && (card_given != null)) {
                this.ajaxcall( "/indianchief/indianchief/takeCard.html", { card_taken: card_taken.id, card_given: card_given.id }, this, function( result ) {
                }, function( is_error) { } );                
            } else {
                this.showMessage( _("You must take a card or skip"), 'error' );
                return;
            }  
        },

        onSkipTakeCard: function()
        {
            if( this.checkAction( 'takeCard' ) )
            {
                this.ajaxcall( "/indianchief/indianchief/takeCard.html", { }, this, function( result ) {
                }, function( is_error) { } );                
            }        
        },

        /*
        
            Here, you are defining methods to handle player's action (ex: results of mouse click on 
            game objects).
            
            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server
        
        */
        
        /* Example:
        
        onMyMethodToCall1: function( evt )
        {
            console.log( 'onMyMethodToCall1' );
            
            // Preventing default browser reaction
            dojo.stopEvent( evt );

            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if( ! this.checkAction( 'myAction' ) )
            {   return; }

            this.ajaxcall( "/indianchief/indianchief/myAction.html", { 
                                                                    lock: true, 
                                                                    myArgument1: arg1, 
                                                                    myArgument2: arg2,
                                                                    ...
                                                                 }, 
                         this, function( result ) {
                            
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)
                            
                         }, function( is_error) {

                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                         } );        
        },        
        
        */

        
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        setupNotifications: function()
        {            
            // TODO: here, associate your game notifications with local methods
            
            // Example 1: standard notification handling
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            
            // Example 2: standard notification handling + tell the user interface to wait
            //            during 3 seconds after calling the method in order to let the players
            //            see what is happening in the game.
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
            // 
            dojo.subscribe( 'newHand', this, "notif_newHand" );
            dojo.subscribe( 'newScores', this, "notif_newScores" );
            this.notifqueue.setSynchronous( 'newScores', 5000 );

            dojo.subscribe( 'meldCards', this, "notif_meldCards" );
            dojo.subscribe( 'undoMeld', this, "notif_undoMeld" );

            dojo.subscribe( 'playCards', this, "notif_playCards" );

            dojo.subscribe( 'takeCard', this, "notif_takeCard" );
            this.notifqueue.setSynchronous( 'takeCard', 3000 );

        },

        /*
            Notification that a new hand has started.
        */
        notif_newHand: function( notif )
        {
            this.playerHand.removeAll();

            for( var i in notif.args.cards )
            {
                var card = notif.args.cards[i];
                var color = card.type;
                var value = card.type_arg;
                this.playerHand.addToStockWithId( this.getCardUniqueId( color, value ), card.id );
            }
            for ( var player_id in this.tableCards) {
                this.tableCards[player_id].removeAll();
            }
        },

        /*
            Notification that the meld is valid and move the cards 
            on the table; note, the player only sees that his cards have
            moved to the table at this point, the move is hidden
            from all other players at this point.
        */
        notif_meldCards: function( notif )
        {
            for( var i in notif.args.cards )
            {
                var card = notif.args.cards[i];
                var color = card.type;
                var value = card.type_arg;

                this.tableCards[this.player_id].addToStockWithId(
                    this.getCardUniqueId( color, value ), card.id, `myhand_item_${card.id}`
                );
                this.playerHand.removeFromStockById( card.id );
            }

            this.tableCards[this.player_id].updateDisplay();
            this.playerHand.updateDisplay();

            this.playerHand.setSelectionMode(0);
        },

        notif_undoMeld: function( notif )
        {
            console.log("Undo Meld");
            for( var i in notif.args.cards )
            {
                var card = notif.args.cards[i];
                var color = card.type;
                var value = card.type_arg;

                this.playerHand.addToStockWithId(
                    this.getCardUniqueId( color, value ), card.id, `playertablecards_${this.player_id}_item_${card.id}`
                );
                this.tableCards[this.player_id].removeFromStockById( card.id );
            }

            this.tableCards[this.player_id].updateDisplay();
            this.playerHand.updateDisplay();

            this.playerHand.setSelectionMode(2);
        },

        notif_playCards: function( notif )
        {
            for( var i in notif.args.cards )
            {
                var card = notif.args.cards[i];
                var color = card.type;
                var value = card.type_arg;

                // A players own cards area already in their table
                if (card.location_arg != this.player_id) {
                    this.tableCards[notif.args.player_id].addToStockWithId(
                        this.getCardUniqueId( color, value ), card.id
                    );
                }
            }

            this.tableCards[notif.args.player_id].updateDisplay();
            this.playerHand.updateDisplay();
        },

        /*
            New scores have been calculated.
        */
        notif_newScores: function( notif )
        {
            this.updateScores( notif.args.scores );
        },

        /*
            New scores have been calculated.
        */
        notif_takeCard: function( notif )
        {
            // Move the card the thief gave away from their table to the other players
            var card_given = notif.args.card_given;
            var card_taken = notif.args.card_taken;

            var thief_id = notif.args.player_id;
            var thieved_id = notif.args.taken_from_player_id;

            var taken_card_dom_id = `playertablecards_${thieved_id}_item_${card_taken.id}`;
            var given_card_dom_id = `playertablecards_${thief_id}_item_${card_given.id}`;

            this.tableCards[thieved_id].addToStockWithId(
                this.getCardUniqueId( card_given.type, card_given.type_arg ), card_given.id, given_card_dom_id
            );
            this.tableCards[thief_id].removeFromStockById(
                card_given.id
            );

            // Move the taken card into the thiefs hand
            if (thief_id == this.player_id ) {
                this.playerHand.addToStockWithId(
                    this.getCardUniqueId( card_taken.type, card_taken.type_arg ), card_taken.id, taken_card_dom_id
                );
            }
            this.tableCards[thieved_id].removeFromStockById(
                card_taken.id
            );
       }
   });             
});
