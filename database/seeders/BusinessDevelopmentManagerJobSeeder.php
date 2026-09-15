<?php

namespace Database\Seeders;

use App\Models\JobPost;
use Illuminate\Database\Seeder;

class BusinessDevelopmentManagerJobSeeder extends Seeder
{
    public function run(): void
    {
        JobPost::updateOrCreate(['slug' => 'business-development-manager'], [
            'title' => 'Business Development Manager',
            'location' => 'Worcestershire, UK',
            'job_type' => 'Full-time',
            'hours' => '37.5 hours per week',
            'salary' => '£56,000 per annum',
            'employment_type' => 'Permanent',
            'application_email' => 'crystalservicesltd@outlook.com',
            'status' => 'draft',
            'summary' => 'Lead the development of new business opportunities across our professional cleaning and salon operations.',
            'description' => "Crystal Services Limited is a growing multi-service business operating across professional cleaning and hairdressing services.\n\nThe Business Development Manager will develop and implement growth strategies, identify new markets and generate new client relationships. Working closely with the Director, the successful candidate will grow the client portfolio, increase recurring contracts and strengthen existing customer relationships.",
            'responsibilities' => [
                'Develop business development strategies aligned with the company’s growth objectives.',
                'Identify opportunities in residential, commercial, property management, short-let and care-sector markets.',
                'Research prospective clients, market trends and competitor activity.',
                'Generate leads through networking, direct marketing, referrals and online channels.',
                'Build relationships with prospective and existing clients.',
                'Prepare and follow up quotations, proposals and commercial opportunities.',
                'Negotiate commercial terms within company guidelines.',
                'Increase recurring cleaning contracts and customer retention.',
                'Maintain accurate client, lead and opportunity records.',
                'Report to the Director on leads, sales activity, contracts and performance.',
                'Attend networking events, meetings and client appointments where required.',
                'Contribute to the company’s five-year expansion strategy.',
            ],
            'essential' => [
                'Experience in business development, sales, account management or a similar commercial role.',
                'Evidence of identifying and converting new business opportunities.',
                'Strong communication, negotiation and relationship-management skills.',
                'Commercial awareness and knowledge of sales and business development principles.',
                'Strong organisation, time management and independent working skills.',
                'Good IT and administrative skills, including maintaining client and sales records.',
                'Ability to analyse market information and identify commercially viable opportunities.',
            ],
            'desirable' => [
                'Experience in cleaning, property services, facilities management, hospitality or personal services.',
                'Experience developing B2B and recurring service contracts.',
                'Knowledge of local and regional business markets.',
            ],
            'benefits' => [
                'Permanent full-time position.',
                'A key role in the growth of an expanding business.',
                'Responsibility for developing new markets and commercial relationships.',
                'Work across the company’s cleaning and salon divisions.',
                'Supportive and entrepreneurial working environment.',
                'Professional development and progression opportunities.',
            ],
        ]);
    }
}
