{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- IndianChief implementation : © Michael Ihde <mike.ihde@randomwalking.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------
-->

<div id="playertables">

    <!-- BEGIN player -->
    <div class="playertable whiteblock">
        <div class="playertablename" style="color:#{PLAYER_COLOR}">
            {PLAYER_NAME}
        </div>
        <div id="playertablecards_{PLAYER_ID}">
        </div>
    </div>
    <!-- END player -->

</div>

<div id="myhand_wrap" class="whiteblock">
    <h3>{MY_HAND}</h3>
    <div id="myhand">
    </div>
</div>

<div id="points_wrap" class="whiteblock">
    <h3>Points</h3>
    <table class="scoretable">
        <tr class="scoretableheader">
            <th>Player</th>
            <th>Thief<br/>
              <div class="meld_tooltip">
                <small>1 card</small>
                <span class="meld_tooltiptext">
                Score face value, optionally steal one card.
                </span>
              </div>
            </th>
            <th>Beggar<br/>
              <div class="meld_tooltip">
                <small>2 cards</small>
                <span class="meld_tooltiptext">
                Score two points for each match with another players meld.
                </span>
              </div>
            </th>
            <th>Poor Man<br/>
              <div class="meld_tooltip">
                <small>3 cards</small>
                <span class="meld_tooltiptext">
                Score face value for spades only.
                </span>
              </div>
            </th>
            <th>Laywer<br/>
              <div class="meld_tooltip">
                <small>4 cards</small>
                <span class="meld_tooltiptext">
                If face values add to 25, score 25.  Otherwise score nothing. 
                </span>
              </div>
            </th>
            <th>Rich Man<br/>
              <div class="meld_tooltip">
                <small>5 cards</small>
                <span class="meld_tooltiptext">
                Add face values together, score total as a negative number.
                </span>
              </div>
            </th>
            <th>Doctor<br/>
              <div class="meld_tooltip">
                <small>6 cards</small>
                <span class="meld_tooltiptext">
                Meld must have one heart, one ace, and all cards must have a different rank.
                Select one suit and receive ten points for each card of that suit in your meld.
                </span>
              </div>
            </th>
            <th>Indian Chief<br/>
              <div class="meld_tooltip">
                <small>7 cards</small>
                <span class="meld_tooltiptext">
                Make a five-card poker hand and a two card "kicker".  Add the two cards together
                and score the last digit of the total.  Score the poker hand as follows:
                <ul>
                    <li>50 for five of a kind</li>
                    <li>45 for a straight flush</li>
                    <li>40 for four of a kind</li>
                    <li>35 for a full house</li>
                    <li>30 for a flush</li>
                    <li>25 for a straight</li>
                    <li>20 for three of a kind</li>
                    <li>15 for two pairs</li>
                    <li>10 for one pair</li>
                    <li>5 for no pair</li>
                </ul>
                </span>
              </div>
            </th>
            <th>Total</th>
        </tr>
        <!-- BEGIN player_score -->
        <tr class="scoretablerow">
            <td style="color:#{PLAYER_COLOR}">{PLAYER_NAME}</td>
            <td class="playerscore" id="scorethief_{PLAYER_ID}"></td>
            <td class="playerscore" id="scorebeggar_{PLAYER_ID}"></td>
            <td class="playerscore" id="scorepoorMan_{PLAYER_ID}"></td>
            <td class="playerscore" id="scorelawyer_{PLAYER_ID}"></td>
            <td class="playerscore" id="scorerichMan_{PLAYER_ID}"></td>
            <td class="playerscore" id="scoredoctor_{PLAYER_ID}"></td>
            <td class="playerscore" id="scoreindianChief_{PLAYER_ID}"></td>
            <td class="playerscore" id="scoretotal_{PLAYER_ID}"></td>
        </tr>
        <!-- END player_score -->
    </table>
</div>

<script type="text/javascript">

var jstpl_cardontable = '<div class="cardontable" id="cardontable_${player_id}" style="background-position:-${x}px -${y}px">\
                        </div>';

</script> 

{OVERALL_GAME_FOOTER}
