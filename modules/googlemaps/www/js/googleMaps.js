    function loadGoogleMap()
    {
      if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("map"));
        map.setCenter(new GLatLng(45.799999, 15.97), 3);
        map.addControl(new GSmallMapControl());
        map.addControl(new GMapTypeControl());

        aCodes = getUserGeoCodes();
        for (var i = 0; i < aCodes.length; i++) {
            var point = new GLatLng(aCodes[i][0], aCodes[i][1]);
            map.addOverlay(new GMarker(point));
        }
      }
    }