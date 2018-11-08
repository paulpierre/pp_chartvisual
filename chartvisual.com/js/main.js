d3.selection.prototype.moveToFront = function() {
    return this.each(function(){
        this.parentNode.appendChild(this);
    });
};

/** ===============================
 *  Load Settings from LocalStorage
 *  ===============================
 */
var hasCSI,
    hasMarket,
    hasLaser,
    hasScalper,
    c_price,c_start,c_end,
    toggleCSI = localStorage.getItem('toggleCSI'),
    toggleLaser = localStorage.getItem('toggleLaser'),
    toggleScalper = localStorage.getItem('toggleScalper'),
    isAutoUpdate = localStorage.getItem('isAutoUpdate'),
    isMouseInfo = localStorage.getItem('isMouseInfo'),
    isAlerts = localStorage.getItem('isAlerts'),
    cv_currency_pair = localStorage.getItem('pair'),
    cv_timeframe  = localStorage.getItem('timeframe'),
    cv_notifications_email  = localStorage.getItem('cv_notifications_email'),
    cv_user_email = localStorage.getItem('cv_user_email'),
    cv_notifications_sound  = localStorage.getItem('cv_notifications_sound'),
    cv_notifications_browser  = localStorage.getItem('cv_notifications_browser'),
    cv_notifications_favicon  = localStorage.getItem('cv_notifications_favicon'),
    cv_notifications_feed  = localStorage.getItem('cv_notifications_feed'),
    cv_newsfeed_data  = localStorage.getItem('cv_newsfeed_data'),
    ls_csi = localStorage.getItem('cv_settings_csi'),
    ls_laser = localStorage.getItem('cv_settings_laser'),
    ls_scalper = localStorage.getItem('cv_settings_scalper'),
    cv_settings_csi  = (ls_csi)?JSON.parse(ls_csi):null,
    cv_settings_laser  = (ls_laser)?JSON.parse(ls_laser):null,
    cv_settings_scalper  = (ls_scalper)?JSON.parse(ls_scalper):null,
    cv_tick_data  = localStorage.getItem('cv_tick_data'),
    cv_domain = 'api.chartvisual.com',
    cv_size = 75,
    /*
    csi_1 = (cv_settings_csi)?cv_settings_csi.csi_1:false,
    csi_2 = (cv_settings_csi)?cv_settings_csi.csi_2:false,
    csi_3 = (cv_settings_csi)?cv_settings_csi.csi_3:false,
    csi_4 = (cv_settings_csi)?cv_settings_csi.csi_4:false,
    csi_5 = (cv_settings_csi)?cv_settings_csi.csi_5:false,
    csi_6 = (cv_settings_csi)?cv_settings_csi.csi_6:false,
    csi_7 = (cv_settings_csi)?cv_settings_csi.csi_7:false,
    csi_8 = (cv_settings_csi)?cv_settings_csi.csi_8:false,*/
    _data,
    _profits,
    _interval,
    _countdown,
    audio = {
        warn:new Audio('/warn.mp3'),
        notify:new Audio('/notify.mp3')
    }


cv_settings();


d3.selection.prototype.moveToBack = function() {
    return this.each(function() {
        var firstChild = this.parentNode.firstChild;
        if (firstChild) {
            this.parentNode.insertBefore(this, firstChild);
        }
    });
};


var vol,barWidth;

var margin = {top: 20, right: 60, bottom: 320, left: 0},
    width = 910 - margin.left - margin.right,
    height = 800 - margin.top - margin.bottom ;


var padding = {top:-50,bottom:0,left:0,right:-50};

var parseDate = d3.time.format("%Y-%m-%d %H:%M:%S").parse;

var x = techan.scale.financetime()
    .range([0, width +padding.right])

var y = d3.scale.linear()
    .range([height+padding.top , 0])

var candlestick = techan.plot.candlestick()
    .xScale(x)
    .yScale(y);


var svg = d3.select("#chart-market-container").append("svg")
    .attr("width", width + margin.left + margin.right)
    .attr("height", height + margin.top + margin.bottom)
    .append("g")
    .attr("transform", "translate(" + margin.left + "," + margin.top + ")")

var xAxis = d3.svg.axis()
    .scale(x)
    .tickFormat(d3.time.format("%d %b %H:%M"))
    .orient("bottom");


var yAxis = d3.svg.axis()
    .scale(y)
    .tickFormat(d3.format(',.5fs'))
    .orient("right");


var yRightAxis = d3.svg.axis()
    .scale(y)
    .orient("right");

var ohlcAnnotation = techan.plot.axisannotation()
    .axis(yAxis)
    .format(d3.format(',.5fs'));

var ohlcRightAnnotation = techan.plot.axisannotation()
    .axis(yRightAxis)
    .format(d3.format(',.5fs'))
    .width(60)
    .translate([width, 0]);


var yVolume = d3.scale.linear()
    .domain([-4,4])
    .range([-30, 30]);

var xVolume = techan.scale.financetime()
    .range([0, width]);


var percentAnnotation = techan.plot.axisannotation()
    .axis(yAxis)
    .translate([width, 0])
    .format(d3.format(',.5fs'));

var currentPrice = techan.plot.supstance()
    .xScale(x)
    .yScale(y)
    .annotation([ percentAnnotation])

var laser_offset = 50;
var laser_buy_offset = 70;
var laser_sell_offset = -70;

var laser_sell = d3.svg.line()
    .interpolate("basis")
    .x(function(d) { return x(d.date); })
    .y(function(d) {
        return y(d.close);
    })
    .defined(function(d,i){

        if(typeof d.date !== "undefined" && d.laser_bear >0 && d.laser_bull==0)
        {
            //console.log('d: ' + _data.length  + 'i: ' + i );

            return d;
        }
        else {

            if(typeof d.date !== "undefined" && d.laser_bear >0 && d.laser_bull >0)
            {
                return; // remove this line to add the circles back in
                var color;
                if(d.date.buy > 0) color = "#da5454"; else color ="#6ada54";

                svg.append("circle")
                    .attr("cx", x(d.date))
                    .attr("cy", y(d.close)-laser_offset)
                    .attr("fill",color)
                    .attr("r", 5);

                return d;

            }

            else
                return false
        }
    });
var laser_buy = d3.svg.line()
    .interpolate("basis")
    .x(function(d) { return x(d.date); })
    .y(function(d,i) {
        return y(d.close) })
    .defined(function(d,i){
        //console.log(d.i);
        if(typeof d.date !== "undefined" && d.laser_bull >0){
            //console.log('d: ' + _data.length  + 'i: ' + i );

            return d;

        }
        else return false;
    });

var csi_x_axis = d3.svg.axis()
    .scale(xVolume)
    .outerTickSize(0)
    .tickFormat("")
    .orient("bottom");

var csi_y_axis = d3.svg.axis()
    .scale(yVolume)
    .outerTickSize(0)
    .orient("right");

var volume = techan.plot.volume()
    .xScale(x)
    .yScale(yVolume);

var csi_colors = {
    "1":"#d0fb99",
    "2":"#afe2ad",
    "3":"#7fbf9c",
    "4":"#418686",
    "-1":"#fb9b00",
    "-2":"#fb7300",
    "-3":"#fa4602",
    "-4":"#f90006"
};

var csi_annotation = techan.plot.axisannotation()
    .axis(xAxis)
    .width(160)
    .translate([0, height+110]);


var timeAnnotation = techan.plot.axisannotation()
    .axis(xAxis)
    .format(d3.time.format('%d %b %Y %H:%M'))
    .width(150)
    .translate([0, height+20]);

var crosshair = techan.plot.crosshair()
    .xScale(x)
    .yScale(y)
    .xAnnotation([timeAnnotation])
    .yAnnotation([ohlcRightAnnotation])
    .on("enter", enter)
    .on("out", out)
    .on("move", move);

var coordsText = svg.append('text')
    .style("text-anchor", "middle")
    .attr("class", "coords")
    .attr("x", width / 2  )
    .attr("y", 15);

var csi_text = svg.append('text')
    .style("text-anchor", "middle")
    .attr("class", "csi_text")
    .attr("x", width / 2 )
    .attr("y", 30);

var laser_text = svg.append('text')
    .style("text-anchor", "middle")
    .attr("class", "csi_text")
    .attr("x", width / 2 )
    .attr("y", 45);

var scalper_text = svg.append('text')
    .style("text-anchor", "middle")
    .attr("class", "scalper_text")
    .attr("x", width / 2 )
    .attr("y", 60);


/*
var l1 = svg.append('text')
    .style("text-anchor", "left")
    .attr("y", 0)
    .attr("x",-50);
var l2 = svg.append('text')
    .style("text-anchor", "left")
    .attr("y", 18)
    .attr("x",-50);
var l3 = svg.append('text')
    .style("text-anchor", "left")
    .attr("y", 36)
    .attr("x",-50);
var l4 = svg.append('text')
    .style("text-anchor", "left")
    .attr("y", 54)
    .attr("x",-50);
var l5 = svg.append('text')
    .style("text-anchor", "left")
    .attr("y", 72)
    .attr("x",-50);
var l6 = svg.append('text')
    .style("text-anchor", "left")
    .attr("y", 90)
    .attr("x",-50);
*/



function cv_server_store()
{

    var _settings = {
        "cv_notifications_email":cv_notifications_email,
        "cv_newsfeed_data":cv_newsfeed_data,
        "cv_notifications_browser":cv_notifications_browser,
        "cv_notifications_favicon":cv_notifications_favicon,
        "cv_notifications_feed":cv_notifications_feed,
        "cv_notifications_sound":cv_notifications_sound,
        "cv_user_email":cv_user_email,
        "cv_settings_csi":cv_settings_csi,
        "cv_settings_laser":cv_settings_laser,
        "cv_settings_scalper":cv_settings_scalper,
        "cv_tick_data":cv_tick_data,
        "isAlerts":isAlerts,
        "isAutoUpdate":isAutoUpdate,
        "isMouseInfo":isMouseInfo,
        "pair":cv_currency_pair,
        "timeframe":cv_timeframe,
        "toggleCSI":toggleCSI,
        "toggleLaser":toggleLaser,
        "toggleScalper":toggleScalper
    };

    var _e = (cv_notifications_email)?1:0;


    var data = {"settings":JSON.stringify(_settings),"email_notifications":_e};
    console.log('cv_server_store:' + JSON.stringify(data));


    if(typeof cv_user_email == 'undefined')
    {
        console.log('Unable to save settings, no email set..');
        return false;
    }
    $.post( "http://" + cv_domain + '/user/update/' + cv_user_email , data )
        .done(function( data ) {
            console.log('server response: ' + data);

            if(data.response.code == 0)
                alert(data.data.message);

        });
}

function cv_settings()
{


    /** ========================
     *  Update Settings controls
     *  ========================
     */

    if(toggleCSI == "true") toggleCSI = true; else toggleCSI = false;
    if(toggleLaser == "true") toggleLaser = true; else toggleLaser = false;
    if(toggleScalper == "true") toggleScalper = true; else toggleScalper = false;


    if(isAutoUpdate == "true") {
        isAutoUpdate = true;
        $('#checkbox-autoupdate').prop('checked',true);
    } else isAutoUpdate = false;

    if(isMouseInfo == "true") {
        isMouseInfo = true;
        $('#checkbox-mouseinfo').prop('checked',true);
    } else isMouseInfo = false

    if(isAlerts == "true") {
        isAlerts = true;
        $('#checkbox-alerts').prop('checked',true);
    }

    if(cv_notifications_email == "true"){
        cv_notifications_email = true;
        $('input#user_email').val(cv_user_email);
        $("#cv_notifications_email").bootstrapSwitch('state',true);
    } else cv_notifications_email = false;

    if(cv_notifications_sound == "true"){
        cv_notifications_sound = true;
        $("#cv_notifications_sound").bootstrapSwitch('state',true);
    } else cv_notifications_sound = false;

    if(cv_notifications_browser == "true"){
        cv_notifications_browser= true;
        $("#cv_notifications_browser").bootstrapSwitch('state',true);
    } else cv_notifications_browser= false;

    if(cv_notifications_favicon == "true"){
        cv_notifications_favicon = true;
    $("#cv_notifications_favicon").bootstrapSwitch('state',true);
    } else cv_notifications_favicon = false;

    if(cv_notifications_feed == "true"){
        cv_notifications_feed = true;
    $("#cv_notifications_feed").bootstrapSwitch('state',true);
    } else cv_notifications_feed = false;

    if(cv_newsfeed_data == null){
        cv_newsfeed_data = {};
    $("#cv_newsfeed_data").bootstrapSwitch('state',false);
    localStorage.setItem('cv_newsfeed_data',JSON.stringify(cv_newsfeed_data));
    }



    if(cv_settings_csi == null){
        cv_settings_csi = {
            isEnabled:false,
            csi_1:false,
            csi_2:false,
            csi_3:false,
            csi_4:false,
            csi_5:false,
            csi_6:false,
            csi_7:false,
            csi_8:false
        };


        $("#cv_settings_csi input").bootstrapSwitch('state',false);
        localStorage.setItem('cv_settings_csi',JSON.stringify(cv_settings_csi));
    };


    if(cv_settings_csi)
    {
       if(cv_settings_csi.csi_1)$('#cv_settings_csi select option[value="-4"]').prop('selected', true);
       if(cv_settings_csi.csi_2)$('#cv_settings_csi select option[value="-3"]').prop('selected', true);
       if(cv_settings_csi.csi_3)$('#cv_settings_csi select option[value="-2"]').prop('selected', true);
       if(cv_settings_csi.csi_4)$('#cv_settings_csi select option[value="-1"]').prop('selected', true);
       if(cv_settings_csi.csi_5)$('#cv_settings_csi select option[value="1"]').prop('selected', true);
       if(cv_settings_csi.csi_6)$('#cv_settings_csi select option[value="2"]').prop('selected', true);
       if(cv_settings_csi.csi_7)$('#cv_settings_csi select option[value="3"]').prop('selected', true);
       if(cv_settings_csi.csi_8)$('#cv_settings_csi select option[value="4"]').prop('selected', true);
    }



    if(cv_settings_laser == null){
        cv_settings_laser = {
            isEnabled:false,
            buy:false,
            sell:false
        };
        $("#cv_settings_laser input").bootstrapSwitch('state',false);
        localStorage.setItem('cv_settings_laser',JSON.stringify(cv_settings_laser));
    }

    if(cv_settings_laser)
    {
        if(cv_settings_laser.buy)$('#cv_settings_laser select option[value="1"]').prop('selected', true);
        if(cv_settings_laser.sell)$('#cv_settings_laser select option[value="2"]').prop('selected', true);
    }

    if(cv_settings_scalper == null){
        cv_settings_scalper = {
            isEnabled:false,
            buy:false,
            sell:false
        };
        $("#cv_settings_scalper input").bootstrapSwitch('state',false);
        localStorage.setItem('cv_settings_scalper',JSON.stringify(cv_settings_scalper));
    }

    if(cv_settings_scalper)
    {
        if(cv_settings_scalper.buy)$('#cv_settings_scalper select option[value="1"]').prop('selected', true);
        if(cv_settings_scalper.sell)$('#cv_settings_scalper select option[value="2"]').prop('selected', true);
    }


    if(cv_tick_data == null){
        cv_tick_data = {};
    localStorage.setItem('cv_tick_data',JSON.stringify(cv_tick_data));
    }

    if(cv_currency_pair == null)
    {
        cv_currency_pair = 'eurusd';
        localStorage.setItem('pair','eurusd');
    }

    if(cv_timeframe  == null)
    {
        cv_timeframe = "h1";
        localStorage.setItem('timeframe','h1');
    }




    /** ========================
     *  Update Settings controls
     *  ========================
     */

    //if(toggleCSI) $('.btn-csi').addClass("active");
    if(toggleCSI)
    {
        $("#btn-csi").bootstrapSwitch('state',true);

        //    showCSI();
    } else {
    //    hideCSI();
        $("#btn-csi").bootstrapSwitch('state',false);
    }

    if(toggleLaser)
    {
        $("#btn-laser").bootstrapSwitch('state',true);
    } else {
        $("#btn-laser").bootstrapSwitch('state',false);
    }
    if(toggleScalper)
    {
        $("#btn-scalper").bootstrapSwitch('state',true);
    } else {
        $("#btn-scalper").bootstrapSwitch('state',false);
    }



    //if(toggleLaser) $('.btn-laser').addClass("active");
    //if(toggleScalper) $('.btn-scalper').addClass("active");

    //console.log("isAlerts:" + isAlerts + " isAutoUpdate:" + isAutoUpdate + " isMouseInfo:" + isMouseInfo);
    if(isAlerts==false){
        $('#checkbox-alerts').prop('checked',false);
    }
    else if(isAlerts){
        $('#checkbox-alerts').prop('checked',true);
    }
    if(!isMouseInfo)  {
        $('#checkbox-mouseinfo').prop('checked',false);

    }  else if(isMouseInfo){
        $('#checkbox-mouseinfo').prop('checked',true);
    }

    if(!isAutoUpdate) {
        $('#checkbox-autoupdate').prop('checked',false);
        $("#ticktimer").hide("fast");
    } else if(isAutoUpdate) {
        $("#ticktimer").show("fast");
        $('#checkbox-autoupdate').prop('checked',true);
        set_autoupdate_timer();
    }

    if(!cv_notifications_email){
        $("#cv_notifications_email").bootstrapSwitch('state',false);
    } else if(cv_notifications_email) {
        $("#cv_notifications_email").bootstrapSwitch('state',true);
        $('#cv_notifications_email').val(cv_user_email);
    }


    if(!cv_notifications_sound){
        $("#cv_notifications_sound").bootstrapSwitch('state',false);
    } else if(cv_notifications_sound) {
        $("#cv_notifications_sound").bootstrapSwitch('state',true);}


    if(!cv_notifications_browser){
        $("#cv_notifications_browser").bootstrapSwitch('state',false);
    } else if(cv_notifications_browser) {
        $("#cv_notifications_browser").bootstrapSwitch('state',true);}

    if(!cv_notifications_favicon){
        $("#cv_notifications_favicon").bootstrapSwitch('state',false);
    } else if(cv_notifications_favicon) {
        $("#cv_notifications_favicon").bootstrapSwitch('state',true);}

    if(!cv_notifications_feed){
        $("#cv_notifications_feed").bootstrapSwitch('state',false);
    } else if(cv_notifications_feed) {
        $("#cv_notifications_feed").bootstrapSwitch('state',true);
    }


    if(!cv_settings_csi.isEnabled){
        $("#cv_settings_csi input").bootstrapSwitch('state',false);
    } else if(cv_settings_csi.isEnabled) {
        $("#cv_settings_csi input").bootstrapSwitch('state',true);}

    if(!cv_settings_laser.isEnabled){
        $("#cv_settings_laser  input").bootstrapSwitch('state',false);
    } else if(cv_settings_laser.isEnabled) {
        $("#cv_settings_laser input").bootstrapSwitch('state',true);}

    if(!cv_settings_scalper.isEnabled){
        $("#cv_settings_scalper input").bootstrapSwitch('state',false);
    } else if(cv_settings_scalper.isEnabled) {
        $("#cv_settings_scalper input").bootstrapSwitch('state',true);}




}


function hideLaser() {
    d3.selectAll(".laser").style('opacity',0);
    d3.selectAll("circle").style('opacity',0);
    $("#chart-trend-detector").hide("fast");
    toggleLaser = false;
    //hasLaser = false;
    localStorage.setItem('toggleLaser', false);
}

function showLaser(){
    d3.selectAll(".laser").style('opacity',1);
    d3.selectAll("circle").style('opacity',1);
    $("#chart-trend-detector").show("fast");

    toggleLaser = true;
    //hasLaser = true;
    localStorage.setItem('toggleLaser', true);
}

function hideScalper() {
    d3.selectAll(".scalper").style('opacity',0);
    $("#scalphero").hide("fast");
    toggleScalper = false;
    //hasScalper = false;
    localStorage.setItem('toggleScalper', false);
}
function showScalper() {
    d3.selectAll(".scalper").style('opacity',1);
    $("#scalphero").show("fast");

    toggleScalper = true;
    //hasScalper = true;
    localStorage.setItem('toggleScalper', true);

}

function hideCSI() {
    vol.style('opacity',0);
    svg.selectAll('.csi_axis').style('opacity',0);
    $("#strengthometer").hide("fast");
    toggleCSI = false;
    //hasCSI = false;
    localStorage.setItem('toggleCSI', false);
}

function showCSI() {
    vol.style('opacity',1);
    svg.selectAll('.csi_axis').style('opacity',1);
    $("#strengthometer").show("fast");
    toggleCSI = true;
    //hasCSI = true;
    localStorage.setItem('toggleCSI', true);
}


function laserVisible() {
    if( toggleLaser) {
        hideLaser();
    } else {
        showLaser();
    }
}

function scalperVisible(){
    if(toggleScalper) {
        hideScalper();

    } else {
        showScalper();
    }
}

function csiVisible()
{

    if(toggleCSI) {
        hideCSI();
    } else {
        showCSI();

    }
}

function set_autoupdate_timer()
{

    //console.log("cvt:" + cv_timeframe + "pair:" + cv_currency_pair)
    //return;

    if(typeof window.autoUpdate !== "undefined") {
        //console.log("auto update timer cleared");
        clearInterval(window.autoUpdate);
        clearInterval(window.countDown);
        $("#ticktimer").hide("fast");

    }


    if(isAutoUpdate)
    {
        $("#ticktimer").show("fast");

        _interval = 0;
        _countDown = 0;
        var _format;
        clearInterval(window.autoUpdate);
        clearInterval(window.countDown);
        switch(cv_timeframe.toLowerCase())
        {
            case "m1":
                _interval = 1000*60;
                _format = 'HH:mm:ss';
                break;

            case "m5":
                _interval = 1000*60*5;
                _format = 'HH:mm:ss';
                break;
            case "m15":
                _interval = 1000*60*15;
                _format = 'HH:mm:ss';
                break;
            case "m30":
                _interval = 1000*60*30;
                _format = 'HH:mm:ss';
                break;
            case "h1":
                _interval = 1000*60*60;
                _format = 'HH:mm:ss';
                break;
            case "h4":
                _interval = 1000*60*60*4;
                _format = 'HH:mm:ss';
                break;
            case "d1":
                _interval = 1000*60*60*24;
                _format = 'HH:mm:ss';
                break;
            case "w1":
                _interval = 1000*60*60*24;//1000*60*60*24*7;
                _format = 'dd:HH:mm:ss';
                break;
            case "mn1":
                _interval = 1000*60*60*24;//1000*60*60*24*30;
                _format = 'd:HH:mm:ss';

                break;
            default:
                return;
                break;


        }
        //return;
        window.autoUpdate = setInterval(function(){
            //console.log("tick:" + cv_timeframe + " interval: " + _interval);
            render_chart('cv');
        },_interval);
        //console.log("auto update timer set: " + cv_timeframe + "==" + _interval);
        _countdown = _interval/1000;


        window.countDown = setInterval(function(){
            _countdown--;

            _countdisplay = (new Date).clearTime()
                .addSeconds(_countdown)
                .toString(_format);
            $("#tickcountdown strong").text(_countdisplay );

            //console.log("tick:" + _countdown + " interval:" + _interval + " frame:" + cv_timeframe);




        },1000);
    }



    // cv_notify();


}


function cv_notify(o)
{
    /**
     * hasCSI,
     hasMarket,
     hasLaser,
     hasScalper,
     */
    if(!isAlerts || !hasMarket) return;

    if(cv_settings_csi.isEnabled && hasCSI)
    {
        var csi_desc;
        switch(parseInt(o.volume))
        {
            case 1: if(cv_settings_csi.csi_1) csi_desc = "Weak Bullish";break;
            case 2: if(cv_settings_csi.csi_2) csi_desc = "Moderate Bullish";break;
            case 3: if(cv_settings_csi.csi_3) csi_desc = "Strong Bullish";break;
            case 4: if(cv_settings_csi.csi_4) csi_desc = "Exhaustive Bullish";break;
            case -1: if(cv_settings_csi.csi_5) csi_desc = "Weak Bearish";break;
            case -2: if(cv_settings_csi.csi_6) csi_desc = "Moderate Bearish";break;
            case -3: if(cv_settings_csi.csi_7) csi_desc = "Strong Bearish";break;
            case -4: if(cv_settings_csi.csi_8) csi_desc = "Exhaustive Bearish";break;
        }

        if(csi_desc)
        {
            cv_alert(csi_desc + " strength on " + cv_currency_pair.toUpperCase() + " \n"+Date.parse('today'));
            $("#notifications").prepend('<li class="list-group-item cv_notification"><span class="glyphicon glyphicon-object-align-bottom "></span><b>' + csi_desc+ '</b> strength on '+cv_currency_pair.toUpperCase()+'<br><i>Strength-o-meter</i><br><strong>'+Date.parse('today')+'</strong></li>');
        }
    }

    if(cv_settings_laser.isEnabled && hasLaser)
    {
        var p_buy =parseFloat(cv_settings_laser.laser_buy),
            p_sell = parseFloat(cv_settings_laser.laser_sell),
            p_bull = parseFloat(cv_settings_laser.laser_bull),
            p_bear = parseFloat(cv_settings_laser.laser_bear),
            buy = parseFloat(o.laser_buy),
            sell=parseFloat(o.laser_sell),
            bull = parseFloat(o.laser_bull),
            bear = parseFloat(o.laser_bear),
            p_sell_trend = ((p_bull>0&&p_bear > 0 && p_sell >0) ||
                           (p_bear>0 &&p_bull==0 && p_buy== 0 && p_sell==0)),
            p_buy_trend =  (p_bear>0&&p_bull > 0 && p_buy >0)||
                           (p_bull>0 &&p_bear==0 && p_sell== 0 && p_buy==0),
            sell_trend = ((bull>0&&bear > 0 && sell >0) ||
                           (bear>0 &&bull==0 && buy== 0 && sell==0)),
            buy_trend =  ((bear>0&&bull > 0 && buy >0)||
                           (bull>0 &&bear==0 && sell== 0 && buy==0));

        if(p_sell_trend && buy_trend)
        {
            cv_alert("Trend change to BUY from SELL on " + cv_currency_pair.toUpperCase() + " \n"+Date.parse('today'))
            $("#notifications").prepend('<li class="list-group-item cv_notification"><span class="glyphicon glyphicon-signal"></span><b class="buy">BUY</b> trend detected on '+cv_currency_pair.toUpperCase()+'<br><i>Trend Detector</i><strong><br>'+Date.parse('today')+'</strong></li>');

        } else if(p_buy_trend && sell_trend)
        {
            cv_alert("Trend change to SELL from BUY on " + cv_currency_pair.toUpperCase() + " \n"+Date.parse('today'))
            $("#notifications").prepend('<li class="list-group-item cv_notification"><span class="glyphicon  glyphicon-signal"></span><b class="sell">SELL</b> trend detected on '+cv_currency_pair.toUpperCase()+'<br><i>Trend Detector</i><br><strong>'+Date.parse('today')+'</strong></li>');
        }
    }

    if(cv_settings_scalper.isEnabled && hasScalper)
    {
        var _buy = parseFloat(cv_settings_scalper.scalper_buy),
            _sell = parseFloat(cv_settings_scalper.scalper_sell),
            bg = parseFloat(cv_settings_scalper.scalper_bg);

        if(bg == _buy)
        {
            cv_alert("Scalping Hero BUY up trend " + cv_currency_pair.toUpperCase() + " \n"+Date.parse('today'))
            $("#notifications").prepend('<li class="list-group-item cv_notification"><span class="glyphicon glyphicon-flash">Scalping </span><b class="buy">BUY</b> uptrend detected on '+cv_currency_pair.toUpperCase()+'<br><i>Scalping Hero</i><br><strong>'+Date.parse('today')+'</strong></li>');

        } else if(bg == sell)
        {
            cv_alert("Scalping Hero SELL up trend " + cv_currency_pair.toUpperCase() + " \n"+Date.parse('today'))
            $("#notifications").prepend('<li class="list-group-item cv_notification"><span class="glyphicon glyphicon-flash">Scalping </span><b class="buy">SELL</b> downtrend detected on '+cv_currency_pair.toUpperCase()+'<br><i>Scalping Hero</i><br><strong>'+Date.parse('today')+'</strong></li>');
        }
    }




}

function cv_alert(msg)
{
    if(typeof window.cv_notify_count == "undefined") window.cv_notify_count = 0;
    window.cv_notify_count++;
    if(cv_notifications_sound) audio.notify.play();

    if(cv_notifications_browser ) {
        $.notify(msg,
            {
                className:"success",
                clickToHide:true,
                autoHide: false
            });
    }
    if(cv_notifications_favicon) Tinycon.setBubble(window.cv_notify_count);
    $(".notification-value").text(window.cv_notify_count);
    $("#notification-badge").show("fast");

}


function render_chart(chart_type)
{
    var url;

    $('.loading').fadeIn('fast');

    set_autoupdate_timer();

    /** ===========
     *  MARKET DATA
     *  ===========
     */
            switch(cv_timeframe.toLowerCase())
            {

                case 'm1':
                    cv_size=75;
                    xAxis.ticks(5);
                    xAxis.tickFormat(d3.time.format("%H:%M"))
                    break;
                case 'm5':
                    cv_size=75;
                    xAxis.ticks(10);
                    xAxis.tickFormat(d3.time.format("%H:%M"))
                    break;
                case 'm15':
                    cv_size=75;
                    xAxis.ticks(10);
                    xAxis.tickFormat(d3.time.format("%d %b %H:%M"))


                    break;
                case 'm30':
                    cv_size=75;
                    xAxis.ticks(20);
                    xAxis.tickFormat(d3.time.format("%d %b %H:%M"))

                    break;
                case 'h1':
                    cv_size=75;
                    xAxis.ticks(10);
                    xAxis.tickFormat(d3.time.format("%d %b %H:%M"))
                    break;
                case 'h4':
                    cv_size=75;
                    xAxis.ticks(5);
                    xAxis.tickFormat(d3.time.format("%d %b %H:%M"))

                    break;
                case 'd1':
                    cv_size=75;
                    xAxis.ticks(10);
                    xAxis.tickFormat(d3.time.format("%d %b"))

                    break;
                case 'w1':
                    cv_size=75;
                    xAxis.ticks(5);
                    xAxis.tickFormat(d3.time.format("%d %b"))

                    break;
                case 'mn1':
                    cv_size=75;
                    xAxis.ticks(10);
                    xAxis.tickFormat(d3.time.format("%Y %d %b"))

                    break;

                default:
                    cv_size=75;
                    xAxis.ticks(5);
                    break;
            }

            svg.selectAll("g").remove();
            svg.selectAll("circle").remove();

            url = "http://" + cv_domain +"/" + chart_type + "/" + cv_currency_pair + "/" + cv_timeframe + "/" + cv_size;
            console.log(url);

            d3.json(url, function(error, data) {

                var accessor = candlestick.accessor();

                if(typeof data.response == "undefined" || data.response == 0)
                {
                    $.notify(data.data.message, "warn");
                    audio.warn.play();
                    return;
                }

                hasCSI = data.data.csi;
                hasMarket = data.data.market;
                hasLaser = data.data.laser;
                hasScalper = data.data.scalper;
                laserSummary = data.data.laser_summary;


                if(!hasMarket) {
                    $.notify("No market data found for "+ cv_currency_pair + ".", "warn");
                    audio.warn.play();
                    return;
                }




                if(!hasCSI) {
                    audio.warn.play();
                    $.notify("No CSI data found for "+ cv_currency_pair + ".", "warn");
                }
                if(!hasLaser){
                    audio.warn.play();
                    $.notify("No Laser data found for "+ cv_currency_pair + ".", "warn");
                }


                data = data.data.result.map(function(d) {
                    return {
                        id:             +d.id,
                        date:           parseDate(d.time),
                        open:           +d.open,
                        high:           +d.high,
                        low:            +d.low,
                        close:          +d.close,
                        csi_bear:       +d.csi_bear,
                        csi_bull:       +d.csi_bull,
                        laser_bull:     +d.laser_bull,
                        laser_bear:     +d.laser_bear,
                        laser_buy:      +d.laser_buy,
                        laser_sell:     +d.laser_sell,
                        laser_av:       +d.laser_av,
                        laser_mm:       +d.laser_mm,
                        scalper_buy:    +d.scalper_buy,
                        scalper_sell:   +d.scalper_sell,
                        scalper_bg:     +d.scalper_bg,
                        volume:(d.csi_bull =="0")? d.csi_bear: d.csi_bull
                    };
                }).sort(function(a, b) { return d3.ascending(accessor.d(a), accessor.d(b)); });

                //console.log(data.map(accessor.d));
                x.domain(data.map(accessor.d));
                y.domain(techan.scale.plot.ohlc(data, accessor).domain());

                barWidth = width / data.length;

                _data = data;


                //Store tick data
                cv_notify(_data[_data.length-1]);
                localStorage.setItem('cv_tick_data', JSON.stringify(_data[_data.length-1]));


                /** =============================
                 *  Lets start building the chart
                 *  =============================
                 */



                //return
                svg.append("g")
                    .datum(data)
                    .attr("class", "candlestick")
                    .call(candlestick);

                svg.append("g")
                    .attr("class", "x axis")
                    .attr("transform", "translate(0," + height + ")")
                    .call(xAxis);

                d3.selectAll(".candlestick")
                    .on('mouseover',function(d,i){
                    });

                svg.append("g")
                    .attr("class", "y axis")
                    .attr("transform", "translate(" + width + " ,0)")
                    .call(yAxis)
                    .append("text")
                    .attr("transform", "rotate(-90)")
                    .attr("y", -16)
                    .attr("dy", ".71em")
                    .style("text-anchor", "end")
                    .text("Price ($)");

                if(hasCSI) crosshair.xAnnotation([timeAnnotation,csi_annotation])
                else crosshair.xAnnotation([timeAnnotation])


                svg.append('g')
                    .attr("class", "crosshair")
                    .call(crosshair);

                //zoom.x(x.zoomable().clamp(false)).y(y);

                /**
                 *  Lets add current price
                 */


                console.log('.. fetching current price');
                var c1 = cv_currency_pair.substr(0,3);
                var c2 = cv_currency_pair.substr(3,3);
                var c_url = "http://api.fixer.io/latest?symbols=" + c2 + "&base=" + c1;
                 c_start = new Date( _data[0].date);
                 c_end = new Date(_data[_data.length-1].date);
                //console.log(c_url);return;
                $.get(c_url, function( data ) {
                    console.log(data);
                    //return;
                   //var j = JSON.parse(data);
                    c_price = data["rates"][c2];
                    console.log("price: " + c_price);
                    var currentPriceData = [
                        { start: c_start, end: c_end, value: c_price }
                    ];

                    console.log(currentPriceData);
                    svg.append("g")
                        .datum(currentPriceData)
                        .attr("class", "currentPrice")
                        .attr("clip-path", "url(#supstanceClip)")
                        .call(currentPrice);
                });

                //console.log(' new date: ' + new Date(2014, 2, 11));
                //return;




    /** ================
     *  LASER TREND TOOL
     *  ================
     */



                if(hasLaser)
                {
                    /** ===============
                     *  Draw BEAR laser
                     *  ===============
                     */

                     svg.select("g").selectAll("laser")
                        .data(data)
                        .enter()
                        .append("path")
                        .attr("class","laser_sell laser")
                        .attr("d", laser_sell(data))
                        .style('transform','translate(0px,' + laser_sell_offset+'px)')


                    svg.select("g").selectAll("laser")
                        .data(data)
                        .enter()
                        .append("path")
                        .attr("class","laser_sell_glow laser")
                        .attr("d", laser_sell(data))
                        .style('transform','translate(0px,' + laser_sell_offset+'px)')


                    /** ===============
                     *  Draw BULL laser
                     *  ===============
                     */

                    svg.select("g").selectAll("laser")
                        .data(data)
                        .enter()
                        .append("path")
                        .attr("class","laser_buy laser")
                        .attr("d", laser_buy(data))
                        .style('transform','translate(0px,'+ laser_buy_offset+'px)')

                    svg.select("g").selectAll("laser")
                        .data(data)
                        .enter()
                        .append("path")
                        .attr("class","laser_buy_glow laser")
                        .attr("d", laser_buy(data))
                        .style('transform','translate(0px,'+ laser_buy_offset+'px)')


                    d3.selectAll('path.laser').moveToBack()


                    _profits = Array();

                    //console.log(laserSummary);


                    var _profit =laserSummary.profitable_trades;
                    var _total = laserSummary.trades;
                    var _success = Math.round((_profit/(_total)*100),2);
                    var _profitMax = parseFloat(laserSummary.profit_max*1000).toFixed(1);
                    //console.log('profit max' + _profitMax);

                    $("#ctd-overall strong").text(laserSummary.trades);
                    $("#ctd-profit strong").text(laserSummary.profitable_trades );
                    $("#ctd-success strong").text(_success + "%");
                    $("#ctd-bestprofit strong").text( _profitMax + " pips");
                    $("#ctd-avgprofit strong").text(parseFloat(laserSummary.av).toFixed(1) + " pips");
                    $("#ctd-mm strong").text(parseFloat(laserSummary.mm).toFixed(1) + " pips");
                    /*
                    l1.text("Overall trades: " + laserSummary.trades );
                    l2.text("Profitable Trades: " + laserSummary.profitable_trades );
                    l3.text("Success Rate: " + _success + "%");
                    l4.text("Best Profit: " + _profitMax + " pips");
                    l5.text("Average Profit: " + parseFloat(laserSummary.av).toFixed(1) + " pips");
                    l6.text("Minimum Move: " + parseFloat(laserSummary.mm).toFixed(1) + " pips");
                    //console.log(_data[_data.length - 1]);
                    */




                }





    /** =============
     *  RAZOR SCALPER
     *  =============
     */


                if(hasScalper)
                {

                    //var padding = {top:-50,bottom:0,left:0,right:-10};
                    //var _padding = {top:-50,bottom:0,left:0,right:-10};


                    svg.select("g").selectAll('scalper')
                        .data(data)
                        .enter()
                        .append('image')
                        .attr('class', 'scalper')
                        .attr('xlink:href',function(d,i){
                            var _img;
                            //console.log(d);
                            if(typeof d.scalper_bg == "undefined" || d.scalper_bg == 0) return;
                            if(d.scalper_bg == d.scalper_buy) _img = '../img/arrow_buy.png';
                            if(d.scalper_bg == d.scalper_sell) _img = '../img/arrow_sell.png';
                            return _img;
                        })
                        .attr('height', '30')
                        .attr('width', '30')
                        /*
                        .attr('transform','translate(' + width/2+ ',' + height/2 +')')
                        .transition()
                        .delay(500)
                        .duration(100)*/
                        .attr('transform',function(d,i){
                          //'translate(59,50)'
                            if(d.scalper_bg == 0) return '';
                            var _x,_y;
                            _x = Math.floor(x(d.date))-15;
                            _y = (d.scalper_buy == d.scalper_bg)? Math.floor(y(d.low))+20:Math.floor(y(d.high))-50;
                            //console.log("rs: x:" + _x + " y:" + _y);
                            return "translate(" + (_x)+ "," + _y + ")";
                        });

                    //d3.selectAll('g.scalper').moveToFront()

                }



    /** ===========================
     *  CURRENCY STRENGTH INDICATOR
     *  ===========================
     */

                if(hasCSI)
                {


                    vol = svg.selectAll("volume")
                        .data(data)
                        .enter()
                        .append("g")
                        .attr("class","volume")
                        .attr("transform", function(d, i) {
                            var _x = Math.floor(x(d.date))-2;
                            if(i == d.length) return;
                            return "translate(" + _x + "," + (height +40) + ")"

                        });
                        //.call(zoom);


                    vol.append("rect")
                        .attr("class",function(d){
                            switch(d.volume)
                            {
                                case 1: return "csi_strong_1";break;
                                case 2: return "csi_strong_2";break;
                                case 3: return "csi_strong_3";break;
                                case 4: return "csi_strong_4";break;
                                case -1: return "csi_weak_1";break;
                                case -2: return "csi_weak_2";break;
                                case -3: return "csi_weak_3";break;
                                case -4: return "csi_weak_4";break;
                            }
                        })
                        .attr("y",function(d) {
                            if(d.volume > 0) return 40-yVolume(d.volume);
                            else return Math.abs(40+yVolume(d.volume))-yVolume(d.volume);
                        })
                        .attr("height", function(d) { return  Math.abs(yVolume(d.volume)); })
                        .attr("width", barWidth - 2)
                        .style("fill", function(d){
                            var _color=csi_colors[d.volume];
                            var t = textures.lines()
                                .strokeWidth(0)
                                .background(_color)
                                .stroke("#fff")
                                .orientation("horizontal")
                                .shapeRendering("crispEdges")
                                .size(10)
                            svg.call(t);
                            return t.url();
                        });

                    svg.append("g")
                        .attr("class", "csi_axis x axis")
                        .attr("transform", "translate(0," + (height+80) + ")")
                        .call(csi_x_axis);

                    svg.append("g")
                        .attr("class", "csi_axis y axis")
                        //.style("display","none")
                        .attr("transform", "translate("+ width + "," + (height+80) + ")")
                        .style("font-size",8)
                        .call(csi_y_axis);
                }


                if(hasLaser && !toggleLaser) hideLaser();
                if(hasCSI && !toggleCSI) hideCSI();
                if(hasScalper && !toggleScalper) hideScalper();

                $('.loading').fadeOut('fast');
       });
}


function enter() {
    coordsText.style("display", "inline");
    if(toggleCSI) csi_text.style("display", "inline");
    if(toggleLaser) laser_text.style("display", "inline");
    if(toggleScalper) scalper_text.style("display", "inline");


}


function out() {
    coordsText.style("display", "none");
    csi_text.style("display", "none");
    laser_text.style("display", "none");
    scalper_text.style("display", "none");


    d3.select("g.volume").style("stroke","none");

}


function move(coords) {

    var _tooltip;

    var _date = timeAnnotation.format()(coords[0]);
    var _price = ohlcAnnotation.format()(coords[1]);
    $("#mkt-date strong").text(_date);
    $("#mkt-price strong").text(_price);
    $("#mkt-current-price strong").text(c_price);


    _tooltip = "<b>Price: </b>" + _price + "<br/><b>Current price:</b> " + c_price + "<br/><b>Date: </b>" + _date;

    if(hasScalper && toggleScalper)
    {
        d3.selectAll(".scalper")
            .filter(function(d) {
                if(timeAnnotation.format()(d.date) == timeAnnotation.format()(coords[0]) && typeof d.date !== "undefined")
                {

                    if(d.scalper_bg == d.scalper_buy) {
                        $("#sh-strength strong").text("Buy");
                        _tooltip +="<br><b>Scalping Hero: </b>Buy";
                    }
                    else if(d.scalper_bg == d.scalper_sell) {
                        $("#sh-strength strong").text("Sell");
                        _tooltip +="<br><b>Scalping Hero: </b>Sell";
                    }
                    else {
                        $("#sh-strength strong").html('<strong><span class="na">N/A</span>');
                    }
                }

            });
    }

    if(hasLaser && toggleLaser)
    {
        var _i = "";

        d3.selectAll(".laser")
            .filter(function(d) {
                if(timeAnnotation.format()(d.date) == timeAnnotation.format()(coords[0]) && typeof d.date !== "undefined")
                {
                    if(d.laser_bull == d.laser_bear  && d.date.sell >0)
                    {
                        $("#ctd-trend strong").text("Sell Trend");
                        _i="<br><b>Trend: </b>Sell";
                    }
                    else if (d.laser_bull == d.laser_bear && d.date.buy >0)
                    {
                        $("#ctd-trend strong").text("Buy trend");
                        _i="<br><b>Trend: </b>Buy";

                    }
                    else if(d.laser_bear >0)
                    {
                        $("#ctd-trend strong").text("Sell Trend");
                        _i="<br><b>Trend: </b>Sell";

                    }
                    else if(d.laser_bull>0)
                    {
                        $("#ctd-trend strong").text("Buy trend");
                        _i="<br><b>Trend: </b>Buy";

                    }


                    else {
                        $("#ctd-trend strong").html('<strong><span class="na">N/A</span>');
                        _i="";
                    }
                }

            });

        _tooltip += _i;

    }

    if(hasCSI && toggleCSI)
    {
        d3.selectAll("g.volume")
            .filter(function(d) {
                if(timeAnnotation.format()(d.date) == timeAnnotation.format()(coords[0]))
                {
                    var csi = d.volume;
                    var csi_desc;

                    switch(parseInt(csi))
                    {
                        case 1: csi_desc = "Weak Bullish";break;
                        case 2: csi_desc = "Moderate Bullish";break;
                        case 3: csi_desc = "Strong Bullish";break;
                        case 4: csi_desc = "Exhaustive Bullish";break;
                        case -1: csi_desc = "Weak Bearish";break;
                        case -2: csi_desc = "Moderate Bearish";break;
                        case -3: csi_desc = "Strong Bearish";break;
                        case -4: csi_desc = "Exhaustive Bearish";break;
                        default: csi_desc = '<strong><span class="na">N/A</span>';break;
                    }

                    $("#som-strength strong").text( d.volume + " " + csi_desc);
                    _tooltip +="<br><b>Strength: </b>" + csi;

                    csi_annotation.format(function(d){ return csi;})
                    d3.select(this)
                        .style('stroke-width', 2)
                        .style('stroke', 'steelblue')
                } else {
                    d3.select(this).style("stroke","none");
                }
        });

    }


    if(isMouseInfo) $("#chart-tooltip").html(_tooltip);
}

$( document ).ready(function() {
    $("#slider a").click(function(){
        var id = $(this).attr("href").substring(1);
        $("html, body").animate({ scrollTop: $("#"+id).offset().top }, 1000, function(){
            $("#slider").slideReveal("hide");
        });
    });

    var slider = $("#slider").slideReveal({
        width: 300,
        push: true,
        position: "left",
        speed: 600,
        trigger: $(".handle"),
        // autoEscape: false,
        shown: function(obj){
            //obj.find(".handle").html('<span class="glyphicon glyphicon-chevron-right"></span>');
            obj.addClass("left-shadow-overlay");
            $("#cv-container").css({float:"left"});
        },
        hidden: function(obj){
            //obj.find(".handle").html('<span class="glyphicon glyphicon-chevron-left"></span>');
            obj.removeClass("left-shadow-overlay");
            //$("#cv-container").css({float:"none"});
            $("#cv-container").css({float:"none"});
        }
    });

    $(".handle").on("click",function(e){
        $("#notification-badge").fadeOut("fast");
    });

    //slider.slideReveal("show");
    set_autoupdate_timer(); //set autoupdate timer if applicable


    $('#btn-select-currency-pair b').text(cv_currency_pair.toUpperCase());
    $('#btn-select-timeframe b').text(cv_timeframe.toUpperCase());
    $('#chart_currency_pair ul li a').click(function(e){
        cv_currency_pair = $(this).text();
        $('#btn-select-currency-pair b').text(cv_currency_pair);
        localStorage.setItem('pair',cv_currency_pair);
        render_chart('cv');
    });

    $('#chart_timeframe ul li a').click(function(e){
        cv_timeframe = $(this).text();
        $('#btn-select-timeframe b').text(cv_timeframe);
        localStorage.setItem('timeframe',cv_timeframe);
        render_chart('cv');
    });




    /*

    $('#btn-csi').on('click',function(e){
        csiVisible();
    });

    $('#btn-laser').on('click',function(e){
        laserVisible();
    });

    $('#btn-scalper').on('click',function(e){
       scalperVisible();
    });*/

    $('#checkbox-autoupdate').on('click',function(){
        var _checked = $('#checkbox-autoupdate').is(":checked");
        //console.log('isAutoUpdate: ' + isAutoUpdate + " checked: " + _checked);
        if(!_checked)
        {
            localStorage.setItem('isAutoUpdate', false);
            isAutoUpdate = false;
            if(typeof window.autoUpdate !== "undefined") {
                //console.log("auto update timer cleared");
                clearInterval(window.autoUpdate);
                clearInterval(window.window.countDown);
                $("#ticktimer").hide("fast");
            }
        }
        else if(_checked) {
            localStorage.setItem('isAutoUpdate', true);
            isAutoUpdate = true;
            $("#ticktimer").show("fast");
        }
    });


    $('#checkbox-mouseinfo').on('click',function(){
        var _checked = $('#checkbox-mouseinfo').is(":checked");
        if(!_checked)
        {
            localStorage.setItem('isMouseInfo', false);
            isMouseInfo = false;
            $('#chart-market-container').powerTip({
                manual: true,
                followMouse: true,
                smartPlacement:true
            });

        }
        else if(_checked) {
            localStorage.setItem('isMouseInfo', true);
            isMouseInfo = true;
            $('#chart-market-container').powerTip({
                manual: false,
                followMouse: true,
                smartPlacement:true
            });

        }
    });


    $('#checkbox-alerts').on('click',function(){
        var _checked = $('#checkbox-alerts').is(":checked");
        if(!_checked)
        {
            localStorage.setItem('isAlerts', false);
            isAlerts = false;
        }
        else if(_checked) {
            localStorage.setItem('isAlerts', true);
            isAlerts = true;

        }
    });



    $('#tab-tools a').on('click',function(e){
        $('#cv-settings,#cv-notifications').fadeOut('fast',function(){$('#cv-tools').fadeIn('fast')});
        $('.cv-tab').removeClass('active');
        $('#tab-tools').addClass('active');
    });

    $('#tab-notifications a').on('click',function(e){
        $('#cv-settings,#cv-tools').fadeOut('fast',function(){$('#cv-notifications').fadeIn('fast')});
        $('.cv-tab').removeClass('active');
        $('#tab-notifications').addClass('active');
    });


    $('#tab-settings a').on('click',function(e){
        $('#cv-tools,#cv-notifications').fadeOut('fast',function(){$('#cv-settings').fadeIn('fast')});
        $('.cv-tab').removeClass('active');
        $('#tab-settings').addClass('active');
    });

    $('#cv-settings,#cv-notifications').fadeOut('fast',function(){$('#cv-tools').fadeIn('fast')});


    $('#chart-market-container').powerTip({
        followMouse: true,
        smartPlacement:true,
        manual:!isMouseInfo,
        offset:-150
    });
    $("#chart-market-container").data('powertip', '<div id="chart-tooltip">N/A</div> ');


    //$("[name='checkbox']").bootstrapSwitch();
    //$('.multi-select').multiselect();
    $(".notification-switch").bootstrapSwitch();

    render_chart('cv');

    $("#cv_notifications_email").on('switchChange.bootstrapSwitch', function(event, state) {
        if(!state){
            localStorage.setItem('cv_notifications_email','false');
            cv_notifications_email = false;
            cv_server_store()
        }
        else if(state) {

            var user_email;
            user_email = $("input#user_email").val();
            if(typeof user_email == 'undefined' || user_email == '')
            {
                alert('You must provide a valid email to send notifications to.');
                $("#cv_notifications_email").bootstrapSwitch('state',false);

                return false;
            }
            localStorage.setItem('cv_notifications_email','true');
            localStorage.setItem('cv_user_email',user_email);
            cv_user_email = user_email;

            cv_notifications_email = true;

            /**
             *  Lets go ahead and switch it on the backend
             */
            cv_server_store();
        }
    });


    $("#cv_notifications_sound").on('switchChange.bootstrapSwitch', function(event, state) {
        if(!state){
            localStorage.setItem('cv_notifications_sound','false');}
        else if(state) {
            localStorage.setItem('cv_notifications_sound','true');
            cv_notifications_sound = true;}
    });





    $("#cv_notifications_browser").on('switchChange.bootstrapSwitch', function(event, state) {
        if(!state){
            localStorage.setItem('cv_notifications_browser','false');}
        else if(state) {
            localStorage.setItem('cv_notifications_browser','true');
            cv_notifications_browser = true;}
    });


    $("#cv_notifications_favicon").on('switchChange.bootstrapSwitch', function(event, state) {
        if(!state){
            localStorage.setItem('cv_notifications_favicon','false');}
        else if(state) {
            localStorage.setItem('cv_notifications_favicon','true');
            cv_notifications_favicon = true;}
    });



    $("#cv_settings_csi input").on('switchChange.bootstrapSwitch', function(event, state) {

        if(!state){
            cv_settings_csi.isEnabled = false;
            localStorage.setItem('cv_settings_csi',JSON.stringify(cv_settings_csi));}
        else if(state) {
            cv_settings_csi.isEnabled = true;
            localStorage.setItem('cv_settings_csi',JSON.stringify(cv_settings_csi));
            }
    });


    $('#cv_settings_csi select').multiselect({
        onChange: function(option, checked, select) {

            switch(parseInt(option.val()))
            {
                case 1: cv_settings_csi.csi_1= checked; break;
                case 2: cv_settings_csi.csi_2= checked; break;
                case 3:cv_settings_csi.csi_3 = checked; break;
                case 4: cv_settings_csi.csi_4= checked; break;
                case -1: cv_settings_csi.csi_5 = checked; break;
                case -2: cv_settings_csi.csi_6= checked; break;
                case -3: cv_settings_csi.csi_7= checked; break;
                case -4: cv_settings_csi.csi_8 = checked;break;
            }

            localStorage.setItem('cv_settings_csi',JSON.stringify(cv_settings_csi));
        }
    });

    $('#cv_settings_laser select').multiselect({
        onChange: function(option, checked, select) {
            switch(parseInt(option.val()))
            {
                case 1: cv_settings_laser.buy= checked; break;
                case 2: cv_settings_laser.sell= checked; break;
            }
            localStorage.setItem('cv_settings_laser',JSON.stringify(cv_settings_laser));
        }
    });

    $('#cv_settings_scalper select').multiselect({
        onChange: function(option, checked, select) {
            switch(parseInt(option.val()))
            {
                case 1: cv_settings_scalper.buy= checked; break;
                case 2: cv_settings_scalper.sell= checked; break;
            }
            localStorage.setItem('cv_settings_scalper',JSON.stringify(cv_settings_scalper));        }
    });



    $("#cv_settings_scalper input").on('switchChange.bootstrapSwitch', function(event, state) {
        if(!state){
            cv_settings_scalper.isEnabled = false;
            localStorage.setItem('cv_settings_scalper',JSON.stringify(cv_settings_scalper));}
        else if(state) {

            cv_settings_scalper.isEnabled = true;}
            localStorage.setItem('cv_settings_scalper',JSON.stringify(cv_settings_scalper));
    });


    $('#scalper-select').multiselect({
        onChange: function(option, checked, select) {

                 }
    });






    $("#cv_notifications_sound input").on('switchChange.bootstrapSwitch', function(event, state) {

        if(!state){
            localStorage.setItem('cv_notifications_sound','false');}
        else if(state) {
            localStorage.setItem('cv_notifications_sound','true');
            cv_notifications_sound = true;}
    });



    $("#cv_settings_laser input").on('switchChange.bootstrapSwitch', function(event, state) {
        if(!state){
            cv_settings_laser.isEnabled = false;
            localStorage.setItem('cv_settings_laser',JSON.stringify(cv_settings_laser))}
        else if(state) {
            cv_settings_laser.isEnabled = true ;
            localStorage.setItem('cv_settings_laser',JSON.stringify(cv_settings_laser));

        }
    });


    $("#cv_notifications_feed").on('switchChange.bootstrapSwitch', function(event, state) {
        var _checked = $("#cv_notifications_feed input").bootstrapSwitch('state');
        if(!_checked){
            localStorage.setItem('cv_notifications_feed','false');}
        else if(_checked) {
            localStorage.setItem('cv_notifications_feed','true');
            cv_notifications_feed = true;}
    });


    $("#btn-csi").bootstrapSwitch();
    $("#btn-csi").on('switchChange.bootstrapSwitch', function(event, state) {
        csiVisible();
    });


    $("#btn-laser").bootstrapSwitch();
    $("#btn-laser").on('switchChange.bootstrapSwitch', function(event, state) {
        laserVisible();
    });


    $("#btn-scalper").bootstrapSwitch();
    $("#btn-scalper").on('switchChange.bootstrapSwitch', function(event, state) {
        scalperVisible();
    });


    /** ==================
     *  EMAIL NOTIFICATION
     *  ==================
     */

    $('#indicator_data ul li a').click(function(e){
        var data = $(this).text();
        $('#indicator_data button').attr('data-value',$(this).attr('data-value'));
        $('#indicator_data button b').text(data);
    });



    $('#btn-select-currency-pair2 b').text(cv_currency_pair.toUpperCase());
    $('#btn-select-timeframe2 b').text(cv_timeframe.toUpperCase());
    $('#chart_currency_pair2 ul li a').click(function(e){
        //cv_currency_pair = $(this).text();
        $('#btn-select-currency-pair2 b').text($(this).text());
        //localStorage.setItem('pair',cv_currency_pair);
        //render_chart('cv');
    });

    $('#chart_timeframe2 ul li a').click(function(e){
        //cv_timeframe = $(this).text();
        $('#btn-select-timeframe2 b').text($(this).text());
        //localStorage.setItem('timeframe',cv_timeframe);
    });

    $("#notification-add").on('click',function(e){
        var currency_pair = $('#btn-select-currency-pair2 b').text();
        var time_frame = $('#btn-select-timeframe2 b').text();
        var indicator_type = $('#indicator_data button').attr('data-value');
        var dupe = false;

        /**
         *  Lets make sure we're not adding duplicates
         */
        $('.notification-row').each(function(e){
            var pair = $(this).find('.notification-currency').text();
            var time = $(this).find('.notification-timeframe').text();
            var type = $(this).find('.notification-type').attr('data-value');

            if(currency_pair == pair && time_frame == time && indicator_type == type)
            {
                $.notify('That is a duplicate alert. You may only subscribe to one of each.', "warn");
                audio.notify.play();
                dupe = true;
            }

        });

        if(dupe == true) return false;


        var _i = indicator_type;
        switch(indicator_type)
        {
            case '1': indicator_type = 'Buy Scalp'; break;
            case '2': indicator_type = 'Sell Scalp'; break;
        }
        //console.log('currency pair: '  + currency_pair + ' time frame:' + time_frame + ' indicator type: ' + indicator_type);

        var html = $('.notification-row:first').clone();
        $(html).removeClass('template');
        $(html).find('.notification-currency').text(currency_pair);
        $(html).find('.notification-timeframe').text(time_frame);
        $(html).find('.notification-type').text(indicator_type);
        $(html).find('.notification-type').attr('data-value',_i);


        $(html).find('.notification-action ul li a').on('click',function(e){
            var data = $(this).text();
            var ref = $(this).parent().parent().parent();
            $(ref).find('button').attr('data-value',$(this).attr('data-value'));
            $(ref).find('button b').text(data);
           // $('.notification-action button').attr('data-value',$(this).attr('data-value'));
           // $('.notification-action button b').text(data);
        });

        $('.notification-list').append(html);

        $(html).fadeIn('fast');

    });


    function load_alerts()
    {

        $.ajax({
            type:    "POST",
            url:     "http://" + cv_domain + "/user/alert/rs/list",
            data:    {email:cv_user_email},
            success: function(data) {
                var alerts = data.data.alerts;
                //console.log(alerts);
                //return false;


                for(item in alerts)
                {
                    var currency_pair = alerts[item]['alert_currency_pair'].toUpperCase();
                    var time_frame = alerts[item]['alert_timeframe'].toUpperCase();
                    var indicator_type = alerts[item]['alert_param'];
                    var is_enabled = alerts[item]['alert_is_enabled'];

                    //console.log(is_enabled);
                    //return false;


                    var _i = indicator_type;
                    switch(indicator_type)
                    {
                        case '1': indicator_type = 'Buy Scalp'; break;
                        case '2': indicator_type = 'Sell Scalp'; break;
                    }
                    //console.log('currency pair: '  + currency_pair + ' time frame:' + time_frame + ' indicator type: ' + indicator_type);

                    var html = $('.notification-row:first').clone();
                    $(html).removeClass('template');
                    $(html).find('.notification-currency').text(currency_pair);
                    $(html).find('.notification-timeframe').text(time_frame);
                    $(html).find('.notification-type').text(indicator_type);
                    $(html).find('.notification-type').attr('data-value',_i);

                    var _txt = '';
                    switch(is_enabled)
                    {
                        case "1":
                            _txt = 'Enabled';
                            break;
                        case "0":
                            _txt = 'Disabled';
                            break;
                        case "2":
                            _txt = 'Delete Notification';
                            break;
                    }
                    //console.log(_txt);

                    $(html).find('.notification-action button').attr('data-value',is_enabled);
                    $(html).find('.notification-action button b').text(_txt);

                    $(html).find('.notification-action ul li a').on('click',function(e){
                        var data = $(this).text();
                        var ref = $(this).parent().parent().parent();
                        $(ref).find('button').attr('data-value',$(this).attr('data-value'));
                        $(ref).find('button b').text(data);

                    });

                    $('.notification-list').append(html);

                    $(html).fadeIn('fast');
                    //alert(data.data.message);

                }







            },
            error:   function(jqXHR, textStatus, errorThrown) {
                alert("Error, status = " + textStatus + ", " +
                    "error thrown: " + errorThrown
                );
            }
        });
    }

    load_alerts();


    $('#notifications-save').on('click',function(e){
        var data = [];

        $('.notification-row').each(function(){
            var currency_pair = $(this).find('.notification-currency').text();
            var time_frame = $(this).find('.notification-timeframe').text();
            var indicator_type = $(this).find('.notification-type').attr('data-value');
            var indicator_action = $(this).find('.notification-action button').attr('data-value');
            console.log('currency pair: ' + currency_pair + ' time frame:' + time_frame + ' indicator type: ' + indicator_type + ' indicator action: ' + indicator_action);

            if(currency_pair !='')
            {
                data.push({pair:currency_pair,time:time_frame,type:indicator_type,action:indicator_action});
            }
        });
        var json = JSON.stringify(data);
        console.log(json);

        $.ajax({
            type:    "POST",
            url:     "http://" + cv_domain + "/user/alert/rs",
            data:    {email:cv_user_email,data:json},
            success: function(data) {
                alert(data.data.message);
            },
            error:   function(jqXHR, textStatus, errorThrown) {
                alert("Error, status = " + textStatus + ", " +
                    "error thrown: " + errorThrown
                );
            }
        });


        //$.post( "http://" + cv_domain + "/user/alert", json,function(data){alert(data);}).success(function(){alert('hi');})




    });




});
