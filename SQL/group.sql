SELECT upper(substr(replace(replace(clei,' ',''), '-', ''),1,7)) as clei, 
avg(pricePerUnit) as quoteAvg,
max(submittedDate) as lastQuoteDate,
count(*) as totalQuotes
FROM search_upload_csg_quotes
where clei is not null and clei != '' and pricePerUnit > 0
group by upper(substr(replace(replace(clei,' ',''), '-', ''),1,7));