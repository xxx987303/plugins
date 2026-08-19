// ChartCC

window.onerror = function(e) { console.log("Error: ", e); };
am5.ready(function() {});
ChartCC(1);
ChartCC(2);
ChartCC(3);

function ChartCC(ID) {
    var fn = 'ChartCC';
    var chartdiv = "chartdiv_" + fn + ID;
    if (document.getElementById(chartdiv)) {
	var root = am5.Root.new(chartdiv);
	root.setThemes([ am5themes_Animated.new(root) ]);

	if (true) {
	    var data = args[fn][ID]['data'];
	} else {
            var data = [ { name: "Switzerland",                     
			   countCC: 45688,
			   flag: { src: "/restor/wp-content/themes/twentytwentyfour-child/flags/switzerland.png" }},
			 { name: "Finland",         
			   countCC: 35781,
			   flag: { src: "/restor/wp-content/themes/twentytwentyfour-child/flags/finland.png" }},
			 { name: "Sweden",                   
			   countCC: 25464,
			   flag: { src: "/restor/wp-content/themes/twentytwentyfour-child/flags/sweden.png" }},
			 { name: "Nepal",
			   countCC: 18788,
			   flag: { src: "/restor/wp-content/themes/twentytwentyfour-child/flags/nepal.png" }},
			 { name: "USA",
			   countCC: 15465,
			   flag: { src: "/restor/wp-content/themes/twentytwentyfour-child/flags/usa.png" }},
			 { name: "Italy",
			   countCC: 11561,
			   flag: { src: "/restor/wp-content/themes/twentytwentyfour-child/flags/italy.png" }} ];
	}
	//document.getElementById("test"+(ID+2)).innerHTML = fn + ": " +ID+"/"+Object.keys(args[fn]).length + " -- " + JSON.stringify(data);
	dataInfo(ID, args, fn);
	
	var chart = root.container.children.push(
	    am5xy.XYChart.new(root, {
		panX: false,
		panY: false,
		paddingLeft:0,
		paddingRight:30,
		wheelX: "none",
		wheelY: "none"
	    })
	);
	
	// Create axes
	// https://www.amcharts.com/docs/v5/charts/xy-chart/axes/
	
	var yRenderer = am5xy.AxisRendererY.new(root, {
	    minorGridEnabled:true
	});
	yRenderer.grid.template.set("visible", false);
	
	var yAxis = chart.yAxes.push(
	    am5xy.CategoryAxis.new(root, {
		categoryField: "name",
		renderer: yRenderer,
		paddingRight:40
	    })
	);
	
	var xRenderer = am5xy.AxisRendererX.new(root, {
	    minGridDistance:80,
	    minorGridEnabled:true
	});
	
	var xAxis = chart.xAxes.push(
	    am5xy.ValueAxis.new(root, {
		min: 0,
		renderer: xRenderer
	    })
	);
	
	// Add series
	// https://www.amcharts.com/docs/v5/charts/xy-chart/series/
	var series = chart.series.push(
	    am5xy.ColumnSeries.new(root, {
		name: "Income",
		xAxis: xAxis,
		yAxis: yAxis,
		valueXField: "countCC",
		categoryYField: "name",
		sequencedInterpolation: true,
		calculateAggregates: true,
		maskBullets: false,
		tooltip: am5.Tooltip.new(root, {
		    dy: -30,
		    pointerOrientation: "vertical",
		    labelText: "{valueX}"
		})
	    })
	);

	series.columns.template.setAll({
	    strokeOpacity: 0,
	    cornerRadiusBR: 10,
	    cornerRadiusTR: 10,
	    cornerRadiusBL: 10,
	    cornerRadiusTL: 10,
	    maxHeight: 50,
	    fillOpacity: 0.8
	});
	
	var currentlyHovered;
	
	series.columns.template.events.on("pointerover", function(e) {
	    handleHover(e.target.dataItem);
	});
	
	series.columns.template.events.on("pointerout", function(e) {
	    handleOut();
	});
	
	function handleHover(dataItem) {
	    if (dataItem && currentlyHovered != dataItem) {
		handleOut();
		currentlyHovered = dataItem;
		var bullet = dataItem.bullets[0];
		bullet.animate({
		    key: "locationX",
		    to: 1,
		    duration: 600,
		    easing: am5.ease.out(am5.ease.cubic)
		});
	    }
	}
	
	function handleOut() {
	    if (currentlyHovered) {
		var bullet = currentlyHovered.bullets[0];
		bullet.animate({
		    key: "locationX",
		    to: 0,
		    duration: 600,
		    easing: am5.ease.out(am5.ease.cubic)
		});
	    }
	}
	
	
	var circleTemplate = am5.Template.new({});
	//document.getElementById('test7').innerHTML="circleTemplate";
	
	series.bullets.push(function(root, series, dataItem) {
	    var bulletContainer = am5.Container.new(root, {});
	    var circle = bulletContainer.children.push(am5.Circle.new(root,
								      { radius: 34 },
								      circleTemplate));
	    var maskCircle = bulletContainer.children.push(am5.Circle.new(root, { radius: 27 }));
	    
	    // only containers can be masked, so we add image to another container
	    var imageContainer = bulletContainer.children.push(am5.Container.new(root,
										 { mask: maskCircle }));
	    // not working
	    var image = imageContainer.children.push(
		am5.Picture.new(root, {
		    templateField: "flag",
		    centerX: am5.p50,
		    centerY: am5.p50,
		    width: 60,
		    height: 60
		})
	    );
	    
	    return am5.Bullet.new(root, {
		locationX: 0,
		sprite: bulletContainer
	    });
	});
	
	// heatrule
	series.set("heatRules", [{ dataField: "valueX",
	    			   min: am5.color(0xe5dc36),
				   max: am5.color(0x5faa46),
				   target: series.columns.template,
				   key: "fill" },
				 { dataField: "valueX",
				   min: am5.color(0xe5dc36),
				   max: am5.color(0x5faa46),
				   target: circleTemplate,
				   key: "fill" }]);
	series.data.setAll(data);
	yAxis.data.setAll(data);
	
	var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {}));
	cursor.lineX.set("visible", false);
	cursor.lineY.set("visible", false);
	cursor.events.on("cursormoved", function() {
	    var dataItem = series.get("tooltip").dataItem;
	    if (dataItem) { handleHover(dataItem) }
	    else          { handleOut(); }});

	series.appear();
	chart.appear(1000, 100);
    } else {
	console.log(chartdiv + " not found");                                                                                                   
    }
}
