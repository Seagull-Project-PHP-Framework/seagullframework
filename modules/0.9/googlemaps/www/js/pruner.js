function Pruner(map)
{
    this.map = map;
    this.markers = new Array();
    this.timeout = null;

    GEvent.addListener(map, 'moveend', callback(display, this));
}

Pruner.prototype.addMarker = function(marker)
{
    marker.onMap = false;
    this.markers.push(marker);
    this.delay();
}

Pruner.prototype.delay = function()
{
    if (this.timeout != null) {
	    clearTimeout(this.timeout);
	}
    this.timeout = setTimeout(callback(display,this), 50);
}

function display(Pruner)
{
    var bounds = Pruner.map.getBounds();

    for (var i = 0; i < Pruner.markers.length; i++) {
        marker = Pruner.markers[i];
        if (!marker.onMap && bounds.contains(marker.getPoint())) {
            Pruner.map.addOverlay(marker);
            marker.onMap = true;
        } else {
            if (marker.onMap && !bounds.contains(marker.getPoint())) {
                Pruner.map.removeOverlay(marker);
                marker.onMap = false;
            }
        }
    }
}

function callback(func, arg)
{
    return function() { func(arg); }
}