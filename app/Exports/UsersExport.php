<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


use App\Models\ProductMasterList;
use App\Models\Product;
use App\Models\Supplier;

class UsersExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        //for product

        // return Product::select(
        //                 'su.supplier_code',
        //                 'pml.product_code',
        //                 'product.delivery_date',
        //                 'product.reference_delivery_document',
        //                 'product.serial_number',
        //                 'product.warranty',
        //                 'product.warranty_start',
        //                 'product.warranty_end',
        //                 'st.name',
        //                 'product.remarks'
        //             )
        //         ->leftJoin('product_master_list as pml','product.product','=','pml.id')
        //         ->leftJoin('supplier as su','product.supplier','=','su.id')
        //         ->leftJoin('status as st','product.status','=','st.id')
        //         ->orderBy('product.updated_at', 'desc')
        //         ->limit(100)->get();

        //for product masterlist
        
        // return ProductMasterList::select(
        //         'product_master_list.product_code',
        //         'product_master_list.product_name',
        //         'cat.name'
        //     )
        //     ->leftJoin('category as cat','product_master_list.category','=','cat.id')
        //     ->limit(100)->get();

        return Supplier::select(
                'supplier_code','supplier_name','address','tin','contact_person','contact_number','email'
            )->limit(100)->get();
    }

    public function headings(): array
    {
        //for product
        // return [
        //     'SUPPLIER CODE',
        //     'PRODUCT CODE',
        //     'DELIVERY DATE',
        //     'REFERENCE DELIVERY DOCUMENT',
        //     'SERIAL NUMBER',
        //     'WARRANTY',
        //     'WARRANTY START',
        //     'WARRANTY END',
        //     'STATUS',
        //     'REMARKS'
        // ];

        //for product master list

        // return [
        //     'PRODUCT CODE',
        //     'PRODUCT NAME',
        //     'CATEGORY'
        // ];

        return [
            "SUPPLIER CODE",
            "SUPPLIER NAME",
            "ADDRESS",
            "TIN",
            "CONTACT PERSON",
            "CONTACT NUMBER",
            "EMAIL",
        ];

    }
}
