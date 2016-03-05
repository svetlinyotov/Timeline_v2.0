var defaultpos2 = new google.maps.LatLng(42.90221762882881,23.799324822425888);
var geocoder2;
var marker2;
var map2;
var infowindow2 = new google.maps.InfoWindow();
var input2;

function initialize() {
    geocoder2 = new google.maps.Geocoder();
    var mapOptions = {
        zoom: 7,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        center: defaultpos2
    };

    map2 = new google.maps.Map(document.getElementById("map-address-new"),
        mapOptions);

    marker2 = new google.maps.Marker({
        map:map2,
        draggable:true,
        animation: google.maps.Animation.DROP,
        position: defaultpos2
    });
    google.maps.event.addListener(marker2, 'click', toggleBounce);
    google.maps.event.addListener(marker2, 'dragend', function(evt){
        document.getElementById('coordinates_edit_text').innerHTML = marker2.getPosition().lat() + "," + marker2.getPosition().lng();
        document.getElementById('coordinates_edit').value = marker2.getPosition().lat() + "," + marker2.getPosition().lng();
    });
    google.maps.event.addListener(marker2, 'dragend', function(evt){
        document.getElementById('address_search').value = "";
    });
    google.maps.event.addListener(marker2, 'dragend', codeLatLngNew);
    map2.setCenter(new google.maps.LatLng(marker2.getPosition().lat(), marker2.getPosition().lng()));
}

function toggleBounce() {

    if (marker2.getAnimation() != null) {
        marker2.setAnimation(null);
    } else {
        marker2.setAnimation(google.maps.Animation.BOUNCE);
    }
}

function codeAddressNew() {
    var address = document.getElementById('address_search').value;
    geocoder2.geocode( { 'address': address}, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            document.getElementById("coordinates_edit_text").innerHTML = results[0].geometry.location;
            document.getElementById("coordinates_edit").value = results[0].geometry.location;
            marker2.setPosition(results[0].geometry.location);
            codeLatLngNew(results[0].geometry.location);
            map2.setZoom(11);
            map2.setCenter(google.maps.LatLng(marker2.getPosition().lat(), marker2.getPosition().lng()));
        }
    });
}

function codeLatLngNew(input2) {
    input2 = marker2.getPosition().lat() + "," + marker2.getPosition().lng();
    var latlngStr = input2.split(',', 2);
    var lat = parseFloat(latlngStr[0]);
    var lng = parseFloat(latlngStr[1]);
    var latlng = new google.maps.LatLng(lat, lng);
    geocoder2.geocode({'latLng': latlng}, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            if (results[1]) {
                map2.setCenter(new google.maps.LatLng(marker2.getPosition().lat(), marker2.getPosition().lng()));
                map2.setZoom(11);
                document.getElementById('address_edit_text').innerHTML=results[0].formatted_address;
                document.getElementById('address_edit').value=results[0].formatted_address;
            } else {
                alert('No results found');
            }
        }
    });
}

google.maps.event.addDomListener(window, 'load', initialize);