<html>
	<head>
		<style>
			#engine-div
			{
				margin: 50 auto; 
				width: 400; 
				height: 175; 
				background-color: gainsboro; 
				border: 1px solid silver;
			}

			#search-div
			{
				font-size: 40;
				font-style: italic;
				text-align: center; 
				width: 100%; 
				background-color: gainsboro; 
			}

			#line
			{
				border-bottom: 1px solid silver;
				width: 90%;
				margin: 0 auto;
			}

			#search-buttons
			{
				margin-left: 46%;
			}

			#link
			{
				margin-left:35%;
			}

			#more-info
			{
				margin-top: 12;
			}

			table.table
			{
				width: 66%; 
				border: 1px solid gainsboro; 
				border-collapse: collapse;
				margin: 0 auto;
			}

			.box
			{
				border: 1px solid gainsboro; 
				border-collapse: collapse;
			}

			.centered
			{
				text-align: center;
			}

			div.error-box
			{
				width: 60%;
				background-color:gainsboro;
				padding: 3 3 3 3;
				border: 1px solid silver;
				margin: 0 auto;
			}
		</style>
	</head>
	<body>
		<div id = "engine-div">
			<div id = "search-div">
				Stock Search
			</div>
			<div id = "line">
			</div>
			<br />
			<div id = "form">
				<form method = "POST" action = "">
					Company Name or Symbol:
					<input id = "input" name = "company" type = "text" oninvalid = "alert('Please enter a Name or Symbol')" value = "<?php echo $_POST['company']; echo $_POST['Symbol']?>" required><br />
					<input id = "search-buttons" name = "Search" type = "submit" value = "Search">
					<input id = "reset" type = "button" value = "Clear" onClick = "document.getElementById('input').value = ''">			
				</form>
			</div>
			<a id = "link" href = "http://www.markit.com/product/markit-on-demand"> Powered by Markit on Demand </a>
		</div>

		<div id = "output">
			<?php
				if(isset($_POST["Search"]))
				{
					$company = $_POST["company"];

					$companies_str = file_get_contents("http://dev.markitondemand.com/MODApis/Api/v2/Lookup/json?input=" . $company);
					$companies = json_decode($companies_str, true);
					
					if(empty($companies))
					{
						echo "<div class = 'centered error-box'> No records have been found. </div>";
						return;
					}

					$output = "<table class = 'table'> <tr class = 'row'>";
					$output .= "<th class = 'centered box'>Name</th>";
					$output .= "<th class = 'centered box'>Symbol</th>";
					$output .= "<th class = 'centered box'>Exchange</th>";
					$output .= "<th class = 'centered box'>Details</th>";
					$output .= "</tr>";

					for($i = 0; $i < count($companies); $i++)
					{
						$output .= "<tr class = 'box'>";

						$output .= "<td class = 'box'>";
						$output .= $companies[$i]['Name'];
						$output .= "</td>";


						$output .= "<td class = 'box'>";
						$output .= $companies[$i]['Symbol'];
						$output .= "</td>";


						$output .= "<td class = 'box'>";
						$output .= $companies[$i]['Exchange'];
						$output .= "</td>";

						
						$output .= "<td class = 'box'>";
						$output .= "<form id = 'more-info' action = '' method = 'POST'>";
						$output .= "<input type = 'hidden' name = 'Symbol' value = '".$companies[$i]['Symbol']."'>";
						$output .= "<input type = 'submit' value = 'MoreInfo'>";
						$output .= "</form></td></tr>";
						
					}

					echo $output;		
				}

				if(isset($_POST['Symbol']))
				{
					$output = "";
					$stockSymbol = $_POST['Symbol'];

					$companies_str = file_get_contents('http://dev.markitondemand.com/MODApis/Api/v2/Quote/json?symbol=' . $stockSymbol);
					
					$companies = json_decode($companies_str, true);

					if($companies["Status"] == "Failure|APP_SPECIFIC_ERROR")
					{
						echo "<div class = 'centered error-box'>There is no stock information available.</div>";
						return;
					}

					$output = "<table class = 'table'> <tr class = 'box'>";
						
					$output .= "<tr class = 'box'><td class = 'box'>Name</td>";
					$output .= "<td class = 'centered box'>".$companies['Name']."</td></tr>";

					$output .= "<tr class = 'box'><td class = 'box'>Symbol</td>";
					$output .= "<td class = 'centered box'>".$companies['Symbol']."</td></tr>";
						
					$output .= "<tr class = 'box'><td class = 'box'>LastPrice</td>";
					$output .= "<td class = 'centered box'>".$companies['LastPrice']."</td></tr>";

					$output .= "<tr class = 'box'><td class = 'box'>Change</td>";
					$output .= "<td class = 'centered box'>".round($companies['Change'], 2);

					$output .= imgadd($companies['Change']);

					$output .= "</td></tr>";

					$output .= "<tr class = 'box'><td class = 'box'>Change Percent</td>";
					$output .= "<td class = 'centered box'>".round($companies['ChangePercent'], 2)."%";

					$output .= imgadd($companies['ChangePercent']);

					$output .= "</td></tr>";

					$output .= "<tr class = 'box'><td class = 'box'>Timestamp</td>";
					$output .= "<td class = 'centered box'>".date_format(date_create($companies['Timestamp']),"Y-m-d h:i A")."</td></tr>";
						
					$output .= "<tr class = 'box'><td class = 'box'>Market Cap</td>";
					$output .= "<td class = 'centered box'>".round($companies['MarketCap']/1000000000, 2)."B</td></tr>";

					$output .= "<tr class = 'box'><td class = 'box'>Volume</td>";
					$output .= "<td class = 'centered box'>".number_format($companies['Volume'])."</td></tr>";

					$change = $companies['LastPrice'] - $companies['ChangeYTD'];
					
					if($change < 0)
					{
						$output .= "<tr class = 'box'><td class = 'box'>Change YTD</td>";
						$output .= "<td class = 'centered box'>(".round($change, 2).")";
					}
					else
					{
						$output .= "<tr class = 'box'><td class = 'box'>Change YTD</td>";
						$output .= "<td class = 'centered box'>".round($change, 2);
					}
					
					$output .= imgadd($change);
					$output .= "</td></tr>";

					$output .= "<tr class = 'box'><td class = 'box'>Change Percent YTD</td>";
					$output .= "<td class = 'centered box'>".round($companies['ChangePercentYTD'], 2)."%";

					$output .= imgadd($companies['ChangePercentYTD']);
					$output .= "</td></tr>";

					$output .= "<tr class = 'box'><td class = 'box'>High</td>";
					$output .= "<td class = 'centered box'>".$companies['High']."</td></tr>";
						
					$output .= "<tr class = 'box'><td class = 'box'>Low</td>";
					$output .= "<td class = 'centered box'>".$companies['Low']."</td></tr>";

					$output .= "<tr class = 'box'><td class = 'box'>Open</td>";
					$output .= "<td class = 'centered box'>".$companies['Open']."</td></tr>";

					echo $output;
				}

				function imgadd($value)
				{
					if($value > 0)
						return "<img style = 'width:10; height:10;' src = 'green.png'>";
					else if($value < 0)
						return "<img style = 'width:10; height:10;' src = 'red.png'>";
				}
			?>
		</div>

	</body>
</html>