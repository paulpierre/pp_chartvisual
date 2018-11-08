/** ===============================
 *  Load Settings from LocalStorage
 *  ===============================
 */
var hasCSI,
    hasMarket,
    hasLaser,
    hasScalper,
    c_price,
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
    data,
    _profits,
    _interval,
    _countdown,
    audio = {
        warn:new Audio('/sound/warn.mp3'),
        notify:new Audio('/sound/notify.mp3')
    }


/*********************************************** Chart Components ***********************************************/
var svg = d3.select("#chart-market-container").append("svg")
    .attr("width", "100%")
    .attr("height", 600)
    .attr("class", "outline")
    .style("display", "block")
    .style("margin-top", 30);

var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

var wicks,
    candles,
    helperLine,
    mainPlot,
    laserPlot,
    delta,
    csiPlot,
    dataset,
    trendDatasetR,
    trendDatasetG,
    commonXaxis,
    chart,
    scalperController,
    greenColor = "#3EB02A",
    redColor = "#F24E4E",
    scalperGreen = "#03992B",
    scalperRed = "#D10808",
    tooltip,
    priceY,
    currentPriceY;

/** ====================
 *  Scalper constructor
 *  ====================
 **/
function createScalper(scalperContainer, point, scalperValue) {
    // 1 for buy-arrow-up
    // -1 for sell-arrow-down
    if(scalperValue == 1) {
        scalperContainer.append("svg:text")
            .attr("font-family","FontAwesome")
            .attr("font-size",22)
            .attr("fill",scalperGreen)
            .attr("stroke",scalperGreen)
            .text(function() { return "\uf062"; })
            .attr({ x: point.x - 10, y: point.y + 30 });
    } else if(scalperValue == -1) {
        scalperContainer.append("svg:text")
            .attr("font-family","FontAwesome")
            .attr("font-size",22)
            .attr("fill",scalperRed)
            .attr("stroke",scalperRed)
            .text(function() { return "\uf063"; })
            .attr({ x: point.x - 10, y: point.y - 20 });
    }
}


/** =============================
 *  ScalperContainer constructor
 *  =============================
 **/
function createScalperContainer(plot) {
    var scalperController = {};
    if(d3.select(".scalper-container")[0][0] != null) {
        d3.select(".scalper-container").remove();
    }
    var scalperContainer = plot.foreground().append("g")
        .style("visibility", "hidden")
        .attr("class", "scalper-container");

    scalperController.container = scalperContainer;

    scalperController.show = function() {
        scalperContainer.style("visibility", "visible");
    }

    scalperController.hide = function() {
        scalperContainer.style("visibility", "hidden");
    }

    return scalperController;
}



/** =====================
 *  Stripped Background
 *  =====================
 **/
function createStrippedBackground(plot) {
    var strippedBackground = {};
    var scalperContainer = plot.background().append("g").style("visibility", "hidden")

    strippedBackground.rect = scalperContainer.append("svg:image")
        .attr("xlink:href", "img/stripes.png")
        .attr("preserveAspectRatio", "none");

    strippedBackground.drawAt = function() {
        strippedBackground.rect.attr({
            x: 0,
            y: 0,
            width: 1000,
            height: 80
        })
    }

    strippedBackground.show = function() {
        scalperContainer.style("visibility", "visible");
    }

    strippedBackground.hide = function() {
        scalperContainer.style("visibility", "hidden");
    }

    return strippedBackground;
}



/** ================
 *  Scalper Drawer
 *  ================
 **/
function scalperDrawer(data, scalperController, cv_size) {
    var xS, yS;

    for(var i=0; i<cv_size; i++) {
        if(data[i]['scalper_bg'] == 0) {
            continue;
        } else if((data[i]['scalper_bg'] == -1) && (data[i]['scalper_sell'] == data[i]['scalper_bg'])) {
            xS = data[i]['dateString'];
            yS = data[i]['open']
            value = data[i]['scalper_bg'];
            createScalper(scalperController.container, { x: xScale.scale(xS), y: yScale.scale(yS) }, value);
        } else if((data[i]['scalper_bg'] == 1) && (data[i]['scalper_buy'] == data[i]['scalper_bg'])) {
            xS = data[i]['dateString'];
            yS = data[i]['open']
            value = data[i]['scalper_bg'];
            createScalper(scalperController.container, { x: xScale.scale(xS), y: yScale.scale(yS) }, value);
        }
    }
    scalperController.show();
}



/** =================
 *  Datetime parser
 *  =================
 **/
var parseDate = d3.time.format("%Y-%m-%d %H:%M:%S").parse;

function customDateFormat(dateString) {
    var date = parseDate(dateString);
    var d = date.getDate();
    month = months[date.getMonth()],
        year = date.getFullYear(),
        itime = date.toTimeString();

    var retStr = d + " " + month + " " + year + " " + itime.substr(0,itime.indexOf(' '));

    return retStr;
}

/** =============================
 *  Candle-stick, Scalper Scales
 *  =============================
 **/
var xScale = new Plottable.Scales.Category();
var yScale = new Plottable.Scales.Linear();

/** ==============================
 *  Candle-stick, Trend-Line Axes
 *  ==============================
 **/
var xAxis = new Plottable.Axes.Category(xScale, "bottom")
    .formatter(function() { return ""; })
    .innerTickLength(0)
    .endTickLength(0)
    .annotationsEnabled(true)
    .annotationTierCount(2)
    .addClass("candle-stick-label");

var yAxis = new Plottable.Axes.Numeric(yScale, "right")
    .formatter(new Plottable.Formatters.fixed(5))
    .addClass("candle-stick-label");

var commonXscale = new Plottable.Scales.Category();
var overlayXaxis = new Plottable.Axes.Category(commonXscale, "bottom")
    .addClass("candle-stick-label");

var overlayYaxis = new Plottable.Axes.Numeric(yScale, "right")
    .formatter(new Plottable.Formatters.fixed(5))
    .addClass("candle-stick-label");

/** =============================
 *  Strength-index Scales & Axes
 *  =============================
 **/
var csiColumnXScale = new Plottable.Scales.Category();
var csiColumnYScale = new Plottable.Scales.Linear()
    .domain([-4, 4]);

var csiColumnXAxis = new Plottable.Axes.Category(csiColumnXScale, "bottom")
    .formatter(function() { return ""; })
    .innerTickLength(0)
    .endTickLength(0)
    .annotationsEnabled(true)
    .addClass("strength-visibility")
    .addClass("candle-stick-label");

var csiColumnYAxis = new Plottable.Axes.Numeric(csiColumnYScale, "right")
    .formatter(function(d) { return d.toString(); })
    .tickLabelPadding(0)
    .addClass("csi-label")
    .addClass("strength-visibility");

var csiColorscale = new Plottable.Scales.Color()
    .domain(["4", "3", "2", "1", "-1", "-2", "-3", "-4"])
    .range(["#d0fb99", "#afe2ad", "#7fbf9c", "#418686", "#fb9b00", "#fb7300", "#fa4602", "#f90006"]);

/** =================
 *  Guideline X/Y
 *  =================
 **/
var guidelineX = new Plottable.Components.GuideLineLayer(Plottable.Components.GuideLineLayer.ORIENTATION_VERTICAL)
    .scale(xScale)
    .addClass("guideline-visibility");

var guidelineY = new Plottable.Components.GuideLineLayer(Plottable.Components.GuideLineLayer.ORIENTATION_HORIZONTAL)
    .scale(yScale)
    .addClass("guideline-visibility");

var guidelineCP = new Plottable.Components.GuideLineLayer(Plottable.Components.GuideLineLayer.ORIENTATION_HORIZONTAL)
    .scale(yScale)
    .addClass("price-visibility");


/** =================
 *  Tool-tip
 *  =================
 **/
Tooltip = function (container) {
    this.container = container;
};

Tooltip.prototype.create = function () {
    var div = d3.select(this.container).append("div").attr("class", "tool-tip-div");

    this.dom = div;
}

Tooltip.prototype.setText = function (text) {
    this.dom.html(text);
}

Tooltip.prototype.show = function () {
    this.dom.style("display", "block");
}

Tooltip.prototype.setX = function (n) {
    var offsetWidth = this.dom[0][0]['offsetWidth'];
    this.dom.style("left", (n - offsetWidth - 20) + "px");
}

Tooltip.prototype.setY = function (n) {
    var offsetHeight = this.dom[0][0]['offsetHeight'];
    this.dom.style("top", (n - offsetHeight) + "px");
}

Tooltip.prototype.hide = function () {
    this.dom.style("display", "none");
}

YAxisAnnotation = function(container) {
    this.container = container;
};

YAxisAnnotation.prototype.create = function() {
    var div = d3.select(this.container).append("div").attr("class", "annotation-y-div");

    this.dom = div;
}

YAxisAnnotation.prototype.setText = function(text) {
    this.dom.html(text);
}

YAxisAnnotation.prototype.setX = function() {
    var offset = helperLine.width() + 10;
    this.dom.style("left", offset + "px");
}

YAxisAnnotation.prototype.setY = function(n) {
    this.dom.style("top", (n + 20) + "px");
}

YAxisAnnotation.prototype.show = function() {
    this.dom.style("display", "block");
}

YAxisAnnotation.prototype.hide = function() {
    this.dom.style("display", "none");
}


/*********************************************** Chart Components ***********************************************/


cv_settings();


/*********************************************** Donot Touch - 1 ***********************************************/
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
    //console.log('cv_server_store:' + JSON.stringify(data));


    if(typeof cv_user_email == 'undefined')
    {
        //console.log('Unable to save settings, no email set..');
        return false;
    }
    $.post( "http://" + cv_domain + '/user/update/' + cv_user_email , data )
        .done(function( data ) {
            //console.log('server response: ' + data);

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
/*********************************************** Donot Touch - 2 ***********************************************/

function hideLaser() {
    $(".laser-visibility").css("opacity", 0);
    $("#chart-trend-detector").hide("fast");
    toggleLaser = false;
    //hasLaser = false;
    localStorage.setItem('toggleLaser', false);
}

function showLaser(){
    $(".laser-visibility").css("opacity", 1);
    $("#chart-trend-detector").show("fast");

    toggleLaser = true;
    //hasLaser = true;
    localStorage.setItem('toggleLaser', true);
}

function hideScalper() {
    scalperController.hide();
    $("#scalphero").hide("fast");
    toggleScalper = false;
    //hasScalper = false;
    localStorage.setItem('toggleScalper', false);
}
function showScalper() {
    scalperController.show();
    $("#scalphero").show("fast");

    toggleScalper = true;
    //hasScalper = true;
    localStorage.setItem('toggleScalper', true);

}

function hideCSI() {
    $(".strength-visibility").css("opacity", 0);
    $("#strengthometer").hide("fast");
    toggleCSI = false;
    //hasCSI = false;
    localStorage.setItem('toggleCSI', false);
}

function showCSI() {
    $(".strength-visibility").css("opacity", 1);
    $("#strengthometer").show("fast");
    toggleCSI = true;
    //hasCSI = true;
    localStorage.setItem('toggleCSI', true);
}

/*********************************************** Donot Touch - 1 ***********************************************/
function laserVisible() {
    if(toggleLaser) {
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
/*********************************************** Donot Touch - 2 ***********************************************/

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
            update_chart('cv');
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

/*********************************************** Donot Touch - 1 ***********************************************/
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
/*********************************************** Donot Touch - 2 ***********************************************/

function render_chart(chart_type) {
    $('.loading').fadeIn('fast');

    set_autoupdate_timer();

    /*********************************************** Chart Everything ***********************************************/
    var url = "http://" + cv_domain +"/" + chart_type + "/" + cv_currency_pair + "/" + cv_timeframe + "/" + cv_size;

    d3.json(url, function(error, data) {
        if(typeof data.response == "undefined" || data.response == 0) {
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
                dateString:     customDateFormat(d.time),
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
                volume:         (d.csi_bull == "0") ? d.csi_bear : d.csi_bull
            };
        });

        data = data.reverse();
        _data = data;

        for(var p=0; p<6; p++) {
            data.push({
                id:             p + 1,
                date:           parseDate("2016-1-1 00:00:00"),
                dateString:     'null' + p + 1,
                open:           undefined,
                high:           undefined,
                low:            undefined,
                close:          undefined,
                csi_bear:       undefined,
                csi_bull:       undefined,
                laser_bull:     undefined,
                laser_bear:     undefined,
                laser_buy:      undefined,
                laser_sell:     undefined,
                laser_av:       undefined,
                laser_mm:       undefined,
                scalper_buy:    undefined,
                scalper_sell:   undefined,
                scalper_bg:     undefined,
                volume:         undefined
            });
        }

        var maxYExtent = d3.max(_data, function(d) { return d.high; });
        var minYExtent = d3.min(_data, function(d) { return d.low; });
        delta = (maxYExtent - minYExtent) / 8;

        /** ===============
         *  Overlay x-axis 11 25 38 52 65
         *  ===============
         **/
        var timeArr = [],
        noOfRecords = parseInt(cv_size) + 6; // 6 extra for gapping
        initialVal = Math.floor(noOfRecords / 6) - 1;
        stepSize = Math.ceil(noOfRecords / 6);
        for(var i=initialVal; i<noOfRecords; i += stepSize) {
            timeArr.push(_data[i]['dateString']);
        }

        commonXscale.domain(timeArr);

        // Store tick data
        cv_notify(_data[_data.length - 7]);
        localStorage.setItem('cv_tick_data', JSON.stringify(_data[_data.length - 7]));


        /** ==========
         *  Datasets
         *  ==========
         **/
        dataset = new Plottable.Dataset(_data);

        trendDatasetG = new Plottable.Dataset(_data, { name: "laser_bull" });
        trendDatasetR = new Plottable.Dataset(_data, { name: "laser_bear" });

        var selection = ["laser_bull", "laser_bear"];

        /** ===============
         *  Candle-stick
         *  ===============
         **/
        wicks = new Plottable.Plots.Segment()
            .addDataset(dataset)
            .x(function(d) { return d['dateString']; }, xScale)
            .y(function(d) { return d['high']; }, yScale)
            .y2(function(d) { return d['low']; })
            .attr("stroke-width", 1)
            .attr("stroke", function(d) {
                if (d['close'] > d['open']) {
                    return greenColor;
                } else {
                    return redColor;
                }
            });

        candles = new Plottable.Plots.Rectangle()
            .addDataset(dataset)
            .x(function(d) { return d['dateString']; }, xScale)
            .y(function(d) { return d['open']; }, yScale)
            .y2(function(d) { return d['close']; }, yScale)
            .attr("fill", function(d) {
                if (d['close'] > d['open']) {
                    return greenColor;
                } else {
                    return redColor;
                }
            })
            .attr("stroke-width",0)
            .attr("stroke", "#035D99");

        // for interaction purpose only
        helperLine = new Plottable.Plots.Line()
            .addDataset(dataset)
            .x(function(d) { return d['dateString']; }, xScale)
            .y(function(d) { return d['high']; }, yScale)
            .attr("stroke-width", 0)
            .attr("stroke", "#666");

        var currentPrice = data[data.length - 7]['open'];
        guidelineCP.value(currentPrice);

        //var currentPrice = data[data.length - 6]['open'];
        c_price = currentPrice;
        //console.log('current price:' + c_price);

        /** ============
         *  Trend-line
         *  ============
         **/

        if(hasLaser) {
            laserPlot = new Plottable.Plots.Line()
                .addDataset(trendDatasetG)
                .addDataset(trendDatasetR)
                .x(function(d) { return d['dateString']; }, xScale)
                .y(function(d,i,dataset) {
                    for(var i=0; i<selection.length; i++) {
                        if(dataset.metadata().name == selection[i]) {
                            return d[selection[i]] != 0 ? ((selection[i] == "laser_bull") ? (d['close'] -  delta) : (d['close'] + delta)) : undefined;
                        }
                    }
                }, yScale)
                .attr("stroke", function(d,i,dataset) {
                    for(var i=0; i<selection.length; i++) {
                        if(dataset.metadata().name == selection[i]) {
                            return selection[i] != "laser_bear" ? greenColor : redColor;
                        }
                    }
                })
                .attr("opacity", 0.7)
                .attr("stroke-width", 3.5)
                .interpolator("cardinal");

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
        }


        /** =================
         *  Strength-index
         *  =================
         **/
        if(hasCSI) {
            csiPlot = new Plottable.Plots.Bar()
                .addDataset(dataset)
                .x(function(d) { return d['dateString']; }, csiColumnXScale)
                .y(function(d) { return d['volume']; }, csiColumnYScale)
                .attr("fill", function(d, i, dataset) { return d['volume']; }, csiColorscale)
                .attr("stroke-width",0)
                .attr("stroke", "#035D99")
                .addClass("strength-visibility");
        } else {
            csiPlot = null;
            csiColumnXAxis = null;
        }


        /** =======================
         *  Dragbox Strength-index
         *  =======================
         **/
        /*if(hasCSI) {
         var dragBox = new Plottable.Components.XDragBoxLayer()
         .resizable(true)
         .movable(true);

         dragBox.onDrag(function(bounds) {
         var min = csiColumnXScale.invert(bounds.topLeft.x);
         var max = csiColumnXScale.invert(bounds.bottomRight.x);
         xScale.domain([min, max]);
         });

         dragBox.onDragEnd(function(bounds) {
         if(bounds.topLeft.x === bounds.bottomRight.x) {
         xScale.domain(csiColumnXScale.domain());
         }
         });
         }

         xScale.onUpdate(function() {
         var xDomain = xScale.domain();
         if(hasCSI) {
         dragBox.boxVisible(true);
         dragBox.bounds({
         topLeft: { x: csiColumnXScale.scale(xDomain[0]), y: null },
         bottomRight: { x: csiColumnXScale.scale(xDomain[1]), y: null }
         });
         }
         });*/

        /** ================
         *  Plot Parameters
         *  ================
         **/
        if(hasLaser) {
            mainPlot = new Plottable.Components.Group();
            mainPlot.append(laserPlot);
            laserPlot.addClass("laser-visibility");
        }

        var candlesticks = new Plottable.Components.Group([wicks, guidelineX, guidelineY, guidelineCP, candles]);
        mainPlot.append(candlesticks);
        mainPlot.append(helperLine);

        var commonXaxis = new Plottable.Components.Group([xAxis, overlayXaxis]);
        var commonYaxis = new Plottable.Components.Group([overlayYaxis, yAxis]);

        var label_y = new Plottable.Components.AxisLabel("PRICE($)", 0);

        chart = new Plottable.Components.Table([
            [null,                  label_y,            null],
            [mainPlot,              commonYaxis,        null],
            [commonXaxis,           null,               null],
            [csiPlot,               csiColumnYAxis,     null],
            [csiColumnXAxis,        null,               null]
        ]);

        chart.rowWeight(3, 0.2);
        chart.renderTo(svg);

        if(hasCSI) {
            background = createStrippedBackground(csiPlot);
            background.drawAt();
            background.show();
        }

        scalperController = createScalperContainer(helperLine);
        scalperDrawer(data, scalperController, parseInt(cv_size));

        tooltip = new Tooltip("#chart-market-container");
        tooltip.create();

        currentPriceY = new YAxisAnnotation("#chart-market-container");
        currentPriceY.create();
        currentPriceY.show();
        currentPriceY.setX(helperLine.width());
        currentPriceY.setY(yScale.scale(currentPrice));
        currentPriceY.setText(currentPrice);

        priceY = new YAxisAnnotation("#chart-market-container");
        priceY.create();

        /** ===============
         *  Interactions
         *  ===============
         **/
        var interaction = new Plottable.Interactions.Pointer();

        // Inside the plot environment
        interaction.onPointerMove(function(point) {
            // Refresh everything
            var nearestEntity = helperLine.entityNearest(point);
            var laserText = "N/A",
                csiText = "N/A",
                scalperText = "N/A";

            if(nearestEntity) {
                $('.guideline-visibility').css("opacity", 1);

                candles.entities().forEach(function(entity) {
                    if(entity.index === nearestEntity.index) {
                        entity.selection.attr("stroke-width",1);
                    } else {
                        entity.selection.attr("stroke-width",0);
                    }
                });

                var date = nearestEntity.datum.dateString;
                var price = d3.round(yScale.invert(point.y), 5);

                guidelineX.value(date);
                guidelineY.value(price);
                xAxis.annotatedTicks([date]);
                xAxis.annotationFormatter(function() {
                    return date.toLocaleString();
                });

                $("#mkt-date strong").text(date);
                $("#mkt-price strong").text(price);
                $("#mkt-current-price strong").text(c_price);



                priceY.setText(price);
                priceY.show();
                priceY.setX();
                priceY.setY(point.y);

                if(hasLaser) {
                    var buy = nearestEntity.datum.laser_buy,
                        sell = nearestEntity.datum.laser_sell,
                        bear = nearestEntity.datum.laser_bear,
                        bull = nearestEntity.datum.laser_bull;

                    if(bull == bear && sell > 0) {
                        laserText = "Sell Trend";
                        $("#ctd-trend strong").text(laserText);
                    } else if (bull == bear && buy > 0) {
                        laserText = "Buy Trend";
                        $("#ctd-trend strong").text(laserText);
                    } else if(bear > 0) {
                        laserText = "Sell Trend";
                        $("#ctd-trend strong").text(laserText);
                    } else if(bull > 0) {
                        laserText = "Buy Trend";
                        $("#ctd-trend strong").text(laserText);
                    }
                }

                if(hasScalper) {
                    var bg = nearestEntity.datum.scalper_bg,
                        buy = nearestEntity.datum.scalper_buy,
                        sell = nearestEntity.datum.scalper_sell;

                    if(bg == 1 && bg == buy) {
                        scalperText = "Buy";
                        $("#sh-strength strong").text(scalperText);
                    } else if(bg == -1 && bg == sell) {
                        scalperText = "Sell";
                        $("#sh-strength strong").text(scalperText);
                    } else {
                        $("#sh-strength strong").html('<strong><span class="na">N/A</span>');
                    }
                }

                if(hasCSI) {
                    csiPlot.entities().forEach(function(entity) {
                        if(entity.index === nearestEntity.index) {
                            entity.selection.attr("stroke-width",1);
                        } else {
                            entity.selection.attr("stroke-width",0);
                        }
                    });

                    csiColumnXAxis.annotatedTicks([date]);
                    csiColumnXAxis.annotationFormatter(function() {
                        return nearestEntity.datum.volume;
                    });

                    csiText = nearestEntity.datum.volume;

                    switch(parseInt(csiText))
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

                    $("#som-strength strong").text(csiText + " " + csi_desc);
                }

                if(isMouseInfo) {
                    var dateText = "<strong>Date : </strong>" + date;
                    var priceText = "<strong>Price : </strong>" + price;
                    tooltipText = dateText + "<br>" + priceText + "<br>";

                    if(hasLaser && toggleLaser)
                        tooltipText += "<strong>Trend : </strong>" + laserText + "<br>";

                    if(hasCSI && toggleCSI)
                        tooltipText += "<strong>Strength : </strong>" + csiText + "<br>";

                    if(hasScalper && toggleScalper)
                        tooltipText += "<strong>Scalping Hero : </strong>" + scalperText;

                    tooltip.setText(tooltipText);
                    tooltip.show()
                    tooltip.setX(point.x);
                    tooltip.setY(point.y);
                }
            }
        })

        // Outside the plot environment
        interaction.onPointerExit(function() {
            // Reset everything
            $('.guideline-visibility').css("opacity", 0);
            xAxis.annotatedTicks([]);
            hasCSI && csiColumnXAxis.annotatedTicks([]);

            $("#ctd-trend strong").html('<strong><span class="na">N/A</span>');
            $("#sh-strength strong").html('<strong><span class="na">N/A</span>');
            $("#som-strength strong").html('<strong><span class="na">N/A</span>');
            tooltip.hide();
            priceY.hide();
        });

        interaction.attachTo(mainPlot);

        /** ===================
         *  Pan-zoom operation
         *  ===================
         **/
        /*var pzi = new Plottable.Interactions.PanZoom(xScale, null)
         .maxDomainExtent(xScale, maxZoom)
         .minDomainExtent(xScale, minZoom)
         .attachTo(candlesticks);*/


        /** ================================
         *  State-preservation upon refresh
         *  ================================
         **/
        if(hasLaser && !toggleLaser) hideLaser();
        if(hasCSI && !toggleCSI) hideCSI();
        if(hasScalper && !toggleScalper) hideScalper();

        $('.loading').fadeOut('fast');
    });
    /*********************************************** Chart Everything ***********************************************/

    window.addEventListener("resize", function() {
        chart.redraw();
        scalperController = createScalperContainer(helperLine);
        scalperDrawer(data, scalperController, parseInt(cv_size));
    });
}

function update_chart(chart_type) {
    $('.outline').css("display", "none"); // here outline is a class of svg
    $('.loading').fadeIn('fast');

    set_autoupdate_timer();
    currentPriceY.hide();

    /*********************************************** Chart Everything ***********************************************/
    var url = "http://" + cv_domain +"/" + chart_type + "/" + cv_currency_pair + "/" + cv_timeframe + "/" + cv_size;

    d3.json(url, function(error, data) {
        if(typeof data.response == "undefined" || data.response == 0) {
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
                dateString:     customDateFormat(d.time),
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
                volume:         (d.csi_bull == "0") ? d.csi_bear : d.csi_bull
            };
        });

        data = data.reverse();
        _data = data;

        for(var p=0; p<6; p++) {
            data.push({
                id:             p + 1,
                date:           parseDate("2016-1-1 00:00:00"),
                dateString:     'null' + p + 1,
                open:           undefined,
                high:           undefined,
                low:            undefined,
                close:          undefined,
                csi_bear:       undefined,
                csi_bull:       undefined,
                laser_bull:     undefined,
                laser_bear:     undefined,
                laser_buy:      undefined,
                laser_sell:     undefined,
                laser_av:       undefined,
                laser_mm:       undefined,
                scalper_buy:    undefined,
                scalper_sell:   undefined,
                scalper_bg:     undefined,
                volume:         undefined
            });
        }

        var maxYExtent = d3.max(_data, function(d) { return d.high; });
        var minYExtent = d3.min(_data, function(d) { return d.low; });
        delta = (maxYExtent - minYExtent) / 8;

        /** ===============
         *  Overlay x-axis
         *  ===============
         **/
        var timeArr = [],
            noOfRecords = parseInt(cv_size) + 6; // 6 extra for gapping
        initialVal = Math.floor(noOfRecords / 6) - 1;
        stepSize = Math.ceil(noOfRecords / 6);
        for(var i=initialVal; i<noOfRecords; i += stepSize) {
            timeArr.push(_data[i]['dateString']);
        }

        commonXscale.domain(timeArr);


        /** =================
         *  Update Datasets
         *  =================
         **/
        dataset.data(_data);
        trendDatasetG.data(_data);
        trendDatasetR.data(_data);

        scalperController = createScalperContainer(helperLine);
        scalperDrawer(_data, scalperController, parseInt(cv_size));

        var currentPrice = data[data.length - 7]['open'];
        c_price = currentPrice;
        //console.log('current price:' + c_price);


        guidelineCP.value(currentPrice);

        currentPriceY.setX(helperLine.width());
        currentPriceY.setY(yScale.scale(currentPrice));
        currentPriceY.setText(currentPrice)
        currentPriceY.show();

        /** =================
         *  Update Laser
         *  =================
         **/
        if(hasLaser) {
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
        }

        // Store tick data
        cv_notify(_data[_data.length - 7]);
        localStorage.setItem('cv_tick_data', JSON.stringify(_data[_data.length - 7]));

        /** ================================
         *  State-preservation upon refresh
         *  ================================
         **/
        if(hasLaser && !toggleLaser) hideLaser();
        if(hasCSI && !toggleCSI) hideCSI();
        if(hasScalper && !toggleScalper) hideScalper();

        $('.loading').fadeOut('fast');
        $('.outline').css("display", "block");
    });
}

$( document ).ready(function() {

/*

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
*/
    //slider.slideReveal("show");
    set_autoupdate_timer(); //set autoupdate timer if applicable


    $('#btn-select-currency-pair b').text(cv_currency_pair.toUpperCase());
    $('#btn-select-timeframe b').text(cv_timeframe.toUpperCase());
    $('#chart_currency_pair ul li a').click(function(e){
        cv_currency_pair = $(this).text();
        $('#btn-select-currency-pair b').text(cv_currency_pair);
        localStorage.setItem('pair',cv_currency_pair);
        update_chart('cv'); // suvrat
    });

    $('#chart_timeframe ul li a').click(function(e){
        cv_timeframe = $(this).text();
        $('#btn-select-timeframe b').text(cv_timeframe);
        localStorage.setItem('timeframe',cv_timeframe);
        update_chart('cv'); // suvrat
    });


    $(function() {
        $('#chart-info-panel .panel').matchHeight();
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


    $('#checkbox-mouseinfo').on('click',function(){ // make changes here
        var _checked = $('#checkbox-mouseinfo').is(":checked");
        if(!_checked)
        {
            localStorage.setItem('isMouseInfo', false);
            isMouseInfo = false;
        }
        else if(_checked) {
            localStorage.setItem('isMouseInfo', true);
            isMouseInfo = true;
        }
    });

    /*********************************************** Donot Touch - 1 ***********************************************/

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
        $('#cv-settings').fadeOut('fast',function(){$('#cv-tools').fadeIn('fast')});
        $('#cv-notifications').fadeOut('fast',function(){$('#cv-tools').fadeIn('fast')});
        $('.cv-tab').removeClass('active');
        $('#tab-tools').addClass('active');
    });

    $('#tab-notifications a').on('click',function(e){
        $('#cv-settings').fadeOut('fast',function(){$('#cv-notifications').fadeIn('fast')});
        $('#cv-tools').fadeOut('fast',function(){$('#cv-notifications').fadeIn('fast')});
        $('.cv-tab').removeClass('active');
        $('#tab-notifications').addClass('active');
    });


    $('#tab-settings a').on('click',function(e){
        $('#cv-tools').fadeOut('fast',function(){$('#cv-settings').fadeIn('fast')});
        $('#cv-notifications').fadeOut('fast',function(){$('#cv-settings').fadeIn('fast')});
        $('.cv-tab').removeClass('active');
        $('#tab-settings').addClass('active');
    });

    $('#cv-settings').fadeOut('fast',function(){$('#cv-tools').fadeIn('fast')});
    $('#cv-notifications').fadeOut('fast',function(){$('#cv-tools').fadeIn('fast')});

    /*********************************************** Donot Touch - 2 ***********************************************/



    /*********************************************** Donot Touch - 1 ***********************************************/

        //$("[name='checkbox']").bootstrapSwitch();
        //$('.multi-select').multiselect();
    $(".notification-switch").bootstrapSwitch();

    render_chart('cv'); // suvrat

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

    /*********************************************** Donot Touch - 2 ***********************************************/


});