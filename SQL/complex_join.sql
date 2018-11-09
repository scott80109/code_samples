SELECT idc.id_id, idc.partNumber, idc.clei, idc.description, idc.serial_number, idc.eci, idc.qty, idc.qtySold, (idc.qty-idc.qtySold) as available,
DATE_FORMAT(po.removal_date, "%m/%d/%Y") as removal_date, datediff(now(),po.removal_date) as age, DATE_FORMAT(cp.payment_date, "%m/%d/%Y") as payment_date
                            from inventory_detail_consignment idc 
                            left join po on idc.po_number = po.po_number
                            left join consignment_sold_items csi on idc.id_id = csi.item_id
                            left join consignment_payments cp on csi.poPaymentId = cp.id 
                            where idc.partNumber LIKE '1223026%' AND idc.serial_number LIKE '77R09014752%'  and
                            (cp.payment_date IS NULL or cp.payment_date = (SELECT MAX(cp.payment_date) FROM consignment_payments cp WHERE cp.id = csi.poPaymentId))
                            group by idc.id_id