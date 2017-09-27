var geoapi_url = "http://caching-atm.net/geo_api/geo.php?token=c9a04e098a082d7d9fad534670123aab";
var geoapi_area_selected;
var geoapi_prefecture_selected;
var geoapi_city_selected;
var geoapi_station_selected;

//初めに呼びだす関数 初期化と変更
$("body").ready(geoApiInitialize);
function geoApiInitialize() {
    if ($("#geoapi-areas").length > 0) {
        geoApiInitializeAreas();
    }
    if ($("#geoapi-prefectures").length > 0) {
        geoApiInitializePrefectures();
    }
    if ($("#geoapi-cities").length > 0) {
        geoApiInitializeCities();
    }
    geoApiInitializeStations();
    $("#geoapi-areas").change(geoApiChangeArea);
    $("#geoapi-prefectures").change(geoApiChangePrefecture);
    $("#geoapi-cities").change(geoApiChangeCity);
    $("#geoapi-stations").change(geoApiChangeStation);
}

//変更されたとき 子要素を初期化し、パラメータを送り、次の段階をセットする
function geoApiChangeArea () {
    geoapi_area_selected = $("#geoapi-areas option:selected");
    geoApiInitializePrefectures();
    geoApiInitializeCities();
    geoApiInitializeStations();
    if (geoapi_area_selected.val() == 'エリアを選択してください') {
      return;
    }
       $.ajax({
        url:geoapi_url,
        type:'GET',
        data:{"area": geoapi_area_selected.text() },
        success:function(data) {
            $(data).find("geodata").each(function(){
                $("#geoapi-prefectures").append('<option value='+$(this).find("prefecture").text()+'>'+$(this).find("prefecture").text()+'</option>');
            });
        }
    });
}

function geoApiChangePrefecture () {
    geoapi_prefecture_selected = $("#geoapi-prefectures option:selected");
    geoApiInitializeCities();
    geoApiInitializeStations();
    if (geoapi_prefecture_selected.val() == '県名を選択してください') {
      return;
    }
       $.ajax({
        url:geoapi_url,
        type:'GET',
        data:{"prefecture": geoapi_prefecture_selected.text() },
        success:function(data) {
            $(data).find("geodata").each(function(){
                $("#geoapi-cities").append('<option value='+$(this).find("city").text()+'>'+$(this).find("city").text()+'</option>');
            });
        }
    });
}

function geoApiChangeCity () {
    geoapi_city_selected = $("#geoapi-cities option:selected");
    geoApiInitializeStations();
    if (geoapi_city_selected.val() == '市区町村名を選択してください') {
      return;
    }
       $.ajax({
        url:geoapi_url,
        type:'GET',
        data:{"city": geoapi_city_selected.text() },
        success:function(data) {
            $(data).find("geodata").each(function(){
                $("#geoapi-stations").append('<option value='+$(this).find("station").text()+'>'+$(this).find("station").text()+'</option>');
            });
        }
    });
}

function geoApiChangeStation () {
    geoapi_station_selected = $("#geoapi-stations option:selected");
    if (geoapi_station_selected.val() == '駅名を選択してください') {
      return;
    }
    $.ajax({
        url:geoapi_url,
        type:'GET',
        data:{"station": geoapi_station_selected.text() },
    });
}


//初期化
function geoApiInitializeAreas() {
    $("#geoapi-areas").val("エリアを選択してください");
        $.ajax({
        url:geoapi_url,
        type:'GET',
        data:'xml',
        success:function(data) {
            $(data).find("geodata").each(function(){
                $("#geoapi-areas").append('<option value='+$(this).find("area").text()+'>'+$(this).find("area").text()+'</option>');
            });
        }
    });
   //     $("#geoapi-areas").empty();
  //  $("#geoapi-areas").append("<option value='エリアを選択してください'>エリアを選択してください</option>");
}
function geoApiInitializePrefectures() {
    $("#geoapi-prefectures").empty();
    $("#geoapi-prefectures").append("<option value='県名を選択してください'>県名を選択してください</option>");
}

function geoApiInitializeCities() {
    $("#geoapi-cities").empty();
    $("#geoapi-cities").append('<option value="市区町村名を選択してください">市区町村名を選択してください</option>');
}

function geoApiInitializeStations() {
    $("#geoapi-stations").empty();
    $("#geoapi-stations").html('<option value="駅名を選択してください">駅名を選択してください</option>');
}