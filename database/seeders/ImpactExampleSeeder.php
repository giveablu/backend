<?php

namespace Database\Seeders;

use App\Models\ImpactExample;
use Illuminate\Database\Seeder;

class ImpactExampleSeeder extends Seeder
{
    public function run(): void
    {
        $examples = [
            // Armenia (AMD)
            [
                'country_iso' => 'AM',
                'category' => 'general',
                'min_usd' => 5,
                'max_usd' => 10,
                'icon' => 'bread',
                'headline' => 'Fresh bread for a week',
                'description' => 'Covers several loaves of lavash and staple pantry items for a household.',
                'metadata' => [
                    'local_currency' => 'AMD',
                    'local_amount' => 4000,
                    'local_note' => 'Approximate cost of daily bread and basics',
                ],
            ],
            [
                'country_iso' => 'AM',
                'category' => 'general',
                'min_usd' => 10,
                'max_usd' => 20,
                'icon' => 'groceries',
                'headline' => 'Groceries for a small family',
                'description' => 'Stocks essentials like vegetables, grains, and dairy for several days.',
                'metadata' => [
                    'local_currency' => 'AMD',
                    'local_amount' => 8000,
                    'local_note' => 'Food staples for a small family',
                ],
            ],
            [
                'country_iso' => 'AM',
                'category' => 'general',
                'min_usd' => 20,
                'max_usd' => 35,
                'icon' => 'utilities',
                'headline' => 'Keeps the lights on',
                'description' => 'Pays a month of utility bills (electricity and gas) for a small apartment.',
                'metadata' => [
                    'local_currency' => 'AMD',
                    'local_amount' => 14000,
                    'local_note' => 'Average utility bill (electric + gas)',
                ],
            ],
            [
                'country_iso' => 'AM',
                'category' => 'general',
                'min_usd' => 35,
                'max_usd' => 50,
                'icon' => 'medical',
                'headline' => 'Covers critical medicine',
                'description' => 'Helps a recipient purchase prescribed treatments or medical supplies.',
                'metadata' => [
                    'local_currency' => 'AMD',
                    'local_amount' => 20000,
                    'local_note' => 'Specialty medication purchase',
                ],
            ],

            // Kenya (KES)
            [
                'country_iso' => 'KE',
                'category' => 'general',
                'min_usd' => 5,
                'max_usd' => 10,
                'icon' => 'water',
                'headline' => 'Safe water for a week',
                'description' => 'Refills multiple 20L jerry cans of clean drinking water.',
                'metadata' => [
                    'local_currency' => 'KES',
                    'local_amount' => 800,
                    'local_note' => 'Water vendor rates for multiple jerry cans',
                ],
            ],
            [
                'country_iso' => 'KE',
                'category' => 'general',
                'min_usd' => 10,
                'max_usd' => 20,
                'icon' => 'groceries',
                'headline' => 'Family food basket',
                'description' => 'Buys maize flour, beans, vegetables, and cooking oil for a family of four.',
                'metadata' => [
                    'local_currency' => 'KES',
                    'local_amount' => 2000,
                    'local_note' => 'Groceries for a family of four',
                ],
            ],
            [
                'country_iso' => 'KE',
                'category' => 'education',
                'min_usd' => 20,
                'max_usd' => 35,
                'icon' => 'education',
                'headline' => 'School essentials paid',
                'description' => 'Handles a term of school fees, uniforms, and supplies for one child.',
                'metadata' => [
                    'local_currency' => 'KES',
                    'local_amount' => 3500,
                    'local_note' => 'Primary school term fees and supplies',
                ],
            ],
            [
                'country_iso' => 'KE',
                'category' => 'general',
                'min_usd' => 35,
                'max_usd' => 50,
                'icon' => 'rent',
                'headline' => 'One month of rent',
                'description' => 'Covers rent for a one-room home in a peri-urban area.',
                'metadata' => [
                    'local_currency' => 'KES',
                    'local_amount' => 5400,
                    'local_note' => 'Monthly rent for a one-room house',
                ],
            ],

            // Philippines (PHP)
            [
                'country_iso' => 'PH',
                'category' => 'general',
                'min_usd' => 5,
                'max_usd' => 10,
                'icon' => 'meals',
                'headline' => 'Home-cooked meals',
                'description' => 'Funds rice, vegetables, and canned goods for a family for several days.',
                'metadata' => [
                    'local_currency' => 'PHP',
                    'local_amount' => 500,
                    'local_note' => 'Groceries for several home-cooked meals',
                ],
            ],
            [
                'country_iso' => 'PH',
                'category' => 'general',
                'min_usd' => 10,
                'max_usd' => 20,
                'icon' => 'transport',
                'headline' => 'Transportation and essentials',
                'description' => 'Pays for a week of jeepney fares plus household supplies.',
                'metadata' => [
                    'local_currency' => 'PHP',
                    'local_amount' => 1000,
                    'local_note' => 'Daily jeepney fares plus household items',
                ],
            ],
            [
                'country_iso' => 'PH',
                'category' => 'general',
                'min_usd' => 20,
                'max_usd' => 35,
                'icon' => 'medical',
                'headline' => 'Clinic visit and medication',
                'description' => 'Covers a doctor consultation and prescribed medicines.',
                'metadata' => [
                    'local_currency' => 'PHP',
                    'local_amount' => 1800,
                    'local_note' => 'Doctor visit and prescribed medicine',
                ],
            ],
            [
                'country_iso' => 'PH',
                'category' => 'general',
                'min_usd' => 35,
                'max_usd' => 50,
                'icon' => 'utilities',
                'headline' => 'Keeps the household running',
                'description' => 'Pays for electricity, water, and internet for a month in provincial areas.',
                'metadata' => [
                    'local_currency' => 'PHP',
                    'local_amount' => 2800,
                    'local_note' => 'Monthly utilities in provincial areas',
                ],
            ],

            // Venezuela (VES)
            [
                'country_iso' => 'VE',
                'category' => 'general',
                'min_usd' => 5,
                'max_usd' => 10,
                'icon' => 'groceries',
                'headline' => 'Staple foods restocked',
                'description' => 'Provides harina PAN, beans, rice, and cooking oil for a family.',
                'metadata' => [
                    'local_currency' => 'VES',
                    'local_amount' => 280,
                    'local_note' => 'Staple groceries indexed to USD rates',
                ],
            ],
            [
                'country_iso' => 'VE',
                'category' => 'general',
                'min_usd' => 10,
                'max_usd' => 20,
                'icon' => 'hygiene',
                'headline' => 'Hygiene essentials',
                'description' => 'Buys soap, shampoo, and feminine care products.',
                'metadata' => [
                    'local_currency' => 'VES',
                    'local_amount' => 550,
                    'local_note' => 'Personal hygiene bundle',
                ],
            ],
            [
                'country_iso' => 'VE',
                'category' => 'general',
                'min_usd' => 20,
                'max_usd' => 35,
                'icon' => 'medical',
                'headline' => 'Healthcare support',
                'description' => 'Pays for a clinic visit plus chronic medication for a month.',
                'metadata' => [
                    'local_currency' => 'VES',
                    'local_amount' => 900,
                    'local_note' => 'Clinic visit plus chronic meds',
                ],
            ],
            [
                'country_iso' => 'VE',
                'category' => 'general',
                'min_usd' => 35,
                'max_usd' => 50,
                'icon' => 'rent',
                'headline' => 'Stable housing',
                'description' => 'Helps cover rent or a mortgage installment for a month.',
                'metadata' => [
                    'local_currency' => 'VES',
                    'local_amount' => 1400,
                    'local_note' => 'Monthly housing costs',
                ],
            ],

            // India (IN)
            [
                'country_iso' => 'IN',
                'category' => 'general',
                'min_usd' => 5,
                'max_usd' => 10,
                'icon' => 'meals',
                'headline' => 'Nutritious meals',
                'description' => 'Provides grains, lentils, and vegetables for a family for several days.',
                'metadata' => [
                    'local_currency' => 'INR',
                    'local_amount' => 600,
                    'local_note' => 'Groceries for several days',
                ],
            ],
            [
                'country_iso' => 'IN',
                'category' => 'education',
                'min_usd' => 10,
                'max_usd' => 20,
                'icon' => 'education',
                'headline' => 'School kit',
                'description' => 'Covers notebooks, uniforms, and fees for one student.',
                'metadata' => [
                    'local_currency' => 'INR',
                    'local_amount' => 1200,
                    'local_note' => 'School fees, uniforms, and notebooks',
                ],
            ],
            [
                'country_iso' => 'IN',
                'category' => 'health',
                'min_usd' => 20,
                'max_usd' => 35,
                'icon' => 'medical',
                'headline' => 'Health check and medicine',
                'description' => 'Pays for a doctor visit and a month of chronic medication.',
                'metadata' => [
                    'local_currency' => 'INR',
                    'local_amount' => 2400,
                    'local_note' => 'Doctor visit plus medicines',
                ],
            ],
            [
                'country_iso' => 'IN',
                'category' => 'general',
                'min_usd' => 35,
                'max_usd' => 50,
                'icon' => 'utilities',
                'headline' => 'Household stability',
                'description' => 'Covers rent or utility bills for a month in a tier-2 city.',
                'metadata' => [
                    'local_currency' => 'INR',
                    'local_amount' => 4000,
                    'local_note' => 'Monthly rent or utility bills',
                ],
            ],

            // Nigeria (NG)
            [
                'country_iso' => 'NG',
                'category' => 'general',
                'min_usd' => 5,
                'max_usd' => 10,
                'icon' => 'groceries',
                'headline' => 'Staple foods',
                'description' => 'Buys garri, rice, palm oil, and vegetables for a family.',
                'metadata' => [
                    'local_currency' => 'NGN',
                    'local_amount' => 8000,
                    'local_note' => 'Family staples for a week',
                ],
            ],
            [
                'country_iso' => 'NG',
                'category' => 'education',
                'min_usd' => 10,
                'max_usd' => 20,
                'icon' => 'education',
                'headline' => 'School levies handled',
                'description' => 'Pays PTA fees and buys textbooks for a public school student.',
                'metadata' => [
                    'local_currency' => 'NGN',
                    'local_amount' => 15000,
                    'local_note' => 'School levies and books',
                ],
            ],
            [
                'country_iso' => 'NG',
                'category' => 'health',
                'min_usd' => 20,
                'max_usd' => 35,
                'icon' => 'medical',
                'headline' => 'Clinic visit and drugs',
                'description' => 'Covers consultations and malaria treatment for one person.',
                'metadata' => [
                    'local_currency' => 'NGN',
                    'local_amount' => 25000,
                    'local_note' => 'Clinic visit and malaria treatment',
                ],
            ],
            [
                'country_iso' => 'NG',
                'category' => 'general',
                'min_usd' => 35,
                'max_usd' => 50,
                'icon' => 'business',
                'headline' => 'Boosts a micro-business',
                'description' => 'Provides stock for a small trader or supports transport costs.',
                'metadata' => [
                    'local_currency' => 'NGN',
                    'local_amount' => 38000,
                    'local_note' => 'Inventory for a micro-business',
                ],
            ],

            // Global fallback
            [
                'country_iso' => 'GL',
                'category' => 'general',
                'min_usd' => 5,
                'max_usd' => 15,
                'icon' => 'meals',
                'headline' => 'Covers a week of meals',
                'description' => 'Provides staple food and fresh produce for a household in many regions.',
                'metadata' => [
                    'local_currency' => 'USD',
                    'local_amount' => 12,
                    'local_note' => 'Reference basket converted globally',
                ],
            ],
            [
                'country_iso' => 'GL',
                'category' => 'general',
                'min_usd' => 15,
                'max_usd' => 30,
                'icon' => 'utilities',
                'headline' => 'Keeps essentials paid',
                'description' => 'Helps cover critical bills like utilities, transport, or school costs.',
                'metadata' => [
                    'local_currency' => 'USD',
                    'local_amount' => 25,
                    'local_note' => 'Represents common expense totals worldwide',
                ],
            ],
            [
                'country_iso' => 'GL',
                'category' => 'general',
                'min_usd' => 30,
                'max_usd' => 50,
                'icon' => 'medical',
                'headline' => 'Funds medical or housing support',
                'description' => 'Covers urgent health needs or provides stable shelter in many contexts.',
                'metadata' => [
                    'local_currency' => 'USD',
                    'local_amount' => 45,
                    'local_note' => 'Anchored to USD for fallback messaging',
                ],
            ],
        ];

        foreach ($examples as $example) {
            ImpactExample::updateOrCreate(
                [
                    'country_iso' => $example['country_iso'],
                    'category' => $example['category'],
                    'min_usd' => $example['min_usd'],
                    'max_usd' => $example['max_usd'],
                ],
                $example
            );
        }
    }
}
