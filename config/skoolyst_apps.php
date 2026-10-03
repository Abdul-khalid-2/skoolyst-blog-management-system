<?php
/**
 * Skoolyst app registry — powers the "Other Skoolyst Apps" cards shown just
 * above the footer.
 *
 * Every Skoolyst app keeps its own copy of this list; there is no runtime
 * dependency between apps. When an app is added or a domain changes, update
 * this list in each app. Set 'active' => false to hide an app everywhere
 * without deleting its entry.
 *
 * 'current' is the key of THIS app (left out of the cards). It's fixed
 * rather than detected from the host so it also works on localhost.
 * 'icons' is 'fontawesome' when the layout already loads Font Awesome 6,
 * otherwise 'initials' (shows the app's first letter instead).
 */

return [
    'current' => 'blogs',    // skoolyst | blogs | mcqs | stores | teachers | ads | docs
    'icons'   => 'initials', // fontawesome | initials
    'apps' => [
        [
            'key' => 'skoolyst', 'name' => 'Skoolyst', 'url' => 'https://skoolyst.com', 'icon' => 'fa-solid fa-school',
            'description' => 'Find and compare schools near you, with reviews, fees and admission details.',
            'active' => true,
        ],
        [
            'key' => 'blogs', 'name' => 'Blogs', 'url' => 'https://blogs.skoolyst.com', 'icon' => 'fa-solid fa-pen-nib',
            'description' => 'Education articles and practical advice for parents, teachers and students.',
            'active' => true,
        ],
        [
            'key' => 'mcqs', 'name' => 'MCQs', 'url' => 'https://mcqs.skoolyst.com', 'icon' => 'fa-solid fa-list-check',
            'description' => 'Practice multiple-choice questions to prepare for tests and exams.',
            'active' => true,
        ],
        [
            'key' => 'stores', 'name' => 'Stores', 'url' => 'https://stores.skoolyst.com', 'icon' => 'fa-solid fa-store',
            'description' => 'Shop for books, uniforms, stationery and other school essentials.',
            'active' => false, // not live yet — flip to true once the domain resolves
        ],
        [
            'key' => 'teachers', 'name' => 'Teachers', 'url' => 'https://teachers.skoolyst.com', 'icon' => 'fa-solid fa-chalkboard-user',
            'description' => 'Build and share a professional teaching portfolio in minutes.',
            'active' => true,
        ],
        [
            'key' => 'ads', 'name' => 'Ads', 'url' => 'https://ads.skoolyst.com', 'icon' => 'fa-solid fa-bullhorn',
            'description' => 'Advertise to parents, students and schools across the Skoolyst network.',
            'active' => true,
        ],
        [
            'key' => 'docs', 'name' => 'Docs', 'url' => 'https://docs.skoolyst.com', 'icon' => 'fa-solid fa-book',
            'description' => 'Guides and documentation for every Skoolyst product.',
            'active' => true,
        ],
    ],
];
