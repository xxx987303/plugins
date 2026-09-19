// ChartTimes

var fontsize = 12;
am5.ready(function(){});
ChartTimes(1);

function ChartTimes(ID) {
    var fn = "ChartTimes";
    var chartdiv = "chartdiv_" + fn + ID;
    if (document.getElementById(chartdiv)) {
	var root = am5.Root.new(chartdiv);
	root.setThemes([ am5themes_Animated.new(root) ]);

	var chart = root.container.children.push(am5xy.XYChart.new(root, { panX: true,
 									   panY: true,
 									   wheelX: "panX",
 									   wheelY: "zoomX",
 									   pinchZoomX: true,
 									   paddingLeft:0,
 									   paddingRight:1 }));
	var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {}));
	cursor.lineY.set("visible", false);

	// X
	var xRenderer = am5xy.AxisRendererX.new(root, {
	    minGridDistance: 30,
	    minorGridEnabled: true
	});
	xRenderer.labels.template.setAll({
	    rotation: -90,
	    centerY: am5.p50,
	    centerX: am5.p100,
	    paddingRight: 15
	});
	xRenderer.grid.template.setAll({
	    location: 1
	})
	var xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
	    maxDeviation: 0.3,
	    categoryField: "time",
	    renderer: xRenderer,
	    tooltip: am5.Tooltip.new(root, {})
	}));

	// Y
	var yRenderer = am5xy.AxisRendererY.new(root, {
	    strokeOpacity: 0.1
	})
	var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
	    maxDeviation: 0.3,
	    renderer: yRenderer
	}));
	
	var series = chart.series.push(am5xy.ColumnSeries.new(root, {
	    name: "Series 1",
	    xAxis: xAxis,
	    yAxis: yAxis,
	    valueYField: "value",
	    sequencedInterpolation: true,
	    categoryXField: "time",
	    tooltip: am5.Tooltip.new(root, { labelText: "{valueY}" })
	}));
	series.columns.template.setAll({cornerRadiusTL: 5, cornerRadiusTR: 5, strokeOpacity: 0});
	series.columns.template.adapters.add("fill",   function (fill,   target) { return chart.get("colors").getIndex(series.columns.indexOf(target));});
	series.columns.template.adapters.add("stroke", function (stroke, target) { return chart.get("colors").getIndex(series.columns.indexOf(target));});

	// Set the font size
	xAxis.get("renderer").labels.template.setAll({ fontSize: fontsize });
	yAxis.get("renderer").labels.template.setAll({ fontSize: fontsize });
	series.bullets.push(function(root) {
	    return am5.Bullet.new(root, {
		sprite: am5.Label.new(root, {
		    text: "{valueY}",
		    centerX: am5.p50,
		    centerY: am5.p100,
		    populateText: true,
		    fontSize: fontsize
		})
	    });
	});
	//legend.labels.template.setAll({ fontSize: fontsize });
	
	// Set data
	if (false) {
	    var data = [{'time':'00:00', 'value':1},
			{'time':'02:00', 'value':2},
			{'time':'04:00', 'value':3},
			{'time':'06:00', 'value':5},
			{'time':'08:00', 'value':10},
			{'time':'10:00', 'value':16},
			{'time':'12:00', 'value':20},
			{'time':'14:00', 'value':25},
			{'time':'16:00', 'value':10},
			{'time':'18:00', 'value':9},
			{'time':'20:00', 'value':8},
			{'time':'22:00', 'value':6},
			{'time':'24:00', 'value':2}];
	} else {
	    var data = args[fn][ID]['data'];
	}
	args[fn][ID]['data'] = data;
	dataInfo(ID, args, fn);
	
	xAxis.data.setAll(data);
	series.data.setAll(data);
	series.appear(1000);
	chart.appear(1000, 100);
    } else {
	console.log(chartdiv + " not found");
    }
}
