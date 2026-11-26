<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            // Andhra Pradesh
            ['name' => 'Visakhapatnam', 'state' => 'Andhra Pradesh'],
            ['name' => 'Vijayawada', 'state' => 'Andhra Pradesh'],
            ['name' => 'Guntur', 'state' => 'Andhra Pradesh'],
            ['name' => 'Nellore', 'state' => 'Andhra Pradesh'],

            // Arunachal Pradesh
            ['name' => 'Itanagar', 'state' => 'Arunachal Pradesh'],

            // Assam
            ['name' => 'Guwahati', 'state' => 'Assam'],
            ['name' => 'Silchar', 'state' => 'Assam'],

            // Bihar
            ['name' => 'Patna', 'state' => 'Bihar'],
            ['name' => 'Gaya', 'state' => 'Bihar'],
            ['name' => 'Bhagalpur', 'state' => 'Bihar'],
            ['name' => 'Muzaffarpur', 'state' => 'Bihar'],

            // Chhattisgarh
            ['name' => 'Raipur', 'state' => 'Chhattisgarh'],
            ['name' => 'Bilaspur', 'state' => 'Chhattisgarh'],

            // Goa
            ['name' => 'Panaji', 'state' => 'Goa'],
            ['name' => 'Margao', 'state' => 'Goa'],

            // Gujarat
            ['name' => 'Ahmedabad', 'state' => 'Gujarat'],
            ['name' => 'Surat', 'state' => 'Gujarat'],
            ['name' => 'Vadodara', 'state' => 'Gujarat'],
            ['name' => 'Rajkot', 'state' => 'Gujarat'],
            ['name' => 'Bhavnagar', 'state' => 'Gujarat'],
            ['name' => 'Jamnagar', 'state' => 'Gujarat'],
            ['name' => 'Gandhinagar', 'state' => 'Gujarat'],

            // Haryana
            ['name' => 'Faridabad', 'state' => 'Haryana'],
            ['name' => 'Gurgaon', 'state' => 'Haryana'],
            ['name' => 'Panipat', 'state' => 'Haryana'],
            ['name' => 'Ambala', 'state' => 'Haryana'],

            // Himachal Pradesh
            ['name' => 'Shimla', 'state' => 'Himachal Pradesh'],
            ['name' => 'Manali', 'state' => 'Himachal Pradesh'],

            // Jharkhand
            ['name' => 'Ranchi', 'state' => 'Jharkhand'],
            ['name' => 'Jamshedpur', 'state' => 'Jharkhand'],
            ['name' => 'Dhanbad', 'state' => 'Jharkhand'],

            // Karnataka
            ['name' => 'Bengaluru', 'state' => 'Karnataka'],
            ['name' => 'Mysuru', 'state' => 'Karnataka'],
            ['name' => 'Mangaluru', 'state' => 'Karnataka'],
            ['name' => 'Hubbali', 'state' => 'Karnataka'],
            ['name' => 'Belagavi', 'state' => 'Karnataka'],

            // Kerala
            ['name' => 'Thiruvananthapuram', 'state' => 'Kerala'],
            ['name' => 'Kochi', 'state' => 'Kerala'],
            ['name' => 'Kozhikode', 'state' => 'Kerala'],
            ['name' => 'Thrissur', 'state' => 'Kerala'],

            // Madhya Pradesh
            ['name' => 'Bhopal', 'state' => 'Madhya Pradesh'],
            ['name' => 'Indore', 'state' => 'Madhya Pradesh'],
            ['name' => 'Gwalior', 'state' => 'Madhya Pradesh'],
            ['name' => 'Jabalpur', 'state' => 'Madhya Pradesh'],
            ['name' => 'Ujjain', 'state' => 'Madhya Pradesh'],

            // Maharashtra
            ['name' => 'Mumbai', 'state' => 'Maharashtra'],
            ['name' => 'Pune', 'state' => 'Maharashtra'],
            ['name' => 'Nagpur', 'state' => 'Maharashtra'],
            ['name' => 'Thane', 'state' => 'Maharashtra'],
            ['name' => 'Nashik', 'state' => 'Maharashtra'],
            ['name' => 'Aurangabad', 'state' => 'Maharashtra'],
            ['name' => 'Solapur', 'state' => 'Maharashtra'],

            // Manipur
            ['name' => 'Imphal', 'state' => 'Manipur'],

            // Meghalaya
            ['name' => 'Shillong', 'state' => 'Meghalaya'],

            // Mizoram
            ['name' => 'Aizawl', 'state' => 'Mizoram'],

            // Nagaland
            ['name' => 'Kohima', 'state' => 'Nagaland'],

            // Odisha
            ['name' => 'Bhubaneswar', 'state' => 'Odisha'],
            ['name' => 'Cuttack', 'state' => 'Odisha'],
            ['name' => 'Rourkela', 'state' => 'Odisha'],

            // Punjab
            ['name' => 'Ludhiana', 'state' => 'Punjab'],
            ['name' => 'Amritsar', 'state' => 'Punjab'],
            ['name' => 'Jalandhar', 'state' => 'Punjab'],
            ['name' => 'Patiala', 'state' => 'Punjab'],

            // Rajasthan
            ['name' => 'Jaipur', 'state' => 'Rajasthan'],
            ['name' => 'Jodhpur', 'state' => 'Rajasthan'],
            ['name' => 'Udaipur', 'state' => 'Rajasthan'],
            ['name' => 'Kota', 'state' => 'Rajasthan'],
            ['name' => 'Bikaner', 'state' => 'Rajasthan'],
            ['name' => 'Ajmer', 'state' => 'Rajasthan'],

            // Sikkim
            ['name' => 'Gangtok', 'state' => 'Sikkim'],

            // Tamil Nadu
            ['name' => 'Chennai', 'state' => 'Tamil Nadu'],
            ['name' => 'Coimbatore', 'state' => 'Tamil Nadu'],
            ['name' => 'Madurai', 'state' => 'Tamil Nadu'],
            ['name' => 'Salem', 'state' => 'Tamil Nadu'],
            ['name' => 'Tiruchirappali', 'state' => 'Tamil Nadu'],

            // Telangana
            ['name' => 'Hyderabad', 'state' => 'Telangana'],
            ['name' => 'Warangal', 'state' => 'Telangana'],
            ['name' => 'Nizamabad', 'state' => 'Telangana'],

            // Tripura
            ['name' => 'Agartala', 'state' => 'Tripura'],

            // Uttar Pradesh
            ['name' => 'Lucknow', 'state' => 'Uttar Pradesh'],
            ['name' => 'Kanpur', 'state' => 'Uttar Pradesh'],
            ['name' => 'Agra', 'state' => 'Uttar Pradesh'],
            ['name' => 'Varanasi', 'state' => 'Uttar Pradesh'],
            ['name' => 'Prayagraj', 'state' => 'Uttar Pradesh'],
            ['name' => 'Ghaziabad', 'state' => 'Uttar Pradesh'],
            ['name' => 'Noida', 'state' => 'Uttar Pradesh'],
            ['name' => 'Meerut', 'state' => 'Uttar Pradesh'],

            // Uttarakhand
            ['name' => 'Dehradun', 'state' => 'Uttarakhand'],
            ['name' => 'Haridwar', 'state' => 'Uttarakhand'],
            ['name' => 'Nainital', 'state' => 'Uttarakhand'],

            // West Bengal
            ['name' => 'Kolkata', 'state' => 'West Bengal'],
            ['name' => 'Siliguri', 'state' => 'West Bengal'],
            ['name' => 'Durgapur', 'state' => 'West Bengal'],
            ['name' => 'Howrah', 'state' => 'West Bengal'],

            // Union Territories
            ['name' => 'Delhi', 'state' => 'Delhi'],
            ['name' => 'New Delhi', 'state' => 'Delhi'],
            ['name' => 'Chandigarh', 'state' => 'Chandigarh'],
            ['name' => 'Port Blair', 'state' => 'Andaman and Nicobar Islands'],
            ['name' => 'Puducherry', 'state' => 'Puducherry'],
            ['name' => 'Kavarati', 'state' => 'Lakshadweep'],
        ];

        $inserted = 0;
        foreach ($cities as $city) {
            City::firstOrCreate(
                ['name' => $city['name'], 'state' => $city['state']],
                ['status' => 'active']
            );
            $inserted++;
        }

        $this->command->info('Cities seeded successfully! Processed: ' . $inserted);
    }
}
