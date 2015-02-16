// JavaScript Document
var XMLHttpRequestObject = false;
var XMLHttpRequestObject2 = false;
var XMLHttpRequestObject3 = false;
var XMLHttpRequestObject4 = false;

if(window.XMLHttpRequest) {
	XMLHttpRequestObject = new XMLHttpRequest();
	XMLHttpRequestObject2 = new XMLHttpRequest();
	XMLHttpRequestObject3 = new XMLHttpRequest();	
	XMLHttpRequestObject4 = new XMLHttpRequest();	
}
else if (window.ActiveXObject) {
	XMLHttpRequestObject = new ActiveXObject("Microsoft.XMLHTTP");
	XMLHttpRequestObject2 = new ActiveXObject("Microsoft.XMLHTTP");
	XMLHttpRequestObject3 = new ActiveXObject("Microsoft.XMLHTTP");
	XMLHttpRequestObject4 = new ActiveXObject("Microsoft.XMLHTTP");
}

function getData_no_div(dataSource) {
	dataSource = dataSource.replace('+', '%2B');
		
	if(XMLHttpRequestObject) {
	//	var obj = document.getElementById(divID);
		XMLHttpRequestObject.open("GET", dataSource);
		
/*		XMLHttpRequestObject.onreadystatechange = function()
		{
			if(XMLHttpRequestObject.readyState == 4 && XMLHttpRequestObject.status == 200) {
				obj.innerHTML = XMLHttpRequestObject.responseText;		// <-- difference
			}
		}
	*/	
		XMLHttpRequestObject.send(null);
	}	
}

function getData(dataSource, divID) {
	//dataSource is a URL that will get data by GET and print an output
	//divID is the name of an element that will have its innerHTML udpated by this function
	//getData('calendar.php?this=that', 'some_div');
		
	// substitute + symbols by %2B so that they are passed by $_GET
	dataSource = dataSource.replace('+', '%2B');
		
	if(XMLHttpRequestObject) {
		var obj = document.getElementById(divID);
		XMLHttpRequestObject.open("GET", dataSource);
		
		XMLHttpRequestObject.onreadystatechange = function()
		{
			if(XMLHttpRequestObject.readyState == 4 && XMLHttpRequestObject.status == 200) {
				obj.innerHTML = XMLHttpRequestObject.responseText;		// <-- difference
			}
		}
		
		XMLHttpRequestObject.send(null);
	}
}

function getData_value(dataSource, divID) {
	//dataSource is a URL that will get data by GET and print an output
	//divID is the name of an element that will have its VALUE udpated by this function
	//getData('calendar.php?this=that', 'some_div');
		
	// substitute + symbols by %2B so that they are passed by $_GET
	dataSource = dataSource.replace('+', '%2B');
		
	if(XMLHttpRequestObject) {
		var obj = document.getElementById(divID);
		XMLHttpRequestObject.open("GET", dataSource);
		
		XMLHttpRequestObject.onreadystatechange = function()
		{
			if(XMLHttpRequestObject.readyState == 4 && XMLHttpRequestObject.status == 200) {
				obj.value = XMLHttpRequestObject.responseText;		// <-- difference
			}
		}
		
		XMLHttpRequestObject.send(null);
	}
}

// ----- used for paralelism ------
function getData2(dataSource, divID) {
	// substitute + symbols by %2B so that they are passed by $_GET
	dataSource = dataSource.replace('+', '%2B');

	if(XMLHttpRequestObject2) {
		var obj = document.getElementById(divID);
		XMLHttpRequestObject2.open("GET", dataSource);
		
		XMLHttpRequestObject2.onreadystatechange = function()
		{
			if(XMLHttpRequestObject2.readyState == 4 && XMLHttpRequestObject2.status == 200) {
				obj.innerHTML = XMLHttpRequestObject2.responseText;
			}
		}
		
		XMLHttpRequestObject2.send(null);
	}
}

// ----- used for paralelism ------
function getData3(dataSource, divID) {
	dataSource = dataSource.replace('+', '%2B');

	if(XMLHttpRequestObject3) {
		var obj = document.getElementById(divID);
		XMLHttpRequestObject3.open("GET", dataSource);
		
		XMLHttpRequestObject3.onreadystatechange = function()
		{
			if(XMLHttpRequestObject3.readyState == 4 && XMLHttpRequestObject3.status == 200) {
				obj.innerHTML = XMLHttpRequestObject3.responseText;
			}
		}
		
		XMLHttpRequestObject3.send(null);
	}
}


function getData_param(dataSource, divID, out_func) {
	dataSource = dataSource.replace('+', '%2B');
		
	if(XMLHttpRequestObject) {
		var obj = document.getElementById(divID);
		XMLHttpRequestObject.open("GET", dataSource);
		
		XMLHttpRequestObject.onreadystatechange = function()
		{
			if(XMLHttpRequestObject.readyState == 4 && XMLHttpRequestObject.status == 200) {
				obj.innerHTML = XMLHttpRequestObject.responseText;		// <-- difference
				eval(out_func);											// <-- difference
			}
		}
		
		XMLHttpRequestObject.send(null);
	}
}

function getData_JS(dataSource) {	// executes a piece of JS code returned by PHP
	dataSource = dataSource.replace('+', '%2B');
		
	if(XMLHttpRequestObject4) {		// use object4 to avoid collisions with other "onBlur" calls to AJAX
		//var obj = document.getElementById(divID);
		XMLHttpRequestObject4.open("GET", dataSource);
		
		XMLHttpRequestObject4.onreadystatechange = function()
		{
			if(XMLHttpRequestObject4.readyState == 4 && XMLHttpRequestObject4.status == 200) {
				eval(XMLHttpRequestObject4.responseText);				// <-- difference
			}
		}
		
		XMLHttpRequestObject4.send(null);
	}
}

/*

function getDataPOST(dataSource, divID, params, out_func) {
	if(XMLHttpRequestObject) {
		var obj = document.getElementById(divID);
		XMLHttpRequestObject.open("POST", dataSource);
		XMLHttpRequestObject.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
//		XMLHttpRequestObject.setRequestHeader('Content-Type', 'charset=iso-8859-1');
				 
		XMLHttpRequestObject.onreadystatechange = function()
		{
			if(XMLHttpRequestObject.readyState == 4 && XMLHttpRequestObject.status == 200) {
				obj.innerHTML = XMLHttpRequestObject.responseText;
				eval(out_func +'();');
			}
		}
		
		XMLHttpRequestObject.send(params);
	}
}*/