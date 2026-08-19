// ChartOS

am5.ready(function(){});
ChartOS(1);
ChartOS(2);
ChartOS(3);

function ChartOS(ID) {
    var fn = 'ChartOS';
    if (document.getElementById("chartdiv_"+fn+ID)) {
	var root = am5.Root.new("chartdiv_"+fn+ID);

	root.setThemes([ am5themes_Animated.new(root) ]);

	var chart = root.container.children.push(am5percent.PieChart.new(root, { endAngle: 270 }));

	var data = args[fn][ID]['data'];
	//document.getElementById("test3"+ID).innerHTML = ID + "--" +JSON.stringify(data);
	dataInfo(ID, args, fn);
	
	var data = args[fn][ID]['data'];
	var series = chart.series.push(am5percent.PieSeries.new(root, {valueField: "value",
	    							       categoryField: "os",
								       endAngle: 270}));
	series.states.create("hidden", { endAngle: -90 });
	series.data.setAll(data);
	series.appear(1000, 100);
    } else {
	console.log("chartdiv_"+fn+ID+" not found");
    }
}
