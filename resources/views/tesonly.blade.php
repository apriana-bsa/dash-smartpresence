<!DOCTYPE HTML>
<html>
<head>
    <title>OpenLayers Demo</title>
    <style type="text/css">
        html, body, #basicMap {
            width: 100%;
            height: 100%;
            margin: 0;
        }
    </style>
    <script src="{{ asset('OpenLayers-2.13.1/OpenLayers.js') }}"></script>
    <script>
        function init() {
//            map = new OpenLayers.Map("basicMap");
//            var mapnik         = new OpenLayers.Layer.OSM();
//            var fromProjection = new OpenLayers.Projection("EPSG:4326");   // Transform from WGS 1984
//            var toProjection   = new OpenLayers.Projection("EPSG:900913"); // to Spherical Mercator Projection
//            var position       = new OpenLayers.LonLat(13.41,52.52).transform( fromProjection, toProjection);
//            var zoom           = 15;
//
//            map.addLayer(mapnik);
//            map.setCenter(position, zoom );
            map = new OpenLayers.Map("basicMap");
            map.addLayer(new OpenLayers.Layer.OSM());

            var lonLat = new OpenLayers.LonLat(	112.7819227 , -7.2761229 )
                .transform(
                    new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
                    map.getProjectionObject() // to Spherical Mercator Projection
                );

            var marker = new OpenLayers.LonLat( 112.79571443666 , -7.335119064726 )
                .transform(
                    new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
                    map.getProjectionObject() // to Spherical Mercator Projection
                );
            var zoom=16;

            var markers = new OpenLayers.Layer.Markers( "Markers" );
            map.addLayer(markers);

//            var marker = new khtml.maplib.overlay.Marker({
//                position: new khtml.maplib.LatLng(112.79571443666 , -7.335119064726),
//                map: map,
//                title:"static marker"
//            });

            markers.addMarker(new OpenLayers.Marker(lonLat));
            markers.addMarker(new OpenLayers.Marker(marker));

            map.setCenter (lonLat, zoom);

//            document.addEventListener("mousewheel", this.mousewheel.bind(this), { passive: false });
        }

        window.addEventListener ("touchmove", function (event) { event.preventDefault (); }, false);
        if (typeof window.devicePixelRatio != 'undefined' && window.devicePixelRatio > 2) {
            var meta = document.getElementById ("viewport");
            meta.setAttribute ('content', 'width=device-width, initial-scale=' + (2 / window.devicePixelRatio) + ', user-scalable=no');
        }
    </script>
</head>
<body onload="init();">
<div id="basicMap"></div>
</body>
</html>