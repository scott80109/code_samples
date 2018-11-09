SELECT search_upload_detailed_results.company,
	    search_upload_detailed_results.clei,
	    search_upload_detailed_results.partNumber,
	    search_upload_detailed_results.qty,
	    search_upload_detailed_results.description,
	    search_upload_files.buyerId,
	    search_upload_files.user,
	    search_upload_buyers.buyer_name,
	    search_upload_buyers.buyer_email,
	    search_upload_companies.id AS buyerCompanyId,
	    search_upload_companies.companyName
	    FROM    (   (   search_upload_files search_upload_files
	    LEFT OUTER JOIN
	    search_upload_companies search_upload_companies
	    ON (search_upload_files.customer = search_upload_companies.id))
	    RIGHT OUTER JOIN
	    search_upload_detailed_results search_upload_detailed_results
	    ON (search_upload_detailed_results.fileId = search_upload_files.id))
	    LEFT OUTER JOIN
	    search_upload_buyers search_upload_buyers
	    ON (search_upload_files.buyerId = search_upload_buyers.id)
	    WHERE search_upload_detailed_results.id = $sudrId