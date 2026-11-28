<?php

namespace Database\Seeders\Clients\Crystal;

use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Database\Seeder;

/**
 * Run once on Crystal's server, after deploying the templates that print
 * their written-in copy from each page's `copy` section.
 *
 *   php artisan db:seed --class='Database\Seeders\Clients\Crystal\MoveOverridesIntoPageCopy'
 *
 * Edits to that copy used to be stored as overrides, by the position of the
 * element on the published page. This gives every Crystal page its `copy`
 * section and moves each stored override into the field that now holds that
 * piece, so the site shows exactly what it showed before. An override whose
 * position no longer holds a piece of copy is left in place and reported.
 * Running it again changes nothing.
 */
class MoveOverridesIntoPageCopy extends Seeder
{
    /**
     * Each page's override positions, as the published page counted them,
     * and the field that now holds the piece at that position.
     */
    private const FIELDS = [
        'home' => [
            'text' => [15 => 'view_all_services', 24 => 'visit_the_salon', 25 => 'why_choose_us', 26 => 'were_dedicated_to_providing_exceptional', 27 => 'we_go_beyond_just_cleaning', 28 => 'experienced_and_professional_team', 29 => 'tailored_cleaning_solutions', 30 => 'reliable_and_flexible_scheduling', 31 => 'customer_satisfaction_guarantee', 43 => 'clean_with_us_in_4', 44 => 'consultation_quote', 45 => 'contact_us_to_discuss_your', 46 => 'tailored_cleaning_plan', 47 => 'once_weve_assessed_your_cleaning', 48 => 'professional_cleaning_service', 49 => 'our_trained_insured_cleaners_arrive', 50 => 'final_inspection_satisfaction', 51 => 'after_cleaning_well_ensure_everything', 55 => 'contact_us', 56 => 'were_open_to_talk_to', 57 => 'our_address', 58 => '56_northfield_street_worcester_worcestershire', 59 => 'our_email', 60 => 'info_crystalservicesltd_co_uk', 61 => 'support_crystalservicesltd_co_uk', 62 => 'call_us', 64 => 'get_in_touch'],
            'images' => [1 => 'airbnb_cleaning_between_guest_photo', 2 => 'professional_cleaner_at_work_photo', 3 => 'cleaner_wiping_down_a_photo', 4 => 'professional_cleaning_supplies_and_photo', 5 => 'deep_cleaning_in_progress_photo', 6 => 'office_cleaning_service_photo', 13 => 'crystal_services_cleaner_at_photo', 14 => 'crystal_services_team_cleaning_photo', 15 => 'window_and_glass_cleaning_photo'],
        ],
        'about' => [
            'text' => [3 => 'our_cleaning_business', 4 => 'crystal_clean_your_trusted_cleaning', 5 => 'our_mission', 6 => 'our_mission_is_simple_to', 7 => 'our_vision', 8 => 'from_small_residential_properties_to', 9 => 'our_salon_business', 10 => 'crystal_salon_beautiful_hair_same', 11 => 'hair_styled_with_care', 12 => 'crystal_isnt_only_about_spotless', 13 => 'products_attachments', 14 => 'we_also_sell_a_wide', 15 => 'visit_the_salon', 16 => 'why_choose_us', 17 => 'were_dedicated_to_providing_exceptional', 18 => 'we_go_beyond_just_cleaning', 19 => 'experienced_and_professional_team', 20 => 'tailored_cleaning_solutions', 21 => 'reliable_and_flexible_scheduling', 22 => 'customer_satisfaction_guarantee', 23 => 'testimonials', 24 => 'trusted_by_homes_and_businesses', 25 => 'hear_directly_from_the_individuals', 27 => 'weve_been_using_crystal_service', 29 => 'i_needed_an_end_of', 31 => 'the_deep_cleaning_service_was', 33 => 'as_an_airbnb_host_cleanliness', 34 => 'clean_with_us_in_4', 35 => 'consultation_quote', 36 => 'contact_us_to_discuss_your', 37 => 'tailored_cleaning_plan', 38 => 'once_weve_assessed_your_cleaning', 39 => 'professional_cleaning_service', 40 => 'our_trained_insured_cleaners_arrive', 41 => 'final_inspection_satisfaction', 42 => 'after_cleaning_well_ensure_everything', 43 => 'let_us_handle_the_dirty', 44 => 'book_us_today_for_a', 45 => 'book_a_cleaning_session'],
            'images' => [0 => 'airbnb_cleaning_between_guest_photo', 1 => 'professional_cleaner_at_work_photo', 2 => 'cleaner_wiping_down_a_photo', 3 => 'professional_cleaning_supplies_and_photo', 4 => 'deep_cleaning_in_progress_photo', 5 => 'office_cleaning_service_photo', 6 => 'upholstery_and_furniture_cleaning_photo', 7 => 'crystal_salon_hairdressing_photo', 8 => 'crystal_services_cleaner_at_photo', 9 => 'crystal_services_team_cleaning_photo', 10 => 'window_and_glass_cleaning_photo'],
        ],
        'services' => [
            'text' => [20 => 'salon_hairdressing', 21 => 'now_offering_professional_hairdressing', 22 => 'alongside_our_cleaning_services_we', 23 => 'braiding', 24 => 'from_box_braids_and_cornrows', 25 => 'sew_in', 26 => 'our_sew_in_weaves_are', 27 => 'dreadlocks', 28 => 'starting_your_loc_journey_or', 29 => 're_locking', 30 => 'regular_retwisting_keeps_your_locs', 31 => 'relaxing', 32 => 'our_relaxer_treatments_are_applied', 33 => 'washing_setting', 34 => 'a_thorough_cleanse_deep_condition', 35 => 'natural_weaving', 36 => 'our_natural_weaving_blends_quality', 37 => 'explore_the_salon', 38 => 'let_us_handle_the_dirty', 39 => 'book_us_today_for_a', 40 => 'book_a_cleaning_session', 41 => 'frequently_asked_questions', 42 => 'at_crystal_services_limited_we'],
            'images' => [0 => 'commercial_cleaning_service_photo', 1 => 'upholstery_cleaning_service_photo', 2 => 'end_of_tenancy_cleaning_photo', 3 => 'airbnb_cleaning_service_photo', 4 => 'deep_cleaning_service_photo', 5 => 'after_builders_cleaning_service_photo', 6 => 'braiding_photo', 7 => 'sew_in_photo', 8 => 'dreadlocks_photo', 9 => 're_locking_photo', 10 => 'relaxing_photo', 11 => 'washing_setting_photo', 12 => 'natural_weaving_photo'],
        ],
        'salon' => [
            'text' => [3 => 'salon_hairdressing', 27 => 'hair_products_attachments', 28 => 'shop_hair_products_attachments', 29 => 'we_also_sell_a_wide', 30 => 'follow_on_tiktok'],
            'images' => [0 => 'braiding_photo', 1 => 'sew_in_photo', 2 => 'dreadlocks_photo', 3 => 're_locking_photo', 4 => 'relaxing_photo', 5 => 'washing_setting_photo', 6 => 'natural_weaving_photo'],
        ],
        'contact' => [
            'text' => [4 => 'our_address', 5 => '56_northfield_street_worcester_worcestershire', 6 => 'our_email', 7 => 'info_crystalservicesltd_co_uk_support', 8 => 'call_us', 10 => 'get_in_touch', 11 => 'frequently_asked_questions', 12 => 'at_crystal_services_limited_we'],
            'images' => [],
        ],
    ];

    public function run(): void
    {
        foreach (Page::where('template', 'crystal')->get() as $page) {
            $copy = $page->sections()->firstOrCreate(
                ['key' => 'copy'],
                ['type' => 'text', 'title' => 'Page copy', 'data' => [], 'order' => $page->sections()->max('order') + 1, 'is_visible' => true],
            );
            $this->move($page, $copy);
        }
    }

    private function move(Page $page, PageSection $copy): void
    {
        $content = json_decode($page->content ?: '{}', true);
        if (! is_array($content)) {
            return;
        }
        $overrides = $content['template_overrides'] ?? $content['crystal_overrides'] ?? null;
        if (! is_array($overrides) || $overrides === []) {
            return;
        }

        $fields = self::FIELDS[$page->slug] ?? ['text' => [], 'images' => []];
        $data = $copy->data ?? [];
        $left = ['text' => [], 'images' => []];
        $moved = 0;

        foreach ($overrides['text'] ?? [] as $index => $value) {
            $key = $fields['text'][(int) $index] ?? null;
            // The published page ignored an override of a single character.
            if ($key === null || mb_strlen(trim(preg_replace('/\s+/u', ' ', (string) $value))) <= 1) {
                $left['text'][$index] = $value;
                continue;
            }
            $data[$key] = (string) $value;
            $moved++;
        }
        foreach ($overrides['images'] ?? [] as $index => $replacement) {
            $key = $fields['images'][(int) $index] ?? null;
            if ($key === null || empty($replacement['url'])) {
                $left['images'][$index] = $replacement;
                continue;
            }
            $data[$key] = $replacement['url'];
            if (! empty($replacement['alt'])) {
                $data[$key.'_alt'] = $replacement['alt'];
            }
            $moved++;
        }

        $copy->update(['data' => $data]);
        unset($content['template_overrides'], $content['crystal_overrides']);
        $left = array_filter($left);
        if ($left !== []) {
            $content['unmoved_overrides'] = $left;
        }
        $page->update(['content' => $content === [] ? '' : json_encode($content)]);

        $this->command?->info("{$page->title}: moved {$moved} edit(s) into the page copy.");
        if ($left !== []) {
            $this->command?->warn("{$page->title}: ".count($left['text'] ?? []).' text and '.count($left['images'] ?? [])
                .' photo override(s) had no matching piece; kept under unmoved_overrides in the page content for review.');
        }
    }
}
