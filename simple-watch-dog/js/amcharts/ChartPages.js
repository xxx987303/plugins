// ChartPages

am5.ready(function(){});
ChartPages(1);
ChartPages(2);
ChartPages(3);

function ChartPages(ID) {
    var fn = "ChartPages";
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
	    categoryField: "page",
	    renderer: xRenderer,
	    tooltip: am5.Tooltip.new(root, {})
	}));
	
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
	    categoryXField: "page",
	    tooltip: am5.Tooltip.new(root, {
		labelText: "{valueY}"
	    })
	}));
	series.columns.template.setAll({
	    cornerRadiusTL: 5, cornerRadiusTR: 5, strokeOpacity: 0
	});
	series.columns.template.adapters.add("fill", function (fill, target) {
	    return chart.get("colors").getIndex(series.columns.indexOf(target));
	});
	series.columns.template.adapters.add("stroke", function (stroke, target) {
	    return chart.get("colors").getIndex(series.columns.indexOf(target));
	});
	
	
	// Set data
	if (false) {
	    var data = [{"page": "USA",
			 "value": 2025},
			{"page": "Page very long name, well... Boring",
			 "value": 1882},
			{"page": "Ну очень длинное имя, прямо тоска",
			 "value": 1809},
			{"page": "Germany",
			 "value": 1322},
			{"page": "UK",
			 "value": 1122},
			{"page": "France",
			 "value": 1114}];
	    args[fn][ID]['data'] = data;
	} else {
	    var data = args[fn][ID]['data'];
	}
	dataInfo(ID, args, fn);
	
	xAxis.data.setAll(data);
	series.data.setAll(data);
	series.appear(1000);
	chart.appear(1000, 100);
    } else {
	console.log(chartdiv + " not found");
    }
}
