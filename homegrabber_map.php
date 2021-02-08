<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$filtersq="mindim=".@$_GET['mindim']."&minprice=".@$_GET['minprice']."&maxprice=".@$_GET['maxprice']."&filtermode=".@$_GET['filtermode']."&nome_agenzia=".@$_GET['nome_agenzia']."&ag_domain=".@$_GET['ag_domain']."&maxdim=".@$_GET['maxdim']."&emq=".@$_GET['emq']."&txt=".@$_GET['txt']."&txt2=".@$_GET['txt2']."&loc=".@$_GET['loc'];
 

$kml_url="http://server/homegrabber/homegrabber_kml.php?".$filtersq;


$list_url="http://server/homegrabber/index.php?".$filtersq;

?>

<!doctype html>
<html lang="en">
  <head>
    <link rel="stylesheet" href="https://cdn.rawgit.com/openlayers/openlayers.github.io/master/en/v5.3.0/css/ol.css" type="text/css">
    <style>
      .map {
        height: 400px;
        width: 100%;
      }
	   .ol-popup {
        position: absolute;
        background-color: white;
        -webkit-filter: drop-shadow(0 1px 4px rgba(0,0,0,0.2));
        filter: drop-shadow(0 1px 4px rgba(0,0,0,0.2));
        padding: 15px;
        border-radius: 10px;
        border: 1px solid #cccccc;
        bottom: 12px;
        left: -50px;
        min-width: 280px;
      }
      .ol-popup:after, .ol-popup:before {
        top: 100%;
        border: solid transparent;
        content: " ";
        height: 0;
        width: 0;
        position: absolute;
        pointer-events: none;
      }
      .ol-popup:after {
        border-top-color: white;
        border-width: 10px;
        left: 48px;
        margin-left: -10px;
      }
      .ol-popup:before {
        border-top-color: #cccccc;
        border-width: 11px;
        left: 48px;
        margin-left: -11px;
      }
      .ol-popup-closer {
        text-decoration: none;
        position: absolute;
        top: 2px;
        right: 8px;
      }
      .ol-popup-closer:after {
       
      }
    </style>
    <script src="https://cdn.rawgit.com/openlayers/openlayers.github.io/master/en/v5.3.0/build/ol.js"></script>
    <title>Casa Bozi Maps</title>
  </head>
<body>


	<form method="GET" action="homegrabber_map.php" > 
		FILTERMODE:<input type="text" name="filtermode" id="filtermode" value="<?php echo @$_GET['filtermode']; ?>" />
		AGENZIA:<input type="text" name="nome_agenzia" id="nome_agenzia" value="<?php echo @$_GET['nome_agenzia']; ?>" />
		SITO:<input type="text" name="ag_domain" id="ag_domain" value="<?php echo @$_GET['ag_domain']; ?>" />
		MIN PRICE:<input type="text" name="minprice" id="minprice" value="<?php echo @$_GET['minprice']; ?>" />
		MAX PRICE:<input type="text" name="maxprice" id="maxprice" value="<?php echo @$_GET['maxprice']; ?>" />
		MIN DIM:<input type="text" name="mindim" id="mindim" value="<?php echo @$_GET['mindim']; ?>" />
		MAX DIM:<input type="text" name="maxdim" id="maxdim" value="<?php echo @$_GET['maxdim']; ?>" />
		MAX EUMQ:<input type="text" name="emq" id="emq" value="<?php echo @$_GET['emq']; ?>" />
		LOCALITA:<input type="text" name="loc" id="loc" value="<?php echo @$_GET['loc']; ?>" />
		DESC1:<input type="text" name="txt" id="txt" value="<?php echo @$_GET['txt']; ?>" />
		DESC2:<input type="text" name="txt2" id="txt2" value="<?php echo @$_GET['txt2']; ?>" />
		<input type="submit" name="search" value="Search"/> 
		&nbsp;<a href="<?php echo $list_url; ?>" >Torna a lista</a>
	</form>
	
	
	
    <div style="width:80%; height:80%; position:fixed; border: 1px solid;" id="map"></div>
    <div id="popup" class="ol-popup">
      <a href="#" id="popup-closer" class="ol-popup-closer">X</a>
      <div id="popup-content"></div>
    </div>
<script>

    var lat=46.49;
    var lon=11.35;
    var zoom=13; 
	   
	var select;
	
	var typeCache = {};
  
	  function flickrStyle(feature) {
		  var featureProps= feature.getProperties();
		   
		  var coord = feature.getGeometry().getCoordinates().toString();
		  var offset=0;
		  if (!typeCache[coord]) {
			  typeCache[coord] = 0;
		  }else{
			  offset =typeCache[coord]+0.1;
		  }
		   typeCache[coord]=offset; 
		  
		  console.log("feature",featureProps.name,coord,offset)
		  
		  var style = new ol.style.Style({
				  image: new ol.style.Icon(/** @type {module:ol/style/Icon~Options} */ ({
				  anchor: [0.5, 0.5 + offset], 
				  scale: 0.5,
				  src: 'http://maps.google.com/mapfiles/kml/pushpin/ylw-pushpin.png'
				})),
			   text: new ol.style.Text({
				  font: '9px Arial',  
				  text: featureProps.name,
				  fill: new ol.style.Fill({
					color: 'green'
				  })
			})	
		  });
		   
	  
		  return [style];
    }

		 
	var vectorLayer =	 new ol.layer.Vector({
		source: new ol.source.Vector({
		  url: '<?php echo $kml_url; ?>',
		  format: new ol.format.KML({
							extractStyles: true,
							extractAttributes: true
						})
			}), 
          style:flickrStyle		
	  });
 
      var content_popup = document.getElementById('popup-content');
      var closer_popup = document.getElementById('popup-closer');
     var popupoverlay = new ol.Overlay({
        element: document.getElementById('popup'),
        autoPan: true,
        autoPanAnimation: {
          duration: 250
        }
      });
	 closer_popup.onclick = function() {
        popupoverlay.setPosition(undefined);
        closer_popup.blur();
        return false;
      };

    var view = new ol.View({
        center: ol.proj.transform([lon,lat], 'EPSG:4326','EPSG:3857'),
        zoom: zoom
    });

    var map = new ol.Map({
        layers: [
            new ol.layer.Tile({
                source: new ol.source.OSM()
            })
			
            ,vectorLayer
        ],
		overlays: [popupoverlay],
        target: 'map',
        controls: ol.control.defaults({
            attributionOptions: /** @type {olx.control.AttributionOptions} */ ({
                collapsible: false
            })
        }),
        view: view
    });
		
	var select = null;  
	var value = 'click';
	 
	var selectSingleClick = new ol.interaction.Select(); 
	var selectClick = new ol.interaction.Select({
	  condition: ol.events.condition.click
	}); 
	var selectPointerMove = new ol.interaction.Select({
	  condition: ol.events.condition.pointerMove
	}); 
	var selectAltClick = new ol.interaction.Select({
	  condition: function(mapBrowserEvent) {
		return ol.events.condition.click(mapBrowserEvent) &&
			ol.events.condition.altKeyOnly(mapBrowserEvent);
	  }
	});
	 
	  

	if (value == 'singleclick') {
		select = selectSingleClick;
	} else if (value == 'click') {
		select = selectClick;
	} else if (value == 'pointermove') {
		select = selectPointerMove;
	} else if (value == 'altclick') {
		select = selectAltClick;
	} else {
		select = null;
	}
	if (select !== null) {
		map.addInteraction(select);
		select.on('select', function(e) {
			//console.log("Clicked feature",e.target.getFeatures(),e.selected[0].getProperties());
			var featureProps= e.selected[0].getProperties();
			var feature_html = '<h4>'+featureProps.name + '</h4>'+ featureProps.description ; 
			
			//console.log('TEST::: ' + e.target.getFeatures().getLength() +   ' selected features (last operation selected ' + e.selected.length +  ' and deselected ' + e.deselected.length + ' features) content='+feature_html, featureProps.flatCoordinates,e.selected[0].coordinate);
			
			var coords = [featureProps.geometry.flatCoordinates[0], featureProps.geometry.flatCoordinates[1]];
	  
			content_popup.innerHTML = feature_html;
			popupoverlay.setPosition(coords);
		});
	} 
	 


</script>

</body>
</html>