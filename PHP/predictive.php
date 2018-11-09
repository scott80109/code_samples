<?php

//calculate slope, intercept, and r-squared
		$data =
		array
		(
				array( 0, 2 ), array( 30, 10 ), array( 60, 15 )
		);
		
		// Precision digits in BC math.
		bcscale( 10 );
		
		// Start a regression class of order 2--linear regression.
		$leastSquareRegression = new PolynomialRegression( 2 );
		
		// Add all the data to the regression analysis.
		foreach ( $data as $dataPoint ) {
			$leastSquareRegression->addData( $dataPoint[ 0 ], $dataPoint[ 1 ] );
		}
		
		// Get coefficients for the polynomial.
		$coefficients = $leastSquareRegression->getCoefficients();
		
		$slope = round( $coefficients[ 1 ], 4 );
		$intercept = round( $coefficients[ 0 ], 4 );
		// Print slope and intercept of linear regression.
		//echo "Slope : $slope<br />\n";
		//echo "Y-intercept : $intercept<br />\n";
		
		//
		// Get average of Y-data.
		//
		$Y_Average = 0.0;
		foreach ( $data as $dataPoint ) {
			$Y_Average += $dataPoint[ 1 ];
		}
		
		$Y_Average /= count( $data );
		
		//
		// Calculate R Squared.
		//
		
		$Y_MeanSum  = 0.0;
		$Y_ErrorSum = 0.0;
		foreach ( $data as $dataPoint )
		{
			$x = $dataPoint[ 0 ];
			$y = $dataPoint[ 1 ];
			$error  = $y;
			$error -= $leastSquareRegression->interpolate( $coefficients, $x );
			$Y_ErrorSum += $error * $error;
		
			$error  = $y;
			$error -= $Y_Average;
			$Y_MeanSum += $error * $error;
		}
		
		$R_Squared = 1.0 - ( $Y_ErrorSum / $Y_MeanSum );
		
		
		