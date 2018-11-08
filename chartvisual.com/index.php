<?

//so ajax won't bitch at us
header('Access-Control-Allow-Origin: *');
set_time_limit(0);
ini_set('mysql.connect_timeout',300);
ini_set('max_execution_time', 300);
ini_set('default_socket_timeout', 300);
session_start();


if(empty($_SESSION['pairs']))
{
    $result = json_decode(file_get_contents('http://api.chartvisual.com/cv/pairs'),true);
    if(intval($result['response'])==1)
    {
        $_SESSION['pairs']= $result['data'];
    } else {
        $_SESSION['pairs'] = array(
            'eurusd',
            'usdjpy',
            'gbpusd',
            'audusd',
            'usdcad',
            'usdchf',
            'nzdusd',
            'gbpjpy',
            'eurjpy',
            'eurgbp',
            'eurchf',
            'nzdusd'
        );
    }
    $currency_pairs_supported = $_SESSION['pairs'];
} else {
    $currency_pairs_supported = $_SESSION['pairs'];
}

/**  ==========================
 *   SUPPORTED FOREX TIMEFRAMES
 *   ==========================
 */

$currency_time_frames_supported = array(
    //'m1',
    'm5',
    'm15',
    'm30',
    'h1',
    'h4',
    'd1',
    'w1',
    'mn1'
);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>ChartVisual.com - World-class Forex Tools</title>

    <link href="css/bootstrap.min.css" rel="stylesheet">

    <link href="css/bootstrap-theme.min.css" rel="stylesheet">
    <link href="css/bootstrap-switch.min.css" rel="stylesheet">
    <link href="css/bootstrap-multiselect.css" rel="stylesheet">
    <link href="css/tooltip.css" rel="stylesheet">

    <link href="css/main.css" rel="stylesheet">
    <script>

        if(window.location.pathname.indexOf('demo') == -1 && window.location.hash.indexOf('demo')==-1)
        {
            if(window==window.top) {
                alert('Access denied');
                window.location.href = 'http://www.chartvisual.com';
            }
        }


    </script>
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>

    <![endif]-->
    <link rel="apple-touch-icon" sizes="57x57" href="/meta/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/meta/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/meta/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/meta/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/meta/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/meta/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/meta/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/meta/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/meta/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="/meta/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/meta/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/meta/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/meta/favicon-16x16.png">
    <link rel="manifest" href="/meta/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/meta/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
</head>
<body>
<div id="slider" class="slider">
    <div class="handle">
        <span class="glyphicon glyphicon-th-list"><span class="badge notification-value" id="notification-badge"></span> </span>
    </div>
    <h3 href="#" class="list-group-item active">
        <span class="glyphicon glyphicon-exclamation-sign" style="color:#fff;font-size:12px;"></span>
        Notifications<span class="badge notification-value" style="margin-right:20px;">12</span>
    </h3>
    <ul class="list-group" id="notifications">


    </ul>
</div>
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#"><img id="logo" src="img/logo.png"></a>
        </div>
        <div id="cv-tabs" class="collapse navbar-collapse">
            <ul class="nav navbar-nav cv-tabs">
                <li id="tab-tools" class="active cv-tab"><a href="#">Tools</a></li>
                <li id="tab-settings" class="cv-tab"><a href="#">Settings</a></li>
                <li id="tab-notifications" class="cv-tab"><a href="#">Notifications</a></li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>

<div class="container"id="cv-container">

<!--
    <ul class="nav nav-tabs" id="cv-tabs">
        <li role="presentation" class="cv-tab active" id="tab-tools"><a href="#">Tools</a></li>
        <li role="presentation" class="cv-tab" id="tab-settings"><a href="#">Settings</a></li>
    </ul>-->
    <div  class="panel panel-default">

        <div class="panel-body container" id="cv-settings" style="width:95%">
            <h3>Settings</h3>
            <div class="panel panel-default">
                <div class="panel-heading"><h4>General</h4></div>
                <ul class="list-group">
                    <li class="list-group-item">
                        <span>
                            Email notifications
                        <br/>
                            Your email address: <input name="email" id="user_email"/>
                        </span>
                        <span class="pull-right">
                            <input style="margin-left:15px;float:right;display:inline-block;" data-size="mini" class="pull-right"  name="checkbox" type="checkbox" name="cv_notifications_email" id="cv_notifications_email">
                        </span>
                    </li>
                    <li class="list-group-item">
                        <span>
                            Play notification sound
                        </span>
                        <span class="pull-right">
                            <input style="margin-left:15px;float:right;display:inline-block;" data-size="mini" class="pull-right"  name="checkbox" type="checkbox" name="cv_notifications_sound" id="cv_notifications_sound">
                        </span>
                    </li>
                    <li class="list-group-item">
                        <span>
                            Display in-browser notification on page
                        </span>
                        <span class="pull-right">
                            <input style="margin-left:15px;float:right;display:inline-block;" data-size="mini"  class="pull-right"  name="checkbox" type="checkbox" name="cv_notifications_browser" id="cv_notifications_browser">
                        </span>
                    </li>
                    <li class="list-group-item">
                        <span>
                            Display notification in browser title bar
                        </span>
                        <span class="pull-right">
                            <input style="margin-left:15px;float:right;display:inline-block;"  data-size="mini" class="pull-right"  name="checkbox" type="checkbox" name="cv_notifications_favicon" id="cv_notifications_favicon">
                        </span>
                    </li>
                    <li class="list-group-item">
                        <span>
                            Update notification feed
                        </span>
                        <span class="pull-right">
                            <input style="margin-left:15px;float:right;display:inline-block;"  data-size="mini" class="pull-right"  name="checkbox" type="checkbox" name="cv_notifications_feed" id="cv_notifications_feed">
                        </span>
                    </li>
                </ul>
            </div>
            <br/><br/>

            <div class="panel panel-default">
                <div class="panel-heading"><h4>Strength Meter</h4></div>
                <ul class="list-group" id="cv_settings_csi">
                    <li class="list-group-item">
                        <span>
                            Notify me when currency pair
                            reaches CSI level
                             <select class="multi-select" multiple="multiple">
                                <option value="-4">Exhaustive Bearish</option>
                                 <option value="-3">Strong Bearish</option>

                                 <option value="-2">Moderate Bearish</option>

                                 <option value="-1">Weak Bearish</option>

                                 <option value="1">Weak Bullish</option>

                                 <option value="2">Moderate Bullish</option>

                                 <option value="3">Strong Bullish</option>

                                 <option value="4">Exhaustive Bullish</option>

                             </select>
                        </span>
                        <span class="pull-right">
                            <input style="margin-left:15px;float:right;display:inline-block;" data-size="mini" class="pull-right"  type="checkbox" name="checkbox">
                        </span>
                    </li>

                </ul>
            </div>
            <br/><br/>


            <div class="panel panel-default">
                <div class="panel-heading"><h4>Trend Detector</h4>
                </div>

                <ul class="list-group"  id="cv_settings_laser">
                    <li class="list-group-item">
                        <span>
                            Notify me when currency pair
                             begins trending as
                             <select class="multi-select" multiple="multiple">

                                 <option value="1">Buy trend</option>

                                 <option value="2">Sell trend</option>

                             </select>
                        </span>
                        <span class="pull-right">
                            <input style="margin-left:15px;float:right;display:inline-block;" data-size="mini" class="pull-right"  type="checkbox" name="checkbox">
                        </span>
                    </li>
                </ul>
            </div>

            <br/><br/>



            <div class="panel panel-default">
                <div class="panel-heading"><h4>Scalping Hero</h4></div>
                <ul class="list-group"  id="cv_settings_scalper">
                    <li class="list-group-item">
                        <span>
                            Notify me when currency pair displays

                             <select class="multi-select" multiple="multiple">
                                 <option value="1">Buy scalp</option>
                                 <option value="2">Sell scalp</option>
                             </select>
                        </span>
                        <span class="pull-right">
                            <input style="margin-left:15px;float:right;display:inline-block;" data-size="mini" class="pull-right"  type="checkbox" name="checkbox">
                        </span>
                    </li>

                </ul>
            </div>



        </div>

        <div class="panel-body container" id="cv-tools">


                <div id="chart-controls" class="col-lg-2">
                    <div class="list-group">
                        <span class="list-group-item">
                            <span class="glyphicon glyphicon-object-align-bottom pull-left" style="font-size:12px;margin-right:5px;"> </span>
                            Strength Meter
                            <div class="text-center" style="padding-top:5px;">
                                <input data-size="mini"  name="checkbox" type="checkbox" id="btn-csi"/>
                            </div>
                        </span>
                        <span class="list-group-item">
                            <span class="glyphicon glyphicon-signal pull-left" style="font-size:12px;margin-right:5px;"> </span>
                            Trend Detector
                            <div class="text-center" style="padding-top:5px;">
                                <input data-size="mini"  name="checkbox" type="checkbox" id="btn-laser"/>
                            </div>
                        </span>
                        <span class="list-group-item">
                            <span class="glyphicon glyphicon-flash pull-left" style="font-size:12px;margin-right:5px;"> </span> Scalping Hero
                            <div class="text-center" style="padding-top:5px;">
                                <input data-size="mini"  name="checkbox" type="checkbox" id="btn-scalper"/>
                            </div>
                        </span>
                    </div>




                        <div style="margin-top:10px;">
                            <div class="dropdown"  id="chart_currency_pair">
                                <button class="btn-sm btn btn-default dropdown-toggle btn-block" type="button" id="btn-select-currency-pair" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                    Pair: <b>GBPUSD</b>
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <?
                                    foreach($currency_pairs_supported as $item)
                                    {
                                        print '<li><a href="#" data-value=" ' . $item .' ">'. strtoupper($item).'</a></li>';
                                    }
                                    ?>
                                </ul>
                            </div>
                            <div class="dropdown" id="chart_timeframe"  style="margin-top:5px;">
                                <button class="btn btn-default dropdown-toggle btn-block" type="button" id="btn-select-timeframe" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                    Timeframe: <b>M1</b>
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" >
                                    <?
                                    foreach($currency_time_frames_supported as $item)
                                    {
                                        print '<li><a href="#" data-value="' . $item . '">'. strtoupper($item).'</a></li>';
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>


                            <button class="btn-xs btn btn-default btn-block"  type="button" id="btn-mouseinfo" style="margin-top:20px;" >
                                <label>
                                    <input type="checkbox" id="checkbox-mouseinfo"/> Mouseover details
                                </label>
                            </button>
                            <button class="btn-xs btn btn-default btn-block"  type="button" id="btn-autoupdate" style="" >
                                <label>
                                    <input type="checkbox" id="checkbox-autoupdate"/> Auto-update
                                </label>
                            </button>


                        <button class="btn-xs btn btn-default btn-block"  type="button" id="btn-alerts" style="" >
                            <label>
                                <input type="checkbox" id="checkbox-alerts"/> Enable Alerts
                            </label>
                        </button>

                </div>


                <div class="col-lg-10" id="chart-info-panel">

                    <div class="row">
                        <div class="list-group-item col-sm-6" id="chart-trend-detector">
                            <div class="row">
                                <span class="col-sm-12">
                                    <span class="glyphicon glyphicon-signal" style="font-size:12px;"></span>
                                    <strong>Trend Detector</strong>
                                </span>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="row">
                                        <span class="col-sm-6">Trend:</span>
                                        <span class="col-sm-5" id="ctd-trend"><strong><span class="na">N/A</span></strong></span>
                                    </div>
                                    <div class="row">
                                        <span class="col-sm-6">Overall trades:</span>
                                        <span class="col-sm-5" id="ctd-overall"><strong><span class="na">N/A</span></strong></span>
                                    </div>
                                    <div class="row">
                                        <span class="col-sm-6">Profitable trades:</span>
                                        <span class="col-sm-5" id="ctd-profit"><strong><span class="na">N/A</span></strong></span>
                                    </div>
                                    <div class="row">
                                        <span class="col-sm-6">Success Rate:</span>
                                        <span class="col-sm-5" id="ctd-success"><strong><span class="na">N/A</span></strong></span>
                                    </div>
                                </div>


                                <div class="col-sm-6">
                                    <div class="row">
                                        <span class="col-sm-6">Best Profit:</span>
                                        <span class="col-sm-5" id="ctd-bestprofit"><strong><span class="na">N/A</span></strong></span>
                                    </div>
                                    <div class="row">
                                        <span class="col-sm-6">Average Profit:</span>
                                        <span class="col-sm-5" id="ctd-avgprofit"><strong><span class="na">N/A</span></strong></span>
                                    </div>
                                    <div class="row">
                                        <span class="col-sm-6">Minimum Move:</span>
                                        <span class="col-sm-5" id="ctd-mm"><strong><span class="na">N/A</span></strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>





                        <div class="list-group-item col-sm-3" id="strengthometer">
                            <div class="row">
                                <span class="col-sm-12">
                                    <span class="glyphicon glyphicon-object-align-bottom" style="font-size:12px;"></span>
                                    <strong>Strength Meter</strong>
                                </span>
                            </div>
                            <br>
                            <div class="row">
                                <span class="col-sm-5">Pair strength:</span>
                                <span class="col-sm-7" id="som-strength"><strong><span class="na">N/A</span></strong></span>
                            </div>
                        </div>


                        <div class="list-group-item col-sm-2" id="scalphero">
                            <div class="row">
                                <span class="col-sm-12">
                                    <span class="glyphicon glyphicon-flash" style="font-size:12px;"></span>
                                    <strong>Scalping Hero</strong>
                                </span>
                            </div>
                            <br>
                            <div class="row">
                                <span class="col-sm-5">Indication:</span>
                                <span class="col-sm-7" id="sh-strength"><strong><span class="na">N/A</span></strong></span>
                            </div>
                        </div>
                    </div>


                    <div class="" id="chart-data" style="padding-top:5px;">
                            <div class="row">
                                <span class="col-sm-2">Date:</span>
                                <span class="col-sm-6  text-left" id="mkt-date"><strong><span class="na">N/A</span></strong></span>
                            </div>
                            <div class="row">
                                <span class="col-sm-2">Price:</span>
                                <span class="col-sm-6  text-left" id="mkt-price"><strong><span class="na">N/A</span></strong></span>
                            </div>
                        <div class="row">
                            <span class="col-sm-2">Current Price:</span>
                            <span class="col-sm-6  text-left" id="mkt-current-price"><strong><span class="na">N/A</span></strong></span>
                        </div>
                            <div class="row" id="ticktimer">
                                <span class="col-sm-2">Next tick:</span>
                                <span class="col-sm-6 text-left" id="tickcountdown"><strong><span class="na">N/A</span></strong></span>
                            </div>

                    </div>



                    <div class="row" style="padding:0px;">
                        <div id="chart-market-container">
                            <img class="loading" src="img/loading.gif" class="text-center" />
                        </div>
                    </div>

                </div>
        </div>


        <div class="panel-body container" id="cv-notifications" style="width:95%;display:none;">
            <h3>Email Notifications</h3>
            <div class="panel panel-default">
                <div class="panel-heading"><h4 style="display:inline-block;margin-right:20px;">Scalping Hero</h4>

                    <div class="dropdown"  id="chart_currency_pair2" style="display:inline-block;">
                        <button class="btn-sm btn btn-default dropdown-toggle" type="button" id="btn-select-currency-pair2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                            Pair: <b>GBPUSD</b>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <?
                            foreach($currency_pairs_supported as $item)
                            {
                                print '<li><a href="#" data-value=" ' . $item .' ">'. strtoupper($item).'</a></li>';
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="dropdown" id="chart_timeframe2"  style="margin-top:5px;display:inline-block;">
                        <button class="btn btn-default dropdown-toggle" type="button" id="btn-select-timeframe2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                            Timeframe: <b>M1</b>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" >
                            <?
                            foreach($currency_time_frames_supported as $item)
                            {
                                print '<li><a href="#" data-value="' . $item . '">'. strtoupper($item).'</a></li>';
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="dropdown" id="indicator_data"  style="margin-top:5px;display:inline-block;">
                        <button class="btn btn-default dropdown-toggle" data-value="1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                            Type: <b>Buy Scalp</b>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" >
                          <li><a href="#" data-value="1">Buy Scalp</a></li>
                          <li><a href="#" data-value="2">Sell Scalp</a></li>
                        </ul>
                    </div>






                    <button class="btn btn-success" type="button" id="notification-add" style="display:inline-block;">
                    Add Alert
                    </button>
                    <button class="btn btn-primary" type="button" id="notifications-save" style="display:inline-block;">
                        Save
                    </button>


                </div>
                <ul class="list-group notification-list">

                    <li class="list-group-item notification-row template" style="display:none;">
                        <span> <img src="img/img-bell-icon.png" width="50" height="50"/>
                                <h4 class="notification-element notification-currency"></h4>
                                <h4 class="notification-element notification-timeframe"></h4>
                                <h4 class="notification-element notification-type"></h4>
                        </span>
                        <span class="pull-right">
                            <div class="dropdown notification-action" style="margin-top:5px;display:inline-block;">
                                <button class="btn btn-default dropdown-toggle" data-value="1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                    Action: <b>Enable</b>
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" >
                                    <li><a href="#" data-value="1">Enabled</a></li>
                                    <li><a href="#" data-value="0">Disabled</a></li>
                                    <li><a href="#" data-value="2">Delete Notification</a></li>
                                </ul>
                            </div>
                        </span>
                    </li>

                </ul>


            </div>
            <br/><br/>




        </div>



    </div>

<footer class="footer">
    <p>&copy; ChartVisual Incorporated 2016</p>
</footer>
</div>




</div>

<script src="js/jquery.min.js"></script>
<script src='js/jquery-ui.min.js'></script>
<script src="js/bootstrap.min.js"></script>
<script src='js/d3.min.js'></script>
<script src='js/techan.min.js'></script>
<script src="js/texture.js"></script>
<script src="js/notify.min.js"></script>
<script src="js/bootstrap-switch.min.js"></script>
<script src="js/bootstrap-multiselect.js"></script>
<script src="js/bootstrap-multiselect-collapsible-groups.js"></script>
<script src="js/slide.js"></script>
<script src="js/date.js"></script>
<script src="js/tooltip.js"></script>
<script src="js/favicon.js"></script>
<script src="js/main.js"></script>
<!--
<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-67836680-1', 'auto');
    ga('send', 'pageview');

</script>
-->
</body>
</html>

