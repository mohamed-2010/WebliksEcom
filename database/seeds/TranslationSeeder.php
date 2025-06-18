<?php

use App\Models\Translation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class TranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Array of translations
        $translations = [
            'Select the export type (All, Category, or Brand)' => [
                'en' => 'Select the export type (All, Category, or Brand)',
                'sa' => 'اختر نوع التصدير (الكل، الفئة، أو العلامة التجارية)',
            ],
            'If you select Category, choose a category and optionally a subcategory' => [
                'en' => 'If you select Category, choose a category and optionally a subcategory',
                'sa' => 'إذا اخترت الفئة، اختر فئة واختياريًا فئة فرعية',
            ],
            'If you select Brand, choose a brand' => [
                'en' => 'If you select Brand, choose a brand',
                'sa' => 'إذا اخترت العلامة التجارية، اختر علامة تجارية',
            ],
            'Click "Export CSV" to download the filtered products' => [
                'en' => 'Click "Export CSV" to download the filtered products',
                'sa' => 'انقر على "تصدير CSV" لتنزيل المنتجات المفلترة',
            ],
            'Export Product File' => [
                'en' => 'Export Product File',
                'sa' => 'تصدير ملف المنتجات',
            ],
            'Export Type' => [
                'en' => 'Export Type',
                'sa' => 'نوع التصدير',
            ],
            'By Brand' => [
                'en' => 'By Brand',
                'sa' => 'حسب العلامة التجارية',
            ],
            'By Category' => [
                'en' => 'By Category',
                'sa' => 'حسب الفئة',
            ],
            'All Products' => [
                'en' => 'All Products',
                'sa' => 'جميع المنتجات',
            ],
            'Select Category' => [
                'en' => 'Select Category',
                'sa' => 'اختر الفئة',
            ],
            'Select Subcategory' => [
                'en' => 'Select Subcategory',
                'sa' => 'اختر الفئة الفرعية',
            ],
            'Select Brand' => [
                'en' => 'Select Brand',
                'sa' => 'اختر العلامة التجارية',
            ],
            'Export CSV' => [
                'en' => 'Export CSV',
                'sa' => 'تصدير CSV',
            ],
            'Maximum Upload File Size: 1 MB' => [
                'en' => 'Maximum Upload File Size: 1 MB',
                'sa' => 'الحد الأقصى لحجم الملف المرفوع: 1 ميجابايت',
            ],
            'Upload New Products' => [
                'en' => 'Upload New Products',
                'sa' => 'تحميل منتجات جديدة',
            ],
            'Edit Existing Products' => [
                'en' => 'Edit Existing Products',
                'sa' => 'تعديل المنتجات الحالية',
            ],
        ];

        foreach ($translations as $key => $values) {
            $langKey = preg_replace('/[^A-Za-z0-9_]/', '', str_replace(' ', '_', strtolower($key)));

            Translation::firstOrCreate(
                ['lang' => 'en', 'lang_key' => $langKey],
                ['lang_value' => $values['en']]
            );

            Translation::firstOrCreate(
                ['lang' => 'sa', 'lang_key' => $langKey],
                ['lang_value' => $values['sa']]
            );
        }

        Cache::forget('translations-en');
        Cache::forget('translations-sa');
    }
}
