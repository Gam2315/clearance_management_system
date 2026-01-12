<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeederTable extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         DB::table('departments')->insert([
            [
                'department_code' => 'SITE',
                'department_name' => 'SCHOOL OF INFORMATION TECHNOLOGY AND ENGINEERING',
                'department_head' => 'N/A',
                
            ],

            [
                'department_code' => 'SASTE',
                'department_name' => 'SCHOOL OF ARTS, SCIENCES AND TEACHER EDUCATION',
                'department_head' => 'N/A',
               
              
            ],

            [
                'department_code' => 'SNAHS',
                'department_name' => 'SCHOOL OF NURSING AND ALLIED HEALTH SCIENCES',
                'department_head' => 'N/A',
               
              
            ],

            [
                'department_code' => 'SBAHM',
                'department_name' => 'SCHOOL OF BUSINESS, ACCOUNTANCY AND HOSPITALITY MANAGEMENT',
                'department_head' => 'N/A',


            ],

            [
                'department_code' => 'BAO',
                'department_name' => 'BUSINESS AFFAIRS OFFICE',
                'department_head' => 'N/A',
            ],


        ]);
    }
}
