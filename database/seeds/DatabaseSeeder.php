<?php

use App\Models\Permission;
use App\Models\Translation;
use Database\Seeders\BusinessSettingsSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(TranslationSeeder::class);

        DB::table('business_settings')
        ->where('type', 'top10_categories')
        ->where('id', 91)
        ->update([
            'value' => json_encode(["57", "61", "60", "59"]),
        ]);



        $langKey = preg_replace('/[^A-Za-z0-9_]/', '', str_replace(' ', '_', strtolower('Sales Report')));

        $translationEn = Translation::firstOrCreate(
            ['lang' => 'en', 'lang_key' => $langKey],
            ['lang_value' => 'Sales Report']
        );

        $translationAr = Translation::firstOrCreate(
            ['lang' => 'sa', 'lang_key' => $langKey],
            ['lang_value' => 'تقرير المبيعات']
        );

        Cache::forget('translations-en');
        Cache::forget('translations-sa');



        Permission::firstOrCreate(
            ['name' => 'sales_report'],
            [
                'section' => 'report',
                'guard_name' => 'web',
                'created_at' => now(),
            ]
        );


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
            'Upload Type' => [
                'en' => 'Upload Type',
                'sa' => 'نوع التحميل',
            ],
            'Upload New Products' => [
                'en' => 'Upload New Products',
                'sa' => 'تحميل منتجات جديدة',
            ],
            'Edit Existing Products' => [
                'en' => 'Edit Existing Products',
                'sa' => 'تعديل المنتجات الحالية',
            ],
            '4 interest-free installments starting from' => [
                'en' => '4 interest-free installments starting from',
                'sa' => '4 دفعات بدون فوائد تبدأ من',
            ],
            'month' => [
                'en' => 'month',
                'sa' => 'شهر',
            ],
            'Sharia compliant.' => [
                'en' => 'Sharia compliant.',
                'sa' => 'متوافق مع الشريعة الإسلامية.',
            ],
            'Learn more.' => [
                'en' => 'Learn more.',
                'sa' => 'اعرف المزيد.',
            ],
            'Tabby is available for orders over' => [
                'en' => 'Tabby is available for orders over',
                'sa' => 'تتوفر خدمة تابي للطلبات التي تزيد عن',
            ],
            'Add more items to your cart to use Tabby at checkout.' => [
                'en' => 'Add more items to your cart to use Tabby at checkout.',
                'sa' => 'أضف المزيد من العناصر إلى سلة التسوق الخاصة بك لاستخدام تابي عند الدفع.',
            ],
            'Divide it into 4 interest-free installments' => [
                'en' => 'Divide it into 4 interest-free installments',
                'sa' => 'قسّمها إلى 4 دفعات بدون فوائد',
            ],
            'Split your purchases and pay at your convenience' => [
                'en' => 'Split your purchases and pay at your convenience',
                'sa' => 'قسّم مشترياتك وادفع في الوقت المناسب لك',
            ],
            'No fees or interest. Sharia-compliant' => [
                'en' => 'No fees or interest. Sharia-compliant',
                'sa' => 'لا توجد رسوم أو فوائد. متوافق مع الشريعة الإسلامية',
            ],
            'How Tabby works' => [
                'en' => 'How Tabby works',
                'sa' => 'كيف تعمل تابي',
            ],
            'Select a payment plan' => [
                'en' => 'Select a payment plan',
                'sa' => 'اختر خطة الدفع',
            ],
            'Choose Tabby at checkout to defer your payment' => [
                'en' => 'Choose Tabby at checkout to defer your payment',
                'sa' => 'اختر تابي عند الدفع لتأجيل دفعتك',
            ],
            'Enter your details' => [
                'en' => 'Enter your details',
                'sa' => 'أدخل بياناتك',
            ],
            'Add your debit or credit card information securely' => [
                'en' => 'Add your debit or credit card information securely',
                'sa' => 'أضف معلومات بطاقة الخصم أو الائتمان الخاصة بك بشكل آمن',
            ],
            'First payment processed' => [
                'en' => 'First payment processed',
                'sa' => 'معالجة الدفعة الأولى',
            ],
            'Your first installment will be charged when order is completed' => [
                'en' => 'Your first installment will be charged when order is completed',
                'sa' => 'سيتم تحصيل القسط الأول عند اكتمال الطلب',
            ],
            'Payment reminders' => [
                'en' => 'Payment reminders',
                'sa' => 'تذكير بالدفع',
            ],
            'We will notify you when your next payment is due' => [
                'en' => 'We will notify you when your next payment is due',
                'sa' => 'سنخطرك عندما يحين موعد دفعتك التالية',
            ],
            'Sharia compliant' => [
                'en' => 'Sharia compliant',
                'sa' => 'متوافق مع الشريعة الإسلامية',
            ],
            'Fully compliant with Islamic finance principles' => [
                'en' => 'Fully compliant with Islamic finance principles',
                'sa' => 'متوافق بالكامل مع مبادئ التمويل الإسلامي',
            ],
            'Trusted by millions' => [
                'en' => 'Trusted by millions',
                'sa' => 'موثوق به من قبل الملايين',
            ],
            'Over 5 million customers across the region' => [
                'en' => 'Over 5 million customers across the region',
                'sa' => 'أكثر من 5 ملايين عميل في جميع أنحاء المنطقة',
            ],
            'No hidden fees' => [
                'en' => 'No hidden fees',
                'sa' => 'لا توجد رسوم خفية',
            ],
            'No interest or late payment fees' => [
                'en' => 'No interest or late payment fees',
                'sa' => 'لا توجد فوائد أو رسوم تأخير في الدفع',
            ],
            'Shop safely with Tabby' => [
                'en' => 'Shop safely with Tabby',
                'sa' => 'تسوق بأمان مع تابي',
            ],
            'Your purchases are protected with our Buyer Protection program' => [
                'en' => 'Your purchases are protected with our Buyer Protection program',
                'sa' => 'مشترياتك محمية ببرنامج حماية المشتري الخاص بنا',
            ],
            'Continue Shopping' => [
                'en' => 'Continue Shopping',
                'sa' => 'مواصلة التسوق',
            ],
            'installments of' => [
                'en' => 'installments of',
                'sa' => 'أقساط من',
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

        $this->call(BusinessSettingsSeeder::class);
    }
}
