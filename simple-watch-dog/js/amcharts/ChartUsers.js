
am5.ready(function() {});
ChartUsers(1);
ChartUsers(2);
ChartUsers(3);

function ChartUsers(ID) {
    var fn = 'ChartUsers';
    var chartdiv = "chartdiv_" + fn + ID;
    if (document.getElementById(chartdiv)) {
	var root = am5.Root.new(chartdiv);
	
 	root.setThemes([am5themes_Animated.new(root)]);
	
	var chart = root.container.children.push(am5xy.XYChart.new(root, {panX: false,
									  panY: false,
									  wheelX: "none",
									  wheelY: "none",
									  paddingLeft: 0}));
	var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {}));
	cursor.lineY.set("visible", false);
	
	var xRenderer = am5xy.AxisRendererX.new(root, { minGridDistance: 30,	
							minorGridEnabled: true});
	var xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, { maxDeviation: 0,
								    categoryField: "name",
								    renderer: xRenderer,
								    tooltip: am5.Tooltip.new(root,{})}));
	xRenderer.grid.template.set("visible", false);
	
	var yRenderer = am5xy.AxisRendererY.new(root, {});
	var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, { maxDeviation: 0,
								 min: 0,
								 extraMax: 0.1,
								 renderer: yRenderer}));    
	yRenderer.grid.template.setAll({ strokeDasharray: [2, 2]});
        
	// Create series <HEAD>/docs/v5/charts/xy-chart/series/
	var series = chart.series.push(am5xy.ColumnSeries.new(root, { name: "Series 1",
								      xAxis: xAxis,
								      yAxis: yAxis,
								      valueYField: "value",
								      sequencedInterpolation: true,
								      categoryXField: "name",
								      tooltip: am5.Tooltip.new(root, { dy: -25, labelText: "{valueY}" })}));
	series.columns.template.setAll({ cornerRadiusTL: 5,
					 cornerRadiusTR: 5,
					 strokeOpacity: 0 });				   
	
	series.columns.template.adapters.add("fill", (fill, target) => {
	    return chart.get("colors").getIndex(series.columns.indexOf(target));
	});
	
	series.columns.template.adapters.add("stroke", (stroke, target) => {
	    return chart.get("colors").getIndex(series.columns.indexOf(target));
	});
	
	var data = args[fn][ID]['data'];
	//document.getElementById("test"+(ID-1)).innerHTML = fn + ": " +ID + "/" + Object.keys(args[fn]).length + " -- " + JSON.stringify(data);
	dataInfo(ID, args, fn);
	if (data) {
	    series.bullets.push(function() {
		return am5.Bullet.new(root, { locationY: 1,	    
					      sprite: am5.Picture.new(root, {
						  templateField: "photo",
						  width: 50,
						  height: 50,
						  centerX: am5.p50,
						  centerY: am5.p50,
						  shadowColor: am5.color(0x000000),
						  shadowBlur: 4,
						  shadowOffsetX: 4,
						  shadowOffsetY: 4,
						  shadowOpacity: 0.6})});});
	    xAxis.data.setAll(data);
	    series.data.setAll(data);
	    
	    // Make stuff animate on load <HEAD>/docs/v5/concepts/animations/
	    series.appear(1000);
	    chart.appear(1000, 100);
	} else {
	    console.log("No data found for "+fn);
	}
    } else {
	console.log(chartdiv + " not found");
    }
}
