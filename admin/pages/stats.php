<?php
if (!defined('adminPage')) {
    exit("Direct access not premitted.");
}
?>
<script src="../js/Chart/Chart.js" type="text/javascript"></script>
<div class='history-chart-container'>
    <div class='history-chart'></div>
    <div class="input-group">
        <div class="input-group-btn dropup">
            <button type="button" class="btn btn-default  dropdown-toggle pull-left" data-toggle="dropdown">
                Select Months <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" role="menu">
                <li><a href="#" onclick="setMonths('12')">12</a></li>
                <li><a href="#" onclick="setMonths('11')">11</a></li>
                <li><a href="#" onclick="setMonths('10')">10</a></li>
                <li><a href="#" onclick="setMonths('9')">9</a></li>
                <li><a href="#" onclick="setMonths('8')">8</a></li>
                <li><a href="#" onclick="setMonths('7')">7</a></li>
                <li><a href="#" onclick="setMonths('6')">6</a></li>
                <li><a href="#" onclick="setMonths('5')">5</a></li>
                <li><a href="#" onclick="setMonths('4')">4</a></li>
                <li><a href="#" onclick="setMonths('3')">3</a></li>
                <li><a href="#" onclick="setMonths('2')">2</a></li>
                <li class="divider"></li>
                <li><a href="#" onclick="selectOther()">Other</a></li>
            </ul>
        </div>
        <input type='number' class='form-control selOther' style='display:none;' id='otherMonths' onchange='setMonths("0")'>
    </div>
    <input type='hidden' value='6' name="numMonths" id='numMonths'>

</div>


<div class='tier-chart-continater'>
<!-- <div class='chartCheckbox'><input type='checkbox' id='allUsers' onchange='getTiers()'><div id='allText'>Show all users</div></div> -->
    <div class='tier-chart'></div>
    <button class='btn btn-default btn-lg pull-right' onclick='getTiers()' id='allBtn' value='0'>Show all users</button>
</div>




<script type="text/javascript">
    function setMonths(num) {
        if (num == 0) {
            var other = $('#otherMonths').val();
            if (other < 2) {
                other = 2;
            }
            $('#numMonths').val(other);
        } else {
            $('#numMonths').val(num);
            $('#otherMonths').val('').hide();
        }


        getHist();
    }

    $(document).ready(function() {
        getHist();
        getTiers();
    });

    function getHist() {
        var varNumMonths = $('#numMonths').val();
        $.ajax({
            type: 'POST',
            url: 'pages/ajax/history-chart.php',
            data: {numMonths: varNumMonths, ajax: 1},
            success: function(result) {
                $('.history-chart').html(result);
                var ctx = document.getElementById('histCanvas').getContext('2d');
                window.myLine = new Chart(ctx).Line(lineChartData, {
                    responsive: true
                });
            }});
    }
    function selectOther() {
        $('.selOther').show();
    }


    function getTiers() {
        // var varAll = $('#allUsers').prop('checked');
        varAll = $('#allBtn').val();
        if (varAll == 1) {
            $('#allBtn').html('Show active users');
            $('#allBtn').val('0');
        } else {
            $('#allBtn').html('Show all users');
            $('#allBtn').val('1');
        }
        $.ajax({
            type: 'POST',
            url: 'pages/ajax/users-chart.php',
            data: {all: varAll, ajax: 1},
            success: function(result) {
                $('.tier-chart').html(result);


                var ctx = document.getElementById("usersCanvas").getContext("2d");
                window.myDoughnut = new Chart(ctx).Doughnut(doughnutData, {responsive: true});

            }});
    }
</script>



