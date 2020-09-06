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
            <th>Thief<br/><small>1 card</small></th>
            <th>Beggar<br/><small>2 cards</small></th>
            <th>Poor Man<br/><small>3 cards</small></th>
            <th>Laywer<br/><small>4 cards</th>
            <th>Rich Man<br/><small>5 cards</th>
            <th>Doctor<br/><small>6 cards</th>
            <th>Indian Chief<br/><small>7 cards</th>
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
