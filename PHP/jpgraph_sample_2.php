    private function createDecommissionedChart($reportYear=NULL)
    {
    	$cId = Csg_Model_User::getCustomerId();
    	
    	//calculate pickups by quarter
    	$poModel = new Csg_Model_PO();
    	$ciModel = new Csg_Model_CostInformation();
    	$db = $poModel->getAdapter();
    	 
    	//Q1
    	if (is_null($reportYear)) {
    	    $year = date("Y");
    	} else {
    	    $year = $reportYear;
    	}
    	$begin = "$year-01-01";
    	$end = "$year-03-31";
    	$sql = "select po_number from po where removal_date BETWEEN '$begin' AND '$end' AND cust_id = '$cId'";
    	$statement = $db->query($sql);
    	$result = $statement->fetchAll();
    	$posArray = array();
    	foreach ($result as $r) {
    		$posArray[] = $r['po_number'];
    	}
    	$posStr = implode("','", $posArray);
    	$sql = "select * from cost_information where poNumber IN ('$posStr') AND amount > 0";
    	$statement = $db->query($sql);
    	$result = $statement->fetchAll();
    	$qTotal = 0;
    	foreach ($result as $r) {
    		if ($r['type'] == "Labor Costs" || $r['type'] == "Shipping Costs" || $r['type'] == "Other Costs" || $r['type'] == "Total Costs" || $r['type'] == "Transportation Costs") {
    			$qTotal += $r['amount'];
    		}
    	}
    	$q1 = (float)$qTotal;
    	
    	//Q2
    	$begin = "$year-04-01";
    	$end = "$year-06-30";
    	$sql = "select po_number from po where removal_date BETWEEN '$begin' AND '$end' AND cust_id = '$cId'";
    	$statement = $db->query($sql);
    	$result = $statement->fetchAll();
    	$posArray = array();
    	foreach ($result as $r) {
    		$posArray[] = $r['po_number'];
    	}
    	$posStr = implode("','", $posArray);
    	$sql = "select * from cost_information where poNumber IN ('$posStr') AND amount > 0";
    	$statement = $db->query($sql);
    	$result = $statement->fetchAll();
    	$qTotal = 0;
    	foreach ($result as $r) {
    		if ($r['type'] == "Labor Costs" || $r['type'] == "Shipping Costs" || $r['type'] == "Other Costs" || $r['type'] == "Total Costs" || $r['type'] == "Transportation Costs") {
    			$qTotal += $r['amount'];
    		}
    	}
    	$q2 = (float)$qTotal;
    	 
    	//Q3
    	$begin = "$year-07-01";
    	$end = "$year-09-30";
    	$sql = "select po_number from po where removal_date BETWEEN '$begin' AND '$end' AND cust_id = '$cId'";
    	$statement = $db->query($sql);
    	$result = $statement->fetchAll();
    	$posArray = array();
    	foreach ($result as $r) {
    		$posArray[] = $r['po_number'];
    	}
    	$posStr = implode("','", $posArray);
    	$sql = "select * from cost_information where poNumber IN ('$posStr') AND amount > 0";
    	$statement = $db->query($sql);
    	$result = $statement->fetchAll();
    	$qTotal = 0;
    	foreach ($result as $r) {
    		if ($r['type'] == "Labor Costs" || $r['type'] == "Shipping Costs" || $r['type'] == "Other Costs" || $r['type'] == "Total Costs" || $r['type'] == "Transportation Costs") {
    			$qTotal += $r['amount'];
    		}
    	}
    	$q3 = (float)$qTotal;
    	 
    	//Q4
    	$begin = "$year-10-01";
    	$end = "$year-12-31";
    	$sql = "select po_number from po where removal_date BETWEEN '$begin' AND '$end' AND cust_id = '$cId'";
    	$statement = $db->query($sql);
    	$result = $statement->fetchAll();
    	$posArray = array();
    	foreach ($result as $r) {
    		$posArray[] = $r['po_number'];
    	}
    	$posStr = implode("','", $posArray);
    	$sql = "select * from cost_information where poNumber IN ('$posStr') AND amount > 0";
    	$statement = $db->query($sql);
    	$result = $statement->fetchAll();
    	$qTotal = 0;
    	foreach ($result as $r) {
    		if ($r['type'] == "Labor Costs" || $r['type'] == "Shipping Costs" || $r['type'] == "Other Costs" || $r['type'] == "Total Costs" || $r['type'] == "Transportation Costs") {
    			$qTotal += $r['amount'];
    		}
    	}
    	$q4 = (float)$qTotal;
    	 
    	//YTD
    	$begin = "$year-01-01";
    	$end = "$year-12-31";
    	$sql = "select po_number from po where removal_date BETWEEN '$begin' AND '$end' AND cust_id = '$cId'";
    	$statement = $db->query($sql);
    	$result = $statement->fetchAll();
    	$posArray = array();
    	foreach ($result as $r) {
    		$posArray[] = $r['po_number'];
    	}
    	$posStr = implode("','", $posArray);
    	$sql = "select * from cost_information where poNumber IN ('$posStr') AND amount > 0";
    	$statement = $db->query($sql);
    	$result = $statement->fetchAll();
    	$qTotal = 0;
    	foreach ($result as $r) {
    		if ($r['type'] == "Labor Costs" || $r['type'] == "Shipping Costs" || $r['type'] == "Other Costs" || $r['type'] == "Total Costs" || $r['type'] == "Transportation Costs") {
    			$qTotal += $r['amount'];
    		}
    	}
    	$ytd = (float)$qTotal;
    	 
    	$datay=array($q1,$q2,$q3,$q4,$ytd);
    	 
    	// Create the graph. These two calls are always required
    	$graph = new Graph(300,170,"auto");
    	$graph->SetScale("textlin");
    	 
    	// Add a drop shadow
    	$graph->SetShadow();
    	 
    	// Adjust the margin a bit to make more room for titles
    	$graph->img->SetMargin(80,30,10,25);
    	
    	//$graph->SetScale("textint");
    	$graph->yaxis->scale->SetGrace(50);
    	 
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
    	
    	//format for money
    	$graph->yaxis->SetLabelFormatCallback('yScaleCallback');
    	
    	//write out the image file
    	$fileName = $this->userChartsDir . "decom.png";
    	@unlink($fileName);
    	$graph->Stroke($fileName);
    }