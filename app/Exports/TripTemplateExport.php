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
            // SAMPLE TRIP 1 (LR-2026-0001): 3 Rows demonstrating MULTIPLE Items,
            // Tolls, Fuels, AdBlue, Other Expenses, and Advances for the SAME LR / Trip.
            // =========================================================================

            // --- LR-2026-0001 : ROW 1 (Item 1, Toll 1, Fuel 1, AdBlue 1, Other 1, Advance 1) ---
            [
                'Bhadrecha Logistics',              // 1. company
                'Main Branch Mumbai',               // 2. branch
                'LR-2026-0001',                     // 3. lr_no
                '2026-05-20',                       // 4. lr_date
                'topay',                            // 5. payment_type (paid / topay / tobill)
                'Mumbai',                           // 6. from_city
                'Delhi',                            // 7. to_city
                'ORD-1001',                         // 8. order_number
                'DEL-2001',                         // 9. delivery_number
                'FORM-8890',                        // 10. form_number
                'INV-9901',                         // 11. invoice_number
                '2026-05-20',                       // 12. invoice_date
                'EWAY-8888001',                     // 13. eway_bill_no
                '2026-05-20',                       // 14. generate_date
                '2026-05-25',                       // 15. expiry_date
                'PO-7701',                          // 16. po_no
                // Builty - GRN / SAP Details
                '2026-05-20',                       // 17. posting_date
                'MATDOC-5001',                      // 18. mat_doc
                'GE-9001',                          // 19. gate_entry_no
                'JSW Steel Co.',                    // 20. supplier
                'SUPP-042',                         // 21. supplier_no
                'CH-4401',                          // 22. challan_no
                '2026-05-20',                       // 23. gate_out_date
                '10',                               // 24. po_item
                'TR-88',                            // 25. transporter_code
                'Bhadrecha Roadways',               // 26. transporter_name
                'Steel Plates HR',                  // 27. material_name
                15.500,                             // 28. challan_qty
                15.480,                             // 29. final_wgt
                // Consignor
                'ABC Logistics Pvt Ltd',            // 17. consignor_name
                '9876543210',                       // 18. consignor_phone
                '27AAACA1234A1Z5',                  // 19. consignor_gstin
                'Plot 12, MIDC, Andheri, Mumbai',   // 20. consignor_address
                // Consignee
                'XYZ Enterprises Ltd',              // 21. consignee_name
                '9876543211',                       // 22. consignee_phone
                '07AAACX5678B1Z2',                  // 23. consignee_gstin
                'Sector 18, Okhla, New Delhi',      // 24. consignee_address
                // Vehicle & Driver
                'MH04AB1234',                       // 25. vehicle_number
                'Truck',                            // 26. vehicle_type
                'Ramesh Singh',                     // 27. driver_name
                '9876543212',                       // 28. driver_phone
                // Item 1
                'Steel Coils',                      // 29. item_name
                'Bundles',                          // 30. packaging_type
                10,                                 // 31. articles
                15.50,                              // 32. weight
                'Ton',                              // 33. unit
                2000.00,                            // 34. freight_per_mt
                5000.00,                            // 35. bilty_advance_amount
                'Handle with care',                 // 36. remark
                500.00,                             // 37. bilty_commission
                // Toll 1
                '2026-05-20 10:30',                 // 38. toll_time
                'Ghoti Toll Plaza',                 // 39. toll_location
                450.00,                             // 40. toll_oneway
                0.00,                               // 41. toll_return
                'FastTag Toll Plaza deduction',     // 42. toll_description
                'TXN-FAST-1001',                    // 43. toll_txn_id
                // Fuel 1
                '2026-05-20',                       // 44. fuel_date
                'Indian Oil Corporation',           // 45. fuel_company_name
                'IOCL Highway Pump Nashik',         // 46. fuel_pump_name
                60.00,                              // 47. fuel_quantity
                95.00,                              // 48. fuel_rate
                12500,                              // 49. fuel_km
                'credit',                           // 50. fuel_payment_type (credit / debit / cash)
                'Full tank diesel refill',          // 51. fuel_remark
                // AdBlue 1
                '2026-05-20',                       // 52. adblue_date
                'GoBlue Liquids',                   // 53. adblue_company_name
                15.00,                              // 54. adblue_quantity
                50.00,                              // 55. adblue_rate
                12500,                              // 56. adblue_km
                'cash',                             // 57. adblue_payment_type (credit / debit / cash)
                // Other Expense 1
                'Loading & Labor Charges',          // 58. other_expense_title
                400.00,                             // 59. other_expense_amount
                '2026-05-20',                       // 60. other_expense_date
                'Labor tip at Mumbai warehouse',    // 61. other_expense_remark
                // Advance 1
                '2026-05-20',                       // 62. advance_date
                'Indian Oil Corporation',           // 63. advance_fuel_company_name
                'IOCL Highway Pump Nashik',         // 64. advance_fuel_pump_name
                2000.00,                            // 65. advance_amount
                'cash',                             // 66. advance_payment_type (credit / debit / cash)
                'Driver trip start cash advance',   // 67. advance_remark
            ],

            // --- LR-2026-0001 : ROW 2 (Item 2, Toll 2, Fuel 2, AdBlue 2, Other 2, Advance 2) ---
            [
                'Bhadrecha Logistics',              // 1. company
                'Main Branch Mumbai',               // 2. branch
                'LR-2026-0001',                     // 3. lr_no (SAME LR)
                '2026-05-20',                       // 4. lr_date
                'topay',                            // 5. payment_type
                'Mumbai',                           // 6. from_city
                'Delhi',                            // 7. to_city
                'ORD-1001',                         // 8. order_number
                'DEL-2001',                         // 9. delivery_number
                'FORM-8890',                        // 10. form_number
                'INV-9901',                         // 11. invoice_number
                '2026-05-20',                       // 12. invoice_date
                'EWAY-8888001',                     // 13. eway_bill_no
                '2026-05-20',                       // 14. generate_date
                '2026-05-25',                       // 15. expiry_date
                'PO-7701',                          // 16. po_no
                // Builty - GRN / SAP Details (Same LR)
                '2026-05-20',                       // 17. posting_date
                'MATDOC-5001',                      // 18. mat_doc
                'GE-9001',                          // 19. gate_entry_no
                'JSW Steel Co.',                    // 20. supplier
                'SUPP-042',                         // 21. supplier_no
                'CH-4401',                          // 22. challan_no
                '2026-05-20',                       // 23. gate_out_date
                '20',                               // 24. po_item
                'TR-88',                            // 25. transporter_code
                'Bhadrecha Roadways',               // 26. transporter_name
                'Iron Rods 12mm',                   // 27. material_name
                10.000,                             // 28. challan_qty
                9.980,                              // 29. final_wgt
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
                // Item 2
                'Iron Rods 12mm',                   // 29. item_name
                'Loose',                            // 30. packaging_type
                50,                                 // 31. articles
                10.00,                              // 32. weight
                'Ton',                              // 33. unit
                2200.00,                            // 34. freight_per_mt
                '',                                 // 35. bilty_advance_amount
                '',                                 // 36. remark
                '',                                 // 37. bilty_commission
                // Toll 2
                '2026-05-21 04:15',                 // 38. toll_time
                'Pimpalgaon Toll Plaza',            // 39. toll_location
                380.00,                             // 40. toll_oneway
                0.00,                               // 41. toll_return
                'FastTag Toll deduction NH3',       // 42. toll_description
                'TXN-FAST-1002',                    // 43. toll_txn_id
                // Fuel 2
                '2026-05-21',                       // 44. fuel_date
                'Bharat Petroleum',                 // 45. fuel_company_name
                'BPCL Dhule Highway Oasis',         // 46. fuel_pump_name
                80.00,                              // 47. fuel_quantity
                94.50,                              // 48. fuel_rate
                12850,                              // 49. fuel_km
                'credit',                           // 50. fuel_payment_type
                'Mid-route diesel refuel',          // 51. fuel_remark
                // AdBlue 2
                '2026-05-21',                       // 52. adblue_date
                'CleanNox India',                   // 53. adblue_company_name
                10.00,                              // 54. adblue_quantity
                52.00,                              // 55. adblue_rate
                12850,                              // 56. adblue_km
                'cash',                             // 57. adblue_payment_type
                // Other Expense 2
                'Weighbridge / Kanta Charges',      // 58. other_expense_title
                150.00,                             // 59. other_expense_amount
                '2026-05-21',                       // 60. other_expense_date
                'Dharampeth Weighbridge slip',      // 61. other_expense_remark
                // Advance 2
                '2026-05-21',                       // 62. advance_date
                'Bharat Petroleum',                 // 63. advance_fuel_company_name
                'BPCL Dhule Highway Oasis',         // 64. advance_fuel_pump_name
                1500.00,                            // 65. advance_amount
                'cash',                             // 66. advance_payment_type
                'En-route driver food advance',     // 67. advance_remark
            ],

            // --- LR-2026-0001 : ROW 3 (Item 3, Toll 3, Fuel 3) ---
            [
                'Bhadrecha Logistics',              // 1. company
                'Main Branch Mumbai',               // 2. branch
                'LR-2026-0001',                     // 3. lr_no (SAME LR)
                '2026-05-20',                       // 4. lr_date
                'topay',                            // 5. payment_type
                'Mumbai',                           // 6. from_city
                'Delhi',                            // 7. to_city
                'ORD-1001',                         // 8. order_number
                'DEL-2001',                         // 9. delivery_number
                'FORM-8890',                        // 10. form_number
                'INV-9901',                         // 11. invoice_number
                '2026-05-20',                       // 12. invoice_date
                'EWAY-8888001',                     // 13. eway_bill_no
                '2026-05-20',                       // 14. generate_date
                '2026-05-25',                       // 15. expiry_date
                'PO-7701',                          // 16. po_no
                // Builty - GRN / SAP Details (Same LR)
                '2026-05-20',                       // 17. posting_date
                'MATDOC-5001',                      // 18. mat_doc
                'GE-9001',                          // 19. gate_entry_no
                'JSW Steel Co.',                    // 20. supplier
                'SUPP-042',                         // 21. supplier_no
                'CH-4401',                          // 22. challan_no
                '2026-05-20',                       // 23. gate_out_date
                '30',                               // 24. po_item
                'TR-88',                            // 25. transporter_code
                'Bhadrecha Roadways',               // 26. transporter_name
                'Steel Binding Wire',               // 27. material_name
                5.000,                              // 28. challan_qty
                4.990,                              // 29. final_wgt
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
                // Item 3
                'Steel Binding Wire',               // 29. item_name
                'Rolls',                            // 30. packaging_type
                25,                                 // 31. articles
                5.00,                               // 32. weight
                'Ton',                              // 33. unit
                2500.00,                            // 34. freight_per_mt
                '',                                 // 35. bilty_advance_amount
                '',                                 // 36. remark
                '',                                 // 37. bilty_commission
                // Toll 3
                '2026-05-21 18:30',                 // 38. toll_time
                'Indore Bypass Toll',               // 39. toll_location
                290.00,                             // 40. toll_oneway
                0.00,                               // 41. toll_return
                'FastTag Toll deduction NH52',      // 42. toll_description
                'TXN-FAST-1003',                    // 43. toll_txn_id
                // Fuel 3
                '2026-05-22',                       // 44. fuel_date
                'Hindustan Petroleum',              // 45. fuel_company_name
                'HPCL Agra Express Fuel Point',     // 46. fuel_pump_name
                50.00,                              // 47. fuel_quantity
                96.00,                              // 48. fuel_rate
                13400,                              // 49. fuel_km
                'credit',                           // 50. fuel_payment_type
                'Final top-up before Delhi',        // 51. fuel_remark
                // AdBlue 3
                '', '', '', '', '', '',
                // Other Expense 3
                '', '', '', '',
                // Advance 3
                '', '', '', '', '', ''
            ],

            // =========================================================================
            // SAMPLE TRIP 2 (LR-2026-0002): Single Row Example (Standard 1-item Trip)
            // =========================================================================
            [
                'Bhadrecha Logistics',              // 1. company
                'Ahmedabad Branch',                 // 2. branch
                'LR-2026-0002',                     // 3. lr_no
                '2026-05-21',                       // 4. lr_date
                'paid',                             // 5. payment_type
                'Ahmedabad',                        // 6. from_city
                'Jaipur',                           // 7. to_city
                'ORD-1002',                         // 8. order_number
                'DEL-2002',                         // 9. delivery_number
                'FORM-8891',                        // 10. form_number
                'INV-9902',                         // 11. invoice_number
                '2026-05-21',                       // 12. invoice_date
                'EWAY-8888002',                     // 13. eway_bill_no
                '2026-05-21',                       // 14. generate_date
                '2026-05-26',                       // 15. expiry_date
                'PO-7702',                          // 16. po_no
                // Builty - GRN / SAP Details
                '2026-05-21',                       // 17. posting_date
                'MATDOC-5002',                      // 18. mat_doc
                'GE-9002',                          // 19. gate_entry_no
                'Adani Cement Ltd',                 // 20. supplier
                'SUPP-088',                         // 21. supplier_no
                'CH-4402',                          // 22. challan_no
                '2026-05-21',                       // 23. gate_out_date
                '10',                               // 24. po_item
                'TR-99',                            // 25. transporter_code
                'Bhadrecha Logistics',              // 26. transporter_name
                'Ceramic Tiles Premium',            // 27. material_name
                22.000,                             // 28. challan_qty
                21.950,                             // 29. final_wgt
                'Gujarat Heavy Industries',         // 17. consignor_name
                '9123456780',                       // 18. consignor_phone
                '24AABCG1234M1Z8',                  // 19. consignor_gstin
                'GIDC Estate, Vatva, Ahmedabad',    // 20. consignor_address
                'Rajasthan Trading Corp',           // 21. consignee_name
                '9123456781',                       // 22. consignee_phone
                '08AABCR5678P1Z4',                  // 23. consignee_gstin
                'VKIA Industrial Area, Jaipur',     // 24. consignee_address
                'GJ01CD5678',                       // 25. vehicle_number
                'Trailer',                          // 26. vehicle_type
                'Suresh Kumar',                     // 27. driver_name
                '9123456782',                       // 28. driver_phone
                'Ceramic Tiles',                    // 29. item_name
                'Boxes',                            // 30. packaging_type
                500,                                // 31. articles
                22.00,                              // 32. weight
                'Ton',                              // 33. unit
                1800.00,                            // 34. freight_per_mt
                0.00,                               // 35. bilty_advance_amount
                'Deliver directly at warehouse',    // 36. remark
                350.00,                             // 37. bilty_commission
                '2026-05-21 14:00',                 // 38. toll_time
                'Shamlaji Toll Plaza',              // 39. toll_location
                520.00,                             // 40. toll_oneway
                0.00,                               // 41. toll_return
                'FastTag NH48 toll plaza',          // 42. toll_description
                'TXN-FAST-2001',                    // 43. toll_txn_id
                '2026-05-21',                       // 44. fuel_date
                'Reliance Petroleum',               // 45. fuel_company_name
                'Jio-bp Udaipur Highway Hub',       // 46. fuel_pump_name
                100.00,                             // 47. fuel_quantity
                93.50,                              // 48. fuel_rate
                8500,                               // 49. fuel_km
                'credit',                           // 50. fuel_payment_type
                'Full diesel refill',               // 51. fuel_remark
                '2026-05-21',                       // 52. adblue_date
                'GoBlue Liquids',                   // 53. adblue_company_name
                20.00,                              // 54. adblue_quantity
                48.00,                              // 55. adblue_rate
                8500,                               // 56. adblue_km
                'cash',                             // 57. adblue_payment_type
                'Toll bridge cess fee',             // 58. other_expense_title
                200.00,                             // 59. other_expense_amount
                '2026-05-21',                       // 60. other_expense_date
                'Local bridge entry receipt',       // 61. other_expense_remark
                '2026-05-21',                       // 62. advance_date
                'Reliance Petroleum',               // 63. advance_fuel_company_name
                'Jio-bp Udaipur Highway Hub',       // 64. advance_fuel_pump_name
                3000.00,                            // 65. advance_amount
                'cash',                             // 66. advance_payment_type
                'Driver food and night allowance'   // 67. advance_remark
            ]
        ];
    }

    public function headings(): array
    {
        return [
            // Company & Branch
            'company',
            'branch',

            // LR / Bilty Basic Details
            'lr_no',
            'lr_date',
            'payment_type',
            'from_city',
            'to_city',
            'order_number',
            'delivery_number',
            'form_number',
            'invoice_number',
            'invoice_date',
            'eway_bill_no',
            'generate_date',
            'expiry_date',
            'po_no',

            // Builty - GRN / SAP Details
            'posting_date',
            'mat_doc',
            'gate_entry_no',
            'supplier',
            'supplier_no',
            'challan_no',
            'gate_out_date',
            'po_item',
            'transporter_code',
            'transporter_name',
            'material_name',
            'challan_qty',
            'final_wgt',

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

            // Builty - Items / Goods (Supports Multi-Items per LR)
            'item_name',
            'packaging_type',
            'articles',
            'weight',
            'unit',
            'freight_per_mt',
            'bilty_advance_amount',
            'remark',
            'bilty_commission',

            // Trip - FastTag / Toll Details (Supports Multi-Tolls per LR)
            'toll_time',
            'toll_location',
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
            'fuel_km',
            'fuel_payment_type',
            'fuel_remark',

            // Trip - AdBlue Details (Supports Multi-AdBlue entries per LR)
            'adblue_date',
            'adblue_company_name',
            'adblue_quantity',
            'adblue_rate',
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
        ];
    }
}
