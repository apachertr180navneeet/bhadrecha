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
                'Bhadrecha Logistics',              // 1. company (Yellow)
                'Main Branch Mumbai',               // 2. branch (Yellow)
                'LR-2026-0001',                     // 3. lr_no
                '2026-05-20',                       // 4. lr_date
                'topay',                            // 5. payment_type (paid / topay / tobill)
                'Mumbai',                           // 6. from_city (Peach)
                'Delhi',                            // 7. to_city (Peach)
                'ORD-1001',                         // 8. order_number
                'DEL-2001',                         // 9. delivery_number
                'FORM-8890',                        // 10. form_number (Yellow)
                'INV-9901',                         // 11. invoice_number
                '2026-05-20',                       // 12. invoice_date
                'EWAY-8888001',                     // 13. eway_bill_no
                '2026-05-20',                       // 14. generate_date (Yellow)
                '2026-05-25',                       // 15. expiry_date (Yellow)
                'PO-7701',                          // 16. po_no
                // Consignor (Peach)
                'ABC Logistics Pvt Ltd',            // 17. consignor_name
                '9876543210',                       // 18. consignor_phone
                '27AAACA1234A1Z5',                  // 19. consignor_gstin
                'Plot 12, MIDC, Andheri, Mumbai',   // 20. consignor_address
                // Consignee (Peach)
                'XYZ Enterprises Ltd',              // 21. consignee_name
                '9876543211',                       // 22. consignee_phone
                '07AAACX5678B1Z2',                  // 23. consignee_gstin
                'Sector 18, Okhla, New Delhi',      // 24. consignee_address
                // Vehicle & Driver
                'MH04AB1234',                       // 25. vehicle_number
                'Truck',                            // 26. vehicle_type
                'Ramesh Singh',                     // 27. driver_name
                '9876543212',                       // 28. driver_phone
                // Item 1 (Blue)
                'Steel Coils',                      // 29. item_name
                'Bundles',                          // 30. packaging_type
                10,                                 // 31. articles
                15.50,                              // 32. weight (MT)
                'MT',                               // 33. unit
                2000.00,                            // 34. freight_per_mt
                5000.00,                            // 35. bilty_advance_amount
                75000.00,                           // 36. bilty_total_amount
                // Toll 1 (Green)
                '2026-05-20 10:30',                 // 37. toll_time
                'Ghoti Toll Plaza',                 // 38. toll_location
                450.00,                             // 39. toll_amount
                450.00,                             // 40. toll_oneway
                0.00,                               // 41. toll_return
                'FastTag Toll Plaza deduction',     // 42. toll_description
                'TXN-FAST-1001',                    // 43. toll_txn_id
                // Fuel 1 (Green)
                '2026-05-20',                       // 44. fuel_date
                'Indian Oil Corporation',           // 45. fuel_company_name
                'IOCL Highway Pump Nashik',         // 46. fuel_pump_name
                60.00,                              // 47. fuel_quantity (Liters)
                95.00,                              // 48. fuel_rate
                5700.00,                            // 49. fuel_amount
                12500,                              // 50. fuel_km
                'credit',                           // 51. fuel_payment_type (credit / debit / cash)
                'Full tank diesel refill',          // 52. fuel_remark
                // AdBlue 1 (Green)
                '2026-05-20',                       // 53. adblue_date
                'GoBlue Liquids',                   // 54. adblue_company_name
                15.00,                              // 55. adblue_quantity (Liters)
                50.00,                              // 56. adblue_rate
                750.00,                             // 57. adblue_amount
                12500,                              // 58. adblue_km
                'cash',                             // 59. adblue_payment_type (credit / debit / cash)
                // Other Expense 1 (Green)
                'Loading & Labor Charges',          // 60. other_expense_title
                400.00,                             // 61. other_expense_amount
                '2026-05-20',                       // 62. other_expense_date
                'Labor tip at Mumbai warehouse',    // 63. other_expense_remark
                // Advance 1 (Green)
                '2026-05-20',                       // 64. advance_date
                'Indian Oil Corporation',           // 65. advance_fuel_company_name
                'IOCL Highway Pump Nashik',         // 66. advance_fuel_pump_name
                2000.00,                            // 67. advance_amount
                'cash',                             // 68. advance_payment_type (credit / debit / cash)
                'Driver trip start cash advance',   // 69. advance_remark
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
                // Item 2 (Blue)
                'Iron Rods 12mm',                   // 29. item_name (Item 2)
                'Loose',                            // 30. packaging_type
                50,                                 // 31. articles
                10.00,                              // 32. weight (MT)
                'MT',                               // 33. unit
                2200.00,                            // 34. freight_per_mt
                '',                                 // 35. bilty_advance_amount (leave blank or same)
                '',                                 // 36. bilty_total_amount
                // Toll 2 (Green)
                '2026-05-21 04:15',                 // 37. toll_time
                'Pimpalgaon Toll Plaza',            // 38. toll_location
                380.00,                             // 39. toll_amount
                380.00,                             // 40. toll_oneway
                0.00,                               // 41. toll_return
                'FastTag Toll deduction NH3',       // 42. toll_description
                'TXN-FAST-1002',                    // 43. toll_txn_id
                // Fuel 2 (Green)
                '2026-05-21',                       // 44. fuel_date
                'Bharat Petroleum',                 // 45. fuel_company_name
                'BPCL Dhule Highway Oasis',         // 46. fuel_pump_name
                80.00,                              // 47. fuel_quantity
                94.50,                              // 48. fuel_rate
                7560.00,                            // 49. fuel_amount
                12850,                              // 50. fuel_km
                'credit',                           // 51. fuel_payment_type
                'Mid-route diesel refuel',          // 52. fuel_remark
                // AdBlue 2 (Green)
                '2026-05-21',                       // 53. adblue_date
                'CleanNox India',                   // 54. adblue_company_name
                10.00,                              // 55. adblue_quantity
                52.00,                              // 56. adblue_rate
                520.00,                             // 57. adblue_amount
                12850,                              // 58. adblue_km
                'cash',                             // 59. adblue_payment_type
                // Other Expense 2 (Green)
                'Weighbridge / Kanta Charges',      // 60. other_expense_title
                150.00,                             // 61. other_expense_amount
                '2026-05-21',                       // 62. other_expense_date
                'Dharampeth Weighbridge slip',      // 63. other_expense_remark
                // Advance 2 (Green)
                '2026-05-21',                       // 64. advance_date
                'Bharat Petroleum',                 // 65. advance_fuel_company_name
                'BPCL Dhule Highway Oasis',         // 66. advance_fuel_pump_name
                1500.00,                            // 67. advance_amount
                'cash',                             // 68. advance_payment_type
                'En-route driver food advance',     // 69. advance_remark
            ],

            // --- LR-2026-0001 : ROW 3 (Item 3, Toll 3, Fuel 3 - demonstrating extra items/expenses) ---
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
                // Item 3 (Blue)
                'Steel Binding Wire',               // 29. item_name (Item 3)
                'Rolls',                            // 30. packaging_type
                25,                                 // 31. articles
                5.00,                               // 32. weight (MT)
                'MT',                               // 33. unit
                2500.00,                            // 34. freight_per_mt
                '',                                 // 35. bilty_advance_amount
                '',                                 // 36. bilty_total_amount
                // Toll 3 (Green)
                '2026-05-21 18:30',                 // 37. toll_time
                'Indore Bypass Toll',               // 38. toll_location
                290.00,                             // 39. toll_amount
                290.00,                             // 40. toll_oneway
                0.00,                               // 41. toll_return
                'FastTag Toll deduction NH52',      // 42. toll_description
                'TXN-FAST-1003',                    // 43. toll_txn_id
                // Fuel 3 (Green)
                '2026-05-22',                       // 44. fuel_date
                'Hindustan Petroleum',              // 45. fuel_company_name
                'HPCL Agra Express Fuel Point',     // 46. fuel_pump_name
                50.00,                              // 47. fuel_quantity
                96.00,                              // 48. fuel_rate
                4800.00,                            // 49. fuel_amount
                13400,                              // 50. fuel_km
                'credit',                           // 51. fuel_payment_type
                'Final top-up before Delhi',        // 52. fuel_remark
                // AdBlue 3 (blank if none)
                '', '', '', '', '', '', '',
                // Other Expense 3 (blank if none)
                '', '', '', '',
                // Advance 3 (blank if none)
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
                'MT',                               // 33. unit
                1800.00,                            // 34. freight_per_mt
                0.00,                               // 35. bilty_advance_amount
                39600.00,                           // 36. bilty_total_amount
                '2026-05-21 14:00',                 // 37. toll_time
                'Shamlaji Toll Plaza',              // 38. toll_location
                520.00,                             // 39. toll_amount
                520.00,                             // 40. toll_oneway
                0.00,                               // 41. toll_return
                'FastTag NH48 toll plaza',          // 42. toll_description
                'TXN-FAST-2001',                    // 43. toll_txn_id
                '2026-05-21',                       // 44. fuel_date
                'Reliance Petroleum',               // 45. fuel_company_name
                'Jio-bp Udaipur Highway Hub',       // 46. fuel_pump_name
                100.00,                             // 47. fuel_quantity
                93.50,                              // 48. fuel_rate
                9350.00,                            // 49. fuel_amount
                8500,                               // 50. fuel_km
                'credit',                           // 51. fuel_payment_type
                'Full diesel refill',               // 52. fuel_remark
                '2026-05-21',                       // 53. adblue_date
                'GoBlue Liquids',                   // 54. adblue_company_name
                20.00,                              // 55. adblue_quantity
                48.00,                              // 56. adblue_rate
                960.00,                             // 57. adblue_amount
                8500,                               // 58. adblue_km
                'cash',                             // 59. adblue_payment_type
                'Toll bridge cess fee',             // 60. other_expense_title
                200.00,                             // 61. other_expense_amount
                '2026-05-21',                       // 62. other_expense_date
                'Local bridge entry receipt',       // 63. other_expense_remark
                '2026-05-21',                       // 64. advance_date
                'Reliance Petroleum',               // 65. advance_fuel_company_name
                'Jio-bp Udaipur Highway Hub',       // 66. advance_fuel_pump_name
                3000.00,                            // 67. advance_amount
                'cash',                             // 68. advance_payment_type
                'Driver food and night allowance'   // 69. advance_remark
            ]
        ];
    }

    public function headings(): array
    {
        return [
            // Company & Branch (Yellow)
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

            // Builty - Parties Details (Peach)
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

            // Builty - Items / Goods (Blue - Supports Multi-Items per LR)
            'item_name',
            'packaging_type',
            'articles',
            'weight',
            'unit',
            'freight_per_mt',
            'bilty_advance_amount',
            'bilty_total_amount',

            // Trip - FastTag / Toll Details (Green - Supports Multi-Tolls per LR)
            'toll_time',
            'toll_location',
            'toll_amount',
            'toll_oneway',
            'toll_return',
            'toll_description',
            'toll_txn_id',

            // Trip - Fuel Details (Green - Supports Multi-Fuel entries per LR)
            'fuel_date',
            'fuel_company_name',
            'fuel_pump_name',
            'fuel_quantity',
            'fuel_rate',
            'fuel_amount',
            'fuel_km',
            'fuel_payment_type',
            'fuel_remark',

            // Trip - AdBlue Details (Green - Supports Multi-AdBlue entries per LR)
            'adblue_date',
            'adblue_company_name',
            'adblue_quantity',
            'adblue_rate',
            'adblue_amount',
            'adblue_km',
            'adblue_payment_type',

            // Trip - Other Expense Details (Green - Supports Multi-Expenses per LR)
            'other_expense_title',
            'other_expense_amount',
            'other_expense_date',
            'other_expense_remark',

            // Trip - Advance Details (Green - Supports Multi-Advances per LR)
            'advance_date',
            'advance_fuel_company_name',
            'advance_fuel_pump_name',
            'advance_amount',
            'advance_payment_type',
            'advance_remark',
        ];
    }
}
