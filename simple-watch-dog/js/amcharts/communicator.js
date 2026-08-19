// communicator

window.onerror = function(e) { console.log("Error: ", e); };

var maxChart = 0;

function dataInfo(ID, args, fn) {
    return;
    var d = document.getElementById("test"+maxChart);
    if (d) {
	d.innerHTML = fn + ": " +ID+"/"+Object.keys(args[fn]).length + " -- " + JSON.stringify(args[fn][ID]['data']);
	maxChart++;
    }
}
