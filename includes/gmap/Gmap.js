//<![CDATA[
google.load("maps", "2.x", {"language" : "en"});
      //var side_bar_html = "";
      var gmarkers = [];
      var htmls = [];
      var i = 0;
  // This function picks up the click and opens the corresponding info window
  function myclick(i) {
    gmarkers[i].openInfoWindowHtml(htmls[i]);
  }

  function MaxContentTab(label, content) {
    this.label_ = label;
    this.contentNode_ = createEl('div', null, content, null, null);
    this.navNode_ = null;
  }

  /**
   * Create an element
   * @param {String} tag of element
   * @param {Object} attrs name-value of attributes as json
   * @param {String|Node} content DOM node or HTML
   * @param {Object} style css object to set to the element
   * @param {Node} parent if supplied, the node will be appended to the parent
   * @return {Node} the new or modified node
   */

  var createEl = function (tag, attrs, content, style, parent) {
    var node = content;
    if (!content || (content && typeof content === 'string')) {
      node = document.createElement(tag);
      node.innerHTML = content || '';
    }

    if (style) {
      setVals(node.style, style);
    }

    if (attrs) {

      setVals(node, attrs);

    }

    if (parent) {

      parent.appendChild(node);

    }

    return node;

  };


function initialize() {
    if (GBrowserIsCompatible()) {



    /**
     * hoverControls method to hide/show GControls by 'mouseout'/'mouseover'
     * @author Esa 2006, 2008
     */
    GMap2.prototype.hoverControls = function(opt_noCloseIw){
      var theMap = this;
      theMap.hideControls();
      GEvent.addListener(theMap, "mouseover", function(){
        theMap.showControls();
      });
      GEvent.addListener(theMap, "mouseout", function(){
        theMap.hideControls();
      });
      theMap.libraryCard = 19162;
    }
    GMap.prototype.hoverControls = GMap2.prototype.hoverControls;





      // Display the map, with some controls
      //var map = new google.maps.Map2(document.getElementById("map"), {draggableCursor:"crosshair"});
      var map = new google.maps.Map2(document.getElementById("map"));
      map.addControl(new GLargeMapControl());
      map.addControl(new GMapTypeControl());
      map.setCenter(new GLatLng(14, 11), 2);
      map.hoverControls();
        GEvent.addListener(map, 'click', function(ov, latlng, ovll) {
          if (latlng) {
            var regular = '<div>LatLng:' + latlng + '</div><a href="javascript:void(0)" onclick="javascript:map.getInfoWindow().maximize()">more info.. </a>'
            var summary = '<div id="sum">Address of' + latlng + '</div><a href="javascript:void(0)" onclick="javascript:map.getInfoWindow().restore()">less info.. </a>';
            var panoDiv = document.createElement('div');
            panoDiv.style.width = "400px"; // can be anything, will be auto resized
            panoDiv.style.width = "200px";
            var tabs = [new MaxContentTab('address', '<div id="address"></div>'), new MaxContentTab('streetview', panoDiv)];
            map.openMaxContentTabsHtml(latlng, regular, summary, tabs, {
              maxTitle: "More Info",
              selectedTab: 'streetview',// or use index 1,
              style: {
                tabOff: {
                  backgroundColor: '#CCCCFF'
                }
              },maximized: document.getElementById('maximized').checked
            });
          }
        });



      //map.addControl(new MStatusControl());

      // arrays to hold copies of the markers and html used by the side_bar
      // because the function closure trick doesnt work there

      // ================================================================
      // === Define the function thats going to process the JSON file ===
      process_it = function(doc) {
        // === Parse the JSON document ===
        var jsonData = eval('(' + doc + ')');
        if(jsonData.markers.length>0){
        // === Plot the markers ===
        for (var i=0; i<jsonData.markers.length; i++) {
          var point = new GLatLng(jsonData.markers[i].lat, jsonData.markers[i].lng);
          var marker = createMarker(point, jsonData.markers[i].label, jsonData.markers[i].html);
          map.addOverlay(marker);
        }
        }
        // put the assembled side_bar_html contents into the side_bar div
        //document.getElementById("side_bar").innerHTML = side_bar_html;
        }

      // A function to create the marker and set up the event window
      function createMarker(point,name,html) {
        var marker = new GMarker(point);
        GEvent.addListener(marker, "click", function() {
          marker.openInfoWindowHtml(html);
        });
        // save the info we need to use later for the side_bar
        gmarkers[i] = marker;
        htmls[i] = html;
        // add a line to the side_bar html
        //side_bar_html += '<a href="javascript:myclick(' + i + ')">' + name + '<\/a><br>';
        i++;
        return marker;
      }


      // ================================================================
      // === Fetch the JSON data file ====
      GDownloadUrl("http://www.noblood.org/map.php?do=json", process_it);
      //GDownloadUrl("http://gv.xchg.com/nb/map/", process_it);
      //GDownloadUrl("includes/gmap/json.dat", process_it);
      // ================================================================

    }

    else {
      alert("Sorry, the Google Maps API is not compatible with this browser");
    }
}


google.setOnLoadCallback(initialize);
//]]>
