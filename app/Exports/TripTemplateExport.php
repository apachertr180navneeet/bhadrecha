<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TripTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function array(): array
    {
        return [
            // =========================================================================
            // TRIP 1 (LR-2026-0001): 3 Rows demonstrating MULTIPLE Items, Tolls,
            // Fuels, AdBlue, Other Expenses, and Advances for the SAME LR / Trip.
            // =========================================================================

            // --- LR-2026-0001 : ROW 1 (Item 1, Toll 1, Fuel 1, AdBlue 1, Other 1, Advance 1) ---
            [
                'LR-2026-0001',                     // 1. lr_no
                '2026-05-20',                       // 2. lr_date
                'topay',                            // 3. payment_type (paid / topay / tobill)
                'Mumbai',                           // 4. from_city
                'Delhi',                            // 5. to_city
                'ORD-1001',                         // 6. order_number
                'DEL-2001',                         // 7. delivery_number
                'INV-9901',                         // 8. invoice_number
                '2026-05-20',                       // 9. invoice_date
                'EWAY-8888001',                     // 10. eway_bill_no
                'MATDOC-5501',                      // 11. mat_doc
                'PO-7701',                          // 12. po_no
                'CH-3301',                          // 13. challan_no
                '2026-05-20',                       // 14. challan_date
                'GRN-4401',                         // 15. grn_no
                '2026-05-20',                       // 16. grn_date
                'ABC Logistics Pvt Ltd',            // 17. consignor_name
                '9876543210',                       // 18. consignor_phone
                '27AAACA1234A1Z5',                  // 19. consignor_gstin
                'Plot 12, MIDC, Andheri, Mumbai',   // 20. consignor_address
                'XYZ Enterprises Ltd',              // 21. consignee_name
                '9876543211',                       // 22. consignee_phone
                '07AAACX5678B1Z2',                  // 23. consignee_gstin
                'Sector 18, Okhla, New Delhi',      // 24. consignee_address
                'MH04AB1234',                       // 25. vehicle_number
                'Truck',                            // 26. vehicle_type
                'Ramesh Singh',                     // 27. driver_name
                '9876543212',                       // 28. driver_phone
                'in_transit',                       // 29. bilty_status
                // Item 1
                'Steel Coils',                      // 30. item_name
                'Bundles',                          // 31. packaging_type
                10,                                 // 32. articles
                15.50,                              // 33. weight (MT)
                'MT',                               // 34. unit
                2000.00,                            // 35. freight_per_mt
                31000.00,                           // 36. item_amount
                5000.00,                            // 37. bilty_advance_amount
                75000.00,                           // 38. bilty_total_amount
                // Toll 1
                '2026-05-20 10:30',                 // 39. toll_time
                'Ghoti Toll Plaza',                 // 40. toll_location
                450.00,                             // 41. toll_amount
                450.00,                             // 42. toll_oneway
                0.00,                               // 43. toll_return
                'FastTag Toll Plaza deduction',     // 44. toll_description
                'TXN-FAST-1001',                    // 45. toll_txn_id
                // Fuel 1
                '2026-05-20',                       // 46. fuel_date
                'Indian Oil Corporation',           // 47. fuel_company_name
                'IOCL Highway Pump Nashik',         // 48. fuel_pump_name
                60.00,                              // 49. fuel_quantity (Liters)
                95.00,                              // 50. fuel_rate
                5700.00,                            // 51. fuel_amount
                12500,                              // 52. fuel_km
                'credit',                           // 53. fuel_payment_type (credit / debit / cash)
                'Full tank diesel refill',          // 54. fuel_remark
                // AdBlue 1
                '2026-05-20',                       // 55. adblue_date
                'GoBlue Liquids',                   // 56. adblue_company_name
                15.00,                              // 57. adblue_quantity (Liters)
                50.00,                              // 58. adblue_rate
                750.00,                             // 59. adblue_amount
                12500,                              // 60. adblue_km
                'cash',                             // 61. adblue_payment_type (credit / debit / cash)
                // Other Expense 1
                'Loading & Labor Charges',          // 62. other_expense_title
                400.00,                             // 63. other_expense_amount
                '2026-05-20',                       // 64. other_expense_date
                'Labor tip at Mumbai warehouse',    // 65. other_expense_remark
                // Advance 1
                '2026-05-20',                       // 66. advance_date
                'Indian Oil Corporation',           // 67. advance_fuel_company_name
                'IOCL Highway Pump Nashik',         // 68. advance_fuel_pump_name
                2000.00,                            // 69. advance_amount
                'cash',                             // 70. advance_payment_type (credit / debit / cash)
                'Driver trip start cash advance',   // 71. advance_remark
                // Trip Status
                'pending'                           // 72. trip_status
            ],

            // --- LR-2026-0001 : ROW 2 (Item 2, Toll 2, Fuel 2, AdBlue 2, Other 2, Advance 2) ---
            [
                'LR-2026-0001',                     // 1. lr_no (SAME LR)
                '2026-05-20',                       // 2. lr_date
                'topay',                            // 3. payment_type
                'Mumbai',                           // 4. from_city
                'Delhi',                            // 5. to_city
                'ORD-1001',                         // 6. order_number
                'DEL-2001',                         // 7. delivery_number
                'INV-9901',                         // 8. invoice_number
                '2026-05-20',                       // 9. invoice_date
                'EWAY-8888001',                     // 10. eway_bill_no
                'MATDOC-5501',                      // 11. mat_doc
                'PO-7701',                          // 12. po_no
                'CH-3301',                          // 13. challan_no
                '2026-05-20',                       // 14. challan_date
                'GRN-4401',                         // 15. grn_no
                '2026-05-20',                       // 16. grn_date
                'ABC Logistics Pvt Ltd',            // 17. consignor_name
                '9876543210',                       // 18. consignor_phone
                '27AAACA1234A1Z5',                  // 19. consignor_gstin
                'Plot 12, MIDC, Andheri, Mumbai',   // 20. consignor_address
                'XYZ Enterprises Ltd',              // 21. consignee_name
                '9876543211',                       // 22. consignee_phone
                '07AAACX5678B1Z2',                  // 23. consignee_gstin
                'Sector 18, Okhla, New Delhi',      // 24. consignee_address
                'MH04AB1234',                       // 25. vehicle_number
                'Truck',                            // 26. vehicle_type
                'Ramesh Singh',                     // 27. driver_name
                '9876543212',                       // 28. driver_phone
                'in_transit',                       // 29. bilty_status
                // Item 2
                'Iron Rods & Bars',                 // 30. item_name
                'Bundles',                          // 31. packaging_type
                20,                                 // 32. articles
                10.00,                              // 33. weight (MT)
                'MT',                               // 34. unit
                2000.00,                            // 35. freight_per_mt
                20000.00,                           // 36. item_amount
                0.00,                               // 37. bilty_advance_amount
                0.00,                               // 38. bilty_total_amount
                // Toll 2
                '2026-05-20 16:45',                 // 39. toll_time
                'Pimpalgaon Toll Plaza',            // 40. toll_location
                380.00,                             // 41. toll_amount
                380.00,                             // 42. toll_oneway
                0.00,                               // 43. toll_return
                'FastTag Toll deduction',           // 44. toll_description
                'TXN-FAST-1002',                    // 45. toll_txn_id
                // Fuel 2
                '2026-05-21',                       // 46. fuel_date
                'Bharat Petroleum',                 // 47. fuel_company_name
                'BPCL Highway Pump Dhule',          // 48. fuel_pump_name
                40.00,                              // 49. fuel_quantity
                95.00,                              // 50. fuel_rate
                3800.00,                            // 51. fuel_amount
                12800,                              // 52. fuel_km
                'credit',                           // 53. fuel_payment_type
                'Enroute top-up fuel',              // 54. fuel_remark
                // AdBlue 2
                '2026-05-21',                       // 55. adblue_date
                'GoBlue Liquids',                   // 56. adblue_company_name
                10.00,                              // 57. adblue_quantity
                50.00,                              // 58. adblue_rate
                500.00,                             // 59. adblue_amount
                12800,                              // 60. adblue_km
                'credit',                           // 61. adblue_payment_type
                // Other Expense 2
                'Weighbridge Charges',              // 62. other_expense_title
                200.00,                             // 63. other_expense_amount
                '2026-05-21',                       // 64. other_expense_date
                'Dharam kanta weight receipt',      // 65. other_expense_remark
                // Advance 2
                '2026-05-21',                       // 66. advance_date
                'Bharat Petroleum',                 // 67. advance_fuel_company_name
                'BPCL Highway Pump Dhule',          // 68. advance_fuel_pump_name
                1000.00,                            // 69. advance_amount
                'cash',                             // 70. advance_payment_type
                'Driver food & tea allowance',      // 71. advance_remark
                // Trip Status
                'pending'                           // 72. trip_status
            ],

            // --- LR-2026-0001 : ROW 3 (Item 3, Toll 3, Fuel 3, AdBlue 3, Other 3, Advance 3) ---
            [
                'LR-2026-0001',                     // 1. lr_no (SAME LR)
                '2026-05-20',                       // 2. lr_date
                'topay',                            // 3. payment_type
                'Mumbai',                           // 4. from_city
                'Delhi',                            // 5. to_city
                'ORD-1001',                         // 6. order_number
                'DEL-2001',                         // 7. delivery_number
                'INV-9901',                         // 8. invoice_number
                '2026-05-20',                       // 9. invoice_date
                'EWAY-8888001',                     // 10. eway_bill_no
                'MATDOC-5501',                      // 11. mat_doc
                'PO-7701',                          // 12. po_no
                'CH-3301',                          // 13. challan_no
                '2026-05-20',                       // 14. challan_date
                'GRN-4401',                         // 15. grn_no
                '2026-05-20',                       // 16. grn_date
                'ABC Logistics Pvt Ltd',            // 17. consignor_name
                '9876543210',                       // 18. consignor_phone
                '27AAACA1234A1Z5',                  // 19. consignor_gstin
                'Plot 12, MIDC, Andheri, Mumbai',   // 20. consignor_address
                'XYZ Enterprises Ltd',              // 21. consignee_name
                '9876543211',                       // 22. consignee_phone
                '07AAACX5678B1Z2',                  // 23. consignee_gstin
                'Sector 18, Okhla, New Delhi',      // 24. consignee_address
                'MH04AB1234',                       // 25. vehicle_number
                'Truck',                            // 26. vehicle_type
                'Ramesh Singh',                     // 27. driver_name
                '9876543212',                       // 28. driver_phone
                'in_transit',                       // 29. bilty_status
                // Item 3
                'Steel Plates',                     // 30. item_name
                'Loose',                            // 31. packaging_type
                15,                                 // 32. articles
                12.00,                              // 33. weight (MT)
                'MT',                               // 34. unit
                2000.00,                            // 35. freight_per_mt
                24000.00,                           // 36. item_amount
                0.00,                               // 37. bilty_advance_amount
                0.00,                               // 38. bilty_total_amount
                // Toll 3
                '2026-05-21 21:15',                 // 39. toll_time
                'Sendhwa Border Toll Plaza',        // 40. toll_location
                520.00,                             // 41. toll_amount
                520.00,                             // 42. toll_oneway
                0.00,                               // 43. toll_return
                'Border toll fee deduction',        // 44. toll_description
                'TXN-FAST-1003',                    // 45. toll_txn_id
                // Fuel 3
                '2026-05-22',                       // 46. fuel_date
                'Hindustan Petroleum',              // 47. fuel_company_name
                'HPCL Highway Pump Indore',         // 48. fuel_pump_name
                50.00,                              // 49. fuel_quantity
                94.80,                              // 50. fuel_rate
                4740.00,                            // 51. fuel_amount
                13150,                              // 52. fuel_km
                'credit',                           // 53. fuel_payment_type
                'Midway fuel refill',               // 54. fuel_remark
                // AdBlue 3
                '2026-05-22',                       // 55. adblue_date
                'BlueMax Chemicals',                // 56. adblue_company_name
                10.00,                              // 57. adblue_quantity
                48.00,                              // 58. adblue_rate
                480.00,                             // 59. adblue_amount
                13150,                              // 60. adblue_km
                'cash',                             // 61. adblue_payment_type
                // Other Expense 3
                'Toll Parking & Entry Tax',         // 62. other_expense_title
                350.00,                             // 63. other_expense_amount
                '2026-05-22',                       // 64. other_expense_date
                'Night rest stop parking fees',     // 65. other_expense_remark
                // Advance 3
                '2026-05-22',                       // 66. advance_date
                'Hindustan Petroleum',              // 67. advance_fuel_company_name
                'HPCL Highway Pump Indore',         // 68. advance_fuel_pump_name
                1500.00,                            // 69. advance_amount
                'cash',                             // 70. advance_payment_type
                'Driver emergency advance',         // 71. advance_remark
                // Trip Status
                'pending'                           // 72. trip_status
            ],

            // =========================================================================
            // TRIP 2 (LR-2026-0002): 2 Rows demonstrating another MULTI-ROW Trip
            // (Surat to Jaipur)
            // =========================================================================

            // --- LR-2026-0002 : ROW 1 (Item 1, Toll 1, Fuel 1, AdBlue 1, Other 1, Advance 1) ---
            [
                'LR-2026-0002',                     // 1. lr_no
                '2026-05-21',                       // 2. lr_date
                'paid',                             // 3. payment_type
                'Surat',                            // 4. from_city
                'Jaipur',                           // 5. to_city
                'ORD-1002',                         // 6. order_number
                'DEL-2002',                         // 7. delivery_number
                'INV-9902',                         // 8. invoice_number
                '2026-05-21',                       // 9. invoice_date
                'EWAY-8888002',                     // 10. eway_bill_no
                'MATDOC-5502',                      // 11. mat_doc
                'PO-7702',                          // 12. po_no
                'CH-3302',                          // 13. challan_no
                '2026-05-21',                       // 14. challan_date
                'GRN-4402',                         // 15. grn_no
                '2026-05-21',                       // 16. grn_date
                'Prime Textiles Inc',               // 17. consignor_name
                '9876543213',                       // 18. consignor_phone
                '24AAACT9999C1Z8',                  // 19. consignor_gstin
                'Ring Road, Surat, Gujarat',        // 20. consignor_address
                'Apex Garments Trading',            // 21. consignee_name
                '9876543214',                       // 22. consignee_phone
                '08AAACG8888D1Z4',                  // 23. consignee_gstin
                'MI Road, Jaipur, Rajasthan',       // 24. consignee_address
                'GJ01CD5678',                       // 25. vehicle_number
                'Trailer',                          // 26. vehicle_type
                'Suresh Patel',                     // 27. driver_name
                '9876543215',                       // 28. driver_phone
                'delivered',                        // 29. bilty_status
                // Item 1
                'Cotton Yarn Bundles',              // 30. item_name
                'Bags',                             // 31. packaging_type
                50,                                 // 32. articles
                18.00,                              // 33. weight (MT)
                'MT',                               // 34. unit
                1777.77,                            // 35. freight_per_mt
                32000.00,                           // 36. item_amount
                4000.00,                            // 37. bilty_advance_amount
                52000.00,                           // 38. bilty_total_amount
                // Toll 1
                '2026-05-21 11:20',                 // 39. toll_time
                'Ahmedabad Ring Road Toll',         // 40. toll_location
                250.00,                             // 41. toll_amount
                250.00,                             // 42. toll_oneway
                0.00,                               // 43. toll_return
                'FastTag Toll pass',                // 44. toll_description
                'TXN-FAST-2001',                    // 45. toll_txn_id
                // Fuel 1
                '2026-05-21',                       // 46. fuel_date
                'Hindustan Petroleum',              // 47. fuel_company_name
                'HPCL Pump Himmatnagar',            // 48. fuel_pump_name
                70.00,                              // 49. fuel_quantity
                94.50,                              // 50. fuel_rate
                6615.00,                            // 51. fuel_amount
                8400,                               // 52. fuel_km
                'credit',                           // 53. fuel_payment_type
                'Full tank diesel refill',          // 54. fuel_remark
                // AdBlue 1
                '2026-05-21',                       // 55. adblue_date
                'BlueMax Chemicals',                // 56. adblue_company_name
                20.00,                              // 57. adblue_quantity
                48.00,                              // 58. adblue_rate
                960.00,                             // 59. adblue_amount
                8400,                               // 60. adblue_km
                'cash',                             // 61. adblue_payment_type
                // Other Expense 1
                'Weighbridge Charges',              // 62. other_expense_title
                150.00,                             // 63. other_expense_amount
                '2026-05-21',                       // 64. other_expense_date
                'Dharam kanta gross weight slip',   // 65. other_expense_remark
                // Advance 1
                '2026-05-21',                       // 66. advance_date
                'Hindustan Petroleum',              // 67. advance_fuel_company_name
                'HPCL Pump Himmatnagar',            // 68. advance_fuel_pump_name
                2500.00,                            // 69. advance_amount
                'cash',                             // 70. advance_payment_type
                'Trip advance from petrol pump',    // 71. advance_remark
                // Trip Status
                'complete'                          // 72. trip_status
            ],

            // --- LR-2026-0002 : ROW 2 (Item 2, Toll 2, Fuel 2, AdBlue 2, Other 2, Advance 2) ---
            [
                'LR-2026-0002',                     // 1. lr_no (SAME LR)
                '2026-05-21',                       // 2. lr_date
                'paid',                             // 3. payment_type
                'Surat',                            // 4. from_city
                'Jaipur',                           // 5. to_city
                'ORD-1002',                         // 6. order_number
                'DEL-2002',                         // 7. delivery_number
                'INV-9902',                         // 8. invoice_number
                '2026-05-21',                       // 9. invoice_date
                'EWAY-8888002',                     // 10. eway_bill_no
                'MATDOC-5502',                      // 11. mat_doc
                'PO-7702',                          // 12. po_no
                'CH-3302',                          // 13. challan_no
                '2026-05-21',                       // 14. challan_date
                'GRN-4402',                         // 15. grn_no
                '2026-05-21',                       // 16. grn_date
                'Prime Textiles Inc',               // 17. consignor_name
                '9876543213',                       // 18. consignor_phone
                '24AAACT9999C1Z8',                  // 19. consignor_gstin
                'Ring Road, Surat, Gujarat',        // 20. consignor_address
                'Apex Garments Trading',            // 21. consignee_name
                '9876543214',                       // 22. consignee_phone
                '08AAACG8888D1Z4',                  // 23. consignee_gstin
                'MI Road, Jaipur, Rajasthan',       // 24. consignee_address
                'GJ01CD5678',                       // 25. vehicle_number
                'Trailer',                          // 26. vehicle_type
                'Suresh Patel',                     // 27. driver_name
                '9876543215',                       // 28. driver_phone
                'delivered',                        // 29. bilty_status
                // Item 2
                'Textile Fabric Rolls',             // 30. item_name
                'Rolls',                            // 31. packaging_type
                80,                                 // 32. articles
                10.00,                              // 33. weight (MT)
                'MT',                               // 34. unit
                2000.00,                            // 35. freight_per_mt
                20000.00,                           // 36. item_amount
                0.00,                               // 37. bilty_advance_amount
                0.00,                               // 38. bilty_total_amount
                // Toll 2
                '2026-05-22 08:45',                 // 39. toll_time
                'Kishangarh Toll Plaza',            // 40. toll_location
                320.00,                             // 41. toll_amount
                320.00,                             // 42. toll_oneway
                0.00,                               // 43. toll_return
                'FastTag Toll deduction',           // 44. toll_description
                'TXN-FAST-2002',                    // 45. toll_txn_id
                // Fuel 2
                '2026-05-22',                       // 46. fuel_date
                'Reliance Petroleum',               // 47. fuel_company_name
                'Jio-bp Highway Pump Ajmer',        // 48. fuel_pump_name
                45.00,                              // 49. fuel_quantity
                95.20,                              // 50. fuel_rate
                4284.00,                            // 51. fuel_amount
                8750,                               // 52. fuel_km
                'credit',                           // 53. fuel_payment_type
                'Midway topup fuel',                // 54. fuel_remark
                // AdBlue 2
                '2026-05-22',                       // 55. adblue_date
                'BlueMax Chemicals',                // 56. adblue_company_name
                10.00,                              // 57. adblue_quantity
                48.00,                              // 58. adblue_rate
                480.00,                             // 59. adblue_amount
                8750,                               // 60. adblue_km
                'credit',                           // 61. adblue_payment_type
                // Other Expense 2
                'Unloading Helper Tip',             // 62. other_expense_title
                300.00,                             // 63. other_expense_amount
                '2026-05-22',                       // 64. other_expense_date
                'Destination unloading helper tip', // 65. other_expense_remark
                // Advance 2
                '2026-05-22',                       // 66. advance_date
                'Reliance Petroleum',               // 67. advance_fuel_company_name
                'Jio-bp Highway Pump Ajmer',        // 68. advance_fuel_pump_name
                1000.00,                            // 69. advance_amount
                'cash',                             // 70. advance_payment_type
                'Driver pocket expense advance',    // 71. advance_remark
                // Trip Status
                'complete'                          // 72. trip_status
            ]
        ];
    }

    public function headings(): array
    {
        return [
            // Builty - Basic & Route Info
            'lr_no',
            'lr_date',
            'payment_type',
            'from_city',
            'to_city',
            'order_number',
            'delivery_number',
            'invoice_number',
            'invoice_date',
            'eway_bill_no',
            'mat_doc',
            'po_no',
            'challan_no',
            'challan_date',
            'grn_no',
            'grn_date',

            // Builty - Parties Details
            'consignor_name',
            'consignor_phone',
            'consignor_gstin',
            'consignor_address',
            'consignee_name',
            'consignee_phone',
            'consignee_gstin',
            'consignee_address',

            // Builty - Vehicle & Driver Info
            'vehicle_number',
            'vehicle_type',
            'driver_name',
            'driver_phone',
            'bilty_status',

            // Builty - Items / Goods (Supports Multi-Items per LR)
            'item_name',
            'packaging_type',
            'articles',
            'weight',
            'unit',
            'freight_per_mt',
            'item_amount',
            'bilty_advance_amount',
            'bilty_total_amount',

            // Trip - FastTag / Toll Details (Supports Multi-Tolls per LR)
            'toll_time',
            'toll_location',
            'toll_amount',
            'toll_oneway',
            'toll_return',
            'toll_description',
            'toll_txn_id',

            // Trip - Fuel Details (Supports Multi-Fuel entries per LR)
            'fuel_date',
            'fuel_company_name',
            'fuel_pump_name',
            'fuel_quantity',
            'fuel_rate',
            'fuel_amount',
            'fuel_km',
            'fuel_payment_type',
            'fuel_remark',

            // Trip - AdBlue Details (Supports Multi-AdBlue entries per LR)
            'adblue_date',
            'adblue_company_name',
            'adblue_quantity',
            'adblue_rate',
            'adblue_amount',
            'adblue_km',
            'adblue_payment_type',

            // Trip - Other Expense Details (Supports Multi-Expenses per LR)
            'other_expense_title',
            'other_expense_amount',
            'other_expense_date',
            'other_expense_remark',

            // Trip - Advance Details (Supports Multi-Advances per LR)
            'advance_date',
            'advance_fuel_company_name',
            'advance_fuel_pump_name',
            'advance_amount',
            'advance_payment_type',
            'advance_remark',

            // Trip Status
            'trip_status'
        ];
    }
}
