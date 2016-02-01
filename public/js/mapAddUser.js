var defaultpos = new google.maps.LatLng(42.90221762882881,23.799324822425888);
var geocoder;
var marker;
var map;
var infowindow = new google.maps.InfoWindow();
var input;

function initialize() {
    geocoder = new google.maps.Geocoder();
    var mapOptions = {
        zoom: 7,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        center: defaultpos
    };

    map = new google.maps.Map(document.getElementById("map-address"),
        mapOptions);

    marker = new google.maps.Marker({
        map:map,
        draggable:true,
        animation: google.maps.Animation.DROP,
        position: defaultpos
    });
    google.maps.event.addListener(marker, 'click', toggleBounce);
    google.maps.event.addListener(marker, 'dragend', function(evt){
        document.getElementById('coordinates').innerHTML = marker.getPosition().lat() + "," + marker.getPosition().lng();
        document.getElementById('coordinates_type').value = marker.getPosition().lat() + "," + marker.getPosition().lng();
    });
    google.maps.event.addListener(marker, 'dragend', function(evt){
        document.getElementById('address_input').value = "";
    });
    google.maps.event.addListener(marker, 'dragend', codeLatLng);
    map.setCenter(new google.maps.LatLng(marker.getPosition().lat(), marker.getPosition().lng()));
}

function toggleBounce() {

    if (marker.getAnimation() != null) {
        marker.setAnimation(null);
    } else {
        marker.setAnimation(google.maps.Animation.BOUNCE);
    }
}

function codeAddress() {
    var address = document.getElementById('address_input').value;
    geocoder.geocode( { 'address': address}, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            document.getElementById("coordinates").innerHTML = results[0].geometry.location;
            document.getElementById("coordinates_type").value = results[0].geometry.location;
            marker.setPosition(results[0].geometry.location);
            codeLatLng(results[0].geometry.location);
            //map.setZoom(11);
            map.setCenter(google.maps.LatLng(marker.getPosition().lat(), marker.getPosition().lng()));
        }
    });
}

function codeLatLng(input) {
    input = marker.getPosition().lat() + "," + marker.getPosition().lng();
    var latlngStr = input.split(',', 2);
    var lat = parseFloat(latlngStr[0]);
    var lng = parseFloat(latlngStr[1]);
    var latlng = new google.maps.LatLng(lat, lng);
    geocoder.geocode({'latLng': latlng}, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            if (results[1]) {
                map.setCenter(new google.maps.LatLng(marker.getPosition().lat(), marker.getPosition().lng()));
                //map.setZoom(11);
                document.getElementById('address').innerHTML=results[0].formatted_address;
                document.getElementById('address_type').value=results[0].formatted_address;
            } else {
                alert('No results found');
            }
        }
    });
}

google.maps.event.addDomListener(window, 'load', initialize);