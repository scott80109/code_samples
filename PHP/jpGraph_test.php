<?php
require_once '../../application/autoloader.inc';
require_once ("../../library/jpgraph/jpgraph.php");
require_once ("../../library/jpgraph/jpgraph_bar.php");

        $datay=array(12,8,19,3, 16);
		
		// Create the graph. These two calls are always required
		$graph = new Graph(300,200,"auto");
		$graph->SetScale("textlin");
		
		// Add a drop shadow
		$graph->SetShadow();
		
		// Adjust the margin a bit to make more room for titles
		$graph->img->SetMargin(40,30,20,40);
		
		// Create a bar pot
		$bplot = new BarPlot($datay);
		
		// Adjust fill color
		$bplot->SetFillColor('orange');
		
		// Setup values
		$bplot->value->Show();
		$bplot->value->SetFormat('%d');
		$bplot->value->SetFont(FF_ARIAL,FS_BOLD);
		
		// Center the values in the bar
		$bplot->SetValuePos('center');
		
		// Make the bar a little bit wider
		$bplot->SetWidth(0.7);
		
		$graph->Add($bplot);
		
		// Setup the titles
		$graph->title->Set("");
		$graph->xaxis->title->Set("");
		$graph->yaxis->title->Set("");
		
		$lbl = array("Q1","Q2","Q3","Q4","YTD");
		$graph->xaxis->SetTickLabels($lbl);
		
		$graph->title->SetFont(FF_ARIAL,FS_BOLD);
		$graph->yaxis->title->SetFont(FF_ARIAL,FS_BOLD);
		$graph->xaxis->title->SetFont(FF_ARIAL,FS_BOLD);

		// Display the graph
		//print '<img src="data:image/png;base64,'.base64_encode($graph->Stroke()).'" />';
        $graph->Stroke();
