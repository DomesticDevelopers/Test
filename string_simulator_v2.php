<?php

include 'data.php';

$data = null;
if (isset($_GET['fileup']) && $_GET['fileup'] === "true") {
	$data = getData($conn, "SELECT * FROM data");
}

?>

<html>
<head>
	<title>Data strings simulator</title>
	
	<link rel="stylesheet" type="text/css" href="supporting_files/style.css">
	<script src="supporting_files/jquery-3.1.0.min.js"></script>
	<script src="supporting_files/p5.min.js"></script>

</head>

<body>
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
			var string_data = {};

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
					generate_strings_datastruct(data, points_data);
				}
				window.addEventListener( 'resize', onWindowResize, false );
			}

			function onWindowResize() {
				resizeCanvas(window.innerWidth,window.innerHeight-38);
				cw = window.innerWidth / (Object.keys(data[0]).length-2);
				for (var i = 2; i < Object.keys(data[0]).length; i++) {
					var current_key = Object.keys(data[0])[i];
					points_data[current_key].col_width = cw;
					points_data[current_key].col_x_pos = cw * (i-2);
					
					add_coords_to_object(generate_coords(points_data[current_key]["points"], points_data[current_key]["col_x_pos"], points_data[current_key]["col_width"]), points_data[current_key]["points"]);
				}
				generate_strings_datastruct(data, points_data);
				for (var i = 0; i < Object.keys(string_data).length; i++) {
					draw_strings(string_data["strings"+i]);
				}

			}

			function generate_coords(data_length, x, width){
				var coords_array = [];
				var x = x + width/2;
				var y_space = height / data_length.length;
				for (var j = 0; j < data_length.length; j++) {
					var y_pos = y_space * (j + 0.5);
					coords_array.push(createVector(x, y_pos));
				}
				return coords_array;
			}

			function add_coords_to_object(coords, data_object){
				for (var i = 0; i < data_object.length; i++) {
					data_object[i]["coord"] = coords[i];
				}
			}

			function draw(){
				background(255);
				for (var i = 0; i < Object.keys(string_data).length; i++) {
					draw_strings(string_data["strings"+i]);
				}
				stroke(0);
				cols = Object.keys(points_data);
				for (var i = 0; i < cols.length; i++) {
					draw_column(points_data[cols[i]].col_x_pos, points_data[cols[i]].col_width, points_data[cols[i]].col_name, points_data[cols[i]].points);
				}
			}

			function draw_column(x, width, name, points){
				noFill();
				stroke(0);
				rect(x, 0, width, height);
				fill(0);
				text(name, x + width/2, 20);
				for (var i = 0; i < points.length; i++) {
					fill(255);
					stroke(0);
					ellipse(points[i].coord.x, points[i].coord.y, 10,10);
					fill(255,0,0);
					noStroke();
					text(points[i].label, points[i].coord.x + 10, points[i].coord.y + 5);
				}
			}

			function generate_strings_datastruct(data, points){
				var current_object = {};
				for (var i = 0; i < Object.keys(points_data).length-1; i++) {
					current_object["strings"+i] = {
						[Object.keys(points_data)[i]]: {
							comparison: points_data[Object.keys(points_data)[i]]["point_type"],
							locs: [],
							color: []
						},
						[Object.keys(points_data)[i+1]]: {
							comparison: points_data[Object.keys(points_data)[i+1]]["point_type"],
							locs: [],
							color: []
						}
					};	
				}
				for (var i = 0; i < Object.keys(current_object).length; i++) {
					for (var j = 0; j < 2; j++) {
						current_object["strings"+i][Object.keys(current_object["strings"+i])[j]].locs = get_data_coords(current_object["strings"+i][Object.keys(current_object["strings"+i])[j]].comparison, Object.keys(current_object["strings"+i])[j], data, points_data)[0];
						current_object["strings"+i][Object.keys(current_object["strings"+i])[j]].color = get_data_coords(current_object["strings"+i][Object.keys(current_object["strings"+i])[j]].comparison, Object.keys(current_object["strings"+i])[j], data, points_data)[1];
					}
				}
				string_data = current_object;
			}

			function get_data_coords(sort_type, name, data, points_data){
				var temp_array = [];
				var temp_color_array = [];
				var rand_off = 4;

				if (sort_type == "discrete") {
					for (var i = 0; i < points_data[name].points.length; i++) {
						for (var j = 0; j < data.length; j++) {
							if (points_data[name].points[i].option == data[j][name]) {
								var t_c = createVector();
								t_c.y = points_data[name].points[i].coord.y + random(-rand_off,rand_off);
								t_c.x = points_data[name].points[i].coord.x;
								temp_array.push(t_c);
								temp_color_array.push(data[j].color);
							}
						}
					}
					return [temp_array, temp_color_array];
				}else{
					for (var i = 0; i < points_data[name].points.length; i++) {
						for (var j = 1; j < data.length; j++) {
							if (parseInt(data[j][name]) >= parseInt(points_data[name].points[i].min) && parseInt(data[j][name]) <= parseInt(points_data[name].points[i].max)) {
								var t_c = createVector();
								t_c.y = points_data[name].points[i].coord.y + random(-rand_off,rand_off);
								t_c.x = points_data[name].points[i].coord.x;
								temp_array.push(t_c);
								temp_color_array.push(data[j].color);
							}
						}
					}
					return [temp_array, temp_color_array];
				}
			}

			function draw_strings(col){
				var names = [];
				for (var i = 0; i < Object.keys(col).length; i++) {
					names.push(Object.keys(col)[i]);
				}
				for (var i = 0; i < col[names[0]].locs.length; i++) {
					if (col[names[1]].locs[i] != null) {
						stroke(col[names[1]].color[i]);
						line(col[names[0]].locs[i].x, col[names[0]].locs[i].y, col[names[1]].locs[i].x, col[names[1]].locs[i].y);
					}
				}

			}

			//data sheetsssssss........

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
						generate_strings_datastruct(data, points_data);
					},
					error: function(a, b, c){
						console.log(a, b, c);
					}
				});
			}



		</script>
	</div>
</body>

</html>