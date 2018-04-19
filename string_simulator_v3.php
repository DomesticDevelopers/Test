<?php

include 'data.php';

$data = null;
if (isset($_GET['complete'])) {
	$data = getData($conn, "SELECT * FROM data");
}

//				 ___________     ___    _________________________
//			    /___   ____/___ /  /   /        _    _   _  _ __
//			       /  / /  ___//  /   /        _    _   _  _ __ 
//			  	  /  / /  /   /  /___/        _    _   _  _ __ 
//			     /  / /  /   /  ____         _    _   _  _ __      ___  ____   _    ___
//			    /  / /  /___/  /   /        _    _   _  _ __      /    /    / / \  /__  
//			   /__/ /______/__/   /________________________      /___ /____/ /__/ /__

?>

<html>
<head>
	<title>Data strings simulator</title>
	
	<link rel="stylesheet" type="text/css" href="supporting_files/style.css">
	<script src="supporting_files/jquery-3.1.0.min.js"></script>
	<script src="supporting_files/p5.min.js"></script>

</head>

<body>
	<div class="header">
		<h1>Domestic Data Streamers - Data Strings Simulator</h1>
	</div>
	<div id="options_container">
		<form method="post" action="to_table.php" enctype="multipart/form-data">
			<div id="file_upload_wrapper">
				<div id="file_upload_wrapper_detail">
					<input type="file" name="data">
				</div>
				<button type="submit">U P L O A D &nbsp;&nbsp;&nbsp; M A I N &nbsp;&nbsp;&nbsp; D A T A S H E E T</button>
			</div>
		</form>	
		<div id="columns_menu">Columns menu</div>
	</div>

	<div id="canvas-background-image">
		<script type='text/javascript'>
			var data = <?php echo json_encode($data); ?>;
			var points_data = {};
			var string_data = [];

			function setup(){
				createCanvas(window.innerWidth,window.innerHeight-38);
				noSmooth();

				if (data != null) {
					cw = window.innerWidth / (Object.keys(data[0]).length-2);
					for (var i = 2; i < Object.keys(data[0]).length; i++) {
						var current_key = Object.keys(data[0])[i];
						points_data[current_key] = {};
						points_data[current_key]["col_name"] = current_key;
						points_data[current_key]["col_width"] = cw;
						points_data[current_key]["col_x_pos"] = cw * (i-2);
						points_data[current_key]["point_type"] = "discrete";

						var flags = [], output = [], k=0;
						for( k=1; k<data.length; k++) {
							if( flags[data[k][current_key]]) continue;
							flags[data[k][current_key]] = true;
							output.push({
								label: data[k][current_key],
								option: data[k][current_key]
							});
						}
						points_data[current_key]["points"] = output;
						add_coords_to_object(generate_coords(points_data[current_key]["points"], points_data[current_key]["col_x_pos"], points_data[current_key]["col_width"]), points_data[current_key]["points"]);
						options(points_data[current_key]["col_x_pos"], points_data[current_key]["col_width"], points_data[current_key]["col_name"]);
					}
					string_data = string_data_struct(data, points_data);
				}
				window.addEventListener( 'resize', onWindowResize, false );
			}


			function generate_coords(data_length, x, width){
				var coords_array = [];
				var x = x + width/2;
				var spacing = 75;
				var y_space = (height - (spacing*2)) / data_length.length;
				for (var j = 0; j < data_length.length; j++) {
					var y_pos = y_space * (j + 0.6) + spacing;
					coords_array.push(createVector(x, y_pos));
				}
				return coords_array;
			}

			function add_coords_to_object(coords, data_object){
				for (var i = 0; i < data_object.length; i++) {
					data_object[i]["coord"] = coords[i];
				}
			}

			function string_data_struct(data, points_data){
				var temp_data_sruct = [];
				for (var i = 1; i < data.length; i++) {
					var str_obj = {
						color: data[i].color,
						coord_array: []
					};
					for (var j = 0; j < Object.keys(points_data).length; j++) {
						str_obj.coord_array.push(generate_string_coords(points_data[Object.keys(points_data)[j]].point_type, points_data[Object.keys(points_data)[j]].points, data[i], Object.keys(points_data)[j]));
					}
					temp_data_sruct.push(str_obj);
				}
				return temp_data_sruct;
			}

			function generate_string_coords(type, locs, data, current_key){
				var temp = createVector(0, random(-4,4));
				if (type == "discrete") {
					for (var i = 0; i < locs.length; i++) {
						if (locs[i].option == data[current_key]) {
							temp.x += locs[i].coord.x;
							temp.y += locs[i].coord.y;
						}
					}
				}else{
					for (var i = 0; i < locs.length; i++) {
						if (parseInt(data[current_key]) >= parseInt(locs[i].min) && parseInt(data[current_key]) <= parseInt(locs[i].max)) {
							temp.x += locs[i].coord.x;
							temp.y += locs[i].coord.y;
						}
					}
				}
				return temp;
				
			}

			function drawStrings(data){
				beginShape();
				stroke(data.color);
				noFill();
				for (var i = 0; i < data.coord_array.length; i++) {
					vertex(data.coord_array[i].x, data.coord_array[i].y)
				}
				endShape();

			}

			function draw(){
				background(255);
				stroke(0);
				cols = Object.keys(points_data);
				for (var i = 0; i < cols.length; i++) {
					draw_column(points_data[cols[i]].col_x_pos, points_data[cols[i]].col_width, points_data[cols[i]].col_name, points_data[cols[i]].points);
				}
				for (var i = 0; i < string_data.length; i++) {
					drawStrings(string_data[i]);
				}
			}

			function draw_column(x, width, name, points){
				fill(0);
				stroke(0);
				rect(x, 0, width, height);
				fill(255);
				text(name, x + width/2 - textWidth(name)/2, 20);
				for (var i = 0; i < points.length; i++) {
					stroke(0);
					ellipse(points[i].coord.x, points[i].coord.y, 10,10);
					fill(0);
					noStroke();
					text(points[i].label, points[i].coord.x - textWidth(points[i].label)/2, points[i].coord.y - 10);
				}
			}

			function options(x, width, type){
				$('#options_container').append('<div id="'+type+'" class="option_container" style="width: '+width+'px; left: '+x+'px;"></div>')
				$('#'+type).append('<select id="option_'+type+'" name="column_options" onchange="selected(option_'+type+')"><option value="discrete">Discrete</option><option value="columns">Columns</option></select>')
			}

			function selected(type){
				var selected_option = type.options[type.selectedIndex].value;
				var id = type.id.slice(7);
				if (selected_option == "columns") {
					points_data[id]["point_type"] = "columns";
					$('#'+id).append('<input type="file" id="option_file_'+id+'" name="file_'+id+'">');
					$('#'+id).append('<button id="option_upload_'+id+'" type="activate_file" onclick="create_form_data(option_file_'+id+')">upload options file</button>');
				}else{
					points_data[id]["point_type"] = "discrete";
					$( '#option_file_'+id ).remove();
					$( '#option_upload_'+id ).remove();
				}
			}

			function create_form_data(option_file_name){
				var file = $('#'+option_file_name.id).prop('files')[0];
				var form_data = new FormData(); 
				form_data.append('file', file);
				form_data.append('tblname', option_file_name.id.slice(12));
				send_ajax_request(form_data);

			}

			function send_ajax_request(pdata){
				$.ajax({
					url: "set_points_data.php",
					data: pdata,
					contentType: false,
					processData: false,
					dataType: "json",
					type: 'post',
					success: function (r_data) {
						var current_col = r_data[r_data.length-1];
						points_data[current_col]["points"] = [];
						for (var i = 1; i < r_data[0].length; i++) {
							points_data[current_col]["points"].push(r_data[0][i]);
						}
						add_coords_to_object(generate_coords(points_data[current_col]["points"], points_data[current_col]["col_x_pos"], points_data[current_col]["col_width"]), points_data[current_col]["points"]);
						string_data = string_data_struct(data, points_data);
					},
					error: function(a, b, c){
						console.log(a, b, c);
					}
				});
			}

			function onWindowResize() {
				if(data != null){
					resizeCanvas(window.innerWidth,window.innerHeight-38);
					cw = window.innerWidth / (Object.keys(data[0]).length-2);
					for (var i = 2; i < Object.keys(data[0]).length; i++) {
						var current_key = Object.keys(data[0])[i];
						points_data[current_key].col_width = cw;
						points_data[current_key].col_x_pos = cw * (i-2);
						
						add_coords_to_object(generate_coords(points_data[current_key]["points"], points_data[current_key]["col_x_pos"], points_data[current_key]["col_width"]), points_data[current_key]["points"]);
						string_data = string_data_struct(data, points_data);
					}
				}
			}




		</script>
	</div>
</body>

</html>