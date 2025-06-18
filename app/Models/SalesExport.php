<?php

namespace App\Models;

use App\Models\Product;
use App\Models\Language;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesExport implements FromCollection, WithMapping, WithHeadings, WithStyles
{
    private $data;

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        $fields = [
            'Code',
            'Products',
            'Branch',
            'Customer',
            'Seller',
            'Amount',
            'Delivery Status',
            'Payment method',
            'Payment Status'
        ];

        return $fields;
    }

    public function map($product): array
    {
        $mappedData = [
            $product['code'],
            $product['products'],
            $product['branch'],
            $product['customer'],
            $product['seller'],
            $product['amount'],
            $product['delivery_status'],
            $product['payment_method'],
            $product['payment_status']
        ];

        return $mappedData;
    }

        /**
     * Apply styles to the Excel sheet.
     *
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Apply bold styling to the first row (headings)
        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }
}
